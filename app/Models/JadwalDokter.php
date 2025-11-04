<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalDokter extends Model
{
    use HasFactory;

    protected $table = 'jadwal_dokter';
    public $incrementing = false; 
    protected $primaryKey = ['dokterId', 'poliID'];
    public $timestamps = false; 

    /**
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dokterId',
        'poliID',
        'gedung',
        'pelayanan_cash',
        'pelayanan_bpjs',
        'pelayanan_asuransi',
        'pelayanan_kapitasi',
        'pelayanan_pertamina',
        'pelayanan_inhealth',
        
        // --- SENIN ---
        'senin_pagi_dari',
        'senin_pagi_sampai',
        'senin_pagi_kuota',
        'senin_siang_dari',
        'senin_siang_sampai',
        'senin_siang_kuota',
        'senin_sore_dari',
        'senin_sore_sampai',
        'senin_sore_kuota',
        'senin_praktek',
        'senin_keterangan',

        // --- SELASA ---
        'selasa_pagi_dari',
        'selasa_pagi_sampai',
        'selasa_pagi_kuota',
        'selasa_siang_dari',
        'selasa_siang_sampai',
        'selasa_siang_kuota',
        'selasa_sore_dari',
        'selasa_sore_sampai',
        'selasa_sore_kuota',
        'selasa_praktek',
        'selasa_keterangan',

        // --- RABU ---
        'rabu_pagi_dari',
        'rabu_pagi_sampai',
        'rabu_pagi_kuota',
        'rabu_siang_dari',
        'rabu_siang_sampai',
        'rabu_siang_kuota',
        'rabu_sore_dari',
        'rabu_sore_sampai',
        'rabu_sore_kuota',
        'rabu_praktek',
        'rabu_keterangan',

        // --- KAMIS ---
        'kamis_pagi_dari',
        'kamis_pagi_sampai',
        'kamis_pagi_kuota',
        'kamis_siang_dari',
        'kamis_siang_sampai',
        'kamis_siang_kuota',
        'kamis_sore_dari',
        'kamis_sore_sampai',
        'kamis_sore_kuota',
        'kamis_praktek',
        'kamis_keterangan',

        // --- JUMAT ---
        'jumat_pagi_dari',
        'jumat_pagi_sampai',
        'jumat_pagi_kuota',
        'jumat_siang_dari',
        'jumat_siang_sampai',
        'jumat_siang_kuota',
        'jumat_sore_dari',
        'jumat_sore_sampai',
        'jumat_sore_kuota',
        'jumat_praktek',
        'jumat_keterangan',

        // --- SABTU ---
        'sabtu_pagi_dari',
        'sabtu_pagi_sampai',
        'sabtu_pagi_kuota',
        'sabtu_siang_dari',
        'sabtu_siang_sampai',
        'sabtu_siang_kuota',
        'sabtu_sore_dari',
        'sabtu_sore_sampai',
        'sabtu_sore_kuota',
        'sabtu_praktek',
        'sabtu_keterangan',

        // --- MINGGU ---
        'minggu_pagi_dari',
        'minggu_pagi_sampai',
        'minggu_pagi_kuota',
        'minggu_siang_dari',
        'minggu_siang_sampai',
        'minggu_siang_kuota',
        'minggu_sore_dari',
        'minggu_sore_sampai',
        'minggu_sore_kuota',
        'minggu_praktek',
        'minggu_keterangan',

        'last_update',
        'last_update_by',
        'update_bpjs',
    ];

    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    /**
     * Relasi ke Dokter
     */
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokterId', 'dokterId');
    }

    /**
     * Relasi ke Poli
     */
    public function poli()
    {
        return $this->belongsTo(Poli::class, 'poliID', 'poliID');
    }
}