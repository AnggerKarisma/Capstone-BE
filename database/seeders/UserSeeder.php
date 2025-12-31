<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Insert user accounts - skip if exists
        $users = [
            [
                'name' => 'lisa',
                'email' => '11241041@student.itk.ac.id',
                'password' => Hash::make('password123'),
                'nomor_telepon' => '082211000615',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Test User',
                'email' => 'user@test.com',
                'password' => Hash::make('password123'),
                'nomor_telepon' => '081234567890',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            if (!DB::table('users')->where('email', $user['email'])->exists()) {
                DB::table('users')->insert($user);
            }
        }

        $this->command->info('✓ User accounts seeded successfully! Total users: ' . DB::table('users')->count());
    }
}
