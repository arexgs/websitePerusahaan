<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once '../config.php';
session_start();

function sendError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Check auth
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    sendError('Unauthorized', 401);
}

try {
    $company_id = $_SESSION['user_id'];
    
    // Get salary deductions
    $query = "SELECT id, name, description, percentage, fixed_amount, is_active 
              FROM salary_deductions 
              WHERE company_id = ? 
              ORDER BY name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $deductions = [];
    while ($row = $result->fetch_assoc()) {
        $deductions[] = $row;
    }
    $stmt->close();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $deductions
    ]);
    
    $conn->close();
} catch (Exception $e) {
    sendError('Error: ' . $e->getMessage(), 500);
}
?>
