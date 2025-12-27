<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'admins';
    protected $primaryKey = 'adminID';

    protected $fillable = [
        'Nama',     
        'Email',    
        'Password', 
        'role',
        'poli_id', 
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'Password' => 'hashed', 
        ];
    }

    public function getAuthPassword()
    {
        return $this->Password;
    }

    public function hasRole($roles)
    {
        $roleArray = explode(',', $roles);
        return in_array($this->role, $roleArray);
    }

    public function scopeIsPoliAdmin($query)
    {
        return $query->whereNotNull('poli_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'poli_id', 'poli_id');
    }

    public function chats()
    {
        return $this->morphMany(Chat::class, 'senderable');
    }
}