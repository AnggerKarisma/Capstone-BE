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
            $table->id('poliID');
            $table->string('nama');
            $table->string('tipeLayanan');
            $table->string('tipePoli');

            // Foreign key ke tabel admins (superadmin termasuk di sini)
            $table->unsignedBigInteger('superAdminID')->nullable();
            $table->foreign('superAdminID')
                  ->references('adminID')
                  ->on('admins')
                  ->onDelete('set null');

            $table->timestamps();
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