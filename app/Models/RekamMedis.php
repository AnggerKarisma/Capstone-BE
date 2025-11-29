<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reservation;

class RekamMedis extends Model
{
    use HasFactory;

    protected $table = 'rekam_medis';
    protected $primaryKey = 'rekam_medis_id';

    protected $fillable = [
        'reservasi_id',
        'no_medrec',
        'gejala',
        'diagnosis',
        'tindakan',
        'resep_obat',
        'tanggal_diperiksa',
    ];

    protected $casts = [
        'tanggal_diperiksa' => 'datetime',
    ];

    public function reservasi()
    {
        return $this->belongsTo(Reservation::class, 'reservasi_id', 'reservid');
    }
}
