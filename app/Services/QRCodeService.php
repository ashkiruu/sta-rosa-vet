<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AttendanceLog;
use App\Models\QRRelease;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
            Log::error("Failed to generate QR code for appointment {$appointment->Appointment_ID}");
            return null;
        }

        file_put_contents($fullPath, $imageContent);
        
        Log::info("QR code generated for appointment {$appointment->Appointment_ID}: {$filename}");
        
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
     * QR CODE RELEASE SYSTEM (Database-backed)
     * =====================================================
     */

    /**
     * Check if QR code has been released for an appointment
     */
    public static function isQRCodeReleased(Appointment $appointment)
    {
        return QRRelease::isReleased($appointment->Appointment_ID);
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

        // Generate the QR code
        $qrPath = self::generateForAppointment($appointment);
        
        if (!$qrPath) {
            return [
                'success' => false,
                'message' => 'Failed to generate QR code.',
            ];
        }
        
        // Create/update the release record in database
        $release = QRRelease::releaseForAppointment($appointment, $releasedBy, $qrPath);
        
        // Create notification for the user (if not already exists)
        if (!UserNotification::exists($appointment->User_ID, $appointment->Appointment_ID, UserNotification::TYPE_QR_READY)) {
            UserNotification::qrReady($appointment);
        }
        
        Log::info("QR code released for appointment {$appointment->Appointment_ID} by {$releasedBy}");
        
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
        $release = QRRelease::getByAppointment($appointmentId);
        return $release ? $release->toLegacyArray() : null;
    }

    /**
     * Get all unreleased QR notifications for a user (for display in their notifications)
     */
    public static function getUnseenQRNotificationsForUser($userId)
    {
        return QRRelease::getUnseenForUser($userId);
    }

    /**
     * Get all QR notifications for a user
     */
    public static function getQRNotificationsForUser($userId)
    {
        return QRRelease::getAllForUser($userId);
    }

    /**
     * Mark QR notification as seen
     */
    public static function markQRNotificationSeen($appointmentId)
    {
        return QRRelease::markSeen($appointmentId);
    }

    /**
     * =====================================================
     * ATTENDANCE LOGGING SYSTEM (Database-backed)
     * =====================================================
     */

    /**
     * Load all attendance logs (returns array for backward compatibility)
     */
    public static function loadAttendanceLogs()
    {
        return AttendanceLog::getAllArray();
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
        
        // Load relationships if not loaded
        if (!$appointment->relationLoaded('pet')) {
            $appointment->load(['pet', 'service', 'user']);
        }
        
        // Record attendance using the model
        return AttendanceLog::recordForAppointment($appointment, $scannedBy);
    }

    /**
     * Get attendance record for an appointment
     */
    public static function getAttendanceRecord($appointmentId)
    {
        $record = AttendanceLog::getByAppointment($appointmentId);
        
        if (!$record) {
            return null;
        }
        
        return [
            'appointment_id' => $record->Appointment_ID,
            'pet_name' => $record->Pet_Name,
            'owner_name' => $record->Owner_Name,
            'owner_id' => $record->User_ID,
            'service' => $record->Service,
            'scheduled_date' => $record->Scheduled_Date->format('Y-m-d'),
            'scheduled_time' => $record->Scheduled_Time,
            'check_in_time' => $record->Check_In_Time->format('Y-m-d H:i:s'),
            'check_in_date' => $record->Check_In_Date->format('Y-m-d'),
            'scanned_by' => $record->Scanned_By,
            'status' => $record->Status,
        ];
    }

    /**
     * Get all attendance records for a specific date
     */
    public static function getAttendanceByDate($date)
    {
        return AttendanceLog::getByDateArray($date);
    }

    /**
     * Get attendance statistics
     */
    public static function getAttendanceStats($date = null)
    {
        return AttendanceLog::getStats($date);
    }

    /**
     * =====================================================
     * LEGACY COMPATIBILITY METHODS
     * These methods maintain backward compatibility with
     * any code that might still reference the old JSON methods
     * =====================================================
     */

    /**
     * @deprecated Use database methods instead
     */
    public static function loadQRReleaseNotifications()
    {
        $releases = QRRelease::all();
        $result = [];
        
        foreach ($releases as $release) {
            $result[$release->Appointment_ID] = $release->toLegacyArray();
        }
        
        return $result;
    }
}
