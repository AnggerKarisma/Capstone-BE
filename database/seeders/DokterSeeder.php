<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DokterSeeder extends Seeder
{
    public function run(): void
    {
        // JANGAN TRUNCATE - update data yang ada saja
        // Karena tabel dokters punya banyak required fields yang kompleks
        
        $this->command->info('Dokter seeder skipped - using existing data');
        $this->command->info('Total dokters in database: ' . DB::table('dokters')->count());
    }
}
