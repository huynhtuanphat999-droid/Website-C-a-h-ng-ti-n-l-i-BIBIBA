<?php
// Component hiển thị ratings cho sản phẩm
function display_product_rating($product_id, $average_rating = 0, $rating_count = 0) {
    $user = current_user();
    ?>
    <style>
        .rating-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .rating-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #e1e5e9;
        }
        
        .rating-summary {
            text-align: center;
        }
        
        .rating-big {
            font-size: 3rem;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .rating-stars {
            color: #ff6600;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .rating-count {
            color: #999;
            font-size: 0.9rem;
        }
        
        .rating-bars {
            flex: 1;
        }
        
        .rating-bar-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.8rem;
        }
        
        .rating-bar-label {
            width: 60px;
            text-align: right;
            font-size: 0.9rem;
            color: #666;
        }
        
        .rating-bar-bg {
            flex: 1;
            height: 8px;
            background: #e1e5e9;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .rating-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff6600, #ff8533);
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .rating-bar-count {
            width: 40px;
            text-align: right;
            font-size: 0.9rem;
            color: #666;
        }
        
        .rating-form {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .rating-form h4 {
            margin-bottom: 1rem;
            color: #2d3748;
            font-weight: 600;
        }
        
        .star-rating {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .star-rating .star {
            font-size: 2rem;
            cursor: pointer;
            color: #ddd;
            transition: all 0.2s ease;
        }
        
        .star-rating .star:hover,
        .star-rating .star.active {
            color: #ff6600;
            transform: scale(1.2);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2d3748;
            font-weight: 500;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            resize: vertical;
            min-height: 100px;
            transition: border-color 0.3s ease;
        }
        
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6600;
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
        }
        
        .btn-submit-rating {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit-rating:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(45, 55, 72, 0.3);
        }
        
        .btn-submit-rating:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .rating-list {
            margin-top: 2rem;
        }
        
        .rating-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #ff6600;
        }
        
        .rating-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.8rem;
        }
        
        .rating-item-user {
            font-weight: 600;
            color: #2d3748;
        }
        
        .rating-item-stars {
            color: #ff6600;
            font-size: 0.9rem;
        }
        
        .rating-item-date {
            color: #999;
            font-size: 0.85rem;
        }
        
        .rating-item-comment {
            color: #555;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }
        
        .alert-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            animation: slideInDown 0.3s ease;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
            color: white;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-prompt {
            text-align: center;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 12px;
            color: #666;
        }
        
        .login-prompt a {
            color: #ff6600;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-prompt a:hover {
            text-decoration: underline;
        }
    </style>
    
    <div class="rating-container">
        <h3 style="margin-bottom: 1.5rem; color: #2d3748; font-weight: 700;">
            <i class="fas fa-star" style="color: #ff6600; margin-right: 0.5rem;"></i>
            Đánh giá sản phẩm
        </h3>
        
        <!-- Rating Summary -->
        <div class="rating-header" id="ratingSummary">
            <div class="rating-summary">
                <div class="rating-big" id="avgRating"><?= number_format($average_rating, 1) ?></div>
                <div class="rating-stars" id="summaryStars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star" style="opacity: <?= $i <= round($average_rating) ? '1' : '0.3' ?>"></i>
                    <?php endfor; ?>
                </div>
                <div class="rating-count" id="ratingCount"><?= $rating_count ?> đánh giá</div>
            </div>
            
            <div class="rating-bars" id="ratingBars">
                <?php for ($stars = 5; $stars >= 1; $stars--): ?>
                    <div class="rating-bar-item">
                        <div class="rating-bar-label"><?= $stars ?> <i class="fas fa-star" style="color: #ff6600;"></i></div>
                        <div class="rating-bar-bg">
                            <div class="rating-bar-fill" style="width: 0%" data-stars="<?= $stars ?>"></div>
                        </div>
                        <div class="rating-bar-count" data-count="<?= $stars ?>">0</div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!-- Rating Form -->
        <div id="ratingFormContainer">
            <?php if ($user): ?>
                <div class="rating-form">
                    <h4>Chia sẻ đánh giá của bạn</h4>
                    <div id="ratingMessage"></div>
                    
                    <form id="ratingForm" onsubmit="submitRating(event, <?= $product_id ?>)">
                        <div class="form-group">
                            <label>Đánh giá của bạn</label>
                            <div class="star-rating" id="starRating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star" data-value="<?= $i ?>" onclick="setRating(<?= $i ?>)">
                                        <i class="fas fa-star"></i>
                                    </span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" id="ratingValue" name="rating" value="0">
                        </div>
                        
                        <div class="form-group">
                            <label>Nhận xét (tùy chọn)</label>
                            <textarea name="comment" id="commentText" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit-rating" id="submitBtn">
                            <i class="fas fa-paper-plane me-2"></i>Gửi đánh giá
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="login-prompt">
                    <p>Vui lòng <a href="login.php">đăng nhập</a> để đánh giá sản phẩm này</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Ratings List -->
        <div class="rating-list" id="ratingsList">
            <h4 style="margin-bottom: 1rem; color: #2d3748; font-weight: 600;">Đánh giá từ khách hàng</h4>
            <div id="ratingsContent">
                <p style="text-align: center; color: #999;">Đang tải...</p>
            </div>
        </div>
    </div>
    
    <script>
        let selectedRating = 0;
        
        function setRating(value) {
            selectedRating = value;
            document.getElementById('ratingValue').value = value;
            
            const stars = document.querySelectorAll('#starRating .star');
            stars.forEach((star, index) => {
                if (index < value) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }
        
        function submitRating(e, productId) {
            e.preventDefault();
            
            if (selectedRating === 0) {
                showMessage('Vui lòng chọn số sao', 'warning');
                return;
            }
            
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang gửi...';
            
            const formData = new FormData(document.getElementById('ratingForm'));
            formData.append('product_id', productId);
            
            fetch('submit_rating.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    document.getElementById('ratingForm').reset();
                    selectedRating = 0;
                    document.querySelectorAll('#starRating .star').forEach(s => s.classList.remove('active'));
                    loadRatings(productId);
                } else {
                    showMessage(data.message, 'error');
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Gửi đánh giá';
            })
            .catch(err => {
                showMessage('Lỗi: ' + err.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Gửi đánh giá';
            });
        }
        
        function showMessage(msg, type) {
            const container = document.getElementById('ratingMessage');
            container.innerHTML = `<div class="alert-message alert-${type}">${msg}</div>`;
            setTimeout(() => {
                container.innerHTML = '';
            }, 3000);
        }
        
        function loadRatings(productId, page = 1) {
            fetch(`get_ratings.php?product_id=${productId}&page=${page}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        updateRatingSummary(data.stats);
                        displayRatings(data.ratings);
                    }
                })
                .catch(err => console.error(err));
        }
        
        function updateRatingSummary(stats) {
            if (!stats || !stats.count) return;
            
            document.getElementById('avgRating').textContent = (stats.avg_rating || 0).toFixed(1);
            document.getElementById('ratingCount').textContent = stats.count + ' đánh giá';
            
            // Update stars
            const summaryStars = document.getElementById('summaryStars');
            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                starsHtml += `<i class="fas fa-star" style="opacity: ${i <= Math.round(stats.avg_rating) ? '1' : '0.3'}"></i>`;
            }
            summaryStars.innerHTML = starsHtml;
            
            // Update bars
            const bars = [
                { stars: 5, count: stats.five_star || 0 },
                { stars: 4, count: stats.four_star || 0 },
                { stars: 3, count: stats.three_star || 0 },
                { stars: 2, count: stats.two_star || 0 },
                { stars: 1, count: stats.one_star || 0 }
            ];
            
            bars.forEach(bar => {
                const percentage = stats.count > 0 ? (bar.count / stats.count) * 100 : 0;
                const fillEl = document.querySelector(`.rating-bar-fill[data-stars="${bar.stars}"]`);
                const countEl = document.querySelector(`.rating-bar-count[data-count="${bar.stars}"]`);
                if (fillEl) fillEl.style.width = percentage + '%';
                if (countEl) countEl.textContent = bar.count;
            });
        }
        
        function displayRatings(ratings) {
            const container = document.getElementById('ratingsContent');
            
            if (ratings.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #999;">Chưa có đánh giá nào</p>';
                return;
            }
            
            let html = '';
            ratings.forEach(rating => {
                const stars = '<i class="fas fa-star"></i>'.repeat(rating.rating) + 
                             '<i class="fas fa-star" style="opacity: 0.3;"></i>'.repeat(5 - rating.rating);
                
                html += `
                    <div class="rating-item">
                        <div class="rating-item-header">
                            <div>
                                <div class="rating-item-user">${rating.username || 'Khách'}</div>
                                <div class="rating-item-date">${new Date(rating.created_at).toLocaleDateString('vi-VN')}</div>
                            </div>
                            <div class="rating-item-stars">${stars}</div>
                        </div>
                        ${rating.comment ? `<div class="rating-item-comment">${rating.comment}</div>` : ''}
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        // Load ratings on page load
        loadRatings(<?= $product_id ?>);
    </script>
    <?php
}
?>
