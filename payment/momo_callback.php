<?php
session_start();
require_once '../config/db.php';  // kết nối PDO

// Nhận dữ liệu từ Momo (POST hoặc GET tùy cấu hình)
$resultCode = $_REQUEST['resultCode'] ?? $_REQUEST['errorCode'] ?? null;

if ($resultCode == '0' || $resultCode === 0) {
    // Thanh toán thành công
    if (isset($_SESSION['payment_temp'])) {
        $orderId = $_SESSION['payment_temp']['order_id'];

        try {
            $pdo->beginTransaction();

            // Cập nhật trạng thái đơn hàng
            $sql = "UPDATE orders SET status = 'paid', payment_method = :pm, paid_at = NOW() 
                    WHERE id = :id AND status = 'pending'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':pm' => $_SESSION['payment_temp']['payment_method'],
                ':id' => $orderId
            ]);

            // Cập nhật số lượng sản phẩm (nếu cần)
            // ... code giảm stock ở đây

            $pdo->commit();

            // Xóa session tạm
            unset($_SESSION['payment_temp']);
            unset($_SESSION['pending_order_id']);
            unset($_SESSION['pending_amount']);

            // Chuyển hướng trang thành công
            header('Location: ../payment_success.php?order_id=' . $orderId);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Momo callback error: " . $e->getMessage());
        }
    }
}

// Nếu thất bại hoặc không có session → về trang lỗi
header('Location: ../payment_failed.php');
exit;