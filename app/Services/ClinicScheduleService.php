<?php

namespace App\Services;

use App\Models\ClinicSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClinicScheduleService
{
    /**
     * Default closed days (Sunday=0, Saturday=6)
     */
    private const DEFAULT_CLOSED_DAYS = [0, 6];

    /**
     * Get the full schedule in legacy format
     * This maintains backward compatibility with existing code
     */
    public static function getSchedule(): array
    {
        return ClinicSchedule::getLegacyFormat();
    }

    /**
     * Check if a specific date is closed
     */
    public static function isDateClosed($date): bool
    {
        return ClinicSchedule::isDateClosed($date);
    }

    /**
     * Open a specific date
     */
    public static function openDate(string $date, ?int $modifiedBy = null): void
    {
        ClinicSchedule::openDate($date, $modifiedBy);
        
        $dayName = Carbon::parse($date)->format('l, M d, Y');
        Log::info("Clinic schedule: {$dayName} set to OPEN" . ($modifiedBy ? " by user {$modifiedBy}" : ''));
    }

    /**
     * Close a specific date
     */
    public static function closeDate(string $date, ?int $modifiedBy = null): void
    {
        ClinicSchedule::closeDate($date, $modifiedBy);
        
        $dayName = Carbon::parse($date)->format('l, M d, Y');
        Log::info("Clinic schedule: {$dayName} set to CLOSED" . ($modifiedBy ? " by user {$modifiedBy}" : ''));
    }

    /**
     * Toggle date status (open/close)
     */
    public static function toggleDateStatus(string $date, string $action, ?int $modifiedBy = null): array
    {
        $isOpening = $action === 'open';
        
        if ($isOpening) {
            self::openDate($date, $modifiedBy);
        } else {
            self::closeDate($date, $modifiedBy);
        }

        $dayName = Carbon::parse($date)->format('l, M d, Y');
        $status = $isOpening ? 'OPEN' : 'CLOSED';

        return [
            'success' => true,
            'message' => "Clinic is now {$status} on {$dayName}",
            'date' => $date,
            'status' => $status,
        ];
    }

    /**
     * Get default closed days
     */
    public static function getDefaultClosedDays(): array
    {
        $days = ClinicSchedule::getDefaultClosedDays();
        return !empty($days) ? $days : self::DEFAULT_CLOSED_DAYS;
    }

    /**
     * Get opened dates
     */
    public static function getOpenedDates(): array
    {
        return ClinicSchedule::getOpenedDates();
    }

    /**
     * Get closed dates
     */
    public static function getClosedDates(): array
    {
        return ClinicSchedule::getClosedDates();
    }

    /**
     * Add a default closed day of week
     */
    public static function addDefaultClosedDay(int $dayOfWeek, ?int $modifiedBy = null): void
    {
        // Check if already exists
        if (ClinicSchedule::where('type', ClinicSchedule::TYPE_DEFAULT_CLOSED)
            ->where('day_of_week', $dayOfWeek)
            ->exists()) {
            return;
        }

        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        ClinicSchedule::create([
            'date' => null,
            'type' => ClinicSchedule::TYPE_DEFAULT_CLOSED,
            'day_of_week' => $dayOfWeek,
            'notes' => "{$dayNames[$dayOfWeek]} - Default closed",
            'modified_by' => $modifiedBy,
        ]);
    }

    /**
     * Remove a default closed day of week
     */
    public static function removeDefaultClosedDay(int $dayOfWeek): void
    {
        ClinicSchedule::where('type', ClinicSchedule::TYPE_DEFAULT_CLOSED)
            ->where('day_of_week', $dayOfWeek)
            ->delete();
    }

    /**
     * Reset a specific date (remove any overrides)
     */
    public static function resetDate(string $date): void
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        ClinicSchedule::where('date', $dateStr)->delete();
    }

    /**
     * Get schedule entries for a date range (for calendar display)
     */
    public static function getScheduleForRange(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $schedule = [];
        $defaultClosedDays = self::getDefaultClosedDays();
        $openedDates = self::getOpenedDates();
        $closedDates = self::getClosedDates();
        
        $current = $start->copy();
        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $dayOfWeek = $current->dayOfWeek;
            
            $isClosed = false;
            $reason = null;
            
            // Check specific overrides first
            if (in_array($dateStr, $openedDates)) {
                $isClosed = false;
                $reason = 'manually_opened';
            } elseif (in_array($dateStr, $closedDates)) {
                $isClosed = true;
                $reason = 'manually_closed';
            } elseif (in_array($dayOfWeek, $defaultClosedDays)) {
                $isClosed = true;
                $reason = 'default_closed';
            }
            
            $schedule[$dateStr] = [
                'date' => $dateStr,
                'day_of_week' => $dayOfWeek,
                'is_closed' => $isClosed,
                'reason' => $reason,
            ];
            
            $current->addDay();
        }
        
        return $schedule;
    }

    /**
     * Migrate data from JSON file to database (one-time migration)
     */
    public static function migrateFromJson(): array
    {
        $jsonPath = storage_path('app/clinic_schedule.json');
        
        if (!file_exists($jsonPath)) {
            return [
                'success' => false,
                'message' => 'No JSON file found to migrate',
            ];
        }
        
        $data = json_decode(file_get_contents($jsonPath), true);
        
        if (!$data) {
            return [
                'success' => false,
                'message' => 'Failed to parse JSON file',
            ];
        }
        
        $migrated = [
            'default_closed_days' => 0,
            'opened_dates' => 0,
            'closed_dates' => 0,
        ];
        
        // Migrate default closed days
        foreach ($data['default_closed_days'] ?? [] as $dayOfWeek) {
            if (!ClinicSchedule::where('type', ClinicSchedule::TYPE_DEFAULT_CLOSED)
                ->where('day_of_week', $dayOfWeek)
                ->exists()) {
                self::addDefaultClosedDay($dayOfWeek);
                $migrated['default_closed_days']++;
            }
        }
        
        // Migrate opened dates
        foreach ($data['opened_dates'] ?? [] as $date) {
            if (!ClinicSchedule::where('date', $date)->exists()) {
                ClinicSchedule::create([
                    'date' => $date,
                    'type' => ClinicSchedule::TYPE_OPENED,
                    'notes' => 'Migrated from JSON',
                ]);
                $migrated['opened_dates']++;
            }
        }
        
        // Migrate closed dates
        foreach ($data['closed_dates'] ?? [] as $date) {
            if (!ClinicSchedule::where('date', $date)->exists()) {
                ClinicSchedule::create([
                    'date' => $date,
                    'type' => ClinicSchedule::TYPE_CLOSED,
                    'notes' => 'Migrated from JSON',
                ]);
                $migrated['closed_dates']++;
            }
        }
        
        // Rename the old JSON file
        rename($jsonPath, $jsonPath . '.migrated.' . date('Y-m-d-His'));
        
        Log::info('Clinic schedule migrated from JSON to database', $migrated);
        
        return [
            'success' => true,
            'message' => 'Migration completed successfully',
            'migrated' => $migrated,
        ];
    }
}
