<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reporttype extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'report_types';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ReportType_ID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Report_Name',
        'Description',
    ];

    /**
     * Report type constants
     */
    const ANTI_RABIES = 1;
    const ROUTINE_SERVICES = 2;

    /**
     * Get the reports for this report type.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'ReportType_ID', 'ReportType_ID');
    }

    /**
     * Get Anti-Rabies report type
     */
    public static function antiRabies(): ?self
    {
        return self::where('Report_Name', 'Anti-Rabies Vaccination Report')->first();
    }

    /**
     * Get Routine Services report type
     */
    public static function routineServices(): ?self
    {
        return self::where('Report_Name', 'Routine Services Report')->first();
    }

    /**
     * Scope to get by name
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('Report_Name', $name);
    }
}
//