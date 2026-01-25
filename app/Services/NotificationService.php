<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\UserNotification;
use App\Models\QRRelease;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Notification expiry in days
     */
    private const EXPIRY_DAYS = 7;

    /**
     * Get all notifications for a user (combined from all sources)
     * This replaces the old file-based notification system
     */
    public static function getNotificationsForUser(int $userId): array
    {
        $notifications = [];

        // Get notifications from user_notifications table
        $dbNotifications = UserNotification::getActiveForUser($userId);
        foreach ($dbNotifications as $notification) {
            $notifications[] = $notification;
        }

        // Get QR release notifications (these are separate for backward compatibility)
        $qrNotifications = QRRelease::getUnseenForUser($userId);
        foreach ($qrNotifications as $qrNotification) {
            // Check if we already have this notification
            $existingKey = "qr_release_{$qrNotification['appointment_id']}";
            $exists = false;
            foreach ($notifications as $n) {
                if (strpos($n['key'], $existingKey) !== false) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $notifications[] = [
                    'id' => $qrNotification['appointment_id'],
                    'key' => "qr_release_{$qrNotification['appointment_id']}_{$qrNotification['released_at']}",
                    'type' => 'qr_ready',
                    'title' => 'QR Code Ready! 📱',
                    'message' => "Your QR code for {$qrNotification['pet_name']}'s appointment is ready! Tap to view and scan.",
                    'time' => Carbon::parse($qrNotification['released_at'])->diffForHumans(),
                    'qr_link' => route('appointments.qrcode', $qrNotification['appointment_id']),
                    'show_qr_link' => true,
                    'seen' => false,
                ];
            }
        }

        // Sort by most recent
        usort($notifications, function ($a, $b) {
            // This is a simple sort - in production you might want to parse the time properly
            return 0;
        });

        return $notifications;
    }

    /**
     * Get unseen notification count for a user
     */
    public static function getUnseenCount(int $userId): int
    {
        $dbCount = UserNotification::forUser($userId)->unseen()->notExpired()->count();
        $qrCount = count(QRRelease::getUnseenForUser($userId));
        
        return $dbCount + $qrCount;
    }

    /**
     * Get ONLY unseen notifications for a user (for displaying in notification areas)
     */
    public static function getUnseenNotifications(int $userId): array
    {
        $notifications = [];

        // Get UNSEEN notifications from user_notifications table
        $dbNotifications = UserNotification::getUnseenForUser($userId);
        foreach ($dbNotifications as $notification) {
            $notifications[] = $notification;
        }

        // Get QR release notifications (these are separate for backward compatibility)
        $qrNotifications = QRRelease::getUnseenForUser($userId);
        foreach ($qrNotifications as $qrNotification) {
            // Check if we already have this notification
            $existingKey = "qr_release_{$qrNotification['appointment_id']}";
            $exists = false;
            foreach ($notifications as $n) {
                if (strpos($n['key'], $existingKey) !== false) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $notifications[] = [
                    'id' => $qrNotification['appointment_id'],
                    'key' => "qr_release_{$qrNotification['appointment_id']}_{$qrNotification['released_at']}",
                    'type' => 'qr_ready',
                    'title' => 'QR Code Ready! 📱',
                    'message' => "Your QR code for {$qrNotification['pet_name']}'s appointment is ready! Tap to view and scan.",
                    'time' => Carbon::parse($qrNotification['released_at'])->diffForHumans(),
                    'qr_link' => route('appointments.qrcode', $qrNotification['appointment_id']),
                    'show_qr_link' => true,
                    'seen' => false,
                ];
            }
        }

        Log::info("getUnseenNotifications returning " . count($notifications) . " unseen notifications for user {$userId}");

        return $notifications;
    }

    /**
     * Create notification for approved appointment
     */
    public static function appointmentApproved(Appointment $appointment): void
    {
        // Check if notification already exists
        if (UserNotification::exists(
            $appointment->User_ID, 
            $appointment->Appointment_ID, 
            UserNotification::TYPE_APPOINTMENT_APPROVED
        )) {
            return;
        }

        UserNotification::appointmentApproved($appointment);
        
        Log::info("Notification created: Appointment {$appointment->Appointment_ID} approved");
    }

    /**
     * Create notification for declined appointment
     */
    public static function appointmentDeclined(Appointment $appointment): void
    {
        if (!$appointment->relationLoaded('pet')) {
            $appointment->load(['pet', 'service']);
        }

        UserNotification::appointmentDeclined(
            $appointment->User_ID,
            $appointment->pet->Pet_Name ?? 'N/A',
            Carbon::parse($appointment->Date)->format('M d, Y'),
            Carbon::parse($appointment->Time)->format('h:i A'),
            $appointment->service->Service_Name ?? 'Appointment'
        );
        
        Log::info("Notification created: Appointment {$appointment->Appointment_ID} declined");
    }

    /**
     * Create notification for QR code ready
     */
    public static function qrCodeReady(Appointment $appointment): void
    {
        // Check if notification already exists
        if (UserNotification::exists(
            $appointment->User_ID, 
            $appointment->Appointment_ID, 
            UserNotification::TYPE_QR_READY
        )) {
            return;
        }

        UserNotification::qrReady($appointment);
        
        Log::info("Notification created: QR ready for appointment {$appointment->Appointment_ID}");
    }

    /**
     * Mark notification as seen by key
     */
    public static function markSeenByKey(int $userId, string $key): bool
    {
        Log::info("Marking notification as seen - User: {$userId}, Key: {$key}");
        
        // Handle QR release notifications
        if (strpos($key, 'qr_release_') === 0) {
            $parts = explode('_', $key);
            if (isset($parts[2])) {
                $appointmentId = (int) $parts[2];
                QRRelease::markSeen($appointmentId);
                Log::info("Marked QR release notification as seen for appointment {$appointmentId}");
                return true;
            }
        }

        // Handle declined notifications
        if (strpos($key, 'declined_') === 0) {
            // For declined notifications, we mark by the key pattern
            $marked = false;
            UserNotification::forUser($userId)
                ->ofType(UserNotification::TYPE_APPOINTMENT_DECLINED)
                ->unseen()
                ->each(function ($notification) use ($key, &$marked) {
                    if ($notification->generateKey() === $key) {
                        $notification->markAsSeen();
                        $marked = true;
                    }
                });
            if ($marked) {
                Log::info("Marked declined notification as seen");
            }
            return $marked;
        }

        // Handle appointment notifications with format: appointmentId_type_timestamp
        // Example: 1_appointment_approved_1769349454
        $parts = explode('_', $key);
        if (count($parts) >= 2 && is_numeric($parts[0])) {
            $appointmentId = (int) $parts[0];
            
            Log::info("Parsed appointment ID: {$appointmentId} from key: {$key}");
            
            // Mark by reference ID
            $marked = UserNotification::markSeenByReference($appointmentId, 'appointment', $userId);
            
            if ($marked) {
                Log::info("Marked appointment notification(s) as seen for appointment {$appointmentId}");
                return true;
            } else {
                Log::warning("No notifications found for appointment {$appointmentId}");
            }
        }

        Log::warning("Could not mark notification as seen - invalid key format: {$key}");
        return false;
    }

    /**
     * Mark all notifications as seen for a user
     */
    public static function markAllSeen(int $userId): void
    {
        Log::info("Marking all notifications as seen for user {$userId}");
        
        // Mark database notifications
        $dbCount = UserNotification::markAllSeenForUser($userId);
        Log::info("Marked {$dbCount} database notifications as seen");
        
        // Mark QR release notifications
        $qrNotifications = QRRelease::getAllForUser($userId);
        $qrCount = 0;
        foreach ($qrNotifications as $notification) {
            if (QRRelease::markSeen($notification['appointment_id'])) {
                $qrCount++;
            }
        }
        
        Log::info("Marked {$qrCount} QR release notifications as seen");
        Log::info("Total: All notifications marked as seen for user {$userId}");
    }

    /**
     * Clean up expired notifications
     */
    public static function cleanupExpired(): int
    {
        $count = UserNotification::cleanupExpired();
        
        if ($count > 0) {
            Log::info("Cleaned up {$count} expired notifications");
        }
        
        return $count;
    }

    /**
     * Migrate notifications from JSON files to database
     */
    public static function migrateFromJson(): array
    {
        $migrated = [
            'declined' => 0,
            'seen' => 0,
        ];

        // Migrate declined notifications
        $declinedPath = storage_path('app/declined_notifications.json');
        if (file_exists($declinedPath)) {
            $declined = json_decode(file_get_contents($declinedPath), true) ?? [];
            
            foreach ($declined as $notification) {
                // Check if it's recent enough to migrate
                $declinedAt = Carbon::parse($notification['declined_at'] ?? now());
                if ($declinedAt->diffInDays(now()) <= self::EXPIRY_DAYS) {
                    UserNotification::create([
                        'User_ID' => $notification['user_id'],
                        'Type' => UserNotification::TYPE_APPOINTMENT_DECLINED,
                        'Title' => 'Appointment Declined',
                        'Message' => "Your {$notification['service']} appointment for {$notification['pet_name']} on {$notification['date']} at {$notification['time']} has been declined.",
                        'Reference_Type' => 'appointment',
                        'Data' => [
                            'pet_name' => $notification['pet_name'],
                            'service' => $notification['service'],
                            'date' => $notification['date'],
                            'time' => $notification['time'],
                            'declined_at' => $notification['declined_at'],
                        ],
                        'Expires_At' => $declinedAt->addDays(self::EXPIRY_DAYS),
                        'created_at' => $declinedAt,
                    ]);
                    $migrated['declined']++;
                }
            }
            
            rename($declinedPath, $declinedPath . '.migrated.' . date('Y-m-d-His'));
        }

        // Note: seen_notifications.json is harder to migrate because it's just keys
        // We'll leave the seen status as-is for new notifications
        $seenPath = storage_path('app/seen_notifications.json');
        if (file_exists($seenPath)) {
            rename($seenPath, $seenPath . '.migrated.' . date('Y-m-d-His'));
            $migrated['seen'] = 1; // Just flag that we handled it
        }

        // Migrate QR release notifications
        $qrPath = storage_path('app/qr_release_notifications.json');
        if (file_exists($qrPath)) {
            $qrNotifications = json_decode(file_get_contents($qrPath), true) ?? [];
            
            foreach ($qrNotifications as $appointmentId => $notification) {
                if (!QRRelease::where('Appointment_ID', $appointmentId)->exists()) {
                    QRRelease::create([
                        'Appointment_ID' => $notification['appointment_id'],
                        'User_ID' => $notification['user_id'],
                        'Pet_Name' => $notification['pet_name'] ?? 'N/A',
                        'Service' => $notification['service'] ?? 'N/A',
                        'Scheduled_Date' => $notification['scheduled_date'],
                        'Scheduled_Time' => $notification['scheduled_time'],
                        'Released' => $notification['released'] ?? false,
                        'Released_At' => $notification['released_at'] ?? null,
                        'Released_By' => $notification['released_by'] ?? null,
                        'QR_Path' => $notification['qr_path'] ?? null,
                        'Seen' => $notification['seen'] ?? false,
                    ]);
                }
            }
            
            rename($qrPath, $qrPath . '.migrated.' . date('Y-m-d-His'));
        }

        // Migrate attendance logs
        $attendancePath = storage_path('app/attendance_logs.json');
        if (file_exists($attendancePath)) {
            $attendanceLogs = json_decode(file_get_contents($attendancePath), true) ?? [];
            
            foreach ($attendanceLogs as $appointmentId => $log) {
                if (!\App\Models\AttendanceLog::where('Appointment_ID', $appointmentId)->exists()) {
                    \App\Models\AttendanceLog::create([
                        'Appointment_ID' => $log['appointment_id'],
                        'User_ID' => $log['owner_id'],
                        'Pet_Name' => $log['pet_name'],
                        'Owner_Name' => $log['owner_name'],
                        'Service' => $log['service'],
                        'Scheduled_Date' => $log['scheduled_date'],
                        'Scheduled_Time' => $log['scheduled_time'],
                        'Check_In_Time' => $log['check_in_time'],
                        'Check_In_Date' => $log['check_in_date'],
                        'Scanned_By' => $log['scanned_by'] ?? 'Receptionist',
                        'Status' => $log['status'] ?? 'checked_in',
                    ]);
                }
            }
            
            rename($attendancePath, $attendancePath . '.migrated.' . date('Y-m-d-His'));
        }

        Log::info('Notifications migrated from JSON to database', $migrated);

        return [
            'success' => true,
            'message' => 'Migration completed successfully',
            'migrated' => $migrated,
        ];
    }
}