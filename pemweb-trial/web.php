<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\PasswordController;

// ── Auth pages ──
Route::get('/', fn() => view('index'));
Route::get('/register', fn() => view('register'));

// ── Auth actions ──
Route::post('/login', [LoginController::class, 'store']);
Route::post('/register', [RegisterController::class, 'store']);
Route::post('/logout', function (Request $request) {
    $request->session()->flush();
    return response()->json(['success' => true]);
});

// ── Dashboard ──
Route::get('/dashboard', [DashboardController::class, 'index']);

// ── API ──
Route::get('/api/dashboard', [DashboardController::class, 'getData']);
Route::get('/api/profile', [CompanyController::class, 'getProfile']);
Route::post('/api/profile/update', [CompanyController::class, 'updateProfile']);
Route::post('/api/profile/password', [CompanyController::class, 'updatePassword']);
Route::post('/api/profile/logo', [CompanyController::class, 'updateLogo']);
Route::post('/api/internship/store', [InternshipController::class, 'store']);
Route::post('/api/internship/update', [InternshipController::class, 'update']);
Route::post('/api/internship/delete', [InternshipController::class, 'destroy']);
Route::post('/api/internship/applicant-status', [InternshipController::class, 'updateApplicantStatus']);
Route::post('/api/settings/update', [CompanyController::class, 'updateSettings']);
Route::get('/api/activities', [CompanyController::class, 'getActivities']);

// ── Password reset ──
Route::get('/forgot-password', [PasswordController::class, 'forgotPage']);
Route::post('/forgot-password', [PasswordController::class, 'sendReset']);
Route::get('/reset-password', [PasswordController::class, 'resetPage']);
Route::post('/reset-password', [PasswordController::class, 'doReset']);
use App\Http\Controllers\UploadController;

// Jalur untuk memproses upload ke Supabase
Route::post('/upload-supabase', [UploadController::class, 'uploadToSupabase'])->name('upload.supabase');