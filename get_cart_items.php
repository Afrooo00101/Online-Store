<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$conn = getConnection();

$cart_items = [];

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    
    if (!empty($product_ids)) {
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        
        $sql = "SELECT p.id, p.name, p.price, p.discount_price, 
                       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as image
                FROM products p 
                WHERE p.id IN ($placeholders)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $product_id = $row['id'];
            $quantity = $_SESSION['cart'][$product_id];
            $price = $row['discount_price'] > 0 ? $row['discount_price'] : $row['price'];
            
            $cart_items[] = [
                'id' => $product_id,
                'name' => $row['name'],
                'price' => floatval($price),
                'image' => $row['image'] ?: 'default.jpg',
                'quantity' => $quantity
            ];
        }
        
        $stmt->close();
    }
}

closeConnection($conn);

echo json_encode($cart_items);
?>