<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('polis', function (Blueprint $table) {
            $table->string('poli_id', 10)->primary(); 
            $table->string('poli_name', 50); 
            $table->date('update_date')->nullable(); 
            $table->string('update_by', 20)->nullable();
            $table->char('tipe_layanan', 1)->nullable(); 
            $table->string('tipe_poli', 5)->nullable(); 
            $table->string('kode_lokasi', 10)->nullable(); 
            $table->string('kode_urutan', 10)->nullable(); 
            $table->string('create_mr', 10)->nullable(); 
            $table->string('as_gudang', 10)->nullable(); 
            $table->string('kode_bagian_1', 10)->nullable(); 
            $table->string('create_miv', 10)->nullable(); 
            $table->string('kode_bag_rs', 10)->nullable(); 
            $table->string('rekening_p_5', 15)->nullable(); 
            $table->string('rekening_r_5', 15)->nullable(); 
            $table->string('rekening_b_5', 15)->nullable(); 
            $table->string('ppk', 5)->nullable(); 
            $table->string('petty_cash', 5)->nullable(); 
            $table->string('hbi', 10)->nullable(); 
            $table->string('kode_apotik', 20)->nullable(); 
            $table->string('kode_konsul', 20)->nullable(); 
            $table->string('aktif', 5)->nullable(); 
            $table->string('id_mysap', 15)->nullable(); 
            $table->string('rawat_jalan', 5)->nullable(); 
            $table->string('desk_lama', 60)->nullable(); 
            $table->string('teknik', 10)->nullable(); 
            $table->string('pertg_jwbn', 10)->nullable(); 
            $table->string('kode_korporat', 10)->nullable(); 
            $table->string('kode_rs', 10)->nullable(); 
            $table->string('kepala', 60)->nullable(); 
            $table->string('wadir', 60)->nullable(); 
            $table->string('proses_stock', 1)->nullable(); 
            $table->string('poli_id_inht', 3)->nullable(); 
            $table->string('poli_pdk', 100)->nullable(); 
            $table->string('poli_id_bpjs', 3)->nullable(); 
            $table->string('kode_printer', 4)->nullable(); 
            $table->string('kode_rujuk_bpjs', 2)->nullable(); 
            $table->string('poli_id_main', 10)->nullable(); 
            $table->string('d_satu_sehat', 255)->nullable(); 
            $table->string('panjar_kerja', 2)->nullable(); 
            $table->char('view_mjkn', 1)->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polis');
    }
};
