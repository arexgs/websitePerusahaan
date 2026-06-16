<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\TeamController; // <-- DIUBAH: Mengarah ke folder utama

// 1. Rute Halaman Utama & Dashboard
Route::get('/', [DashboardController::class, 'index']);
Route::get('/dashboard', [DashboardController::class, 'index']);

// 2. Rute Validasi Magang
Route::get('/validasi-magang', [DashboardController::class, 'validasiMagang']);
Route::get('/validasi-magang/proses', [DashboardController::class, 'prosesValidasi']);

// 3. Rute Daftar Perusahaan 
Route::get('/daftar-perusahaan', [CompanyController::class, 'index']);
Route::get('/daftar-perusahaan/hapus/{id}', [CompanyController::class, 'destroy']);
// 4. Rute Daftar Team (Langsung memanggil TeamController tanpa ->name)
Route::get('/daftar-team', [TeamController::class, 'index']);

// Rute untuk menampilkan halaman pengaturan
Route::get('/pengaturan', function () {
    return view('pengaturan');
});