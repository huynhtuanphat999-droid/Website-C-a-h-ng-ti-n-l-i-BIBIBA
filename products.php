<?php
require_once 'config.php';
require_once 'functions.php';

// Xử lý AJAX request
if(isset($_GET['ajax'])) {
    $cat = $_GET['cat'] ?? '';
    $search = $_GET['q'] ?? '';

    // Lấy sản phẩm theo category và search
    if($cat && $search){
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? AND name LIKE ? ORDER BY created_at DESC");
        $stmt->execute([$cat, "%$search%"]);
    } elseif($cat){
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
        $stmt->execute([$cat]);
    } elseif($search){
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY created_at DESC");
        $stmt->execute(["%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    }
    $products = $stmt->fetchAll();

    if(empty($products)){
        echo '<div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Không tìm thấy sản phẩm nào</h5>
              </div>';
        exit;
    }

    $delay = 0;
    foreach($products as $p):
?>
<div class="col-md-4 col-sm-6 mb-4">
    <div class="card product-card h-100 fade-in" style="animation-delay: <?=$delay?>s;">
        <?php if(!empty($p['image'])): ?>
            <img loading="lazy" src="<?=htmlspecialchars($p['image'])?>" class="card-img-top" alt="<?=htmlspecialchars($p['name'])?>">
        <?php endif; ?>
        <div class="card-body d-flex flex-column">
            <span class="badge bg-warning text-dark mb-2" style="background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%) !important; color: white !important; font-weight: 600;"><?=htmlspecialchars($p['category'])?></span>
            <h6 class="fw-bold"><?=htmlspecialchars($p['name'])?></h6>
            <p class="text-muted small mb-2"><?=htmlspecialchars(substr($p['description'] ?? '', 0, 60))?>...</p>
            <p class="mb-2 text-success fw-bold fs-5"><?=pretty_money($p['price'])?></p>
            <div class="d-flex gap-2 mt-auto">
                <a href="product.php?slug=<?=urlencode($p['slug'])?>" class="comic-button-small flex-grow-1 text-center">
                    <i class="fas fa-eye"></i> Chi tiết
                </a>
                <form method="post" action="add_to_cart.php" class="flex-grow-1">
                    <input type="hidden" name="product_id" value="<?=$p['id']?>">
                    <button type="submit" class="comic-button-small w-100">
                        <i class="fas fa-cart-plus"></i> Thêm
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
    $delay += 0.1;
    endforeach;
    exit;
}

// Trang thực đơn đầy đủ
$cat = $_GET['cat'] ?? '';
$search = $_GET['q'] ?? '';

include 'header.php';
?>

<style>
/* Background cho toàn bộ trang thực đơn - Màu nâu sữa */
body {
    background: linear-gradient(135deg, #d7ccc8 0%, #bcaaa4 50%, #d7ccc8 100%);
    background-attachment: fixed;
    min-height: 100vh;
}

/* Hoặc dùng hình ảnh background */
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

.products-header {
    margin: -1rem -15px 2rem;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.header-banner-image {
    width: 100%;
    height: auto;
    max-height: 200px;
    object-fit: cover;
    object-position: center;
    display: block;
    border-radius: 15px;
}

.banner-text-overlay {
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

.banner-text-overlay h1 {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: white;
}

.banner-text-overlay p {
    font-size: 1.1rem;
    opacity: 0.95;
}

@media (max-width: 768px) {
    .banner-text-overlay h1 {
        font-size: 1.8rem;
    }
    .banner-text-overlay p {
        font-size: 0.9rem;
    }
}

.filter-section {
    background: #212529;
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.3);
    margin-bottom: 2rem;
    color: white;
}

.filter-section h6 {
    color: rgba(255, 255, 255, 0.85);
    font-weight: 500;
}

.filter-section h6 i {
    color: #ff6600;
}

.filter-btn {
    border-radius: 25px;
    padding: 0.5rem 1.5rem;
    margin: 0.25rem;
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.3);
    background: transparent;
    color: rgba(255, 255, 255, 0.85);
}

.filter-btn:hover {
    transform: translateY(-2px);
    color: #ff6600;
    border-color: #ff6600;
    background: rgba(255, 102, 0, 0.1);
}

.filter-btn.active {
    background: #ff6600;
    color: white;
    border-color: #ff6600;
}

.filter-btn.active:hover {
    background: #ff8533;
    border-color: #ff8533;
}

.product-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    height: 100%;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
}

.product-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.product-card img {
    height: 220px;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.product-card:hover img {
    transform: scale(1.1);
}

.fade-in {
    animation: fadeInUp 0.6s ease forwards;
    opacity: 0;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.search-box {
    border-radius: 25px;
    border: 2px solid #ffffff;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
    background: white;
    color: #1a1a1a;
}

.search-box:focus {
    border-color: #ffffff;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
    outline: none;
}

.search-box::placeholder {
    color: #999;
}

.loading-spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #2d3748;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Comic Button Style - Đặc sắc hơn */
.comic-button-small {
    display: inline-block;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
    color: #fff;
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    border: none;
    border-radius: 25px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.3);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.comic-button-small::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.comic-button-small:hover::before {
    width: 300px;
    height: 300px;
}

.comic-button-small:hover {
    background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(255, 102, 0, 0.5);
}

.comic-button-small:active {
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(255, 102, 0, 0.4);
}

.comic-button-small i {
    margin-right: 5px;
    transition: transform 0.3s ease;
}

.comic-button-small:hover i {
    transform: scale(1.2);
}
</style>

<div class="products-header text-center position-relative">
    <img src="images/baner.png" alt="Thực Đơn" class="header-banner-image">
    <div class="banner-text-overlay">
        <h1 class="mb-2"><i class="fas fa-utensils me-2"></i>Thực Đơn</h1>
        <p class="mb-0">Khám phá các món ăn, đồ uống và tráng miệng tuyệt vời</p>
    </div>
</div>

<div class="filter-section">
    <div class="row align-items-center">
        <div class="col-md-6 mb-3 mb-md-0">
            <h6 class="mb-2"><i class="fas fa-filter me-2"></i>Lọc theo loại:</h6>
            <div class="d-flex flex-wrap">
                <button class="btn filter-btn active" onclick="filterCategory('', this)">
                    <i class="fas fa-th"></i> Tất cả
                </button>
                <button class="btn filter-btn" onclick="filterCategory('food', this)">
                    <i class="fas fa-hamburger"></i> Đồ ăn
                </button>
                <button class="btn filter-btn" onclick="filterCategory('drink', this)">
                    <i class="fas fa-coffee"></i> Đồ uống
                </button>
                <button class="btn filter-btn" onclick="filterCategory('dessert', this)">
                    <i class="fas fa-ice-cream"></i> Tráng miệng
                </button>
            </div>
        </div>
        <div class="col-md-6">
            <h6 class="mb-2"><i class="fas fa-search me-2"></i>Tìm kiếm:</h6>
            <input type="text" id="searchBox" class="form-control search-box" 
                   placeholder="Tìm món ăn, đồ uống..." 
                   value="<?=htmlspecialchars($search)?>">
        </div>
    </div>
</div>

<div id="filterResults" class="row g-4"></div>

<script>
let currentCategory = '';
let searchTimeout;

function filterCategory(cat, btn) {
    currentCategory = cat;
    
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    if(btn) btn.classList.add('active');
    
    loadProducts();
}

function loadProducts() {
    const target = document.getElementById('filterResults');
    const search = document.getElementById('searchBox').value;
    
    target.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="loading-spinner"></div>
            <p class="mt-3 text-muted">Đang tải sản phẩm...</p>
        </div>`;
    
    let url = 'products.php?ajax=1&cat=' + encodeURIComponent(currentCategory);
    if(search) {
        url += '&q=' + encodeURIComponent(search);
    }
    
    fetch(url)
        .then(r => r.text())
        .then(html => {
            target.innerHTML = html;
        })
        .catch(err => {
            target.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Có lỗi xảy ra</h5>
                    <button class="btn btn-primary mt-3" onclick="loadProducts()">Thử lại</button>
                </div>`;
        });
}

// Search with debounce
document.getElementById('searchBox').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadProducts();
    }, 500);
});

// Load initial products
filterCategory('<?=$cat?>');
</script>

<?php include 'footer.php'; ?>

