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
        // Validasi directory uploads
        $uploadDir = '../uploads/documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $company_id = $_SESSION['user_id'];
        $document_type = isset($_POST['document_type']) ? trim($_POST['document_type']) : '';
        $document_name = isset($_POST['document_name']) ? trim($_POST['document_name']) : '';
        
        // Validasi
        if (empty($document_type)) {
            sendError('Jenis dokumen tidak boleh kosong', 400);
        }
        
        if (!isset($_FILES['file'])) {
            sendError('File tidak ada', 400);
        }
        
        $file = $_FILES['file'];
        
        // Validasi file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            sendError('Error upload: ' . $file['error'], 400);
        }
        
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (!in_array($file['type'], $allowed_types)) {
            sendError('Tipe file hanya boleh PDF atau DOCX', 400);
        }
        
        if ($file['size'] > 10 * 1024 * 1024) { // 10MB max
            sendError('Ukuran file maksimal 10MB', 400);
        }
        
        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $company_id . '_' . time() . '_' . uniqid() . '.' . $ext;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            sendError('Gagal upload file', 500);
        }
        
        // Save to database
        $query = "INSERT INTO company_documents (company_id, document_type, document_name, file_path, file_size) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            unlink($filepath);
            sendError('Database error: ' . $conn->error, 500);
        }
        
        $file_size = $file['size'];
        $stmt->bind_param('isssi', $company_id, $document_type, $document_name, $filename, $file_size);
        
        if (!$stmt->execute()) {
            unlink($filepath);
            sendError('Gagal menyimpan dokumen', 500);
        }
        
        $doc_id = $stmt->insert_id;
        $stmt->close();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Dokumen berhasil diupload',
            'data' => [
                'id' => $doc_id,
                'document_name' => $document_name,
                'file_size' => $file_size
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
