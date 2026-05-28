<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'PPemWeb-06-L0124086');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('<div style="padding:20px; background:#FEE2E2; color:#B91C1C; font-family:sans-serif; border-radius:8px; margin:20px">
        <strong>Koneksi database gagal!</strong><br>
        Pastikan MySQL sudah dijalankan di XAMPP.<br><br>
        Error: ' . $conn->connect_error . '
    </div>');
}
