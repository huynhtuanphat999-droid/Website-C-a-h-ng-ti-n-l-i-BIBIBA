<?php
require_once 'config.php';
require_once 'functions.php';
$errors = [];
$redirect = $_GET['redirect'] ?? 'index.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $login = trim($_POST['login'] ?? '');
  $password = $_POST['password'] ?? '';
  $redirect = $_POST['redirect'] ?? 'index.php';
  
  if(!$login||!$password) $errors[]='Vui lòng nhập.';
  else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=:l OR email=:l LIMIT 1");
    $stmt->execute([':l'=>$login]); $u = $stmt->fetch();
    if(!$u || !password_verify($password, $u['password'])) $errors[]='Thông tin sai.';
    else {
      unset($u['password']);
      $_SESSION['user']=$u;
      header('Location: ' . $redirect); exit;
    }
  }
}
include 'header.php';
?>
<div class="row justify-content-center"><div class="col-md-5">
<h3>Đăng nhập</h3>
<?php if(isset($_GET['registered'])) echo '<div class="alert alert-success">Đăng ký thành công, hãy đăng nhập.</div>'; ?>
<?php if(isset($_GET['msg'])) echo '<div class="alert alert-warning">'.htmlspecialchars($_GET['msg']).'</div>'; ?>
<?php if($errors) echo '<div class="alert alert-danger">'.implode('<br>',$errors).'</div>'; ?>
<form method="post">
  <input type="hidden" name="redirect" value="<?=htmlspecialchars($redirect)?>">
  <div class="mb-3"><input name="login" class="form-control" placeholder="Username hoặc Email" required></div>
  <div class="mb-3"><input name="password" type="password" class="form-control" placeholder="Mật khẩu" required></div>
  <button class="btn btn-primary">Đăng nhập</button>
  <div class="mt-3 text-center">
    <small>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></small>
  </div>
</form>
</div></div>
<?php include 'footer.php'; ?>
