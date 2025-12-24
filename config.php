<?php
// config.php - FIXED TO MATCH YOUR ACTUAL DATABASE
define('DB_HOST', 'localhost');
define('DB_NAME', 'online_store');
define('DB_USER', 'root');
define('DB_PASS', 'mono20806');

// Get database connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        // Try to create database if it doesn't exist
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Create database
        $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $conn->select_db(DB_NAME);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Check and create necessary tables
function checkAndCreateTables($conn) {
    // Check if products table exists with correct structure
    $result = $conn->query("SHOW TABLES LIKE 'products'");
    if ($result->num_rows == 0) {
        // Create products table based on your structure
        $sql = "CREATE TABLE products (
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
        )";
        
        if (!$conn->query($sql)) {
            error_log("Error creating products table: " . $conn->error);
        }
    }
    
    // Check for other tables and create if missing
    $tables = [
        'product_images' => "CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            is_main TINYINT(1) DEFAULT 0,
            image_path VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        
        'categories' => "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            parent_id INT DEFAULT NULL
        )",
        
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(150) NOT NULL,
            email VARCHAR(150) UNIQUE,
            phone VARCHAR(20) UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    foreach ($tables as $table_name => $create_sql) {
        $result = $conn->query("SHOW TABLES LIKE '$table_name'");
        if ($result->num_rows == 0) {
            if (!$conn->query($create_sql)) {
                error_log("Error creating $table_name table: " . $conn->error);
            }
        }
    }
    
    // Insert sample data if needed
    insertSampleData($conn);
}

function insertSampleData($conn) {
    // Insert sample categories
    $categories = ['2025 Season', '2024 Season', 'Iconic Jerseys', 'National Teams', 'Special Editions'];
    
    foreach ($categories as $category) {
        $check_sql = "SELECT id FROM categories WHERE name = ?";
        $check_stmt = $conn->prepare($check_sql);
        if ($check_stmt) {
            $check_stmt->bind_param("s", $category);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows == 0) {
                $insert_sql = "INSERT INTO categories (name) VALUES (?)";
                $insert_stmt = $conn->prepare($insert_sql);
                if ($insert_stmt) {
                    $insert_stmt->bind_param("s", $category);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
            }
            $check_stmt->close();
        }
    }
    
    // Insert sample products
    $sampleProducts = [
        [
            'name' => 'Barcelona 2025/26 Spotify Home Kit',
            'price' => 2000.00,
            'discount_price' => 1800.00,
            'stock' => 50,
            'season' => '2025',
            'team' => 'Barcelona',
            'category' => '2025 Season',
            'featured' => 1
        ],
        [
            'name' => 'Liverpool 2025/26 Home Kit',
            'price' => 2400.00,
            'discount_price' => 1650.00,
            'stock' => 30,
            'season' => '2025',
            'team' => 'Liverpool',
            'category' => '2025 Season',
            'featured' => 1
        ]
    ];
    
    foreach ($sampleProducts as $product) {
        $check_sql = "SELECT id FROM products WHERE name = ?";
        $check_stmt = $conn->prepare($check_sql);
        if ($check_stmt) {
            $check_stmt->bind_param("s", $product['name']);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows == 0) {
                $sql = "INSERT INTO products (name, price, discount_price, stock, season, team, category, featured) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("sddissii", 
                        $product['name'],
                        $product['price'],
                        $product['discount_price'],
                        $product['stock'],
                        $product['season'],
                        $product['team'],
                        $product['category'],
                        $product['featured']
                    );
                    
                    if ($stmt->execute()) {
                        $product_id = $stmt->insert_id;
                        
                        // Insert sample image
                        $image_sql = "INSERT INTO product_images (product_id, filename, is_main) 
                                     VALUES (?, 'sample.jpg', 1)";
                        $image_stmt = $conn->prepare($image_sql);
                        if ($image_stmt) {
                            $image_stmt->bind_param("i", $product_id);
                            $image_stmt->execute();
                            $image_stmt->close();
                        }
                    }
                    $stmt->close();
                }
            }
            $check_stmt->close();
        }
    }
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get database connection
try {
    $conn = getConnection();
    checkAndCreateTables($conn);
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    $conn = null;
}

// Check if database is connected
function isDatabaseConnected() {
    global $conn;
    return $conn && $conn->ping();
}
?>