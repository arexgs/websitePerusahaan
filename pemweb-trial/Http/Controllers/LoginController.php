<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        $email    = trim($request->input('email', ''));
        $password = trim($request->input('password', ''));
        $mode     = trim($request->input('mode', 'company'));

        if (empty($email) || empty($password)) {
            return response()->json(['success' => false, 'message' => 'Semua field harus diisi'], 400);
        }

        $role = $mode === 'admin' ? 'admin' : 'company';
        $user = DB::table('users')->where('email', $email)->where('role', $role)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Email atau password salah'], 401);
        }

        if ($role === 'company') {
            // Tabel: companies, kolom: id_company, company_name
            $detail = DB::table('companies')->where('id_user', $user->id_user)->first();
            $name   = $detail?->company_name ?? 'Company Name Tidak Diatur';
            $typeId = $detail?->id_company ?? null;
        } else {
            // Tabel: admins, kolom: id_admin
            $detail = DB::table('admins')->where('id_user', $user->id_user)->first();
            $name   = 'Admin';
            $typeId = $detail?->id_admin ?? null;
        }

        // Simpan ke session
        session([
            'user_id'    => $user->id_user,
            'user_email' => $user->email,
            'user_name'  => $name,
            'user_type'  => $role,
            'type_id'    => $typeId,
        ]);

        // Catat activity log (tabel: activity_logs, relasi ke companies via id_company)
        if ($role === 'company' && !empty($typeId)) {
            DB::table('activity_logs')->insert([
                'id_activity_log' => DB::raw('gen_random_uuid()'),
                'id_company'      => $typeId,
                'action'          => 'Login',
                'created_at'      => now(),
            ]);
        }

        $redirect = $role === 'admin' ? '/admin-dashboard' : '/dashboard';

        return response()->json([
            'success'   => true,
            'message'   => 'Login berhasil!',
            'user_name' => $name,
            'redirect'  => $redirect
        ]);
    }
}