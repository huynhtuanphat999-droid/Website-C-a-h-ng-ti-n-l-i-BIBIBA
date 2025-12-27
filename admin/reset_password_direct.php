<?php
// Script reset mật khẩu admin trực tiếp
require_once __DIR__ . '/../config.php';

try {
    // Tạo hash cho mật khẩu "123456"
    $password = "123456";
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Cập nhật mật khẩu trong database
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
    $result = $stmt->execute([$hash]);
    
    if ($result) {
        echo "✅ Đã reset mật khẩu admin thành công!\n";
        echo "Username: admin\n";
        echo "Password: 123456\n";
        echo "\nBạn có thể đăng nhập ngay bây giờ.\n";
    } else {
        echo "❌ Lỗi khi cập nhật mật khẩu\n";
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}
?>