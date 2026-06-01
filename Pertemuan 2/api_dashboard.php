<?php
/**
 * api_dashboard.php
 * GET /api_dashboard.php
 * Header: Authorization: Bearer <session_token>   ATAU pakai session PHP
 *
 * Response:
 *  {
 *    success: true,
 *    company: { id, name, email, industry, website, company_size, description, logo_path, phone, is_verified },
 *    stats: { lowongan_aktif, lowongan_pending, total_pelamar, pelamar_diterima, perlu_review },
 *    lowongan: [ ...summary ],
 *    pelamar_terbaru: [ ...5 terbaru ],
 *    notifikasi: [ ...unread ],
 *    aktivitas: [ ...5 terbaru ]
 *  }
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
require_once 'config.php';

function sendError($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// Auth check
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'company') {
    sendError('Unauthorized', 401);
}

$company_id = (int) $_SESSION['user_id'];

// ── 1. Data perusahaan ────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT id, name, email, industry, website, company_size, description,
            logo_path, phone, address, is_verified, created_at
     FROM companies WHERE id = ?"
);
$stmt->bind_param('i', $company_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$company) sendError('Perusahaan tidak ditemukan', 404);

// ── 2. Statistik dashboard ────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM v_dashboard_company WHERE company_id = ?");
$stmt->bind_param('i', $company_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$stats) {
    $stats = ['lowongan_aktif'=>0,'lowongan_pending'=>0,'total_pelamar'=>0,'pelamar_diterima'=>0,'perlu_review'=>0];
}

// ── 3. Semua lowongan milik perusahaan ───────────────────
$stmt = $conn->prepare(
    "SELECT l.id, l.judul, l.tipe, l.lokasi, l.gaji_min, l.gaji_max,
            l.status, l.batas_lamaran, l.created_at, l.has_mou, l.has_proposal,
            l.admin_note,
            COUNT(lm.id) AS jumlah_pelamar
     FROM lowongan l
     LEFT JOIN lamaran lm ON lm.lowongan_id = l.id
     WHERE l.company_id = ?
     GROUP BY l.id
     ORDER BY l.created_at DESC"
);
$stmt->bind_param('i', $company_id);
$stmt->execute();
$rows = $stmt->get_result();
$lowongan = [];
while ($r = $rows->fetch_assoc()) {
    // ambil dokumen kerja sama per lowongan
    $s2 = $conn->prepare(
        "SELECT id, tipe, nama_file, path_file, ukuran, uploaded_at
         FROM dokumen_lowongan WHERE lowongan_id = ?"
    );
    $s2->bind_param('i', $r['id']);
    $s2->execute();
    $dok_rows = $s2->get_result();
    $r['dokumen'] = [];
    while ($d = $dok_rows->fetch_assoc()) $r['dokumen'][] = $d;
    $s2->close();
    $lowongan[] = $r;
}
$stmt->close();

// ── 4. Pelamar terbaru (5) ───────────────────────────────
$stmt = $conn->prepare(
    "SELECT lm.id AS lamaran_id, m.id AS mahasiswa_id, m.name, m.email,
            m.jurusan, m.universitas, m.ipk, m.angkatan,
            lo.judul AS posisi, lm.status, lm.step_seleksi,
            lm.created_at AS tanggal_lamar
     FROM lamaran lm
     JOIN mahasiswa m  ON m.id  = lm.mahasiswa_id
     JOIN lowongan  lo ON lo.id = lm.lowongan_id
     WHERE lo.company_id = ?
     ORDER BY lm.created_at DESC
     LIMIT 5"
);
$stmt->bind_param('i', $company_id);
$stmt->execute();
$rows = $stmt->get_result();
$pelamar_terbaru = [];
while ($r = $rows->fetch_assoc()) $pelamar_terbaru[] = $r;
$stmt->close();

// ── 5. Notifikasi belum dibaca ───────────────────────────
$stmt = $conn->prepare(
    "SELECT id, judul, isi, tipe_warna, created_at
     FROM notifikasi
     WHERE target_type = 'company' AND target_id = ? AND is_read = 0
     ORDER BY created_at DESC LIMIT 10"
);
$stmt->bind_param('i', $company_id);
$stmt->execute();
$rows = $stmt->get_result();
$notifikasi = [];
while ($r = $rows->fetch_assoc()) $notifikasi[] = $r;
$stmt->close();

// ── 6. Aktivitas terbaru (gabungan lowongan + lamaran) ────
$stmt = $conn->prepare(
    "SELECT 'lamar' AS tipe, m.name AS aktor, lo.judul AS target,
            lm.created_at AS waktu
     FROM lamaran lm
     JOIN mahasiswa m  ON m.id  = lm.mahasiswa_id
     JOIN lowongan  lo ON lo.id = lm.lowongan_id
     WHERE lo.company_id = ?
     UNION ALL
     SELECT 'status_lowongan' AS tipe,
            CASE lo2.status
              WHEN 'aktif'    THEN 'Lowongan disetujui admin'
              WHEN 'ditolak'  THEN 'Lowongan ditolak admin'
              ELSE lo2.status
            END,
            lo2.judul, lo2.updated_at
     FROM lowongan lo2
     WHERE lo2.company_id = ?
       AND lo2.status IN ('aktif','ditolak')
     ORDER BY waktu DESC LIMIT 8"
);
$stmt->bind_param('ii', $company_id, $company_id);
$stmt->execute();
$rows = $stmt->get_result();
$aktivitas = [];
while ($r = $rows->fetch_assoc()) $aktivitas[] = $r;
$stmt->close();

$conn->close();

echo json_encode([
    'success'         => true,
    'company'         => $company,
    'stats'           => $stats,
    'lowongan'        => $lowongan,
    'pelamar_terbaru' => $pelamar_terbaru,
    'notifikasi'      => $notifikasi,
    'aktivitas'       => $aktivitas,
]);