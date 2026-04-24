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
        // $schedule->command('inspire')->hourly();
        
        // Generar reportes semanales de insights automáticamente
        // Se ejecuta cada lunes a las 8:00 AM
        $schedule->command('insights:weekly')
            ->weeklyOn(1, '8:00')
            ->timezone('America/Mexico_City');
        
        // Enviar recordatorios de citas por WhatsApp
        // Se ejecuta todos los días a las 10:00 AM
        $schedule->command('citas:recordatorios --dias=1')
            ->dailyAt('10:00')
            ->timezone('America/Mexico_City')
            ->when(function () {
                return config('services.twilio.enabled', false);
            });
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
