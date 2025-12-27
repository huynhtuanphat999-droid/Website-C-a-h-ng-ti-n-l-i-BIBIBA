<?php
require_once 'config.php';
require_once 'functions.php';

// lấy featured (nổi bật)
$stmt = $pdo->query("SELECT * FROM products WHERE featured=1 ORDER BY created_at DESC LIMIT 6");
$featured = $stmt->fetchAll();

// top sellers
$stmt2 = $pdo->query("SELECT * FROM products ORDER BY sales_count DESC LIMIT 6");
$bestsellers = $stmt2->fetchAll();
?>
<?php include 'header.php'; ?>

<style>
/* Background cho trang chủ - giống trang thực đơn */
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

/* Hiệu ứng cho card */
.product-card {
  transition: .3s;
  border: none;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(10px);
}
.product-card:hover {
  transform: translateY(-7px);
  box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}

/* Hiệu ứng hình */
.product-card img {
  width: 100%;
  height: 220px;
  object-fit: cover;
  object-position: center;
  transition: transform .4s;
}
.product-card:hover img {
  transform: scale(1.08);
}

.product-card .card-img-top {
  width: 100%;
  height: 220px;
  object-fit: cover;
  object-position: center;
}

/* Badge nổi bật */
.badge-featured {
  background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(255, 102, 0, 0.4);
  display: inline-block;
  width: fit-content;
}

/* Comic Button Style */
.comic-button {
  display: inline-block;
  padding: 10px 20px;
  font-size: 16px;
  font-weight: bold;
  text-align: center;
  text-decoration: none;
  color: #fff;
  background-color: #2d3748;
  border: 3px solid #000;
  border-radius: 10px;
  box-shadow: 4px 4px 0px #000;
  transition: all 0.3s ease;
  cursor: pointer;
}

.comic-button:hover {
  background-color: #ff6600;
  color: #fff;
  border: 3px solid #ff6600;
  box-shadow: 4px 4px 0px #ff6600;
  transform: translateY(-2px);
}

.comic-button:active {
  background-color: #1a202c;
  color: #fff;
  box-shadow: none;
  transform: translateY(4px);
}

/* Fade in */
.fade-in {
  animation: fadeInUp .6s ease forwards;
  opacity: 0;
}
@keyframes fadeInUp {
  from {opacity:0; transform:translateY(20px);}
  to {opacity:1; transform:translateY(0);}
}

/* Banner header - Full width và to hơn */
.home-banner {
    margin: -1rem 0 2rem;
    margin-left: calc(-50vw + 50%);
    margin-right: calc(-50vw + 50%);
    width: 100vw;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    position: relative;
    height: 50vh;
    min-height: 400px;
}

.banner-slideshow {
    position: relative;
    width: 100%;
    height: 100%;
}

.banner-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 1s ease-in-out;
}

.banner-slide.active {
    opacity: 1;
}

.banner-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}

/* Overlay tối để text dễ đọc hơn */
.banner-slide::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    z-index: 1;
}

/* Nút điều khiển slideshow */
.banner-controls {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 10;
}

.banner-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid white;
}

.banner-dot.active {
    background: white;
    transform: scale(1.3);
}

.banner-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.5rem;
    transition: all 0.3s ease;
    z-index: 10;
}

.banner-arrow:hover {
    background: rgba(255, 102, 0, 0.8);
    transform: translateY(-50%) scale(1.1);
}

.banner-arrow.prev {
    left: 20px;
}

.banner-arrow.next {
    right: 20px;
}

@media (max-width: 768px) {
    .home-banner {
        height: 40vh;
        min-height: 300px;
    }
    
    .banner-arrow {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
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
    z-index: 5;
}

.banner-title {
    margin-bottom: 2rem;
}

.banner-icon {
    font-size: 4rem;
    display: block;
    margin-bottom: 1rem;
    animation: bounceIn 1s ease, rotate 3s ease-in-out infinite;
    filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.8));
}

.text-line-1 {
    display: block;
    font-size: 2rem;
    font-weight: 600;
    letter-spacing: 3px;
    text-transform: uppercase;
    animation: slideInLeft 1s ease;
    text-shadow: 3px 3px 10px rgba(0,0,0,0.8);
    margin-bottom: 0.5rem;
}

.text-line-2 {
    display: block;
    font-size: 5rem;
    font-weight: 900;
    letter-spacing: 5px;
    text-transform: uppercase;
    background: linear-gradient(45deg, #fff, #ffd700, #fff);
    background-size: 200% 200%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: slideInRight 1s ease, gradientShift 3s ease infinite;
    filter: drop-shadow(3px 3px 10px rgba(0,0,0,0.8));
}

.banner-subtitle {
    font-size: 1.5rem;
    opacity: 0;
    animation: fadeInUp 1s ease 0.5s forwards;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.8);
    margin-bottom: 2rem;
}

