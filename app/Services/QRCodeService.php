<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class QRCodeService
{
    /**
     * Generate QR code for an appointment
     * Uses Google Charts API (no package needed)
     * 
     * @param Appointment $appointment
     * @return string|null Path to QR code image
     */
    public static function generateForAppointment(Appointment $appointment)
    {
        // Load relationships if not loaded
        if (!$appointment->relationLoaded('pet')) {
            $appointment->load(['pet', 'service', 'user']);
        }

        // Generate verification URL (this is what the QR code will contain)
        $token = self::generateVerificationToken($appointment);
        $verificationUrl = url("/appointments/verify/{$appointment->Appointment_ID}/{$token}");
        
        // Generate filename
        $filename = 'qrcodes/appointment_' . $appointment->Appointment_ID . '.png';
        $fullPath = storage_path('app/public/' . $filename);
        
        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate QR code using Google Charts API
        $qrCodeUrl = 'https://chart.googleapis.com/chart?'
            . 'chs=300x300'  // Size
            . '&cht=qr'      // Chart type: QR
            . '&chl=' . urlencode($verificationUrl)
            . '&choe=UTF-8'; // Encoding

        // Download and save the QR code image
        $imageContent = @file_get_contents($qrCodeUrl);
        
        if ($imageContent === false) {
            // Fallback: Use alternative QR API
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?'
                . 'size=300x300'
                . '&data=' . urlencode($verificationUrl);
            
            $imageContent = @file_get_contents($qrCodeUrl);
        }

        if ($imageContent === false) {
            return null;
        }

        file_put_contents($fullPath, $imageContent);
        
        return $filename;
    }

    /**
     * Generate a simple verification token for the appointment
     */
    public static function generateVerificationToken(Appointment $appointment)
    {
        // Create a simple hash based on appointment details
        $data = $appointment->Appointment_ID . '-' . $appointment->User_ID . '-' . $appointment->Date;
        return substr(md5($data . config('app.key', 'veterinary-clinic-secret')), 0, 16);
    }

    /**
     * Verify an appointment token
     */
    public static function verifyToken(Appointment $appointment, $token)
    {
        return $token === self::generateVerificationToken($appointment);
    }

    /**
     * Get QR code path for an appointment
     */
    public static function getQRCodePath(Appointment $appointment)
    {
        $filename = 'qrcodes/appointment_' . $appointment->Appointment_ID . '.png';
        $fullPath = storage_path('app/public/' . $filename);
        
        if (file_exists($fullPath)) {
            return $filename;
        }
        
        return null;
    }

    /**
     * Get QR code URL for display
     */
    public static function getQRCodeUrl(Appointment $appointment)
    {
        $path = self::getQRCodePath($appointment);
        
        if ($path) {
            return asset('storage/' . $path);
        }
        
        return null;
    }

    /**
     * Delete QR code for an appointment (when cancelled/deleted)
     */
    public static function deleteQRCode(Appointment $appointment)
    {
        $filename = 'qrcodes/appointment_' . $appointment->Appointment_ID . '.png';
        $fullPath = storage_path('app/public/' . $filename);
        
        if (file_exists($fullPath)) {
            unlink($fullPath);
            return true;
        }
        
        return false;
    }

    /**
     * =====================================================
     * QR CODE RELEASE SYSTEM
     * =====================================================
     */

    /**
     * Get QR release notifications file path
     */
    private static function getQRReleaseNotificationsPath()
    {
        return storage_path('app/qr_release_notifications.json');
    }

    /**
     * Load all QR release notifications
     */
    public static function loadQRReleaseNotifications()
    {
        $path = self::getQRReleaseNotificationsPath();
        
        if (!file_exists($path)) {
            $directory = dirname($path);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($path, json_encode([], JSON_PRETTY_PRINT));
            return [];
        }
        
        return json_decode(file_get_contents($path), true) ?? [];
    }

    /**
     * Save QR release notifications
     */
    private static function saveQRReleaseNotifications($notifications)
    {
        $path = self::getQRReleaseNotificationsPath();
        file_put_contents($path, json_encode($notifications, JSON_PRETTY_PRINT));
    }

    /**
     * Check if QR code has been released for an appointment
     */
    public static function isQRCodeReleased(Appointment $appointment)
    {
        $notifications = self::loadQRReleaseNotifications();
        $appointmentId = $appointment->Appointment_ID;
        
        return isset($notifications[$appointmentId]) && $notifications[$appointmentId]['released'] === true;
    }

    /**
     * Release QR code for an appointment (Admin action)
     * This generates the QR code and creates a notification for the user
     */
    public static function releaseQRCode(Appointment $appointment, $releasedBy = 'Admin')
    {
        // Load relationships if not loaded
        if (!$appointment->relationLoaded('pet')) {
            $appointment->load(['pet', 'service', 'user']);
        }

        $appointmentId = $appointment->Appointment_ID;
        
        // Generate the QR code
        $qrPath = self::generateForAppointment($appointment);
        
        if (!$qrPath) {
            return [
                'success' => false,
                'message' => 'Failed to generate QR code.',
            ];
        }
        
        // Create/update the release notification
        $notifications = self::loadQRReleaseNotifications();
        
        $notifications[$appointmentId] = [
            'appointment_id' => $appointmentId,
            'user_id' => $appointment->User_ID,
            'pet_name' => $appointment->pet->Pet_Name ?? 'N/A',
            'service' => $appointment->service->Service_Name ?? 'N/A',
            'scheduled_date' => $appointment->Date instanceof Carbon 
                ? $appointment->Date->format('Y-m-d') 
                : $appointment->Date,
            'scheduled_time' => $appointment->Time,
            'released' => true,
            'released_at' => now()->format('Y-m-d H:i:s'),
            'released_by' => $releasedBy,
            'qr_path' => $qrPath,
            'seen' => false,
        ];
        
        self::saveQRReleaseNotifications($notifications);
        
        return [
            'success' => true,
            'message' => 'QR code released successfully!',
            'qr_path' => $qrPath,
        ];
    }

    /**
     * Get QR release notification for a specific appointment
     */
    public static function getQRReleaseNotification($appointmentId)
    {
        $notifications = self::loadQRReleaseNotifications();
        return $notifications[$appointmentId] ?? null;
    }

    /**
     * Get all unreleased QR notifications for a user (for display in their notifications)
     */
    public static function getUnseenQRNotificationsForUser($userId)
    {
        $notifications = self::loadQRReleaseNotifications();
        
        return array_filter($notifications, function($notification) use ($userId) {
            return $notification['user_id'] == $userId 
                && $notification['released'] === true 
                && $notification['seen'] === false;
        });
    }

    /**
     * Get all QR notifications for a user
     */
    public static function getQRNotificationsForUser($userId)
    {
        $notifications = self::loadQRReleaseNotifications();
        
        return array_filter($notifications, function($notification) use ($userId) {
            return $notification['user_id'] == $userId && $notification['released'] === true;
        });
    }

    /**
     * Mark QR notification as seen
     */
    public static function markQRNotificationSeen($appointmentId)
    {
        $notifications = self::loadQRReleaseNotifications();
        
        if (isset($notifications[$appointmentId])) {
            $notifications[$appointmentId]['seen'] = true;
            self::saveQRReleaseNotifications($notifications);
            return true;
        }
        
        return false;
    }

    /**
     * =====================================================
     * ATTENDANCE LOGGING SYSTEM (JSON-based, no database)
     * =====================================================
     */

    /**
     * Get attendance log file path
     */
    private static function getAttendanceLogPath()
    {
        return storage_path('app/attendance_logs.json');
    }

    /**
     * Load all attendance logs
     */
    public static function loadAttendanceLogs()
    {
        $path = self::getAttendanceLogPath();
        
        if (!file_exists($path)) {
            $directory = dirname($path);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($path, json_encode([], JSON_PRETTY_PRINT));
            return [];
        }
        
        return json_decode(file_get_contents($path), true) ?? [];
    }

    /**
     * Save attendance logs
     */
    private static function saveAttendanceLogs($logs)
    {
        $path = self::getAttendanceLogPath();
        file_put_contents($path, json_encode($logs, JSON_PRETTY_PRINT));
    }

    /**
     * Record attendance when QR code is scanned
     * 
     * @param Appointment $appointment
     * @param string $scannedBy (optional) Who scanned it
     * @return array Attendance record or error
     */
    public static function recordAttendance(Appointment $appointment, $scannedBy = 'Receptionist')
    {
        // First, check if QR code has been released
        if (!self::isQRCodeReleased($appointment)) {
            return [
                'error' => true,
                'not_released' => true,
                'message' => 'QR code has not been released yet. Please check in at the reception desk.',
            ];
        }
        
        $logs = self::loadAttendanceLogs();
        $appointmentId = $appointment->Appointment_ID;
        
        // Check if already checked in
        $existingRecord = self::getAttendanceRecord($appointmentId);
        
        if ($existingRecord) {
            // Already checked in - return existing record with flag
            $existingRecord['already_checked_in'] = true;
            return $existingRecord;
        }
        
        // Create new attendance record
        $record = [
            'appointment_id' => $appointmentId,
            'pet_name' => $appointment->pet->Pet_Name ?? 'N/A',
            'owner_name' => ($appointment->user->First_Name ?? '') . ' ' . ($appointment->user->Last_Name ?? ''),
            'owner_id' => $appointment->User_ID,
            'service' => $appointment->service->Service_Name ?? 'N/A',
            'scheduled_date' => $appointment->Date instanceof Carbon 
                ? $appointment->Date->format('Y-m-d') 
                : $appointment->Date,
            'scheduled_time' => $appointment->Time,
            'check_in_time' => now()->format('Y-m-d H:i:s'),
            'check_in_date' => now()->format('Y-m-d'),
            'scanned_by' => $scannedBy,
            'status' => 'checked_in',
            'already_checked_in' => false,
        ];
        
        // Add to logs (use appointment_id as key for easy lookup)
        $logs[$appointmentId] = $record;
        
        // Save logs
        self::saveAttendanceLogs($logs);
        
        // Update appointment status to "Completed" in database
        $appointment->Status = 'Completed';
        $appointment->save();
        
        return $record;
    }

    /**
     * Get attendance record for an appointment
     */
    public static function getAttendanceRecord($appointmentId)
    {
        $logs = self::loadAttendanceLogs();
        return $logs[$appointmentId] ?? null;
    }

    /**
     * Get all attendance records for a specific date
     */
    public static function getAttendanceByDate($date)
    {
        $logs = self::loadAttendanceLogs();
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        
        return array_filter($logs, function($record) use ($dateStr) {
            return ($record['check_in_date'] ?? '') === $dateStr;
        });
    }

    /**
     * Get attendance statistics
     */
    public static function getAttendanceStats($date = null)
    {
        $logs = self::loadAttendanceLogs();
        
        if ($date) {
            $logs = self::getAttendanceByDate($date);
        }
        
        return [
            'total_check_ins' => count($logs),
            'records' => array_values($logs),
        ];
    }
}