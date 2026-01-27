<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';
    protected $primaryKey = 'Appointment_ID';
    public $incrementing = true;

    protected $fillable = [
        'User_ID', 'Pet_ID', 'Service_ID', 'Date', 'Time', 'Location', 'Status', 'Special_Notes'
    ];

    protected $casts = [
        // Use immutable date to prevent timezone shifting
        // This treats the date as a pure date without time component
        'Date' => 'date:Y-m-d',
    ];

    /**
     * Get the Date attribute without timezone conversion
     * This ensures the date stored is the date displayed
     */
    public function getDateAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        
        // Parse the date without timezone conversion
        // This returns a Carbon instance set to midnight in the app timezone
        return \Carbon\Carbon::createFromFormat('Y-m-d', date('Y-m-d', strtotime($value)))->startOfDay();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'Pet_ID', 'Pet_ID');
    }

    public function service()
    {
        return $this->belongsTo(ServiceType::class, 'Service_ID', 'Service_ID');
    }
}