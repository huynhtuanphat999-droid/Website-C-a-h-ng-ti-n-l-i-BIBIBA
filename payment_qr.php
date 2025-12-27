<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$method = $_GET['method'] ?? '';
$orderId = $_GET['order_id'] ?? 0;

if (!$orderId || !in_array($method, ['momo', 'zalopay'])) {
    header('Location: cart.php');
    exit;
}

// Lấy thông tin đơn hàng
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: cart.php');
    exit;
}

// Xử lý xác nhận thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // Cập nhật trạng thái đơn hàng
    $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
    $stmt->execute([$orderId]);
    
    // Xóa session
    unset($_SESSION['pending_order_id']);
    unset($_SESSION['pending_amount']);
    
    // Chuyển đến trang thành công
    header("Location: order_success.php?id=$orderId&method=$method");
    exit;
}

include 'header.php';
?>

<style>
.payment-qr-container {
    max-width: 600px;
    margin: 3rem auto;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
}

.payment-header {
    background: linear-gradient(135deg, <?= $method === 'momo' ? '#a50064, #d82d8b' : '#0068ff, #00a8ff' ?>);
    color: white;
    padding: 2rem;
    text-align: center;
}

.payment-header img {
    width: 80px;
    height: 80px;
    background: white;
    border-radius: 15px;
    padding: 10px;
    margin-bottom: 1rem;
}

.payment-body {
    padding: 2rem;
}

.qr-section {
    text-align: center;
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    margin-bottom: 2rem;
}

.qr-code-box {
    width: 300px;
    height: 300px;
    margin: 0 auto 1rem;
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.qr-code-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.qr-upload-area {
    border: 3px dashed #dee2e6;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.qr-upload-area:hover {
    border-color: <?= $method === 'momo' ? '#a50064' : '#0068ff' ?>;
    background: #f8f9fa;
}

.qr-upload-area i {
    font-size: 3rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.order-info {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 1.5rem;
}

.order-info-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #dee2e6;
}

.order-info-item:last-child {
    border-bottom: none;
    font-size: 1.25rem;
    font-weight: bold;
    color: <?= $method === 'momo' ? '#a50064' : '#0068ff' ?>;
}

.instruction {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.instruction ol {
    margin: 0.5rem 0 0 0;
    padding-left: 1.5rem;
}

.instruction li {
    margin-bottom: 0.5rem;
}

.btn-confirm {
    background: linear-gradient(135deg, <?= $method === 'momo' ? '#a50064, #d82d8b' : '#0068ff, #00a8ff' ?>);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(<?= $method === 'momo' ? '165, 0, 100' : '0, 104, 255' ?>, 0.3);
}

.btn-confirm:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(<?= $method === 'momo' ? '165, 0, 100' : '0, 104, 255' ?>, 0.4);
}

.qr-info-text {
    color: #6c757d;
    font-size: 0.9rem;
    margin-top: 1rem;
}

#qrPreview {
    display: none;
}
</style>

<div class="payment-qr-container">
    <div class="payment-header">
        <img src="images/<?= $method === 'momo' ? 'CONG-TY-CO-PHAN-DICH-VU-DI-DONG-TRUC-TUYEN_8011.png' : 'Logo-ZaloPay-Square-1024x1024.webp' ?>" 
             alt="<?= $method === 'momo' ? 'MoMo' : 'ZaloPay' ?>">
        <h3 class="mb-2">Thanh toán qua <?= $method === 'momo' ? 'MoMo' : 'ZaloPay' ?></h3>
        <p class="mb-0 opacity-75">Quét mã QR để thanh toán</p>
    </div>

    <div class="payment-body">
        <div class="order-info">
            <div class="order-info-item">
                <span><i class="fas fa-receipt me-2"></i>Mã đơn hàng:</span>
                <strong>#<?= $orderId ?></strong>
            </div>
            <div class="order-info-item">
                <span><i class="fas fa-user me-2"></i>Khách hàng:</span>
                <strong><?= htmlspecialchars($order['full_name']) ?></strong>
            </div>
            <div class="order-info-item">
                <span><i class="fas fa-money-bill-wave me-2"></i>Số tiền thanh toán:</span>
                <strong><?= pretty_money($order['total']) ?></strong>
            </div>
        </div>

        <div class="qr-section">
            <h5 class="mb-3"><i class="fas fa-qrcode me-2"></i>Quét mã QR để thanh toán</h5>
            
            <div class="qr-code-box">
                <?php
                // Đường dẫn QR code theo phương thức thanh toán
                $qrImage = $method === 'momo' ? 'images/qr_momo.jpg' : 'images/qr_zalopay.jpg';
                
                // Kiểm tra file có tồn tại không
                if (file_exists($qrImage)):
                ?>
                    <img src="<?= $qrImage ?>" alt="QR Code <?= $method === 'momo' ? 'MoMo' : 'ZaloPay' ?>">
                <?php else: ?>
                    <div class="text-center p-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <p class="text-muted">
                            Chưa có QR code <?= $method === 'momo' ? 'MoMo' : 'ZaloPay' ?><br>
                            <small>Vui lòng thêm file: <strong><?= $qrImage ?></strong></small>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <p class="qr-info-text">
                <i class="fas fa-mobile-alt me-1"></i>
                Mở ứng dụng <?= $method === 'momo' ? 'MoMo' : 'ZaloPay' ?> và quét mã QR này để thanh toán
            </p>
        </div>

        <div class="instruction">
            <strong><i class="fas fa-info-circle me-2"></i>Hướng dẫn thanh toán:</strong>
            <ol>
                <li>Tải lên mã QR <?= $method === 'momo' ? 'MoMo' : 'ZaloPay' ?> của bạn ở trên</li>
                <li>Khách hàng sẽ quét mã QR này để thanh toán</li>
                <li>Sau khi nhận được tiền, click nút "Xác nhận đã thanh toán"</li>
                <li>Đơn hàng sẽ được cập nhật trạng thái thành công</li>
            </ol>
        </div>

        <form method="POST">
            <div class="d-grid gap-2">
                <button type="submit" name="confirm_payment" class="btn btn-confirm">
                    <i class="fas fa-check-circle me-2"></i>
                    Tôi đã thanh toán
                </button>
                <a href="checkout.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Quay lại
                </a>
            </div>
        </form>
    </div>
</div>



<?php include 'footer.php'; ?>
