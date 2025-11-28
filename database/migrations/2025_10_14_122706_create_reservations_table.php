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
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('nomor_whatsapp');
            $table->enum('penjaminan',['asuransi','cash']);
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
