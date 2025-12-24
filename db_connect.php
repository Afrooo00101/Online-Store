<?php
// db_connect.php - UPDATED TO MATCH YOUR DATABASE
$host = 'localhost';
$dbname = 'online_store';
$username = 'root';
$password = 'mono20806';

try {
    // PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // MySQLi connection (for legacy code)
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        error_log("MySQLi connection failed: " . $conn->connect_error);
    } else {
        // Set charset
        $conn->set_charset("utf8mb4");
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
function createTablesIfNotExist($conn) {
    // Your table creation SQL here (simplified version)
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(150) NOT NULL,
            email VARCHAR(150) UNIQUE,
            phone VARCHAR(20) UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            parent_id INT DEFAULT NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            category_id INT,
            price DECIMAL(10,2) NOT NULL,
            discount_price DECIMAL(10,2),
            stock INT DEFAULT 0,
            sku VARCHAR(100) UNIQUE,
            attributes JSON,
            video VARCHAR(255) DEFAULT NULL,
            season VARCHAR(20),
            team VARCHAR(100),
            era VARCHAR(100),
            category VARCHAR(100),
            featured TINYINT DEFAULT 0,
            stock_status ENUM('in_stock','out_of_stock','preorder') DEFAULT 'in_stock',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            is_main TINYINT(1) DEFAULT 0,
            image_path VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
        )"
    ];
    
    foreach ($queries as $query) {
        if (!$conn->query($query)) {
            error_log("Error creating table: " . $conn->error);
        }
    }
}

// Call the function to create tables
if (isset($conn) && !$conn->connect_error) {
    createTablesIfNotExist($conn);
}
?>