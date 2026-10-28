
<?php

use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PasienController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuktiPembayaranController;
use App\Http\Controllers\Dokter\JadwalPeriksaController;
use App\Http\Controllers\Dokter\PeriksaPasienController;
use App\Http\Controllers\Dokter\RiwayatPasienController;
use App\Http\Controllers\Dokter\RiwayatPasienExportController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Pasien\PembayaranController as PasienPembayaranController;
use App\Http\Controllers\Pasien\PoliController as PasienPoliController;
use App\Http\Controllers\Pasien\RiwayatPeriksaController;
use Illuminate\Support\Facades\Route;

// ================================================
// HALAMAN AWAL
// ================================================

Route::get('/', function () {
    return view('welcome');
});

// ================================================
// AUTENTIKASI
// ================================================

Route::get('/login', [
    AuthController::class,
    'showLogin',
])->name('login');

Route::post('/login', [
    AuthController::class,
    'login',
]);

Route::get('/register', [
    AuthController::class,
    'showRegister',
]);

Route::post('/register', [
    AuthController::class,
    'register',
])->name('register');

Route::post('/logout', [
    AuthController::class,
    'logout',
])->name('logout');

// ================================================
// PROFILE DAN GANTI PASSWORD
// SEMUA ROLE
// ================================================

Route::middleware('auth')
    ->group(function () {

        // Halaman Profile
        Route::get('/profile', [
            ProfileController::class,
            'show',
        ])->name('profile.show');

        // Proses Ganti Password
        Route::put('/profile/password', [
            ProfileController::class,
            'updatePassword',
        ])->name('profile.password.update');
    });

// ================================================
// NOTIFIKASI
// ================================================

Route::middleware('auth')
    ->prefix('notifikasi')
    ->name('notifikasi.')
    ->group(function () {

        Route::get('/', [
            NotifikasiController::class,
            'index',
        ])->name('index');

        Route::post('/baca-semua', [
            NotifikasiController::class,
            'bacaSemua',
        ])->name('baca-semua');

        Route::post('/{id}/buka', [
            NotifikasiController::class,
            'buka',
        ])->whereUuid('id')
            ->name('buka');
    });

// ================================================
// ADMIN
// ================================================

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

        // Dashboard Admin
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // ========================================
        // EXPORT EXCEL ADMIN
        // ========================================

        Route::get('/dokter/export-excel', [
            ExportController::class,
            'dokter',
        ])->name('admin.dokter.export');

        Route::get('/pasien/export-excel', [
            ExportController::class,
            'pasien',
        ])->name('admin.pasien.export');

        Route::get('/obat/export-excel', [
            ExportController::class,
            'obat',
        ])->name('admin.obat.export');

        // ========================================
        // MASTER DATA
        // ========================================

        Route::resource(
            'polis',
            AdminPoliController::class
        );

        Route::resource(
            'dokter',
            DokterController::class
        );

        Route::resource(
            'pasien',
            PasienController::class
        );

        Route::resource(
            'obat',
            ObatController::class
        );

        // ========================================
        // PEMBAYARAN ADMIN
        // ========================================

        Route::get('/pembayaran', [
            AdminPembayaranController::class,
            'index',
        ])->name('admin.pembayaran.index');

        Route::post('/pembayaran/{id}/verifikasi', [
            AdminPembayaranController::class,
            'verifikasi',
        ])->whereNumber('id')
            ->name('admin.pembayaran.verifikasi');

        Route::get('/pembayaran/{id}/bukti', [
            BuktiPembayaranController::class,
        ])->whereNumber('id')
            ->name('admin.pembayaran.bukti');
    });

// ================================================
// DOKTER
// ================================================

Route::middleware(['auth', 'role:dokter'])
    ->prefix('dokter')
    ->group(function () {

        // Dashboard Dokter
        Route::get('/dashboard', function () {
            return view('dokter.dashboard');
        })->name('dokter.dashboard');

        // ========================================
        // EXPORT JADWAL PERIKSA
        // ========================================

        Route::get('/jadwal-periksa/export-excel', [
            JadwalPeriksaController::class,
            'exportExcel',
        ])->name('jadwal-periksa.export');

        // ========================================
        // JADWAL PERIKSA
        // ========================================

        Route::resource(
            'jadwal-periksa',
            JadwalPeriksaController::class
        );

        // ========================================
        // PERIKSA PASIEN
        // ========================================

        Route::post('/periksa-pasien/{id}/panggil', [
            PeriksaPasienController::class,
            'panggil',
        ])->whereNumber('id')
            ->name('periksa-pasien.panggil');

        Route::get('/periksa-pasien', [
            PeriksaPasienController::class,
            'index',
        ])->name('periksa-pasien.index');

        Route::post('/periksa-pasien', [
            PeriksaPasienController::class,
            'store',
        ])->name('periksa-pasien.store');

        Route::get('/periksa-pasien/{id}', [
            PeriksaPasienController::class,
            'create',
        ])->name('periksa-pasien.create');

        // ========================================
        // EXPORT RIWAYAT PASIEN
        // ========================================

        Route::get(
            '/riwayat-pasien/export-excel',
            RiwayatPasienExportController::class
        )->name('riwayat-pasien.export');

        // ========================================
        // RIWAYAT PASIEN
        // ========================================

        Route::get('/riwayat-pasien', [
            RiwayatPasienController::class,
            'index',
        ])->name('riwayat-pasien.index');

        Route::get('/riwayat-pasien/{id}', [
            RiwayatPasienController::class,
            'show',
        ])->name('riwayat-pasien.show');
    });

// ================================================
// PASIEN
// ================================================

Route::middleware(['auth', 'role:pasien'])
    ->prefix('pasien')
    ->group(function () {

        // Dashboard Pasien
        Route::get('/dashboard', [
            PasienPoliController::class,
            'dashboard',
        ])->name('pasien.dashboard');

        // ========================================
        // PENDAFTARAN
        // ========================================

        Route::get('/daftar', [
            PasienPoliController::class,
            'get',
        ])->name('pasien.daftar');

        Route::post('/daftar', [
            PasienPoliController::class,
            'submit',
        ])->name('pasien.daftar.submit');

        // ========================================
        // RIWAYAT PEMERIKSAAN
        // ========================================

        Route::get('/riwayat', [
            RiwayatPeriksaController::class,
            'index',
        ])->name('pasien.riwayat.index');

        Route::get('/riwayat/{id}', [
            RiwayatPeriksaController::class,
            'show',
        ])->whereNumber('id')
            ->name('pasien.riwayat.show');

        // ========================================
        // PEMBAYARAN PASIEN
        // ========================================

        Route::get('/pembayaran', [
            PasienPembayaranController::class,
            'index',
        ])->name('pasien.pembayaran.index');

        Route::get('/pembayaran/{id}', [
            PasienPembayaranController::class,
            'show',
        ])->name('pasien.pembayaran.show');

        Route::post('/pembayaran/{id}/bukti', [
            PasienPembayaranController::class,
            'unggah',
        ])->whereNumber('id')
            ->name('pasien.pembayaran.unggah');

        Route::get('/pembayaran/{id}/bukti', [
            BuktiPembayaranController::class,
        ])->whereNumber('id')
            ->name('pasien.pembayaran.bukti');
    });
