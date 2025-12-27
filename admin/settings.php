<?php
session_start();
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$admin = $_SESSION['admin'];
$success = '';
$error = '';

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Mật khẩu mới không khớp';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự';
    } else {
        // Kiểm tra mật khẩu hiện tại
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$admin['id']]);
        $adminData = $stmt->fetch();
        
        if (password_verify($currentPassword, $adminData['password'])) {
            // Cập nhật mật khẩu mới
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $admin['id']]);
            
            $success = 'Đổi mật khẩu thành công!';
        } else {
            $error = 'Mật khẩu hiện tại không đúng';
        }
    }
}

// Số liên hệ chưa đọc
$unreadContacts = $pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'unread' OR status IS NULL")->fetchColumn();

// Lấy thống kê hệ thống
$stats = [
    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'total_products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_news' => $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT SUM(total) FROM orders WHERE status IN ('paid', 'completed')")->fetchColumn() ?? 0,
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài đặt - BIBIBABA Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-menu li a i {
            width: 25px;
            margin-right: 1rem;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 2rem;
        }
        
        .top-bar {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .info-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-burger me-2"></i>BIBIBABA</h4>
            <small class="opacity-75">Admin Panel</small>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i>Đơn hàng</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i>Sản phẩm</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i>Khách hàng</a></li>
            <li><a href="news.php"><i class="fas fa-newspaper"></i>Tin tức</a></li>
            <li><a href="contacts.php"><i class="fas fa-envelope"></i>Liên hệ
                <?php if ($unreadContacts > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $unreadContacts ?></span>
                <?php endif; ?>
            </a></li>
            <li><a href="settings.php" class="active"><i class="fas fa-cog"></i>Cài đặt</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Cài đặt Hệ thống</h4>
        </div>
        
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="row g-4">
            <!-- Thông tin Admin -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Thông tin Admin</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <span class="text-muted">Tên đăng nhập:</span>
                            <strong><?= htmlspecialchars($admin['username']) ?></strong>
                        </div>
                        <div class="info-item">
                            <span class="text-muted">Email:</span>
                            <strong><?= htmlspecialchars($admin['email']) ?></strong>
                        </div>
                        <div class="info-item">
                            <span class="text-muted">ID:</span>
                            <strong>#<?= $admin['id'] ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Thống kê Hệ thống -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Thống kê Hệ thống</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <span class="text-muted"><i class="fas fa-shopping-cart me-2"></i>Tổng đơn hàng:</span>
                            <strong class="text-primary"><?= number_format($stats['total_orders']) ?></strong>
                        </div>
                        <div class="info-item">
                            <span class="text-muted"><i class="fas fa-box me-2"></i>Tổng sản phẩm:</span>
                            <strong class="text-info"><?= number_format($stats['total_products']) ?></strong>
                        </div>
                        <div class="info-item">
                            <span class="text-muted"><i class="fas fa-users me-2"></i>Tổng khách hàng:</span>
                            <strong class="text-warning"><?= number_format($stats['total_users']) ?></strong>
                        </div>
                        <div class="info-item">
                            <span class="text-muted"><i class="fas fa-newspaper me-2"></i>Tổng tin tức:</span>
                            <strong class="text-secondary"><?= number_format($stats['total_news']) ?></strong>
                        </div>
                        <div class="info-item">
                            <span class="text-muted"><i class="fas fa-dollar-sign me-2"></i>Tổng doanh thu:</span>
                            <strong class="text-success"><?= number_format($stats['total_revenue']) ?>₫</strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Đổi mật khẩu -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Đổi mật khẩu</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Mật khẩu hiện tại *</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mật khẩu mới *</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="6">
                                    <small class="text-muted">Tối thiểu 6 ký tự</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Xác nhận mật khẩu mới *</label>
                                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="change_password" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Đổi mật khẩu
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Thông tin Hệ thống -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-server me-2"></i>Thông tin Hệ thống</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="text-muted">PHP Version:</span>
                                    <strong><?= phpversion() ?></strong>
                                </div>
                                <div class="info-item">
                                    <span class="text-muted">Server:</span>
                                    <strong><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="text-muted">Database:</span>
                                    <strong>MySQL <?= $pdo->query('SELECT VERSION()')->fetchColumn() ?></strong>
                                </div>
                                <div class="info-item">
                                    <span class="text-muted">Timezone:</span>
                                    <strong><?= date_default_timezone_get() ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Thao tác nhanh</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="../index.php" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-home me-2"></i>Xem trang chủ
                            </a>
                            <a href="dashboard.php" class="btn btn-outline-info">
                                <i class="fas fa-chart-line me-2"></i>Xem Dashboard
                            </a>
                            <a href="orders.php" class="btn btn-outline-warning">
                                <i class="fas fa-shopping-cart me-2"></i>Quản lý đơn hàng
                            </a>
                            <a href="products.php" class="btn btn-outline-success">
                                <i class="fas fa-box me-2"></i>Quản lý sản phẩm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
