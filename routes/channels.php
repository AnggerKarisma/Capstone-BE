<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Admin;

Broadcast::routes(); // Pastikan ini aktif

Broadcast::channel('chat.user.{id}', function ($user, $id) {
    return $user instanceof User && (int) $user->userid === (int) $id;
});
Broadcast::channel('chat.admin', function ($user) {
    return $user instanceof Admin;
});