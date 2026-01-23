<?php

namespace App\Services;

use Carbon\Carbon;

class CleanupService
{
    /**
     * Auto cleanup - call this periodically
     * Cleans QR codes older than 30 days and limits notifications to 50 per user
     */
    public static function autoCleanup()
    {
        self::cleanupOldQRCodes(30);
        self::cleanupOldNotifications(50);
        self::cleanupDeclinedNotifications(30);
    }

    /**
     * Clean up old QR codes (older than X days)
     */
    private static function cleanupOldQRCodes($daysOld = 30)
    {
        $qrCodeDir = storage_path('app/public/qrcodes');
        
        if (!is_dir($qrCodeDir)) {
            return;
        }
        
        $cutoffDate = Carbon::now()->subDays($daysOld);
        $files = glob($qrCodeDir . '/appointment_*.png');
        
        foreach ($files as $file) {
            if (Carbon::createFromTimestamp(filemtime($file))->lt($cutoffDate)) {
                @unlink($file);
            }
        }
    }

    /**
     * Clean up notifications - keep only last X per user
     */
    private static function cleanupOldNotifications($maxPerUser = 50)
    {
        $path = storage_path('app/seen_notifications.json');
        
        if (!file_exists($path)) {
            return;
        }
        
        $notifications = json_decode(file_get_contents($path), true) ?? [];
        
        foreach ($notifications as $userId => $items) {
            if (count($items) > $maxPerUser) {
                $notifications[$userId] = array_slice($items, -$maxPerUser);
            }
        }
        
        file_put_contents($path, json_encode($notifications, JSON_PRETTY_PRINT));
    }

    /**
     * Clean up declined notifications older than X days
     */
    private static function cleanupDeclinedNotifications($daysOld = 30)
    {
        $path = storage_path('app/declined_notifications.json');
        
        if (!file_exists($path)) {
            return;
        }
        
        $notifications = json_decode(file_get_contents($path), true) ?? [];
        $cutoffDate = Carbon::now()->subDays($daysOld)->toDateTimeString();
        
        $notifications = array_values(array_filter($notifications, function($record) use ($cutoffDate) {
            return ($record['declined_at'] ?? '') >= $cutoffDate;
        }));
        
        file_put_contents($path, json_encode($notifications, JSON_PRETTY_PRINT));
    }
}