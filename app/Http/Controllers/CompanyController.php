<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class companyController extends Controller
{
    public function index(Request $request)
    {
        // Tangkap query pencarian
        $searchQuery = $request->query('search', '');

        // A. Tren Registrasi Perusahaan 6 Bulan Terakhir
        $resChartComp = DB::table('users as u')
            ->join('companies as c', 'u.id_user', '=', 'c.id_user')
            ->select(
                DB::raw("TO_CHAR(u.created_at, 'Mon YYYY') AS bulan"),
                DB::raw("COUNT(c.id_company) AS total_reg")
            )
            ->where('u.created_at', '>=', DB::raw("NOW() - INTERVAL '6 MONTH'"))
            ->groupBy(
                DB::raw("EXTRACT(YEAR FROM u.created_at)"), 
                DB::raw("EXTRACT(MONTH FROM u.created_at)"), 
                DB::raw("TO_CHAR(u.created_at, 'Mon YYYY')")
            )
            ->orderBy(DB::raw("EXTRACT(YEAR FROM u.created_at)"), 'ASC')
            ->orderBy(DB::raw("EXTRACT(MONTH FROM u.created_at)"), 'ASC')
            ->get();

        // PASTIKAN: Menggunakan CamelCase (C besar) agar sama dengan yang dipanggil di Blade View
        $monthsCompanies = [];
        $countsCompanies = [];
        foreach ($resChartComp as $row) {
            $monthsCompanies[] = $row->bulan;
            $countsCompanies[] = $row->total_reg;
        }

        // B. Tren Pertumbuhan Lowongan Magang 6 Bulan Terakhir
        $resChartIntern = DB::table('internships')
            ->select(
                DB::raw("TO_CHAR(deadline, 'Mon YYYY') AS bulan"),
                DB::raw("COUNT(id_internship) AS total_intern")
            )
            ->where('deadline', '>=', DB::raw("NOW() - INTERVAL '6 MONTH'"))
            ->groupBy(
                DB::raw("EXTRACT(YEAR FROM deadline)"), 
                DB::raw("EXTRACT(MONTH FROM deadline)"), 
                DB::raw("TO_CHAR(deadline, 'Mon YYYY')")
            )
            ->orderBy(DB::raw("EXTRACT(YEAR FROM deadline)"), 'ASC')
            ->orderBy(DB::raw("EXTRACT(MONTH FROM deadline)"), 'ASC')
            ->get();

        $monthsIntern = [];
        $countsIntern = [];
        foreach ($resChartIntern as $row) {
            $monthsIntern[] = $row->bulan;
            $countsIntern[] = $row->total_intern;
        }

        // C. Counter Peningkatan Bulan Ini
        $compThisMonth = DB::table('companies as c')
            ->join('users as u', 'c.id_user', '=', 'u.id_user')
            ->whereRaw("EXTRACT(MONTH FROM u.created_at) = EXTRACT(MONTH FROM NOW())")
            ->whereRaw("EXTRACT(YEAR FROM u.created_at) = EXTRACT(YEAR FROM NOW())")
            ->count();

        $internThisMonth = DB::table('internships')
            ->whereRaw("EXTRACT(MONTH FROM deadline) = EXTRACT(MONTH FROM NOW())")
            ->whereRaw("EXTRACT(YEAR FROM deadline) = EXTRACT(YEAR FROM NOW())")
            ->count();

        // 3. QUERY UTAMA: DAFTAR PERUSAHAAN & JUMLAH LOWONGAN
        $querycompaniesList = DB::table('companies as c')
            ->join('users as u', 'c.id_user', '=', 'u.id_user')
            ->select(
                'c.id_company',
                'c.company_name', 
                'c.industry_field',
                'c.contact',
                'u.email',
                'u.created_at as create_at', 
                DB::raw('(SELECT COUNT(*) FROM internships i WHERE i.id_company = c.id_company) AS total_lowongan')
            );

        if (!empty($searchQuery)) {
            $querycompaniesList->where(function ($q) use ($searchQuery) {
                $q->where('c.company_name', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('c.industry_field', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $daftarPerusahaan = $querycompaniesList->orderBy('c.id_company', 'DESC')->get();

        // PASTIKAN: Di dalam compact() menggunakan 'monthsCompanies' dan 'countsCompanies'
        return view('daftarPerusahaan', compact(
            'searchQuery',
            'monthsCompanies',
            'countsCompanies',
            'monthsIntern',
            'countsIntern',
            'compThisMonth',
            'internThisMonth',
            'daftarPerusahaan'
        ));
    }

    public function destroy($id)
    {
        $deleted = DB::table('companies')->where('id_company', $id)->delete();

        if ($deleted) {
            return redirect('/daftar-perusahaan')->with('success', 'Kemitraan perusahaan berhasil dihapus.');
        } else {
            return redirect('/daftar-perusahaan')->with('error', 'Gagal menghapus data perusahaan.');
        }
    }
}