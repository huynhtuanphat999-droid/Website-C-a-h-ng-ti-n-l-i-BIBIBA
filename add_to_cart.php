<?php
require_once 'config.php';
require_once 'cart_functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect=products.php&msg=Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng');
    exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $pid = (int)($_POST['product_id'] ?? 0);
  $qty = max(1, (int)($_POST['qty'] ?? 1));
  // kiểm tra tồn tại
  $stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id LIMIT 1");
  $stmt->execute([':id'=>$pid]);
  if($stmt->fetch()){
    cart_add($pid, $qty);
    header('Location: cart.php?added=1');
    exit;
  }
}
header('Location: products.php');
