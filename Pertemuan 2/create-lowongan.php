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

    // Optional fields
    $position_type = trim($data['position_type'] ?? 'Full-time');
    $department = trim($data['department'] ?? '');
    $location = trim($data['location'] ?? '');
    $salary_range = trim($data['salary_range'] ?? '');
    $education = trim($data['education'] ?? '');
    $major = trim($data['major'] ?? '');
    $min_ipk = !empty($data['min_ipk']) ? floatval($data['min_ipk']) : 0.00;
    $quota = !empty($data['quota']) ? intval($data['quota']) : 0;
    $deadline = !empty($data['deadline']) ? $data['deadline'] : null;

    // Query with all fields from database schema
    $query = "INSERT INTO job_postings (
                company_id, 
                title, 
                description, 
                position_type, 
                department,
                location, 
                salary_range, 
                education, 
                major,
                requirements, 
                min_ipk, 
                quota,
                deadline, 
                status, 
                created_at
              ) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    // Bind parameters: i=integer, s=string, d=double
    // Order: company_id(i), title(s), description(s), position_type(s), department(s), 
    //        location(s), salary_range(s), education(s), major(s), requirements(s), 
    //        min_ipk(d), quota(i), deadline(s)
    $stmt->bind_param('issssssssssdis',
        $company_id,
        $title,
        $description,
        $position_type,
        $department,
        $location,
        $salary_range,
        $education,
        $major,
        $requirements,
        $min_ipk,
        $quota,
        $deadline
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
