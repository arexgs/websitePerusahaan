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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
        sendError('Unauthorized', 401);
    }

    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            sendError('Invalid JSON input', 400);
        }
        
        $company_id = $_SESSION['user_id'];
        $deduction_id = isset($data['deduction_id']) ? (int)$data['deduction_id'] : 0;
        $name = isset($data['name']) ? trim($data['name']) : '';
        $description = isset($data['description']) ? trim($data['description']) : '';
        $percentage = isset($data['percentage']) ? (float)$data['percentage'] : NULL;
        $fixed_amount = isset($data['fixed_amount']) ? (int)$data['fixed_amount'] : NULL;
        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        
        // Validasi
        if ($deduction_id <= 0) {
            sendError('ID potongan tidak valid', 400);
        }
        
        if (empty($name)) {
            sendError('Nama potongan tidak boleh kosong', 400);
        }
        
        if ($percentage !== NULL && ($percentage < 0 || $percentage > 100)) {
            sendError('Persentase harus antara 0-100', 400);
        }
        
        if ($fixed_amount !== NULL && $fixed_amount < 0) {
            sendError('Jumlah tetap tidak boleh negatif', 400);
        }
        
        // Check ownership
        $checkQuery = "SELECT id FROM salary_deductions WHERE id = ? AND company_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('ii', $deduction_id, $company_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            sendError('Potongan gaji tidak ditemukan', 404);
        }
        $checkStmt->close();
        
        // Update
        $query = "UPDATE salary_deductions 
                  SET name = ?, description = ?, percentage = ?, fixed_amount = ?, is_active = ?
                  WHERE id = ? AND company_id = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            sendError('Database error: ' . $conn->error, 500);
        }
        
        $stmt->bind_param('ssdiii', $name, $description, $percentage, $fixed_amount, $is_active, $deduction_id, $company_id);
        
        if (!$stmt->execute()) {
            sendError('Gagal update potongan gaji', 500);
        }
        
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Potongan gaji berhasil diupdate'
        ]);
        
        $conn->close();
    } catch (Exception $e) {
        sendError('Error: ' . $e->getMessage(), 500);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>
