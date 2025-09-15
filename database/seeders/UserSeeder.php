<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@alanwar.com',
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Admin Street',
            'city' => 'Admin City',
            'state' => 'Admin State',
            'postal_code' => '12345',
            'country' => 'United States',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        // Create sample customer users
        User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1 (555) 234-5678',
            'address' => '456 Customer Ave',
            'city' => 'Customer City',
            'state' => 'Customer State',
            'postal_code' => '67890',
            'country' => 'United States',
            'role' => 'user',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'phone' => '+1 (555) 345-6789',
            'address' => '789 User Blvd',
            'city' => 'User City',
            'state' => 'User State',
            'postal_code' => '11111',
            'country' => 'United States',
            'role' => 'user',
            'password' => Hash::make('password123'),
        ]);
    }
}
