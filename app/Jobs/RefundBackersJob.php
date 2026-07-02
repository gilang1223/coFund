<?php

namespace App\Jobs;

use App\Models\Backing;
use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RefundBackersJob implements ShouldQueue
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

            if (!$campaign || $campaign->status !== 'active' || $campaign->hasReachedTarget()) {
                return;
            }

            // Mark campaign as failed
            $campaign->update(['status' => 'failed']);

            // Get all completed backings
            $backings = Backing::where('campaign_id', $this->campaignId)
                ->where('status', 'completed')
                ->lockForUpdate()
                ->get();

            foreach ($backings as $backing) {
                // Refund to backer's balance
                $backer = User::lockForUpdate()->find($backing->user_id);
                $backer->addBalance($backing->amount);

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

                // Send notification to backer
                UserNotification::create([
                    'user_id' => $backing->user_id,
                    'type' => 'backing_refunded',
                    'title' => 'Dana Dikembalikan 💰',
                    'body' => "Kampanye \"{$campaign->title}\" tidak mencapai target. Dana sebesar Rp " . number_format($backing->amount, 0, ',', '.') . " telah dikembalikan ke saldo Anda.",
                    'data' => [
                        'campaign_id' => $campaign->id,
                        'backing_id' => $backing->id,
                        'amount' => $backing->amount,
                    ],
                ]);
            }

            // Send notification to creator
            UserNotification::create([
                'user_id' => $campaign->user_id,
                'type' => 'campaign_failed',
                'title' => 'Kampanye Gagal 😔',
                'body' => "Kampanye \"{$campaign->title}\" tidak mencapai target dan telah diakhiri. Semua dana backer telah dikembalikan.",
                'data' => [
                    'campaign_id' => $campaign->id,
                    'collected' => $campaign->collected_amount,
                    'target' => $campaign->target_amount,
                ],
            ]);
        });
    }
}
