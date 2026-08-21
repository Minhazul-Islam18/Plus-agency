<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->call(function () {
            \App\SecureToken::where('expires_at', '<', now()->subDays(30))->delete();
        })->weekly();

        // Shared hosting (Hostinger) has no persistent background process,
        // only cron — so the queue worker runs as a scheduled task instead
        // of `queue:work` running forever. Fires every minute, drains
        // whatever's in queue_jobs, exits. withoutOverlapping() stops a
        // slow-draining run from double-running if the queue backs up;
        // max-time is a hard safety cap so it can never bleed into the next
        // minute's invocation.
        //
        // Deliberately Artisan::call() via ->call(), NOT ->command() —
        // ->command() always shells out through Symfony Process (whether or
        // not ->runInBackground() is used), and Process requires proc_open
        // unconditionally, no fallback. Hostinger has proc_open disabled
        // (same restriction that broke `composer install`'s post-autoload
        // script), so a ->command() entry here silently no-ops — schedule:run
        // reports done in ~20ms without ever actually invoking queue:work.
        // Artisan::call() runs the command in-process, no subprocess at all,
        // so it works regardless of proc_open.
        $schedule->call(function () {
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--max-time' => 50,
                '--tries' => 3,
            ]);
        })
            ->name('queue-worker')
            ->everyMinute()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
