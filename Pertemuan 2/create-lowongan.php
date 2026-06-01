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
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    $company_id = intval($data['company_id'] ?? 0);
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $requirements = trim($data['requirements'] ?? '');

    if (!$company_id || !$title || !$description || !$requirements) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit;
    }

    $position_type = trim($data['position_type'] ?? 'Full-time');
    $location = trim($data['location'] ?? '');
    $salary_min = intval($data['salary_min'] ?? 0);
    $salary_max = intval($data['salary_max'] ?? 0);
    $deadline = !empty($data['deadline']) ? $data['deadline'] : null;

    $query = "INSERT INTO job_postings (company_id, title, description, requirements, position_type, location, salary_min, salary_max, status, deadline, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('isssssii', 
        $company_id, $title, $description, $requirements, 
        $position_type, $location, $salary_min, $salary_max, $deadline
    );

    if ($stmt->execute()) {
        $lowongan_id = $stmt->insert_id;
        $stmt->close();

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Lowongan berhasil dibuat',
            'data' => ['id' => $lowongan_id]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal membuat lowongan: ' . $stmt->error]);
    }

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
