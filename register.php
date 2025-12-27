<?php
require_once 'config.php';
require_once 'functions.php';
$errors=[]; if($_SERVER['REQUEST_METHOD']==='POST'){
  $username=trim($_POST['username']??''); $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
  if(!$username||!$email||!$pass) $errors[]='Vui lòng điền đủ.';
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Email không hợp lệ.';
  if(empty($errors)){
    // check exists
    $stmt=$pdo->prepare("SELECT id FROM users WHERE username=:u OR email=:e LIMIT 1"); $stmt->execute([':u'=>$username,':e'=>$email]);
    if($stmt->fetch()) $errors[]='Username hoặc Email đã tồn tại.';
    else {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $pdo->prepare("INSERT INTO users(username,email,password) VALUES(:u,:e,:p)")->execute([':u'=>$username,':e'=>$email,':p'=>$hash]);
      header('Location: login.php?registered=1'); exit;
    }
  }
}
include 'header.php';
?>
<div class="row justify-content-center"><div class="col-md-6">
<h3>Đăng ký</h3>
<?php if($errors) echo '<div class="alert alert-danger">'.implode('<br>',$errors).'</div>'; ?>
<form method="post">
  <div class="mb-3"><input name="username" class="form-control" placeholder="Tên đăng nhập" required></div>
  <div class="mb-3"><input name="email" class="form-control" placeholder="Email" required></div>
  <div class="mb-3"><input name="password" type="password" class="form-control" placeholder="Mật khẩu" required></div>
  <button class="btn btn-primary">Đăng ký</button>
</form>
</div></div>
<?php include 'footer.php'; ?>
