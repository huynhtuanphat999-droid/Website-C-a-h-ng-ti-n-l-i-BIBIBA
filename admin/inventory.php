<?php
session_start();
require_once '../config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$admin = $_SESSION['admin'];
$message = '';
$messageType = '';

// Xử lý cập nhật tồn kho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_stock'])) {
        $productId = $_POST['product_id'];
        $action = $_POST['action'];
        $quantity = abs((int)$_POST['quantity']);
        
        if ($quantity > 0) {
            if ($action === 'add') {
                $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                $stmt->execute([$quantity, $productId]);
                $message = "Đã thêm $quantity sản phẩm vào kho";
                $messageType = 'success';
            } elseif ($action === 'remove') {
                // Kiểm tra số lượng tồn kho trước khi trừ
                $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $currentStock = $stmt->fetchColumn();
                
                if ($currentStock >= $quantity) {
                    $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$quantity, $productId]);
                    $message = "Đã xuất $quantity sản phẩm khỏi kho";
                    $messageType = 'success';
                } else {
                    $message = "Không đủ hàng trong kho! Hiện có: $currentStock";
                    $messageType = 'danger';
                }
            }
        }
    }
}

// Lấy danh sách sản phẩm
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Thống kê
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 10")->fetchColumn();
$outOfStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock = 0")->fetchColumn();
$totalStockValue = $pdo->query("SELECT SUM(stock * price) FROM products")->fetchColumn() ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tồn kho - Admin</title>
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
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
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
        
        .stat-card.low .icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .stat-card.out .icon {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }
        
        .stat-card.value .icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .product-card-body {
            padding: 1.5rem;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 1rem;
        }
        
        .stock-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .stock-high {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .stock-medium {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
        }
        
        .stock-low {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .stock-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .stock-input {
            width: 80px;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.5rem;
            font-weight: bold;
        }
        
        .btn-stock {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-add {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
        }
        
        .btn-add:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
            color: white;
        }
        
        .btn-remove {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
        }
        
        .btn-remove:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
            color: white;
        }
        
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
            <li><a href="inventory.php" class="active"><i class="fas fa-boxes"></i>Tồn kho</a></li>
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
            <h4 class="mb-0">Quản lý tồn kho</h4>
            <small class="text-muted">Theo dõi và cập nhật số lượng hàng tồn kho</small>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card total">
                    <div class="icon"><i class="fas fa-boxes"></i></div>
                    <h3><?= number_format($totalProducts) ?></h3>
                    <p class="text-muted mb-0">Tổng sản phẩm</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card low">
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <h3><?= number_format($lowStock) ?></h3>
                    <p class="text-muted mb-0">Sắp hết hàng</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card out">
                    <div class="icon"><i class="fas fa-times-circle"></i></div>
                    <h3><?= number_format($outOfStock) ?></h3>
                    <p class="text-muted mb-0">Hết hàng</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card value">
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                    <h3><?= number_format($totalStockValue) ?>₫</h3>
                    <p class="text-muted mb-0">Giá trị tồn kho</p>
                </div>
            </div>
        </div>
        
        <!-- Filter -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Tìm kiếm sản phẩm</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tên sản phẩm..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Danh mục</label>
                    <select name="category" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="food" <?= $category === 'food' ? 'selected' : '' ?>>Đồ ăn</option>
                        <option value="drink" <?= $category === 'drink' ? 'selected' : '' ?>>Đồ uống</option>
                        <option value="dessert" <?= $category === 'dessert' ? 'selected' : '' ?>>Tráng miệng</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Lọc
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="inventory.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Products List -->
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-5x text-muted mb-3"></i>
                <h5 class="text-muted">Không có sản phẩm nào</h5>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <?php
                $stock = $product['stock'];
                $stockClass = $stock >= 50 ? 'stock-high' : ($stock >= 10 ? 'stock-medium' : 'stock-low');
                $stockIcon = $stock >= 50 ? 'check-circle' : ($stock >= 10 ? 'exclamation-triangle' : 'times-circle');
                ?>
                <div class="product-card">
                    <div class="product-card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <?php if ($product['image']): ?>
                                        <img src="../<?= htmlspecialchars($product['image']) ?>" 
                                             class="product-image" 
                                             alt="<?= htmlspecialchars($product['name']) ?>">
                                    <?php else: ?>
                                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-image fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-tag me-1"></i>
                                            <?= $product['category'] ?>
                                        </small>
                                        <br>
                                        <small class="text-success fw-bold">
                                            <?= number_format($product['price']) ?>₫
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 text-center">
                                <small class="text-muted d-block mb-2">Tồn kho hiện tại</small>
                                <span class="stock-badge <?= $stockClass ?>">
                                    <i class="fas fa-<?= $stockIcon ?> me-1"></i>
                                    <?= number_format($stock) ?> sản phẩm
                                </span>
                            </div>
                            
                            <div class="col-md-5">
                                <form method="POST" class="stock-controls">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    
                                    <input type="number" name="quantity" class="stock-input" 
                                           value="10" min="1" max="1000" required>
                                    
                                    <button type="submit" name="update_stock" value="1" 
                                            class="btn btn-add btn-stock"
                                            onclick="this.form.action.value='add'">
                                        <input type="hidden" name="action" value="add">
                                        <i class="fas fa-plus me-1"></i>Nhập hàng
                                    </button>
                                    
                                    <button type="submit" name="update_stock" value="1" 
                                            class="btn btn-remove btn-stock"
                                            onclick="this.form.action.value='remove'; return confirm('Xác nhận xuất hàng?')">
                                        <input type="hidden" name="action" value="remove">
                                        <i class="fas fa-minus me-1"></i>Xuất hàng
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fix form action issue
        document.querySelectorAll('form').forEach(form => {
            const addBtn = form.querySelector('.btn-add');
            const removeBtn = form.querySelector('.btn-remove');
            
            if (addBtn) {
                addBtn.addEventListener('click', function(e) {
                    form.querySelector('input[name="action"]').value = 'add';
                });
            }
            
            if (removeBtn) {
                removeBtn.addEventListener('click', function(e) {
                    form.querySelector('input[name="action"]').value = 'remove';
                });
            }
        });
    </script>
</body>
</html>
