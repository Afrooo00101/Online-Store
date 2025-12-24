<?php
// get_cart_modal.php
session_start();
require_once 'config.php';

$conn = getConnection();
$cartItems = $_SESSION['cart'] ?? [];
$total = 0;

ob_start();
?>

<?php if (empty($cartItems)): ?>
    <div class="cart-modal-empty">
        <i class="fas fa-shopping-cart fa-3x"></i>
        <p>Your cart is empty</p>
    </div>
<?php else: ?>
    <div id="cartItemsContainer">
        <?php 
        foreach ($cartItems as $item):
            $itemTotal = $item['price'] * $item['quantity'];
            $total += $itemTotal;
        ?>
        <div class="cart-modal-item" data-id="<?php echo $item['id']; ?>">
            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                 onerror="this.src='https://via.placeholder.com/60?text=Image'">
            <div class="cart-modal-item-details">
                <div class="cart-modal-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                <?php if (!empty($item['team'])): ?>
                <div class="cart-modal-item-team"><?php echo htmlspecialchars($item['team']); ?></div>
                <?php endif; ?>
                <div class="cart-modal-item-price">LE <?php echo number_format($item['price'], 2); ?></div>
                <div class="cart-modal-item-quantity">
                    <button class="modal-minus-btn" data-id="<?php echo $item['id']; ?>">-</button>
                    <input type="number" class="modal-quantity-input" data-id="<?php echo $item['id']; ?>" 
                           value="<?php echo $item['quantity']; ?>" min="1" max="99">
                    <button class="modal-plus-btn" data-id="<?php echo $item['id']; ?>">+</button>
                </div>
            </div>
            <button class="remove-modal-item" data-id="<?php echo $item['id']; ?>">×</button>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="cart-modal-footer">
        <div class="cart-total">
            <span>Total:</span>
            <span id="modalCartTotal">LE <?php echo number_format($total, 2); ?></span>
        </div>
        <button class="view-cart-btn" onclick="window.location.href='cart.php'">
            View Full Cart
        </button>
    </div>
<?php endif; ?>

<?php
$html = ob_get_clean();
echo $html;
?>