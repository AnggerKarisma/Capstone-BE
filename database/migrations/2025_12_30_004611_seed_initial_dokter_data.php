<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert dokter data with all required fields set to default values
        // tipe: 'U' = umum atau 'S' = spesialis
        DB::statement("
            INSERT INTO dokters (
                nama_dokter, bidang_keahlian, tipe, praktek, aktif, 
                jenis_kelamin, telpon_praktek, flags, no_ktp
            ) VALUES 
            ('Dr. Ahmad Santoso, Sp.PD', 'Penyakit Dalam', 'U', 'P-001', 1, 'L', '081234567801', 0, '-'),
            ('Dr. Siti Nurhaliza, Sp.PD', 'Penyakit Dalam', 'U', 'P-001', 1, 'P', '081234567802', 0, '-'),
            ('Dr. Budi Wijaya, Sp.A', 'Anak', 'S', 'P-002', 1, 'L', '081234567803', 0, '-'),
            ('Dr. Dewi Lestari, Sp.A', 'Anak', 'S', 'P-002', 1, 'P', '081234567804', 0, '-'),
            ('Dr. Rini Kusuma, Sp.KG', 'Gigi Dan Mulut', 'S', 'P-003', 1, 'P', '081234567805', 0, '-'),
            ('Dr. Andi Prasetyo, Sp.KG', 'Gigi Dan Mulut', 'S', 'P-003', 1, 'L', '081234567806', 0, '-'),
            ('Dr. Hendra Gunawan, Sp.JP', 'Jantung', 'S', 'P-004', 1, 'L', '081234567807', 0, '-'),
            ('Dr. Maya Sari, Sp.JP', 'Jantung', 'S', 'P-004', 1, 'P', '081234567808', 0, '-'),
            ('Dr. Teguh Suryanto, Sp.PD', 'Penyakit Dalam', 'S', 'P-005', 1, 'L', '081234567809', 0, '-'),
            ('Dr. Linda Anggraini, Sp.PD', 'Penyakit Dalam', 'S', 'P-005', 1, 'P', '081234567810', 0, '-'),
            ('Dr. Rizky Firmansyah, Sp.THT', 'THT', 'S', 'P-006', 1, 'L', '081234567811', 0, '-'),
            ('Dr. Wulan Dari, Sp.THT', 'THT', 'S', 'P-006', 1, 'P', '081234567812', 0, '-'),
            ('Dr. Bambang Setiawan, Sp.N', 'Neurologi', 'S', 'P-007', 1, 'L', '081234567813', 0, '-'),
            ('Dr. Ratna Puspita, Sp.N', 'Neurologi', 'S', 'P-007', 1, 'P', '081234567814', 0, '-'),
            ('Dr. Agus Kurniawan, Sp.OT', 'Bedah Tulang', 'S', 'P-008', 1, 'L', '081234567815', 0, '-'),
            ('Dr. Yuni Kartika, Sp.OT', 'Bedah Tulang', 'S', 'P-008', 1, 'P', '081234567816', 0, '-'),
            ('Dr. Fajar Maulana, Sp.An', 'Anestesi', 'S', 'P-009', 1, 'L', '081234567817', 0, '-'),
            ('Dr. Anisa Rahma, Sp.An', 'Anestesi', 'S', 'P-009', 1, 'P', '081234567818', 0, '-')
        ");
    }

    public function down(): void
    {
        DB::statement("DELETE FROM dokters WHERE no_ktp = '-'");
    }
};
