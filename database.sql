-- Database for Swahili Food Ordering System

CREATE DATABASE IF NOT EXISTS swahili_food;
USE swahili_food;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

-- Products table (Food and Drinks)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    rating DECIMAL(2, 1) DEFAULT 0.0,
    reviews_count INT DEFAULT 0,
    discount INT DEFAULT 0,
    image_url TEXT,
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_price DECIMAL(10, 2) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    status ENUM('Pending', 'Preparing', 'Out for delivery', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order Items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_at_time DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Seed Data
INSERT INTO categories (name) VALUES ('Foods'), ('Drinks');

INSERT INTO products (category_id, name, price, rating, reviews_count, discount, image_url) VALUES 
(1, 'Ugali', 3000, 4.5, 120, 0, 'https://i.pinimg.com/1200x/fa/8e/03/fa8e03bad48021d28a3181e5e2e4afb0.jpg'),
(1, 'Pilau', 5000, 4.7, 95, 10, 'https://i.pinimg.com/736x/29/90/5b/29905b5e366cf1301132177b032beebc.jpg'),
(1, 'Samosa', 500, 4.3, 200, 0, 'https://i.pinimg.com/736x/a8/0a/c5/a80ac5a1b93b074abdf13d64b8cb7acd.jpg'),
(1, 'Wali Samaki', 5000, 4.8, 80, 5, 'https://i.pinimg.com/1200x/bf/8f/6e/bf8f6e33c977e66775c3c919ad54b5b7.jpg'),
(1, 'Wali wa Nazi', 3000, 4.4, 110, 0, 'https://i.pinimg.com/736x/01/6f/e9/016fe95aac7d5cef1260569c85112572.jpg'),
(1, 'Mahamri', 500, 4.2, 70, 0, 'https://i.pinimg.com/736x/0a/bf/7f/0abf7fc2520602ec4f97f39e22cd8534.jpg'),
(1, 'Mishkaki', 1000, 4.6, 130, 15, 'https://i.pinimg.com/1200x/ad/6c/50/ad6c5089311d053d137e42c61b382665.jpg'),
(1, 'Ndizi za kuKaanga', 500, 4.1, 60, 0, 'https://i.pinimg.com/1200x/24/d0/b9/24d0b9bb0930b2ddae246b46fdc07d0e.jpg'),
(1, 'Kuku', 6000, 4.7, 90, 0, 'https://i.pinimg.com/1200x/1f/10/b3/1f10b3adb83e9ecdd56d2a6d1d430e9b.jpg'),
(1, 'Chipsi', 3000, 4.3, 100, 0, 'https://i.pinimg.com/736x/3c/54/75/3c54751cdaf44bc2f20a36ece8c4860a.jpg'),
(2, 'Chai ya Tangawizi', 500, 4.5, 150, 0, 'https://i.pinimg.com/736x/91/e4/3b/91e43b0370c301a6630bf9557fb08a25.jpg'),
(2, 'Maji', 500, 4.2, 90, 0, 'https://i.pinimg.com/1200x/54/0f/16/540f1669b741ed4ffd0adc0e6583945c.jpg'),
(2, 'Kahawa', 500, 4.6, 120, 5, 'https://i.pinimg.com/736x/f8/38/17/f83817560f3c6cc3c9b311ad82517daf.jpg'),
(2, 'Juice ya Matunda', 500, 4.7, 110, 0, 'https://i.pinimg.com/736x/b3/3c/24/b33c24ddd7c4fe45f4bb6f71d37e32e1.jpg'),
(2, 'Soda', 600, 4.0, 80, 0, 'https://i.pinimg.com/1200x/79/6c/34/796c34906c9111f02f41a319298a261b.jpg'),
(2, 'Maziwa Freshi', 1000, 4.3, 70, 0, 'https://i.pinimg.com/736x/4e/e9/2b/4ee92bb7febf5a17825e77cd2cba416d.jpg'),
(2, 'Milk shake', 2000, 4.4, 65, 10, 'https://i.pinimg.com/736x/ba/c9/0d/bac90dcb464636dbad025d79c19a93e4.jpg'),
(2, 'Maziwa mgando', 1000, 4.5, 75, 0, 'https://i.pinimg.com/1200x/e3/0e/0a/e30e0a3070bae2b2ec7a9a6101c85b7b.jpg');

-- Default Admin (Password: admin123)
-- NOTE: In production, password should be hashed. Using plain text for demonstration if hashing is not immediately available.
-- However, I will use password_hash in PHP.
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$A5v3wL3mXmXmXmXmXmXmOe9I2mXmXmXmXmXmXmXmXmXmXmXmXmXmXm', 'admin'); 
-- The above is a dummy hash. PHP script should handle real registration.
