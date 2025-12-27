<?php
require_once 'config.php';
include 'header.php';

// cấu hình pagination
$perPage = 6; // số tin trên 1 "page" - thay đổi nếu muốn

// nếu request là ajax -> trả về HTML các card (chỉ phần nội dung)
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $perPage;
    $stmt = $pdo->prepare("SELECT * FROM news ORDER BY created_at DESC LIMIT :offset, :limit");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    $news = $stmt->fetchAll();

    if (!$news) {
        // trả về rỗng nếu không còn tin
        echo '';
        exit;
    }

    foreach ($news as $n) {
        ?>
        <div class="col-md-6 mb-4 news-item">
          <div class="card news-card h-100">
            <?php if (!empty($n['image'])): ?>
              <div class="row g-0">
                <div class="col-4">
                  <img loading="lazy" src="<?=htmlspecialchars($n['image'])?>" class="img-fluid rounded-start news-thumb" alt="<?=htmlspecialchars($n['title'])?>">
                </div>
                <div class="col-8">
                  <div class="card-body">
                    <h5 class="news-title"><?=htmlspecialchars($n['title'])?></h5>
                    <small class="news-date text-muted"><?=htmlspecialchars($n['created_at'])?></small>
                    <p class="news-content mt-2 mb-0"><?=nl2br(htmlspecialchars(substr($n['content'],0,300)))?>...</p>
                    <a href="news_detail.php?id=<?=urlencode($n['id'])?>" class="btn btn-sm btn-outline-primary mt-2">Đọc thêm →</a>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <div class="card-body">
                <h5 class="news-title"><?=htmlspecialchars($n['title'])?></h5>
                <small class="news-date text-muted"><?=htmlspecialchars($n['created_at'])?></small>
                <p class="news-content mt-2 mb-0"><?=nl2br(htmlspecialchars(substr($n['content'],0,350)))?>...</p>
                <a href="news_detail.php?id=<?=urlencode($n['id'])?>" class="btn btn-sm btn-outline-primary mt-2">Đọc thêm →</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php
    }
    exit;
}

// nếu không phải AJAX -> hiển thị trang đầy đủ, load page 1 mặc định
$page = 1;
$offset = 0;
$stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$news = $stmt->fetchAll();
?>
<style>
/* Background cho trang tin tức */
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

