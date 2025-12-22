<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Your Store</title>
    <link rel="stylesheet" href="cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php
    // Start session
    session_start();
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Handle cart actions via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handleCartActions();
    }
    
    $cartItems = $_SESSION['cart'];
    $totalItems = count($cartItems);
    $itemCount = 0;
    $subtotal = 0;
    
    // Calculate totals
    foreach ($cartItems as $item) {
        $itemCount += $item['quantity'];
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $shipping = $subtotal > 1000 ? 0 : 50;
    $tax = $subtotal * 0.14;
    $total = $subtotal + $shipping + $tax;
    
    function handleCartActions() {
        $action = $_POST['action'] ?? '';
        $productId = $_POST['product_id'] ?? 0;
        
        switch ($action) {
            case 'add':
                $name = $_POST['name'] ?? '';
                $price = $_POST['price'] ?? 0;
                $image = $_POST['image'] ?? '';
                addToCart($productId, $name, $price, $image);
                break;
                
            case 'update':
                $quantity = $_POST['quantity'] ?? 1;
                updateCartItem($productId, $quantity);
                break;
                
            case 'remove':
                removeFromCart($productId);
                break;
                
            case 'clear':
                clearCart();
                break;
        }
        
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    
    function addToCart($id, $name, $price, $image) {
        // Check if item already exists
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $id) {
                $item['quantity']++;
                return;
            }
        }
        
        // Add new item
        $_SESSION['cart'][] = [
            'id' => $id,
            'name' => $name,
            'price' => floatval($price),
            'quantity' => 1,
            'image' => $image ?: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
        ];
    }
    
    function updateCartItem($id, $quantity) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $id) {
                $item['quantity'] = max(1, intval($quantity));
                break;
            }
        }
    }
    
    function removeFromCart($id) {
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($id) {
            return $item['id'] != $id;
        });
    }
    
    function clearCart() {
        $_SESSION['cart'] = [];
    }
    ?>
    
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
                    <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                        <form method="POST" class="cart-item-form">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
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
                            <button type="submit" class="remove-item-btn">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
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
            </div>

            <div class="cart-summary-section">
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="subtotal">LE <?php echo number_format($subtotal, 2); ?></span>
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

                <div class="payment-section">
                    <h2>Payment Method</h2>
                    
                    <form method="POST" action="checkout.php" class="checkout-form">
                        <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
                        <input type="hidden" name="shipping" value="<?php echo $shipping; ?>">
                        <input type="hidden" name="tax" value="<?php echo $tax; ?>">
                        <input type="hidden" name="total" value="<?php echo $total; ?>">
                        <input type="hidden" name="item_count" value="<?php echo $itemCount; ?>">
                        
                        <div class="payment-options">
                            <div class="payment-option">
                                <input type="radio" id="cashOnDelivery" name="paymentMethod" value="cash" required>
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

    <script src="cart.js"></script>
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