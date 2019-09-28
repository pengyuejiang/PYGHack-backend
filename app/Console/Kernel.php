<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Console\Commands\InitApp;
use App\Console\Commands\InitGit;
use App\Console\Commands\RegisterEmailTemplate;
use App\Console\Commands\RegisterEmailConfig;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        InitApp::class,
        InitGit::class,
        RegisterEmailTemplate::class,
        RegisterEmailConfig::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
