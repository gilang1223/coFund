<?php

namespace App\Services;

use App\Models\Backing;
use App\Models\Campaign;
use App\Models\CampaignTier;
use App\Models\Transaction;
use App\Models\User;
use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\DB;

class BackingService extends BaseService
{
    /**
     * Create a new backing (donation) — pending, menunggu pembayaran via Midtrans.
     */
    public function create(array $data): ?Backing
    {
        $campaign = Campaign::find($data['campaign_id']);

        if (!$campaign || $campaign->status !== 'active') {
            return null;
        }

        // Validate amount minimum
        if ($data['amount'] < 10000) {
            return null;
        }

        // Check if campaign has already reached target
        if ($campaign->hasReachedTarget()) {
            return null;
        }

        // Rule 4: Creator tidak bisa backing kampanye miliknya sendiri
        if ($campaign->user_id === $data['user_id']) {
            return null;
        }

        return DB::transaction(function () use ($data, $campaign) {

            // If tier is selected, validate
            if (!empty($data['tier_id'])) {
                $tier = CampaignTier::lockForUpdate()->find($data['tier_id']);

                if (!$tier || $tier->campaign_id !== $campaign->id) {
                    return null;
                }

                if ($tier->isSoldOut()) {
                    return null;
                }

                if ($data['amount'] < $tier->min_amount) {
                    return null;
                }

                // Decrease remaining quota
                $tier->decrement('remaining_quota');
            }

            $backing = Backing::create([
                'user_id' => $data['user_id'],
                'campaign_id' => $data['campaign_id'],
                'tier_id' => $data['tier_id'] ?? null,
                'amount' => $data['amount'],
                'status' => 'pending',
            ]);

            // Create pending escrow transaction
            Transaction::create([
                'user_id' => $data['user_id'],
                'backing_id' => $backing->id,
                'type' => 'payment',
                'amount' => $data['amount'],
                'status' => 'pending',
                'reference' => 'BACK-' . strtoupper(uniqid()),
            ]);

            return $backing->load(['campaign', 'tier']);
        });
    }

    /**
     * Complete a backing — release funds from escrow to campaign.
     */
    public function complete(int $backingId): ?Backing
    {
        $shouldTriggerSuccess = false;
        $disbursementCampaignId = null;
        $campaignTitle = null;

        $result = DB::transaction(function () use ($backingId, &$shouldTriggerSuccess, &$disbursementCampaignId, &$campaignTitle) {
            $backing = Backing::lockForUpdate()->find($backingId);

            if (!$backing || $backing->status !== 'pending') {
                return null;
            }

            $user = User::lockForUpdate()->find($backing->user_id);
            $campaign = Campaign::lockForUpdate()->find($backing->campaign_id);

            // Deduct from user's balance
            if (!$user->deductBalance($backing->amount)) {
                return null; // Insufficient balance
            }

            // Check if adding this amount would exceed target
            $newTotal = $campaign->collected_amount + $backing->amount;
            if ($newTotal > $campaign->target_amount) {
                // Adjust the backing amount to not exceed target
                $excess = $newTotal - $campaign->target_amount;
                $adjustedAmount = $backing->amount - $excess;

                if ($adjustedAmount <= 0) {
                    // Refund the excess back to user
                    $user->addBalance($backing->amount);
                    return null;
                }

                // Refund excess to user
                $excessRefund = $backing->amount - $adjustedAmount;
                if ($excessRefund > 0) {
                    $user->addBalance($excessRefund);
                }

                $backing->update(['amount' => $adjustedAmount]);
            }

            $backing->update(['status' => 'completed']);

            // Update transaction to success
            $backing->transaction()->update([
                'status' => 'success',
            ]);

            // Increment campaign collected amount
            $campaign->increment('collected_amount', $backing->amount);

            // Refresh campaign to get updated collected_amount
            $campaign->refresh();

            // Evaluate campaign status: if target reached, trigger success & disbursement
            if ($campaign->hasReachedTarget() && $campaign->status === 'active') {
                $shouldTriggerSuccess = true;
                $disbursementCampaignId = $campaign->id;
                $campaignTitle = $campaign->title;
            }

            return $backing->fresh()->load(['campaign', 'tier', 'transaction']);
        });

        // Dispatch jobs AFTER the transaction has fully committed
        // so sync queue processing works correctly
        if ($shouldTriggerSuccess && $disbursementCampaignId) {
            // Proses pencairan dana secara synchronous (bukan via queue)
            app(\App\Services\TransactionService::class)->processDisbursement($disbursementCampaignId);

            // Notify all backers that the campaign succeeded (synchronous)
            $backings = Backing::where('campaign_id', $disbursementCampaignId)
                ->where('status', 'completed')
                ->get();

            foreach ($backings as $backing) {
                SendNotificationJob::dispatchSync(
                    $backing->user_id,
                    'campaign_success',
                    'Kampanye Berhasil! 🎉',
                    "Kampanye \"{$campaignTitle}\" yang Anda dukung telah berhasil mencapai target! Dana telah dicairkan ke creator (setelah fee 5%). Terima kasih atas dukungan Anda.",
                    ['campaign_id' => $disbursementCampaignId, 'backing_id' => $backing->id],
                    true, // send email
                );
            }
        }
 
         if ($result) {
             $backer = User::find($result->user_id);
             $campaign = Campaign::find($result->campaign_id);
 
             // Notify creator (in-app only)
             SendNotificationJob::dispatch(
                 $campaign->user_id,
                 'backing_received',
                 'Donasi Baru Masuk! 💖',
                 "{$backer->name} telah mendukung kampanye \"{$campaign->title}\" dengan donasi sebesar Rp " . number_format($result->amount, 0, ',', '.') . ".",
                 ['campaign_id' => $campaign->id, 'backing_id' => $result->id],
                 false, // send email
             );
 
             // Notify backer (in-app + email)
             SendNotificationJob::dispatch(
                 $result->user_id,
                 'backing_completed',
                 'Donasi Berhasil Dikonfirmasi! 🎉',
                 "Terima kasih! Donasi Anda sebesar Rp " . number_format($result->amount, 0, ',', '.') . " untuk kampanye \"{$campaign->title}\" telah berhasil diterima.",
                 ['campaign_id' => $campaign->id, 'backing_id' => $result->id],
                 true, // send email
             );
         }
 
         return $result;
     }

    /**
     * Cancel/refund a backing — release funds back to backer.
     */
    public function refund(int $backingId): ?Backing
    {
        return DB::transaction(function () use ($backingId) {
            $backing = Backing::lockForUpdate()->find($backingId);

            if (!$backing || $backing->status !== 'pending') {
                return null;
            }

            $user = User::lockForUpdate()->find($backing->user_id);

            // Refund to user's balance
            $user->addBalance($backing->amount);

            $backing->update(['status' => 'refunded']);

            // Update transaction
            $backing->transaction()->update([
                'status' => 'failed',
            ]);

            // Restore tier quota if applicable
            if ($backing->tier_id) {
                $backing->tier()->increment('remaining_quota');
            }

            return $backing->fresh();
        });
    }

    /**
     * Get a backing by ID.
     */
    public function getById(int $id): ?Backing
    {
        return Backing::with(['campaign', 'tier', 'transaction', 'backer'])->find($id);
    }

    /**
     * Get backings by user.
     */
    public function getByUser(int $userId)
    {
        return Backing::with(['campaign', 'tier', 'transaction'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get backings by campaign.
     */
    public function getByCampaign(int $campaignId)
    {
        return Backing::with(['backer', 'tier', 'transaction'])
            ->where('campaign_id', $campaignId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
