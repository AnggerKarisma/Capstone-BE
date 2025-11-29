<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id('reservid');
            $table->foreignId('booked_user_id')->constrained(table:'users',column:'userid')->onDelete('cascade');
            $table->foreignId('verif_adminID')->nullable()->constrained(table:'admins',column:'adminID')->onDelete('set null');
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained(table:'penanggung_jawabs',column:'PjId')->onDelete('set null');

            $table->string('nama');
            $table->string('email');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('status_keluarga')->nullable();
            $table->string('nama_keluarga')->nullable();
            $table->string('status_perkawinan')->nullable();
            $table->string('suku')->nullable();
            $table->string('agama')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('alamat')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kota/kabupaten')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('nomor_pegawai')->nullable();
            $table->string('nomor_whatsapp');
            $table->enum('penjaminan',['asuransi','cash']);
            $table->string('nama_asuransi')->nullable();
            $table->string('nomor_asuransi')->nullable();
            $table->string('nomor_ktp', 16);

            $table->text('keluhan');

            $table->string('rekomendasi_ai')->nullable(); 
            $table->boolean('sesuai_ai')->default(0)->nullable();

            $table->string('poli_id',10)->nullable();
            $table->foreign('poli_id')->references('poli_id')->on('polis')->onDelete('set null');

            $table->unsignedBigInteger('dokter_id')->nullable(); 
            $table->foreign('dokter_id')->references('dokter_id')->on('dokters')->onDelete('set null');

            $table->string('nomor_antrian')->nullable();
            $table->date('tanggal_reservasi')->nullable();
            $table->enum('status',['pending','confirmed','cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
