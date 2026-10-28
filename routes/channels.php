<?php

use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Validator;

Broadcast::channel('App.Models.User.{id}', function (?User $user, string $id): bool {
    return $user !== null && (string) $user->id === $id;
});

Broadcast::channel('antrian.jadwal.{jadwalId}.{tanggal}', function (
    ?User $user,
    string $jadwalId,
    string $tanggal,
): bool {
    if ($user === null || (string) (int) $jadwalId !== $jadwalId || (int) $jadwalId < 1) {
        return false;
    }

    if ($tanggal !== 'tanpa-tanggal' && Validator::make(
        ['tanggal' => $tanggal],
        ['tanggal' => ['date_format:Y-m-d']],
    )->fails()) {
        return false;
    }

    $jadwal = JadwalPeriksa::query()->find((int) $jadwalId, ['id', 'id_dokter']);

    if ($jadwal === null) {
        return false;
    }

    if ($user->role === 'dokter') {
        return (int) $jadwal->id_dokter === (int) $user->id;
    }

    if ($user->role !== 'pasien') {
        return false;
    }

    $pendaftaran = DaftarPoli::query()
        ->where('id_pasien', $user->id)
        ->where('id_jadwal', $jadwal->id)
        ->whereIn('status', [
            DaftarPoli::STATUS_MENUNGGU,
            DaftarPoli::STATUS_DIPANGGIL,
        ])
        ->whereDoesntHave('periksas');

    if ($tanggal === 'tanpa-tanggal') {
        return $pendaftaran->whereNull('tanggal_kunjungan')->exists();
    }

    return $pendaftaran->whereDate('tanggal_kunjungan', $tanggal)->exists();
});