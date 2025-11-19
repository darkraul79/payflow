<?php

namespace Darkraul79\Payflow;

use Darkraul79\Payflow\Gateways\RedsysGateway;
use Darkraul79\Payflow\Gateways\StripeGateway;
use Illuminate\Support\ServiceProvider;

class PayflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/payflow.php', 'payflow');

        $this->app->singleton('gateway', function ($app) {
            $manager = new PayflowManager();

            // Register default gateways
            $manager->extend('redsys', fn () => new RedsysGateway());
            $manager->extend('stripe', fn () => new StripeGateway());

            return $manager;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/payflow.php' => config_path('payflow.php'),
            ], 'payflow-config');
        }

        // Load helpers
        if (file_exists(__DIR__.'/Helpers/helpers.php')) {
            require_once __DIR__.'/Helpers/helpers.php';
        }
    }
}
