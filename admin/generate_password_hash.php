<?php
// Script tạo hash mật khẩu cho admin
$password = "123456";
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Mật khẩu: " . $password . "\n";
echo "Hash: " . $hash . "\n";
echo "\nSQL để cập nhật:\n";
echo "UPDATE admins SET password = '" . $hash . "' WHERE username = 'admin';\n";
?>