<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create a super admin user
        User::firstOrCreate(
            ['email' => 'superadmin@barangay.local'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('superadmin123'), // Change this password!
                'role' => 'super_admin',
            ]
        );

        // Create an admin user
        User::firstOrCreate(
            ['email' => 'admin@barangay.local'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'), // Change this password!
                'role' => 'admin',
            ]
        );

        // Create a test resident user
        User::firstOrCreate(
            ['email' => 'resident@barangay.local'],
            [
                'name' => 'Test Resident',
                'password' => Hash::make('resident123'),
                'role' => 'resident',
            ]
        );
    }
}
