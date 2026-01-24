<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends Model
{
    protected $table = 'system_logs';
    protected $primaryKey = 'Log_ID';

    protected $fillable = [
        'User_ID',
        'Action',
        'Timestamp',
        'Description',
    ];

    protected $casts = [
        'Timestamp' => 'datetime',
    ];

    /**
     * Get the user who performed this action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    /**
     * Scope to filter by action type
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('Action', 'like', "%{$action}%");
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('User_ID', $userId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('Timestamp', [$startDate, $endDate]);
    }

    /**
     * Scope to get only admin actions (excludes super admin)
     */
    public function scopeAdminActions($query)
    {
        $normalAdminIds = Admin::normalAdmins()->pluck('User_ID');
        return $query->whereIn('User_ID', $normalAdminIds);
    }

    /**
     * Scope to get recent logs
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('Timestamp', '>=', now()->subDays($days));
    }

    /**
     * Get formatted action for display
     */
    public function getActionDisplayAttribute(): string
    {
        return str_replace('_', ' ', ucwords($this->Action, '_'));
    }

    /**
     * Get time ago format
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->Timestamp->diffForHumans();
    }
}