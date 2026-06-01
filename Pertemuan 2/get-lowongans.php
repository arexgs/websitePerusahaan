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

    // Get lowongans
    $query = "SELECT id, title, description, location, position_type, salary_min, salary_max, status, applicants_count, created_at 
              FROM job_postings 
              WHERE company_id = ? 
              ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('i', $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $lowongans = [];

    while ($row = $result->fetch_assoc()) {
        $row['salary_range'] = ($row['salary_min'] ?? 'N/A') . ' – ' . ($row['salary_max'] ?? 'N/A');
        $lowongans[] = $row;
    }

    $stmt->close();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $lowongans
    ]);

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
