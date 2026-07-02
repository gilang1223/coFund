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
     * Process disbursement for a successful campaign (admin only).
     * Fee: 5% platform fee. Credits creator's balance.
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

            return $disbursement->load(['user']);
        });
    }

    /**
     * Process refunds for a failed campaign (admin only).
     * Credits back each backer's balance.
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
