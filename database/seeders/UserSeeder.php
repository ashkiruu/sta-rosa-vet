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
        // Note: Run BarangaySeeder first to populate all 18 barangays
        // This user will be assigned to Barangay Aplaya (ID: 1)

        // Create verification status if it doesn't exist
        DB::table('verification_statuses')->insertOrIgnore([
            'Verification_Status_ID' => 1,
            'Verification_Status_Name' => 'Verified',
            'Description' => 'User is verified',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create account status if it doesn't exist
        DB::table('account_statuses')->insertOrIgnore([
            'Account_Status_ID' => 1,
            'Account_Status_Name' => 'Active',
            'Description' => 'Account is active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create test user
        DB::table('users')->insert([
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

        echo "Test user created successfully!\n";
        echo "Email: test@example.com\n";
        echo "Password: password\n";
    }
}