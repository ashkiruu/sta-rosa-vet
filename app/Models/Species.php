<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Species extends Model
{
    use HasFactory;

    protected $table = 'species';
    protected $primaryKey = 'Species_ID';
    public $incrementing = true;

    protected $fillable = [
        'Species_Name',
        'Description',
    ];

    public function pets()
    {
        return $this->hasMany(Pet::class, 'Species_ID', 'Species_ID');
    }
}