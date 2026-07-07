<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService extends BaseService
{
    /**
     * Get transactions by user.
     */
    public function getByUser(int $userId)
    {
        return Transaction::with(['backing.campaign'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get a transaction by reference.
     */
    public function getByReference(string $reference)
    {
        return Transaction::with(['backing.campaign', 'user'])
            ->where('reference', $reference)
            ->first();
    }

    /**
     * Process disbursement for a successful campaign.
     * Fee: 5% platform fee deducted at disbursement (Rule 8). Credits creator's balance.
     * Can process from 'success' or 'active' (if target reached before deadline).
     */
    public function processDisbursement(int $campaignId): ?Transaction
    {
        return DB::transaction(function () use ($campaignId) {
            $campaign = Campaign::lockForUpdate()->find($campaignId);

            if (!$campaign) {
                return null;
            }

            // Must be active
            if ($campaign->status !== 'active') {
                return null;
            }

            if (!$campaign->hasReachedTarget()) {
                return null;
            }

            // Check if already disbursed for this campaign (reference includes campaign ID)
            $alreadyDisbursed = Transaction::where('type', 'disbursement')
                ->where('user_id', $campaign->user_id)
                ->where('reference', 'LIKE', 'DISB-' . $campaign->id . '-%')
                ->whereNull('backing_id')
                ->where('status', 'success')
                ->exists();

            if ($alreadyDisbursed) {
                return null;
            }

            // Mark campaign as success if not already
            if ($campaign->status !== 'success') {
                $campaign->update(['status' => 'success']);
            }

            // Calculate fee (5%)
            $platformFee = round($campaign->collected_amount * 0.05, 2);
            $creatorAmount = $campaign->collected_amount - $platformFee;

            // Credit creator's balance
            $creator = User::lockForUpdate()->find($campaign->user_id);
            $creator->addBalance($creatorAmount);

            // Create disbursement transaction for creator
            $disbursement = Transaction::create([
                'user_id' => $campaign->user_id,
                'backing_id' => null,
                'type' => 'disbursement',
                'amount' => $creatorAmount,
                'status' => 'success',
                'reference' => 'DISB-' . $campaign->id . '-' . strtoupper(uniqid()),
            ]);

            // Create platform fee transaction
            Transaction::create([
                'user_id' => $campaign->user_id,
                'backing_id' => null,
                'type' => 'platform_fee',
                'amount' => $platformFee,
                'status' => 'success',
                'reference' => 'FEE-' . $campaign->id . '-' . strtoupper(uniqid()),
            ]);

            // Send notification + email to creator (synchronous)
            \App\Jobs\SendNotificationJob::dispatchSync(
                $campaign->user_id,
                'campaign_success',
                'Kampanye Berhasil! 🎉',
                "Dana kampanye \"{$campaign->title}\" sebesar Rp " . number_format($creatorAmount, 0, ',', '.') . " telah dicairkan ke saldo Anda (setelah fee 5%).",
                ['campaign_id' => $campaign->id, 'amount' => $creatorAmount, 'fee' => $platformFee],
                true, // send email
            );

            return $disbursement->load(['user']);
        });
    }

    /**
     * Process refunds for a failed campaign.
     * Credits back each backer's balance.
     */
    public function processRefunds(int $campaignId): bool
    {
        return DB::transaction(function () use ($campaignId) {
            $campaign = Campaign::lockForUpdate()->find($campaignId);

            if (!$campaign) {
                return false;
            }

            // Can refund from 'active' (expired + not reached target) or 'failed'
            if (!in_array($campaign->status, ['active', 'failed'])) {
                return false;
            }

            if ($campaign->hasReachedTarget() && $campaign->status !== 'failed') {
                return false;
            }

            // Mark campaign as failed
            $campaign->update(['status' => 'failed']);

            // Get all completed backings
            $backings = $campaign->backings()->where('status', 'completed')->get();

            foreach ($backings as $backing) {
                $user = User::lockForUpdate()->find($backing->user_id);

                // Refund to backer's balance
                $user->addBalance($backing->amount);

                // Create refund transaction
                Transaction::create([
                    'user_id' => $backing->user_id,
                    'backing_id' => $backing->id,
                    'type' => 'refund',
                    'amount' => $backing->amount,
                    'status' => 'success',
                    'reference' => 'REF-' . strtoupper(uniqid()),
                ]);

                // Update backing status
                $backing->update(['status' => 'refunded']);

                // Restore tier quota
                if ($backing->tier_id) {
                    $backing->tier()->increment('remaining_quota');
                }
            }

            return true;
        });
    }

    /**
     * Settle an expired campaign — auto-determine disbursement or refund.
     * Returns action taken: 'disbursement', 'refund', or null if cannot settle.
     */
    public function settleCampaign(int $campaignId): ?array
    {
        $campaign = Campaign::find($campaignId);

        if (!$campaign || $campaign->status !== 'active' || !$campaign->isExpired()) {
            return null;
        }

        if ($campaign->hasReachedTarget()) {
            $disbursement = $this->processDisbursement($campaignId);
            if (!$disbursement) {
                return null;
            }
            return [
                'action' => 'disbursement',
                'transaction' => $disbursement,
                'campaign_status' => 'success',
            ];
        } else {
            $success = $this->processRefunds($campaignId);
            if (!$success) {
                return null;
            }
            return [
                'action' => 'refund',
                'campaign_status' => 'failed',
            ];
        }
    }
}
