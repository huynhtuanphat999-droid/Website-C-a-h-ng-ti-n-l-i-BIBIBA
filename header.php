<?php
// header.php
require_once __DIR__.'/functions.php';
$user = current_user();
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>HoangPhat food</title>

<!-- Font đẹp -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<link href="css/style.css" rel="stylesheet">
<style>
/* --- Giữ tất cả trong navbar nằm ngang --- */
.custom-nav .navbar-collapse {
  display: flex !important;
  align-items: center;
  justify-content: space-between;
  flex-wrap: nowrap !important;
}

/* Menu bên trái (Trang chủ, Thực đơn, Tin tức, ...) */
.custom-nav .navbar-nav.me-auto {
  display: flex;
  flex-direction: row;
  align-items: center;
  flex-wrap: nowrap;
  gap: 10px;
}

/* Form tìm kiếm nằm ngang gọn */
.custom-nav form.d-flex {
  flex-wrap: nowrap;
  align-items: center;
  white-space: nowrap;
}

/* Đảm bảo input và select không chiếm nhiều chỗ */
.custom-nav input.form-control,
.custom-nav select.form-select {
  width: auto;
}

/* Tài khoản (đăng ký / đăng nhập) nằm ngang */
.custom-nav .navbar-nav:last-child {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8px;
}

/* Ngăn chữ tự động xuống hàng */
.custom-nav * {
  white-space: nowrap;
}

/* Khi màn hình nhỏ thì cho phép xuống hàng */
@media (max-width: 995px) {
  .custom-nav .navbar-collapse {
    flex-wrap: wrap !important;
  }
}
</style>
</head>

<body>
    <script>
window.addEventListener("scroll", function() {
  let nav = document.querySelector(".custom-nav");
  nav.classList.toggle("scrolled", window.scrollY > 50);
});
</script>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top custom-nav">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
      <i class="fa-solid fa-burger"></i> BIBIBABA
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="navMenu" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="fa-solid fa-house"></i> Trang chủ</a></li>
<li class="nav-item"><a class="nav-link active" href="products.php"><i class="fa-solid fa-utensils"></i> Thực đơn</a></li>
<li class="nav-item"><a class="nav-link" href="news.php"><i class="fa-regular fa-newspaper"></i> Tin tức</a></li>
<li class="nav-item"><a class="nav-link" href="checkout.php"><i class="fa-solid fa-credit-card"></i> Thanh toán</a></li>
<li class="nav-item"><a class="nav-link" href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Giỏ hàng (<?=array_sum($_SESSION['cart'] ?? [])?>)</a></li>
<li class="nav-item"><a class="nav-link" href="contact.php"><i class="fa-solid fa-phone"></i> Liên hệ</a></li>

      </ul>

      <form class="d-flex me-3" method="get" action="products.php">
        <input class="form-control form-control-sm me-2" name="q" placeholder="Tìm món...">
        <select class="form-select form-select-sm me-2" name="cat">
          <option value="">Tất cả</option>
          <option value="food">Đồ ăn</option>
          <option value="drink">Đồ uống</option>
          <option value="dessert">Tráng miệng</option>
        </select>
        <button class="btn btn-sm btn-outline-light">Tìm</button>
      </form>

      <ul class="navbar-nav">
        <?php if($user): ?>
          <li class="nav-item"><a class="nav-link" href="#"><?=htmlspecialchars($user['username'])?></a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Đăng xuất</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="register.php">Đăng ký</a></li>
          <li class="nav-item"><a class="nav-link" href="login.php">Đăng nhập</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
