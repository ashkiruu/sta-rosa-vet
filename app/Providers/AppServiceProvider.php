<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Run cleanup randomly (1% chance per request)
        if (rand(1, 100) === 1) {
            try {
                CleanupService::autoCleanup();
            } catch (\Exception $e) {
                // Silently fail
            }
        }
    }
}



    