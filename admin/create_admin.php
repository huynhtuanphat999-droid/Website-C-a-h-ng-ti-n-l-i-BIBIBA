<?php
require_once '../config.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Tạo Admin - BIBIBABA</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class='container'>
        <h2 class='mb-4 text-center'>Tạo tài khoản Admin</h2>";

try {
    // Tạo bảng admins nếu chưa có
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Xóa admin cũ nếu có
    $pdo->exec("DELETE FROM admins WHERE username = 'admin'");
    
    // Tạo mật khẩu mới
    $password = 'admin123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Thêm admin mới
    $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute(['admin', 'admin@bibibaba.com', $hashedPassword]);
    
    echo "<div class='alert alert-success'>
        <h5>✅ Tạo tài khoản admin thành công!</h5>
        <hr>
        <p class='mb-2'><strong>Username:</strong> <code>admin</code></p>
        <p class='mb-2'><strong>Password:</strong> <code>admin123</code></p>
        <p class='mb-0'><strong>Email:</strong> <code>admin@bibibaba.com</code></p>
    </div>";
    
    echo "<div class='alert alert-info'>
        <strong>ℹ️ Thông tin kỹ thuật:</strong><br>
        Password hash: <small>" . htmlspecialchars($hashedPassword) . "</small>
    </div>";
    
    echo "<div class='text-center mt-4'>
        <a href='login.php' class='btn btn-primary btn-lg'>
            <i class='fas fa-sign-in-alt me-2'></i>
            Đăng nhập ngay
        </a>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
        <strong>❌ Lỗi:</strong> " . htmlspecialchars($e->getMessage()) . "
    </div>";
}

echo "    </div>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'/>
</body>
</html>";
?>
