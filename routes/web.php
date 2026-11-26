<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanExportController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PesananController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.attempt');
});

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard.home');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | F1 — Pencatatan pesanan
    |--------------------------------------------------------------------------
    */
    Route::resource('pesanan', PesananController::class);

    /*
    |--------------------------------------------------------------------------
    | F2 — Pembayaran, DP, dan pembatalan H-1
    |--------------------------------------------------------------------------
    */
    Route::post('pesanan/{pesanan}/batal', [PesananController::class, 'batalkan'])
        ->name('pesanan.batal');

    Route::post('pesanan/{pesanan}/pembayaran', [PembayaranController::class, 'store'])
        ->name('pembayaran.store');

    Route::put('pembayaran/{pembayaran}', [PembayaranController::class, 'update'])
        ->name('pembayaran.update');

    Route::delete('pembayaran/{pembayaran}', [PembayaranController::class, 'destroy'])
        ->name('pembayaran.destroy');

    /*
    |--------------------------------------------------------------------------
    | F4 — Jadwal pesanan
    |--------------------------------------------------------------------------
    | Pemilik dan Pencatat sama-sama boleh melihat.
    */
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');

    /*
    |--------------------------------------------------------------------------
    | F3 — Pencatatan pengeluaran
    |--------------------------------------------------------------------------
    | Lihat & tambah: Pemilik dan Pencatat.
    | Ubah & hapus: hanya Pemilik (dijaga di controller dan FormRequest).
    */
    Route::resource('pengeluaran', PengeluaranController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | F5 — Laporan keuangan bulanan
    |--------------------------------------------------------------------------
    | Hanya Pemilik, dijaga middleware role pada group route.
    */
    Route::middleware('role:pemilik')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

        // Ekspor laporan bulanan: Excel dan PDF.
        Route::get('/laporan/export', [LaporanExportController::class, 'excel'])
            ->name('laporan.export.excel');

        Route::get('/laporan/export/pdf', [LaporanExportController::class, 'pdf'])
            ->name('laporan.export.pdf');

        // Ringkasan AI: hanya angka agregat yang dikirim ke penyedia AI.
        Route::post('/laporan/ringkasan-ai', [LaporanController::class, 'ringkasanAi'])
            ->middleware('throttle:6,1')
            ->name('laporan.ringkasan-ai');
    });
});
