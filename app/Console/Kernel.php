<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('app:attendance-generate-teacher')
        //     ->dailyAt('06:00')
        //     ->onFailure(function () {
        //         //
        //     });
        $schedule->command('app:attendance-generate-teacher')
            ->everyMinute()
            ->onFailure(function () {
                //
            });
        $schedule->call(function () {
            Log::info('✅ Test cron ran at ' . now());
        })->everyMinute();
    }


    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
