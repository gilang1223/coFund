<?php

namespace App\Services;

use App\Models\Backing;
use App\Models\Campaign;
use App\Models\CampaignTier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BackingService extends BaseService
{
    /**
     * Create a new backing (donation) — funds held in escrow.
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
        return DB::transaction(function () use ($backingId) {
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

            return $backing->fresh()->load(['campaign', 'tier', 'transaction']);
        });
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
