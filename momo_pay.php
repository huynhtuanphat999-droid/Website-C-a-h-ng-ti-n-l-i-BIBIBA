<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['pending_order_id']) || !isset($_SESSION['pending_amount'])) {
    header('Location: cart.php');
    exit;
}

$orderId   = $_SESSION['pending_order_id'];
$amount    = $_SESSION['pending_amount'];
$returnUrl = "http://" . $_SERVER['HTTP_HOST'] . "/return_momo.php";

$endpoint    = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = "MOMO";
$accessKey   = "klm05TvNBzhg7h7j";
$secretKey   = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bs14nir";
$orderInfo   = "Thanh toan don hang #$orderId";
$requestId   = time() . "";
$requestType = "captureWallet";
$extraData   = "";

$rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$returnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$returnUrl&requestId=$requestId&requestType=$requestType";
$signature = hash_hmac("sha256", $rawHash, $secretKey);

$data = array(
    'partnerCode' => $partnerCode,
    'partnerName' => "Test",
    'storeId'     => "TestStore",
    'requestId'   => $requestId,
    'amount'      => $amount,
    'orderId'     => $orderId,
    'orderInfo'   => $orderInfo,
    'redirectUrl' => $returnUrl,
    'ipnUrl'      => $returnUrl,
    'lang'        => 'vi',
    'extraData'   => $extraData,
    'requestType' => $requestType,
    'signature'   => $signature
);

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$response = json_decode($result, true);

// Nếu có lỗi, hiển thị thông báo
if (!isset($response['payUrl']) && !isset($response['qrCodeUrl'])) {
    include 'header.php';
    echo '<div class="alert alert-danger">Lỗi MoMo: ' . ($response['message'] ?? 'Không có phản hồi') . '</div>';
    echo '<a href="checkout.php" class="btn btn-primary">Quay lại</a>';
    include 'footer.php';
    exit;
}

include 'header.php';
?>

<style>
.payment-container {
    max-width: 600px;
    margin: 2rem auto;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
}

.payment-header {
    background: linear-gradient(135deg, #a50064 0%, #d82d8b 100%);
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

.qr-container {
    text-align: center;
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    margin-bottom: 2rem;
}

.qr-code {
    width: 280px;
    height: 280px;
    margin: 0 auto;
    background: white;
    padding: 15px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.qr-code img {
    width: 100%;
    height: 100%;
    object-fit: contain;
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
    color: #a50064;
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

.btn-momo {
    background: linear-gradient(135deg, #a50064 0%, #d82d8b 100%);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(165, 0, 100, 0.3);
}

.btn-momo:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(165, 0, 100, 0.4);
    color: white;
}

.countdown {
    text-align: center;
    font-size: 1.5rem;
    color: #a50064;
    font-weight: bold;
    margin-top: 1rem;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.checking {
    animation: pulse 2s infinite;
}
</style>

<div class="payment-container">
    <div class="payment-header">
        <img src="images/CONG-TY-CO-PHAN-DICH-VU-DI-DONG-TRUC-TUYEN_8011.png" alt="MoMo">
        <h3 class="mb-2">Thanh toán qua MoMo</h3>
        <p class="mb-0 opacity-75">Quét mã QR để thanh toán</p>
    </div>

    <div class="payment-body">
        <div class="order-info">
            <div class="order-info-item">
                <span><i class="fas fa-receipt me-2"></i>Mã đơn hàng:</span>
                <strong>#<?= $orderId ?></strong>
            </div>
            <div class="order-info-item">
                <span><i class="fas fa-money-bill-wave me-2"></i>Số tiền:</span>
                <strong class="text-danger"><?= pretty_money($amount) ?></strong>
            </div>
        </div>

        <?php if (isset($response['qrCodeUrl'])): ?>
        <div class="qr-container">
            <h5 class="mb-3"><i class="fas fa-qrcode me-2"></i>Quét mã QR</h5>
            <div class="qr-code">
                <img src="<?= htmlspecialchars($response['qrCodeUrl']) ?>" alt="MoMo QR Code">
            </div>
            <div class="countdown checking mt-3">
                <i class="fas fa-spinner fa-spin me-2"></i>
                Đang chờ thanh toán...
            </div>
        </div>
        <?php endif; ?>

        <div class="instruction">
            <strong><i class="fas fa-info-circle me-2"></i>Hướng dẫn thanh toán:</strong>
            <ol>
                <li>Mở ứng dụng MoMo trên điện thoại</li>
                <li>Chọn "Quét mã QR" hoặc nhấn vào biểu tượng máy ảnh</li>
                <li>Quét mã QR bên trên</li>
                <li>Xác nhận thông tin và hoàn tất thanh toán</li>
            </ol>
        </div>

        <div class="d-grid gap-2">
            <?php if (isset($response['payUrl'])): ?>
            <a href="<?= htmlspecialchars($response['payUrl']) ?>" class="btn btn-momo">
                <i class="fas fa-mobile-alt me-2"></i>
                Mở ứng dụng MoMo
            </a>
            <?php endif; ?>
            <a href="checkout.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Quay lại
            </a>
        </div>
    </div>
</div>

<script>
// Tự động kiểm tra trạng thái thanh toán mỗi 3 giây
let checkCount = 0;
const maxChecks = 60; // Kiểm tra tối đa 3 phút

const checkInterval = setInterval(() => {
    checkCount++;
    
    // Gọi API kiểm tra trạng thái (bạn cần tạo file check_payment.php)
    fetch('check_payment.php?order_id=<?= $orderId ?>')
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                clearInterval(checkInterval);
                window.location.href = 'order_success.php?id=<?= $orderId ?>&method=momo';
            }
        })
        .catch(err => console.log('Checking...'));
    
    // Dừng sau 3 phút
    if (checkCount >= maxChecks) {
        clearInterval(checkInterval);
        document.querySelector('.countdown').innerHTML = 
            '<i class="fas fa-clock me-2"></i>Hết thời gian chờ. Vui lòng kiểm tra lại đơn hàng.';
    }
}, 3000);
</script>

<?php include 'footer.php'; ?>