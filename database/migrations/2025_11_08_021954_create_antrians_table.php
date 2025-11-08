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
            
            $table->foreignId('reservation_id')->constrained(table:'reservations', column:'reservid')->onDelete('cascade');
            $table->string('poli_id',10)->nullable();
            $table->foreign('poli_id')->references('poli_id')->on('polis')->onDelete('cascade');
            $table->foreignId('dokter_id')->constrained(table:'dokters', column:'dokter_id')->onDelete('cascade');
            $table->string('nomor_antrian');
            $table->date('tanggal_antrian');
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'dilewati'])->default('menunggu');
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