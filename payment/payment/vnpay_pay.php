<?php
session_start();
require_once '../config.php';

if (empty($_SESSION['pending_order_id']) || empty($_SESSION['pending_amount'])) {
    die("<h3>Lỗi: Không tìm thấy thông tin đơn hàng!</h3>
         <p>Vui lòng <a href='../checkout.php'>quay lại đặt hàng</a> và thử lại.</p>");
}

$orderId = $_SESSION['pending_order_id'];
$amount  = $_SESSION['pending_amount'] * 100;

$vnp_TmnCode    = "SANDBOX123";
$vnp_HashSecret = "SANDBOXSECRET1234567890";
$vnp_Url        = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl  = "https://" . $_SERVER['HTTP_HOST'] . "/return_vnpay.php";

$inputData = array(
    "vnp_Version"    => "2.1.0",
    "vnp_Command"    => "pay",
    "vnp_TmnCode"    => $vnp_TmnCode,
    "vnp_Amount"     => $amount,
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode"   => "VND",
    "vnp_IpAddr"     => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
    "vnp_Locale"     => "vn",
    "vnp_OrderInfo"  => "Thanh toan don hang #$orderId",
    "vnp_OrderType"  => "other",
    "vnp_ReturnUrl"  => $vnp_Returnurl,
    "vnp_TxnRef"     => $orderId,
);

ksort($inputData);
$query = $hashdata = "";
foreach ($inputData as $key => $value) {
    $hashdata .= ($hashdata ? '&' : '') . urlencode($key) . "=" . urlencode($value);
    $query    .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url .= "?" . $query;
$vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
$vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

header('Location: ' . $vnp_Url);
exit();