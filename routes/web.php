<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\TeamController; 
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PengaturanController;

/*
|--------------------------------------------------------------------------
| 1. Rute Public / Umum (Landing Page & Auth)
|--------------------------------------------------------------------------
*/

// Halaman Utama Website (Landing Page) -> Memanggil file index.blade.php
Route::get('/', function () {
    return view('index');
});

// Endpoint API Proses Login dari Form Landing Page
Route::post('/api/login', [LoginController::class, 'store']);


/*
|--------------------------------------------------------------------------
| 2. Rute Khusus Admin (Diarahkan dari LoginController: /dashboard-admin)
|--------------------------------------------------------------------------
*/

// Halaman Utama Dashboard Admin
Route::get('/dashboard-admin', [DashboardController::class, 'dashboardAdmin']);

// Validasi Magang (Daftar Lowongan & Aksi Approve/Reject)
Route::get('/validasi-magang', [DashboardController::class, 'validasiMagang']);
Route::get('/validasi-magang/proses', [DashboardController::class, 'prosesValidasi']);

// Manajemen Daftar Perusahaan Mitra oleh Admin
Route::get('/daftar-perusahaan', [CompanyController::class, 'indexAdmin']); // DIUBAH: method disesuaikan agar tidak tabrakan
Route::get('/daftar-perusahaan/hapus/{id}', [CompanyController::class, 'destroy']);

// Manajemen Daftar Team Mahasiswa oleh Admin
Route::get('/daftar-team', [TeamController::class, 'indexAdmin']); // DIUBAH: method disesuaikan agar tidak tabrakan


/*
|--------------------------------------------------------------------------
| 3. Rute Khusus Perusahaan / Company (Diarahkan dari LoginController: /dashboard)
|--------------------------------------------------------------------------
*/

// Halaman Utama Dashboard Perusahaan
Route::get('/dashboard', [CompanyController::class, 'index']);


/*
|--------------------------------------------------------------------------
| 4. Rute Fitur Tambahan & API Pendukung
|--------------------------------------------------------------------------
*/

// Pengaturan Profil (Ditambahkan proteksi middleware session jika diperlukan)
Route::get('/pengaturan', [PengaturanController::class, 'indexPengaturan']); 
Route::put('/pengaturan/simpan', [PengaturanController::class, 'simpan']);

// Endpoint API Ajax untuk Modal Detail (Pop-up)
Route::get('/api/perusahaan/{id}', [CompanyController::class, 'getDetailJson']);
Route::get('/team/{id}', [TeamController::class, 'getDetailTeam']);