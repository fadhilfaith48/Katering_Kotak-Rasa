<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
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
    | Placeholder modul tahap berikutnya
    |--------------------------------------------------------------------------
    | Dibuat agar navigasi bawah tidak error sebelum fitur F2-F5 dibangun.
    */
    Route::view('/jadwal', 'placeholder', [
        'judul' => 'Jadwal',
        'deskripsi' => 'Jadwal pesanan per tanggal acara akan tersedia pada tahap berikutnya.',
    ])->name('jadwal.index');

    Route::view('/pengeluaran', 'placeholder', [
        'judul' => 'Pengeluaran',
        'deskripsi' => 'Pencatatan pengeluaran usaha akan tersedia pada tahap berikutnya.',
    ])->name('pengeluaran.index');

    // Laporan keuangan hanya untuk Pemilik.
    Route::middleware('role:pemilik')->group(function () {
        Route::view('/laporan', 'placeholder', [
            'judul' => 'Laporan',
            'deskripsi' => 'Laporan keuangan bulanan akan tersedia pada tahap berikutnya.',
        ])->name('laporan.index');
    });
});
