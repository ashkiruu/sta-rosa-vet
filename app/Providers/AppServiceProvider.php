<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Services\CleanupService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ✅ Force HTTPS URL generation on Cloud Run
        if (app()->environment(['staging', 'production']) && request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');

            if (config('app.url')) URL::forceRootUrl(config('app.url'));
        }

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
