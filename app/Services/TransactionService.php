<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService extends BaseService
{
    /**
     * Process disbursement for a successful campaign (admin only).
     * Fee: 5% platform fee.
     */
    public function processDisbursement(int $campaignId): ?Transaction
    {
        return DB::transaction(function () use ($campaignId) {
            $campaign = Campaign::lockForUpdate()->find($campaignId);

            if (!$campaign || $campaign->status !== 'active' || !$campaign->isExpired()) {
                return null;
            }

            if (!$campaign->hasReachedTarget()) {
                return null;
            }

            // Mark campaign as success
            $campaign->update(['status' => 'success']);

            // Calculate fee (5%)
            $platformFee = $campaign->collected_amount * 0.05;
            $creatorAmount = $campaign->collected_amount - $platformFee;

            // Create disbursement transaction for creator
            $disbursement = Transaction::create([
                'user_id' => $campaign->user_id,
                'backing_id' => null,
                'type' => 'disbursement',
                'amount' => $creatorAmount,
                'status' => 'success',
                'reference' => 'DISB-' . strtoupper(uniqid()),
            ]);

            // Create platform fee transaction
            Transaction::create([
                'user_id' => $campaign->user_id,
                'backing_id' => null,
                'type' => 'platform_fee',
                'amount' => $platformFee,
                'status' => 'success',
                'reference' => 'FEE-' . strtoupper(uniqid()),
            ]);

            return $disbursement;
        });
    }

    /**
     * Process refunds for a failed campaign (scheduled job).
     */
    public function processRefunds(int $campaignId): bool
    {
        return DB::transaction(function () use ($campaignId) {
            $campaign = Campaign::lockForUpdate()->find($campaignId);

            if (!$campaign || $campaign->status !== 'active' || !$campaign->isExpired()) {
                return false;
            }

            if ($campaign->hasReachedTarget()) {
                return false;
            }

            // Mark campaign as failed
            $campaign->update(['status' => 'failed']);

            // Get all completed backings
            $backings = $campaign->backings()->where('status', 'completed')->get();

            foreach ($backings as $backing) {
                // Create refund transaction
                Transaction::create([
                    'user_id' => $backing->user_id,
                    'backing_id' => $backing->id,
                    'type' => 'refund',
                    'amount' => $backing->amount,
                    'status' => 'success',
                    'reference' => 'REF-' . strtoupper(uniqid()),
                ]);

                // Restore tier quota
                if ($backing->tier_id) {
                    $backing->tier()->increment('remaining_quota');
                }
            }

            return true;
        });
    }

    /**
     * Get transactions by user.
     */
    public function getByUser(int $userId)
    {
        return Transaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
