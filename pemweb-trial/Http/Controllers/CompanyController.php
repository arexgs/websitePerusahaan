<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    // Helper log aktivitas — tabel: activity_logs, kolom: id_company
    private function logActivity(string $action)
    {
        $companyId = session('type_id');
        if (!$companyId) return;
        DB::table('activity_logs')->insert([
            'id_activity_log' => DB::raw('gen_random_uuid()'),
            'id_company'      => $companyId,
            'action'          => $action,
            'created_at'      => now(),
        ]);
    }

    // GET profil — tabel: companies
    public function getProfile()
    {
        $companyId = session('type_id');

        $company = DB::table('companies')
            ->join('users', 'companies.id_user', '=', 'users.id_user')
            ->where('companies.id_company', $companyId)
            ->select(
                'companies.id_company',
                'companies.company_name',
                'companies.industry_field',
                'companies.description',
                'companies.contact',
                'companies.company_logo',
                'users.email'
            )
            ->first();

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Perusahaan tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $company]);
    }

    // POST update profil
    public function updateProfile(Request $request)
    {
        $companyId = session('type_id');
        $data = $request->json()->all();

        $name        = trim($data['company_name'] ?? '');
        $industry    = trim($data['industry_field'] ?? '');
        $description = trim($data['description'] ?? '');
        $contact     = trim($data['contact'] ?? '');

        if (empty($name)) {
            return response()->json(['success' => false, 'message' => 'Nama perusahaan tidak boleh kosong'], 400);
        }

        DB::table('companies')->where('id_company', $companyId)->update([
            'company_name'   => $name,
            'industry_field' => $industry,
            'description'    => $description,
            'contact'        => $contact,
        ]);

        session(['user_name' => $name]);
        $this->logActivity('Update profil perusahaan');

        return response()->json(['success' => true, 'message' => 'Profil berhasil disimpan']);
    }

    // POST upload logo
    public function updateLogo(Request $request)
    {
        $companyId = session('type_id');

        if (!$request->hasFile('logo')) {
            return response()->json(['success' => false, 'message' => 'File logo tidak ditemukan'], 400);
        }

        $file    = $request->file('logo');
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext     = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, $allowed)) {
            return response()->json(['success' => false, 'message' => 'Format harus JPG, PNG, atau WebP'], 400);
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return response()->json(['success' => false, 'message' => 'Ukuran maksimal 2MB'], 400);
        }

        $filename = 'logo_' . $companyId . '_' . time() . '.' . $ext;
        $file->move(public_path('uploads/logos'), $filename);

        DB::table('companies')->where('id_company', $companyId)->update([
            'company_logo' => 'uploads/logos/' . $filename,
        ]);

        $this->logActivity('Ganti logo perusahaan');

        return response()->json([
            'success'  => true,
            'message'  => 'Logo berhasil diupload!',
            'logo_url' => asset('uploads/logos/' . $filename)
        ]);
    }

    // POST update pengaturan
    public function updateSettings(Request $request)
    {
        $companyId = session('type_id');
        $data      = $request->json()->all();

        $username = trim($data['username'] ?? '');
        $phone    = trim($data['phone'] ?? '');

        DB::table('companies')->where('id_company', $companyId)->update([
            'company_name' => $username,
            'contact'      => $phone,
        ]);

        session(['user_name' => $username]);
        $this->logActivity('Update pengaturan akun');

        return response()->json(['success' => true, 'message' => 'Pengaturan berhasil disimpan']);
    }

    // POST ganti password — tetap update di tabel users
    public function updatePassword(Request $request)
    {
        $userId = session('user_id');
        $data   = $request->json()->all();

        $oldPassword = $data['old_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';

        if (empty($oldPassword) || empty($newPassword)) {
            return response()->json(['success' => false, 'message' => 'Password lama dan baru wajib diisi'], 400);
        }

        if (strlen($newPassword) < 6) {
            return response()->json(['success' => false, 'message' => 'Password baru minimal 6 karakter'], 400);
        }

        $users = DB::table('users')->where('id_user', $userId)->first();

        if (!$users || !Hash::check($oldPassword, $users->password)) {
            return response()->json(['success' => false, 'message' => 'Password lama salah'], 401);
        }

        DB::table('users')->where('id_user', $userId)->update([
            'password' => Hash::make($newPassword),
        ]);

        $this->logActivity('Ubah password akun');

        return response()->json(['success' => true, 'message' => 'Password berhasil diubah']);
    }

    // GET aktivitas — tabel: activity_logs, filter by id_company
    public function getActivities()
    {
        $companyId = session('type_id');
        $activities = DB::table('activity_logs')
            ->where('id_company', $companyId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['success' => true, 'data' => $activities]);
    }
}