-- Reset mật khẩu admin
-- Chạy script này trong phpMyAdmin hoặc MySQL

-- Mật khẩu mới: 123456
UPDATE admins SET password = '$2y$10$oX8dI0Ao9c5voHk0Oe9x3OpeBOQ7FJpWSZTCn1h1.YedRHW7VpshO' WHERE username = 'admin';

-- Hash cho mật khẩu "123456": $2y$10$oX8dI0Ao9c5voHk0Oe9x3OpeBOQ7FJpWSZTCn1h1.YedRHW7VpshO

-- Kiểm tra kết quả
SELECT username, password FROM admins WHERE username = 'admin';

-- Sau khi chạy xong, đăng nhập với:
-- Username: admin
-- Password: 123456
