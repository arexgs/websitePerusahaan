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

    // Get pelamars for all job postings from this company
    $query = "SELECT a.id, a.name, a.email, a.phone, a.status, a.applied_at, 
                     jp.title as job_title, ad.education as major, ad.experience, ad.skills, ad.portfolio_url, a.cover_letter
              FROM applicants a
              INNER JOIN job_postings jp ON a.job_posting_id = jp.id
              LEFT JOIN applicant_details ad ON a.id = ad.applicant_id
              WHERE jp.company_id = ?
              ORDER BY a.applied_at DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('i', $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pelamars = [];

    while ($row = $result->fetch_assoc()) {
        $pelamars[] = $row;
    }

    $stmt->close();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $pelamars
    ]);

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
