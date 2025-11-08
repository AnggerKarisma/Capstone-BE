<?php

namespace App\Events;

use App\Models\Antrian;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // <-- Penting
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// 1. Terapkan 'ShouldBroadcast'
class AntrianDipanggil implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // 2. Buat properti publik untuk data
    public Antrian $antrian;
    public int $sisaAntrian;

    // 3. Terima data yang ingin di-broadcast
    public function __construct(Antrian $antrian, int $sisaAntrian)
    {
        $this->antrian = $antrian;
        $this->sisaAntrian = $sisaAntrian;
    }

    public function broadcastOn(): array
    {
        // 4. Kita kirim ini ke "channel publik" untuk poli tersebut
        // Siapa saja boleh mendengarkan (Pasien, TV Dashboard, Admin)
        return [
            new Channel("antrian.poli.{$this->antrian->poli_id}"),
        ];
    }
}