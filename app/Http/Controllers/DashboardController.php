<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        //buat menghitung data statistik untuk card bagian atas
        $totalCompany = DB::table('companies')->count();
        $totalTeam = 0; 
        $totalPending = DB::table('applications')->where('application_status', 'pending')->count();

        //buat menagambil data pengajuan magang baru
        $daftarDokumen = DB::table('applications')
            ->join('internships', 'applications.id_internship', '=', 'internships.id_internship')
            ->join('companies', 'internships.id_company', '=', 'companies.id_company')
            ->select(
                'companies.company_name',
                'internships.title as internship_title',
                DB::raw("'2026-06-12' as apply_date"), 
                'applications.application_status as status'
            )
            ->get();

        //buat ambil data aktivitas tim mahasiswa
        $daftarFeedback = [];

        return view('index', compact('totalCompany', 'totalTeam', 'totalPending', 'daftarDokumen', 'daftarFeedback'));
    }

    public function validasiMagang(Request $request)
    {
        $searchQuery = $request->query('search');

        //buat ngitung data statistik counter card sesuai approval_status 
        $totalPending = DB::table('internships')->where('approval_status', 'pending')->count();
        $totalAccepted = DB::table('internships')->where('approval_status', 'approved')->count();

        //buat mengambil daftar lowongan & perusahaan
        $queryInternship = DB::table('internships as i')
            ->join('companies as c', 'i.id_company', '=', 'c.id_company')
            ->select(
                'i.id_internship',
                'c.company_name',
                'i.title as internship_title',
                'i.deadline',
                'i.approval_status',
                'i.supporting_document',
                'i.location',
                'c.industry_field'
            );

        if (!empty($searchQuery)) {
            $queryInternship->where(function($q) use ($searchQuery) {
                $q->where('c.company_name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('i.title', 'like', '%' . $searchQuery . '%');
            });
        }

        $daftarLowongan = $queryInternship->orderBy('i.id_internship', 'desc')->get();

        return view('validasiMagang', compact('totalPending', 'totalAccepted', 'daftarLowongan', 'searchQuery'));
    }

    public function prosesValidasi(Request $request)
    {
        $action = $request->query('action');
        $id = $request->query('id');

        if (!$id) {
            return redirect()->back()->with('error', 'ID Lowongan tidak ditemukan.');
        }

        if ($action === 'approve') {
            DB::table('internships')
                ->where('id_internship', $id)
                ->update(['approval_status' => 'approved']);
                
            return redirect()->back()->with('success', 'Lowongan berhasil disetujui dan diterbitkan!');
            
        } elseif ($action === 'reject') {
            DB::table('internships')
                ->where('id_internship', $id)
                ->update(['approval_status' => 'rejected']);
                
            return redirect()->back()->with('success', 'Lowongan kerja resmi ditolak.');
        }

        return redirect()->back()->with('error', 'Aksi tidak valid.');
    }
}
    