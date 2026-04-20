<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\MqttListen::class,
        \App\Console\Commands\SomministrazioneScheduler::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Controlla ogni minuto le somministrazioni in scadenza
        $schedule->command('pillmate:scheduler')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
