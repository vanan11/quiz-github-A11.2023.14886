<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RiwayatPeriksaController extends Controller
{
    public function index(): View
    {
        $riwayats = DaftarPoli::query()
            ->where('id_pasien', Auth::id())
            ->with('jadwalPeriksa.dokter.poli')
            ->withCount('periksas')
            ->latest('id')
            ->paginate(10);

        return view('pasien.riwayat.index', compact('riwayats'));
    }

    public function show(string $id): View
    {
        $pendaftaran = DaftarPoli::query()
            ->where('id_pasien', Auth::id())
            ->with([
                'jadwalPeriksa.dokter.poli',
                'periksas.detailPeriksas.obat',
            ])
            ->findOrFail($id);

        return view('pasien.riwayat.show', compact('pendaftaran'));
    }
}