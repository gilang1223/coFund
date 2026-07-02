<?php

namespace App\Console\Commands;

use App\Jobs\DisburseCampaignJob;
use App\Jobs\RefundBackersJob;
use App\Models\Campaign;
use Illuminate\Console\Command;

class CheckExpiredCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all active campaigns that have passed their deadline and process settlement';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredCampaigns = Campaign::where('status', 'active')
            ->where('deadline', '<', now())
            ->get();

        $this->info("Found {$expiredCampaigns->count()} expired campaign(s).");

        $disbursed = 0;
        $refunded = 0;

        foreach ($expiredCampaigns as $campaign) {
            if ($campaign->hasReachedTarget()) {
                $this->line("  → Campaign #{$campaign->id} \"{$campaign->title}\": Target reached — dispatching disbursement...");
                DisburseCampaignJob::dispatch($campaign->id);
                $disbursed++;
            } else {
                $this->line("  → Campaign #{$campaign->id} \"{$campaign->title}\": Target not reached — dispatching refund...");
                RefundBackersJob::dispatch($campaign->id);
                $refunded++;
            }
        }

        $this->newLine();
        $this->info("Done! {$disbursed} campaign(s) sent for disbursement, {$refunded} campaign(s) sent for refund.");
    }
}
