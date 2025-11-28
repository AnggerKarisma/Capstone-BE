<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenanggungJawab extends Model
{
    use HasFactory;

    protected $table = 'penanggung_jawabs';
    protected $primaryKey = 'PjId';

    protected $fillable = [
        'nama',
        'nomor_whatsapp',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'penanggung_jawab_id', 'PjId');
    }
}
