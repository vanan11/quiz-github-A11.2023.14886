<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AntrianDiperbarui implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $tries = 5;

    public int $timeout = 15;

    /** @var list<int> */
    public array $backoff = [1, 5, 15, 30];

    public function __construct(
        public int $jadwalId,
        public int $dokterId,
        public int $pasienId,
        public ?string $tanggalKunjungan,
    ) {}

    /** @return list<PrivateChannel> */
    public function broadcastOn(): array
    {
        $tanggal = $this->tanggalKunjungan ?? 'tanpa-tanggal';

        return [
            new PrivateChannel('App.Models.User.'.$this->dokterId),
            new PrivateChannel('App.Models.User.'.$this->pasienId),
            new PrivateChannel('antrian.jadwal.'.$this->jadwalId.'.'.$tanggal),
        ];
    }

    public function broadcastAs(): string
    {
        return 'antrian.diperbarui';
    }

    /** @return array{id_jadwal: int, tanggal_kunjungan: ?string} */
    public function broadcastWith(): array
    {
        return [
            'id_jadwal' => $this->jadwalId,
            'tanggal_kunjungan' => $this->tanggalKunjungan,
        ];
    }
}