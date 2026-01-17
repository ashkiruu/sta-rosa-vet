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
     * This seeder creates the first super admin account.
     * Run with: php artisan db:seed --class=SuperAdminSeeder
     */
    public function run(): void
    {
        // Option 1: Create a brand new user as super admin
        $this->createNewSuperAdmin();
        
        // Option 2: If you want to promote an existing user, uncomment below:
        // $this->promoteExistingUser(1); // Replace 1 with the User_ID
    }

    /**
     * Create a new user and make them super admin
     */
    private function createNewSuperAdmin(): void
    {
        // Check if super admin email already exists
        $existingUser = User::where('Email', 'superadmin@starosa.vet')->first();
        
        if ($existingUser) {
            $this->command->info('Super admin user already exists. Checking admin status...');
            
            // Ensure they are a super admin
            $admin = Admin::find($existingUser->User_ID);
            if (!$admin) {
                Admin::create([
                    'User_ID' => $existingUser->User_ID,
                    'is_super_admin' => true,
                    'admin_role' => 'super_admin',
                    'created_by' => null,
                ]);
                $this->command->info('Existing user promoted to super admin.');
            } elseif (!$admin->is_super_admin) {
                $admin->update(['is_super_admin' => true, 'admin_role' => 'super_admin']);
                $this->command->info('Existing admin promoted to super admin.');
            } else {
                $this->command->info('User is already a super admin.');
            }
            return;
        }

        // Create new user
        $user = User::create([
            'Username' => 'superadmin',
            'Password' => Hash::make('SuperAdmin@123'), // CHANGE THIS IN PRODUCTION!
            'First_Name' => 'Super',
            'Middle_Name' => null,
            'Last_Name' => 'Admin',
            'Contact_Number' => '09000000000',
            'Email' => 'superadmin@starosa.vet',
            'Address' => 'Sta. Rosa Veterinary Clinic',
            'Barangay_ID' => 1, // Adjust based on your barangays table
            'Verification_Status_ID' => 2, // Verified
            'Account_Status_ID' => 1, // Active
            'Registration_Date' => now(),
        ]);

        // Create admin record with super admin privileges
        Admin::create([
            'User_ID' => $user->User_ID,
            'is_super_admin' => true,
            'admin_role' => 'super_admin',
            'created_by' => null, // System created
        ]);

        $this->command->info('========================================');
        $this->command->info('SUPER ADMIN ACCOUNT CREATED SUCCESSFULLY');
        $this->command->info('========================================');
        $this->command->info('Email: superadmin@starosa.vet');
        $this->command->info('Password: SuperAdmin@123');
        $this->command->warn('IMPORTANT: Change this password immediately after first login!');
        $this->command->info('========================================');
    }

    /**
     * Promote an existing user to super admin
     */
    private function promoteExistingUser(int $userId): void
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->command->error("User with ID {$userId} not found.");
            return;
        }

        $admin = Admin::find($userId);
        
        if ($admin) {
            // Update existing admin to super admin
            $admin->update([
                'is_super_admin' => true,
                'admin_role' => 'super_admin',
            ]);
            $this->command->info("User '{$user->First_Name} {$user->Last_Name}' has been promoted to super admin.");
        } else {
            // Create new admin record
            Admin::create([
                'User_ID' => $userId,
                'is_super_admin' => true,
                'admin_role' => 'super_admin',
                'created_by' => null,
            ]);
            $this->command->info("User '{$user->First_Name} {$user->Last_Name}' has been made a super admin.");
        }
    }
}