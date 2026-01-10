<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationStatus extends Model
{
    protected $table = 'verification_statuses';
    protected $primaryKey = 'Verification_Status_ID';

    protected $fillable = ['Status_Name'];

    public function users()
    {
        return $this->hasMany(User::class, 'Verification_Status_ID', 'Verification_Status_ID');
    }
}