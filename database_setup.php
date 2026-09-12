<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "E_medicine_shop";

// Create connection without database first
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully<br>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully<br>";
} else {
    die("Error creating database: " . mysqli_error($conn));
}

// Select database
mysqli_select_db($conn, $dbname);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','customer','vendor','delivery') NOT NULL DEFAULT 'customer',
    profile_picture VARCHAR(255) NULL,
    address TEXT NULL,
    phone VARCHAR(30) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . mysqli_error($conn) . "<br>";
}

// Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category_type ENUM('liquid','solid') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Categories table created successfully<br>";
} else {
    echo "Error creating categories table: " . mysqli_error($conn) . "<br>";
}

// Create medicines table
$sql = "CREATE TABLE IF NOT EXISTS medicines (
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
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "Medicines table created successfully<br>";
} else {
    echo "Error creating medicines table: " . mysqli_error($conn) . "<br>";
}

// Create cart table
$sql = "CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, medicine_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "Cart table created successfully<br>";
} else {
    echo "Error creating cart table: " . mysqli_error($conn) . "<br>";
}

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    status ENUM('pending','accepted','rejected','delivered') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50) NOT NULL,
    delivery_person_id INT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_person_id) REFERENCES users(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "Orders table created successfully<br>";
} else {
    echo "Error creating orders table: " . mysqli_error($conn) . "<br>";
}

// Create order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "Order items table created successfully<br>";
} else {
    echo "Error creating order_items table: " . mysqli_error($conn) . "<br>";
}

// Create payments table
$sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(120) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Payments table created successfully<br>";
} else {
    echo "Error creating payments table: " . mysqli_error($conn) . "<br>";
}

// Create remember_tokens table for Remember Me login
$sql = "CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector VARCHAR(32) NOT NULL UNIQUE,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Remember tokens table created successfully<br>";
} else {
    echo "Error creating remember_tokens table: " . mysqli_error($conn) . "<br>";
}

// Insert default admin account if it does not exist
$admin_password = password_hash("admin12345", PASSWORD_DEFAULT);
$admin_password = mysqli_real_escape_string($conn, $admin_password);

$sql = "INSERT INTO users (name, email, password_hash, role, address, phone)
        SELECT 'Admin User', 'admin@example.com', '$admin_password', 'admin', 'Dhaka', '01700000000'
        WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@example.com')";

if (mysqli_query($conn, $sql)) {
    echo "Default admin checked/inserted successfully<br>";
} else {
    echo "Error inserting default admin: " . mysqli_error($conn) . "<br>";
}

// Insert default vendor account if it does not exist
$sql = "INSERT INTO users (name, email, password_hash, role, address, phone)
        SELECT 'Default Vendor', 'vendor@example.com', '$admin_password', 'vendor', 'Dhaka', '01700000001'
        WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'vendor@example.com')";

if (mysqli_query($conn, $sql)) {
    echo "Default vendor checked/inserted successfully<br>";
} else {
    echo "Error inserting default vendor: " . mysqli_error($conn) . "<br>";
}

// Insert default delivery account if it does not exist
$sql = "INSERT INTO users (name, email, password_hash, role, address, phone)
        SELECT 'Default Delivery', 'delivery@example.com', '$admin_password', 'delivery', 'Dhaka', '01700000002'
        WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'delivery@example.com')";

if (mysqli_query($conn, $sql)) {
    echo "Default delivery checked/inserted successfully<br>";
} else {
    echo "Error inserting default delivery: " . mysqli_error($conn) . "<br>";
}

// Insert default categories
$sql = "INSERT INTO categories (name, category_type)
        SELECT 'Aspirin genre', 'solid'
        WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Aspirin genre')";
mysqli_query($conn, $sql);

$sql = "INSERT INTO categories (name, category_type)
        SELECT 'Paracetamol genre', 'solid'
        WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Paracetamol genre')";
mysqli_query($conn, $sql);

$sql = "INSERT INTO categories (name, category_type)
        SELECT 'Cough syrup genre', 'liquid'
        WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Cough syrup genre')";
mysqli_query($conn, $sql);

echo "Default categories checked/inserted successfully<br>";

// Insert default medicines
$sql = "INSERT INTO medicines (name, category_id, vendor_name, price, availability, description)
        SELECT 'Napa 500mg', categories.id, 'Beximco Pharma', 10.00, 120, 'Paracetamol tablet for fever and mild pain.'
        FROM categories
        WHERE categories.name = 'Paracetamol genre'
        AND NOT EXISTS (SELECT 1 FROM medicines WHERE name = 'Napa 500mg')";
mysqli_query($conn, $sql);

$sql = "INSERT INTO medicines (name, category_id, vendor_name, price, availability, description)
        SELECT 'Aspirin Protect', categories.id, 'Bayer', 25.00, 70, 'Solid aspirin tablet.'
        FROM categories
        WHERE categories.name = 'Aspirin genre'
        AND NOT EXISTS (SELECT 1 FROM medicines WHERE name = 'Aspirin Protect')";
mysqli_query($conn, $sql);

$sql = "INSERT INTO medicines (name, category_id, vendor_name, price, availability, description)
        SELECT 'Cough Relief Syrup', categories.id, 'Square Pharma', 90.00, 35, 'Liquid cough syrup.'
        FROM categories
        WHERE categories.name = 'Cough syrup genre'
        AND NOT EXISTS (SELECT 1 FROM medicines WHERE name = 'Cough Relief Syrup')";
mysqli_query($conn, $sql);

echo "Default medicines checked/inserted successfully<br>";

// Create stored procedures (MySQL Procedural requirement)
mysqli_query($conn, "DROP PROCEDURE IF EXISTS sp_dashboard_stats");

$sql = "CREATE PROCEDURE sp_dashboard_stats()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM medicines) AS medicines_count,
        (SELECT COUNT(*) FROM categories) AS categories_count,
        (SELECT COUNT(*) FROM users WHERE role = 'customer') AS customers_count,
        (SELECT COUNT(*) FROM orders WHERE status = 'pending') AS pending_orders_count;
END";

if (mysqli_query($conn, $sql)) {
    echo "sp_dashboard_stats procedure created successfully<br>";
} else {
    echo "Error creating sp_dashboard_stats: " . mysqli_error($conn) . "<br>";
}

mysqli_query($conn, "DROP PROCEDURE IF EXISTS sp_category_create");

$sql = "CREATE PROCEDURE sp_category_create(IN p_name VARCHAR(120), IN p_type VARCHAR(10))
BEGIN
    INSERT INTO categories (name, category_type) VALUES (p_name, p_type);
END";

if (mysqli_query($conn, $sql)) {
    echo "sp_category_create procedure created successfully<br>";
} else {
    echo "Error creating sp_category_create: " . mysqli_error($conn) . "<br>";
}

echo "Database setup finished successfully";

mysqli_close($conn);

?>
