<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Report extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reports';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'Report_ID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Report_Number',
        'ReportType_ID',
        'Week_Number',
        'Year',
        'Start_Date',
        'End_Date',
        'Generated_By',
        'Generated_At',
        'File_Path',
        'Record_Count',
        'Summary',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'Start_Date' => 'date',
        'End_Date' => 'date',
        'Generated_At' => 'datetime',
        'Summary' => 'array',
        'Week_Number' => 'integer',
        'Year' => 'integer',
        'Record_Count' => 'integer',
    ];

    /**
     * Get the report type that owns this report.
     */
    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class, 'ReportType_ID', 'ReportType_ID');
    }

    /**
     * Generate a unique report number
     */
    public static function generateReportNumber(string $type = 'WEEKLY'): string
    {
        $year = date('Y');
        $week = date('W');
        $count = self::whereYear('created_at', $year)->count() + 1;
        
        return "RPT-{$type}-{$year}-W{$week}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope to filter by report type
     */
    public function scopeOfType($query, int $reportTypeId)
    {
        return $query->where('ReportType_ID', $reportTypeId);
    }

    /**
     * Scope to filter Anti-Rabies reports
     */
    public function scopeAntiRabies($query)
    {
        return $query->where('ReportType_ID', ReportType::ANTI_RABIES);
    }

    /**
     * Scope to filter Routine Services reports
     */
    public function scopeRoutineServices($query)
    {
        return $query->where('ReportType_ID', ReportType::ROUTINE_SERVICES);
    }

    /**
     * Scope to filter by week and year
     */
    public function scopeForWeek($query, int $weekNumber, int $year)
    {
        return $query->where('Week_Number', $weekNumber)->where('Year', $year);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where('Start_Date', '>=', $startDate)
                     ->where('End_Date', '<=', $endDate);
    }

    /**
     * Get formatted date range
     */
    public function getFormattedDateRangeAttribute(): string
    {
        return $this->Start_Date->format('M d') . ' - ' . $this->End_Date->format('M d, Y');
    }

    /**
     * Get formatted generation date
     */
    public function getFormattedGeneratedAtAttribute(): string
    {
        return $this->Generated_At->format('F d, Y h:i A');
    }

    /**
     * Check if this is an Anti-Rabies report
     */
    public function isAntiRabies(): bool
    {
        return $this->ReportType_ID === ReportType::ANTI_RABIES;
    }

    /**
     * Check if this is a Routine Services report
     */
    public function isRoutineServices(): bool
    {
        return $this->ReportType_ID === ReportType::ROUTINE_SERVICES;
    }

    /**
     * Get the full file path for the report
     */
    public function getFullFilePathAttribute(): ?string
    {
        if (empty($this->File_Path)) {
            return null;
        }
        
        return storage_path('app/public/' . $this->File_Path);
    }

    /**
     * Check if the report file exists
     */
    public function fileExists(): bool
    {
        $path = $this->full_file_path;
        return $path && file_exists($path);
    }

    /**
     * Delete the associated file when the report is deleted
     */
    protected static function booted()
    {
        static::deleting(function ($report) {
            if ($report->File_Path && file_exists($report->full_file_path)) {
                unlink($report->full_file_path);
            }
        });
    }
}