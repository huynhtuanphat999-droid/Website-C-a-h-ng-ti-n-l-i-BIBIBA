<?php
session_start();
require_once '../config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$admin = $_SESSION['admin'];

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $orderId]);
    
    header('Location: orders.php?msg=updated');
    exit;
}

// Lấy danh sách đơn hàng
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (id = ? OR full_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $params[] = $search;
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Thống kê
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$paidOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'paid'")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total) FROM orders WHERE status IN ('paid', 'completed')")->fetchColumn() ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Admin</title>
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
            overflow-y: auto;
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
            margin-bottom: 1.5rem;
        }
        
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-card.total .icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-card.pending .icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .stat-card.paid .icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }
        
        .stat-card.revenue .icon {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table {
            margin: 0;
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .btn-action {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 8px;
        }
        
        .order-id {
            font-weight: bold;
            color: #2d3748;
        }
        
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        /* Order Cards */
        .order-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .order-card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1rem 1.25rem;
            border-bottom: 2px solid #dee2e6;
        }
        
        .order-number {
            font-size: 1.25rem;
            font-weight: bold;
            color: #2d3748;
        }
        
        .badge-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .badge-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .badge-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .badge-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .order-card-body {
            padding: 1.25rem;
            flex: 1;
        }
        
        .customer-info {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .customer-info i {
            font-size: 2rem;
        }
        
        .order-amount {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        
        .amount-text {
            font-size: 1.5rem;
            color: #28a745;
        }
        
        .order-date {
            display: flex;
            align-items: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .order-card-footer {
            padding: 1rem 1.25rem;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .btn-view {
            width: 100%;
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 55, 72, 0.4);
            color: white;
        }
        
        /* Animation */
        .order-card {
            animation: fadeInUp 0.5s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i>Đơn hàng</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i>Sản phẩm</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i>Khách hàng</a></li>
            <li><a href="news.php"><i class="fas fa-newspaper"></i>Tin tức</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i>Cài đặt</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h4 class="mb-0">Quản lý đơn hàng</h4>
            <small class="text-muted">Xem và quản lý tất cả đơn hàng</small>
        </div>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                Đã cập nhật trạng thái đơn hàng thành công!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card total">
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                    <h3><?= number_format($totalOrders) ?></h3>
                    <p class="text-muted mb-0">Tổng đơn hàng</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card pending">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <h3><?= number_format($pendingOrders) ?></h3>
                    <p class="text-muted mb-0">Chờ xử lý</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card paid">
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                    <h3><?= number_format($paidOrders) ?></h3>
                    <p class="text-muted mb-0">Đã thanh toán</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card revenue">
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                    <h3><?= number_format($totalRevenue) ?>₫</h3>
                    <p class="text-muted mb-0">Doanh thu</p>
                </div>
            </div>
        </div>
        
        <!-- Filter -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Mã ĐH, tên, SĐT, email..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Lọc
                    </button>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="orders.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Orders Cards -->
        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-5x text-muted mb-3"></i>
                <h5 class="text-muted">Không có đơn hàng nào</h5>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $statusClass = [
                        'pending' => 'warning',
                        'paid' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger'
                    ];
                    $statusText = [
                        'pending' => 'Chờ xử lý',
                        'paid' => 'Đã thanh toán',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy'
                    ];
                    $statusIcon = [
                        'pending' => 'clock',
                        'paid' => 'check-circle',
                        'completed' => 'check-double',
                        'cancelled' => 'times-circle'
                    ];
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="order-card">
                            <div class="order-card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="order-number">#<?= $order['id'] ?></span>
                                    <span class="badge badge-<?= $statusClass[$order['status']] ?? 'secondary' ?>">
                                        <i class="fas fa-<?= $statusIcon[$order['status']] ?? 'question' ?> me-1"></i>
                                        <?= $statusText[$order['status']] ?? $order['status'] ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="order-card-body">
                                <div class="customer-info">
                                    <i class="fas fa-user-circle me-2 text-primary"></i>
                                    <div>
                                        <strong><?= htmlspecialchars($order['full_name']) ?></strong>
                                        <small class="d-block text-muted"><?= htmlspecialchars($order['phone']) ?></small>
                                    </div>
                                </div>
                                
                                <div class="order-amount">
                                    <i class="fas fa-money-bill-wave me-2 text-success"></i>
                                    <strong class="amount-text"><?= number_format($order['total']) ?>₫</strong>
                                </div>
                                
                                <div class="order-date">
                                    <i class="far fa-calendar me-2 text-muted"></i>
                                    <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="order-card-footer">
                                <button class="btn btn-view" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#orderModal<?= $order['id'] ?>">
                                    <i class="fas fa-eye me-2"></i>Xem chi tiết
                                </button>
                            </div>
                        </div>
                    </div>
                                
                                <!-- Modal chi tiết đơn hàng -->
                                <div class="modal fade" id="orderModal<?= $order['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Chi tiết đơn hàng #<?= $order['id'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <h6>Thông tin khách hàng</h6>
                                                        <p class="mb-1"><strong>Họ tên:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
                                                        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                                                        <p class="mb-1"><strong>SĐT:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                                                        <p class="mb-1"><strong>Địa chỉ:</strong> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Thông tin đơn hàng</h6>
                                                        <p class="mb-1"><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                                                        <p class="mb-1"><strong>Tổng tiền:</strong> <span class="text-danger"><?= number_format($order['total']) ?>₫</span></p>
                                                        <p class="mb-1"><strong>Trạng thái:</strong> 
                                                            <span class="badge bg-<?= $statusClass[$order['status']] ?? 'secondary' ?>">
                                                                <?= $statusText[$order['status']] ?? $order['status'] ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                                <h6>Sản phẩm</h6>
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Tên sản phẩm</th>
                                                            <th>SL</th>
                                                            <th>Đơn giá</th>
                                                            <th>Thành tiền</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                                        $stmt->execute([$order['id']]);
                                                        $items = $stmt->fetchAll();
                                                        foreach ($items as $item):
                                                        ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                            <td><?= $item['qty'] ?></td>
                                                            <td><?= number_format($item['price']) ?>₫</td>
                                                            <td><?= number_format($item['total']) ?>₫</td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                
                                                <form method="POST" class="mt-3">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <select name="status" class="form-select">
                                                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                                                <option value="paid" <?= $order['status'] === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                                                                <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                                                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <button type="submit" name="update_status" class="btn btn-primary w-100">
                                                                <i class="fas fa-save me-2"></i>Cập nhật
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
