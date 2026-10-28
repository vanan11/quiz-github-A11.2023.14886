<?php

namespace Database\Factories;

use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Pembayaran> */
class PembayaranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'jumlah' => 150000,
            'status' => Pembayaran::STATUS_BELUM_BAYAR,
        ];
    }
}