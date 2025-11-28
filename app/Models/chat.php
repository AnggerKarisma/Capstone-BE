<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class chat extends Model
{
    use HasFactory;
    protected $table = 'chats';
    protected $primaryKey = 'chatId';
    protected $fillable = [
        'senderable_id',
        'senderable_type',
        'receiverable_id',
        'receiverable_type',
        'message',
        'is_read',
    ];

    protected $with = ['senderable', 'receiverable'];

    public function senderable()
    {
        return $this->morphTo();
    }

    public function receiverable()
    {
        return $this->morphTo();
    }
}
