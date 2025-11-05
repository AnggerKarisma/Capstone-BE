<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class ReservationPolicy
{
    public function before(Authenticatable $actor, string $ability): bool|null
    {
        // Pengecekan: Apakah aktor ini adalah model Admin DAN rolenya 'superadmin'?
        if ($actor instanceof Admin && $actor->role === 'superadmin') {
            return true;
        }
        return null; // Jika bukan superadmin, lanjutkan ke pengecekan method di bawah
    }

    public function viewAny(Authenticatable $actor): bool
    {
        // Hanya Admin (dan SuperAdmin via 'before') yang boleh
        return $actor instanceof Admin;
    }

    public function view(Authenticatable $actor, Reservation $reservation): bool
    {
        // Jika aktor adalah User (pasien)
        if ($actor instanceof User) {
            // Dia hanya boleh lihat jika ID-nya cocok dengan ID pembuat reservasi
            return $actor->userid === $reservation->booked_user_id;
        }

        // Jika aktor adalah Admin, dia boleh lihat
        return $actor instanceof Admin;
    }

    public function create(Authenticatable $actor): bool
    {
        // Hanya User (pasien) yang bisa membuat reservasi
        return $actor instanceof User;
    }

    public function verify(Authenticatable $actor, Reservation $reservation): bool
    {
        // Hanya Admin (dan SuperAdmin via 'before')
        return $actor instanceof Admin;
    }

    public function cancel(Authenticatable $actor, Reservation $reservation): bool
    {
        // Jika aktor adalah User (pasien)
        if ($actor instanceof User) {
            // Dia hanya boleh batalin jika itu reservasi miliknya
            return $actor->userid === $reservation->booked_user_id;
        }

        // Jika aktor adalah Admin, dia boleh batalkan
        return $actor instanceof Admin;
    }

    public function update(Authenticatable $actor, Reservation $reservation): bool
    {
        // Hanya Admin (dan SuperAdmin via 'before')
        return $actor instanceof Admin;
    }

    public function delete(Authenticatable $actor, Reservation $reservation): bool
    {
        // Hanya Admin (dan SuperAdmin via 'before')
        return $actor instanceof Admin;
    }
}
