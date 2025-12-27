<?php
require_once 'config.php';
include 'header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo '<div class="container my-5"><div class="alert alert-danger">Bài viết không hợp lệ.</div></div>';
    include 'footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$n = $stmt->fetch();

if (!$n) {
    echo '<div class="container my-5"><div class="alert alert-warning">Không tìm thấy bài viết.</div></div>';
    include 'footer.php';
    exit;
}
?>
<style>
.article-hero { border-radius: 12px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.08); margin-bottom:18px; }
.article-content { line-height:1.8; color: #333; }
.article-meta { color:#666; font-size:.95rem; margin-bottom:12px; }
</style>

<div class="container my-5">
  <a href="news.php" class="btn btn-sm btn-outline-secondary mb-3">← Quay về</a>

  <div class="article-hero">
    <?php if (!empty($n['image'])): ?>
      <img loading="lazy" src="<?=htmlspecialchars($n['image'])?>" class="img-fluid w-100" alt="<?=htmlspecialchars($n['title'])?>">
    <?php endif; ?>
  </div>

  <h1 class="fw-bold"><?=htmlspecialchars($n['title'])?></h1>
  <div class="article-meta">
    <span class="me-3"><i class="bi bi-clock"></i> <?=htmlspecialchars($n['created_at'])?></span>
    <?php if (!empty($n['author'])): ?><span class="me-3"><i class="bi bi-person"></i> <?=htmlspecialchars($n['author'])?></span><?php endif; ?>
  </div>

  <div class="article-content">
    <?= nl2br(htmlspecialchars($n['content'])) ?>
  </div>

  <!-- Gợi ý: có thể thêm tin liên quan -->
  <div class="mt-5">
    <h5>Tin liên quan</h5>
    <div id="related" class="row">
      <?php
        // lấy 3 bài cùng ngày hoặc gần nhất (ví dụ đơn giản)
        $stmt2 = $pdo->prepare("SELECT id,title,created_at FROM news WHERE id <> :id ORDER BY created_at DESC LIMIT 3");
        $stmt2->execute([':id'=>$id]);
        $related = $stmt2->fetchAll();
        foreach($related as $r): ?>
          <div class="col-md-4">
            <a href="news_detail.php?id=<?=urlencode($r['id'])?>">
              <div class="p-2 small"><?=htmlspecialchars($r['title'])?></div>
            </a>
          </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
