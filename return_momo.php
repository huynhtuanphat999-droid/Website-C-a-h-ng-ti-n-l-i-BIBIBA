<?php
session_start();
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php?msg=Vui lòng đăng nhập');
    exit;
}

if (isset($_GET['resultCode']) && $_GET['resultCode'] == 0) {
    $orderId = $_SESSION['pending_order_id'] ?? 0;
    if ($orderId) {
        $pdo->prepare("UPDATE orders SET status = 'paid', paid_at = NOW() WHERE id = ?")->execute([$orderId]);
        unset($_SESSION['pending_order_id'], $_SESSION['pending_amount']);
        header("Location: order_success.php?id=$orderId&method=momo");
    }
} else {
    header("Location: checkout.php?error=Thanh toán MoMo thất bại.");
}
exit();