<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\PengaturanController;

/*
|--------------------------------------------------------------------------
| 1. Rute Publik & Autentikasi (Landing Page & Auth)
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('index'));
Route::get('/register', fn() => view('register'));

// Aksi Autentikasi (Mendukung endpoint web biasa dan endpoint API JavaScript kamu)
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
// Menggunakan rute '/dashboard-admin' sesuai dengan redirect LoginController pasca perbaikan JS
Route::get('/dashboard-admin', [DashboardController::class, 'dashboardAdmin']);
Route::get('/admin-dashboard', [DashboardController::class, 'adminIndex']); // Menjaga kompatibilitas jika ada link lama

// Validasi Lowongan Magang
Route::get('/validasi-magang', [DashboardController::class, 'validasiMagang']);
Route::get('/validasi-magang/proses', [DashboardController::class, 'prosesValidasi']);

// Manajemen Daftar Perusahaan Mitra (Menggunakan indexAdmin agar tidak bentrok dengan dashboard company)
Route::get('/daftar-perusahaan', [CompanyController::class, 'indexAdmin']);
Route::get('/daftar-perusahaan/hapus/{id}', [CompanyController::class, 'destroy']);

// Manajemen Daftar Team Mahasiswa
Route::get('/daftar-team', [TeamController::class, 'indexAdmin']);
Route::delete('/daftar-team/hapus/{id}', [TeamController::class, 'destroy']);


/*
|--------------------------------------------------------------------------
| 3. Rute Khusus Perusahaan (Company Dashboard)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [DashboardController::class, 'dashboardAdmin']);


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