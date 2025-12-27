CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category ENUM('food','drink','dessert') NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  image VARCHAR(255),
  featured TINYINT(1) DEFAULT 0,
  sales_count INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL, 
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(50),
  address TEXT NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  status VARCHAR(50) DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  qty INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

CREATE TABLE news (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  content TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100),
  message TEXT,
  status ENUM('unread', 'read') DEFAULT 'unread',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sample products
INSERT INTO products (name, category, slug, description, price, image, featured, sales_count) VALUES
('Salad Caesar', 'food', 'salad-caesar', 'Salad tươi với sốt Caesar', 59.00, 'images/t1.jpg', 1, 120),
('Súp Bí Đỏ', 'food', 'sup-bi-do', 'Súp bí đỏ thơm ngon', 45.00, 'images/t2.jpg', 0, 80),
('Cà Phê Espresso', 'drink', 'espresso', 'Cà phê đậm đà', 35.00, 'images/t3.jpg', 1, 200),
('Trà Xanh Đá', 'drink', 'tra-xanh-da', 'Trà xanh mát lạnh', 25.00, 'images/t4.jpg', 0, 150),
('Bánh Cheesecake', 'dessert', 'cheesecake', 'Bánh kem phô mai mịn', 75.00, 'images/t5.jpg', 1, 95),
('Kem Tươi', 'dessert', 'kem-tuoi', 'Kem tươi vị vani', 40.00, 'images/t6.jpg', 0, 70);

-- Sample news
INSERT INTO news (title, content) VALUES
('Khai trương chi nhánh mới', 'Chúng tôi vừa khai trương chi nhánh mới...'),
('Khuyến mãi mùa hè', 'Giảm giá 20% cho đồ uống...');
