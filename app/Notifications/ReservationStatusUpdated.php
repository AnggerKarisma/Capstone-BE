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
        return [
            'reservation_id' => $this->reservation->reservid,
            'title' => 'Status Reservasi Diperbarui',
            'message' => 'Reservasi Poli ' . $this->reservation->poli->poli_name . ' Anda telah ' . $this->reservation->status,
            'status' => $this->reservation->status, // confirmed/cancelled
            'time' => now()
        ];
    }
}