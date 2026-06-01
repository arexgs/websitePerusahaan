<?php
// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once 'config.php';

// Fungsi error handler
function sendError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            sendError('Invalid JSON input', 400);
        }
        
        $email = isset($data['email']) ? trim($data['email']) : '';
        $password = isset($data['password']) ? trim($data['password']) : '';
        $mode = isset($data['mode']) ? trim($data['mode']) : 'company';
        
        // Validasi input
        if (empty($email) || empty($password)) {
            sendError('Email dan password tidak boleh kosong', 400);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendError('Format email tidak valid', 400);
        }
        
        if ($mode === 'company') {
            // Login untuk Perusahaan
            $query = "SELECT id, email, name, password FROM companies WHERE email = ?";
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                sendError('Database error: ' . $conn->error, 500);
            }
            
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $company = $result->fetch_assoc();
                
                if (password_verify($password, $company['password'])) {
                    // Password benar
                    session_start();
                    $_SESSION['user_id'] = $company['id'];
                    $_SESSION['user_email'] = $company['email'];
                    $_SESSION['user_name'] = $company['name'];
                    $_SESSION['user_type'] = 'company';
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login berhasil',
                        'redirect' => 'dashboard.html'
                    ]);
                } else {
                    sendError('Email atau password salah', 401);
                }
            } else {
                sendError('Email atau password salah', 401);
            }
            
            $stmt->close();
        } else if ($mode === 'admin') {
            // Login untuk Admin
            $query = "SELECT id, email, name, password FROM admins WHERE email = ?";
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                sendError('Database error: ' . $conn->error, 500);
            }
            
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                
                if (password_verify($password, $admin['password'])) {
                    // Password benar
                    session_start();
                    $_SESSION['user_id'] = $admin['id'];
                    $_SESSION['user_email'] = $admin['email'];
                    $_SESSION['user_name'] = $admin['name'];
                    $_SESSION['user_type'] = 'admin';
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login berhasil',
                        'redirect' => 'admin-dashboard.html'
                    ]);
                } else {
                    sendError('Email atau password salah', 401);
                }
            } else {
                sendError('Email atau password salah', 401);
            }
            
            $stmt->close();
        } else {
            sendError('Mode tidak valid', 400);
        }
        
        $conn->close();
    } catch (Exception $e) {
        sendError('Error: ' . $e->getMessage(), 500);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>
