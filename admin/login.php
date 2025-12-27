<?php
session_start();
require_once '../config.php';

// Nếu đã đăng nhập, chuyển đến dashboard
if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        // Kiểm tra admin trong database
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'email' => $admin['email']
            ];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - BIBIBABA</title>
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
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .login-body {
            padding: 2.5rem 2rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #2d3748;
            box-shadow: 0 0 0 3px rgba(45, 55, 72, 0.1);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            border: none;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(45, 55, 72, 0.6);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .back-home {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-home a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-home a:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-user-shield"></i>
                <h3 class="mb-0">Admin Panel</h3>
                <p class="mb-0 opacity-75">BIBIBABA Food</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Tên đăng nhập" required autofocus>
                        <label for="username">
                            <i class="fas fa-user me-2"></i>Tên đăng nhập
                        </label>
                    </div>
                    
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Mật khẩu" required>
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>Mật khẩu
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Đăng nhập
                    </button>
                </form>
            </div>
        </div>
        
        <div class="back-home">
            <a href="../index.php">
                <i class="fas fa-arrow-left me-2"></i>
                Quay về trang chủ
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
