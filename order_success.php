<?php
session_start();
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php?msg=Vui lòng đăng nhập để xem đơn hàng');
    exit;
}

// Lấy thông tin đơn hàng
$order_id = $_GET['id'] ?? 0;
$method   = $_GET['method'] ?? 'cod';

if ($order_id) {
    // Optional: lấy thông tin đơn hàng để hiển thị chi tiết hơn (nếu muốn)
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
}

include 'header.php'; 
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
.success-box {
    max-width: 650px;
    margin: 80px auto;
    background: #fff;
    border-radius: 20px;
    padding: 50px 30px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    text-align: center;
}
.success-box i {
    font-size: 80px;
    color: #28a745;
    margin-bottom: 20px;
}
h2 { font-size: 2.2rem; margin-bottom: 15px; }
.text-muted { font-size: 1.1rem; }
.btn-custom {
    padding: 14px 30px;
    font-size: 17px;
    border-radius: 12px;
    font-weight: 600;
    margin: 10px;
    transition: all .3s;
}
.btn-custom:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
#confetti-canvas {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
    z-index: 9999;
}
</style>

<canvas id="confetti-canvas"></canvas>

<div class="success-box" data-aos="zoom-in">
    <i class="fa-solid fa-circle-check"></i>
    <h2>Đặt hàng thành công!</h2>
    <p class="text-muted">
        Mã đơn hàng: <strong class="text-danger">#<?= htmlspecialchars($order_id) ?></strong><br><br>
        <?php if ($method === 'cod'): ?>
            Phương thức thanh toán: <strong>Thanh toán khi nhận hàng (COD)</strong><br>
            Phí COD (nếu có): +25.000 ₫
        <?php else: ?>
            Phương thức thanh toán: <strong class="text-success">Thanh toán online thành công</strong><br>
            Số tiền: <strong><?= number_format($order['total'] ?? 0) ?> ₫</strong>
        <?php endif; ?>
    </p>
    <p class="mt-3 text-muted">
        Cảm ơn bạn đã mua sắm tại cửa hàng chúng tôi ❤️<br>
        Một email xác nhận đã được gửi đến <strong><?= htmlspecialchars($order['email'] ?? '') ?></strong>
    </p>

    <div class="mt-4">
        <a href="index.php" class="btn btn-success btn-custom">
            <i class="fa-solid fa-house"></i> Về trang chủ
        </a>
        <a href="#order-details" class="btn btn-outline-primary btn-custom" onclick="stopConfetti(); document.getElementById('order-details').scrollIntoView({behavior: 'smooth'})">
            <i class="fa-solid fa-receipt"></i> Xem chi tiết đơn hàng
        </a>
    </div>
</div>

