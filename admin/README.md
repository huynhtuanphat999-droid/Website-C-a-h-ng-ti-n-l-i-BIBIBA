# Admin Panel - BIBIBABA Food

## Hướng dẫn cài đặt

### Bước 1: Chạy Setup
Truy cập vào URL sau để cài đặt admin panel:
```
phpphp
```

Hoặc chạy file SQL thủ công:
```
http://localhost/admin/setup_admin.sql
```

### Bước 2: Đăng nhập
Sau khi setup xong, truy cập:
```
http://localhost/admin/login.php
```

**Thông tin đăng nhập mặc định:**
- Username: `admin`
- Password: `admin123`

⚠️ **LƯU Ý BẢO MẬT:** Vui lòng đổi mật khẩu ngay sau khi đăng nhập lần đầu!

## Tính năng

### Dashboard
- Thống kê tổng quan (đơn hàng, sản phẩm, khách hàng, doanh thu)
- Danh sách đơn hàng gần đây
- Top sản phẩm bán chạy

### Quản lý
- ✅ Đơn hàng
- ✅ Sản phẩm
- ✅ Khách hàng
- ✅ Tin tức
- ✅ Cài đặt

## Cấu trúc file

```
admin/
├── login.php          # Trang đăng nhập
├── dashboard.php      # Trang chủ admin
├── logout.php         # Đăng xuất
├── setup.php          # Cài đặt tự động
├── setup_admin.sql    # File SQL setup
└── README.md          # Hướng dẫn
```

## Bảo mật

1. **Đổi mật khẩu mặc định** ngay sau khi đăng nhập lần đầu
2. **Không để file setup.php** trên server production
3. **Sử dụng HTTPS** cho trang admin
4. **Giới hạn IP** truy cập vào thư mục admin nếu có thể

## Hỗ trợ

Nếu gặp vấn đề, vui lòng kiểm tra:
- Database connection trong `config.php`
- Quyền truy cập thư mục
- PHP version >= 7.4
- MySQL/MariaDB đang chạy
