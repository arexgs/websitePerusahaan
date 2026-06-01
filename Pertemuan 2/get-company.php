<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once 'config.php';

function sendError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method tidak diizinkan', 405);
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['email'])) {
        sendError('Email tidak ditemukan', 400);
    }

    $email = trim($data['email']);

    // Get company data
    $query = "SELECT id, name, email, industry, website, description, phone, logo_url, created_at FROM companies WHERE email = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        sendError('Database error: ' . $conn->error, 500);
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendError('Perusahaan tidak ditemukan', 404);
    }

    $company = $result->fetch_assoc();
    $stmt->close();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $company
    ]);

    $conn->close();
} catch (Exception $e) {
    sendError('Error: ' . $e->getMessage(), 500);
}
?>
