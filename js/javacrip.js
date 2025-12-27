<script>
function filterCategory(cat){
  const target = document.getElementById('filterResults');
  target.innerHTML = `
    <div class="col-12 text-center py-4 opacity-75">
      <div class="spinner-border" role="status"></div>
      <div class="mt-2">Đang tải...</div>
    </div>`;

  fetch('products.php?ajax=1&cat=' + encodeURIComponent(cat))
    .then(r => r.text())
    .then(html => {
      target.innerHTML = html;
      target.classList.add('fade-in');
    });
}

// load mặc định
filterCategory('');
</script>
