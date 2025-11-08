<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(); // Pastikan ini aktif

Broadcast::channel('chat.{type}.{id}', function ($actor, $type, $id) {
    
    if ($type === 'user' && $actor instanceof \App\Models\User) {
        return $actor->userid == (int) $id;
    }
    
    if ($type === 'admin' && $actor instanceof \App\Models\Admin) {
        return $actor->adminID == (int) $id;
    }

    return false; 
});