<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Insert admin accounts - skip if exists
        // Note: admins table uses capitalized column names (Nama, Email, Password)
        // Role values: 'superadmin' or 'admin'
        $admins = [
            [
                'Nama' => 'Admin Niko',
                'Email' => 'adminniko@gmail.com',
                'Password' => Hash::make('adminniko'),
                'role' => 'admin',
                'poli_id' => 'P-001', // Poli Umum
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Nama' => 'Super Admin',
                'Email' => 'superadmin@gmail.com',
                'Password' => Hash::make('superadmin'),
                'role' => 'superadmin',
                'poli_id' => null, // Super admin tidak terikat poli
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($admins as $admin) {
            if (!DB::table('admins')->where('Email', $admin['Email'])->exists()) {
                DB::table('admins')->insert($admin);
            }
        }

        $this->command->info('✓ Admin accounts seeded successfully! Total admins: ' . DB::table('admins')->count());
    }
}
