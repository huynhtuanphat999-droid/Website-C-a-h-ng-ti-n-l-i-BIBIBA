-- Tạo bảng admins nếu chưa có
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Xóa admin cũ nếu có
DELETE FROM admins WHERE username = 'admin';

-- Thêm admin mới với mật khẩu: admin123
INSERT INTO admins (username, password, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@bibibaba.com');

-- Kiểm tra kết quả
SELECT * FROM admins;
