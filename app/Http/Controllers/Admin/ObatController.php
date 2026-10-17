<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;

class ObatController extends Controller
{

# Function index() digunakan untuk menampilkan daftar data obat yang tersimpan di database.
    public function index()
    {
        $obats = Obat::all(); # Kode tersebut digunakan untuk mengambil seluruh data obat dari tabel obats kemudian menyimpannya ke dalam variabel $obats
        return view('admin.obat.index', compact('obats')); # Menampilkan halaman index pada folder admin/obat dan mengirimkan data $obats ke view agar dapat ditampilkan dalam bentuk tabel daftar obat.
    }

# Function create() digunakan untuk menampilkan form input data obat baru.
    public function create()
    {
        return view('admin.obat.create'); # Menampilkan halaman create pada resources\views\admin\obat\create.blade.php
    }

# Function store() digunakan untuk menyimpan data obat baru yang dikirim dari form input.
    public function store(Request $request)
    {

# Memvalidasi data input sebelum disimpan ke database dengan hanya boleh field tertentu saja sesuai aturan validasi.
        $request->validate([
            'nama_obat' => 'required|string',
            'kemasan' => 'required|string',
            'harga' => 'required|integer',
        ]);

# Menyimpan data obat baru ke dalam tabel obat di database.
        Obat::create([
            'nama_obat' => $request->nama_obat,
            'kemasan' => $request->kemasan,
            'harga' => $request->harga
        ]);

# Mengarahkan pengguna kembali ke halaman daftar obat setelah data berhasil disimpan serta menampilkan notifikasi bahwa data obat berhasil dibuat.
        return redirect()
            ->route('obat.index')
            ->with('message', 'Data Obat Berhasil dibuat')
            ->with('type', 'success');
    }

# Function edit() digunakan untuk menampilkan form edit data obat.
    public function edit(string $id)
    {
        $obat = Obat::findOrFail($id); # Mencari data obat berdasarkan ID yang didapat dari route parameter.
        return view('admin.obat.edit')->with([
            'obat' => $obat
        ]); # Menampilkan halaman edit dan mengirimkan data obat ke view agar dapat ditampilkan pada form edit.
    }

# Function update() digunakan untuk memperbarui data obat yang sudah ada di database
public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_obat' => 'required|string',
            'kemasan' => 'nullable|string',
            'harga' => 'required|integer',
        ]);

        $obat = Obat::findOrFail($id); # Melakukan validasi data dari form edit sebelum data diperbarui.

# Memperbarui data obat yang telah ditemukan sebelumnya.
        $obat->update([
            'nama_obat' => $request->nama_obat,
            'kemasan' => $request->kemasan,
            'harga' => $request->harga
        ]);

        return redirect()
            ->route('obat.index')
            ->with('message', 'Data Obat berhasil di edit')
            ->with('type', 'success');
    }

# Function destroy() digunakan untuk menghapus data obat dari database.
    public function destroy(string $id)
    {

# mencari data obat berdasarkan ID, kemudian menghapus data tersebut dari tabel obat.
        $obat = Obat::findOrFail($id);
        $obat->delete();

        return redirect()
            ->route('obat.index')
            ->with('message', 'Data Obat berhasil di Hapus')
            ->with('type', 'success');
    }
}