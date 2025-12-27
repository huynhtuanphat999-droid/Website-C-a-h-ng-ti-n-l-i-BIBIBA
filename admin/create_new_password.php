<?php
require_once '../config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['new_password'] ?? '');
    
    if (empty($newPassword)) {
        $message = 'Vui lòng nhập mật khẩu mới';
        $messageType = 'danger';
    } else {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
            $stmt->execute([$hashedPassword]);
            
            $message = "Mật khẩu đã được đổi thành công!<br><strong>Mật khẩu mới:</strong> " . htmlspecialchars($newPassword);
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Mật Khẩu Mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        body {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .password-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .header-icon {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header-icon i {
            font-size: 4rem;
            color: #2d3748;
        }
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1.1rem;
        }
        .form-control:focus {
            border-color: #2d3748;
            box-shadow: 0 0 0 3px rgba(45, 55, 72, 0.1);
        }
        .btn-create {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(45, 55, 72, 0.4);
        }
        .suggestion-box {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1rem;
        }
        .suggestion-item {
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 5px;
            transition: background 0.2s;
        }
        .suggestion-item:hover {
            background: #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="password-container">
        <div class="header-icon">
            <i class="fas fa-key"></i>
            <h3 class="mt-3">Tạo Mật Khẩu Mới</h3>
            <p class="text-muted mb-0">Đặt mật khẩu mới cho admin</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= $message ?>
            </div>
            
            <?php if ($messageType === 'success'): ?>
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-primary btn-create w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Đăng nhập ngay
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-lock me-2"></i>
                        Nhập mật khẩu mới
                    </label>
                    <input type="text" name="new_password" id="passwordInput" 
                           class="form-control" 
                           placeholder="Nhập mật khẩu bạn muốn" 
                           required autofocus>
                    <small class="text-muted">Tối thiểu 6 ký tự</small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-create w-100">
                    <i class="fas fa-check me-2"></i>
                    Tạo mật khẩu mới
                </button>
            </form>
            
            <div class="suggestion-box">
                <small class="text-muted d-block mb-2">
                    <i class="fas fa-lightbulb me-1"></i>
                    Gợi ý mật khẩu (click để chọn):
                </small>
                <div class="suggestion-item" onclick="setPassword('admin123')">
                    <i class="fas fa-arrow-right me-2"></i>admin123
                </div>
                <div class="suggestion-item" onclick="setPassword('bibibaba2024')">
                    <i class="fas fa-arrow-right me-2"></i>bibibaba2024
                </div>
                <div class="suggestion-item" onclick="setPassword('123456')">
                    <i class="fas fa-arrow-right me-2"></i>123456
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="login.php" class="text-muted">
                    <i class="fas fa-arrow-left me-2"></i>
                    Quay lại đăng nhập
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function setPassword(password) {
            document.getElementById('passwordInput').value = password;
        }
    </script>
</body>
</html>
