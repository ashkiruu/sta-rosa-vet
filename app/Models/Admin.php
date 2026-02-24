<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'User_ID';
    public $incrementing = false;

    protected $fillable = [
        'User_ID',
        'admin_role',
        'created_by',
    ];

    // =====================================================
    // ROLE CONSTANTS
    // =====================================================

    public const ROLE_STAFF = 'staff';
    public const ROLE_DOCTOR = 'doctor';
    public const ROLE_ADMIN = 'admin';

    public const ALL_ROLES = [
        self::ROLE_STAFF,
        self::ROLE_DOCTOR,
        self::ROLE_ADMIN,
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the user that this admin record belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    /**
     * Get the admin who created this admin
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'User_ID');
    }

    // =====================================================
    // ROLE CHECKS
    // =====================================================

    /**
     * Check if this admin is a Staff member
     * Access: Verification, Reports, Attendance
     */
    public function isStaff(): bool
    {
        return $this->admin_role === self::ROLE_STAFF;
    }

    /**
     * Check if this admin is a Doctor / Veterinary Doctor
     * Access: Appointments, Certificates, Reports, Attendance
     */
    public function isDoctor(): bool
    {
        return $this->admin_role === self::ROLE_DOCTOR;
    }

    /**
     * Check if this admin is an Admin (replaces old super admin)
     * Access: System logs, Role management
     */
    public function isAdmin(): bool
    {
        return $this->admin_role === self::ROLE_ADMIN;
    }

    // =====================================================
    // PERMISSION CHECKS
    // =====================================================

    /**
     * Can this admin access verification features?
     */
    public function canAccessVerification(): bool
    {
        return $this->isStaff();
    }

    /**
     * Can this admin access appointment management?
     */
    public function canAccessAppointments(): bool
    {
        return $this->isDoctor();
    }

    /**
     * Can this admin access certificate management?
     */
    public function canAccessCertificates(): bool
    {
        return $this->isDoctor();
    }

    /**
     * Can this admin access reports?
     */
    public function canAccessReports(): bool
    {
        return $this->isStaff() || $this->isDoctor();
    }

    /**
     * Can this admin access attendance logs?
     */
    public function canAccessAttendance(): bool
    {
        return $this->isStaff() || $this->isDoctor();
    }

    /**
     * Can this admin manage other admin accounts?
     */
    public function canManageAdmins(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Can this admin view activity/system logs?
     */
    public function canAccessLogs(): bool
    {
        return $this->isAdmin();
    }

    // =====================================================
    // DISPLAY HELPERS
    // =====================================================

    /**
     * Get admin role display name
     */
    public function getRoleDisplayAttribute(): string
    {
        return match ($this->admin_role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_DOCTOR => 'Veterinary Doctor',
            self::ROLE_STAFF => 'Staff',
            default => ucfirst($this->admin_role ?? 'Staff'),
        };
    }

    /**
     * Get short role label for badges
     */
    public function getRoleBadgeAttribute(): string
    {
        return match ($this->admin_role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_DOCTOR => 'Doctor',
            self::ROLE_STAFF => 'Staff',
            default => ucfirst($this->admin_role ?? 'Staff'),
        };
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope to get only staff members
     */
    public function scopeStaff($query)
    {
        return $query->where('admin_role', self::ROLE_STAFF);
    }

    /**
     * Scope to get only doctors
     */
    public function scopeDoctors($query)
    {
        return $query->where('admin_role', self::ROLE_DOCTOR);
    }

    /**
     * Scope to get only admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('admin_role', self::ROLE_ADMIN);
    }

    /**
     * Scope to get non-admin roles (staff + doctors) — for activity log monitoring
     */
    public function scopeNonAdmins($query)
    {
        return $query->whereIn('admin_role', [self::ROLE_STAFF, self::ROLE_DOCTOR]);
    }

    /**
     * Legacy alias — used in activity logs controller
     * Returns staff + doctors (the roles whose activity admin monitors)
     */
    public function scopeNormalAdmins($query)
    {
        return $this->scopeNonAdmins($query);
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get all activity logs for this admin
     */
    public function activityLogs()
    {
        return $this->hasMany(SystemLog::class, 'User_ID', 'User_ID');
    }
}