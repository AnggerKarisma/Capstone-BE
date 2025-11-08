<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    use HasFactory;

    protected $table = 'dokters';
    protected $primaryKey = 'dokter_id';

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'dokter_id',
        'nama_dokter',
        'bidang_keahlian',
        'tipe',
        'praktek',
        'last_update',
        'last_update_by',
        'telpon_praktek',
        'alamat_rumah',
        'telp_rumah',
        'aktif',
        'flags',
        'nama_dokter_asli',
        'konsulen',
        'start_date',
        'expire_date',
        'id_dokter_inht',
        'type_dok',
        'sip_dokter',
        'tmt_sip',
        'str_perawat',
        'tmt_str',
        'id_dokter_bpjs',
        'ttd_id',
        'sts_peg',
        'no_ktp',
        'id_satu_sehat',
        'jenis_kelamin',
        'ksm_role',
    ];

    public function jadwalDokter()
    {
        return $this->hasMany(JadwalDokter::class, 'dokter_id', 'dokter_id');
    }
}
