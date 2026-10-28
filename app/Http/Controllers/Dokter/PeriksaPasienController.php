<?php

namespace App\Http\Controllers\Dokter;

use App\Events\AntrianDiperbarui;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Pembayaran;
use App\Models\Periksa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PeriksaPasienController extends Controller
{
    private const BIAYA_JASA_DOKTER = 150000;

    public function index(Request $request): View|JsonResponse
    {
        $daftarPasien = $this->pendaftaranDokter()
            ->with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->orderBy('tanggal_kunjungan')
            ->orderBy('id_jadwal')
            ->orderBy('no_antrian')
            ->orderBy('id')
            ->get();

        $channels = ['App.Models.User.'.Auth::id()];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view(
                    'dokter.periksa-pasien.antrian',
                    compact('daftarPasien')
                )->render(),
                'channels' => $channels,
            ])->header('Cache-Control', 'private, no-store');
        }

        return view(
            'dokter.periksa-pasien.index',
            compact('daftarPasien', 'channels')
        );
    }

    public function panggil(string $id): RedirectResponse
    {
        $nomor = DB::transaction(function () use ($id): int {
            $pendaftaran = $this->kunciPendaftaran($id);

            if (
                $pendaftaran->periksas()->exists()
                || $pendaftaran->status === DaftarPoli::STATUS_SELESAI
            ) {
                throw ValidationException::withMessages([
                    'antrian' => 'Pasien ini sudah selesai diperiksa.',
                ]);
            }

            if ($pendaftaran->status === DaftarPoli::STATUS_DIPANGGIL) {
                return $pendaftaran->no_antrian;
            }

            $antrian = $this->kelompokAntrian($pendaftaran)
                ->whereDoesntHave('periksas');

            $sedangDilayani = (clone $antrian)
                ->where('status', DaftarPoli::STATUS_DIPANGGIL)
                ->lockForUpdate()
                ->first();

            if ($sedangDilayani !== null) {
                throw ValidationException::withMessages([
                    'antrian' => 'Selesaikan pasien yang sedang dilayani pada jadwal dan tanggal ini terlebih dahulu.',
                ]);
            }

            $berikutnya = (clone $antrian)
                ->where('status', DaftarPoli::STATUS_MENUNGGU)
                ->orderBy('no_antrian')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($berikutnya?->id !== $pendaftaran->id) {
                throw ValidationException::withMessages([
                    'antrian' => 'Panggil pasien sesuai urutan nomor antrian.',
                ]);
            }

            $pendaftaran->update([
                'status' => DaftarPoli::STATUS_DIPANGGIL,
                'dipanggil_pada' => now(),
            ]);

            $this->kirimPembaruanAntrian($pendaftaran);

            return $pendaftaran->no_antrian;
        }, 3);

        return redirect()->route('periksa-pasien.index')
            ->with('success', 'Nomor antrian '.$nomor.' berhasil dipanggil.');
    }

    public function create(string $id): View|RedirectResponse
    {
        $pendaftaran = $this->pendaftaranDokter()->findOrFail($id);

        if (
            $pendaftaran->periksas()->exists()
            || $pendaftaran->status === DaftarPoli::STATUS_SELESAI
        ) {
            return redirect()->route('periksa-pasien.index')
                ->with('error', 'Pasien ini sudah selesai diperiksa.');
        }

        if ($pendaftaran->status !== DaftarPoli::STATUS_DIPANGGIL) {
            return redirect()->route('periksa-pasien.index')
                ->with('error', 'Panggil pasien terlebih dahulu sebelum melakukan pemeriksaan.');
        }

        $obats = Obat::query()->orderBy('nama_obat')->get();

        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_daftar_poli' => ['required', 'integer'],
            'obat_json' => ['required', 'json'],
            'catatan' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->pendaftaranDokter()->findOrFail($data['id_daftar_poli']);
        $obatIds = json_decode($data['obat_json']);

        if (! is_array($obatIds)) {
            throw ValidationException::withMessages([
                'obat_json' => 'Format daftar obat tidak valid.',
            ]);
        }

        Validator::make(['obat' => $obatIds], [
            'obat' => ['array', 'min:1'],
            'obat.*' => ['integer', 'distinct', 'exists:obat,id'],
        ], [
            'obat.min' => 'Pilih minimal satu obat terlebih dahulu.',
            'obat.*.distinct' => 'Obat yang sama hanya boleh dipilih sekali.',
            'obat.*.exists' => 'Obat yang dipilih tidak ditemukan.',
        ])->validate();

        DB::transaction(function () use ($data, $obatIds): void {
            $pendaftaran = $this->kunciPendaftaran(
                (string) $data['id_daftar_poli']
            );

            if ($pendaftaran->periksas()->exists()) {
                $pendaftaran->update([
                    'status' => DaftarPoli::STATUS_SELESAI,
                ]);

                return;
            }

            if ($pendaftaran->status !== DaftarPoli::STATUS_DIPANGGIL) {
                throw ValidationException::withMessages([
                    'pendaftaran' => 'Panggil pasien terlebih dahulu sebelum menyimpan pemeriksaan.',
                ]);
            }

            $obats = Obat::query()
                ->whereKey($obatIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($obats->count() !== count($obatIds)) {
                throw ValidationException::withMessages([
                    'obat_json' => 'Data obat berubah. Silakan pilih obat kembali.',
                ]);
            }

            $periksa = Periksa::create([
                'id_daftar_poli' => $pendaftaran->id,
                'tgl_periksa' => now(),
                'catatan' => $data['catatan'] ?? null,
                'biaya_periksa' => self::BIAYA_JASA_DOKTER
                    + (int) $obats->sum('harga'),
            ]);

            foreach ($obats as $obat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $obat->id,
                    'harga_satuan' => $obat->harga,
                ]);
            }

            $periksa->pembayaran()->create([
                'jumlah' => $periksa->biaya_periksa,
                'status' => Pembayaran::STATUS_BELUM_BAYAR,
            ]);

            $pendaftaran->update([
                'status' => DaftarPoli::STATUS_SELESAI,
            ]);

            $this->kirimPembaruanAntrian($pendaftaran);
        }, 3);

        return redirect()->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan.');
    }

    private function kirimPembaruanAntrian(DaftarPoli $pendaftaran): void
    {
        AntrianDiperbarui::dispatch(
            (int) $pendaftaran->id_jadwal,
            (int) Auth::id(),
            (int) $pendaftaran->id_pasien,
            $pendaftaran->tanggal_kunjungan?->toDateString(),
        );
    }

    /** @return Builder<DaftarPoli> */
    private function pendaftaranDokter(): Builder
    {
        return DaftarPoli::query()
            ->whereHas('jadwalPeriksa', function (Builder $query): void {
                $query->where('id_dokter', Auth::id());
            });
    }

    private function kunciPendaftaran(string $id): DaftarPoli
    {
        $awal = $this->pendaftaranDokter()->findOrFail($id);

        JadwalPeriksa::query()
            ->whereKey($awal->id_jadwal)
            ->where('id_dokter', Auth::id())
            ->lockForUpdate()
            ->firstOrFail();

        return $this->pendaftaranDokter()
            ->lockForUpdate()
            ->findOrFail($id);
    }

    /** @return Builder<DaftarPoli> */
    private function kelompokAntrian(DaftarPoli $pendaftaran): Builder
    {
        $antrian = DaftarPoli::query()
            ->where('id_jadwal', $pendaftaran->id_jadwal);

        if ($pendaftaran->tanggal_kunjungan === null) {
            return $antrian->whereNull('tanggal_kunjungan');
        }

        return $antrian->whereDate(
            'tanggal_kunjungan',
            $pendaftaran->tanggal_kunjungan
        );
    }
}