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
        $name = isset($data['name']) ? trim($data['name']) : '';
        $description = isset($data['description']) ? trim($data['description']) : '';
        $percentage = isset($data['percentage']) ? (float)$data['percentage'] : NULL;
        $fixed_amount = isset($data['fixed_amount']) ? (int)$data['fixed_amount'] : NULL;
        
        // Validasi
        if (empty($name)) {
            sendError('Nama potongan tidak boleh kosong', 400);
        }
        
        if ($percentage === NULL && $fixed_amount === NULL) {
            sendError('Masukkan persentase atau jumlah tetap', 400);
        }
        
        if ($percentage !== NULL && ($percentage < 0 || $percentage > 100)) {
            sendError('Persentase harus antara 0-100', 400);
        }
        
        if ($fixed_amount !== NULL && $fixed_amount < 0) {
            sendError('Jumlah tetap tidak boleh negatif', 400);
        }
        
        // Insert
        $query = "INSERT INTO salary_deductions (company_id, name, description, percentage, fixed_amount) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            sendError('Database error: ' . $conn->error, 500);
        }
        
        $stmt->bind_param('issdi', $company_id, $name, $description, $percentage, $fixed_amount);
        
        if (!$stmt->execute()) {
            sendError('Gagal menambah potongan gaji', 500);
        }
        
        $deduction_id = $stmt->insert_id;
        $stmt->close();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Potongan gaji berhasil ditambahkan',
            'data' => [
                'id' => $deduction_id,
                'name' => $name,
                'description' => $description,
                'percentage' => $percentage,
                'fixed_amount' => $fixed_amount
            ]
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
