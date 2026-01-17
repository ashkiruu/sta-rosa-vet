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
        'is_super_admin',
        'admin_role',
        'created_by',
    ];

    protected $casts = [
        'is_super_admin' => 'boolean',
    ];

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

    /**
     * Check if this admin is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Check if this admin is a normal staff/admin
     */
    public function isNormalAdmin(): bool
    {
        return !$this->is_super_admin;
    }

    /**
     * Get admin role display name
     */
    public function getRoleDisplayAttribute(): string
    {
        if ($this->is_super_admin) {
            return 'Super Admin';
        }
        
        return match($this->admin_role) {
            'admin' => 'Administrator',
            'staff' => 'Staff',
            default => ucfirst($this->admin_role ?? 'Staff')
        };
    }

    /**
     * Scope to get only super admins
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('is_super_admin', true);
    }

    /**
     * Scope to get only normal admins (non-super)
     */
    public function scopeNormalAdmins($query)
    {
        return $query->where('is_super_admin', false);
    }

    /**
     * Get all activity logs for this admin
     */
    public function activityLogs()
    {
        return $this->hasMany(SystemLog::class, 'User_ID', 'User_ID');
    }
}