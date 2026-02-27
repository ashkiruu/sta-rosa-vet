<?php

namespace App\Services;

use App\Models\SystemLog;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class AdminLogService
{
    /**
     * Log an admin action to system_logs table
     * Only logs actions by staff and doctors (not admins)
     */
    public static function log(string $action, ?string $description = null, ?int $userId = null): ?SystemLog
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return null;
        }

        $admin = Admin::find($userId);
        
        // Only log if it's a staff or doctor (not admin role)
        if (!$admin || $admin->isAdmin()) {
            return null;
        }

        return SystemLog::create([
            'User_ID' => $userId,
            'Action' => $action,
            'Timestamp' => now(),
            'Description' => $description,
        ]);
    }

    /**
     * Force log an action (regardless of admin type)
     * Use this sparingly for critical actions
     */
    public static function forceLog(string $action, ?string $description = null, ?int $userId = null): ?SystemLog
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return null;
        }

        return SystemLog::create([
            'User_ID' => $userId,
            'Action' => $action,
            'Timestamp' => now(),
            'Description' => $description,
        ]);
    }

    /**
     * Log user verification action
     */
    public static function logUserVerification(int $targetUserId, string $status, string $userName): void
    {
        $action = $status === 'approved' ? 'USER_VERIFICATION_APPROVED' : 'USER_VERIFICATION_REJECTED';
        $description = "User '{$userName}' (ID: {$targetUserId}) was {$status}";
        
        self::log($action, $description);
    }

    /**
     * Log appointment action
     */
    public static function logAppointmentAction(int $appointmentId, string $action, string $petName, string $ownerName): void
    {
        $actionType = strtoupper("APPOINTMENT_{$action}");
        $description = "Appointment #{$appointmentId} for pet '{$petName}' (Owner: {$ownerName}) was {$action}";
        
        self::log($actionType, $description);
    }

    /**
     * Log certificate action
     */
    public static function logCertificateAction(string $certificateId, string $action, string $petName): void
    {
        $actionType = strtoupper("CERTIFICATE_{$action}");
        $description = "Certificate '{$certificateId}' for pet '{$petName}' was {$action}";
        
        self::log($actionType, $description);
    }

    /**
     * Log report generation
     */
    public static function logReportGeneration(string $reportType, int $weekNumber, int $year): void
    {
        $action = 'REPORT_GENERATED';
        $description = "{$reportType} report for Week {$weekNumber}, {$year} was generated";
        
        self::log($action, $description);
    }

    /**
     * Log schedule change
     */
    public static function logScheduleChange(string $date, string $status): void
    {
        $action = 'SCHEDULE_MODIFIED';
        $description = "Clinic schedule for {$date} was set to {$status}";
        
        self::log($action, $description);
    }

    /**
     * Log admin management action (force log since this is done by admin role)
     */
    public static function logAdminManagement(int $targetUserId, string $action, string $userName): void
    {
        $actionType = strtoupper("ADMIN_{$action}");
        $description = "Admin account for '{$userName}' (ID: {$targetUserId}) was {$action}";
        
        self::forceLog($actionType, $description);
    }

    /**
     * Get logs for a specific admin
     */
    public static function getLogsForAdmin(int $userId, int $limit = 50)
    {
        return SystemLog::where('User_ID', $userId)
            ->orderBy('Timestamp', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all staff/doctor logs (excluding admin role actions)
     */
    public static function getAllAdminLogs(int $limit = 100)
    {
        $monitoredIds = Admin::nonAdmins()->pluck('User_ID');
        
        return SystemLog::whereIn('User_ID', $monitoredIds)
            ->with('user')
            ->orderBy('Timestamp', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs filtered by action type
     */
    public static function getLogsByAction(string $action, int $limit = 50)
    {
        $monitoredIds = Admin::nonAdmins()->pluck('User_ID');
        
        return SystemLog::whereIn('User_ID', $monitoredIds)
            ->where('Action', 'like', "%{$action}%")
            ->with('user')
            ->orderBy('Timestamp', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs for date range
     */
    public static function getLogsForDateRange($startDate, $endDate)
    {
        $monitoredIds = Admin::nonAdmins()->pluck('User_ID');
        
        return SystemLog::whereIn('User_ID', $monitoredIds)
            ->whereBetween('Timestamp', [$startDate, $endDate])
            ->with('user')
            ->orderBy('Timestamp', 'desc')
            ->get();
    }

    /**
     * Get activity summary for dashboard
     */
    public static function getActivitySummary(): array
    {
        $monitoredIds = Admin::nonAdmins()->pluck('User_ID');
        
        $todayCount = SystemLog::whereIn('User_ID', $monitoredIds)
            ->whereDate('Timestamp', today())
            ->count();
        
        $weekCount = SystemLog::whereIn('User_ID', $monitoredIds)
            ->where('Timestamp', '>=', now()->subDays(7))
            ->count();
        
        $recentLogs = SystemLog::whereIn('User_ID', $monitoredIds)
            ->with('user')
            ->orderBy('Timestamp', 'desc')
            ->limit(5)
            ->get();

        return [
            'today_count' => $todayCount,
            'week_count' => $weekCount,
            'recent_logs' => $recentLogs,
        ];
    }
}