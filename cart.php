<?php
session_start();
require_once 'config.php';

// Initialize session arrays if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['saved_items'])) {
    $_SESSION['saved_items'] = [];
}
if (!isset($_SESSION['promo_code'])) {
    $_SESSION['promo_code'] = null;
}
if (!isset($_SESSION['shipping_method'])) {
    $_SESSION['shipping_method'] = ['id' => 1, 'name' => 'Standard Shipping', 'cost' => 50.00];
}
if (!isset($_SESSION['shipping_address'])) {
    $_SESSION['shipping_address'] = [
        'full_name' => '',
        'address' => '',
        'city' => '',
        'postal_code' => '',
        'phone' => '',
        'email' => ''
    ];
}

// Define valid promo codes - INCLUDES MOMO50
$validPromoCodes = [
    'WELCOME10' => ['discount' => 10, 'type' => 'percentage', 'min_order' => 0],
    'SAVE20' => ['discount' => 20, 'type' => 'fixed', 'min_order' => 100],
    'FREESHIP' => ['discount' => 50, 'type' => 'shipping', 'min_order' => 0],
    'SUMMER25' => ['discount' => 25, 'type' => 'percentage', 'min_order' => 200],
    'MOMO50' => ['discount' => 50, 'type' => 'percentage', 'min_order' => 0] // ADDED MOMO50
];

// Define shipping methods
$shippingMethods = [
    ['id' => 1, 'name' => 'Standard Shipping', 'cost' => 50.00, 'estimated_days' => '5-7 days'],
    ['id' => 2, 'name' => 'Express Shipping', 'cost' => 100.00, 'estimated_days' => '2-3 days'],
    ['id' => 3, 'name' => 'Next Day Delivery', 'cost' => 150.00, 'estimated_days' => '1 day'],
    ['id' => 4, 'name' => 'Store Pickup', 'cost' => 0.00, 'estimated_days' => 'Same day']
];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(handleAjaxRequest());
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax'])) {
    handleFormRequest();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

function handleAjaxRequest() {
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];
    
    switch ($action) {
        case 'add':
            $response = addToCartAjax();
            break;
        case 'update':
            $response = updateCartItemAjax();
            break;
        case 'remove':
            $response = removeFromCartAjax();
            break;
        case 'save_for_later':
            $response = saveForLaterAjax();
            break;
        case 'move_to_cart':
            $response = moveToCartAjax();
            break;
        case 'clear':
            $response = clearCartAjax();
            break;
        case 'apply_promo':
            $response = applyPromoCodeAjax();
            break;
        case 'remove_promo':
            $response = removePromoCodeAjax();
            break;
        case 'update_shipping':
            $response = updateShippingMethodAjax();
            break;
        case 'save_address':
            $response = saveShippingAddressAjax();
            break;
        case 'get_cart_summary':
            $response = getCartSummaryAjax();
            break;
    }
    
    return $response;
}

function handleFormRequest() {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            addToCartForm();
            break;
        case 'update':
            updateCartItemForm();
            break;
        case 'remove':
            removeFromCartForm();
            break;
        case 'clear':
            clearCartForm();
            break;
        case 'save_for_later':
            saveForLaterForm();
            break;
        case 'move_to_cart':
            moveToCartForm();
            break;
        case 'apply_promo':
            applyPromoCodeForm();
            break;
        case 'remove_promo':
            removePromoCodeForm();
            break;
        case 'update_shipping':
            updateShippingMethodForm();
            break;
        case 'save_address':
            saveShippingAddressForm();
            break;
    }
}

// AJAX Functions
function addToCartAjax() {
    $id = $_POST['product_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $image = $_POST['image'] ?? '';
    
    // Check if item already exists
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) {
            $item['quantity']++;
            $message = "Updated quantity for $name";
            break;
        }
    }
    
    if (!isset($message)) {
        // Add new item
        $_SESSION['cart'][] = [
            'id' => $id,
            'name' => $name,
            'price' => floatval($price),
            'quantity' => 1,
            'image' => $image ?: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
            'timestamp' => time()
        ];
        $message = "$name added to cart";
    }
    
    return ['success' => true, 'message' => $message, 'cart_count' => count($_SESSION['cart']), 'cart_items' => $_SESSION['cart']];
}

