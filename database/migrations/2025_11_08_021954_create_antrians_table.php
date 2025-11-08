<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antrians', function (Blueprint $table) {
            $table->id();
            
            // Kunci penghubung utama
            $table->foreignId('reservation_id')->constrained(table:'reservations', column:'reservid')->onDelete('cascade');

            // Data duplikat untuk query cepat (tidak perlu join)
            $table->foreignId('poli_id')->constrained(table:'polis', column:'poli_id')->onDelete('cascade');
            $table->foreignId('dokter_id')->constrained(table:'dokters', column:'dokter_id')->onDelete('cascade');
            $table->string('nomor_antrian');
            $table->date('tanggal_antrian');

            // Ini adalah STATUS LIVE untuk hari-H
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'dilewati'])->default('menunggu');
            
            // Data operasional
            $table->timestamp('waktu_panggil')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained(table:'admins', column:'adminID')->onDelete('set null'); // Admin/Loket yang memanggil

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antrians');
    }
};