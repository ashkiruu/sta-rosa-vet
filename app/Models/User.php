<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'User_ID';
    public $incrementing = true;
    
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

    protected $hidden = ['Password', 'remember_token'];

    // Auth Overrides for your custom columns
    public function getAuthPassword() 
    { 
        return $this->Password; 
    }
    
    // This tells Laravel which column is the PRIMARY KEY (for session storage)
    public function getAuthIdentifierName() 
    { 
        return 'User_ID'; 
    }
    
    // This returns the actual ID value
    public function getAuthIdentifier() 
    { 
        return $this->User_ID; 
    }

    // Relationships
    public function barangay() 
    {
        return $this->belongsTo(Barangay::class, 'Barangay_ID', 'Barangay_ID');
    }
}