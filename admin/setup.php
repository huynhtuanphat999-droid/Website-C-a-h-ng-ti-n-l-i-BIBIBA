<?php
require_once '../config.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup Admin - BIBIBABA</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-container {
            max-width: 600px;
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .alert { border-radius: 12px; }
    </style>
</head>
<body>
    <div class='setup-container'>
        <h2 class='mb-4 text-center'>Cài đặt Admin Panel</h2>";

try {
    // Kiểm tra xem bảng admins đã tồn tại chưa
    $checkTable = $pdo->query("SHOW TABLES LIKE 'admins'")->fetch();
    
    if (!$checkTable) {
        // Tạo bảng admins
        $pdo->exec("CREATE TABLE admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "<div class='alert alert-success'>✓ Đã tạo bảng admins</div>";
    } else {
        echo "<div class='alert alert-info'>ℹ Bảng admins đã tồn tại</div>";
    }
    
    // Kiểm tra xem đã có admin chưa
    $checkAdmin = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    
    if ($checkAdmin == 0) {
        // Tạo tài khoản admin mặc định
        // Password: admin123
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['admin', 'admin@bibibaba.com', $hashedPassword]);
        
        echo "<div class='alert alert-success'>
            <h5>✓ Đã tạo tài khoản admin mặc định</h5>
            <hr>
            <p class='mb-1'><strong>Username:</strong> admin</p>
            <p class='mb-0'><strong>Password:</strong> admin123</p>
        </div>";
        
        echo "<div class='alert alert-warning'>
            <strong>⚠ Lưu ý bảo mật:</strong> Vui lòng đổi mật khẩu ngay sau khi đăng nhập lần đầu!
        </div>";
    } else {
        echo "<div class='alert alert-info'>ℹ Đã có tài khoản admin trong hệ thống</div>";
    }
    
    // Kiểm tra và thêm cột payment_method nếu chưa có
    $checkColumn = $pdo->query("SHOW COLUMNS FROM orders LIKE 'payment_method'")->fetch();
    if (!$checkColumn) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cod'");
        echo "<div class='alert alert-success'>✓ Đã thêm cột payment_method vào bảng orders</div>";
    }
    
    echo "<div class='alert alert-success mt-4'>
        <h5>🎉 Cài đặt hoàn tất!</h5>
        <p class='mb-0'>Bạn có thể đăng nhập vào admin panel ngay bây giờ.</p>
    </div>";
    
    echo "<div class='text-center mt-4'>
        <a href='login.php' class='btn btn-primary btn-lg'>
            <i class='fas fa-sign-in-alt me-2'></i>
            Đăng nhập Admin
        </a>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
        <strong>❌ Lỗi:</strong> " . htmlspecialchars($e->getMessage()) . "
    </div>";
    
    echo "<div class='text-center mt-4'>
        <a href='setup.php' class='btn btn-warning'>
            <i class='fas fa-redo me-2'></i>
            Thử lại
        </a>
    </div>";
}

echo "    </div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'/>
</body>
</html>";
