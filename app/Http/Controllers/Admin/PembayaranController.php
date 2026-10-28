<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Notifications\PembayaranDiverifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function index(): View
    {
        $pembayarans = Pembayaran::query()
            ->with([
                'periksa.daftarPoli.pasien',
                'periksa.daftarPoli.jadwalPeriksa.dokter',
                'verifikator',
            ])
            ->latest('id')
            ->paginate(10);

        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    public function verifikasi(string $id): RedirectResponse
    {
        $berubah = DB::transaction(function () use ($id): bool {
            $pembayaran = Pembayaran::query()
                ->lockForUpdate()
                ->findOrFail($id);

            if ($pembayaran->status === Pembayaran::STATUS_TERBAYAR) {
                return false;
            }

            if (
                $pembayaran->status !== Pembayaran::STATUS_MENUNGGU_VERIFIKASI
                || ! $pembayaran->bukti_path
                || ! Storage::disk('local')->exists($pembayaran->bukti_path)
            ) {
                throw ValidationException::withMessages([
                    'pembayaran' => 'Pembayaran belum memiliki bukti yang dapat diverifikasi.',
                ]);
            }

            $pembayaran->update([
                'status' => Pembayaran::STATUS_TERBAYAR,
                'diverifikasi_oleh' => Auth::id(),
                'diverifikasi_pada' => now(),
            ]);

            $pembayaran->load('periksa.daftarPoli.pasien');

            $pembayaran->periksa?->daftarPoli?->pasien?->notify(
                new PembayaranDiverifikasi(
                    $pembayaran->id,
                    $pembayaran->jumlah,
                ),
            );

            return true;
        }, 3);

        return redirect()
            ->route('admin.pembayaran.index')
            ->with(
                'success',
                $berubah
                    ? 'Pembayaran berhasil diverifikasi.'
                    : 'Pembayaran ini sudah terverifikasi.',
            );
    }
}