<?php if ($order): ?>
<!-- Chi tiết đơn hàng -->
<div id="order-details" class="container my-5" data-aos="fade-up">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                <div class="card-header text-white text-center py-4" style="background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);">
                    <h3 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Chi tiết đơn hàng #<?= $order_id ?></h3>
                </div>
                
                <div class="card-body p-4">
                    <!-- Thông tin khách hàng -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-user-circle text-primary me-2"></i>
                            Thông tin khách hàng
                        </h5>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Họ tên:</strong> <?= htmlspecialchars($order['full_name']) ?>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Email:</strong> <?= htmlspecialchars($order['email']) ?>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                            </div>
                            <div class="col-12 mb-2">
                                <strong>Địa chỉ giao hàng:</strong><br>
                                <?= nl2br(htmlspecialchars($order['address'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sản phẩm đã đặt -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-shopping-bag text-success me-2"></i>
                            Sản phẩm đã đặt
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">Số lượng</th>
                                        <th class="text-end">Đơn giá</th>
                                        <th class="text-end">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                    $stmt->execute([$order_id]);
                                    $items = $stmt->fetchAll();
                                    
                                    foreach ($items as $item):
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?= $item['qty'] ?></span>
                                        </td>
                                        <td class="text-end"><?= number_format($item['price']) ?>₫</td>
                                        <td class="text-end">
                                            <strong class="text-success"><?= number_format($item['total']) ?>₫</strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                        <td class="text-end">
                                            <h5 class="mb-0 text-danger"><?= number_format($order['total']) ?>₫</h5>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Trạng thái đơn hàng -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Trạng thái đơn hàng
                        </h5>
                        <div class="alert alert-info mb-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock fa-2x me-3"></i>
                                <div>
                                    <strong>Trạng thái:</strong> 
                                    <?php
                                    $statusText = [
                                        'pending' => 'Đang chờ xử lý',
                                        'paid' => 'Đã thanh toán',
                                        'completed' => 'Hoàn thành',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                    echo $statusText[$order['status']] ?? $order['status'];
                                    ?>
                                    <br>
                                    <small class="text-muted">
                                        Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận đơn hàng.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phương thức thanh toán -->
                    <div class="mb-3">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-credit-card text-warning me-2"></i>
                            Phương thức thanh toán
                        </h5>
                        <div class="alert alert-light mb-0">
                            <?php if ($method === 'cod'): ?>
                                <i class="fas fa-truck text-warning me-2"></i>
                                <strong>Thanh toán khi nhận hàng (COD)</strong>
                                <br><small class="text-muted">Vui lòng chuẩn bị số tiền <?= number_format($order['total']) ?>₫ khi nhận hàng</small>
                            <?php elseif ($method === 'momo'): ?>
                                <img src="images/CONG-TY-CO-PHAN-DICH-VU-DI-DONG-TRUC-TUYEN_8011.png" width="30" class="me-2">
                                <strong>Ví MoMo</strong>
                                <br><small class="text-success">✓ Đã thanh toán thành công</small>
                            <?php elseif ($method === 'zalopay'): ?>
                                <img src="images/Logo-ZaloPay-Square-1024x1024.webp" width="30" class="me-2">
                                <strong>ZaloPay</strong>
                                <br><small class="text-success">✓ Đã thanh toán thành công</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Nút hành động -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-home me-2"></i>Về trang chủ
                        </a>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i>Tiếp tục mua sắm
                        </a>
                        <button onclick="window.print()" class="btn btn-outline-primary ms-2">
                            <i class="fas fa-print me-2"></i>In đơn hàng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .success-box, #confetti-canvas, nav, footer, .btn {
        display: none !important;
    }
    #order-details {
        margin: 0 !important;
    }
}
</style>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init({ duration: 1000 });
</script>

<!-- Confetti đẹp lung linh -->
<script>
const canvas = document.getElementById('confetti-canvas');
const ctx = canvas.getContext('2d');
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;
window.addEventListener('resize', () => {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
});

const pieces = [];
const colors = ['#f00', '#0f0', '#00f', '#ff0', '#f0f', '#0ff', '#ffa500', '#ff1493'];
let animationId;
let isConfettiRunning = true;

for(let i = 0; i < 300; i++) {
    pieces.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height - canvas.height,
        r: Math.random() * 4 + 1,
        d: Math.random() * 8 + 5,
        color: colors[Math.floor(Math.random() * colors.length)],
        tilt: Math.random() * 10 - 5,
        tiltAngleIncremental: Math.random() * 0.07 + 0.05,
        tiltAngle: 0
    });
}

function draw() {
    if (!isConfettiRunning) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        return;
    }
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    pieces.forEach(p => {
        p.tiltAngle += p.tiltAngleIncremental;
        p.y += p.d;
        p.tilt = Math.sin(p.tiltAngle) * 15;

        if (p.y > canvas.height) {
            p.y = -20;
            p.x = Math.random() * canvas.width;
        }

        ctx.beginPath();
        ctx.lineWidth = p.r;
        ctx.strokeStyle = p.color;
        ctx.moveTo(p.x + p.tilt + p.r / 2, p.y);
        ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r);
        ctx.stroke();
    });
    animationId = requestAnimationFrame(draw);
}

function stopConfetti() {
    isConfettiRunning = false;
    if (animationId) {
        cancelAnimationFrame(animationId);
    }
    // Fade out effect
    canvas.style.transition = 'opacity 0.5s ease';
    canvas.style.opacity = '0';
    setTimeout(() => {
        canvas.style.display = 'none';
    }, 500);
}

draw();
</script>

<?php include 'footer.php'; ?>
