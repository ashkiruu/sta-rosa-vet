<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    // Create ALL verification statuses
    DB::table('verification_statuses')->insertOrIgnore([
        [
            'Verification_Status_ID' => 1,
            'Verification_Status_Name' => 'Pending',
            'Description' => 'Awaiting OCR or Admin review',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'Verification_Status_ID' => 2,
            'Verification_Status_Name' => 'Verified',
            'Description' => 'ID matched successfully via OCR',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'Verification_Status_ID' => 3,
            'Verification_Status_Name' => 'Not Verified',
            'Description' => 'No ID uploaded or identification rejected',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);

    // Create account status
    DB::table('account_statuses')->insertOrIgnore([
        'Account_Status_ID' => 1,
        'Account_Status_Name' => 'Active',
        'Description' => 'Account is active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Create test user (Juan Dela Cruz)
    DB::table('users')->insertOrIgnore([
        'User_ID' => 1,
        'Username' => 'testuser',
        'Password' => Hash::make('password'),
        'First_Name' => 'Juan',
        'Middle_Name' => 'Dela',
        'Last_Name' => 'Cruz',
        'Contact_Number' => '09123456789',
        'Email' => 'test@example.com',
        'Address' => 'Test Address, Sta. Rosa City',
        'Barangay_ID' => 1,
        'Verification_Status_ID' => 1,
        'Account_Status_ID' => 1,
        'Registration_Date' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
}