<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalDokterSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('jadwal_dokter')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil semua dokter
        $dokters = DB::table('dokters')->get();
        
        $jadwals = [];
        
        foreach ($dokters as $dokter) {
            // Buat jadwal untuk setiap dokter
            // Senin - Jumat praktek pagi & siang
            $jadwals[] = [
                'dokter_id' => $dokter->dokter_id,
                'poli_id' => $dokter->praktek,
                'gedung' => 'A',
                'pelayanan_cash' => 1,
                'pelayanan_bpjs' => 1,
                'pelayanan_asuransi' => 1,
                'pelayanan_kapitasi' => 0,
                'pelayanan_pertamina' => 0,
                'pelayanan_inhealth' => 0,
                
                // SENIN
                'senin_pagi_dari' => '08:00:00',
                'senin_pagi_sampai' => '12:00:00',
                'senin_pagi_kuota' => 20,
                'senin_siang_dari' => '13:00:00',
                'senin_siang_sampai' => '16:00:00',
                'senin_siang_kuota' => 15,
                'senin_sore_dari' => null,
                'senin_sore_sampai' => null,
                'senin_sore_kuota' => 0,
                'senin_praktek' => 1,
                'senin_keterangan' => 'Praktek normal',
                
                // SELASA
                'selasa_pagi_dari' => '08:00:00',
                'selasa_pagi_sampai' => '12:00:00',
                'selasa_pagi_kuota' => 20,
                'selasa_siang_dari' => '13:00:00',
                'selasa_siang_sampai' => '16:00:00',
                'selasa_siang_kuota' => 15,
                'selasa_sore_dari' => null,
                'selasa_sore_sampai' => null,
                'selasa_sore_kuota' => 0,
                'selasa_praktek' => 1,
                'selasa_keterangan' => 'Praktek normal',
                
                // RABU
                'rabu_pagi_dari' => '08:00:00',
                'rabu_pagi_sampai' => '12:00:00',
                'rabu_pagi_kuota' => 20,
                'rabu_siang_dari' => '13:00:00',
                'rabu_siang_sampai' => '16:00:00',
                'rabu_siang_kuota' => 15,
                'rabu_sore_dari' => null,
                'rabu_sore_sampai' => null,
                'rabu_sore_kuota' => 0,
                'rabu_praktek' => 1,
                'rabu_keterangan' => 'Praktek normal',
                
                // KAMIS
                'kamis_pagi_dari' => '08:00:00',
                'kamis_pagi_sampai' => '12:00:00',
                'kamis_pagi_kuota' => 20,
                'kamis_siang_dari' => '13:00:00',
                'kamis_siang_sampai' => '16:00:00',
                'kamis_siang_kuota' => 15,
                'kamis_sore_dari' => null,
                'kamis_sore_sampai' => null,
                'kamis_sore_kuota' => 0,
                'kamis_praktek' => 1,
                'kamis_keterangan' => 'Praktek normal',
                
                // JUMAT
                'jumat_pagi_dari' => '08:00:00',
                'jumat_pagi_sampai' => '11:00:00',
                'jumat_pagi_kuota' => 15,
                'jumat_siang_dari' => '13:00:00',
                'jumat_siang_sampai' => '16:00:00',
                'jumat_siang_kuota' => 15,
                'jumat_sore_dari' => null,
                'jumat_sore_sampai' => null,
                'jumat_sore_kuota' => 0,
                'jumat_praktek' => 1,
                'jumat_keterangan' => 'Praktek normal',
                
                // SABTU (beberapa dokter praktek)
                'sabtu_pagi_dari' => '08:00:00',
                'sabtu_pagi_sampai' => '12:00:00',
                'sabtu_pagi_kuota' => 15,
                'sabtu_siang_dari' => null,
                'sabtu_siang_sampai' => null,
                'sabtu_siang_kuota' => 0,
                'sabtu_sore_dari' => null,
                'sabtu_sore_sampai' => null,
                'sabtu_sore_kuota' => 0,
                'sabtu_praktek' => 1,
                'sabtu_keterangan' => 'Praktek pagi saja',
                
                // MINGGU (libur)
                'minggu_pagi_dari' => null,
                'minggu_pagi_sampai' => null,
                'minggu_pagi_kuota' => 0,
                'minggu_siang_dari' => null,
                'minggu_siang_sampai' => null,
                'minggu_siang_kuota' => 0,
                'minggu_sore_dari' => null,
                'minggu_sore_sampai' => null,
                'minggu_sore_kuota' => 0,
                'minggu_praktek' => 0,
                'minggu_keterangan' => 'Libur',
            ];
        }

        DB::table('jadwal_dokter')->insert($jadwals);
        
        $this->command->info('✓ Jadwal Dokter data seeded successfully! Total: ' . count($jadwals) . ' schedules');
    }
}
