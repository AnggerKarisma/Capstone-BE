<?php
// app/Events/MessageSent.php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel; // <-- Gunakan Private
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // <-- Penting
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Chat $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    public function broadcastOn(): array
    {
        // Kirim ke channel pribadi milik si PENERIMA
        $receiver = $this->chat->receiverable;
        
        $type = $receiver instanceof \App\Models\Admin ? 'admin' : 'user';
        $key = $receiver instanceof \App\Models\Admin ? $receiver->adminID : $receiver->userid;
        
        // cth: 'chat.admin.5' atau 'chat.user.1'
        return [
            new PrivateChannel("chat.{$type}.{$key}"),
        ];
    }
}