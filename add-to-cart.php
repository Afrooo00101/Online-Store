<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? 0;
    $productName = $_POST['product_name'] ?? '';
    $productPrice = $_POST['product_price'] ?? 0;
    $productImage = $_POST['product_image'] ?? '';
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if item already exists
    $itemExists = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productId) {
            $item['quantity']++;
            $itemExists = true;
            break;
        }
    }
    
    // Add new item if not exists
    if (!$itemExists) {
        $_SESSION['cart'][] = [
            'id' => $productId,
            'name' => $productName,
            'price' => floatval($productPrice),
            'quantity' => 1,
            'image' => $productImage ?: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
        ];
    }
    
    $_SESSION['notification'] = "{$productName} added to cart!";
    
    // Return cart count for AJAX updates
    $cartCount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'cart_count' => $cartCount,
        'message' => "{$productName} added to cart!"
    ]);
    exit();
}

// Return error if not POST
echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
]);