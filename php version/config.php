<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'mono20806');
define('DB_NAME', 'online_store');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to UTF-8
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        // Log error and show user-friendly message
        error_log("Database error: " . $e->getMessage());
        die("We're experiencing technical difficulties. Please try again later.");
    }
}

// Close connection
function closeConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Function to get cart count
function getCartCount() {
    return array_sum($_SESSION['cart'] ?? []);
}

// Function to calculate cart total
function calculateCartTotal() {
    if (empty($_SESSION['cart'])) {
        return ['subtotal' => 0, 'shipping' => 0, 'tax' => 0, 'total' => 0];
    }
    
    require_once 'config.php';
    $conn = getConnection();
    
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    
    $sql = "SELECT p.id, p.price, p.discount_price
            FROM products p 
            WHERE p.id IN ($placeholders)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subtotal = 0;
    while ($row = $result->fetch_assoc()) {
        $product_id = $row['id'];
        $price = $row['discount_price'] > 0 ? $row['discount_price'] : $row['price'];
        $quantity = $_SESSION['cart'][$product_id];
        $subtotal += $price * $quantity;
    }
    
    $stmt->close();
    closeConnection($conn);
    
    $shipping = $subtotal > 1000 ? 0 : 50;
    $tax = $subtotal * 0.14;
    $total = $subtotal + $shipping + $tax;
    
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total
    ];
}
// In config.php, add these functions:

// Function to save cart for later
function saveCartForLater($user_id, $cart_data) {
    $conn = getConnection();
    
    $cart_json = json_encode($cart_data);
    $sql = "INSERT INTO saved_carts (user_id, cart_data, saved_at) VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE cart_data = ?, saved_at = NOW()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $cart_json, $cart_json);
    $result = $stmt->execute();
    
    $stmt->close();
    closeConnection($conn);
    return $result;
}

// Function to get saved cart
function getSavedCart($user_id) {
    $conn = getConnection();
    
    $sql = "SELECT cart_data FROM saved_carts WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cart_data = null;
    if ($row = $result->fetch_assoc()) {
        $cart_data = json_decode($row['cart_data'], true);
    }
    
    $stmt->close();
    closeConnection($conn);
    return $cart_data;
}

// Create saved_carts table if not exists
function createSavedCartsTable() {
    $conn = getConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS saved_carts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE,
        cart_data JSON,
        saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $conn->query($sql);
    closeConnection($conn);
}

// Call this function once to create the table
// createSavedCartsTable();
?>