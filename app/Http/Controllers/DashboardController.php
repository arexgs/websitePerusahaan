<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function dashboardAdmin() 
    {
        // 1. Menghitung total data statistik counter card
        $totalCompany = DB::table('companies')->count();
        $totalTeam = DB::table('teams')->count();
        $totalPending = DB::table('internships')->where('approval_status', 'pending')->count();

        // 2. Ambil 3 data dokumen lowongan kerja berstatus 'pending' terbaru
        $daftarDokumen = DB::table('internships as i')
            ->join('companies as c', 'i.id_company', '=', 'c.id_company')
            ->select('i.*', 'c.company_name', 'i.posted_at as apply_date')
            ->where('i.approval_status', 'pending')
            ->orderBy('i.posted_at', 'desc')
            ->take(3)
            ->get();

        // 3. Ambil 3 data tim mahasiswa terbaru (Join dengan tabel students untuk ambil nama pembuat)
        $daftarTeam = DB::table('teams as t')
            ->join('students as s', 't.id_creator', '=', 's.id_student')
            ->select('t.*', 's.full_name as student_name', 's.nim as NIM')
            ->orderBy('t.created_at', 'desc')
            ->take(3)
            ->get();

        // 4. Ambil 3 data mitra perusahaan terbaru (Join dengan tabel users untuk ambil email dan tanggal daftar)
        $daftarPerusahaan = DB::table('companies as c')
            ->join('users as u', 'c.id_user', '=', 'u.id_user')
            ->select('c.*', 'u.created_at', 'u.email') 
            ->orderBy('u.created_at', 'desc')
            ->take(3)
            ->get();

        // 5. Return data utuh ke view dashboard admin
        return view('dashboardAdmin', compact(
            'totalCompany', 'totalTeam', 'totalPending', 
            'daftarDokumen', 'daftarTeam', 'daftarPerusahaan'
        ));
    }

    public function validasiMagang(Request $request)
    {
        $searchQuery = $request->query('search');

        // Buat ngitung data statistik counter card sesuai approval_status 
        $totalPending = DB::table('internships')->where('approval_status', 'pending')->count();
        $totalAccepted = DB::table('internships')->where('approval_status', 'approved')->count();

        // Buat mengambil daftar lowongan & perusahaan sesuai skema DB asli
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
                'c.industry_field',
                'c.company_logo'
            );

        if (!empty($searchQuery)) {
            $queryInternship->where(function($q) use ($searchQuery) {
                $q->where('c.company_name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('i.title', 'like', '%' . $searchQuery . '%');
            });
        }

        $daftarLowongan = $queryInternship->orderBy('i.id_internship', 'desc')->get();

        // Generate URL unduh dokumen dari Supabase Storage
        $supabaseUrl = rtrim(env('SUPABASE_ENDPOINT', 'https://qdcjgonjjrxhghlbdarz.supabase.co/storage/v1/s3'), '/');
        // Konversi endpoint S3 ke URL public: .../v1/s3 → .../v1/object/public
        $supabasePublicBase = str_replace('/s3', '/object/public', $supabaseUrl);
        $docBucket = env('SUPABASE_DOC_BUCKET', env('SUPABASE_DEFAULT_BUCKET', 'logo-comp'));

        foreach ($daftarLowongan as $row) {
            if (!empty($row->supporting_document)) {
                $fileName = basename($row->supporting_document);
                $row->file_url = $supabasePublicBase . '/' . $docBucket . '/' . $fileName;
            } else {
                $row->file_url = null;
            }
        }

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