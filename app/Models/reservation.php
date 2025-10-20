<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class reservation extends Model
{
    use hasFactory;

    protected $table = 'reservations';
    protected $primaryKey = 'reservid';

    protected $fillable = [
        'booked_user_id',
        'verif_adminID',
        'nama',
        'email',
        'tempat_lahir',
        'tanggal_lahir',
        'nomor_whatsapp',
        'nomor_ktp',
        'penjaminan',
        'keluhan',
        'status',
        'poli_id',
        'jadwal_dokter_id',
        'nomor_antrian',
        'tanggal_reservasi',
    ];
    
    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_reservasi' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'booked_user_id', 'userid');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'verif_adminID', 'adminID');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'poli_id', 'poliID');
    }

    public function jadwalDokter()
    {
        return $this->belongsTo(JadwalDokter::class, 'jadwal_dokter_id', 'jadwaldokterid');
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->translatedFormat('l, d F Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->translatedFormat('l, d F Y H:i:s');
    }
}
