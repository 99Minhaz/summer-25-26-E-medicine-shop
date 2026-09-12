CREATE DATABASE IF NOT EXISTS E_medicine_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE E_medicine_shop;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer', 'vendor', 'delivery') NOT NULL DEFAULT 'customer',
    profile_picture VARCHAR(255) NULL,
    address TEXT NULL,
    phone VARCHAR(30) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category_type ENUM('liquid', 'solid') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category_id INT NOT NULL,
    vendor_name VARCHAR(120) NOT NULL,
    vendor_id INT NULL,
    price DECIMAL(10,2) NOT NULL,
    availability INT NOT NULL DEFAULT 0,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_medicines_category FOREIGN KEY (category_id) REFERENCES categories(id),
    CONSTRAINT fk_medicines_vendor FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, medicine_id),
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'delivered') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50) NOT NULL,
    delivery_person_id INT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_orders_delivery FOREIGN KEY (delivery_person_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(120) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector VARCHAR(32) NOT NULL UNIQUE,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO categories (name, category_type)
SELECT 'Aspirin genre', 'solid'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Aspirin genre');

INSERT INTO categories (name, category_type)
SELECT 'Paracetamol genre', 'solid'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Paracetamol genre');

INSERT INTO categories (name, category_type)
SELECT 'Cough syrup genre', 'liquid'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Cough syrup genre');

INSERT INTO medicines (name, category_id, vendor_name, price, availability, description)
SELECT 'Napa 500mg', c.id, 'Beximco Pharma', 10.00, 120, 'Paracetamol tablet for fever and mild pain.'
FROM categories c
WHERE c.name = 'Paracetamol genre'
AND NOT EXISTS (SELECT 1 FROM medicines WHERE name = 'Napa 500mg');

INSERT INTO medicines (name, category_id, vendor_name, price, availability, description)
SELECT 'Aspirin Protect', c.id, 'Bayer', 25.00, 70, 'Solid aspirin tablet.'
FROM categories c
WHERE c.name = 'Aspirin genre'
AND NOT EXISTS (SELECT 1 FROM medicines WHERE name = 'Aspirin Protect');

INSERT INTO medicines (name, category_id, vendor_name, price, availability, description)
SELECT 'Cough Relief Syrup', c.id, 'Square Pharma', 90.00, 35, 'Liquid cough syrup.'
FROM categories c
WHERE c.name = 'Cough syrup genre'
AND NOT EXISTS (SELECT 1 FROM medicines WHERE name = 'Cough Relief Syrup');

INSERT INTO users (name, email, password_hash, role, address, phone)
SELECT 'Admin User', 'admin@example.com', '$2y$12$NFez2D6Qd9d02dyzVgNbbetdiB2Tu0MpfVRfkLix/6yRSXuXnMVLq', 'admin', 'Dhaka', '01700000000'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@example.com');

INSERT INTO users (name, email, password_hash, role, address, phone)
SELECT 'Default Vendor', 'vendor@example.com', '$2y$12$NFez2D6Qd9d02dyzVgNbbetdiB2Tu0MpfVRfkLix/6yRSXuXnMVLq', 'vendor', 'Dhaka', '01700000001'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'vendor@example.com');

INSERT INTO users (name, email, password_hash, role, address, phone)
SELECT 'Default Delivery', 'delivery@example.com', '$2y$12$NFez2D6Qd9d02dyzVgNbbetdiB2Tu0MpfVRfkLix/6yRSXuXnMVLq', 'delivery', 'Dhaka', '01700000002'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'delivery@example.com');

-- Stored procedures (MySQL Procedural requirement)

DROP PROCEDURE IF EXISTS sp_dashboard_stats;
DELIMITER $$
CREATE PROCEDURE sp_dashboard_stats()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM medicines) AS medicines_count,
        (SELECT COUNT(*) FROM categories) AS categories_count,
        (SELECT COUNT(*) FROM users WHERE role = 'customer') AS customers_count,
        (SELECT COUNT(*) FROM orders WHERE status = 'pending') AS pending_orders_count;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_category_create;
DELIMITER $$
CREATE PROCEDURE sp_category_create(IN p_name VARCHAR(120), IN p_type VARCHAR(10))
BEGIN
    INSERT INTO categories (name, category_type) VALUES (p_name, p_type);
END$$
DELIMITER ;
