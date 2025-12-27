<?php
require_once 'config.php';
require_once 'functions.php';
$errors = [];
$redirect = $_GET['redirect'] ?? 'index.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $login = trim($_POST['login'] ?? '');
  $password = $_POST['password'] ?? '';
  $redirect = $_POST['redirect'] ?? 'index.php';
  
  if(!$login||!$password) $errors[]='Vui lòng nhập đầy đủ thông tin.';
  else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=:l OR email=:l LIMIT 1");
    $stmt->execute([':l'=>$login]); $u = $stmt->fetch();
    if(!$u || !password_verify($password, $u['password'])) $errors[]='Tên đăng nhập hoặc mật khẩu không đúng.';
    else {
      unset($u['password']);
      $_SESSION['user']=$u;
      header('Location: ' . $redirect); exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - BIBIBABA Food</title>
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
        
        /* Animated background particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .particle:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 60px; height: 60px; left: 20%; animation-delay: 1s; }
        .particle:nth-child(3) { width: 100px; height: 100px; left: 35%; animation-delay: 2s; }
        .particle:nth-child(4) { width: 40px; height: 40px; left: 70%; animation-delay: 3s; }
        .particle:nth-child(5) { width: 120px; height: 120px; left: 85%; animation-delay: 4s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
            50% { transform: translateY(-100px) rotate(180deg); opacity: 0.3; }
        }
        
        .login-container {
            position: relative;
            z-index: 10;
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        
        .login-card {
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
        
        .login-header {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .login-header .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s ease-in-out infinite;
            position: relative;
            z-index: 2;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        .login-header h2 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }
        
        .login-header p {
            opacity: 0.9;
            font-weight: 300;
            position: relative;
            z-index: 2;
        }
        
        .login-body {
            padding: 3rem 2rem;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 2rem;
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
        
        .btn-login {
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
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(45, 55, 72, 0.4);
        }
        
        .btn-login:active {
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
        
        .alert-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
            color: white;
        }
        
        .register-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e1e5e9;
        }
        
        .register-link a {
            color: #2d3748;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .register-link a:hover {
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
            .login-container {
                padding: 10px;
            }
            
            .login-header {
                padding: 2rem 1.5rem;
            }
            
            .login-body {
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
    <!-- Animated background particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <!-- Back to home button -->
    <div class="back-home">
        <a href="index.php">
            <i class="fas fa-arrow-left"></i>
            Về trang chủ
        </a>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h2>Chào mừng trở lại!</h2>
                <p>Đăng nhập để tiếp tục mua sắm</p>
            </div>
            
            <div class="login-body">
                <?php if(isset($_GET['registered'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Đăng ký thành công! Hãy đăng nhập để tiếp tục.
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['msg'])): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($_GET['msg']) ?>
                    </div>
                <?php endif; ?>
                
                <?php if($errors): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        <?= implode('<br>', $errors) ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" id="loginForm">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                    
                    <div class="form-group">
                        <input type="text" name="login" class="form-control" placeholder="Username hoặc Email" required>
                        <i class="fas fa-user form-icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                        <i class="fas fa-lock form-icon"></i>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Đăng nhập
                    </button>
                </form>
                
                <div class="register-link">
                    <p class="mb-0">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add form validation and smooth animations
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = this.querySelector('.btn-login');
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang đăng nhập...';
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
