<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
#Fixed the file name
class QRRelease extends Model
{
    use HasFactory;

    protected $table = 'qr_releases';
    protected $primaryKey = 'QRRelease_ID';

    protected $fillable = [
        'Appointment_ID',
        'User_ID',
        'Pet_Name',
        'Service',
        'Scheduled_Date',
        'Scheduled_Time',
        'Released',
        'Released_At',
        'Released_By',
        'QR_Path',
        'Seen',
        'Seen_At',
    ];

    protected $casts = [
        'Scheduled_Date' => 'date',
        'Released' => 'boolean',
        'Released_At' => 'datetime',
        'Seen' => 'boolean',
        'Seen_At' => 'datetime',
    ];

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
     * Scope: Released QR codes
     */
    public function scopeReleased($query)
    {
        return $query->where('Released', true);
    }

    /**
     * Scope: Unseen notifications
     */
    public function scopeUnseen($query)
    {
        return $query->where('Seen', false);
    }

    /**
     * Scope: For user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('User_ID', $userId);
    }

    /**
     * Check if QR is released for an appointment
     */
    public static function isReleased(int $appointmentId): bool
    {
        return self::where('Appointment_ID', $appointmentId)
            ->where('Released', true)
            ->exists();
    }

    /**
     * Get QR release record for an appointment
     */
    public static function getByAppointment(int $appointmentId): ?self
    {
        return self::where('Appointment_ID', $appointmentId)->first();
    }

    /**
     * Release QR code for an appointment
     */
    public static function releaseForAppointment(Appointment $appointment, string $releasedBy, string $qrPath): self
    {
        // Load relationships if not loaded
        if (!$appointment->relationLoaded('pet')) {
            $appointment->load(['pet', 'service', 'user']);
        }

        // Check if record exists
        $existing = self::getByAppointment($appointment->Appointment_ID);
        
        if ($existing) {
            $existing->update([
                'Released' => true,
                'Released_At' => now(),
                'Released_By' => $releasedBy,
                'QR_Path' => $qrPath,
                'Seen' => false, // Reset seen status when re-released
                'Seen_At' => null,
            ]);
            return $existing->fresh();
        }

        // Create new record
        return self::create([
            'Appointment_ID' => $appointment->Appointment_ID,
            'User_ID' => $appointment->User_ID,
            'Pet_Name' => $appointment->pet->Pet_Name ?? 'N/A',
            'Service' => $appointment->service->Service_Name ?? 'N/A',
            'Scheduled_Date' => $appointment->Date,
            'Scheduled_Time' => $appointment->Time,
            'Released' => true,
            'Released_At' => now(),
            'Released_By' => $releasedBy,
            'QR_Path' => $qrPath,
            'Seen' => false,
        ]);
    }

    /**
     * Mark QR notification as seen
     */
    public static function markSeen(int $appointmentId): bool
    {
        $record = self::getByAppointment($appointmentId);
        
        if ($record) {
            $record->update([
                'Seen' => true,
                'Seen_At' => now(),
            ]);
            return true;
        }
        
        return false;
    }

    /**
     * Get unseen QR notifications for a user
     */
    public static function getUnseenForUser(int $userId): array
    {
        return self::forUser($userId)
            ->released()
            ->unseen()
            ->get()
            ->map(function ($record) {
                return [
                    'appointment_id' => $record->Appointment_ID,
                    'user_id' => $record->User_ID,
                    'pet_name' => $record->Pet_Name,
                    'service' => $record->Service,
                    'scheduled_date' => $record->Scheduled_Date->format('Y-m-d'),
                    'scheduled_time' => $record->Scheduled_Time,
                    'released' => $record->Released,
                    'released_at' => $record->Released_At->format('Y-m-d H:i:s'),
                    'released_by' => $record->Released_By,
                    'qr_path' => $record->QR_Path,
                    'seen' => $record->Seen,
                ];
            })
            ->toArray();
    }

    /**
     * Get all QR notifications for a user
     */
    public static function getAllForUser(int $userId): array
    {
        return self::forUser($userId)
            ->released()
            ->get()
            ->map(function ($record) {
                return [
                    'appointment_id' => $record->Appointment_ID,
                    'user_id' => $record->User_ID,
                    'pet_name' => $record->Pet_Name,
                    'service' => $record->Service,
                    'scheduled_date' => $record->Scheduled_Date->format('Y-m-d'),
                    'scheduled_time' => $record->Scheduled_Time,
                    'released' => $record->Released,
                    'released_at' => $record->Released_At->format('Y-m-d H:i:s'),
                    'released_by' => $record->Released_By,
                    'qr_path' => $record->QR_Path,
                    'seen' => $record->Seen,
                ];
            })
            ->toArray();
    }

    /**
     * Convert to legacy array format
     */
    public function toLegacyArray(): array
    {
        return [
            'appointment_id' => $this->Appointment_ID,
            'user_id' => $this->User_ID,
            'pet_name' => $this->Pet_Name,
            'service' => $this->Service,
            'scheduled_date' => $this->Scheduled_Date->format('Y-m-d'),
            'scheduled_time' => $this->Scheduled_Time,
            'released' => $this->Released,
            'released_at' => $this->Released_At?->format('Y-m-d H:i:s'),
            'released_by' => $this->Released_By,
            'qr_path' => $this->QR_Path,
            'seen' => $this->Seen,
        ];
    }
}
