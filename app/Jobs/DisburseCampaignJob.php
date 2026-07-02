<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class DisburseCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $campaignId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $campaign = Campaign::lockForUpdate()->find($this->campaignId);

            if (!$campaign || $campaign->status !== 'active' || !$campaign->hasReachedTarget()) {
                return;
            }

            // Mark campaign as success
            $campaign->update(['status' => 'success']);

            // Calculate fee (5%)
            $platformFee = round($campaign->collected_amount * 0.05, 2);
            $creatorAmount = $campaign->collected_amount - $platformFee;

            // Credit creator's balance
            $creator = User::lockForUpdate()->find($campaign->user_id);
            $creator->addBalance($creatorAmount);

            // Create disbursement transaction
            Transaction::create([
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

            // Send notification + email to creator
            SendNotificationJob::dispatch(
                $campaign->user_id,
                'campaign_success',
                'Kampanye Berhasil! 🎉',
                "Selamat! Kampanye \"{$campaign->title}\" berhasil mencapai target. Dana sebesar Rp " . number_format($creatorAmount, 0, ',', '.') . " telah dicairkan ke saldo Anda (setelah fee 5%).",
                [
                    'campaign_id' => $campaign->id,
                    'amount' => $creatorAmount,
                    'fee' => $platformFee,
                ],
                true, // send email
            );
        });
    }
}