function updateCartItemAjax() {
    $id = $_POST['product_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    $message = 'Item updated';
    
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) {
            $oldQuantity = $item['quantity'];
            $item['quantity'] = max(1, intval($quantity));
            $message = "Quantity updated from $oldQuantity to {$item['quantity']}";
            break;
        }
    }
    
    $summary = calculateCartSummary();
    return ['success' => true, 'message' => $message, 'summary' => $summary];
}

function removeFromCartAjax() {
    $id = $_POST['product_id'] ?? 0;
    $itemName = '';
    
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $id) {
            $itemName = $item['name'];
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            break;
        }
    }
    
    $summary = calculateCartSummary();
    return ['success' => true, 'message' => "$itemName removed from cart", 
            'cart_count' => count($_SESSION['cart']), 'summary' => $summary];
}

function saveForLaterAjax() {
    $id = $_POST['product_id'] ?? 0;
    $itemName = '';
    
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $id) {
            $itemName = $item['name'];
            $item['saved'] = true;
            $_SESSION['saved_items'][] = $item;
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            break;
        }
    }
    
    $summary = calculateCartSummary();
    return ['success' => true, 'message' => "$itemName saved for later", 'summary' => $summary];
}

function moveToCartAjax() {
    $id = $_POST['product_id'] ?? 0;
    $itemName = '';
    
    foreach ($_SESSION['saved_items'] as $key => $item) {
        if ($item['id'] == $id) {
            $itemName = $item['name'];
            unset($item['saved']);
            $_SESSION['cart'][] = $item;
            unset($_SESSION['saved_items'][$key]);
            $_SESSION['saved_items'] = array_values($_SESSION['saved_items']);
            break;
        }
    }
    
    $summary = calculateCartSummary();
    return ['success' => true, 'message' => "$itemName moved to cart", 'summary' => $summary];
}

function clearCartAjax() {
    $_SESSION['cart'] = [];
    $summary = calculateCartSummary();
    return ['success' => true, 'message' => 'Cart cleared successfully', 'summary' => $summary];
}

function applyPromoCodeAjax() {
    global $validPromoCodes;
    
    $code = strtoupper(trim($_POST['promo_code'] ?? ''));
    
    if (empty($code) || !isset($validPromoCodes[$code])) {
        return ['success' => false, 'message' => 'Invalid promo code'];
    }
    
    $promo = $validPromoCodes[$code];
    
    // Check minimum order amount
    $subtotal = calculateSubtotal();
    if ($subtotal < $promo['min_order']) {
        $minOrder = number_format($promo['min_order'], 2);
        return ['success' => false, 'message' => "Minimum order amount of LE $minOrder required"];
    }
    
    // Save promo code to session
    $_SESSION['promo_code'] = [
        'code' => $code,
        'discount' => $promo['discount'],
        'type' => $promo['type']
    ];
    
    $summary = calculateCartSummary();
    return ['success' => true, 'message' => 'Promo code applied successfully', 'summary' => $summary];
}

function removePromoCodeAjax() {
    $_SESSION['promo_code'] = null;
    $summary = calculateCartSummary();
    return ['success' => true, 'message' => 'Promo code removed', 'summary' => $summary];
}

function updateShippingMethodAjax() {
    global $shippingMethods;
    
    $methodId = intval($_POST['shipping_method_id'] ?? 1);
    $selectedMethod = null;
    
    foreach ($shippingMethods as $method) {
        if ($method['id'] == $methodId) {
            $selectedMethod = $method;
            break;
        }
    }
    
    if (!$selectedMethod) {
        return ['success' => false, 'message' => 'Invalid shipping method'];
    }
    
    $_SESSION['shipping_method'] = $selectedMethod;
    $summary = calculateCartSummary();
    return ['success' => true, 'message' => 'Shipping method updated', 'summary' => $summary];
}

