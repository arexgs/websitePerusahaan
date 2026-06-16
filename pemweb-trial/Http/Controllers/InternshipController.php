<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternshipController extends Controller
{
    public function store(Request $request)
    {
        $companyId = session('type_id');

        if (!$companyId) {
            return response()->json(['success' => false, 'message' => 'Sesi Anda telah habis atau Anda tidak memiliki akses.'], 401);
        }

        $title         = trim($request->input('title', ''));
        $description   = trim($request->input('description', ''));
        $requirements  = trim($request->input('requirements', ''));
        $benefit       = trim($request->input('benefit', ''));
        $location      = trim($request->input('location', ''));
        $workMode      = trim($request->input('work_mode', 'onsite'));
        $paymentStatus = trim($request->input('payment_status', 'unpaid'));
        $quota         = intval($request->input('quota', 1));
        $duration      = trim($request->input('duration', ''));
        $deadline      = $request->input('deadline', null);

        if (empty($title) || empty($description) || empty($requirements)) {
            return response()->json(['success' => false, 'message' => 'Judul, deskripsi, dan kualifikasi wajib diisi'], 400);
        }

        // Upload image banner (opsional)
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower($file->getClientOriginalExtension());
            if (in_array($ext, $allowed) && $file->getSize() <= 2 * 1024 * 1024) {
                $filename = 'internship_img_' . time() . '_' . uniqid() . '.' . $ext;
                $file->move(public_path('uploads/internships'), $filename);
                $imagePath = 'uploads/internships/' . $filename;
            }
        }

        // Upload supporting document (opsional)
        $docPath = null;
        if ($request->hasFile('supporting_document')) {
            $file = $request->file('supporting_document');
            $allowed = ['pdf', 'doc', 'docx'];
            $ext = strtolower($file->getClientOriginalExtension());
            if (in_array($ext, $allowed) && $file->getSize() <= 10 * 1024 * 1024) {
                $filename = 'internship_doc_' . time() . '_' . uniqid() . '.' . $ext;
                $file->move(public_path('uploads/internships'), $filename);
                $docPath = 'uploads/internships/' . $filename;
            }
        }

        DB::table('internships')->insert([
            'id_internship'      => DB::raw('gen_random_uuid()'),
            'id_company'         => $companyId,
            'title'              => $title,
            'description'        => $description,
            'requirement'        => $requirements,
            'benefit'            => $benefit,
            'location'           => $location,
            'work_mode'          => $workMode,
            'payment_status'     => $paymentStatus,
            'quota'              => $quota,
            'duration'           => $duration,
            'deadline'           => $deadline,
            'image'              => $imagePath,
            'supporting_document'=> $docPath,
            'approval_status'    => 'pending',
            'posted_at'          => now(),
        ]);

        DB::table('activity_logs')->insert([
            'id_activity_log' => DB::raw('gen_random_uuid()'),
            'id_company'      => $companyId,
            'action'          => 'Membuat lowongan: ' . $title,
            'created_at'      => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dikirim! Menunggu review admin.',
        ]);
    }

    // POST edit lowongan — hanya kalau masih pending
    public function update(Request $request)
    {
        $companyId    = session('type_id');
        $idInternship = trim($request->input('id_internship', ''));

        if (!$companyId || empty($idInternship)) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid'], 400);
        }

        // Cek lowongan milik company ini dan masih pending
        $internship = DB::table('internships')
            ->where('id_internship', $idInternship)
            ->where('id_company', $companyId)
            ->first();

        if (!$internship) {
            return response()->json(['success' => false, 'message' => 'Lowongan tidak ditemukan'], 404);
        }

        if ($internship->approval_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Lowongan hanya bisa diedit selama masih pending'], 403);
        }

        $title         = trim($request->input('title', ''));
        $description   = trim($request->input('description', ''));
        $requirements  = trim($request->input('requirements', ''));

        if (empty($title) || empty($description) || empty($requirements)) {
            return response()->json(['success' => false, 'message' => 'Judul, deskripsi, dan kualifikasi wajib diisi'], 400);
        }

        $updateData = [
            'title'          => $title,
            'description'    => $description,
            'requirement'    => $requirements,
            'benefit'        => trim($request->input('benefit', '')),
            'location'       => trim($request->input('location', '')),
            'work_mode'      => trim($request->input('work_mode', 'onsite')),
            'payment_status' => trim($request->input('payment_status', 'unpaid')),
            'quota'          => intval($request->input('quota', 1)),
            'duration'       => trim($request->input('duration', '')),
            'deadline'       => $request->input('deadline', null),
        ];

        // Update image kalau ada file baru
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext  = strtolower($file->getClientOriginalExtension());
            if (in_array($ext, ['jpg','jpeg','png','webp']) && $file->getSize() <= 2 * 1024 * 1024) {
                $filename = 'internship_img_' . time() . '_' . uniqid() . '.' . $ext;
                $file->move(public_path('uploads/internships'), $filename);
                $updateData['image'] = 'uploads/internships/' . $filename;
            }
        }

        // Update dokumen kalau ada file baru
        if ($request->hasFile('supporting_document')) {
            $file = $request->file('supporting_document');
            $ext  = strtolower($file->getClientOriginalExtension());
            if (in_array($ext, ['pdf','doc','docx']) && $file->getSize() <= 10 * 1024 * 1024) {
                $filename = 'internship_doc_' . time() . '_' . uniqid() . '.' . $ext;
                $file->move(public_path('uploads/internships'), $filename);
                $updateData['supporting_document'] = 'uploads/internships/' . $filename;
            }
        }

        DB::table('internships')->where('id_internship', $idInternship)->update($updateData);

        DB::table('activity_logs')->insert([
            'id_activity_log' => DB::raw('gen_random_uuid()'),
            'id_company'      => $companyId,
            'action'          => 'Mengedit lowongan: ' . $title,
            'created_at'      => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Lowongan berhasil diperbarui!']);
    }

    // POST hapus lowongan — hanya kalau masih pending
    public function destroy(Request $request)
    {
        $companyId    = session('type_id');
        $idInternship = trim($request->input('id_internship', ''));

        if (!$companyId || empty($idInternship)) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid'], 400);
        }

        $internship = DB::table('internships')
            ->where('id_internship', $idInternship)
            ->where('id_company', $companyId)
            ->first();

        if (!$internship) {
            return response()->json(['success' => false, 'message' => 'Lowongan tidak ditemukan'], 404);
        }

        if ($internship->approval_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Lowongan hanya bisa dihapus selama masih pending'], 403);
        }

        DB::table('internships')->where('id_internship', $idInternship)->delete();

        DB::table('activity_logs')->insert([
            'id_activity_log' => DB::raw('gen_random_uuid()'),
            'id_company'      => $companyId,
            'action'          => 'Menghapus lowongan: ' . $internship->title,
            'created_at'      => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Lowongan berhasil dihapus']);
    }

    {
        $idStudent    = trim($request->input('id_student', ''));
        $idInternship = trim($request->input('id_internship', ''));
        $status       = trim($request->input('status', ''));

        if (empty($idStudent) || empty($idInternship)) {
            return response()->json(['success' => false, 'message' => 'ID Student atau ID Internship tidak valid'], 400);
        }

        // Enum di DB: pending, reviewed, accepted, rejected
        if (!in_array($status, ['pending', 'reviewed', 'accepted', 'rejected'])) {
            return response()->json(['success' => false, 'message' => 'Status tidak valid'], 400);
        }

        // Tabel: applications (bukan apply)
        $updated = DB::table('applications')
            ->where('id_student', $idStudent)
            ->where('id_internship', $idInternship)
            ->update(['application_status' => $status]);

        if (!$updated) {
            return response()->json(['success' => false, 'message' => 'Data pelamar tidak ditemukan atau tidak ada perubahan status'], 404);
        }

        $companyId   = session('type_id');
        $statusLabel = ['pending' => 'Menunggu', 'reviewed' => 'Direview', 'accepted' => 'Diterima', 'rejected' => 'Ditolak'][$status] ?? $status;

        DB::table('activity_logs')->insert([
            'id_activity_log' => DB::raw('gen_random_uuid()'),
            'id_company'      => $companyId,
            'action'          => 'Update status pelamar menjadi: ' . $statusLabel,
            'created_at'      => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status pelamar berhasil diperbarui']);
    }
}