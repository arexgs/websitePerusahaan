<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Wajib ada ini untuk menembak ke Supabase

class UploadController extends Controller
{
    public function uploadToSupabase(Request $request)
    {
        // 1. Validasi file
        $request->validate([
            'foto_produk' => 'required|image|max:2048', 
        ]);

        // 2. Tangkap file dari form web
        $file = $request->file('foto_produk');
        $namaFile = time() . '_' . $file->getClientOriginalName(); 
        $fileKonten = file_get_contents($file->getRealPath()); 

        // 3. Masukkan Kredensial Supabase Kamu di Sini
        $supabaseUrl = 'https://qdcjgonjjrxhghlbdarz.supabase.co'; // Masukkan URL Supabase proyekmu
        $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFkY2pnb25qanJ4aGdobGJkYXJ6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODEyNTkzMDMsImV4cCI6MjA5NjgzNTMwM30.PbvvDNzw2X0evntV2Ksw_tbypR2DjE8R4r7nUW7DHeM';     // Masukkan Anon Key Supabase proyekmu
        $namaBucket  = 'logo-comp'; // GANTI dengan nama bucket yang kamu buat di Supabase tadi

        // Folder tujuan otomatis dibuat di Supabase (di dalam folder 'uploads')
        $urlTujuan = "{$supabaseUrl}/storage/v1/object/{$namaBucket}/uploads/{$namaFile}";

        // 4. Kirim ke Supabase
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $supabaseKey,
            'API-KEY'       => $supabaseKey,
            'Content-Type'  => $file->getClientMimeType(),
        ])->withBody($fileKonten, $file->getClientMimeType())->put($urlTujuan);

        // 5. Cek Berhasil / Gagal
        if ($response->successful()) {
            return back()->with('success', 'Mantap! File berhasil masuk ke Supabase.');
        } else {
            return back()->with('error', 'Gagal upload: ' . $response->body());
        }
    }
}