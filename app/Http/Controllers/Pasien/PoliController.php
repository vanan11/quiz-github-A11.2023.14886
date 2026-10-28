<?php

namespace App\Http\Controllers\Pasien;

use App\Events\AntrianDiperbarui;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PoliController extends Controller
{
    private const PESAN_PENDAFTARAN_AKTIF = 'Anda masih memiliki pendaftaran aktif. Selesaikan pemeriksaan terlebih dahulu.';

    public function dashboard(Request $request): View|JsonResponse
    {
        $pendaftaranAktif = $this->pendaftaranAktif((int) Auth::id())
            ->with('jadwalPeriksa.dokter.poli')
            ->oldest('id')
            ->first();

        $nomorDipanggil = null;
        $channels = ['App.Models.User.'.Auth::id()];

        if ($pendaftaranAktif !== null) {
            $tanggal = $pendaftaranAktif->tanggal_kunjungan?->toDateString()
                ?? 'tanpa-tanggal';

            $channels[] = 'antrian.jadwal.'
                .$pendaftaranAktif->id_jadwal.'.'.$tanggal;

            $antrianDipanggil = DaftarPoli::query()
                ->where('id_jadwal', $pendaftaranAktif->id_jadwal)
                ->where('status', DaftarPoli::STATUS_DIPANGGIL)
                ->whereDoesntHave('periksas');

            if ($pendaftaranAktif->tanggal_kunjungan !== null) {
                $antrianDipanggil->whereDate(
                    'tanggal_kunjungan',
                    $pendaftaranAktif->tanggal_kunjungan
                );
            } else {
                $antrianDipanggil->whereNull('tanggal_kunjungan');
            }

            $nomorDipanggil = $antrianDipanggil
                ->latest('dipanggil_pada')
                ->latest('id')
                ->value('no_antrian');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view(
                    'pasien.antrian',
                    compact('pendaftaranAktif', 'nomorDipanggil')
                )->render(),
                'channels' => $channels,
            ])->header('Cache-Control', 'private, no-store');
        }

        $jadwals = $this->jadwalTersedia()->get();

        return view('pasien.dashboard', compact(
            'pendaftaranAktif',
            'jadwals',
            'nomorDipanggil',
            'channels'
        ));
    }

    public function get(): View|RedirectResponse
    {
        if ($this->pendaftaranAktif((int) Auth::id())->exists()) {
            return redirect()->route('pasien.dashboard')
                ->with('error', self::PESAN_PENDAFTARAN_AKTIF);
        }

        return view('pasien.daftar', [
            'user' => Auth::user(),
            'polis' => Poli::query()->orderBy('nama_poli')->get(),
            'jadwals' => $this->jadwalTersedia()->get(),
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_poli' => ['required', 'integer', 'exists:poli,id'],
            'id_jadwal' => ['required', 'integer', 'exists:jadwal_periksa,id'],
            'keluhan' => ['required', 'string', 'max:2000'],
        ], [
            'id_poli.required' => 'Pilih poli terlebih dahulu.',
            'id_jadwal.required' => 'Pilih jadwal dokter.',
            'keluhan.required' => 'Keluhan wajib diisi.',
            'keluhan.max' => 'Keluhan maksimal 2.000 karakter.',
        ]);

        $pendaftaran = DB::transaction(function () use ($data): DaftarPoli {
            $pasien = User::query()
                ->lockForUpdate()
                ->findOrFail(Auth::id());

            if ($this->pendaftaranAktif((int) $pasien->id)
                ->lockForUpdate()->first() !== null) {
                throw ValidationException::withMessages([
                    'pendaftaran' => self::PESAN_PENDAFTARAN_AKTIF,
                ]);
            }

            $jadwal = JadwalPeriksa::with('dokter')
                ->lockForUpdate()
                ->findOrFail($data['id_jadwal']);

            if (
                $jadwal->dokter?->role !== 'dokter'
                || (int) $jadwal->dokter->id_poli !== (int) $data['id_poli']
            ) {
                throw ValidationException::withMessages([
                    'id_jadwal' => 'Jadwal dokter tidak sesuai dengan poli yang dipilih.',
                ]);
            }

            $tanggalKunjungan = $this->tanggalKunjungan($jadwal);

            $antrianTerakhir = DaftarPoli::query()
                ->where('id_jadwal', $jadwal->id)
                ->where(function (Builder $query) use ($tanggalKunjungan): void {
                    $query->whereDate('tanggal_kunjungan', $tanggalKunjungan)
                        ->orWhere(function (Builder $lama) use ($tanggalKunjungan): void {
                            $lama->whereNull('tanggal_kunjungan')
                                ->whereDate('created_at', $tanggalKunjungan);
                        });
                })
                ->orderByDesc('no_antrian')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $pendaftaran = DaftarPoli::create([
                'id_pasien' => $pasien->id,
                'id_jadwal' => $jadwal->id,
                'keluhan' => $data['keluhan'],
                'no_antrian' => ($antrianTerakhir?->no_antrian ?? 0) + 1,
                'tanggal_kunjungan' => $tanggalKunjungan,
                'status' => DaftarPoli::STATUS_MENUNGGU,
            ]);

            AntrianDiperbarui::dispatch(
                (int) $jadwal->id,
                (int) $jadwal->id_dokter,
                (int) $pasien->id,
                $tanggalKunjungan,
            );

            return $pendaftaran;
        }, 3);

        return redirect()->route('pasien.dashboard')
            ->with(
                'success',
                'Pendaftaran berhasil. Nomor antrian Anda: '
                    .$pendaftaran->no_antrian.'.'
            );
    }

    /** @return Builder<DaftarPoli> */
    private function pendaftaranAktif(int $pasienId): Builder
    {
        return DaftarPoli::query()
            ->where('id_pasien', $pasienId)
            ->whereIn('status', [
                DaftarPoli::STATUS_MENUNGGU,
                DaftarPoli::STATUS_DIPANGGIL,
            ])
            ->whereDoesntHave('periksas');
    }

    /** @return Builder<JadwalPeriksa> */
    private function jadwalTersedia(): Builder
    {
        return JadwalPeriksa::with('dokter.poli')
            ->whereHas('dokter', function (Builder $query): void {
                $query->where('role', 'dokter')
                    ->whereNotNull('id_poli');
            })
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->orderBy('id');
    }

    private function tanggalKunjungan(JadwalPeriksa $jadwal): string
    {
        $daftarHari = [
            'minggu' => 0,
            'senin' => 1,
            'selasa' => 2,
            'rabu' => 3,
            'kamis' => 4,
            'jumat' => 5,
            'sabtu' => 6,
        ];

        $hariJadwal = $daftarHari[mb_strtolower(trim($jadwal->hari))] ?? null;

        if ($hariJadwal === null) {
            throw ValidationException::withMessages([
                'id_jadwal' => 'Hari pada jadwal dokter tidak valid. Hubungi dokter atau admin.',
            ]);
        }

        $hariIni = CarbonImmutable::today(config('app.timezone'));
        $selisihHari = ($hariJadwal - $hariIni->dayOfWeek + 7) % 7;

        return $hariIni->addDays($selisihHari)->toDateString();
    }
}