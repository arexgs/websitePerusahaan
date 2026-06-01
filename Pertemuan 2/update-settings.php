<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['company_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Company ID tidak ditemukan']);
        exit;
    }

    $company_id = intval($data['company_id']);
    $username = trim($data['username'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $current_password = $data['current_password'] ?? '';
    $new_password = $data['new_password'] ?? '';

    // Get current company data
    $query = "SELECT password FROM companies WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $company = $result->fetch_assoc();
    $stmt->close();

    if (!$company) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Perusahaan tidak ditemukan']);
        exit;
    }

    // If changing password, verify current password
    if ($new_password) {
        if (!$current_password) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Masukkan password saat ini']);
            exit;
        }

        if (!password_verify($current_password, $company['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Password saat ini salah']);
            exit;
        }

        if (strlen($new_password) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Password baru minimal 6 karakter']);
            exit;
        }

        $new_password = password_hash($new_password, PASSWORD_BCRYPT);

        $query = "UPDATE companies SET name = ?, phone = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssi', $username, $phone, $new_password, $company_id);
    } else {
        $query = "UPDATE companies SET name = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssi', $username, $phone, $company_id);
    }

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    if ($stmt->execute()) {
        $stmt->close();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pengaturan: ' . $stmt->error]);
    }

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
