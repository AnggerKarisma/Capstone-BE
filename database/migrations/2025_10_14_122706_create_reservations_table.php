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
            $table->string('nama');
            $table->string('email');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('nomor_whatsapp');
            $table->enum('penjaminan',['asuransi','cash']);
            $table->string('nomor_ktp', 16);
            $table->text('keluhan');
            $table->foreignId('poli_id')->nullable()->constrained(table:'polis',column:'poliID')->onDelete('set null');
            //$table->foreignId('jadwal_dokter_id')->constrained(table:'jadwal_dokters',column:'jadwaldokterid')->onDelete('set null');
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
