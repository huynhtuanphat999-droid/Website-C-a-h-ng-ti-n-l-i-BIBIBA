<?php
// cart_functions.php
// config.php must be loaded first (session started)
function cart_get() {
    return $_SESSION['cart'] ?? [];
}
function cart_add($pid, $qty=1) {
    $cart = cart_get();
    $pid = (int)$pid;
    $qty = max(1,(int)$qty);
    if(isset($cart[$pid])) $cart[$pid] += $qty;
    else $cart[$pid] = $qty;
    $_SESSION['cart'] = $cart;
}
function cart_update($pid, $qty) {
    $cart = cart_get();
    $pid=(int)$pid; $qty=(int)$qty;
    if($qty<=0) unset($cart[$pid]);
    else $cart[$pid] = $qty;
    $_SESSION['cart'] = $cart;
}
function cart_remove($pid) {
    $cart = cart_get();
    $pid=(int)$pid;
    if(isset($cart[$pid])) { unset($cart[$pid]); $_SESSION['cart']=$cart; }
}
function cart_clear(){ unset($_SESSION['cart']); }
function cart_total($pdo) {
    $cart = cart_get();
    if(empty($cart)) return 0;
    $ids = array_keys($cart);
    $place = implode(',', array_fill(0,count($ids),'?'));
    $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($place)");
    $stmt->execute($ids);
    $rows=$stmt->fetchAll();
    $total=0;
    foreach($rows as $r){ $total += $r['price'] * ($cart[$r['id']]); }
    return $total;
}
