<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // // $schedule->command('inspire')->hourly();
        // // 每天凌晨2点执行同步命令
        // $schedule->command('sync:new-domain-rankings')
        //          ->dailyAt('06:00')
        //          ->withoutOverlapping()  // 防止重复执行
        //          ->runInBackground();    // 后台运行
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
