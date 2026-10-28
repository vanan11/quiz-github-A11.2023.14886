<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PembayaranDiverifikasi extends Notification
{
    public function __construct(
        public int $pembayaranId,
        public int $jumlah,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'judul' => 'Pembayaran diverifikasi',
            'pesan' => 'Pembayaran Rp '.number_format($this->jumlah, 0, ',', '.').' telah diverifikasi admin.',
            'url' => route('pasien.pembayaran.show', [
                'id' => $this->pembayaranId,
            ]),
            'pembayaran_id' => $this->pembayaranId,
        ];
    }
}