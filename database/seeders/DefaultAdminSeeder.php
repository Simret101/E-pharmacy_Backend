<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if the admin already exists
        $admin = User::where('email', 'rahel.aberare@gmail.com')->first();

        if (!$admin) {
            // Create the default admin user
            User::create([
                'name' => 'Rahel Aberare',
                'email' => 'rahel.aberare@gmail.com',
                'password' => Hash::make('12345QAZ'),
                'is_role' => 0, // Assuming 0 is the role for admin
                'status' => 'pending', // Ensure this matches the ENUM values in your migration
                'email_verified_at' => now(), // Add email_verified_at field
            ]);

            $this->command->info('Default admin user created successfully.');
        } else {
            $this->command->info('Default admin user already exists.');
        }
    }
}