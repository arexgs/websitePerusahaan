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
    $name = trim($data['name'] ?? '');
    $industry = trim($data['industry'] ?? '');
    $website = trim($data['website'] ?? '');
    $address = trim($data['address'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $description = trim($data['description'] ?? '');

    if (!$name) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nama perusahaan tidak boleh kosong']);
        exit;
    }

    $query = "UPDATE companies SET name = ?, industry = ?, website = ?, address = ?, phone = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('ssssssi', $name, $industry, $website, $address, $phone, $description, $company_id);

    if ($stmt->execute()) {
        $stmt->close();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Profil berhasil diperbarui'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal update profil: ' . $stmt->error]);
    }

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
