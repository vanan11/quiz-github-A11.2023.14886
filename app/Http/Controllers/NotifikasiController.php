<?php

namespace App\Http\Controllers;

use App\Notifications\BuktiPembayaranDiunggah;
use App\Notifications\PembayaranDiverifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $notifikasis = $request->user()->notifications()
            ->reorder()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        $jumlahBelumDibaca = $request->user()
            ->unreadNotifications()
            ->count();

        return view(
            'notifikasi.index',
            compact('notifikasis', 'jumlahBelumDibaca')
        );
    }

    public function buka(Request $request, string $id): RedirectResponse
    {
        $notifikasi = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notifikasi->markAsRead();

        return redirect()->to(
            $this->tujuanNotifikasi(
                $notifikasi,
                $request->user()->role
            )
        );
    }

    public function bacaSemua(Request $request): RedirectResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return redirect()
            ->route('notifikasi.index')
            ->with('success', 'Semua notifikasi sudah ditandai dibaca.');
    }

    private function tujuanNotifikasi(
        DatabaseNotification $notifikasi,
        string $role
    ): string {
        if (
            $role === 'dokter'
            && $notifikasi->type === BuktiPembayaranDiunggah::class
        ) {
            return route('riwayat-pasien.index');
        }

        if (
            $role === 'pasien'
            && $notifikasi->type === PembayaranDiverifikasi::class
        ) {
            $pembayaranId = filter_var(
                $notifikasi->data['pembayaran_id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]],
            );

            if ($pembayaranId !== false) {
                return route('pasien.pembayaran.show', [
                    'id' => $pembayaranId,
                ]);
            }
        }

        return route('notifikasi.index');
    }
}