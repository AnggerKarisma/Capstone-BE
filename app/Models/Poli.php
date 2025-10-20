<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poli extends Model
{
    use HasFactory;

    protected $primaryKey = 'poliID';

    protected $fillable = [
        'nama',
        'tipeLayanan',
        'tipePoli',
        'adminID'
    ];

    public function superAdmin()
    {
        return $this->belongsTo(SuperAdmin::class, 'superAdminID', 'superAdminID');
    }
}
