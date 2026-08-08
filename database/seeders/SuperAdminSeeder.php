<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates default administrative accounts (Admin, Doctor, Staff).
     * Run with: php artisan db:seed --class=AdminSeeder
     */
    public function run(): void
    {
        $this->seedAdminAccounts();
        $this->syncPostgresSequence();
    }

    /**
     * Seed default administrative users and assign roles.
     */
    private function seedAdminAccounts(): void
    {
        $accounts = [
            [
                'email'       => 'admin@starosa.vet',
                'username'    => 'admin',
                'password'    => 'Admin@123',
                'first_name'  => 'System',
                'last_name'   => 'Administrator',
                'admin_role'  => 'admin',
            ],
            [
                'email'       => 'doctor@starosa.vet',
                'username'    => 'doctor',
                'password'    => 'Doctor@123',
                'first_name'  => 'Duty',
                'last_name'   => 'Veterinarian',
                'admin_role'  => 'doctor',
            ],
            [
                'email'       => 'staff@starosa.vet',
                'username'    => 'staff',
                'password'    => 'Staff@123',
                'first_name'  => 'Clinic',
                'last_name'   => 'Staff',
                'admin_role'  => 'staff',
            ],
        ];

        foreach ($accounts as $acc) {
            // Upsert User record using Eloquent
            $user = User::updateOrCreate(
                ['Email' => $acc['email']],
                [
                    'Username'               => $acc['username'],
                    'Password'               => Hash::make($acc['password']),
                    'First_Name'             => $acc['first_name'],
                    'Middle_Name'            => null,
                    'Last_Name'              => $acc['last_name'],
                    'Contact_Number'         => '09000000000',
                    'Address'                => 'Sta. Rosa Veterinary Clinic',
                    'Barangay_ID'            => 1,
                    'Verification_Status_ID' => 2, // Verified
                    'Account_Status_ID'      => 1, // Active
                    'Registration_Date'      => now(),
                ]
            );

            // Upsert Admin role permissions record
            Admin::updateOrCreate(
                ['User_ID' => $user->User_ID],
                [
                    'admin_role' => $acc['admin_role'],
                    'created_by' => null,
                ]
            );
        }

        if ($this->command) {
            $this->command->info('========================================');
            $this->command->info('ADMINISTRATIVE ACCOUNTS SEEDED');
            $this->command->info('========================================');
            $this->command->info('1. Admin:  admin@starosa.vet / Admin@123');
            $this->command->info('2. Doctor: doctor@starosa.vet / Doctor@123');
            $this->command->info('3. Staff:  staff@starosa.vet / Staff@123');
            $this->command->info('========================================');
        }
    }

    /**
     * Reset PostgreSQL sequence to prevent duplicate key errors on future inserts
     */
    private function syncPostgresSequence(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('users', 'User_ID'), COALESCE(MAX(\"User_ID\"), 1)) FROM users;");
        }
    }
}