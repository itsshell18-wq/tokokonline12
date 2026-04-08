
database_sql = """
-- Database: toko_online
-- Struktur lengkap untuk toko online dengan fitur cicilan, spaylater, dll

CREATE DATABASE IF NOT EXISTS toko_online CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE toko_online;

-- Tabel Users (Pengguna)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_image VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Kategori Produk
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Produk
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(15,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255) DEFAULT 'default-product.jpg',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tabel Keranjang
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- Tabel Metode Pembayaran
CREATE TABLE payment_methods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('instant', 'cicilan', 'paylater') DEFAULT 'instant',
    icon VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Pesanan
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_code VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method_id INT,
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    cicilan_months INT DEFAULT 0, -- 0 untuk tidak cicilan
    cicilan_per_month DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
);

-- Tabel Detail Pesanan
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Tabel Riwayat Pembayaran
CREATE TABLE payment_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method VARCHAR(100),
    status VARCHAR(50),
    transaction_id VARCHAR(100),
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert Data Awal
INSERT INTO categories (name, slug, description) VALUES
('Elektronik', 'elektronik', 'Produk elektronik dan gadget'),
('Fashion', 'fashion', 'Pakaian dan aksesoris'),
('Makanan', 'makanan', 'Makanan dan minuman'),
('Rumah Tangga', 'rumah-tangga', 'Kebutuhan rumah tangga');

INSERT INTO payment_methods (name, code, type, icon) VALUES
('Transfer Bank', 'bank_transfer', 'instant', 'bank-icon.png'),
('E-Wallet', 'e_wallet', 'instant', 'wallet-icon.png'),
('Credit Card', 'credit_card', 'instant', 'card-icon.png'),
('Cicilan 0%', 'cicilan', 'cicilan', 'cicilan-icon.png'),
('SPayLater', 'spaylater', 'paylater', 'spaylater-icon.png'),
('COD (Bayar di Tempat)', 'cod', 'instant', 'cod-icon.png');

INSERT INTO products (category_id, name, slug, description, price, stock, image) VALUES
(1, 'Smartphone Samsung Galaxy S24', 'samsung-galaxy-s24', 'Smartphone flagship terbaru', 12999000, 50, 'samsung-s24.jpg'),
(1, 'Laptop ASUS ROG Strix', 'asus-rog-strix', 'Laptop gaming high-end', 18500000, 30, 'asus-rog.jpg'),
(2, 'Nike Air Max 90', 'nike-air-max-90', 'Sepatu olahraga premium', 1899000, 100, 'nike-airmax.jpg'),
(2, 'Jaket Bomber Pria', 'jaket-bomber-pria', 'Jaket stylish untuk pria', 450000, 80, 'jaket-bomber.jpg'),
(3, 'Kopi Arabika Premium', 'kopi-arabika-premium', 'Kopi arabika grade A', 125000, 200, 'kopi-arabika.jpg'),
(4, 'Set Peralatan Dapur', 'set-peralatan-dapur', 'Complete kitchen set', 750000, 60, 'kitchen-set.jpg');

-- Insert user admin default (password: admin123)
INSERT INTO users (username, email, password, full_name, phone, address) VALUES
('admin', 'admin@toko.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '08123456789', 'Jl. Admin No. 1');
"""

# Simpan ke file
with open('/mnt/kimi/output/database.sql', 'w', encoding='utf-8') as f:
    f.write(database_sql)

print("✅ File database.sql berhasil dibuat!")
print("\n📋 Struktur Database yang dibuat:")
print("- users: Tabel pengguna dengan profil lengkap")
print("- categories: Kategori produk")
print("- products: Daftar produk toko")
print("- cart: Keranjang belanja")
print("- payment_methods: Metode pembayaran (termasuk cicilan & spaylater)")
print("- orders: Data pesanan")
print("- order_items: Detail item dalam pesanan")
print("- payment_history: Riwayat pembayaran")
print("\n🎁 Bonus: Data sample sudah di-insert (produk, kategori, metode pembayaran, admin)")
