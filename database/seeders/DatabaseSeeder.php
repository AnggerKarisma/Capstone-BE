<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan seeder untuk semua data master dan akun
        $this->call([
            PoliSeeder::class,
            DokterSeeder::class,
            JadwalDokterSeeder::class,
            UserSeeder::class,
            AdminSeeder::class,
        ]);

        $this->command->info('✓ All seeders completed successfully!');
    }
}
