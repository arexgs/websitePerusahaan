<?php
/**
 * api_profil.php
 *
 * GET  /api_profil.php   → data profil perusahaan
 * POST /api_profil.php   → update profil (multipart: field + optional logo file)
 * PUT  /api_profil.php?action=password → ganti password
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
require_once 'config.php';

function sendError($msg, $code = 400) {
    http_response_code($code); echo json_encode(['success' => false, 'message' => $msg]); exit;
}

if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'company') sendError('Unauthorized', 401);
$company_id = (int) $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── GET profil ──────────────────────────────────────────
if ($method === 'GET') {
    $s = $conn->prepare(
        "SELECT id, name, email, industry, website, company_size,
                description, logo_path, phone, address, is_verified, created_at
         FROM companies WHERE id=?"
    );
    $s->bind_param('i', $company_id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();
    echo json_encode(['success' => true, 'company' => $row]);
    exit;
}

// ── POST update profil ──────────────────────────────────
if ($method === 'POST' && $action !== 'password') {
    $name         = trim($_POST['name']         ?? '');
    $industry     = trim($_POST['industry']     ?? '');
    $website      = trim($_POST['website']      ?? '');
    $company_size = trim($_POST['company_size'] ?? '');
    $description  = trim($_POST['description']  ?? '');
    $phone        = trim($_POST['phone']        ?? '');
    $address      = trim($_POST['address']      ?? '');

    if (!$name) sendError('Nama perusahaan tidak boleh kosong');

    $logo_path = null;
    // Upload logo jika ada
    if (!empty($_FILES['logo']['name'])) {
        $logo_dir = UPLOAD_DIR . 'logo/';
        if (!is_dir($logo_dir)) mkdir($logo_dir, 0755, true);
        $ext  = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed_img = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed_img)) sendError('Format logo harus JPG/PNG/WebP');
        $filename = 'logo_' . $company_id . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $logo_dir . $filename)) sendError('Gagal upload logo');
        $logo_path = 'uploads/logo/' . $filename;
    }

    $sql = "UPDATE companies SET name=?, industry=?, website=?, company_size=?,
                description=?, phone=?, address=?";
    $vals  = [$name, $industry, $website, $company_size, $description, $phone, $address];
    $types = 'sssssss';
    if ($logo_path) { $sql .= ', logo_path=?'; $vals[] = $logo_path; $types .= 's'; }
    $sql .= " WHERE id=?"; $vals[] = $company_id; $types .= 'i';

    $s = $conn->prepare($sql);
    $s->bind_param($types, ...$vals);
    if (!$s->execute()) sendError('Gagal update: ' . $conn->error, 500);
    $s->close();

    // Refresh session name
    $_SESSION['user_name'] = $name;
    echo json_encode(['success' => true, 'message' => 'Profil berhasil disimpan', 'logo_path' => $logo_path]);
    exit;
}

// ── PUT ganti password ─────────────────────────────────
if ($method === 'PUT' && $action === 'password') {
    $data     = json_decode(file_get_contents('php://input'), true) ?? [];
    $old_pass = $data['old_password'] ?? '';
    $new_pass = $data['new_password'] ?? '';

    if (!$old_pass || !$new_pass) sendError('Password lama dan baru wajib diisi');
    if (strlen($new_pass) < 8) sendError('Password baru minimal 8 karakter');

    $s = $conn->prepare("SELECT password FROM companies WHERE id=?");
    $s->bind_param('i', $company_id); $s->execute();
    $row = $s->get_result()->fetch_assoc(); $s->close();
    if (!$row || !password_verify($old_pass, $row['password'])) sendError('Password lama salah', 401);

    $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
    $s = $conn->prepare("UPDATE companies SET password=? WHERE id=?");
    $s->bind_param('si', $hashed, $company_id); $s->execute(); $s->close();

    echo json_encode(['success' => true, 'message' => 'Password berhasil diubah']);
    exit;
}

sendError('Method tidak diizinkan', 405);