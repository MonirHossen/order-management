<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567890',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create Vendor users
        $vendor1 = User::create([
            'name' => 'Vendor One',
            'email' => 'vendor1@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567891',
            'is_active' => true,
        ]);
        $vendor1->assignRole('vendor');

        $vendor2 = User::create([
            'name' => 'Vendor Two',
            'email' => 'vendor2@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567892',
            'is_active' => true,
        ]);
        $vendor2->assignRole('vendor');

        // Create Customer users
        $customer1 = User::create([
            'name' => 'John Doe',
            'email' => 'customer1@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567893',
            'is_active' => true,
        ]);
        $customer1->assignRole('customer');

        $customer2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'customer2@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567894',
            'is_active' => true,
        ]);
        $customer2->assignRole('customer');

        // Create inactive user for testing
        $inactive = User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567895',
            'is_active' => false,
        ]);
        $inactive->assignRole('customer');
    }
}