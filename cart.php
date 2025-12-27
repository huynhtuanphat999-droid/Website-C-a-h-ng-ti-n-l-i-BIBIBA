<?php
require_once 'config.php';
require_once 'cart_functions.php';
require_once 'functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect=cart.php&msg=Vui lòng đăng nhập để xem giỏ hàng');
    exit;
}

// xử lý update/remove
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['update'])){
    foreach($_POST['qty'] as $pid=>$q) cart_update($pid,$q);
    header('Location: cart.php'); exit;
  }
  if(isset($_POST['remove'])){
    cart_remove((int)$_POST['product_id']);
    header('Location: cart.php'); exit;
  }
}

$cart = cart_get();
$items = [];
$total = 0;
if(!empty($cart)){
  $ids = array_keys($cart);
  $place = implode(',', array_fill(0,count($ids),'?'));
  $stmt = $pdo->prepare("SELECT id,name,price,image FROM products WHERE id IN ($place)");
  $stmt->execute($ids);
  $rows = $stmt->fetchAll();
  foreach($rows as $r){
    $qty = $cart[$r['id']];
    $sub = $r['price'] * $qty;
    $items[] = ['product'=>$r,'qty'=>$qty,'sub'=>$sub];
    $total += $sub;
  }
}

include 'header.php';
?>

<style>
/* Background cho trang giỏ hàng */
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

