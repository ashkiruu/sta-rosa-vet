<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Facades\Storage;

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
     * @return array Attendance record
     */
    public static function recordAttendance(Appointment $appointment, $scannedBy = 'Receptionist')
    {
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
            'scheduled_date' => $appointment->Date instanceof \Carbon\Carbon 
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
        $dateStr = $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : $date;
        
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