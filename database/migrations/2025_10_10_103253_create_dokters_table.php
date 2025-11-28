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
        Schema::create('dokters', function (Blueprint $table) {
            $table->id('dokter_id');
            $table->string('nama_dokter', 100); 
            $table->string('bidang_keahlian', 100); 
            $table->char('tipe', 1); // char(1), No Null
            $table->string('praktek', 100)->nullable(); 
            $table->date('last_update')->nullable(); 
            $table->string('last_update_by', 20)->nullable(); 
            $table->string('telpon_praktek', 50)->nullable(); 
            $table->string('alamat_rumah', 100)->nullable(); 
            $table->string('telp_rumah', 50)->nullable(); 
            $table->char('aktif', 1); 
            $table->string('flags', 10); 
            $table->string('nama_dokter_asli', 100)->nullable(); 
            $table->string('konsulen', 1)->nullable(); 
            $table->date('start_date')->nullable(); 
            $table->date('expire_date')->nullable(); 
            $table->string('id_dokter_inht', 5)->nullable(); 
            $table->string('type_dok', 15)->nullable(); 
            $table->string('sip_dokter', 50)->nullable(); 
            $table->timestamp('tmt_sip')->nullable(); 
            $table->string('str_perawat', 50)->nullable(); 
            $table->timestamp('tmt_str')->nullable(); 
            $table->string('id_dokter_bpjs', 20)->nullable(); 
            $table->integer('ttd_id')->nullable(); 
            $table->string('sts_peg', 20)->nullable(); 
            $table->string('no_ktp', 16); 
            $table->string('id_satu_sehat', 255)->nullable(); 
            $table->char('jenis_kelamin', 1)->nullable(); 
            $table->string('ksm_role', 20)->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokters');
    }
};
