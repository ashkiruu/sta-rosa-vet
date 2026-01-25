<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $table = 'attendance_logs';
    protected $primaryKey = 'Attendance_ID';

    protected $fillable = [
        'Appointment_ID',
        'User_ID',
        'Pet_Name',
        'Owner_Name',
        'Service',
        'Scheduled_Date',
        'Scheduled_Time',
        'Check_In_Time',
        'Check_In_Date',
        'Scanned_By',
        'Status',
    ];

    protected $casts = [
        'Scheduled_Date' => 'date',
        'Check_In_Time' => 'datetime',
        'Check_In_Date' => 'date',
    ];

    // Status constants
    const STATUS_CHECKED_IN = 'checked_in';
    const STATUS_COMPLETED = 'completed';
    const STATUS_NO_SHOW = 'no_show';

    /**
     * Relationship: Appointment
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'Appointment_ID', 'Appointment_ID');
    }

    /**
     * Relationship: User (pet owner)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    /**
     * Scope: By date
     */
    public function scopeByDate($query, $date)
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        return $query->where('Check_In_Date', $dateStr);
    }

    /**
     * Scope: Today's attendance
     */
    public function scopeToday($query)
    {
        return $query->where('Check_In_Date', now()->format('Y-m-d'));
    }

    /**
     * Scope: Checked in
     */
    public function scopeCheckedIn($query)
    {
        return $query->where('Status', self::STATUS_CHECKED_IN);
    }

    /**
     * Check if appointment already has attendance record
     */
    public static function hasRecord(int $appointmentId): bool
    {
        return self::where('Appointment_ID', $appointmentId)->exists();
    }

    /**
     * Get attendance record for an appointment
     */
    public static function getByAppointment(int $appointmentId): ?self
    {
        return self::where('Appointment_ID', $appointmentId)->first();
    }

    /**
     * Get attendance by date (returns array format for backward compatibility)
     */
    public static function getByDateArray($date): array
    {
        $records = self::byDate($date)->orderBy('Check_In_Time', 'desc')->get();
        
        return $records->map(function ($record) {
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
                'already_checked_in' => false,
            ];
        })->toArray();
    }

    /**
     * Get all attendance logs as array (for backward compatibility)
     */
    public static function getAllArray(): array
    {
        $records = self::orderBy('Check_In_Time', 'desc')->get();
        
        $result = [];
        foreach ($records as $record) {
            $result[$record->Appointment_ID] = [
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
        
        return $result;
    }

    /**
     * Record attendance for an appointment
     */
    public static function recordForAppointment(Appointment $appointment, string $scannedBy = 'Receptionist'): array
    {
        // Check if already checked in
        $existing = self::getByAppointment($appointment->Appointment_ID);
        
        if ($existing) {
            return [
                'appointment_id' => $existing->Appointment_ID,
                'pet_name' => $existing->Pet_Name,
                'owner_name' => $existing->Owner_Name,
                'owner_id' => $existing->User_ID,
                'service' => $existing->Service,
                'scheduled_date' => $existing->Scheduled_Date->format('Y-m-d'),
                'scheduled_time' => $existing->Scheduled_Time,
                'check_in_time' => $existing->Check_In_Time->format('Y-m-d H:i:s'),
                'check_in_date' => $existing->Check_In_Date->format('Y-m-d'),
                'scanned_by' => $existing->Scanned_By,
                'status' => $existing->Status,
                'already_checked_in' => true,
            ];
        }

        // Load relationships if not loaded
        if (!$appointment->relationLoaded('pet')) {
            $appointment->load(['pet', 'service', 'user']);
        }

        // Create new record
        $record = self::create([
            'Appointment_ID' => $appointment->Appointment_ID,
            'User_ID' => $appointment->User_ID,
            'Pet_Name' => $appointment->pet->Pet_Name ?? 'N/A',
            'Owner_Name' => trim(($appointment->user->First_Name ?? '') . ' ' . ($appointment->user->Last_Name ?? '')),
            'Service' => $appointment->service->Service_Name ?? 'N/A',
            'Scheduled_Date' => $appointment->Date,
            'Scheduled_Time' => $appointment->Time,
            'Check_In_Time' => now(),
            'Check_In_Date' => now()->format('Y-m-d'),
            'Scanned_By' => $scannedBy,
            'Status' => self::STATUS_CHECKED_IN,
        ]);

        // Update appointment status
        $appointment->Status = 'Completed';
        $appointment->save();

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
            'already_checked_in' => false,
        ];
    }

    /**
     * Get attendance statistics
     */
    public static function getStats($date = null): array
    {
        $query = self::query();
        
        if ($date) {
            $query->byDate($date);
        }
        
        return [
            'total_check_ins' => $query->count(),
            'records' => $date ? self::getByDateArray($date) : array_values(self::getAllArray()),
        ];
    }
}