-- Thêm cột stock (hàng tồn kho) vào bảng products
ALTER TABLE products ADD COLUMN IF NOT EXISTS stock INT DEFAULT 0;

-- Cập nhật stock mặc định cho các sản phẩm hiện có
UPDATE products SET stock = 100 WHERE stock = 0;
