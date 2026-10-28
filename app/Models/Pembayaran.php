<?php

namespace App\Models;

use Database\Factories\PembayaranFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    /** @use HasFactory<PembayaranFactory> */
    use HasFactory;

    public const STATUS_BELUM_BAYAR = 'belum_bayar';

    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';

    public const STATUS_TERBAYAR = 'terbayar';

    protected $table = 'pembayaran';

    protected $fillable = [
        'id_periksa', 'jumlah', 'status', 'bukti_path',
        'diunggah_pada', 'diverifikasi_oleh', 'diverifikasi_pada',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'diunggah_pada' => 'datetime',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function periksa(): BelongsTo
    {
        return $this->belongsTo(Periksa::class, 'id_periksa');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            self::STATUS_BELUM_BAYAR => 'Belum Bayar',
            self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::STATUS_TERBAYAR => 'Terbayar',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
