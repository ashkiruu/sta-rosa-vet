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
        'Date' => 'date',
    ];

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