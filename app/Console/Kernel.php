<?php

namespace App\Console;

use App\Console\Commands\CheckExpiredCampaigns;
use App\Console\Commands\NotifyDeadlineApproaching;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by the application.
     *
     * @var array
     */
    protected $commands = [
        CheckExpiredCampaigns::class,
        NotifyDeadlineApproaching::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check expired campaigns every day at 00:05
        $schedule->command('campaigns:check-expired')
            ->dailyAt('00:05')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // Send deadline approaching notifications every day at 08:00
        $schedule->command('campaigns:notify-deadline')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scheduler.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
