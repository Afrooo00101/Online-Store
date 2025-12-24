<?php
// init_database.php - Run this once to initialize your database
require_once 'db_connect.php';

if (isset($conn)) {
    echo "Connected to database successfully!<br>";
    
    // Check if tables exist
    $tables = ['products', 'product_images', 'categories', 'users'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✓ Table '$table' exists<br>";
        } else {
            echo "✗ Table '$table' does not exist<br>";
        }
    }
    
    // Count products
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Total products in database: " . $row['count'] . "<br>";
    }
    
    // Test query
    echo "<h3>Sample Products:</h3>";
    $result = $conn->query("SELECT id, name, price FROM products LIMIT 5");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "ID: {$row['id']} - {$row['name']} - LE {$row['price']}<br>";
        }
    } else {
        echo "No products found. Adding sample products...<br>";
        
        // Add sample products
        include 'config.php';
        if (isset($conn)) {
            insertSampleData($conn);
            echo "Sample products added successfully!<br>";
        }
    }
    
    $conn->close();
} else {
    echo "Failed to connect to database!";
}
?>