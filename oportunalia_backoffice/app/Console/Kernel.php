<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\CheckAuctions',
        'App\Console\Commands\CheckFavs',
        'App\Console\Commands\CheckDirectPayment',
        'App\Console\Commands\CheckCesion',
        'App\Console\Commands\SendAuctionsMonthlyCommand',
        'App\Console\Commands\UploadToFotocasa',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        date_default_timezone_set('Europe/Madrid');
        
        $schedule->command('auction:check')->everyFifteenMinutes();
        $schedule->command('favs:check')->everyMinute();
        $schedule->command('direct_payment:check')->everyMinute();
        $schedule->command('cesion:check')->everyMinute();
        $schedule->command('news:auctions')->monthly();
        $schedule->command('upload:fotocasa')->everyMinute();
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
