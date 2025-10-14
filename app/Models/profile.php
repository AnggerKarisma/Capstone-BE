<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class profile extends Model
{
    protected $table = 'profiles';
    protected $fillable = [
        'user_id',
        'lokasi',
        'jenis_kelamin',
        'noKTP',
        'suku',
        'tempat_lahir',
        'tanggal_lahir',
        'status_keluarga',
        'nama_keluarga',
        'agama',
        'status_perkawinan',
        'pendidikan_terakhir',
        'alamat',
        'provinsi',
        'kota/kabupaten',
        'kecamatan',
        'kelurahan',
        'nomor_telepon',
        'nomor_pegawai',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
