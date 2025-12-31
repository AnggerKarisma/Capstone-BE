<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PoliSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama dengan disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('polis')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $polis = [
            ['poli_id' => 'P-001', 'poli_name' => 'Poli Umum'],
            ['poli_id' => 'P-002', 'poli_name' => 'Poli Anak'],
            ['poli_id' => 'P-003', 'poli_name' => 'Poli Gigi Dan Mulut'],
            ['poli_id' => 'P-004', 'poli_name' => 'Poli Jantung'],
            ['poli_id' => 'P-005', 'poli_name' => 'Poli Penyakit Dalam'],
            ['poli_id' => 'P-006', 'poli_name' => 'Poli THT'],
            ['poli_id' => 'P-007', 'poli_name' => 'Poli Neurologi'],
            ['poli_id' => 'P-008', 'poli_name' => 'Poli Bedah Tulang'],
            ['poli_id' => 'P-009', 'poli_name' => 'Poli Anestesi'],
        ];

        DB::table('polis')->insert($polis);
        
        $this->command->info('✓ Poli data seeded successfully!');
    }
}
