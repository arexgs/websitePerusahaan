<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// ── Controller Autentikasi ──
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordController;

// ── Controller Dashboard & Umum ──
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\UploadController;

// ── Controller Entitas (Company & Team) ──
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DaftarCompanyController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\DaftarTeamController;
use App\Http\Controllers\InternshipController;

/*
|--------------------------------------------------------------------------
| 1. Rute Publik & Autentikasi (Landing Page & Auth)
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('index'));
Route::get('/register', fn() => view('register'));

// Aksi Autentikasi (Mendukung endpoint web biasa dan endpoint API JavaScript)
Route::post('/login', [LoginController::class, 'store']);
Route::post('/api/login', [LoginController::class, 'store']); 
Route::post('/register', [RegisterController::class, 'store']);
Route::post('/logout', function (Request $request) {
    $request->session()->flush();
    return response()->json(['success' => true]);
});

/*
|--------------------------------------------------------------------------
| 2. Rute Khusus Admin
|--------------------------------------------------------------------------
*/
// Dashboard Admin (Menjaga kompatibilitas jika ada link lama)
Route::get('/dashboard-admin', [DashboardAdminController::class, 'dashboardAdmin']);
Route::get('/admin-dashboard', [DashboardController::class, 'adminIndex']); // Dari file 1

// Validasi Lowongan Magang
Route::get('/validasi-magang', [DashboardAdminController::class, 'validasiMagang']);
Route::get('/validasi-magang/proses', [DashboardAdminController::class, 'prosesValidasi']);

// Manajemen Daftar Perusahaan Mitra
Route::get('/daftar-perusahaan', [DaftarCompanyController::class, 'indexAdmin']);
Route::get('/daftar-perusahaan/hapus/{id}', [DaftarCompanyController::class, 'destroy']);

// Manajemen Daftar Team Mahasiswa
Route::get('/daftar-team', [DaftarTeamController::class, 'indexAdmin']);
Route::delete('/daftar-team/hapus/{id}', [DaftarTeamController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| 3. Rute Khusus Perusahaan (Company Dashboard)
|--------------------------------------------------------------------------
*/
// Arahkan ke method dashboard company sesuai arsitektur file 2
Route::get('/dashboard', [DashboardController::class, 'indexCompany']);

/*
|--------------------------------------------------------------------------
| 4. Fitur Pengaturan Profil & Fitur Tambahan
|--------------------------------------------------------------------------
*/
Route::get('/pengaturan', [PengaturanController::class, 'indexPengaturan']); 
Route::put('/pengaturan/simpan', [PengaturanController::class, 'simpan']);

// Lupa Password / Reset Password
Route::get('/forgot-password', [PasswordController::class, 'forgotPage']);
Route::post('/forgot-password', [PasswordController::class, 'sendReset']);
Route::get('/reset-password', [PasswordController::class, 'resetPage']);
Route::post('/reset-password', [PasswordController::class, 'doReset']);

// Upload File ke Supabase Storage via Controller
Route::post('/upload-supabase', [UploadController::class, 'uploadToSupabase'])->name('upload.supabase');

/*
|--------------------------------------------------------------------------
| 5. API Pendukung (AJAX / Fetch)
|--------------------------------------------------------------------------
*/
// API untuk Keperluan Dashboard & Profil Perusahaan
Route::get('/api/dashboard', [DashboardController::class, 'getData']);
Route::get('/api/profile', [CompanyController::class, 'getProfile']);
Route::post('/api/profile/update', [CompanyController::class, 'updateProfile']);
Route::post('/api/profile/password', [CompanyController::class, 'updatePassword']);
Route::post('/api/profile/logo', [CompanyController::class, 'updateLogo']);
Route::post('/api/settings/update', [CompanyController::class, 'updateSettings']);
Route::get('/api/activities', [CompanyController::class, 'getActivities']);

// API untuk Manajemen Lowongan Magang & Status Pelamar
Route::post('/api/internship/store', [InternshipController::class, 'store']);
Route::post('/api/internship/update', [InternshipController::class, 'update']);
Route::post('/api/internship/delete', [InternshipController::class, 'destroy']);
Route::post('/api/internship/applicant-status', [InternshipController::class, 'updateApplicantStatus']);

// API untuk Modal Detail Pop-up (Admin & General)
Route::get('/api/perusahaan/{id}', [CompanyController::class, 'getDetailJson']);
Route::get('/team/{id}', [TeamController::class, 'getDetailTeam']);