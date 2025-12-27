-- Cập nhật bảng contacts để thêm cột status
-- Chạy file này nếu bảng contacts đã tồn tại và chưa có cột status

USE ecommerce_food;

-- Kiểm tra và thêm cột status nếu chưa có
ALTER TABLE contacts 
ADD COLUMN IF NOT EXISTS status ENUM('unread', 'read') DEFAULT 'unread' AFTER message;

-- Cập nhật các bản ghi cũ thành 'unread'
UPDATE contacts SET status = 'unread' WHERE status IS NULL;

-- Cập nhật cột created_at nếu chưa có default
ALTER TABLE contacts 
MODIFY COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;

SELECT 'Cập nhật bảng contacts thành công!' AS message;
