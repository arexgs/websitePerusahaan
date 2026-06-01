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
    
    if (!$data || !isset($data['pelamar_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Pelamar ID tidak ditemukan']);
        exit;
    }

    $pelamar_id = intval($data['pelamar_id']);
    $status = trim($data['status'] ?? 'pending');

    if (!in_array($status, ['pending', 'review', 'accepted', 'rejected'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
        exit;
    }

    $query = "UPDATE applicants SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('si', $status, $pelamar_id);

    if ($stmt->execute()) {
        $stmt->close();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Status pelamar berhasil diperbarui'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal update status: ' . $stmt->error]);
    }

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
