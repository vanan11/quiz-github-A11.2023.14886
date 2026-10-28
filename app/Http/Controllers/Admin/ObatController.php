<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;

class ObatController extends Controller
{
    // ============================================
    // MENAMPILKAN DATA OBAT
    // ============================================

    public function index()
    {
        $obats = Obat::orderBy('nama_obat', 'asc')
            ->get();

        return view('admin.obat.index', compact('obats'));
    }

    // ============================================
    // FORM TAMBAH OBAT
    // ============================================

    public function create()
    {
        return view('admin.obat.create');
    }

    // ============================================
    // SIMPAN DATA OBAT
    // ============================================

    public function store(Request $request)
    {
        // Validasi data obat
        $data = $request->validate([
            'nama_obat' => [
                'required',
                'string',
                'max:255'
            ],
            'kemasan' => [
                'required',
                'string',
                'max:35'
            ],
            'harga' => [
                'required',
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'stok' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
                'max:4294967295'
            ],
        ], [
            'nama_obat.required' => 'Nama obat wajib diisi.',
            'kemasan.required' => 'Kemasan wajib diisi.',
            'harga.required' => 'Harga obat wajib diisi.',
            'harga.integer' => 'Harga harus berupa angka.',
            'harga.min' => 'Harga tidak boleh negatif.',
            'stok.required' => 'Stok obat wajib diisi.',
            'stok.integer' => 'Stok harus berupa bilangan bulat.',
            'stok.min' => 'Stok tidak boleh negatif.',
            'stok.max' => 'Jumlah stok terlalu besar.',
        ]);

        // Stok default 0 jika form lama belum
        // memiliki input stok.
        $data['stok'] = $data['stok'] ?? 0;

        // Simpan data obat
        Obat::create($data);

        return redirect()
            ->route('obat.index')
            ->with('message', 'Data Obat Berhasil dibuat')
            ->with('type', 'success');
    }

    // ============================================
    // FORM EDIT OBAT
    // ============================================

    public function edit(string $id)
    {
        $obat = Obat::findOrFail($id);

        return view('admin.obat.edit', compact('obat'));
    }

    // ============================================
    // UPDATE DATA OBAT
    // ============================================

    public function update(Request $request, string $id)
    {
        // Cari obat
        $obat = Obat::findOrFail($id);

        // Validasi data
        $data = $request->validate([
            'nama_obat' => [
                'required',
                'string',
                'max:255'
            ],
            'kemasan' => [
                'nullable',
                'string',
                'max:35'
            ],
            'harga' => [
                'required',
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'stok' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
                'max:4294967295'
            ],
        ], [
            'nama_obat.required' => 'Nama obat wajib diisi.',
            'harga.required' => 'Harga obat wajib diisi.',
            'harga.integer' => 'Harga harus berupa angka.',
            'harga.min' => 'Harga tidak boleh negatif.',
            'stok.required' => 'Stok obat wajib diisi.',
            'stok.integer' => 'Stok harus berupa bilangan bulat.',
            'stok.min' => 'Stok tidak boleh negatif.',
            'stok.max' => 'Jumlah stok terlalu besar.',
        ]);

        // Update data obat
        // Jika form belum mengirimkan stok,
        // nilai stok sebelumnya tetap tersimpan.
        $obat->update($data);

        return redirect()
            ->route('obat.index')
            ->with('message', 'Data Obat berhasil di edit')
            ->with('type', 'success');
    }

    // ============================================
    // HAPUS DATA OBAT
    // ============================================

    public function destroy(string $id)
    {
        $obat = Obat::findOrFail($id);

        $obat->delete();

        return redirect()
            ->route('obat.index')
            ->with('message', 'Data Obat berhasil di Hapus')
            ->with('type', 'success');
    }
}
