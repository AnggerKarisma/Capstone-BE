<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table ='users';
    protected $primaryKey = 'userid';
    protected $fillable = [
        'name',
        'email',
        'password',
        'nomor_telepon',
    ];
    protected $hidden = [
        'password',
        'remember_token',
        'reservations',
        'profile',
        'chats',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id', 'userid');
    }
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'userid');
    }
    public function chats()
    {
        return $this->morphMany(Chat::class, 'senderable');
    }
}
