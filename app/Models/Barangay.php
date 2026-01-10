<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $table = 'barangays';
    protected $primaryKey = 'Barangay_ID'; // Matches your DB

    protected $fillable = [
        'Barangay_Name'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'Barangay_ID', 'Barangay_ID');
    }
}