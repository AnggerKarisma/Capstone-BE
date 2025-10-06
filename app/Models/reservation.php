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
        'user_id',
        'nama',
        'email',
        'tempat_lahir',
        'tanggal_lahir',
        'nomor_whatsapp',
        'penjaminan',
        'nomor_ktp',
        'keluhan',
    ];
    
    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'userid');
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
