<?php

namespace Darkraul79\Cartify;

use Illuminate\Support\ServiceProvider;

class CartifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cartify.php', 'cartify');

        $this->app->singleton('cart', fn ($app) => new CartManager);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cartify.php' => config_path('cartify.php'),
            ], 'cartify-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'cartify-migrations');
        }

        // Load helpers
        if (file_exists(__DIR__.'/Helpers/helpers.php')) {
            require_once __DIR__.'/Helpers/helpers.php';
        }
    }
}
