<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\StockService::class, function ($app) {
            return new \App\Services\StockService();
        });

        $this->app->singleton(\App\Services\ManifestService::class, function ($app) {
            return new \App\Services\ManifestService(
                $app->make(\App\Services\StockService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manageUsers', function (User $user) {
            return $user->role === 'super_admin';
        });
    }
}
