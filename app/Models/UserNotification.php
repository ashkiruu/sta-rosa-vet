<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserNotification extends Model
{
    use HasFactory;

    protected $table = 'user_notifications';
    protected $primaryKey = 'Notification_ID';

    protected $fillable = [
        'User_ID',
        'Type',
        'Title',
        'Message',
        'Reference_ID',
        'Reference_Type',
        'Data',
        'Seen',
        'Seen_At',
        'Expires_At',
    ];

    protected $casts = [
        'Data' => 'array',
        'Seen' => 'boolean',
        'Seen_At' => 'datetime',
        'Expires_At' => 'datetime',
    ];

    // Notification type constants
    const TYPE_APPOINTMENT_APPROVED = 'appointment_approved';
    const TYPE_APPOINTMENT_DECLINED = 'appointment_declined';
    const TYPE_QR_READY = 'qr_ready';
    const TYPE_CERTIFICATE_READY = 'certificate_ready';
    const TYPE_GENERAL = 'general';

    // Default expiry days
    const DEFAULT_EXPIRY_DAYS = 7;

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    /**
     * Scope: For user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('User_ID', $userId);
    }

    /**
     * Scope: Unseen
     */
    public function scopeUnseen($query)
    {
        return $query->where('Seen', false);
    }

    /**
     * Scope: Not expired
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('Expires_At')
              ->orWhere('Expires_At', '>', now());
        });
    }

    /**
     * Scope: By type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('Type', $type);
    }

    /**
     * Scope: Recent (last N days)
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Create an appointment approved notification
     */
    public static function appointmentApproved(Appointment $appointment): self
    {
        if (!$appointment->relationLoaded('pet')) {
            $appointment->load(['pet', 'service']);
        }

        return self::create([
            'User_ID' => $appointment->User_ID,
            'Type' => self::TYPE_APPOINTMENT_APPROVED,
            'Title' => 'Appointment Approved! 🎉',
            'Message' => "Your appointment for {$appointment->pet->Pet_Name} on " . 
                Carbon::parse($appointment->Date)->format('M d, Y') . " at " . 
                Carbon::parse($appointment->Time)->format('h:i A') . 
                " has been approved! Please proceed to the clinic and check in at the reception desk.",
            'Reference_ID' => $appointment->Appointment_ID,
            'Reference_Type' => 'appointment',
            'Data' => [
                'pet_name' => $appointment->pet->Pet_Name ?? 'N/A',
                'service' => $appointment->service->Service_Name ?? 'N/A',
                'date' => Carbon::parse($appointment->Date)->format('Y-m-d'),
                'time' => $appointment->Time,
            ],
            'Expires_At' => now()->addDays(self::DEFAULT_EXPIRY_DAYS),
        ]);
    }

    /**
     * Create an appointment declined notification
     */
    public static function appointmentDeclined(
        int $userId, 
        string $petName, 
        string $date, 
        string $time, 
        string $service
    ): self {
        return self::create([
            'User_ID' => $userId,
            'Type' => self::TYPE_APPOINTMENT_DECLINED,
            'Title' => 'Appointment Declined',
            'Message' => "Your {$service} appointment for {$petName} on {$date} at {$time} has been declined.",
            'Reference_ID' => null,
            'Reference_Type' => 'appointment',
            'Data' => [
                'pet_name' => $petName,
                'service' => $service,
                'date' => $date,
                'time' => $time,
                'declined_at' => now()->toDateTimeString(),
            ],
            'Expires_At' => now()->addDays(self::DEFAULT_EXPIRY_DAYS),
        ]);
    }

    /**
     * Create a QR ready notification
     */
    public static function qrReady(Appointment $appointment): self
    {
        if (!$appointment->relationLoaded('pet')) {
            $appointment->load(['pet']);
        }

        return self::create([
            'User_ID' => $appointment->User_ID,
            'Type' => self::TYPE_QR_READY,
            'Title' => 'QR Code Ready! 📱',
            'Message' => "Your QR code for {$appointment->pet->Pet_Name}'s appointment is ready! Tap to view and scan.",
            'Reference_ID' => $appointment->Appointment_ID,
            'Reference_Type' => 'appointment',
            'Data' => [
                'pet_name' => $appointment->pet->Pet_Name ?? 'N/A',
                'appointment_id' => $appointment->Appointment_ID,
            ],
            'Expires_At' => now()->addDays(self::DEFAULT_EXPIRY_DAYS),
        ]);
    }

    /**
     * Mark notification as seen
     */
    public function markAsSeen(): self
    {
        $this->update([
            'Seen' => true,
            'Seen_At' => now(),
        ]);
        return $this;
    }

    /**
     * Mark all notifications as seen for a user
     */
    public static function markAllSeenForUser(int $userId): int
    {
        \Log::info("UserNotification::markAllSeenForUser called for user {$userId}");
        
        $count = self::where('User_ID', $userId)
            ->where('Seen', false)
            ->update([
                'Seen' => true,
                'Seen_At' => now(),
            ]);
        
        \Log::info("Updated {$count} notifications to seen for user {$userId}");
        
        return $count;
    }

    /**
     * Mark notification as seen by reference
     */
    public static function markSeenByReference(int $referenceId, string $referenceType, int $userId): bool
    {
        \Log::info("markSeenByReference called - User: {$userId}, Ref ID: {$referenceId}, Type: {$referenceType}");
        
        $count = self::where('User_ID', $userId)
            ->where('Reference_ID', $referenceId)
            ->where('Reference_Type', $referenceType)
            ->where('Seen', false)
            ->update([
                'Seen' => true,
                'Seen_At' => now(),
            ]);
        
        \Log::info("Marked {$count} notifications as seen");
        
        return $count > 0;
    }

    /**
     * Get unseen notifications for a user
     */
    public static function getUnseenForUser(int $userId): array
    {
        return self::forUser($userId)
            ->unseen()
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($n) => $n->toLegacyArray())
            ->toArray();
    }

    /**
     * Get all active notifications for a user
     */
    public static function getActiveForUser(int $userId): array
    {
        \Log::info("Getting active notifications for user {$userId}");
        
        $notifications = self::where('User_ID', $userId)
            ->where(function ($q) {
                $q->whereNull('Expires_At')
                  ->orWhere('Expires_At', '>', now());
            })
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->get();
        
        \Log::info("Found " . $notifications->count() . " notifications, " . 
                   $notifications->where('Seen', false)->count() . " unseen");
        
        return $notifications->map(fn($n) => $n->toLegacyArray())->toArray();
    }

    /**
     * Check if notification exists (to prevent duplicates)
     */
    public static function exists(int $userId, int $referenceId, string $type): bool
    {
        return self::forUser($userId)
            ->where('Reference_ID', $referenceId)
            ->where('Type', $type)
            ->exists();
    }

    /**
     * Convert to legacy array format for backward compatibility
     */
    public function toLegacyArray(): array
    {
        $data = $this->Data ?? [];
        
        $result = [
            'id' => $this->Reference_ID ?? $this->Notification_ID,
            'key' => $this->generateKey(),
            'type' => $this->mapTypeToLegacy(),
            'title' => $this->Title,
            'message' => $this->Message,
            'time' => $this->created_at->diffForHumans(),
            'seen' => $this->Seen,
        ];

        // Add type-specific fields
        if ($this->Type === self::TYPE_QR_READY) {
            $result['qr_link'] = route('appointments.qrcode', $this->Reference_ID);
            $result['show_qr_link'] = true;
        }

        if ($this->Type === self::TYPE_APPOINTMENT_APPROVED) {
            $result['show_qr_link'] = false;
        }

        return $result;
    }

    /**
     * Generate a unique key for this notification
     */
    public function generateKey(): string
    {
        if ($this->Type === self::TYPE_APPOINTMENT_DECLINED) {
            $data = $this->Data ?? [];
            return "declined_{$data['pet_name']}_{$data['date']}_{$data['declined_at']}";
        }

        if ($this->Type === self::TYPE_QR_READY) {
            return "qr_release_{$this->Reference_ID}_{$this->created_at->timestamp}";
        }

        return "{$this->Reference_ID}_{$this->Type}_{$this->created_at->timestamp}";
    }

    /**
     * Map type to legacy format
     */
    private function mapTypeToLegacy(): string
    {
        return match ($this->Type) {
            self::TYPE_APPOINTMENT_APPROVED => 'success',
            self::TYPE_APPOINTMENT_DECLINED => 'error',
            self::TYPE_QR_READY => 'qr_ready',
            self::TYPE_CERTIFICATE_READY => 'success',
            default => 'info',
        };
    }

    /**
     * Clean up expired notifications
     */
    public static function cleanupExpired(): int
    {
        return self::where('Expires_At', '<', now())->delete();
    }
}
