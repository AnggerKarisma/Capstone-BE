<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class ReservationStatusUpdated extends Notification
{
    use Queueable;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function via(object $notifiable): array
    {
        return ['database','broadcast']; 
    }

    /**
     * Format data yang disimpan ke Database
     */
    public function toArray(object $notifiable): array
    {
        // Format status message
        $statusText = match($this->reservation->status) {
            'confirmed' => 'dikonfirmasi',
            'cancelled' => 'dibatalkan',
            'pending' => 'sedang diproses',
            default => $this->reservation->status
        };

        return [
            'reservation_id' => $this->reservation->reservid,
            'category' => 'Reservasi',
            'title' => 'Reservasi ' . ucfirst($statusText),
            'message' => 'Reservasi Anda pada ' . $this->reservation->poli->poli_name . ' sudah ' . $statusText . ' oleh admin',
            'status' => $this->reservation->status, // confirmed/cancelled
            'poli_name' => $this->reservation->poli->poli_name,
            'tanggal_reservasi' => $this->reservation->tanggal_reservasi,
            'time' => now()
        ];
    }
}