<?php

namespace App\Console\Commands;

use App\Jobs\SendNotificationJob;
use App\Models\Campaign;
use App\Models\Backing;
use Illuminate\Console\Command;

class NotifyDeadlineApproaching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:notify-deadline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to backers about approaching campaign deadlines (H-3 and H-1)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $notifiedCount = 0;

        // Campaigns with deadline in 3 days (H-3)
        $hMinus3 = Campaign::where('status', 'active')
            ->whereDate('deadline', '=', $now->copy()->addDays(3)->toDateString())
            ->get();

        foreach ($hMinus3 as $campaign) {
            $backings = Backing::where('campaign_id', $campaign->id)
                ->where('status', 'completed')
                ->with('backer')
                ->get();

            foreach ($backings as $backing) {
                SendNotificationJob::dispatch(
                    $backing->user_id,
                    'deadline_approaching',
                    'Deadline H-3 ⏰',
                    "Kampanye \"{$campaign->title}\" akan berakhir dalam 3 hari! Target: Rp " . number_format($campaign->target_amount, 0, ',', '.') . ", terkumpul: Rp " . number_format($campaign->collected_amount, 0, ',', '.') . ".",
                    [
                        'campaign_id' => $campaign->id,
                        'days_left' => 3,
                        'progress' => $campaign->collected_amount / max($campaign->target_amount, 1) * 100,
                    ]
                );
                $notifiedCount++;
            }
        }

        // Campaigns with deadline in 1 day (H-1)
        $hMinus1 = Campaign::where('status', 'active')
            ->whereDate('deadline', '=', $now->copy()->addDay()->toDateString())
            ->get();

        foreach ($hMinus1 as $campaign) {
            $backings = Backing::where('campaign_id', $campaign->id)
                ->where('status', 'completed')
                ->with('backer')
                ->get();

            foreach ($backings as $backing) {
                SendNotificationJob::dispatch(
                    $backing->user_id,
                    'deadline_approaching',
                    'Deadline H-1 ⚠️',
                    "Kampanye \"{$campaign->title}\" akan berakhir besok! Ajak teman-temanmu untuk mendukung sebelum deadline.",
                    [
                        'campaign_id' => $campaign->id,
                        'days_left' => 1,
                        'progress' => $campaign->collected_amount / max($campaign->target_amount, 1) * 100,
                    ]
                );
                $notifiedCount++;
            }
        }

        $this->info("Sent {$notifiedCount} deadline approaching notification(s) to backers.");
    }
}