.cart-header {
    margin: -1rem -15px 2rem;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.cart-banner-image {
    width: 100%;
    height: auto;
    max-height: 200px;
    object-fit: cover;
    object-position: center;
    display: block;
    border-radius: 15px;
}

.cart-banner-overlay {
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

.cart-banner-overlay h2 {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0;
    color: white;
}

@media (max-width: 768px) {
    .cart-banner-overlay h2 {
        font-size: 1.8rem;
    }
}

.cart-container {
    background: rgba(255, 255, 255, 0.98);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    backdrop-filter: blur(10px);
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 2px solid #f7fafc;
    transition: background 0.3s ease;
}

.cart-item:hover {
    background: #f7fafc;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    object-position: center;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    margin-right: 1.5rem;
}

.cart-item-info {
    flex: 1;
}

.cart-item-name {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.cart-item-price {
    color: #2d3748;
    font-weight: 600;
    font-size: 1.1rem;
}

.qty-control {
    display: flex;
    align-items: center;
    gap: 0;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.qty-control:hover {
    border-color: #2d3748;
    box-shadow: 0 4px 12px rgba(45, 55, 72, 0.15);
}

.qty-btn {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border: none;
    width: 40px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: #2d3748;
    font-size: 1rem;
    position: relative;
    overflow: hidden;
}

.qty-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(45, 55, 72, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.qty-btn:hover::before {
    width: 100px;
    height: 100px;
}

.qty-btn:hover {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(45, 55, 72, 0.4);
}

.qty-btn:active {
    transform: translateY(0) scale(0.95);
    box-shadow: 0 2px 6px rgba(45, 55, 72, 0.3);
}

.qty-btn i {
    position: relative;
    z-index: 1;
    transition: transform 0.3s ease;
}

.qty-btn:hover i {
    transform: scale(1.2) rotate(90deg);
}

.qty-minus {
    border-right: 1px solid #e2e8f0;
}

.qty-minus:hover i {
    transform: scale(1.2) rotate(-90deg);
}

.qty-plus {
    border-left: 1px solid #e2e8f0;
}

.cart-item-qty {
    width: 70px;
    border: none;
    padding: 0.5rem;
    text-align: center;
    font-weight: bold;
    background: white;
    font-size: 1.2rem;
    color: #2d3748;
    transition: all 0.3s ease;
}

.cart-item-qty:focus {
    outline: none;
}

/* Animation khi số lượng thay đổi */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.qty-changed {
    animation: pulse 0.3s ease;
}

/* Ripple effect */
@keyframes ripple {
    0% {
        transform: translate(-50%, -50%) scale(0);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(4);
        opacity: 0;
    }
}

.qty-btn.clicked::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    animation: ripple 0.6s ease-out;
}

/* Glow effect khi hover */
@keyframes glow {
    0%, 100% {
        box-shadow: 0 0 5px rgba(45, 55, 72, 0.5);
    }
    50% {
        box-shadow: 0 0 20px rgba(45, 55, 72, 0.8);
    }
}

.qty-control:hover {
    animation: glow 2s infinite;
}

.cart-item-subtotal {
    font-size: 1.3rem;
    font-weight: bold;
    color: #e53e3e;
    min-width: 120px;
    text-align: right;
}

.cart-summary {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    padding: 2rem;
    border-radius: 15px;
    margin-top: 2rem;
}

.cart-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1.8rem;
    font-weight: bold;
    color: #2d3748;
    margin-bottom: 1.5rem;
}

.cart-total-amount {
    color: #e53e3e;
}

.btn-update {
    background: #718096;
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-update:hover {
    background: #4a5568;
    transform: translateY(-2px);
}

.btn-checkout {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    color: white;
    border: none;
    padding: 1rem 3rem;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-checkout:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(45, 55, 72, 0.6);
    color: white;
}

.btn-remove {
    background: #fc8181;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-remove:hover {
    background: #e53e3e;
    transform: scale(1.05);
}

.empty-cart {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-cart i {
    font-size: 5rem;
    color: #cbd5e0;
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .cart-item {
        flex-direction: column;
        text-align: center;
    }
    
    .cart-item-image {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .cart-item-subtotal {
        text-align: center;
        margin-top: 1rem;
    }
}
</style>

<div class="cart-header text-center position-relative">
    <img src="images/baner.png" alt="Giỏ hàng" class="cart-banner-image">
    <div class="cart-banner-overlay">
        <h2 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Giỏ hàng của bạn</h2>
    </div>
</div>

<?php if(empty($items)): ?>
    <div class="cart-container">
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h4 class="mb-3">Giỏ hàng trống</h4>
            <p class="text-muted mb-4">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
            <a href="products.php" class="btn btn-checkout">
                <i class="fas fa-utensils me-2"></i>
                Khám phá thực đơn
            </a>
        </div>
    </div>
<?php else: ?>
<form method="post">
    <div class="cart-container">
        <?php foreach($items as $it): ?>
        <div class="cart-item">
            <?php if($it['product']['image']): ?>
                <img src="<?=htmlspecialchars($it['product']['image'])?>" 
                     class="cart-item-image" 
                     alt="<?=htmlspecialchars($it['product']['name'])?>">
            <?php else: ?>
                <div class="cart-item-image d-flex align-items-center justify-content-center bg-light">
                    <i class="fas fa-image fa-2x text-muted"></i>
                </div>
            <?php endif; ?>
            
            <div class="cart-item-info">
                <div class="cart-item-name"><?=htmlspecialchars($it['product']['name'])?></div>
                <div class="cart-item-price"><?=pretty_money($it['product']['price'])?> / món</div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div>
                    <label class="text-muted small d-block mb-1 text-center">Số lượng</label>
                    <div class="qty-control">
                        <button type="button" class="qty-btn qty-minus" data-product-id="<?=$it['product']['id']?>">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" 
                               name="qty[<?=$it['product']['id']?>]" 
                               value="<?=$it['qty']?>" 
                               min="1" 
                               class="cart-item-qty"
                               readonly>
                        <button type="button" class="qty-btn qty-plus" data-product-id="<?=$it['product']['id']?>">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <div>
                    <label class="text-muted small d-block mb-1">Thành tiền</label>
                    <div class="cart-item-subtotal"><?=pretty_money($it['sub'])?></div>
                </div>
                
                <button type="submit" 
                        name="remove" 
                        class="btn-remove" 
                        onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                    <input type="hidden" name="product_id" value="<?=$it['product']['id']?>">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="cart-summary">
        <div class="cart-total">
            <span>Tổng cộng:</span>
            <span class="cart-total-amount"><?=pretty_money($total)?></span>
        </div>
        
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex gap-2">
                <button type="submit" name="update" class="btn btn-update">
                    <i class="fas fa-sync-alt me-2"></i>
                    Cập nhật giỏ hàng
                </button>
                <a href="products.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Tiếp tục mua
                </a>
            </div>
            
            <a href="checkout.php" class="btn btn-checkout">
                <i class="fas fa-credit-card me-2"></i>
                Thanh toán ngay
            </a>
        </div>
    </div>
</form>
<?php endif; ?>

<script>
// Tự động tính lại tổng tiền khi thay đổi số lượng
document.addEventListener('DOMContentLoaded', function() {
    const qtyInputs = document.querySelectorAll('.cart-item-qty');
    
    // Xử lý nút tăng/giảm
    document.querySelectorAll('.qty-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            // Thêm ripple effect
            this.classList.add('clicked');
            setTimeout(() => this.classList.remove('clicked'), 600);
            
            const productId = this.dataset.productId;
            const input = document.querySelector(`input[name="qty[${productId}]"]`);
            let qty = parseInt(input.value) || 1;
            
            if (qty > 1) {
                qty--;
                input.value = qty;
                
                // Animation cho số
                input.classList.add('qty-changed');
                setTimeout(() => input.classList.remove('qty-changed'), 300);
                
                updateItemTotal(input);
            } else {
                // Xác nhận xóa khi giảm xuống 0
                if (confirm('Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                    const cartItem = input.closest('.cart-item');
                    cartItem.querySelector('.btn-remove').click();
                }
            }
        });
    });
    
    document.querySelectorAll('.qty-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            // Thêm ripple effect
            this.classList.add('clicked');
            setTimeout(() => this.classList.remove('clicked'), 600);
            
            const productId = this.dataset.productId;
            const input = document.querySelector(`input[name="qty[${productId}]"]`);
            let qty = parseInt(input.value) || 1;
            
            if (qty < 99) {
                qty++;
                input.value = qty;
                
                // Animation cho số
                input.classList.add('qty-changed');
                setTimeout(() => input.classList.remove('qty-changed'), 300);
                
                updateItemTotal(input);
            } else {
                // Thông báo đã đạt giới hạn
                this.style.background = '#fc8181';
                setTimeout(() => {
                    this.style.background = '';
                }, 200);
            }
        });
    });
    
    // Cập nhật tổng tiền của từng item
    function updateItemTotal(input) {
        const cartItem = input.closest('.cart-item');
        const qty = parseInt(input.value) || 0;
        
        // Lấy giá từ text
        const priceText = cartItem.querySelector('.cart-item-price').textContent;
        const price = parseInt(priceText.replace(/[^\d]/g, ''));
        
        // Tính thành tiền mới
        const subtotal = price * qty;
        const subtotalElement = cartItem.querySelector('.cart-item-subtotal');
        subtotalElement.textContent = new Intl.NumberFormat('vi-VN').format(subtotal) + '₫';
        
        // Tính tổng cộng
        updateTotal();
        
        // Hiệu ứng animation
        cartItem.style.background = '#f0fff4';
        setTimeout(() => {
            cartItem.style.background = '';
        }, 300);
    }
    
    // Tính tổng cộng
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.cart-item-subtotal').forEach(element => {
            const amount = parseInt(element.textContent.replace(/[^\d]/g, ''));
            if (!isNaN(amount)) {
                total += amount;
            }
        });
        
        const totalElement = document.querySelector('.cart-total-amount');
        if (totalElement) {
            totalElement.textContent = new Intl.NumberFormat('vi-VN').format(total) + '₫';
        }
    }
    
    // Thêm data-price vào mỗi item để dễ tính toán
    document.querySelectorAll('.cart-item').forEach(item => {
        const priceText = item.querySelector('.cart-item-price').textContent;
        const price = parseInt(priceText.replace(/[^\d]/g, ''));
        item.dataset.price = price;
    });
});
</script>

<?php include 'footer.php'; ?>
