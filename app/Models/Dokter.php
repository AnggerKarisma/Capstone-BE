<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    use HasFactory;

    protected $table = 'dokters';
    protected $primaryKey = 'dokterId';

    protected $fillable = [
        'nama',
        'spesialis',
        'aktif',
        'jenisKelamin',
        'SIP',
        'SIPdate',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'SIPdate' => 'date',
    ];
}
