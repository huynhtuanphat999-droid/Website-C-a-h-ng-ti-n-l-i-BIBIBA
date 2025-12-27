<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

$product_id = (int)($_GET['product_id'] ?? 0);
$page = (int)($_GET['page'] ?? 1);
$limit = 5;
$offset = ($page - 1) * $limit;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

try {
    // Lấy ratings
    $stmt = $pdo->prepare("
        SELECT r.id, r.rating, r.comment, r.created_at, u.username
        FROM ratings r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$product_id, $limit, $offset]);
    $ratings = $stmt->fetchAll();
    
    // Lấy tổng số ratings
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM ratings WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $total = $stmt->fetch()['total'];
    
    // Lấy thống kê
    $stmt = $pdo->prepare("
        SELECT 
            AVG(rating) as avg_rating,
            COUNT(*) as count,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM ratings WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    $stats = $stmt->fetch();
    
    // Kiểm tra user đã đánh giá chưa
    $user = current_user();
    $user_rating = null;
    if ($user) {
        $stmt = $pdo->prepare("SELECT rating, comment FROM ratings WHERE product_id = ? AND user_id = ?");
        $stmt->execute([$product_id, $user['id']]);
        $user_rating = $stmt->fetch();
    }
    
    echo json_encode([
        'success' => true,
        'ratings' => $ratings,
        'stats' => $stats,
        'user_rating' => $user_rating,
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
