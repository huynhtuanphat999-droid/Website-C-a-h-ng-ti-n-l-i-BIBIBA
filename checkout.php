<?php
require_once 'config.php';
require_once 'cart_functions.php';
require_once 'functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect=checkout.php&msg=Vui lòng đăng nhập để thanh toán');
    exit;
}

$cart = cart_get();
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// Load sản phẩm
$ids   = array_keys($cart);
$place = implode(',', array_fill(0, count($ids), '?'));
$stmt  = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($place)");
$stmt->execute($ids);
$rows  = $stmt->fetchAll();
$map   = [];
$total = 0;
foreach ($rows as $r) {
    $map[$r['id']] = $r;
    $total += $r['price'] * $cart[$r['id']];
}

$errors = [];

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname       = trim($_POST['fullname'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';

    // Validation
    if (empty($fullname)) {
        $errors[] = 'Vui lòng nhập họ và tên.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Vui lòng nhập email hợp lệ.';
    }
    if (empty($phone)) {
        $errors[] = 'Vui lòng nhập số điện thoại.';
    }
    if (empty($address)) {
        $errors[] = 'Vui lòng nhập địa chỉ giao hàng.';
    }
    if (empty($payment_method)) {
        $errors[] = 'Vui lòng chọn phương thức thanh toán.';
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $user_id = $_SESSION['user']['id'] ?? null;

            // Tạo đơn hàng
            $stmt = $pdo->prepare("INSERT INTO orders 
                (user_id, full_name, email, phone, address, total, status, created_at) 
                VALUES (:uid, :fn, :em, :ph, :ad, :total, 'pending', NOW())");

            $stmt->execute([
                ':uid'    => $user_id,
                ':fn'     => $fullname,
                ':em'     => $email,
                ':ph'     => $phone,
                ':ad'     => $address,
                ':total'  => $total
            ]);
            $orderId = $pdo->lastInsertId();

            // Thêm chi tiết đơn hàng
            $stmtItem = $pdo->prepare("INSERT INTO order_items 
                (order_id, product_id, product_name, qty, price, total) 
                VALUES (:oid, :pid, :pname, :qty, :price, :total)");

            foreach ($cart as $pid => $qty) {
                $p   = $map[$pid];
                $sub = $p['price'] * $qty;
                $stmtItem->execute([
                    ':oid'    => $orderId,
                    ':pid'    => $pid,
                    ':pname'  => $p['name'],
                    ':qty'    => $qty,
                    ':price'  => $p['price'],
                    ':total'  => $sub
                ]);
                $pdo->prepare("UPDATE products SET sales_count = sales_count + ? WHERE id = ?")
                    ->execute([$qty, $pid]);
            }

            $pdo->commit();
            cart_clear();

            // === CHUYỂN HƯỚNG THEO PHƯƠNG THỨC ===
            if ($payment_method === 'cod') {
                header("Location: order_success.php?id=$orderId&method=cod");
                exit;
            } else {
                // Lưu tạm để các file thanh toán dùng
                $_SESSION['pending_order_id'] = $orderId;
                $_SESSION['pending_amount']   = $total;

                switch ($payment_method) {
                    case 'momo':      header('Location: payment_qr.php?method=momo&order_id=' . $orderId);      exit;
                    case 'zalopay':   header('Location: payment_qr.php?method=zalopay&order_id=' . $orderId);   exit;
                    default:
                        $errors[] = 'Phương thức thanh toán chưa được hỗ trợ.';
                }
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'xin vui long thu lai';
        }
    }
}

include 'header.php';
?>

<style>
/* Background cho trang thanh toán */
body {
    background: linear-gradient(135deg, #d7ccc8 0%, #bcaaa4 50%, #d7ccc8 100%);
    background-attachment: fixed;
    min-height: 100vh;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('images/plan.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    opacity: 0.1;
    z-index: -1;
}

.checkout-header {
    margin: -1rem -15px 2rem;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.checkout-banner-image {
    width: 100%;
    height: auto;
    max-height: 200px;
    object-fit: cover;
    object-position: center;
    display: block;
    border-radius: 15px;
}

.checkout-banner-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    text-align: center;
    width: 100%;
    padding: 0 20px;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
}

.checkout-banner-overlay h2 {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0;
    color: white;
}

@media (max-width: 768px) {
    .checkout-banner-overlay h2 {
        font-size: 1.8rem;
    }
}

.bg-white {
    background: rgba(255, 255, 255, 0.98) !important;
    backdrop-filter: blur(10px);
}
</style>

<div class="checkout-header text-center position-relative">
    <img src="images/baner.png" alt="Thanh toán" class="checkout-banner-image">
    <div class="checkout-banner-overlay">
        <h2 class="mb-0"><i class="fas fa-credit-card me-2"></i>Xác nhận & Thanh toán</h2>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Form bên trái -->
    <div class="col-lg-7">
        <form method="post" class="bg-white p-4 rounded shadow">
            <h5 class="mb-4">Thông tin nhận hàng</h5>

            <div class="mb-3">
                <label class="form-label">Họ và tên *</label>
                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Số điện thoại *</label>
                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>

            <div class="mb-4">
                <label class="form-label">Địa chỉ giao hàng *</label>
                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>

            <h5 class="mb-3">Phương thức thanh toán *</h5>
            <div class="border rounded p-3">

                <label class="d-block border rounded p-3 mb-3" style="cursor:pointer;">
                    <input type="radio" name="payment_method" value="momo" class="me-3">
                    <img src="images/CONG-TY-CO-PHAN-DICH-VU-DI-DONG-TRUC-TUYEN_8011.png" width="50" alt="MoMo">
                    <strong class="ms-2">MoMo</strong>
                    <span class="badge bg-danger ms-2">Giảm 50k</span>
                </label>

                <label class="d-block border rounded p-3 mb-3" style="cursor:pointer;">
                    <input type="radio" name="payment_method" value="bank_qr" class="me-3">
                    <img src="images/Logo-ZaloPay-Square-1024x1024.webp" width="50" alt="ZaloPay">
                    <strong class="ms-2">ZaloPay</strong>
                </label>

                <label class="d-block border rounded p-3 mb-3 bg-light" style="cursor:pointer;">
                    <input type="radio" name="payment_method" value="cod" class="me-3">
                    <i class="fas fa-truck text-warning fa-lg"></i>
                    <strong class="ms-2">Thanh toán khi nhận hàng (COD)</strong>
                    <small class="text-muted d-block ms-5">boom hàng làm chó</small>
                </label>

            </div>

            <button type="submit" class="btn btn-success btn-lg w-100 mt-4">
                XÁC NHẬN & THANH TOÁN
            </button>
        </form>
    </div>

    <!-- Tóm tắt đơn hàng bên phải -->
    <div class="col-lg-5">
        <div class="bg-white p-4 rounded shadow sticky-top" style="top:20px;">
            <h5>Đơn hàng (<?= count($cart) ?> sản phẩm)</h5>
            <ul class="list-group mb-3">
                <?php foreach ($map as $pid => $p): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= htmlspecialchars($p['name']) ?> × <?= $cart[$pid] ?></span>
                        <span><?= pretty_money($p['price'] * $cart[$pid]) ?></span>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item d-flex justify-content-between fw-bold fs-5">
                    <span>Tổng cộng</span>
                    <span class="text-danger"><?= pretty_money($total) ?></span>
                </li>
            </ul>
            <small class="text-muted">
                Thanh toán an toàn • Mã hóa SSL 256-bit
            </small>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
