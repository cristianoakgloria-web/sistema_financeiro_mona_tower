<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Executar às 9h da manhã
        $schedule->command('invoices:check-overdue')->dailyAt('09:00');
        
        // Executar às 15h da tarde
        $schedule->command('invoices:check-overdue')->dailyAt('15:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}