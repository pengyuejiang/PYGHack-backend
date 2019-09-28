<?php

namespace App\Providers;

use ZuggrCloud\ZuggrCloud;
use Illuminate\Support\ServiceProvider;

class ZuggrCloudServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ZuggrCloud::class, function ($app) {
            $config = [
                'app_id' => \env('ZUGGR_CLOUD_APP_ID'),
                'app_secret' => \env('ZUGGR_CLOUD_APP_SECRET'),
                'client_config' => [
                    'node' => \env('ZUGGR_CLOUD_CLIENT_NODE')
                ]
            ];

            $mock = \env('APP_ENV') == 'testing';

            return new ZuggrCloud(\Cache::store('redis'), $config, $mock);
        });
    }
}
