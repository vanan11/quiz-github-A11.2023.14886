<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class BuktiPembayaranDiunggah extends Notification
{
    public function __construct(
        public int $pembayaranId,
        public string $namaPasien,
        public int $jumlah,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'judul' => 'Bukti pembayaran diunggah',
            'pesan' => $this->namaPasien.' mengunggah bukti pembayaran Rp '.number_format($this->jumlah, 0, ',', '.').'.',
            'url' => route('riwayat-pasien.index'),
            'pembayaran_id' => $this->pembayaranId,
        ];
    }
}