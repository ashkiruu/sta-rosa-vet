<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    protected $table = 'pets';
    protected $primaryKey = 'Pet_ID';
    public $incrementing = true;

    protected $fillable = [
        'Owner_ID',
        'Species_ID',
        'Pet_Name',
        'Breed',
        'Sex',
        'Date_of_Birth',
        'Age',
        'Color',
        'Reproductive_Status',
        'Registration_Date',
    ];

    protected $casts = [
        'Date_of_Birth' => 'date',
        'Registration_Date' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'Owner_ID', 'User_ID');
    }

    public function species()
    {
        return $this->belongsTo(Species::class, 'Species_ID', 'Species_ID');
    }
}