<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'User_ID';
    protected $table = 'users';
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

    public function getAuthIdentifierName()
    {
        return 'User_ID';
    }

    /**
 * Check if user is verified
 */
public function isVerified(): bool
{
    return $this->Verification_Status_ID == 2; // 2 = Verified
}

/**
 * Get verification status name
 */
public function getVerificationStatusName(): string
{
    return match($this->Verification_Status_ID) {
        1 => 'Pending Verification',
        2 => 'Verified',
        3 => 'Not Verified',
        default => 'Unknown'
    };
}

/**
 * Check if verification is pending
 */
public function isVerificationPending(): bool
{
    return $this->Verification_Status_ID == 1;
}

/**
 * Check if verification was rejected
 */
public function isVerificationRejected(): bool
{
    return $this->Verification_Status_ID == 3;
}

    /**
     * Relationship with Pets
     */
    public function pets()
    {
        return $this->hasMany(Pet::class, 'Owner_ID', 'User_ID');
    }

    /**
     * Relationship with Barangay
     */
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'Barangay_ID', 'Barangay_ID');
    }

    /**
     * Relationship with Appointments
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'User_ID', 'User_ID');
    }

    /**
     * Relationship with Admin record
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'User_ID', 'User_ID');
    }

    /**
     * Check if user is an admin (any type)
     */
    public function isAdmin(): bool
    {
        return Admin::where('User_ID', $this->User_ID)->exists();
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        $admin = Admin::find($this->User_ID);
        return $admin && $admin->is_super_admin === true;
    }

    /**
     * Check if user is a normal admin (not super admin)
     */
    public function isNormalAdmin(): bool
    {
        $admin = Admin::find($this->User_ID);
        return $admin && $admin->is_super_admin === false;
    }

    /**
     * Get admin role display name
     */
    public function getAdminRoleDisplay(): string
    {
        $admin = Admin::find($this->User_ID);
        
        if (!$admin) {
            return 'User';
        }

        if ($admin->is_super_admin) {
            return 'Super Admin';
        }

        return match($admin->admin_role) {
            'admin' => 'Administrator',
            'staff' => 'Staff',
            default => ucfirst($admin->admin_role ?? 'Staff')
        };
    }

    /**
     * Relationship with OCR Data
     */
    public function ocrData()
    {
        return $this->hasOne(MlOcrProcessing::class, 'User_ID', 'User_ID');
    }

    /**
     * Relationship with System Logs (activity logs)
     */
    public function activityLogs()
    {
        return $this->hasMany(SystemLog::class, 'User_ID', 'User_ID');
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->First_Name;
        if ($this->Middle_Name) {
            $name .= ' ' . $this->Middle_Name;
        }
        $name .= ' ' . $this->Last_Name;
        return $name;
    }
}