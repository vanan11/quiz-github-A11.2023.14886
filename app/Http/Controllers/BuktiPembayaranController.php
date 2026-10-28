<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BuktiPembayaranController extends Controller
{
    public function __invoke(Request $request, string $id): StreamedResponse
    {
        $query = Pembayaran::query();

        if ($request->user()->role === 'pasien') {
            $query->whereHas(
                'periksa.daftarPoli',
                function (Builder $query) use ($request): void {
                    $query->where('id_pasien', $request->user()->id);
                }
            );
        } else {
            abort_unless($request->user()->role === 'admin', 403);
        }

        $pembayaran = $query->findOrFail($id);
        $disk = Storage::disk('local');

        abort_unless(
            $pembayaran->bukti_path
                && $disk->exists($pembayaran->bukti_path),
            404
        );

        $gambar = @getimagesize(
            $disk->path($pembayaran->bukti_path)
        );

        abort_unless(
            $gambar && in_array($gambar['mime'] ?? null, [
                'image/jpeg',
                'image/png',
                'image/webp',
            ], true),
            404
        );

        return $disk->response($pembayaran->bukti_path, null, [
            'Content-Type' => $gambar['mime'],
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}