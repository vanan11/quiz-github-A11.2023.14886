<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // ============================================
    // MENAMPILKAN HALAMAN PROFILE
    // ============================================

    public function show(Request $request): View
    {
        // Ambil user yang sedang login
        $user = $request->user();

        return view('profile.show', compact('user'));
    }

    // ============================================
    // GANTI PASSWORD
    // ============================================

    public function updatePassword(
        Request $request
    ): RedirectResponse {

        // ========================================
        // VALIDASI PASSWORD
        // ========================================

        $data = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'different:current_password',
            ],

            'password_confirmation' => [
                'required',
                'same:password',
            ],
        ], [

            // Pesan password lama
            'current_password.required' =>
                'Password lama wajib diisi.',

            'current_password.current_password' =>
                'Password lama yang dimasukkan salah.',

            // Pesan password baru
            'password.required' =>
                'Password baru wajib diisi.',

            'password.min' =>
                'Password baru minimal 8 karakter.',

            'password.different' =>
                'Password baru tidak boleh sama dengan password lama.',

            // Pesan konfirmasi
            'password_confirmation.required' =>
                'Konfirmasi password wajib diisi.',

            'password_confirmation.same' =>
                'Konfirmasi password baru tidak cocok.',
        ]);

        // ========================================
        // AMBIL USER YANG SEDANG LOGIN
        // ========================================

        $user = $request->user();

        // ========================================
        // HASH PASSWORD BARU
        // ========================================

        $user->password = Hash::make(
            $data['password']
        );

        // Simpan perubahan ke database
        $user->save();

        // Perbarui session setelah perubahan
        $request->session()->regenerate();

        // ========================================
        // REDIRECT DENGAN PESAN SUKSES
        // ========================================

        return redirect()
            ->route('profile.show')
            ->with(
                'success',
                'Password berhasil diubah! Gunakan password baru saat login berikutnya.'
            );
    }
}
