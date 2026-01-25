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
        $projectId = $config['project_id'] ?? env('GOOGLE_CLOUD_PROJECT_ID') ?? env('GCLOUD_PROJECT');
        $bucketName = $config['bucket'] ?? env('GOOGLE_CLOUD_STORAGE_BUCKET');

        if (!$bucketName) {
            throw new \InvalidArgumentException('GCS bucket is not configured.');
        }

        $storageClient = new \Google\Cloud\Storage\StorageClient([
            'projectId' => $projectId,
        ]);

        $bucket = $storageClient->bucket($bucketName);

        $prefix = trim((string)($config['path_prefix'] ?? ''), '/');

        $adapter = new \League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter(
            $bucket,
            $prefix !== '' ? $prefix . '/' : '',
            null,
            'private'
        );



        $flysystem = new \League\Flysystem\Filesystem($adapter);

        return new \Illuminate\Filesystem\FilesystemAdapter($flysystem, $adapter, $config);
    });

    }
}
