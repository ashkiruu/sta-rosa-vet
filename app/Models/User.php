<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Set custom primary key
    protected $primaryKey = 'User_ID';
    public $incrementing = true;

    // Allow mass assignment
    protected $fillable = [
        'Username',
        'Password',
        'First_Name',
        'Middle_Name',
        'Last_Name',
        'Contact_Number',
        'Email',
        'Address',
        'Barangay_ID',
        'Verification_Status_ID',
        'Account_Status_ID',
        'Registration_Date'
    ];

    // Hide sensitive data
    protected $hidden = [
        'Password',
        'remember_token',
    ];

    // Cast attributes
    protected function casts(): array
    {
        return [
            'Registration_Date' => 'datetime',
            'Password' => 'hashed',
        ];
    }

    // CRITICAL: Override Laravel's default column names
    public function getAuthPassword()
    {
        return $this->Password;
    }

    public function getEmailForPasswordReset()
    {
        return $this->Email;
    }

    // Tell Laravel which column is the email (for login)
    public function getAuthIdentifierName()
    {
        return 'User_ID';
    }

    public function getAuthIdentifier()
    {
        return $this->User_ID;
    }
}