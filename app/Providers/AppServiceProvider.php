<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Services\CleanupService;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Filesystem\FilesystemAdapter;


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

        Storage::extend('gcs', function ($app, $config) {
            $storageClient = new StorageClient([
                'projectId' => $config['project_id'] ?? env('GOOGLE_CLOUD_PROJECT_ID') ?? env('GCLOUD_PROJECT'),
            ]);

            $bucket = $storageClient->bucket($config['bucket']);

            $adapter = new GoogleCloudStorageAdapter(
                $bucket,
                $config['path_prefix'] ?? ''
            );

            $flysystem = new Filesystem($adapter);

            // ✅ Important: wrap in Laravel adapter so putFileAs(), url(), etc work
            return new FilesystemAdapter($flysystem, $adapter, $config);
        });
    }
}
