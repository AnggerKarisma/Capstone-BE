<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poli extends Model
{
    use HasFactory;

    protected $table = 'polis';
    protected $primaryKey = 'poli_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'poli_id',  
        'poli_name',  
        'update_date',  
        'update_by',  
        'tipe_layanan',  
        'tipe_poli',  
        'kode_lokasi',  
        'kode_urutan',  
        'create_mr',  
        'as_gudang',  
        'kode_bagian_1',  
        'create_miv',  
        'kode_bag_rs',  
        'rekening_p_5',  
        'rekening_r_5',  
        'rekening_b_5',  
        'ppk',  
        'petty_cash',  
        'hbi',  
        'kode_apotik',  
        'kode_konsul',  
        'aktif',  
        'id_mysap',  
        'rawat_jalan',  
        'desk_lama',  
        'teknik',  
        'pertg_jwbn',  
        'kode_korporat',  
        'kode_rs',  
        'kepala',  
        'wadir',  
        'proses_stock',  
        'poli_id_inht',  
        'poli_pdk',  
        'poli_id_bpjs',  
        'kode_printer',  
        'kode_rujuk_bpjs',  
        'poli_id_main',  
        'd_satu_sehat',  
        'panjar_kerja',  
        'view_mjkn',  
    ];
    
    public function jadwalDokter()
    {
        return $this->hasMany(JadwalDokter::class, 'poli_id', 'poli_id');
    }

    public function superAdmin()
    {
        return $this->belongsTo(Admin::class, 'superAdminID', 'adminID');
    }
}