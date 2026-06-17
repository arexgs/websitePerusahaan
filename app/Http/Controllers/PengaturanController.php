<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PengaturanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Tangkap query pencarian jika nanti dibutuhkan di halaman pengaturan
        $searchQuery = $request->query('search', '');

        // 2. Mengambil 5 aktivitas terbaru dari admin yang sedang login secara dinamis
        // Menggunakan Auth::id() untuk mengambil ID admin yang sedang aktif aman dari error null
        $adminLogs = DB::table('admin_activity_logs') 
            ->where('admin_id', Auth::id())   
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 3. Menghitung data statistik counter untuk card info di atas tabel/halaman
        $totalPending = DB::table('internships')
            ->where('approval_status', 'pending')
            ->count();

        $totalAccepted = DB::table('internships')
            ->where('approval_status', 'approved')
            ->count();

        // 4. Return ke view 'pengaturan' dengan membawa variabel kembarannya
        return view('pengaturan', compact('adminLogs', 'totalPending', 'totalAccepted', 'searchQuery'));
    }

    public function simpan(Request $request)
    {
        // Validasi input form pengaturan akun
        $request->validate([
            'admin_name' => 'required|string|max:255',
            'validation_method' => 'required|in:manual,auto',
            'document_expiration' => 'required|in:7,14,30',
        ]);

        // [TULIS KODE UPDATE PARAMETER KAMU DI SINI]
        // Contoh jika disimpan ke tabel konfigurasi web:
        // DB::table('system_settings')->where('id', 1)->update([...]);

        // Tambahkan riwayat baru ke log aktivitas secara otomatis saat admin klik simpan
        DB::table('admin_activity_logs')->insert([
            'admin_id' => Auth::id(),
            'activity_description' => 'Mengubah konfigurasi sistem dan parameter kontrol web',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}