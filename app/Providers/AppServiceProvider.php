<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Vite;
use Illuminate\Support\ServiceProvider;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Model::shouldBeStrict(!$this->app->isProduction());
        FilamentFabricator::registerStyles([
            app(Vite::class)([
                'resources/css/app.css',
                'resources/css/frontend.css',
            ]), //vite
        ]);
    }
}
