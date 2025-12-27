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

-- Tạo trigger để cập nhật average_rating khi có rating mới
DELIMITER $$
CREATE TRIGGER update_product_rating AFTER INSERT ON ratings
FOR EACH ROW
BEGIN
  UPDATE products 
  SET average_rating = (SELECT AVG(rating) FROM ratings WHERE product_id = NEW.product_id),
      rating_count = (SELECT COUNT(*) FROM ratings WHERE product_id = NEW.product_id)
  WHERE id = NEW.product_id;
END$$
DELIMITER ;

-- Tạo trigger để cập nhật khi xóa rating
DELIMITER $$
CREATE TRIGGER update_product_rating_delete AFTER DELETE ON ratings
FOR EACH ROW
BEGIN
  UPDATE products 
  SET average_rating = COALESCE((SELECT AVG(rating) FROM ratings WHERE product_id = OLD.product_id), 0),
      rating_count = (SELECT COUNT(*) FROM ratings WHERE product_id = OLD.product_id)
  WHERE id = OLD.product_id;
END$$
DELIMITER ;

-- Tạo trigger để cập nhật khi sửa rating
DELIMITER $$
CREATE TRIGGER update_product_rating_update AFTER UPDATE ON ratings
FOR EACH ROW
BEGIN
  UPDATE products 
  SET average_rating = (SELECT AVG(rating) FROM ratings WHERE product_id = NEW.product_id),
      rating_count = (SELECT COUNT(*) FROM ratings WHERE product_id = NEW.product_id)
  WHERE id = NEW.product_id;
END$$
DELIMITER ;
