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

    // Get company documents
    $query = "SELECT id, document_type, document_name, file_path, file_size, uploaded_at 
              FROM company_documents 
              WHERE company_id = ? 
              ORDER BY uploaded_at DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('i', $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $documents = [];

    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }

    $stmt->close();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $documents
    ]);

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
