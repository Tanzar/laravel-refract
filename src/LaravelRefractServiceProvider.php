<?php

declare(strict_types=1);

namespace Tanzar\Refract;

use Illuminate\Support\ServiceProvider;
use Tanzar\Refract\Services\RefractTracker;

class LaravelRefractServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/refract.php', 'refract');

        $this->app->singleton(Refract::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(RefractTracker $tracker): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/refract.php' => config_path('refract.php'),
        ], ['refract', 'refract-config']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['refract', 'refract-migrations']);

        $this->commands([]);

        $tracker->initialize();
    }
}