function saveShippingAddressAjax() {
    $_SESSION['shipping_address'] = [
        'full_name' => $_POST['full_name'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'postal_code' => $_POST['postal_code'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? ''
    ];
    
    return ['success' => true, 'message' => 'Shipping address saved'];
}

function getCartSummaryAjax() {
    $summary = calculateCartSummary();
    return [
        'success' => true,
        'data' => $summary,
        'cart_items' => $_SESSION['cart'] ?? [],
        'saved_items' => $_SESSION['saved_items'] ?? [],
        'cart_count' => count($_SESSION['cart'] ?? [])
    ];
}

// Form Functions (for non-AJAX requests)
function addToCartForm() {
    $result = addToCartAjax();
    $_SESSION['notification'] = $result['message'];
}

function updateCartItemForm() {
    $result = updateCartItemAjax();
    $_SESSION['notification'] = $result['message'];
}

function removeFromCartForm() {
    $result = removeFromCartAjax();
    $_SESSION['notification'] = $result['message'];
}

function clearCartForm() {
    $result = clearCartAjax();
    $_SESSION['notification'] = $result['message'];
}

function saveForLaterForm() {
    $result = saveForLaterAjax();
    $_SESSION['notification'] = $result['message'];
}

function moveToCartForm() {
    $result = moveToCartAjax();
    $_SESSION['notification'] = $result['message'];
}

function applyPromoCodeForm() {
    $result = applyPromoCodeAjax();
    $_SESSION['notification'] = $result['message'];
}

function removePromoCodeForm() {
    $result = removePromoCodeAjax();
    $_SESSION['notification'] = $result['message'];
}

function updateShippingMethodForm() {
    $result = updateShippingMethodAjax();
    $_SESSION['notification'] = $result['message'];
}

function saveShippingAddressForm() {
    $result = saveShippingAddressAjax();
    $_SESSION['notification'] = $result['message'];
}

// Helper Functions
function calculateSubtotal() {
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    return $subtotal;
}

function calculateCartSummary() {
    $subtotal = calculateSubtotal();
    $shipping = $_SESSION['shipping_method']['cost'] ?? 50.00;
    $tax = $subtotal * 0.14;
    $discount = 0;
    
    // Apply promo code discount
    if (isset($_SESSION['promo_code'])) {
        $promo = $_SESSION['promo_code'];
        if ($promo['type'] === 'percentage') {
            $discount = ($subtotal * $promo['discount']) / 100;
        } elseif ($promo['type'] === 'fixed') {
            $discount = min($promo['discount'], $subtotal);
        } elseif ($promo['type'] === 'shipping') {
            $shipping = max(0, $shipping - $promo['discount']);
        }
    }
    
    // REMOVED FREE SHIPPING CONDITION - Shipping always applies
    // if ($subtotal > 1000) {
    //     $shipping = 0;
    // }
    
    $total = $subtotal + $shipping + $tax - $discount;
    
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'discount' => $discount,
        'total' => $total,
        'item_count' => array_sum(array_column($_SESSION['cart'] ?? [], 'quantity')),
        'total_items' => count($_SESSION['cart'] ?? [])
    ];
}

// Calculate totals for display
$cartSummary = calculateCartSummary();
$itemCount = $cartSummary['item_count'];
$totalItems = $cartSummary['total_items'];
$subtotal = $cartSummary['subtotal'];
$shipping = $cartSummary['shipping'];
$tax = $cartSummary['tax'];
$discount = $cartSummary['discount'];
$total = $cartSummary['total'];

// Get saved items
$savedItems = $_SESSION['saved_items'] ?? [];
$promoCode = $_SESSION['promo_code'] ?? null;
$shippingMethod = $_SESSION['shipping_method'] ?? $shippingMethods[0];
$shippingAddress = $_SESSION['shipping_address'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Your Store</title>
    <link rel="stylesheet" href="cart.css">
    <script src="cart.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="cart-container">
        <header class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h1>
            <p class="item-count"><?php echo $itemCount; ?> item<?php echo $itemCount != 1 ? 's' : ''; ?> in your cart</p>
        </header>

        <div class="cart-content">
            <div class="cart-items-section">
                <div class="section-header">
                    <h2>Selected Products</h2>
                    <form method="POST" class="clear-cart-form">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="clear-cart-btn">
                            <i class="fas fa-trash-alt"></i> Clear Cart
                        </button>
                    </form>
                </div>
                
                <?php if ($totalItems > 0): ?>
                <div class="cart-items" id="cartItems">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                        <div class="item-image">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="item-details">
                            <h3 class="item-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="item-price">LE <?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <div class="item-quantity">
                            <form method="POST" class="quantity-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>" 
                                        class="quantity-btn minus-btn">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="quantity-input" name="quantity" 
                                       value="<?php echo $item['quantity']; ?>" min="1" max="99" 
                                       onchange="this.form.submit()">
                                <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" 
                                        class="quantity-btn plus-btn">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </form>
                        </div>
                        <div class="item-total">
                            LE <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                        <div class="item-actions">
                            <form method="POST" class="save-for-later-form">
                                <input type="hidden" name="action" value="save_for_later">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="save-for-later-btn" title="Save for Later">
                                    <i class="fas fa-bookmark"></i>
                                </button>
                            </form>
                            <form method="POST" class="remove-item-form">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="remove-item-btn">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="cart-empty" id="emptyCartMessage">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                    <h3>Your cart is empty</h3>
                    <p>Add some products to your cart to see them here</p>
                    <a href="index.php" class="continue-shopping-btn">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
                <?php endif; ?>

                <!-- Saved Items Section -->
                <?php if (!empty($savedItems)): ?>
                <div class="saved-items-section">
                    <h3><i class="fas fa-bookmark"></i> Saved for Later</h3>
                    <div class="saved-items" id="savedItems">
                        <?php foreach ($savedItems as $item): ?>
                        <div class="saved-item" data-id="<?php echo $item['id']; ?>">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="saved-item-details">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p>LE <?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <div class="saved-item-actions">
                                <form method="POST">
                                    <input type="hidden" name="action" value="move_to_cart">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="move-to-cart-btn">Add to Cart</button>
                                </form>
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="remove-saved-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="cart-summary-section">
                <!-- Order Summary -->
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="subtotal">LE <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <!-- Promo Code Section -->
                        <div class="promo-code-section">
                            <form method="POST" class="promo-form">
                                <div class="promo-input-group">
                                    <input type="text" name="promo_code" placeholder="Enter promo code" 
                                           value="<?php echo $promoCode ? $promoCode['code'] : ''; ?>">
                                    <input type="hidden" name="action" value="apply_promo">
                                    <button type="submit">Apply</button>
                                </div>
                            </form>
                            <?php if ($promoCode): ?>
                            <div class="promo-applied" id="promoApplied">
                                <span>Discount (<?php echo $promoCode['code']; ?>)</span>
                                <span>- LE <?php echo number_format($discount, 2); ?></span>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="remove_promo">
                                    <button type="submit" class="remove-promo-btn">&times;</button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Shipping Method Selection -->
                        <div class="shipping-section">
                            <h4>Shipping Method</h4>
                            <form method="POST" id="shippingForm">
                                <input type="hidden" name="action" value="update_shipping">
                                <select name="shipping_method_id" onchange="this.form.submit()">
                                    <?php foreach ($shippingMethods as $method): ?>
                                    <option value="<?php echo $method['id']; ?>" 
                                            <?php echo $shippingMethod['id'] == $method['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($method['name']); ?> - 
                                        LE <?php echo number_format($method['cost'], 2); ?> 
                                        (<?php echo htmlspecialchars($method['estimated_days']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>

                        <div class="summary-row">
                            <span>Shipping</span>
                            <span id="shipping"><?php echo $shipping == 0 ? 'FREE' : 'LE ' . number_format($shipping, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Tax (14%)</span>
                            <span id="tax">LE <?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="summary-row total-row">
                            <span>Total</span>
                            <span id="total">LE <?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address Section -->
                <div class="shipping-address-section">
                    <h2>Shipping Address</h2>
                    <form method="POST" class="address-form">
                        <input type="hidden" name="action" value="save_address">
                        <div class="form-group">
                            <input type="text" name="full_name" placeholder="Full Name *" required
                                   value="<?php echo htmlspecialchars($shippingAddress['full_name']); ?>">
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Email Address *" required
                                   value="<?php echo htmlspecialchars($shippingAddress['email']); ?>">
                        </div>
                        <div class="form-group">
                            <input type="text" name="address" placeholder="Street Address *" required
                                   value="<?php echo htmlspecialchars($shippingAddress['address']); ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" name="city" placeholder="City *" required
                                       value="<?php echo htmlspecialchars($shippingAddress['city']); ?>">
                            </div>
                            <div class="form-group">
                                <input type="text" name="postal_code" placeholder="Postal Code"
                                       value="<?php echo htmlspecialchars($shippingAddress['postal_code']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="tel" name="phone" placeholder="Phone Number *" required
                                   value="<?php echo htmlspecialchars($shippingAddress['phone']); ?>">
                        </div>
                        <button type="submit" class="save-address-btn">Save Address</button>
                    </form>
                </div>

                <!-- Payment Section -->
                <div class="payment-section">
                    <h2>Payment Method</h2>
                    
                    <form method="POST" action="checkout.php" class="checkout-form" id="checkoutForm">
                        <!-- Hidden fields for order data -->
                        <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
                        <input type="hidden" name="shipping" value="<?php echo $shipping; ?>">
                        <input type="hidden" name="tax" value="<?php echo $tax; ?>">
                        <input type="hidden" name="total" value="<?php echo $total; ?>">
                        <input type="hidden" name="item_count" value="<?php echo $itemCount; ?>">
                        <input type="hidden" name="original_total" value="<?php echo $subtotal + $shipping + $tax; ?>">
                        
                        <!-- Cart items as hidden fields -->
                        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                        <input type="hidden" name="cart_items[<?php echo $index; ?>][id]" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="cart_items[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($item['name']); ?>">
                        <input type="hidden" name="cart_items[<?php echo $index; ?>][price]" value="<?php echo $item['price']; ?>">
                        <input type="hidden" name="cart_items[<?php echo $index; ?>][quantity]" value="<?php echo $item['quantity']; ?>">
                        <input type="hidden" name="cart_items[<?php echo $index; ?>][image]" value="<?php echo htmlspecialchars($item['image']); ?>">
                        <?php endforeach; ?>
                        
                        <!-- Promo code data -->
                        <?php if ($promoCode): ?>
                        <input type="hidden" name="promo_code" value="<?php echo htmlspecialchars($promoCode['code']); ?>">
                        <input type="hidden" name="discount_amount" value="<?php echo $discount; ?>">
                        <input type="hidden" name="discount_type" value="<?php echo $promoCode['type']; ?>">
                        <?php endif; ?>
                        
                        <!-- Shipping address data -->
                        <input type="hidden" name="shipping_name" value="<?php echo htmlspecialchars($shippingAddress['full_name']); ?>">
                        <input type="hidden" name="shipping_email" value="<?php echo htmlspecialchars($shippingAddress['email']); ?>">
                        <input type="hidden" name="shipping_phone" value="<?php echo htmlspecialchars($shippingAddress['phone']); ?>">
                        <input type="hidden" name="shipping_address" value="<?php echo htmlspecialchars($shippingAddress['address']); ?>">
                        <input type="hidden" name="shipping_city" value="<?php echo htmlspecialchars($shippingAddress['city']); ?>">
                        <input type="hidden" name="shipping_postal" value="<?php echo htmlspecialchars($shippingAddress['postal_code']); ?>">
                        
                        <div class="payment-options">
                            <div class="payment-option">
                                <input type="radio" id="cashOnDelivery" name="paymentMethod" value="cash" required checked>
                                <label for="cashOnDelivery">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <div>
                                        <h4>Cash on Delivery</h4>
                                        <p>Pay when you receive your order</p>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="visaCard" name="paymentMethod" value="visa">
                                <label for="visaCard">
                                    <i class="fab fa-cc-visa"></i>
                                    <div>
                                        <h4>Visa / Mastercard</h4>
                                        <p>Pay with your credit or debit card</p>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="instaPay" name="paymentMethod" value="instapay">
                                <label for="instaPay">
                                    <i class="fas fa-bolt"></i>
                                    <div>
                                        <h4>InstaPay</h4>
                                        <p>Instant bank transfer</p>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="vodafoneCash" name="paymentMethod" value="vodafone">
                                <label for="vodafoneCash">
                                    <i class="fas fa-mobile-alt"></i>
                                    <div>
                                        <h4>Vodafone Cash</h4>
                                        <p>Pay using your Vodafone mobile wallet</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="payment-details" id="paymentDetails">
                            <div class="payment-instruction">
                                <p>Select a payment method to proceed</p>
                            </div>
                        </div>
                        
                        <?php if ($totalItems > 0): ?>
                        <button type="submit" class="checkout-btn" name="checkout">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </button>
                        <?php else: ?>
                        <button type="button" class="checkout-btn" disabled>
                            <i class="fas fa-lock"></i> Cart is Empty
                        </button>
                        <?php endif; ?>
                    </form>
                    
                    <a href="index.php" class="continue-shopping-link">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
        
        <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification" id="notification">
            <span id="notificationText"><?php echo $_SESSION['notification']; ?></span>
        </div>
        <?php 
        unset($_SESSION['notification']);
        endif; ?>
    </div>

    <script>
    // JavaScript for payment details
    document.addEventListener('DOMContentLoaded', function() {
        const paymentOptions = document.querySelectorAll('input[name="paymentMethod"]');
        const paymentDetails = document.getElementById('paymentDetails');
        
        paymentOptions.forEach(option => {
            option.addEventListener('change', function() {
                const paymentMethod = this.value;
                let paymentHTML = '';
                
                switch(paymentMethod) {
                    case 'cash':
                        paymentHTML = `
                            <div class="payment-instruction">
                                <h4><i class="fas fa-money-bill-wave"></i> Cash on Delivery</h4>
                                <p>You'll pay with cash when your order is delivered. No extra fees.</p>
                                <p>Please have exact change ready for the delivery person.</p>
                            </div>
                        `;
                        break;
                    case 'visa':
                        paymentHTML = `
                            <div class="payment-card-form">
                                <h4><i class="fab fa-cc-visa"></i> Card Details</h4>
                                <div class="form-group">
                                    <label>Card Number</label>
                                    <input type="text" name="card_number" class="card-input" placeholder="1234 5678 9012 3456" required>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Expiry Date</label>
                                        <input type="text" name="card_expiry" class="card-input" placeholder="MM/YY" required>
                                    </div>
                                    <div class="form-group">
                                        <label>CVV</label>
                                        <input type="text" name="card_cvv" class="card-input" placeholder="123" required>
                                    </div>
                                </div>
                            </div>
                        `;
                        break;
                    case 'instapay':
                        paymentHTML = `
                            <div class="payment-instruction">
                                <h4><i class="fas fa-bolt"></i> InstaPay Instructions</h4>
                                <p>1. Open your banking app and select "InstaPay"</p>
                                <p>2. Send payment to: <strong>Bank XYZ - Account: 1234567890</strong></p>
                                <p>3. Use order ID: <strong>ORD-<?php echo date('YmdHis'); ?></strong> as reference</p>
                                <p>4. Upload payment receipt after completion</p>
                            </div>
                        `;
                        break;
                    case 'vodafone':
                        paymentHTML = `
                            <div class="payment-instruction">
                                <h4><i class="fas fa-mobile-alt"></i> Vodafone Cash</h4>
                                <p>1. Open Vodafone Cash app on your phone</p>
                                <p>2. Send payment to: <strong>0100 123 4567</strong></p>
                                <p>3. Total amount: <strong>LE <?php echo number_format($total, 2); ?></strong></p>
                                <p>4. Take a screenshot of the payment confirmation</p>
                            </div>
                        `;
                        break;
                    default:
                        paymentHTML = `<div class="payment-instruction"><p>Select a payment method to proceed</p></div>`;
                }
                
                paymentDetails.innerHTML = paymentHTML;
            });
        });
        
        // Show notification if exists
        const notification = document.getElementById('notification');
        if (notification) {
            setTimeout(() => {
                notification.style.opacity = '1';
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 500);
                }, 3000);
            }, 100);
        }
    });
    </script>
</body>
</html>