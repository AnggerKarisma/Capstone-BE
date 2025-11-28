<?php
// app/Events/MessageSent.php

namespace App\Events;

use App\Models\Chat;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
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
        if ($this->chat->receiverable_type === Admin::class){
            return [new PrivateChannel('chat.admin')
        ];
    }
    if ($this->chat->receiverable_type === User::class){
        return [
            new PrivateChannel('chat.user.' . $this->chat->receiverable_id)
        ];
    }
    return [];
    }
}