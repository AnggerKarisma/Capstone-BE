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
            $table->foreignId('user_id')->constrained(table:'users',column:'userid')->onDelete('cascade');
            $table->string('nama');
            $table->string('email');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('nomor_whatsapp');
            $table->enum('penjaminan',['asuransi','cash']);
            $table->string('nomor_ktp', 16)->unique();
            $table->string('keluhan');
            $table->timestamps();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
