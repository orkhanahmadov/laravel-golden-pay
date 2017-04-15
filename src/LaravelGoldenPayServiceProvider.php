<?php

namespace Orkhanahmadov\LaravelGoldenPay;

use Illuminate\Support\ServiceProvider;

/**
 * Class GoldenPayServiceProvider
 * @package Orkhanahmadov\LaravelGoldenPay
 */
class LaravelGoldenPayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migration');

        $this->loadRoutesFrom(__DIR__ . '/Route/routes.php');

        $this->publishes([
            __DIR__ . '/Config' => config_path('goldenpay'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/main.php', 'goldenpay'
        );

        $this->app->bind('laravelgoldenpay', function () {
            return new \Orkhanahmadov\LaravelGoldenPay\GoldenPay;
        });


        $this->app->singleton('orkhanahmadov.laravelgoldenpay.console.kernel', function($app) {
            $dispatcher = $app->make(\Illuminate\Contracts\Events\Dispatcher::class);
            return new \Orkhanahmadov\LaravelGoldenPay\Console\Kernel($app, $dispatcher);
        });

        $this->app->make('orkhanahmadov.laravelgoldenpay.console.kernel');
    }
}