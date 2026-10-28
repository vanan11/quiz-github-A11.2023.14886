<?php

namespace App\Console\Commands;

use App\Models\Pembayaran;
use App\Models\Periksa;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pembayaran:lengkapi-tagihan')]
#[Description('Melengkapi tagihan untuk pemeriksaan yang belum memiliki pembayaran')]
class LengkapiTagihan extends Command
{
    public function handle(): int
    {
        $jumlahBaru = 0;

        foreach (Periksa::query()->whereDoesntHave('pembayaran')->lazyById(100) as $periksa) {
            $pembayaran = Pembayaran::query()->firstOrCreate(
                ['id_periksa' => $periksa->id],
                [
                    'jumlah' => $periksa->biaya_periksa,
                    'status' => Pembayaran::STATUS_BELUM_BAYAR,
                ],
            );

            if ($pembayaran->wasRecentlyCreated) {
                $jumlahBaru++;
            }
        }

        $this->info($jumlahBaru.' tagihan baru berhasil dibuat.');

        return self::SUCCESS;
    }
}