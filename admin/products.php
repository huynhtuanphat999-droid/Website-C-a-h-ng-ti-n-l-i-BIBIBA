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

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        $success = 'Xóa sản phẩm thành công!';
    } catch (Exception $e) {
        $error = 'Không thể xóa sản phẩm này!';
    }
}

// Xử lý thêm/sửa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $id = $_POST['product_id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $category = $_POST['category'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $image = $_POST['image'] ?? '';
    
    // Tạo slug từ tên
    function create_slug($string) {
        $search = array(
            '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
            '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
            '#(ì|í|ị|ỉ|ĩ)#',
            '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
            '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
            '#(ỳ|ý|ỵ|ỷ|ỹ)#',
            '#(đ)#',
            '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
            '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
            '#(Ì|Í|Ị|Ỉ|Ĩ)#',
            '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
            '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
            '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
            '#(Đ)#',
            "/[^a-zA-Z0-9\-\_]/",
        );
        $replace = array(
            'a',
            'e',
            'i',
            'o',
            'u',
            'y',
            'd',
            'A',
            'E',
            'I',
            'O',
            'U',
            'Y',
            'D',
            '-',
        );
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', '-', $string);
        $string = strtolower($string);
        return $string;
    }
    
    $slug = create_slug($name);
    
    if (empty($name) || empty($category) || $price <= 0) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } else {
        try {
            if ($id > 0) {
                // Cập nhật sản phẩm
                $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, slug=?, description=?, price=?, image=?, featured=? WHERE id=?");
                $stmt->execute([$name, $category, $slug, $description, $price, $image, $featured, $id]);
                $success = 'Cập nhật sản phẩm thành công!';
            } else {
                // Thêm sản phẩm mới
                $stmt = $pdo->prepare("INSERT INTO products (name, category, slug, description, price, image, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $category, $slug, $description, $price, $image, $featured]);
                $success = 'Thêm sản phẩm mới thành công!';
            }
        } catch (Exception $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Số liên hệ chưa đọc
$unreadContacts = $pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'unread' OR status IS NULL")->fetchColumn();

// Lọc sản phẩm
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Thống kê
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$foodCount = $pdo->query("SELECT COUNT(*) FROM products WHERE category = 'food'")->fetchColumn();
$drinkCount = $pdo->query("SELECT COUNT(*) FROM products WHERE category = 'drink'")->fetchColumn();
$dessertCount = $pdo->query("SELECT COUNT(*) FROM products WHERE category = 'dessert'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm - BIBIBABA Admin</title>
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
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
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
            <li><a href="products.php" class="active"><i class="fas fa-box"></i>Sản phẩm</a></li>
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
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-box me-2"></i>Quản lý Sản phẩm</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()">
                    <i class="fas fa-plus me-2"></i>Thêm sản phẩm
                </button>
            </div>
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
        
        <!-- Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-box fa-2x text-primary"></i>
                    <h3><?= number_format($totalProducts) ?></h3>
                    <p class="text-muted mb-0">Tổng sản phẩm</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-hamburger fa-2x text-warning"></i>
                    <h3><?= number_format($foodCount) ?></h3>
                    <p class="text-muted mb-0">Đồ ăn</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-coffee fa-2x text-info"></i>
                    <h3><?= number_format($drinkCount) ?></h3>
                    <p class="text-muted mb-0">Đồ uống</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-ice-cream fa-2x text-danger"></i>
                    <h3><?= number_format($dessertCount) ?></h3>
                    <p class="text-muted mb-0">Tráng miệng</p>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Tìm kiếm</label>
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
                        <a href="products.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-redo me-2"></i>Đặt lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Products Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Hình ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Đã bán</th>
                                <th>Nổi bật</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Không có sản phẩm nào</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><strong>#<?= $product['id'] ?></strong></td>
                                    <td>
                                        <?php if ($product['image']): ?>
                                            <img src="../<?= htmlspecialchars($product['image']) ?>" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                                 class="product-img">
                                        <?php else: ?>
                                            <div class="product-img bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($product['name']) ?></strong>
                                        <?php if ($product['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $categoryBadge = [
                                            'food' => ['class' => 'warning', 'icon' => 'hamburger', 'text' => 'Đồ ăn'],
                                            'drink' => ['class' => 'info', 'icon' => 'coffee', 'text' => 'Đồ uống'],
                                            'dessert' => ['class' => 'danger', 'icon' => 'ice-cream', 'text' => 'Tráng miệng']
                                        ];
                                        $cat = $categoryBadge[$product['category']] ?? ['class' => 'secondary', 'icon' => 'box', 'text' => $product['category']];
                                        ?>
                                        <span class="badge bg-<?= $cat['class'] ?>">
                                            <i class="fas fa-<?= $cat['icon'] ?> me-1"></i>
                                            <?= $cat['text'] ?>
                                        </span>
                                    </td>
                                    <td><strong class="text-primary"><?= number_format($product['price']) ?>₫</strong></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-fire me-1"></i>
                                            <?= number_format($product['sales_count']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($product['featured']): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-star me-1"></i>Nổi bật
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" 
                                                onclick='editProduct(<?= json_encode($product) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="if(confirm('Bạn có chắc muốn xóa sản phẩm này?')) window.location.href='products.php?delete=<?= $product['id'] ?>'">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
    
    <!-- Modal Thêm/Sửa Sản phẩm -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm sản phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="product_id">
                        
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Tên sản phẩm *</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Danh mục *</label>
                                <select name="category" id="category" class="form-select" required>
                                    <option value="">Chọn danh mục</option>
                                    <option value="food">Đồ ăn</option>
                                    <option value="drink">Đồ uống</option>
                                    <option value="dessert">Tráng miệng</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Giá (VNĐ) *</label>
                                <input type="number" name="price" id="price" class="form-control" min="0" step="1000" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Hình ảnh</label>
                                <input type="text" name="image" id="image" class="form-control" placeholder="images/ten-hinh.jpg">
                                <small class="text-muted">Ví dụ: images/t1.jpg</small>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Mô tả</label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="featured" id="featured" class="form-check-input">
                                    <label class="form-check-label" for="featured">
                                        <i class="fas fa-star text-warning me-1"></i>
                                        Sản phẩm nổi bật
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="save_product" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Lưu sản phẩm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('modalTitle').textContent = 'Thêm sản phẩm mới';
            document.getElementById('product_id').value = '';
            document.getElementById('name').value = '';
            document.getElementById('category').value = '';
            document.getElementById('price').value = '';
            document.getElementById('image').value = '';
            document.getElementById('description').value = '';
            document.getElementById('featured').checked = false;
        }
        
        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Sửa sản phẩm';
            document.getElementById('product_id').value = product.id;
            document.getElementById('name').value = product.name;
            document.getElementById('category').value = product.category;
            document.getElementById('price').value = product.price;
            document.getElementById('image').value = product.image || '';
            document.getElementById('description').value = product.description || '';
            document.getElementById('featured').checked = product.featured == 1;
            
            var modal = new bootstrap.Modal(document.getElementById('productModal'));
            modal.show();
        }
    </script>
</body>
</html>
