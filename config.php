<?php
// config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cấu hình database
// Để test trên localhost, dùng cấu hình này:
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ecommerce_food";

// Khi upload lên ByetHost, thay bằng thông tin từ VistaPanel:
// $host = "sqlXXX.byethost.com"; // ví dụ: sql108.byethost.com
// $user = "b1234567_user";
// $pass = "abc12345";
// $db   = "b1234567_project";

// Kết nối mysqli
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập charset UTF-8
$conn->set_charset("utf8mb4");

// Tạo PDO connection để tương thích với code cũ
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("DB connect failed: " . $e->getMessage());
}
