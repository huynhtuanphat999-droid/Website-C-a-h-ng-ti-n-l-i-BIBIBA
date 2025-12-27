<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

$user = current_user();
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if (!$product_id || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Kiểm tra sản phẩm tồn tại
$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
$stmt->execute([$product_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit;
}

// Kiểm tra user đã mua sản phẩm này chưa
$stmt = $pdo->prepare("
    SELECT oi.id FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'completed'
    LIMIT 1
");
$stmt->execute([$user['id'], $product_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Bạn chỉ có thể đánh giá sản phẩm đã mua']);
    exit;
}

try {
    // Insert hoặc update rating
    $stmt = $pdo->prepare("
        INSERT INTO ratings (product_id, user_id, rating, comment)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        rating = VALUES(rating),
        comment = VALUES(comment),
        created_at = NOW()
    ");
    $stmt->execute([$product_id, $user['id'], $rating, $comment]);
    
    // Lấy rating mới
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as count
        FROM ratings WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Cảm ơn bạn đã đánh giá!',
        'average_rating' => round($result['avg_rating'], 1),
        'rating_count' => $result['count']
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
