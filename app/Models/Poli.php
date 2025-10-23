<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poli extends Model
{
    use HasFactory;

    protected $primaryKey = 'poliID';
    protected $table = 'polis';

    protected $fillable = [
        'nama',
        'tipeLayanan',
        'tipePoli',
        'superAdminID'
    ];

    public function superAdmin()
    {
        return $this->belongsTo(Admin::class, 'superAdminID', 'adminID');
    }
}