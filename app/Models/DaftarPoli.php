<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DaftarPoli extends Model
{
    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DIPANGGIL = 'dipanggil';
    public const STATUS_SELESAI = 'selesai';

    protected $table = 'daftar_poli';

    protected $fillable = [
        'id_pasien',
        'id_jadwal',
        'keluhan',
        'no_antrian',
        'tanggal_kunjungan',
        'status',
        'dipanggil_pada',
    ];

    protected function casts(): array
    {
        return [
            'no_antrian' => 'integer',
            'tanggal_kunjungan' => 'date',
            'dipanggil_pada' => 'datetime',
        ];
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pasien');
    }

    public function jadwalPeriksa(): BelongsTo
    {
        return $this->belongsTo(JadwalPeriksa::class, 'id_jadwal');
    }

    public function periksas(): HasMany
    {
        return $this->hasMany(Periksa::class, 'id_daftar_poli');
    }
}