/* Banner header */
.news-header {
    margin: -1rem -15px 2rem;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.news-banner-image {
    width: 100%;
    height: auto;
    max-height: 200px;
    object-fit: cover;
    object-position: center;
    display: block;
    border-radius: 15px;
}

.news-banner-overlay {
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

.news-banner-overlay h2 {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: white;
}

.news-banner-overlay p {
    font-size: 1.1rem;
    opacity: 0.95;
}

@media (max-width: 768px) {
    .news-banner-overlay h2 {
        font-size: 1.8rem;
    }
    .news-banner-overlay p {
        font-size: 0.9rem;
    }
}

/* --- Styles modern dành cho news --- */
.news-item { opacity: 0; transform: translateY(15px); transition: .45s ease; }
.news-item.visible { opacity: 1; transform: translateY(0); }

.news-card { 
    border: none; 
    border-radius: 15px; 
    box-shadow: 0 8px 25px rgba(0,0,0,0.12); 
    overflow: hidden; 
    transition: all .4s ease;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    height: 100%;
}
.news-card:hover { 
    transform: translateY(-10px) scale(1.02); 
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.news-thumb { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
    display: block;
    transition: transform 0.4s ease;
}

.news-card:hover .news-thumb {
    transform: scale(1.1);
}

.news-title { 
    font-weight: 700;
    color: #2d3748;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
}

.news-date { 
    font-size: .85rem;
    color: #ff6600;
    font-weight: 600;
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: rgba(255, 102, 0, 0.1);
    border-radius: 20px;
}

.news-content { 
    color: #555; 
    line-height: 1.6;
    font-size: 0.95rem;
}

.btn-outline-primary {
    border-color: #ff6600;
    color: #ff6600;
    font-weight: 600;
    border-radius: 20px;
    padding: 0.5rem 1.5rem;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    background: #ff6600;
    border-color: #ff6600;
    color: white;
    transform: translateX(5px);
}

/* spinner và footer load more */
#loadingMore { text-align: center; padding: 20px 0; display: none; }
#noMore { text-align:center; padding:18px 0; color:#777; display:none; }

/* responsive tweak */
@media (max-width: 576px) {
  .news-thumb { height: 120px; }
}
</style>

<div class="news-header text-center position-relative">
    <img src="images/baner.png" alt="Tin tức" class="news-banner-image">
    <div class="news-banner-overlay">
        <h2 class="mb-2"><i class="fas fa-newspaper me-2"></i>Tin Tức</h2>
        <p class="mb-0">Cập nhật những tin tức mới nhất từ chúng tôi</p>
    </div>
</div>

<div class="container my-4">
  <div id="newsGrid" class="row">
    <?php foreach($news as $n): ?>
      <div class="col-md-6 mb-4 news-item">
        <div class="card news-card h-100">
          <?php if (!empty($n['image'])): ?>
            <div class="row g-0">
              <div class="col-4">
                <img loading="lazy" src="<?=htmlspecialchars($n['image'])?>" class="img-fluid rounded-start news-thumb" alt="<?=htmlspecialchars($n['title'])?>">
              </div>
              <div class="col-8">
                <div class="card-body">
                  <h5 class="news-title"><?=htmlspecialchars($n['title'])?></h5>
                  <small class="news-date text-muted"><?=htmlspecialchars($n['created_at'])?></small>
                  <p class="news-content mt-2 mb-0"><?=nl2br(htmlspecialchars(substr($n['content'],0,300)))?>...</p>
                  <a href="news_detail.php?id=<?=urlencode($n['id'])?>" class="btn btn-sm btn-outline-primary mt-2">Đọc thêm →</a>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="card-body">
              <h5 class="news-title"><?=htmlspecialchars($n['title'])?></h5>
              <small class="news-date text-muted"><?=htmlspecialchars($n['created_at'])?></small>
              <p class="news-content mt-2 mb-0"><?=nl2br(htmlspecialchars(substr($n['content'],0,350)))?>...</p>
              <a href="news_detail.php?id=<?=urlencode($n['id'])?>" class="btn btn-sm btn-outline-primary mt-2">Đọc thêm →</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div id="loadingMore">
    <div class="spinner-border" role="status" style="width:36px;height:36px">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>
  <div id="noMore">Không còn bài viết nào nữa.</div>
</div>

<script>
/*
 Infinite scroll logic:
 - Khi user cuộn gần đáy trang, fetch page tiếp theo: news.php?ajax=1&page=...
 - perPage trên server là 6; JS chỉ cần biết số page hiện tại
*/
(function(){
  let page = 1; // đã load page 1 ban đầu
  const perPage = <?= (int)$perPage ?>;
  let loading = false;
  let noMore = false;
  const threshold = 300; // px trước khi đáy sẽ kích hoạt
  const grid = document.getElementById('newsGrid');
  const loadingEl = document.getElementById('loadingMore');
  const noMoreEl = document.getElementById('noMore');

  // reveal initial items with animation
  document.querySelectorAll('.news-item').forEach((el, idx)=>{
    setTimeout(()=> el.classList.add('visible'), idx*80);
  });

  function loadNext(){
    if (loading || noMore) return;
    loading = true;
    loadingEl.style.display = 'block';
    const nextPage = page + 1;
    fetch(`news.php?ajax=1&page=${nextPage}`)
      .then(r => r.text())
      .then(html => {
        loading = false;
        loadingEl.style.display = 'none';
        if (!html.trim()) {
          // server trả về rỗng => no more
          noMore = true;
          noMoreEl.style.display = 'block';
          return;
        }
        // tạo container tạm để parse HTML trả về
        const temp = document.createElement('div');
        temp.innerHTML = html;
        // append each .news-item
        const items = temp.querySelectorAll('.news-item');
        let delayOffset = 0;
        items.forEach((it, i) => {
          it.classList.add('visible');
          grid.appendChild(it);
          // stagger animation
          it.style.transitionDelay = (i*70) + 'ms';
        });
        page = nextPage;
      })
      .catch(err => {
        console.error(err);
        loading = false;
        loadingEl.style.display = 'none';
      });
  }

  // check scroll
  window.addEventListener('scroll', () => {
    if (noMore || loading) return;
    const scrollPos = window.scrollY + window.innerHeight;
    const docHeight = document.documentElement.scrollHeight;
    if (docHeight - scrollPos < threshold) {
      loadNext();
    }
  });

  // also support "load on resize" (optional)
  window.addEventListener('resize', () => {
    if (noMore || loading) return;
    const scrollPos = window.scrollY + window.innerHeight;
    const docHeight = document.documentElement.scrollHeight;
    if (docHeight - scrollPos < threshold) {
      loadNext();
    }
  });
})();
</script>

<?php include 'footer.php'; ?>
