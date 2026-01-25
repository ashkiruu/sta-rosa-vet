<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ClinicSchedule extends Model
{
    use HasFactory;

    protected $table = 'clinic_schedules';
    protected $primaryKey = 'Schedule_ID';

    protected $fillable = [
        'date',
        'type',
        'day_of_week',
        'notes',
        'modified_by',
    ];

    protected $casts = [
        'date' => 'date',
        'day_of_week' => 'integer',
    ];

    // Type constants
    const TYPE_DEFAULT_CLOSED = 'default_closed';
    const TYPE_OPENED = 'opened';
    const TYPE_CLOSED = 'closed';

    /**
     * Relationship: Modified by user
     */
    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'modified_by', 'User_ID');
    }

    /**
     * Scope: Default closed days
     */
    public function scopeDefaultClosed($query)
    {
        return $query->where('type', self::TYPE_DEFAULT_CLOSED);
    }

    /**
     * Scope: Specific date overrides
     */
    public function scopeDateOverrides($query)
    {
        return $query->whereNotNull('date');
    }

    /**
     * Scope: Opened dates
     */
    public function scopeOpened($query)
    {
        return $query->where('type', self::TYPE_OPENED);
    }

    /**
     * Scope: Closed dates
     */
    public function scopeClosed($query)
    {
        return $query->where('type', self::TYPE_CLOSED);
    }

    /**
     * Get default closed days of week (returns array of integers 0-6)
     */
    public static function getDefaultClosedDays(): array
    {
        return self::defaultClosed()
            ->whereNotNull('day_of_week')
            ->pluck('day_of_week')
            ->toArray();
    }

    /**
     * Get all opened date strings
     */
    public static function getOpenedDates(): array
    {
        return self::opened()
            ->whereNotNull('date')
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();
    }

    /**
     * Get all closed date strings
     */
    public static function getClosedDates(): array
    {
        return self::closed()
            ->whereNotNull('date')
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();
    }

    /**
     * Check if a specific date is closed
     */
    public static function isDateClosed($date): bool
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
        $dayOfWeek = Carbon::parse($dateStr)->dayOfWeek;

        // Check if specifically opened (override)
        if (self::where('date', $dateStr)->where('type', self::TYPE_OPENED)->exists()) {
            return false;
        }

        // Check if specifically closed (override)
        if (self::where('date', $dateStr)->where('type', self::TYPE_CLOSED)->exists()) {
            return true;
        }

        // Check default closed days
        return self::where('type', self::TYPE_DEFAULT_CLOSED)
            ->where('day_of_week', $dayOfWeek)
            ->exists();
    }

    /**
     * Open a specific date (remove from closed, add to opened if needed)
     */
    public static function openDate(string $date, ?int $modifiedBy = null): self
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        
        // Remove any existing entry for this date
        self::where('date', $dateStr)->delete();
        
        // Add as opened
        return self::create([
            'date' => $dateStr,
            'type' => self::TYPE_OPENED,
            'day_of_week' => null,
            'modified_by' => $modifiedBy,
            'notes' => 'Manually opened',
        ]);
    }

    /**
     * Close a specific date
     */
    public static function closeDate(string $date, ?int $modifiedBy = null): self
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        
        // Remove any existing entry for this date
        self::where('date', $dateStr)->delete();
        
        // Add as closed
        return self::create([
            'date' => $dateStr,
            'type' => self::TYPE_CLOSED,
            'day_of_week' => null,
            'modified_by' => $modifiedBy,
            'notes' => 'Manually closed',
        ]);
    }

    /**
     * Get schedule data in legacy format (for backward compatibility)
     */
    public static function getLegacyFormat(): array
    {
        return [
            'default_closed_days' => self::getDefaultClosedDays(),
            'opened_dates' => self::getOpenedDates(),
            'closed_dates' => self::getClosedDates(),
        ];
    }
}
