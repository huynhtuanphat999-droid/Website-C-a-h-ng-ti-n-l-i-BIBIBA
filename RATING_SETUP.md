# Hướng dẫn cài đặt hệ thống đánh giá sản phẩm

## 1. Tạo bảng ratings trong database

Chạy file SQL sau trong phpMyAdmin:

```sql
-- Tạo bảng ratings cho đánh giá sản phẩm
CREATE TABLE IF NOT EXISTS ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  user_id INT,
  rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  UNIQUE KEY unique_user_product (product_id, user_id)
);

-- Thêm cột average_rating vào bảng products
ALTER TABLE products ADD COLUMN average_rating DECIMAL(3,2) DEFAULT 0;
ALTER TABLE products ADD COLUMN rating_count INT DEFAULT 0;
```

## 2. Các file đã được tạo

### `rating_component.php`
- Component hiển thị ratings
- Hàm: `display_product_rating($product_id, $average_rating, $rating_count)`
- Hiển thị: Form đánh giá, thống kê sao, danh sách đánh giá

### `submit_rating.php`
- API endpoint để submit đánh giá
- Kiểm tra user đã mua sản phẩm
- Cập nhật hoặc tạo rating mới
- Trả về JSON response

### `get_ratings.php`
- API endpoint để lấy danh sách ratings
- Hỗ trợ phân trang
- Trả về thống kê chi tiết (5 sao, 4 sao, v.v.)

## 3. Cách sử dụng

### Trên trang chi tiết sản phẩm (product.php)

```php
<?php
require_once 'rating_component.php';

// Hiển thị ratings component
display_product_rating($product_id, $average_rating, $rating_count);
?>
```

### Trên trang danh sách sản phẩm

Hiển thị sao và số lượng đánh giá:

```php
<?php 
$avg_rating = $product['average_rating'] ?? 0;
$rating_count = $product['rating_count'] ?? 0;
for ($i = 1; $i <= 5; $i++): 
?>
    <i class="fas fa-star" style="opacity: <?= $i <= round($avg_rating) ? '1' : '0.3' ?>"></i>
<?php endfor; ?>
<span>(<?= $rating_count ?> đánh giá)</span>
```

## 4. Tính năng

✅ Đánh giá từ 1-5 sao
✅ Thêm bình luận (tùy chọn)
✅ Chỉ user đã mua mới được đánh giá
✅ Mỗi user chỉ đánh giá 1 lần/sản phẩm (có thể sửa)
✅ Hiển thị thống kê chi tiết (biểu đồ sao)
✅ Tự động cập nhật trung bình rating
✅ Responsive design
✅ Real-time updates

## 5. Yêu cầu

- User phải đăng nhập
- User phải có đơn hàng hoàn thành (status = 'completed')
- Sản phẩm phải có trong đơn hàng đó

## 6. Cấu trúc dữ liệu

### Bảng ratings
```
id              - ID đánh giá
product_id      - ID sản phẩm
user_id         - ID user
rating          - Số sao (1-5)
comment         - Bình luận
created_at      - Thời gian tạo
```

### Cột thêm vào products
```
average_rating  - Trung bình rating (0-5)
rating_count    - Số lượng đánh giá
```

## 7. Lưu ý

- Triggers tự động cập nhật average_rating khi có thay đổi
- Unique constraint đảm bảo 1 user chỉ đánh giá 1 lần/sản phẩm
- Xóa user sẽ set user_id = NULL nhưng giữ lại đánh giá
- Xóa sản phẩm sẽ xóa tất cả đánh giá của nó

## 8. Tùy chỉnh

### Thay đổi màu sắc
Sửa trong `rating_component.php`:
- `#ff6600` - Màu cam (sao)
- `#2d3748` - Màu xám đậm (button)
- `#f8f9fa` - Màu nền form

### Thay đổi số sao tối đa
Sửa trong `submit_rating.php` và `rating_component.php`:
```php
if ($rating < 1 || $rating > 5) // Thay 5 thành số khác
```

## 9. Troubleshooting

**Lỗi: "Bạn chỉ có thể đánh giá sản phẩm đã mua"**
- Kiểm tra order status có phải 'completed' không
- Kiểm tra order_items có chứa product_id không

**Lỗi: "Vui lòng đăng nhập"**
- User chưa đăng nhập
- Session hết hạn

**Rating không cập nhật**
- Kiểm tra triggers có được tạo không
- Kiểm tra database permissions
