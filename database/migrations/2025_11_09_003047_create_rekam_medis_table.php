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
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->id('rekam_medis_id'); 

            $table->foreignId('reservasi_id')->unique()->constrained(table: 'reservations', column: 'reservid')->onDelete('cascade');

            $table->string('no_medrec'); 
            $table->text('gejala')->nullable(); 
            $table->text('diagnosis')->nullable(); 
            $table->text('tindakan')->nullable();
            $table->dateTime('tanggal_diperiksa'); 

            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};
