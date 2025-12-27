<?php
// return_vnpay.php - ĐẶT Ở THƯ MỤC GỐC (cùng cấp với checkout.php)
session_start();
require_once 'config.php';   // config.php cùng cấp

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php?msg=Vui lòng đăng nhập');
    exit;
}

// Bật debug 1 lần để xem VNPAY trả gì (tùy chọn)
// echo "<pre>"; print_r($_GET); echo "</pre>"; exit;

if (!isset($_GET['vnp_ResponseCode'])) {
    die("Lỗi: Không nhận được phản hồi từ VNPAY!");
}

$responseCode = $_GET['vnp_ResponseCode'];

if ($responseCode === '00') {
    // THANH TOÁN THÀNH CÔNG
    $orderId = $_SESSION['pending_order_id'] ?? null;
    if ($orderId) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'paid', paid_at = NOW() WHERE id = ?");
            $stmt->execute([$orderId]);

            // Xóa session tạm
            unset($_SESSION['pending_order_id'], $_SESSION['pending_amount']);

            // CHUYỂN VỀ TRANG CẢM ƠN
            header("Location: order_success.php?id=$orderId&method=vnpay");
            exit;
        } catch (Exception $e) {
            die("Lỗi cập nhật đơn hàng: " . $e->getMessage());
        }
    } else {
        die("Lỗi: Không tìm thấy mã đơn hàng!");
    }
} else {
    // THANH TOÁN THẤT BẠI HOẶC BỊ HỦY
    unset($_SESSION['pending_order_id'], $_SESSION['pending_amount']);

    // SỬA DÒNG NÀY: DÙNG ĐƯỜNG DẪN ĐÚNG!!!
  header("Location: checkout.php?error=VNPAY_ERROR_$responseCode");
    exit;
}