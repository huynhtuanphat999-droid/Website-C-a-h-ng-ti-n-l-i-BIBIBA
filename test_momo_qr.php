<?php
// File test MoMo QR Code
session_start();

// Giả lập dữ liệu đơn hàng để test
$_SESSION['pending_order_id'] = 99999;
$_SESSION['pending_amount'] = 100000;

$orderId   = $_SESSION['pending_order_id'];
$amount    = $_SESSION['pending_amount'];
$returnUrl = "http://" . $_SERVER['HTTP_HOST'] . "/return_momo.php";

$endpoint    = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = "MOMO";
$accessKey   = "klm05TvNBzhg7h7j";
$secretKey   = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bs14nir";
$orderInfo   = "Test thanh toan don hang #$orderId";
$requestId   = time() . "";
$requestType = "captureWallet"; // Quan trọng: phải là captureWallet để có QR
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

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Test MoMo QR</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 2rem; background: #f8f9fa; }
        .container { max-width: 800px; background: white; padding: 2rem; border-radius: 15px; }
        pre { background: #f8f9fa; padding: 1rem; border-radius: 8px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>🧪 Test MoMo QR Code API</h2>
        <hr>
        
        <h5>📤 Request Data:</h5>
        <pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>
        
        <h5 class='mt-4'>📡 Đang gọi API MoMo...</h5>";

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$response = json_decode($result, true);

echo "<div class='alert alert-info'>HTTP Code: $httpCode</div>";

echo "<h5>📥 Response từ MoMo:</h5>
        <pre>" . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

if (isset($response['qrCodeUrl'])) {
    echo "<div class='alert alert-success'>
            <h5>✅ Có QR Code!</h5>
            <p><strong>QR Code URL:</strong> " . htmlspecialchars($response['qrCodeUrl']) . "</p>
            <img src='" . htmlspecialchars($response['qrCodeUrl']) . "' style='max-width: 300px; border: 2px solid #ddd; padding: 10px; border-radius: 10px;'>
          </div>";
} else {
    echo "<div class='alert alert-warning'>
            <h5>⚠ Không có QR Code</h5>
            <p>API không trả về qrCodeUrl. Có thể do:</p>
            <ul>
                <li>requestType không đúng (phải là 'captureWallet')</li>
                <li>Tài khoản test không hỗ trợ QR</li>
                <li>Cần đăng ký tài khoản MoMo Business thật</li>
            </ul>
          </div>";
}

if (isset($response['payUrl'])) {
    echo "<div class='alert alert-info'>
            <h5>🔗 Có Pay URL</h5>
            <p><a href='" . htmlspecialchars($response['payUrl']) . "' target='_blank' class='btn btn-primary'>Mở trang thanh toán MoMo</a></p>
          </div>";
}

echo "    </div>
</body>
</html>";
