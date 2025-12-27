<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing order ID']);
    exit;
}

// Kiểm tra trạng thái đơn hàng trong database
try {
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
        exit;
    }
    
    // Nếu đơn hàng đã được thanh toán (status = 'paid' hoặc 'completed')
    if (in_array($order['status'], ['paid', 'completed', 'success'])) {
        echo json_encode(['status' => 'success', 'message' => 'Payment completed']);
    } else {
        echo json_encode(['status' => 'pending', 'message' => 'Waiting for payment']);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
