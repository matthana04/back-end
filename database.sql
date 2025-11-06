-- สร้างฐานข้อมูล
CREATE DATABASE IF NOT EXISTS online_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE online_shop;

-- ตารางผู้ใช้
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'seller', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางร้านค้า
CREATE TABLE shops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    shop_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางสินค้า
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางคำสั่งซื้อ
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางรายการสินค้าในคำสั่งซื้อ
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางตะกร้าสินค้า
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- เพิ่มข้อมูลตัวอย่าง
-- Admin (username: admin, password: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'admin');

-- Seller (username: seller1, password: seller123)
INSERT INTO users (username, email, password, full_name, phone, role) VALUES 
('seller1', 'seller1@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ร้านค้าตัวอย่าง', '081-234-5678', 'seller');

-- User (username: user1, password: user123)
INSERT INTO users (username, email, password, full_name, phone, address) VALUES 
('user1', 'user1@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ลูกค้าทดสอบ', '082-345-6789', '123 ถนนทดสอบ กรุงเทพฯ 10100');

-- ร้านค้าตัวอย่าง
INSERT INTO shops (seller_id, shop_name, description) VALUES 
(2, 'ร้านค้าตัวอย่าง', 'ร้านค้าสำหรับทดสอบระบบ');

-- สินค้าตัวอย่าง
INSERT INTO products (shop_id, name, description, price, stock, image) VALUES 
(1, 'สินค้าตัวอย่าง 1', 'รายละเอียดสินค้าตัวอย่าง 1', 299.00, 50, 'product1.jpg'),
(1, 'สินค้าตัวอย่าง 2', 'รายละเอียดสินค้าตัวอย่าง 2', 499.00, 30, 'product2.jpg'),
(1, 'สินค้าตัวอย่าง 3', 'รายละเอียดสินค้าตัวอย่าง 3', 799.00, 20, 'product3.jpg');