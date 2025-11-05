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
            $table->id('dokterId');
            $table->string('nama');
            $table->string('spesialis');
            $table->boolean('aktif')->default(true);
            $table->enum('jenisKelamin', ['Laki-laki', 'Perempuan']);
            $table->string('SIP')->unique();
            $table->date('SIPdate');
            $table->timestamps();
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
