<?php

namespace App\Services;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Log;

class GcsStorageService
{
    private StorageClient $storage;
    private $bucket;
    private string $prefix; // e.g. "ids"

    public function __construct()
    {
        $projectId  = env('GOOGLE_CLOUD_PROJECT_ID', env('GCLOUD_PROJECT'));
        $bucketName = env('GOOGLE_CLOUD_STORAGE_BUCKET');
        $this->prefix = trim((string) env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''), '/');

        $this->storage = new StorageClient(['projectId' => $projectId]);
        $this->bucket = $this->storage->bucket($bucketName);
    }

    public function normalizeObjectName(string $objectName): string
    {
        $objectName = ltrim($objectName, '/');
        if ($this->prefix === '') return $objectName;

        // If caller already included prefix, don't double it
        if (str_starts_with($objectName, $this->prefix . '/')) return $objectName;

        return $this->prefix . '/' . $objectName;
    }

    public function uploadFromLocalPath(string $localPath, string $objectName): string
    {
        $objectName = $this->normalizeObjectName($objectName);

        $this->bucket->upload(
            fopen($localPath, 'r'),
            ['name' => $objectName] // UBLA-safe: no ACL
        );

        return $objectName;
    }

    public function uploadFromUploadedFile(\Illuminate\Http\UploadedFile $file, string $objectName): string
    {
        return $this->uploadFromLocalPath($file->getRealPath(), $objectName);
    }

    public function exists(string $objectName): bool
    {
        $objectName = $this->normalizeObjectName($objectName);
        return $this->bucket->object($objectName)->exists();
    }

    public function delete(string $objectName): void
    {
        $objectName = $this->normalizeObjectName($objectName);
        $obj = $this->bucket->object($objectName);
        if ($obj->exists()) $obj->delete();
    }

    public function move(string $from, string $to): string
    {
        $from = $this->normalizeObjectName($from);
        $to   = $this->normalizeObjectName($to);

        $src = $this->bucket->object($from);
        if (!$src->exists()) {
            throw new \RuntimeException("GCS source object not found: {$from}");
        }

        // Copy then delete (GCS "rename" pattern)
        $src->copy($this->bucket, ['name' => $to]);
        $src->delete();

        return $to;
    }

    public function signedUrl(string $objectName, int $minutes = 10): string
    {
        $objectName = $this->normalizeObjectName($objectName);
        $obj = $this->bucket->object($objectName);

        if (!$obj->exists()) {
            throw new \RuntimeException("GCS object not found: {$objectName}");
        }

        return $obj->signedUrl(new \DateTime("+{$minutes} minutes"));
    }
}
