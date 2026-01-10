<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $table = 'service_types';
    protected $primaryKey = 'Service_ID';
    public $incrementing = true;

    protected $fillable = [
        'Service_Name',
        'Description',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'Service_ID', 'Service_ID');
    }
}