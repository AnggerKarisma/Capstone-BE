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
        'penanggung_jawab_id',
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
        'dokter_id',
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
        return $this->belongsTo(Poli::class, 'poli_id', 'poli_id');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id', 'dokter_id');
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(PenanggungJawab::class, 'penanggung_jawab_id', 'PjId');
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
