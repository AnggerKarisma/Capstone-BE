<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'admins';
    protected $primaryKey = 'adminID';

    protected $fillable = [
        'Nama',
        'Email',
        'Password',
        'role'
    ];

    protected $hidden = [
        'Password',
    ];

    public function getAuthPassword()
    {
        return $this->Password;
    }

    public function polis()
    {
        return $this->hasMany(Poli::class, 'adminID');
    }

    // public function reservasis()
    // {
    //     return $this->hasMany(Reservasi::class, 'adminID');
    // }
}
