<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (!session('user_id')) {
            return redirect('/');
        }
        return view('dashboard');
    }

    public function getData()
    {
        $companyId = session('type_id');

        // Stats — tabel: internships, applications
        $totalLowongan = DB::table('internships')
            ->where('id_company', $companyId)->count();

        $pendingLowongan = DB::table('internships')
            ->where('id_company', $companyId)
            ->where('approval_status', 'pending')->count();

        $totalPelamar = DB::table('applications')
            ->join('internships', 'applications.id_internship', '=', 'internships.id_internship')
            ->where('internships.id_company', $companyId)->count();

        $diterimaPelamar = DB::table('applications')
            ->join('internships', 'applications.id_internship', '=', 'internships.id_internship')
            ->where('internships.id_company', $companyId)
            ->where('applications.application_status', 'accepted')->count();

        // Lowongan list
        $lowongan = DB::table('internships')
            ->where('id_company', $companyId)
            ->orderBy('posted_at', 'desc')
            ->get();

        // Pelamar — tabel: applications, students, users
        $pelamar = DB::table('applications')
            ->join('internships', 'applications.id_internship', '=', 'internships.id_internship')
            ->join('students', 'applications.id_student', '=', 'students.id_student')
            ->join('users', 'students.id_user', '=', 'users.id_user')
            ->where('internships.id_company', $companyId)
            ->select(
                'applications.id_student',
                'applications.id_internship',
                'applications.application_status',
                'applications.apply_date',
                'applications.cv',
                'applications.cover_letter',
                'students.full_name',
                'students.major',
                'students.skill',
                'students.experience',
                'students.bio',
                'students.portofolio',
                'students.profile_picture',
                'users.email',
                'internships.title as posisi'
            )
            ->orderBy('applications.apply_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'stats'   => [
                'total_lowongan'   => $totalLowongan,
                'pending_lowongan' => $pendingLowongan,
                'total_pelamar'    => $totalPelamar,
                'diterima'         => $diterimaPelamar,
            ],
            'lowongan' => $lowongan,
            'pelamar'  => $pelamar,
        ]);
    }
}