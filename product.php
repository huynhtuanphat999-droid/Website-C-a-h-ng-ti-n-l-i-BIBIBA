<?php
require_once 'config.php';
require_once 'functions.php';
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM products WHERE slug = :s LIMIT 1");
$stmt->execute([':s'=>$slug]);
$p = $stmt->fetch();
if(!$p){ header('Location: products.php'); exit; }

include 'header.php';
?>

<style>
.product-detail-container {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.product-image-wrapper {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    background: #f8f9fa;
    padding: 1rem;
}

.product-detail-image {
    width: 100%;
    height: 450px;
    object-fit: cover;
    object-position: center;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.product-info {
    padding: 1rem;
}

.product-category-badge {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    display: inline-block;
    margin-bottom: 1rem;
    font-weight: 600;
}

.product-title {
    font-size: 2rem;
    font-weight: bold;
    color: #2d3748;
    margin-bottom: 1rem;
}

.product-description {
    color: #4a5568;
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 1.5rem;
}

.product-price {
    font-size: 2.5rem;
    font-weight: bold;
    color: #e53e3e;
    margin-bottom: 2rem;
}

.product-price small {
    font-size: 1rem;
    color: #718096;
    text-decoration: line-through;
    margin-left: 1rem;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.quantity-selector label {
    font-weight: 600;
    color: #4a5568;
}

.quantity-selector input {
    width: 100px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.75rem;
    text-align: center;
    font-weight: bold;
}

.btn-add-cart {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    color: white;
    border: none;
    padding: 1rem 3rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.4);
}

.btn-add-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(45, 55, 72, 0.6);
    color: white;
}

.product-meta {
    background: #f7fafc;
    padding: 1.5rem;
    border-radius: 12px;
    margin-top: 2rem;
}

.product-meta-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.product-meta-item:last-child {
    border-bottom: none;
}

.product-meta-item i {
    color: #2d3748;
    font-size: 1.2rem;
}

@media (max-width: 768px) {
    .product-detail-image {
        height: 300px;
    }
    
    .product-title {
        font-size: 1.5rem;
    }
    
    .product-price {
        font-size: 2rem;
    }
}
</style>

<div class="product-detail-container">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="product-image-wrapper">
                <?php if($p['image']): ?>
                    <img src="<?=htmlspecialchars($p['image'])?>" class="product-detail-image" alt="<?=htmlspecialchars($p['name'])?>">
                <?php else: ?>
                    <div class="product-detail-image d-flex align-items-center justify-content-center bg-light">
                        <i class="fas fa-image fa-5x text-muted"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="product-info">
                <span class="product-category-badge">
                    <i class="fas fa-tag me-2"></i>
                    <?php
                    $categories = [
                        'food' => 'Đồ ăn',
                        'drink' => 'Đồ uống',
                        'dessert' => 'Tráng miệng'
                    ];
                    echo $categories[$p['category']] ?? $p['category'];
                    ?>
                </span>
                
                <h1 class="product-title"><?=htmlspecialchars($p['name'])?></h1>
                
                <p class="product-description">
                    <?=htmlspecialchars($p['description'] ?: 'Sản phẩm chất lượng cao, được chế biến từ nguyên liệu tươi ngon, đảm bảo vệ sinh an toàn thực phẩm.')?>
                </p>
                
                <div class="product-price">
                    <?=pretty_money($p['price'])?>
                </div>
                
                <form method="post" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?=$p['id']?>">
                    
                    <div class="quantity-selector">
                        <label for="qty">
                            <i class="fas fa-shopping-basket me-2"></i>
                            Số lượng:
                        </label>
                        <input type="number" id="qty" name="qty" value="1" min="1" max="99">
                    </div>
                    
                    <button type="submit" class="btn btn-add-cart w-100">
                        <i class="fas fa-cart-plus me-2"></i>
                        Thêm vào giỏ hàng
                    </button>
                </form>
                
                <div class="product-meta">
                    <div class="product-meta-item">
                        <i class="fas fa-fire"></i>
                        <div>
                            <strong>Đã bán:</strong>
                            <span class="text-muted"><?=$p['sales_count']?> sản phẩm</span>
                        </div>
                    </div>
                    <div class="product-meta-item">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>Bảo đảm:</strong>
                            <span class="text-muted">Chất lượng 100%</span>
                        </div>
                    </div>
                    <div class="product-meta-item">
                        <i class="fas fa-truck"></i>
                        <div>
                            <strong>Giao hàng:</strong>
                            <span class="text-muted">Nhanh chóng trong 30-60 phút</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="text-center mb-4">
    <a href="products.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>
        Quay lại thực đơn
    </a>
</div>

<?php include 'footer.php'; ?>
