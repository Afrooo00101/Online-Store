<?php
session_start();
require_once 'config.php';

$conn = getConnection();

$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
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
        $item_total = $price * $quantity;
        
        $cart_items[] = [
            'id' => $product_id,
            'name' => $row['name'],
            'price' => $price,
            'image' => $row['image'] ?: 'https://via.placeholder.com/150',
            'quantity' => $quantity,
            'item_total' => $item_total
        ];
        
        $total += $item_total;
    }
    
    $stmt->close();
}

closeConnection($conn);
?>

<?php if(empty($cart_items)): ?>
    <div class="cart-modal-empty">
        <i class="fas fa-shopping-cart fa-2x"></i>
        <p>Your cart is empty</p>
    </div>
<?php else: ?>
    <?php foreach($cart_items as $item): ?>
    <div class="cart-modal-item" data-id="<?php echo $item['id']; ?>">
        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
        <div class="cart-modal-item-details">
            <div class="cart-modal-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
            <div class="cart-modal-item-price">LE <?php echo number_format($item['price'], 2); ?></div>
            <div class="cart-modal-item-quantity">
                <button class="modal-minus-btn" data-id="<?php echo $item['id']; ?>">-</button>
                <input type="number" class="modal-quantity-input" data-id="<?php echo $item['id']; ?>" 
                       value="<?php echo $item['quantity']; ?>" min="1" max="99">
                <button class="modal-plus-btn" data-id="<?php echo $item['id']; ?>">+</button>
                <button class="remove-modal-item" data-id="<?php echo $item['id']; ?>">×</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <script>
        // Update total in modal footer
        document.getElementById('modalCartTotal').textContent = 'LE <?php echo number_format($total, 2); ?>';
    </script>
<?php endif; ?>