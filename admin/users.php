<?php
session_start();
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$admin = $_SESSION['admin'];

// Tìm kiếm
$search = $_GET['search'] ?? '';

$sql = "SELECT u.*, COUNT(DISTINCT o.id) as total_orders, SUM(o.total) as total_spent 
        FROM users u 
        LEFT JOIN orders o ON u.id = o.user_id 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (u.username LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Thống kê
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$newUsersToday = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$activeUsers = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM orders WHERE user_id IS NOT NULL")->fetchColumn();

// Số liên hệ chưa đọc
$unreadContacts = $pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'unread' OR status IS NULL")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khách hàng - BIBIBABA Admin</title>
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
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
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
            <li><a href="users.php" class="active"><i class="fas fa-users"></i>Khách hàng</a></li>
            <li><a href="news.php"><i class="fas fa-newspaper"></i>Tin tức</a></li>
            <li><a href="contacts.php"><i class="fas fa-envelope"></i>Liên hệ
                <?php if ($unreadContacts > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $unreadContacts ?></span>
                <?php endif; ?>
            </a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i>Cài đặt</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h4 class="mb-0"><i class="fas fa-users me-2"></i>Quản lý Khách hàng</h4>
        </div>
        
        <!-- Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-users fa-2x text-primary"></i>
                    <h3><?= number_format($totalUsers) ?></h3>
                    <p class="text-muted mb-0">Tổng khách hàng</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-user-plus fa-2x text-success"></i>
                    <h3><?= number_format($newUsersToday) ?></h3>
                    <p class="text-muted mb-0">Đăng ký hôm nay</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-user-check fa-2x text-info"></i>
                    <h3><?= number_format($activeUsers) ?></h3>
                    <p class="text-muted mb-0">Đã mua hàng</p>
                </div>
            </div>
        </div>
        
        <!-- Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Tìm kiếm khách hàng</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tên đăng nhập hoặc email..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="users.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-redo me-2"></i>Đặt lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tên đăng nhập</th>
                                <th>Email</th>
                                <th>Số đơn hàng</th>
                                <th>Tổng chi tiêu</th>
                                <th>Ngày đăng ký</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Không có khách hàng nào</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><strong>#<?= $user['id'] ?></strong></td>
                                    <td>
                                        <i class="fas fa-user-circle text-primary me-2"></i>
                                        <strong><?= htmlspecialchars($user['username']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <i class="fas fa-shopping-cart me-1"></i>
                                            <?= number_format($user['total_orders']) ?> đơn
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            <?= number_format($user['total_spent'] ?? 0) ?>₫
                                        </strong>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
