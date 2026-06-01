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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            sendError('Invalid JSON input', 400);
        }
        
        $email = isset($data['email']) ? trim($data['email']) : '';
        $mode = isset($data['mode']) ? trim($data['mode']) : 'company';
        
        if (empty($email)) {
            sendError('Email tidak boleh kosong', 400);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendError('Format email tidak valid', 400);
        }
        
        $table = ($mode === 'company') ? 'companies' : 'admins';
        
        // Check apakah email ada di database
        $query = "SELECT id, email, name FROM $table WHERE email = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            sendError('Database error: ' . $conn->error, 500);
        }
        
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // Email tidak ditemukan - jangan beri tahu spesifik untuk keamanan
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Jika email terdaftar, link reset password telah dikirimkan ke inbox Anda. Silakan cek email Anda (termasuk folder Spam).'
            ]);
            $stmt->close();
            $conn->close();
            exit;
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Simpan reset token ke database
        $updateQuery = "UPDATE $table SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        
        if (!$updateStmt) {
            sendError('Database error: ' . $conn->error, 500);
        }
        
        $updateStmt->bind_param('ssi', $resetToken, $tokenExpiry, $user['id']);
        
        if (!$updateStmt->execute()) {
            sendError('Gagal memproses reset password', 500);
        }
        
        $updateStmt->close();
        
        // Buat reset link
        $resetLink = "http://localhost/unscollab/reset-password.html?token=" . $resetToken . "&type=" . $mode;
        
        // Kirim email
        $to = $email;
        $subject = "Reset Password UNSCollab - " . ($mode === 'company' ? 'Company' : 'Admin');
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0dcaf0; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .body { background-color: #f9f9f9; padding: 20px; }
                .button { background-color: #0dcaf0; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                .footer { background-color: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                .warning { background-color: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 15px 0; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Reset Password UNSCollab</h2>
                </div>
                <div class='body'>
                    <p>Halo " . htmlspecialchars($user['name']) . ",</p>
                    <p>Kami menerima permintaan untuk mereset password akun Anda. Klik tombol di bawah untuk melanjutkan:</p>
                    
                    <center>
                        <a href='" . $resetLink . "' class='button'>Reset Password</a>
                    </center>
                    
                    <p>Atau copy link berikut ke browser Anda:</p>
                    <p style='word-break: break-all; background-color: #f0f0f0; padding: 10px; border-radius: 3px;'>" . $resetLink . "</p>
                    
                    <div class='warning'>
                        <strong>⚠️ Perhatian:</strong>
                        <ul>
                            <li>Link ini hanya berlaku selama 1 jam</li>
                            <li>Jika Anda tidak merasa meminta reset password, abaikan email ini</li>
                            <li>Jangan pernah bagikan link ini kepada siapapun</li>
                        </ul>
                    </div>
                    
                    <p>Pertanyaan? Hubungi tim support kami di <a href='mailto:support@unscollab.com'>support@unscollab.com</a></p>
                </div>
                <div class='footer'>
                    <p>&copy; 2026 UNSCollab. All rights reserved.</p>
                    <p>Email ini dikirim karena ada permintaan reset password. Jika ini bukan Anda, silakan abaikan.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@unscollab.com" . "\r\n";
        
        // Kirim email
        mail($to, $subject, $message, $headers);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Jika email terdaftar, link reset password telah dikirimkan ke inbox Anda. Silakan cek email Anda (termasuk folder Spam).'
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
