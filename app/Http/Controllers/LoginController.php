<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        try {
            // 1. Ambil data JSON dari request JavaScript
            $data     = $request->json()->all();
            $email    = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');
            $mode     = trim($data['mode'] ?? 'company');

            if (empty($email) || empty($password)) {
                return response()->json(['success' => false, 'message' => 'Semua field harus diisi'], 400);
            }

            // 2. Tentukan role berdasarkan mode login halaman front-end
            $role = $mode === 'admin' ? 'admin' : 'company';
            
            // Cari user berdasarkan email dan role di database Supabase
            $user = DB::table('users')->where('email', $email)->where('role', $role)->first();

            // 3. Validasi Keberadaan User & Password (Hybrid: support Hash Laravel & teks dummy)
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Email tidak terdaftar sebagai ' . $role], 401);
            }

            $passwordMatches = false;
            if (Hash::check($password, $user->password)) {
                $passwordMatches = true;
            } else if ($password === $user->password) {
                $passwordMatches = true;
            }

            if (!$passwordMatches) {
                return response()->json(['success' => false, 'message' => 'Email atau password salah'], 401);
            }

            // 4. Cari Detail Data berdasarkan Role (UUID dicasting ke string agar aman)
            if ($role === 'company') {
                $detail = DB::table('companies')->where('id_user', (string) $user->id_user)->first();
                $name   = $detail?->company_name ?? 'Company Name Tidak Diatur';
                $typeId = $detail?->id_company ?? null;
            } else {
                $detail = DB::table('admins')->where('id_user', (string) $user->id_user)->first();
                $name   = 'Admin';
                $typeId = $detail?->id_admin ?? null;
            }

            // 5. Set Session Laravel
            session([
                'user_id'    => (string) $user->id_user,
                'user_email' => $user->email,
                'user_name'  => $name,
                'user_type'  => $role,
                'type_id'    => $typeId ? (string) $typeId : null,
            ]);

            // 6. Catat Log Aktivitas (Sekarang aman karena $typeId sudah didefinisikan di atas)
            if ($role === 'company' && !empty($typeId)) {
                DB::table('activity_logs')->insert([
                    'id_activity_log' => (string) Str::uuid(), 
                    'id_company'      => (string) $typeId,
                    'action'          => 'Login Perusahaan',
                    'created_at'      => now(),
                ]);
            } elseif ($role === 'admin' && !empty($typeId)) {
                DB::table('admin_logs')->insert([
                    'id_admin_log' => (string) Str::uuid(),
                    'id_admin'     => (string) $typeId,
                    'action'       => 'Admin Login',
                    'created_at'   => now(),
                ]);
            }

            // 7. Tentukan halaman redirect
            $redirect = $role === 'admin' ? '/dashboard-admin' : '/dashboard';

            return response()->json([
                'success'   => true,
                'message'   => 'Login berhasil!',
                'user_name' => $name,
                'redirect'  => $redirect
            ]);

        } catch (Exception $e) {
            // JIKA ADA CODE YANG SALAH ATAU DATABASE ERROR, DIAKAN MENTAL KE SINI
            return response()->json([
                'success' => false,
                'message' => 'Sistem Error: ' . $e->getMessage()
            ], 500);
        }
    }
}