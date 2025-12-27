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

// Xử lý xóa liên hệ
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM contacts WHERE id = ?")->execute([$id]);
    $success = 'Đã xóa tin nhắn!';
}

// Lấy danh sách liên hệ
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM contacts WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contacts = $stmt->fetchAll();

// Thống kê
$totalContacts = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$todayContacts = $pdo->query("SELECT COUNT(*) FROM contacts WHERE DATE(created_at) = CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Liên hệ - BIBIBABA Admin</title>
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
        
        .contact-card {
            transition: all 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
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
            <li><a href="contacts.php" class="active"><i class="fas fa-envelope"></i>Liên hệ</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i>Cài đặt</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Quản lý Liên hệ</h4>
        </div>
        
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <i class="fas fa-envelope fa-2x text-primary"></i>
                    <h3><?= number_format($totalContacts) ?></h3>
                    <p class="text-muted mb-0">Tổng tin nhắn</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <i class="fas fa-envelope-open fa-2x text-success"></i>
                    <h3><?= number_format($todayContacts) ?></h3>
                    <p class="text-muted mb-0">Tin nhắn hôm nay</p>
                </div>
            </div>
        </div>
        
        <!-- Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <label class="form-label">Tìm kiếm tin nhắn</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tên, email hoặc nội dung..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Tìm
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Contacts List -->
        <div class="row g-4">
            <?php if (empty($contacts)): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Chưa có tin nhắn nào</p>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <?php foreach ($contacts as $contact): ?>
                <div class="col-md-6">
                    <div class="card contact-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <i class="fas fa-user-circle text-primary me-2"></i>
                                        <?= htmlspecialchars($contact['name']) ?>
                                    </h5>
                                    <small class="text-muted">
                                        <i class="fas fa-envelope me-1"></i>
                                        <?= htmlspecialchars($contact['email']) ?>
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="if(confirm('Bạn có chắc muốn xóa tin nhắn này?')) window.location.href='contacts.php?delete=<?= $contact['id'] ?>'">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <div class="card-text">
                                <p class="mb-2"><?= nl2br(htmlspecialchars($contact['message'])) ?></p>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?>
                                </small>
                                <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-reply me-1"></i>Trả lời
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
