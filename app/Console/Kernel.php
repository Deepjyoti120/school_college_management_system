<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('app:attendance-generate-teacher')
            // ->dailyAt('04:00')
            ->dailyAt('22:30')   // runs at 10:30 PM UTC = 4:00 AM IST
            // ->timezone('Asia/Kolkata')
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
