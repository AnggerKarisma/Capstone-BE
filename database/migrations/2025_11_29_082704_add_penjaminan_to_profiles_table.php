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
        Schema::table('profiles', function (Blueprint $table) {
            $table->enum('penjaminan', ['asuransi', 'cash'])->default('cash')->after('nomor_pegawai')->nullable();
            $table->string('nama_asuransi', 100)->nullable()->after('penjaminan');
            $table->string('nomor_asuransi', 50)->nullable()->after('nama_asuransi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['penjaminan', 'nama_asuransi', 'nomor_asuransi']);
        });
    }
};
