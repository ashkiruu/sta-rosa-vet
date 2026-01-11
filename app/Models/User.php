<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'User_ID'; // Crucial for your schema
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
    public function getAuthPassword() { return $this->Password; }
    public function getAuthIdentifierName() { return 'User_ID'; }

    public function admin()
    {
        // Primary key is User_ID, Foreign key in admin table is also User_ID
        return $this->hasOne(Admin::class, 'User_ID', 'User_ID');
    }

    public function isAdmin(): bool
    {
        // This looks for the current user's ID in the admin table
        return \DB::table('admins')->where('User_ID', $this->User_ID)->exists();
    }
    public function ocrData()
    {
        // A user has one entry in the ml_ocr_processing table
        return $this->hasOne(MlOcrProcessing::class, 'User_ID', 'User_ID');
    }
}