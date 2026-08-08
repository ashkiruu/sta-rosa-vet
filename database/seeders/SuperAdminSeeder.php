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
     * This seeder creates or ensures the existence of the super admin account.
     * Run with: php artisan db:seed --class=SuperAdminSeeder
     */
    public function run(): void
    {
        $this->createNewSuperAdmin();
        $this->syncPostgresSequence();
    }

    /**
     * Create or update user as super admin
     */
    private function createNewSuperAdmin(): void
    {
        $email = 'superadmin@starosa.vet';

        // Upsert superadmin user safely using Eloquent
        $user = User::updateOrCreate(
            ['Email' => $email],
            [
                'Username'               => 'superadmin',
                'Password'               => Hash::make('SuperAdmin@123'), // CHANGE THIS IN PRODUCTION!
                'First_Name'             => 'Super',
                'Middle_Name'            => null,
                'Last_Name'              => 'Admin',
                'Contact_Number'         => '09000000000',
                'Address'                => 'Sta. Rosa Veterinary Clinic',
                'Barangay_ID'            => 1,
                'Verification_Status_ID' => 2, // Verified
                'Account_Status_ID'      => 1, // Active
                'Registration_Date'      => now(),
            ]
        );

        // Upsert admin permissions for the user
        Admin::updateOrCreate(
            ['User_ID' => $user->User_ID],
            [
                'is_super_admin' => true,
                'admin_role'     => 'super_admin',
                'created_by'     => null,
            ]
        );

        if ($this->command) {
            $this->command->info('========================================');
            $this->command->info('SUPER ADMIN ACCOUNT READY');
            $this->command->info('========================================');
            $this->command->info('Email: ' . $email);
            $this->command->info('Password: SuperAdmin@123');
            $this->command->info('========================================');
        }
    }

    /**
     * Promote an existing user to super admin
     */
    public function promoteExistingUser(int $userId): void
    {
        $user = User::find($userId);

        if (!$user) {
            if ($this->command) {
                $this->command->error("User with ID {$userId} not found.");
            }
            return;
        }

        Admin::updateOrCreate(
            ['User_ID' => $user->User_ID],
            [
                'is_super_admin' => true,
                'admin_role'     => 'super_admin',
            ]
        );

        if ($this->command) {
            $this->command->info("User '{$user->First_Name} {$user->Last_Name}' has been promoted to super admin.");
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