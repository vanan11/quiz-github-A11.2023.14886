<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Notifications\BuktiPembayaranDiunggah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PembayaranController extends Controller
{
    public function index(): View
    {
        $pembayarans = $this->pembayaranPasien()
            ->with('periksa.daftarPoli.jadwalPeriksa.dokter.poli')
            ->latest('id')
            ->paginate(10);

        return view('pasien.pembayaran.index', compact('pembayarans'));
    }

    public function show(string $id): View
    {
        $pembayaran = $this->pembayaranPasien()
            ->with('periksa.daftarPoli.jadwalPeriksa.dokter.poli')
            ->findOrFail($id);

        return view('pasien.pembayaran.show', compact('pembayaran'));
    }

    public function unggah(Request $request, string $id): RedirectResponse
    {
        $this->pembayaranPasien()->findOrFail($id);

        $request->validate([
            'bukti' => [
                'bail',
                'required',
                'image',
                'dimensions:min_width=1,min_height=1',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ], [
            'bukti.required' => 'Pilih foto bukti pembayaran terlebih dahulu.',
            'bukti.image' => 'Bukti pembayaran harus berupa gambar.',
            'bukti.dimensions' => 'Bukti pembayaran harus berupa gambar.',
            'bukti.mimes' => 'Gunakan gambar JPG, PNG, atau WEBP.',
            'bukti.extensions' => 'Nama file harus berakhiran JPG, JPEG, PNG, atau WEBP.',
            'bukti.max' => 'Ukuran bukti pembayaran maksimal 4 MB.',
            'bukti.uploaded' => 'Bukti gagal diunggah. Periksa ukuran file dan pengaturan unggahan PHP.',
        ]);

        $path = null;
        $pathLama = null;

        try {
            DB::transaction(function () use ($request, $id, &$path, &$pathLama): void {
                $pembayaran = $this->pembayaranPasien()
                    ->lockForUpdate()
                    ->findOrFail($id);

                if ($pembayaran->status !== Pembayaran::STATUS_BELUM_BAYAR) {
                    throw ValidationException::withMessages([
                        'bukti' => 'Bukti sudah dikirim atau pembayaran sudah selesai. Muat ulang halaman tagihan.',
                    ]);
                }

                $path = $request->file('bukti')
                    ->store('bukti-pembayaran', 'local');

                if ($path === false) {
                    throw new RuntimeException('Bukti pembayaran gagal disimpan.');
                }

                $pathLama = $pembayaran->bukti_path;

                $pembayaran->update([
                    'bukti_path' => $path,
                    'status' => Pembayaran::STATUS_MENUNGGU_VERIFIKASI,
                    'diunggah_pada' => now(),
                ]);

                $pembayaran->load('periksa.daftarPoli.jadwalPeriksa.dokter');

                $dokter = $pembayaran->periksa?->daftarPoli?->jadwalPeriksa?->dokter;

                $dokter?->notify(new BuktiPembayaranDiunggah(
                    $pembayaran->id,
                    $request->user()->nama,
                    $pembayaran->jumlah,
                ));
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        if ($pathLama) {
            Storage::disk('local')->delete($pathLama);
        }

        return redirect()
            ->route('pasien.pembayaran.show', ['id' => $id])
            ->with(
                'success',
                'Bukti pembayaran berhasil diunggah. Silakan menunggu verifikasi admin.'
            );
    }

    /** @return Builder<Pembayaran> */
    private function pembayaranPasien(): Builder
    {
        return Pembayaran::query()
            ->whereHas('periksa.daftarPoli', function (Builder $query): void {
                $query->where('id_pasien', Auth::id());
            });
    }
}