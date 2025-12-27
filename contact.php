<?php
require_once 'config.php';
include 'header.php';

$success = "";
$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if($name == "" || $email == "" || $message == ""){
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO contacts(name, email, message, created_at) VALUES (?, ?, ?, NOW())");
        if($stmt->execute([$name, $email, $message])){
            $success = "Gửi thành công! Chúng tôi sẽ phản hồi sớm nhất.";
        } else {
            $error = "Lỗi hệ thống! Vui lòng thử lại.";
        }
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<div class="contact-wrapper container py-5">

    <h2 class="text-center mb-4" data-aos="fade-down">
        📩 Liên hệ với chúng tôi
    </h2>

    <?php if($success): ?>
      <div class="alert alert-success"><?=$success?></div>
    <?php endif; ?>

    <?php if($error): ?>
      <div class="alert alert-danger"><?=$error?></div>
    <?php endif; ?>

    <div class="row g-4">

        <div class="col-lg-6" data-aos="fade-right">
            <form method="POST" class="contact-form p-4 shadow-lg rounded-3 bg-white">
                
                <div class="form-floating mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Tên của bạn">
                    <label><i class="fa-solid fa-user"></i> Họ & Tên</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" placeholder="email của bạn">
                    <label><i class="fa-solid fa-envelope"></i> Email</label>
                </div>

                <div class="form-floating mb-3">
                    <textarea name="message" class="form-control" style="height: 150px;" placeholder="Nội dung"></textarea>
                    <label><i class="fa-solid fa-message"></i> Nội dung</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    <i class="fa-solid fa-paper-plane"></i> Gửi liên hệ
                </button>

            </form>
        </div>

  <?php
$google_map_url = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.541570837748!2d106.66748231533463!3d10.764288061411166!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752ef276e0d47b%3A0x8a76d0d9e04e92c!2zVHLGsOG7nW5nIMSQ4bqhaSBI4bqvYyBIb8Og!5e0!3m2!1sen!2s!4v1699812345678!5m2!1sen!2s";
?>

<div class="col-lg-6" data-aos="fade-left">
  <div class="shadow-lg rounded-3" style="height:400px;">
    <iframe
      src="<?= $google_map_url ?>"
      width="100%" height="100%"
      style="border:0;"
      allowfullscreen=""
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade">
    </iframe>
  </div>
</div>


    </div>
</div>
<div class="row mt-5">

  <div class="col-md-4 text-center" data-aos="fade-up">
    <div class="info-box p-3 shadow-sm rounded">
      <i class="fa-solid fa-phone fa-2x mb-2 text-primary"></i>
      <h5>Hotline</h5>
      <p><a href="tel:0123456789">0365376880</a></p>
    </div>
  </div>

  <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="200">
    <div class="info-box p-3 shadow-sm rounded">
      <i class="fa-solid fa-location-dot fa-2x mb-2 text-danger"></i>
      <h5>Địa chỉ</h5>
      <p>cầu chữ Y, TP. Hồ Chí Minh</p>
    </div>
  </div>

  <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="400">
    <div class="info-box p-3 shadow-sm rounded">
      <i class="fa-solid fa-clock fa-2x mb-2 text-warning"></i>
      <h5>Giờ mở cửa</h5>
      <p>8:00 - 22:00 mỗi ngày</p>
    </div>
  </div>

</div>

<div class="text-center mt-4" data-aos="zoom-in">
  <h4>Kết nối với chúng tôi</h4>
  <div class="social-icons mt-2">
    <a href="#" class="me-3"><i class="fab fa-facebook fa-2x"></i></a>
    <a href="#" class="me-3"><i class="fab fa-instagram fa-2x"></i></a>
    <a href="#"><i class="fab fa-tiktok fa-2x"></i></a>
  </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>AOS.init({ duration: 900 });</script>
<!-- Popup cảm ơn -->
<div id="thankyouModal" class="modal-overlay">
  <div class="modal-content" data-aos="zoom-in">
    <i class="fa-solid fa-circle-check text-success fa-4x mb-3"></i>
    <h4>Cảm ơn bạn!</h4>
    <p>Chúng tôi sẽ phản hồi trong thời gian sớm nhất.</p>
    <button class="btn btn-success mt-3" onclick="closePopup()">Đóng</button>
  </div>
</div>
<?php if($success): ?>
<script>
document.getElementById('thankyouModal').style.display = 'flex';
setTimeout(() => {
    closePopup();
}, 3500);
</script>
<?php endif; ?>

<script>
function closePopup(){
  document.getElementById('thankyouModal').style.display = 'none';
}
</script>


<?php include 'footer.php'; ?>
