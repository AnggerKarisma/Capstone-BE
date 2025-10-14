<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'admins';
    protected $primaryKey = 'adminID';

    protected $fillable = [
        'Nama',
        'Email',
        'Password',
    ];

    protected $hidden = [
        'Password',
    ];

    public function polis()
    {
        return $this->hasMany(Poli::class, 'adminID');
    }

    public function reservasis()
    {
        return $this->hasMany(Reservasi::class, 'adminID');
    }
}
