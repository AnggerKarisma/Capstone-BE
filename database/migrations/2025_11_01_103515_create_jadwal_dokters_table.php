<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jadwal_dokter', function (Blueprint $table) {
            $table->unsignedBigInteger('dokter_id');
            $table->unsignedBigInteger('poliID');

            // informasi Pelayanan
            $table->string('gedung', 1)->nullable();
            $table->string('pelayanan_cash', 1)->default('N');
            $table->string('pelayanan_bpjs', 1)->default('N');
            $table->string('pelayanan_asuransi', 1)->default('N');
            $table->string('pelayanan_kapitasi', 1)->nullable();
            $table->string('pelayanan_pertamina', 1)->nullable();
            $table->string('pelayanan_inhealth', 1)->nullable();

            // --- SENIN ---
            $table->time('senin_pagi_dari')->nullable();
            $table->time('senin_pagi_sampai')->nullable();
            $table->integer('senin_pagi_kuota')->default(0);
            $table->time('senin_siang_dari')->nullable();
            $table->time('senin_siang_sampai')->nullable();
            $table->integer('senin_siang_kuota')->default(0);
            $table->time('senin_sore_dari')->nullable();
            $table->time('senin_sore_sampai')->nullable();
            $table->integer('senin_sore_kuota')->default(0);
            $table->string('senin_praktek', 1)->default('N'); // 'Y' or 'N'
            $table->string('senin_keterangan', 1000)->nullable();

            // --- SELASA ---
            $table->time('selasa_pagi_dari')->nullable();
            $table->time('selasa_pagi_sampai')->nullable();
            $table->integer('selasa_pagi_kuota')->default(0);
            $table->time('selasa_siang_dari')->nullable();
            $table->time('selasa_siang_sampai')->nullable();
            $table->integer('selasa_siang_kuota')->default(0);
            $table->time('selasa_sore_dari')->nullable();
            $table->time('selasa_sore_sampai')->nullable();
            $table->integer('selasa_sore_kuota')->default(0);
            $table->string('selasa_praktek', 1)->default('N'); // 'Y' or 'N'
            $table->string('selasa_keterangan', 1000)->nullable();

            // --- RABU ---
            $table->time('rabu_pagi_dari')->nullable();
            $table->time('rabu_pagi_sampai')->nullable();
            $table->integer('rabu_pagi_kuota')->default(0);
            $table->time('rabu_siang_dari')->nullable();
            $table->time('rabu_siang_sampai')->nullable();
            $table->integer('rabu_siang_kuota')->default(0);
            $table->time('rabu_sore_dari')->nullable();
            $table->time('rabu_sore_sampai')->nullable();
            $table->integer('rabu_sore_kuota')->default(0);
            $table->string('rabu_praktek', 1)->default('N'); // 'Y' or 'N'
            $table->string('rabu_keterangan', 1000)->nullable();

            // --- KAMIS ---
            $table->time('kamis_pagi_dari')->nullable();
            $table->time('kamis_pagi_sampai')->nullable();
            $table->integer('kamis_pagi_kuota')->default(0);
            $table->time('kamis_siang_dari')->nullable();
            $table->time('kamis_siang_sampai')->nullable();
            $table->integer('kamis_siang_kuota')->default(0);
            $table->time('kamis_sore_dari')->nullable();
            $table->time('kamis_sore_sampai')->nullable();
            $table->integer('kamis_sore_kuota')->default(0);
            $table->string('kamis_praktek', 1)->default('N'); // 'Y' or 'N'
            $table->string('kamis_keterangan', 1000)->nullable();

            // --- JUMAT ---
            $table->time('jumat_pagi_dari')->nullable();
            $table->time('jumat_pagi_sampai')->nullable();
            $table->integer('jumat_pagi_kuota')->default(0);
            $table->time('jumat_siang_dari')->nullable();
            $table->time('jumat_siang_sampai')->nullable();
            $table->integer('jumat_siang_kuota')->default(0);
            $table->time('jumat_sore_dari')->nullable();
            $table->time('jumat_sore_sampai')->nullable();
            $table->integer('jumat_sore_kuota')->default(0);
            $table->string('jumat_praktek', 1)->default('N'); // 'Y' or 'N'
            $table->string('jumat_keterangan', 1000)->nullable();

            // --- SABTU ---
            $table->time('sabtu_pagi_dari')->nullable();
            $table->time('sabtu_pagi_sampai')->nullable();
            $table->integer('sabtu_pagi_kuota')->default(0);
            $table->time('sabtu_siang_dari')->nullable();
            $table->time('sabtu_siang_sampai')->nullable();
            $table->integer('sabtu_siang_kuota')->default(0);
            $table->time('sabtu_sore_dari')->nullable();
            $table->time('sabtu_sore_sampai')->nullable();
            $table->integer('sabtu_sore_kuota')->default(0);
            $table->string('sabtu_praktek', 1)->default('N'); // 'Y' or 'N'
            $table->string('sabtu_keterangan', 1000)->nullable();

            // --- MINGGU ---
            $table->time('minggu_pagi_dari')->nullable();
            $table->time('minggu_pagi_sampai')->nullable();
            $table->integer('minggu_pagi_kuota')->default(0);
            $table->time('minggu_siang_dari')->nullable();
            $table->time('minggu_siang_sampai')->nullable();
            $table->integer('minggu_siang_kuota')->default(0);
            $table->time('minggu_sore_dari')->nullable();
            $table->time('minggu_sore_sampai')->nullable();
            $table->integer('minggu_sore_kuota')->default(0);
            $table->string('minggu_praktek', 1)->default('N'); // 'Y' or 'N'
            $table->string('minggu_keterangan', 1000)->nullable();

            $table->timestamp('last_update')->nullable();
            $table->string('last_update_by', 500)->nullable();
            $table->char('update_bpjs', 1)->nullable();

            $table->primary(['dokter_id', 'poliID']);
            $table->foreign('dokter_id')->references('dokter_id')->on('dokters')->onDelete('cascade');
            $table->foreign('poliID')->references('poliID')->on('polis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jadwal_dokter');
    }
};