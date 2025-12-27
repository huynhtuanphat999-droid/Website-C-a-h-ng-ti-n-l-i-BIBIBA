<?php session_start(); 
$order_id = $_GET['order_id'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Thanh toán thành công</title>
    <style>
        body {font-family: Arial; text-align:center; padding:50px; background:#f7f8fc;}
        .success {color:green; font-size:50px;}
        .box {max-width:600px; margin:0 auto; padding:40px; background:white; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.1);}
    </style>
</head>
<body>
<div class="box">
    <div class="success">✓</div>
    <h1>CẢM ƠN BẠN ĐÃ THANH TOÁN!</h1>
    <p>Mã đơn hàng: <strong>#<?php echo htmlspecialchars($order_id); ?></strong></p>
    <p>Chúng tôi đã nhận được tiền và đang xử lý đơn hàng của bạn.</p>
    <hr>
    <p><a href="../index.php">← Về trang chủ</a> | <a href="order_detail.php?id=<?php echo $order_id; ?>">Xem chi tiết đơn hàng</a></p>
</div>
</body>
</html>