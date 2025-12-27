-- Thêm cột image vào bảng news nếu chưa có
ALTER TABLE news ADD COLUMN IF NOT EXISTS image VARCHAR(255) AFTER content;

-- Xóa tin tức cũ (nếu muốn)
-- DELETE FROM news;

-- Thêm các tin tức mới độc đáo với hình ảnh
INSERT INTO news (title, content, image, created_at) VALUES
(
    '🎉 Ra mắt thực đơn mùa thu đặc biệt - Hương vị truyền thống Việt Nam',
    'Chào mừng mùa thu về, BIBIBABA tự hào giới thiệu thực đơn mùa thu đặc biệt với những món ăn truyền thống Việt Nam được chế biến theo công thức gia truyền.\n\nĐặc biệt, chúng tôi có:\n- Phở bò truyền thống với nước dùng ninh từ xương trong 12 tiếng\n- Bún chả Hà Nội với thịt nướng than hoa thơm lừng\n- Bánh xèo miền Tây giòn rụm, nhân đầy đặn\n- Chè đậu xanh nước cốt dừa mát lạnh\n\nGiảm giá 15% cho tất cả các món trong thực đơn mùa thu từ ngày 1-15 tháng này. Đặt hàng ngay để thưởng thức hương vị đặc trưng của mùa thu Việt Nam!',
    'images/t1.jpg',
    DATE_SUB(NOW(), INTERVAL 2 DAY)
),
(
    '☕ Bí quyết pha chế cà phê hoàn hảo từ Barista chuyên nghiệp',
    'Bạn có biết rằng một tách cà phê ngon không chỉ đến từ hạt cà phê chất lượng mà còn từ kỹ thuật pha chế?\n\nBarista chuyên nghiệp của BIBIBABA chia sẻ:\n\n1. Chọn hạt cà phê: Sử dụng hạt cà phê Arabica rang vừa, bảo quản trong hộp kín\n2. Nhiệt độ nước: 92-96°C là nhiệt độ lý tưởng\n3. Tỷ lệ cà phê/nước: 1:15 cho espresso, 1:17 cho pour over\n4. Thời gian chiết xuất: 25-30 giây cho espresso\n5. Dụng cụ sạch sẽ: Vệ sinh máy pha sau mỗi lần sử dụng\n\nGhé BIBIBABA để thưởng thức cà phê được pha chế bởi những barista tay nghề cao nhất!',
    'images/t3.jpg',
    DATE_SUB(NOW(), INTERVAL 5 DAY)
),
(
    '🍰 Khám phá nghệ thuật làm bánh Cheesecake Nhật Bản',
    'Cheesecake Nhật Bản nổi tiếng với kết cấu mềm mịn như mây, tan chảy trong miệng. Đây là món tráng miệng được yêu thích nhất tại BIBIBABA.\n\nĐiểm đặc biệt:\n- Sử dụng phô mai cream cheese Nhật Bản cao cấp\n- Kỹ thuật nướng cách thủy để giữ độ ẩm\n- Nhiệt độ lạnh vừa phải, không quá cứng\n- Lớp bánh mỏng, nhẹ như bông\n\nMỗi chiếc bánh được làm thủ công bởi đầu bếp có hơn 10 năm kinh nghiệm. Đặt trước 1 ngày để đảm bảo độ tươi ngon nhất!\n\nGiá đặc biệt: 65.000đ (giảm từ 75.000đ) trong tuần này.',
    'images/t5.jpg',
    DATE_SUB(NOW(), INTERVAL 7 DAY)
),
(
    '🥗 Xu hướng ăn uống lành mạnh 2024 - Salad không còn nhàm chán',
    'Salad đã không còn là món ăn nhàm chán! BIBIBABA mang đến 10+ công thức salad sáng tạo, đầy màu sắc và dinh dưỡng.\n\nTop 3 salad được yêu thích:\n\n1. Salad Caesar Đặc Biệt\n- Rau xà lách romaine tươi giòn\n- Gà nướng hun khói\n- Sốt Caesar tự làm\n- Phô mai Parmesan bào\n- Bánh mì nướng giòn\n\n2. Salad Cầu Vồng Nhiệt Đới\n- 7 loại rau củ đầy màu sắc\n- Tôm nướng bơ tỏi\n- Sốt chanh dây\n\n3. Salad Quinoa Siêu Thực Phẩm\n- Quinoa hữu cơ\n- Bơ, cà chua bi\n- Hạt chia, hạnh nhân\n\nMỗi phần salad cung cấp đầy đủ protein, vitamin và khoáng chất cho một bữa ăn lành mạnh!',
    'images/t2.jpg',
    DATE_SUB(NOW(), INTERVAL 10 DAY)
),
(
    '🎊 Chương trình khách hàng thân thiết - Tích điểm đổi quà hấp dẫn',
    'BIBIBABA tri ân khách hàng với chương trình tích điểm đổi quà cực kỳ hấp dẫn!\n\nQuy đổi điểm:\n- Mỗi 10.000đ = 1 điểm\n- Sinh nhật tặng 50 điểm\n- Giới thiệu bạn bè tặng 100 điểm\n\nQuà tặng:\n- 100 điểm: Voucher 50.000đ\n- 200 điểm: Combo đồ uống miễn phí\n- 500 điểm: Voucher 200.000đ\n- 1000 điểm: Bữa ăn miễn phí cho 2 người\n\nĐăng ký thành viên ngay hôm nay để nhận 50 điểm chào mừng!\n\nLiên hệ: 0123.456.789 hoặc đến trực tiếp cửa hàng để đăng ký.',
    'images/t4.jpg',
    DATE_SUB(NOW(), INTERVAL 12 DAY)
),
(
    '🍜 Câu chuyện về món Phở - Linh hồn ẩm thực Việt Nam',
    'Phở không chỉ là món ăn, mà là một phần văn hóa, là niềm tự hào của người Việt Nam. Tại BIBIBABA, chúng tôi tôn vinh món ăn truyền thống này.\n\nLịch sử:\nPhở xuất hiện từ đầu thế kỷ 20 tại Hà Nội, kết hợp giữa ẩm thực Việt và Pháp. Từ một món ăn đường phố bình dân, phở đã trở thành biểu tượng ẩm thực Việt Nam trên thế giới.\n\nBí quyết nước dùng:\n- Xương bò ninh 12-15 tiếng\n- Gia vị: Hành, gừng nướng, hồi, quế, thảo quả\n- Nước trong, ngọt tự nhiên từ xương\n- Không dùng bột ngọt\n\nCách thưởng thức:\n- Thêm rau thơm: húng quế, ngò gai, giá\n- Nêm chanh, ớt theo khẩu vị\n- Ăn nóng khi mới múc\n\nGhé BIBIBABA để thưởng thức tô phở truyền thống đúng điệu!',
    'images/t6.jpg',
    DATE_SUB(NOW(), INTERVAL 15 DAY)
),
(
    '🌟 Khai trương chi nhánh thứ 5 - Ưu đãi khủng trong tháng đầu',
    'Tin vui cho các tín đồ ẩm thực! BIBIBABA chính thức khai trương chi nhánh thứ 5 tại Quận 7, TP.HCM.\n\nĐịa chỉ mới:\n📍 123 Nguyễn Văn Linh, Quận 7, TP.HCM\n⏰ Giờ mở cửa: 7:00 - 22:00 hàng ngày\n☎️ Hotline: 0123.456.789\n\nƯu đãi khai trương (30 ngày đầu):\n- Giảm 30% toàn bộ thực đơn\n- Tặng 1 ly đồ uống khi order từ 2 món\n- Miễn phí giao hàng trong bán kính 3km\n- Tặng voucher 100.000đ cho 100 khách hàng đầu tiên\n\nKhông gian mới:\n- Diện tích 200m2 rộng rãi\n- Thiết kế hiện đại, Instagram-able\n- Khu vực riêng cho gia đình\n- Wifi tốc độ cao miễn phí\n\nHẹn gặp bạn tại chi nhánh mới!',
    'images/t7.jpg',
    DATE_SUB(NOW(), INTERVAL 1 DAY)
),
(
    '🍹 Top 5 đồ uống giải nhiệt mùa hè không thể bỏ qua',
    'Mùa hè nóng bức, BIBIBABA giới thiệu 5 loại đồ uống giải nhiệt tuyệt vời, vừa ngon vừa tốt cho sức khỏe!\n\n1. Trà Xanh Chanh Dây\n- Trà xanh Thái Nguyên\n- Chanh dây tươi\n- Mật ong nguyên chất\n- Giá: 35.000đ\n\n2. Sinh Tố Bơ Sữa Chua\n- Bơ Đắk Lắk\n- Sữa chua Hy Lạp\n- Hạt chia\n- Giá: 45.000đ\n\n3. Nước Ép Dưa Hấu Bạc Hà\n- Dưa hấu không hạt\n- Lá bạc hà tươi\n- Chanh tươi\n- Giá: 30.000đ\n\n4. Trà Đào Cam Sả\n- Đào ngâm tự làm\n- Cam tươi\n- Sả tươi\n- Giá: 40.000đ\n\n5. Soda Việt Quất\n- Việt quất tươi\n- Soda\n- Chanh tươi\n- Giá: 38.000đ\n\nCombo 3 ly bất kỳ chỉ 99.000đ!',
    'images/xoai.jpg',
    DATE_SUB(NOW(), INTERVAL 20 DAY)
);
