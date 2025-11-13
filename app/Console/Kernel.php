<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
    }


    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