.banner-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    opacity: 0;
    animation: fadeInUp 1s ease 1s forwards;
}

.btn-banner {
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.btn-primary-banner {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    color: white;
    border: 3px solid #fff;
}

.btn-primary-banner:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    color: white;
}

.btn-secondary-banner {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 3px solid #fff;
    backdrop-filter: blur(10px);
}

.btn-secondary-banner:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 10px 25px rgba(255,255,255,0.3);
    color: white;
}

/* Animations */
@keyframes bounceIn {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes rotate {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-10deg); }
    75% { transform: rotate(10deg); }
}

@keyframes slideInLeft {
    from {
        transform: translateX(-100px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideInRight {
    from {
        transform: translateX(100px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeInUp {
    from {
        transform: translateY(30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

@media (max-width: 768px) {
    .banner-icon {
        font-size: 2.5rem;
    }
    .text-line-1 {
        font-size: 1.2rem;
    }
    .text-line-2 {
        font-size: 2.5rem;
    }
    .banner-subtitle {
        font-size: 1rem;
    }
    .banner-buttons {
        flex-direction: column;
        padding: 0 1rem;
    }
    .btn-banner {
        width: 100%;
        justify-content: center;
    }
}

/* Tiêu đề section nổi bật */
.section-title {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 15px;
    margin: 2rem 0 1.5rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
    text-align: center;
    font-size: 2rem;
    font-weight: 800;
    position: relative;
    overflow: hidden;
    animation: pulse 2s infinite;
}

.section-title::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

.section-title i {
    font-size: 2.5rem;
    margin-right: 1rem;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

@media (max-width: 768px) {
    .section-title {
        font-size: 1.5rem;
        padding: 1rem 1.5rem;
    }
    .section-title i {
        font-size: 1.8rem;
    }
}
</style>

<!-- Banner slideshow lớn -->
<div class="home-banner">
    <div class="banner-slideshow">
        <!-- Slide 1 -->
        <div class="banner-slide active">
            <img src="images/baner.png" alt="Slide 1" class="banner-image">
        </div>
        <!-- Slide 2 -->
        <div class="banner-slide">
            <img src="images/t1.jpg" alt="Slide 2" class="banner-image">
        </div>
        <!-- Slide 3 -->
        <div class="banner-slide">
            <img src="images/t2.jpg" alt="Slide 3" class="banner-image">
        </div>
        <!-- Slide 4 -->
        <div class="banner-slide">
            <img src="images/t3.jpg" alt="Slide 4" class="banner-image">
        </div>
        <!-- Slide 5 -->
        <div class="banner-slide">
            <img src="images/t4.jpg" alt="Slide 5" class="banner-image">
        </div>
    </div>
    
    <!-- Text overlay với hiệu ứng đẹp -->
    <div class="banner-text-overlay">
        <h1 class="banner-title">
            <i class="fas fa-utensils banner-icon"></i>
            <span class="text-line-1">Chào mừng đến với</span>
            <span class="text-line-2">BIBIBABA</span>
        </h1>
        <p class="banner-subtitle">Khám phá hương vị tuyệt vời từ những món ăn đặc sắc</p>
        <div class="banner-buttons">
            <a href="products.php" class="btn-banner btn-primary-banner">
                <i class="fas fa-utensils me-2"></i>Xem thực đơn
            </a>
        </div>
    </div>
    
    <!-- Nút điều khiển -->
    <button class="banner-arrow prev" onclick="changeSlide(-1)">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="banner-arrow next" onclick="changeSlide(1)">
        <i class="fas fa-chevron-right"></i>
    </button>
    
    <!-- Dots -->
    <div class="banner-controls">
        <span class="banner-dot active" onclick="goToSlide(0)"></span>
        <span class="banner-dot" onclick="goToSlide(1)"></span>
        <span class="banner-dot" onclick="goToSlide(2)"></span>
        <span class="banner-dot" onclick="goToSlide(3)"></span>
        <span class="banner-dot" onclick="goToSlide(4)"></span>
    </div>
</div>

<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.banner-slide');
const dots = document.querySelectorAll('.banner-dot');
let slideInterval;

function showSlide(index) {
    // Đảm bảo index trong phạm vi
    if (index >= slides.length) currentSlide = 0;
    else if (index < 0) currentSlide = slides.length - 1;
    else currentSlide = index;
    
    // Ẩn tất cả slides
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    // Hiện slide hiện tại
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

function changeSlide(direction) {
    showSlide(currentSlide + direction);
    resetInterval();
}

function goToSlide(index) {
    showSlide(index);
    resetInterval();
}

function resetInterval() {
    clearInterval(slideInterval);
    slideInterval = setInterval(() => {
        showSlide(currentSlide + 1);
    }, 5000);
}

// Tự động chuyển slide mỗi 5 giây
slideInterval = setInterval(() => {
    showSlide(currentSlide + 1);
}, 5000);

// Dừng tự động khi hover
document.querySelector('.home-banner').addEventListener('mouseenter', () => {
    clearInterval(slideInterval);
});

document.querySelector('.home-banner').addEventListener('mouseleave', () => {
    resetInterval();
});
</script>

<div class="section-title">
    <i class="fas fa-star"></i>
    Sản phẩm nổi bật
</div>
<div class="row">
  <?php $delay = 0; foreach($featured as $p): ?>
    <div class="col-md-4 mb-4 fade-in" style="animation-delay: <?=$delay?>s">
      <div class="card product-card h-100">
        <?php if($p['image']): ?>
          <img loading="lazy" src="<?=htmlspecialchars($p['image'])?>" class="card-img-top" alt="">
        <?php endif; ?>
        <div class="card-body d-flex flex-column">
          <span class="badge badge-featured mb-2">⭐ Nổi bật</span>
          <h5 class="card-title"><?=htmlspecialchars($p['name'])?></h5>
          <p class="card-text text-muted small"><?=htmlspecialchars($p['description'])?></p>
          <div class="mt-auto d-flex justify-content-between align-items-center">
            <strong class="text-success fs-5"><?=pretty_money($p['price'])?></strong>
            <a href="product.php?slug=<?=urlencode($p['slug'])?>" class="comic-button btn-sm">Xem ngay</a>
          </div>
        </div>
      </div>
    </div>
  <?php $delay+=0.1; endforeach; ?>
</div>

<div class="section-title">
    <i class="fas fa-fire"></i>
    Sản phẩm bán chạy
</div>
<div class="row g-4">
    <?php 
    $delay = 0; 
    $count = 0; // đếm số sản phẩm hiển thị
    foreach($bestsellers as $p): 
        if($count >= 6) break; // chỉ hiển thị 6 sản phẩm
    ?>
    <div class="col-md-4 col-sm-6 mb-4"> <!-- 3 cột trên desktop, 2 cột tablet -->
        <div class="card product-card h-100 fade-in" style="animation-delay: <?=$delay?>s;">
            <?php if(!empty($p['image'])): ?>
                <img loading="lazy" src="<?=htmlspecialchars($p['image'])?>" class="card-img-top" alt="<?=htmlspecialchars($p['name'])?>">
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
                <span class="badge bg-warning text-dark mb-2">Best Seller</span>
                <h6 class="fw-bold"><?=htmlspecialchars($p['name'])?></h6>
                <p class="mb-2 text-success fw-bold fs-5"><?=pretty_money($p['price'])?></p>
                <a href="product.php?slug=<?=urlencode($p['slug'])?>" class="comic-button btn-sm mt-auto">Xem chi tiết</a>
            </div>
        </div>
    </div>
    <?php 
        $delay += 0.1; 
        $count++;
    endforeach; 
    ?>
</div>
<div class="section-title" style="font-size: 1.8rem;">
    <i class="fas fa-filter"></i>
    Lọc theo loại
</div>
<div class="mb-4 filter-btns d-flex flex-wrap gap-3">
    <button class="comic-button filter-active" onclick="filterCategory('', this)">Tất cả</button>
    <button class="comic-button" onclick="filterCategory('food', this)">🍔 Đồ ăn</button>
    <button class="comic-button" onclick="filterCategory('drink', this)">☕ Đồ uống</button>
    <button class="comic-button" onclick="filterCategory('dessert', this)">🍰 Tráng miệng</button>
</div>

<style>
.comic-button.filter-active {
    background-color: #fcf414;
    color: #000;
    border-color: #000;
    box-shadow: 4px 4px 0px #000;
}
</style>

<div id="filterResults" class="row g-4 fade-in"></div>

<script>
function filterCategory(cat, btn){
    // Update active button
    document.querySelectorAll('.filter-btns .comic-button').forEach(b => {
        b.classList.remove('filter-active');
    });
    if(btn) {
        btn.classList.add('filter-active');
    }
    
    const target = document.getElementById('filterResults');
    target.innerHTML = `<div class="col-12 text-center p-3">⏳ Đang tải...</div>`;
    fetch('products.php?ajax=1&cat=' + encodeURIComponent(cat))
        .then(r => r.text())
        .then(html => {
            target.innerHTML = html;
            target.classList.add('fade-in');
        });
}
// Load mặc định
filterCategory('', document.querySelector('.filter-active'));
</script>
