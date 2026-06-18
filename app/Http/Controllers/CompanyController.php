<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Services\SupabaseStorageService;

class CompanyController extends Controller
{
    private SupabaseStorageService $storage;

    public function __construct(SupabaseStorageService $storage)
    {
        $this->storage = $storage;
    }

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

        // Convert path logo (tersimpan di DB) jadi public URL Supabase
        if (!empty($company->company_logo)) {
            $company->company_logo = $this->storage->publicUrl('logo-comp', $company->company_logo);
        }

        return response()->json(['success' => true, 'data' => $company]);
    }

    public function updateProfile(Request $request)
    {
        $companyId   = session('type_id');
        $data        = $request->json()->all();
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

    public function updateLogo(Request $request)
    {
        $companyId = session('type_id');

        if (!$request->hasFile('logo')) {
            return response()->json(['success' => false, 'message' => 'File logo tidak ditemukan'], 400);
        }

        $file = $request->file('logo');

        $error = $this->storage->validateFile($file, ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
        if ($error) {
            return response()->json(['success' => false, 'message' => $error], 400);
        }

        // Upload ke bucket Supabase 'logo-comp', dikelompokkan per id_company
        $result = $this->storage->upload($file, 'logo-comp', $companyId);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => 'Gagal upload logo: ' . $result['error']], 500);
        }

        // Hapus logo lama dari Supabase kalau ada, supaya storage tidak menumpuk
        $old = DB::table('companies')->where('id_company', $companyId)->first();
        if (!empty($old?->company_logo)) {
            $this->storage->delete('logo-comp', $old->company_logo);
        }

        DB::table('companies')->where('id_company', $companyId)->update([
            'company_logo' => $result['path'],
        ]);

        $this->logActivity('Ganti logo perusahaan');

        return response()->json([
            'success'  => true,
            'message'  => 'Logo berhasil diupload!',
            'logo_url' => $result['public_url'],
        ]);
    }

    public function updateSettings(Request $request)
    {
        $companyId = session('type_id');
        $data      = $request->json()->all();
        $username  = trim($data['username'] ?? '');
        $phone     = trim($data['phone'] ?? '');

        DB::table('companies')->where('id_company', $companyId)->update([
            'company_name' => $username,
            'contact'      => $phone,
        ]);

        session(['user_name' => $username]);
        $this->logActivity('Update pengaturan akun');

        return response()->json(['success' => true, 'message' => 'Pengaturan berhasil disimpan']);
    }

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

    public function getActivities()
    {
        $companyId  = session('type_id');
        $activities = DB::table('activity_logs')
            ->where('id_company', $companyId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['success' => true, 'data' => $activities]);
    }

    // FIX #3: Method index untuk daftarPerusahaan dengan semua variabel chart yang dibutuhkan blade
    public function index(Request $request)
    {
        if (!session('user_id') || session('user_type') !== 'admin') {
            return redirect('/');
        }

        $searchQuery = $request->input('search');

        // Tren registrasi perusahaan 6 bulan terakhir
        $chartCompanies = DB::table('companies')
            ->join('users', 'companies.id_user', '=', 'users.id_user')
            ->select(
                DB::raw("TO_CHAR(users.created_at, 'Mon YYYY') as bulan"),
                DB::raw("COUNT(companies.id_company) as total_reg"),
                DB::raw("DATE_TRUNC('month', users.created_at) as bulan_sort")
            )
            ->where('users.created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy(
                DB::raw("TO_CHAR(users.created_at, 'Mon YYYY')"),
                DB::raw("DATE_TRUNC('month', users.created_at)")
            )
            ->orderBy('bulan_sort', 'asc')
            ->get();

        $monthsCompanies = $chartCompanies->pluck('bulan')->toArray();
        $countsCompanies = $chartCompanies->pluck('total_reg')->toArray();

        // Tren lowongan magang 6 bulan terakhir
        $chartIntern = DB::table('internships')
            ->select(
                DB::raw("TO_CHAR(deadline, 'Mon YYYY') as bulan"),
                DB::raw("COUNT(id_internship) as total_intern"),
                DB::raw("DATE_TRUNC('month', deadline) as bulan_sort")
            )
            ->where('deadline', '>=', Carbon::now()->subMonths(6))
            ->groupBy(
                DB::raw("TO_CHAR(deadline, 'Mon YYYY')"),
                DB::raw("DATE_TRUNC('month', deadline)")
            )
            ->orderBy('bulan_sort', 'asc')
            ->get();

        $monthsIntern = $chartIntern->pluck('bulan')->toArray();
        $countsIntern = $chartIntern->pluck('total_intern')->toArray();

        // Counter bulan ini
        $compThisMonth   = DB::table('companies')
            ->join('users', 'companies.id_user', '=', 'users.id_user')
            ->whereMonth('users.created_at', Carbon::now()->month)
            ->whereYear('users.created_at', Carbon::now()->year)
            ->count();

        $internThisMonth = DB::table('internships')
            ->whereMonth('deadline', Carbon::now()->month)
            ->whereYear('deadline', Carbon::now()->year)
            ->count();

        // Query daftar perusahaan
        $query = DB::table('companies')
            ->join('users', 'companies.id_user', '=', 'users.id_user')
            ->leftJoin(
                DB::raw('(SELECT id_company, COUNT(*) as total_lowongan FROM internships GROUP BY id_company) as intern_count'),
                'companies.id_company', '=', 'intern_count.id_company'
            )
            ->select(
                'companies.id_company',
                'companies.company_name',
                'companies.industry_field',
                'companies.contact',
                'users.email',
                'users.created_at as create_at',
                DB::raw('COALESCE(intern_count.total_lowongan, 0) as total_lowongan')
            );

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('companies.company_name', 'ILIKE', '%' . $searchQuery . '%')
                  ->orWhere('companies.industry_field', 'ILIKE', '%' . $searchQuery . '%');
            });
        }

        $daftarPerusahaan = $query->orderBy('users.created_at', 'desc')->get();

        return view('daftarPerusahaan', compact(
            'daftarPerusahaan',
            'searchQuery',
            'monthsCompanies',
            'countsCompanies',
            'monthsIntern',
            'countsIntern',
            'compThisMonth',
            'internThisMonth'
        ));
    }

    // Hapus perusahaan
    public function destroy($id)
    {
        if (!session('user_id') || session('user_type') !== 'admin') {
            return redirect('/');
        }

        // Hapus data terkait dulu
        $company = DB::table('companies')->where('id_company', $id)->first();
        if (!$company) {
            return redirect('/daftar-perusahaan')->with('error', 'Perusahaan tidak ditemukan.');
        }

        // Hapus internships milik perusahaan ini, lalu perusahaannya, lalu user-nya
        DB::table('internships')->where('id_company', $id)->delete();
        DB::table('activity_logs')->where('id_company', $id)->delete();
        DB::table('companies')->where('id_company', $id)->delete();
        DB::table('users')->where('id_user', $company->id_user)->delete();

        return redirect('/daftar-perusahaan')->with('success', 'Perusahaan berhasil dihapus.');
    }
}