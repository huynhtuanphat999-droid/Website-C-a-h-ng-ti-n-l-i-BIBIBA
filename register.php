<?php
require_once 'config.php';
require_once 'functions.php';
$errors = [];

if($_SERVER['REQUEST_METHOD']==='POST'){
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  
  if(!$username || !$email || !$password || !$confirm_password) {
    $errors[] = 'Vui lòng điền đầy đủ thông tin.';
  }
  
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ.';
  }
  
  if(strlen($password) < 6) {
    $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
  }
  
  if($password !== $confirm_password) {
    $errors[] = 'Mật khẩu xác nhận không khớp.';
  }
  
  if(empty($errors)){
    // Check if username or email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username=:u OR email=:e LIMIT 1");
    $stmt->execute([':u' => $username, ':e' => $email]);
    if($stmt->fetch()) {
      $errors[] = 'Tên đăng nhập hoặc Email đã tồn tại.';
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $pdo->prepare("INSERT INTO users(username,email,password) VALUES(:u,:e,:p)")
          ->execute([':u' => $username, ':e' => $email, ':p' => $hash]);
      header('Location: login.php?registered=1');
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - BIBIBABA Food</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #d7ccc8 0%, #bcaaa4 50%, #d7ccc8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('images/plan.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            opacity: 0.1;
            z-index: -1;
        }
        
        /* Animated background shapes */
        .shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 8s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 200px;
            height: 200px;
            left: 10%;
            top: 20%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            right: 10%;
            top: 60%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            left: 70%;
            top: 10%;
            animation-delay: 4s;
        }
        
        .shape:nth-child(4) {
            width: 80px;
            height: 80px;
            left: 20%;
            bottom: 20%;
            animation-delay: 6s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.7;
            }
            50% {
                transform: translateY(-50px) rotate(180deg);
                opacity: 0.3;
            }
        }
        
        .register-container {
            position: relative;
            z-index: 10;
            max-width: 500px;
            width: 100%;
            padding: 20px;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .register-header {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .register-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotateReverse 25s linear infinite;
        }
        
        @keyframes rotateReverse {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }
        
        .register-header .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: pulse 2s ease-in-out infinite;
            position: relative;
            z-index: 2;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .register-header h2 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }
        
        .register-header p {
            opacity: 0.9;
            font-weight: 300;
            position: relative;
            z-index: 2;
        }
        
        .register-body {
            padding: 3rem 2rem;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 3.5rem;
            border: 2px solid #e1e5e9;
            border-radius: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2d3748;
            box-shadow: 0 0 0 3px rgba(45, 55, 72, 0.1);
            transform: translateY(-2px);
        }
        
        .form-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #2d3748;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus + .form-icon {
            color: #ff6600;
            transform: translateY(-50%) scale(1.1);
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }
        
        .strength-weak { color: #ff6b6b; }
        .strength-medium { color: #ff6600; }
        .strength-strong { color: #2d3748; }
        
        .btn-register {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            border: none;
            border-radius: 15px;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-register:hover::before {
            left: 100%;
        }
        
        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(45, 55, 72, 0.4);
        }
        
        .btn-register:active {
            transform: translateY(-1px);
        }
        
        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            animation: slideInDown 0.5s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
        }
        
        .login-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e1e5e9;
        }
        
        .login-link a {
            color: #2d3748;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .login-link a:hover {
            color: #ff6600;
            text-decoration: underline;
        }
        
        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            z-index: 20;
        }
        
        .back-home a {
            display: inline-flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .back-home a:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .back-home i {
            margin-right: 0.5rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .register-container {
                padding: 10px;
            }
            
            .register-header {
                padding: 2rem 1.5rem;
            }
            
            .register-body {
                padding: 2rem 1.5rem;
            }
            
            .back-home {
                top: 1rem;
                left: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Animated background shapes -->
    <div class="shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <!-- Back to home button -->
    <div class="back-home">
        <a href="index.php">
            <i class="fas fa-arrow-left"></i>
            Về trang chủ
        </a>
    </div>
    
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2>Tạo tài khoản mới</h2>
                <p>Tham gia cộng đồng BIBIBABA Food</p>
            </div>
            
            <div class="register-body">
                <?php if($errors): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        <?= implode('<br>', $errors) ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" id="registerForm">
                    <div class="form-group">
                        <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required>
                        <i class="fas fa-user form-icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                        <i class="fas fa-envelope form-icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required id="password">
                        <i class="fas fa-lock form-icon"></i>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-group">
                        <input type="password" name="confirm_password" class="form-control" placeholder="Xác nhận mật khẩu" required id="confirmPassword">
                        <i class="fas fa-lock form-icon"></i>
                        <div class="password-match" id="passwordMatch"></div>
                    </div>
                    
                    <button type="submit" class="btn-register">
                        <i class="fas fa-user-plus me-2"></i>
                        Đăng ký
                    </button>
                </form>
                
                <div class="login-link">
                    <p class="mb-0">Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            let strengthText = '';
            let strengthClass = '';
            
            if (strength < 2) {
                strengthText = 'Mật khẩu yếu';
                strengthClass = 'strength-weak';
            } else if (strength < 4) {
                strengthText = 'Mật khẩu trung bình';
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Mật khẩu mạnh';
                strengthClass = 'strength-strong';
            }
            
            strengthDiv.innerHTML = `<span class="${strengthClass}"><i class="fas fa-shield-alt me-1"></i>${strengthText}</span>`;
        });
        
        // Password match checker
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.innerHTML = '<span class="strength-strong"><i class="fas fa-check me-1"></i>Mật khẩu khớp</span>';
            } else {
                matchDiv.innerHTML = '<span class="strength-weak"><i class="fas fa-times me-1"></i>Mật khẩu không khớp</span>';
            }
        });
        
        // Form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const button = this.querySelector('.btn-register');
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang đăng ký...';
            button.disabled = true;
        });
        
        // Add focus animations to form inputs
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
