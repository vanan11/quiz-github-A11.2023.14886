<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalPeriksaController extends Controller
{

# Function index() digunakan untuk menampilkan daftar jadwal periksa dokter yang tersimpan di database.
    public function index(){
        $dokter = Auth::user(); # Mengambil data user yang sedang login dari sistem autentikasi Laravel, kemudian menyimpannya ke dalam variabel $dokter.
        $jadwalPeriksas = JadwalPeriksa::where('id_dokter', $dokter->id)->orderBy('hari')->get(); # Mengambil data jadwal periksa yang dimiliki oleh dokter yang sedang login, kemudian menyimpannya ke dalam variabel $jadwals.
        return view('dokter.jadwal-periksa.index', compact('jadwalPeriksas')); # Menampilkan halaman index yang berada pada folder jadwal-periksa di dalam folder dokter, kemudian mengirimkan data $jadwals ke view agar dapat ditampilkan pada tabel daftar jadwal periksa
    }

# Function create() digunakan untuk mengembalikan ke tampilan form input jadwal periksa baru.
    public function create(){
        return view('dokter.jadwal-periksa.create'); # Menampilkan halaman create yang berada pada folder jadwal-periksa di dalam folder dokter.
    }

# Function store() digunakan untuk menyimpan data jadwal periksa baru yang dikirim dari form input. Pada proses ini sistem akan melakukan beberapa langkah.
    public function store(Request $request){
        $request->validate([
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required'
        ]); # Melakukan validasi data dari form input seperti hari, jam mulai, dan jam selesai.
            # Melakukan validasi data yang dikirim dari form input. # Field hari, jam_mulai, dan jam_selesai harus diisi sebelum data disimpan ke database. Jika salah satu field tidak diisi, sistem akan menampilkan pesan error.      
            
            JadwalPeriksa::create([
            'id_dokter' => Auth::id(),
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai
        ]); # Menyimpan data jadwal periksa ke dalam tabel jadwal_periksas di database. # Menyimpan data jadwal periksa baru ke dalam tabel jadwal_periksas. Data dokter diambil dari user yang sedang login, sedangkan data hari dan jam diambil dari input form.

        return redirect()->route('jadwal-periksa.index')
            ->with('message', 'Data Berhasil di Simpan')
            ->with('type', 'success'); # Mengarahkan pengguna kembali ke halaman daftar jadwal periksa setelah data berhasil disimpan.
    }
# Setelah data berhasil disimpan, sistem akan mengarahkan pengguna kembali ke halaman daftar jadwal periksa dengan pesan bahwa data berhasil ditambahkan.

# Function edit() digunakan untuk menampilkan form edit jadwal periksa.
    public function edit($id){
        $jadwalPeriksa = JadwalPeriksa::findOrFail($id); # Mencari data jadwal periksa berdasarkan ID, kemudian menyimpannya ke dalam variabel $jadwal.
        return view('dokter.jadwal-periksa.edit', compact('jadwalPeriksa')); #  Menampilkan halaman edit dan mengirimkan data jadwal periksa ke view agar dapat ditampilkan pada form edit.
    }

    # Function update() digunakan untuk memperbarui data jadwal periksa yang sudah ada di database berdasarkan ID jadwal yang dipilih.
    public function update(Request $request, string $id){
        $request->validate([
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required'
        ]); # Melakukan validasi data yang dikirim dari form edit. Field hari, jam_mulai, dan jam_selesai harus diisi sebelum data dapat diperbarui di database.

        $jadwalPeriksa = JadwalPeriksa::findOrFail($id); # Mencari data jadwal periksa berdasarkan ID. Method findOrFail() mengambil data jadwal yang sesuai dengan ID yang diberikan. Jika data tidak ditemukan, maka Laravel akan menampilkan halaman error.
        $jadwalPeriksa->update([
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai
        ]); # Memperbarui data jadwal periksa yang telah ditemukan sebelumnya. Data yang diperbarui meliputi hari, jam mulai, dan jam selesai yang diambil dari input form edit.

        return redirect()->route('jadwal-periksa.index')
            ->with('message', 'Berhasil Melakukan Update Data')
            ->with('type', 'success');
    }

# Function destroy() digunakan untuk menghapus data jadwal periksa dari database.
    public function destroy(string $id){
        $jadwalPeriksa = JadwalPeriksa::findOrFail($id);
        $jadwalPeriksa->delete(); # Mencari data jadwal periksa berdasarkan ID, kemudian menghapus data tersebut dari tabel jadwal_periksas.

        return redirect()->route('jadwal-periksa.index')
            ->with('message', 'Berhasil Melakukan Hapus Data')
            ->with('type', 'success'); # Setelah proses penghapusan berhasil dilakukan, sistem akan mengarahkan pengguna kembali ke halaman daftar jadwal periksa dengan pesan bahwa data berhasil dihapus.
    }
}