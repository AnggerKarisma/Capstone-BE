<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antrian extends Model
{
    use HasFactory;

    protected $table = 'antrians';

    protected $fillable = [
        'reservation_id',
        'poli_id',
        'dokter_id',
        'nomor_antrian',
        'tanggal_antrian',
        'status',
        'waktu_panggil',
        'waktu_selesai',
        'admin_id',
    ];

    protected $casts = [
        'tanggal_antrian' => 'date',
        'waktu_panggil' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    /**
     * Relasi ke data reservasi (data pendaftaran pasien).
     */
    public function reservation()
    {
        // Pastikan 'reservid' adalah primary key di model Reservation Anda
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservid');
    }

    /**
     * Relasi ke poli.
     */
    public function poli()
    {
        // Pastikan 'poli_id' adalah primary key di model Poli Anda
        return $this->belongsTo(Poli::class, 'poli_id', 'poli_id');
    }

    /**
     * Relasi ke dokter.
     */
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id', 'dokter_id');
    }
}