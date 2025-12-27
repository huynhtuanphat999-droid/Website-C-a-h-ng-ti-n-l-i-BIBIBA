<?php
session_start();
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$admin = $_SESSION['admin'];

// Thống kê
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total) FROM orders WHERE status IN ('paid', 'completed')")->fetchColumn() ?? 0;

// Đơn hàng gần đây
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10")->fetchAll();

// Sản phẩm bán chạy
$topProducts = $pdo->query("SELECT * FROM products ORDER BY sales_count DESC LIMIT 5")->fetchAll();

// Số liên hệ chưa đọc
$unreadContacts = $pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'unread' OR status IS NULL")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BIBIBABA</title>
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
        
        .sidebar-header h4 {
            margin: 0;
            font-size: 1.5rem;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stat-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-card.orders .icon {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
        }
        
        .stat-card.products .icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .stat-card.users .icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .stat-card.revenue .icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }
        
        .stat-card p {
            color: #6c757d;
            margin: 0;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card-header {
            background: white;
            border-bottom: 2px solid #f8f9fa;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }
        
        .table {
            margin: 0;
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
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i>Dashboard</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i>Đơn hàng</a></li>
            <li><a href="inventory.php"><i class="fas fa-boxes"></i>Tồn kho</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i>Sản phẩm</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i>Khách hàng</a></li>
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
            <div>
                <h4 class="mb-0">Dashboard</h4>
                <small class="text-muted">Chào mừng trở lại, <?= htmlspecialchars($admin['username']) ?>!</small>
            </div>
            <div>
                <span class="text-muted me-3">
                    <i class="far fa-calendar me-2"></i>
                    <?= date('d/m/Y') ?>
                </span>
                <span class="text-muted">
                    <i class="far fa-clock me-2"></i>
                    <?= date('H:i') ?>
                </span>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card orders">
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3><?= number_format($totalOrders) ?></h3>
                    <p>Tổng đơn hàng</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card products">
                    <div class="icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3><?= number_format($totalProducts) ?></h3>
                    <p>Sản phẩm</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card users">
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?= number_format($totalUsers) ?></h3>
                    <p>Khách hàng</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card revenue">
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3><?= number_format($totalRevenue) ?>₫</h3>
                    <p>Doanh thu</p>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Recent Orders -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Đơn hàng gần đây
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Mã ĐH</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đặt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><strong>#<?= $order['id'] ?></strong></td>
                                        <td><?= htmlspecialchars($order['full_name']) ?></td>
                                        <td><strong><?= number_format($order['total']) ?>₫</strong></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'warning',
                                                'paid' => 'success',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $statusText = [
                                                'pending' => 'Chờ xử lý',
                                                'paid' => 'Đã thanh toán',
                                                'completed' => 'Hoàn thành',
                                                'cancelled' => 'Đã hủy'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $statusClass[$order['status']] ?? 'secondary' ?>">
                                                <?= $statusText[$order['status']] ?? $order['status'] ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Products -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-fire me-2"></i>
                        Sản phẩm bán chạy
                    </div>
                    <div class="card-body">
                        <?php foreach ($topProducts as $product): ?>
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <?php if ($product['image']): ?>
                            <img src="../<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                            <?php endif; ?>
                            <div class="ms-3 flex-grow-1">
                                <strong class="d-block"><?= htmlspecialchars($product['name']) ?></strong>
                                <small class="text-muted">Đã bán: <?= $product['sales_count'] ?></small>
                            </div>
                            <strong class="text-primary"><?= number_format($product['price']) ?>₫</strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
