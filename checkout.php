<?php
session_start();

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Save cart items BEFORE clearing anything
$cartItems = $_SESSION['cart'];
$cartItemCount = array_sum(array_column($cartItems, 'quantity'));

// Get form data
$paymentMethod = $_POST['paymentMethod'] ?? 'cash';
$subtotal = $_POST['subtotal'] ?? 0;
$shipping = $_POST['shipping'] ?? 0;
$tax = $_POST['tax'] ?? 0;
$total = $_POST['total'] ?? 0;
$item_count = $_POST['item_count'] ?? 0;

// Get shipping address from session
$shippingAddress = $_SESSION['shipping_address'] ?? [
    'full_name' => 'Guest',
    'address' => 'Not provided',
    'city' => 'Not provided',
    'postal_code' => '',
    'phone' => 'Not provided',
    'email' => 'guest@example.com'
];

// Get promo code info
$promoCode = $_SESSION['promo_code'] ?? null;
$discount = 0;
$discountAmount = 0;
$originalTotal = $subtotal + $shipping + $tax;

// Calculate discount if promo code is applied
if ($promoCode) {
    $subtotalBeforeDiscount = $subtotal;
    
    if ($promoCode['type'] === 'percentage') {
        $discountAmount = ($subtotal * $promoCode['discount']) / 100;
        $discount = $promoCode['discount'] . '%';
    } elseif ($promoCode['type'] === 'fixed') {
        $discountAmount = min($promoCode['discount'], $subtotal);
        $discount = 'LE ' . number_format($promoCode['discount'], 2);
    } elseif ($promoCode['type'] === 'shipping') {
        // Shipping discount already applied in cart calculation
        $discount = 'LE ' . number_format($promoCode['discount'], 2) . ' (Shipping)';
        $discountAmount = $promoCode['discount'];
    }
}

// Generate order ID
$orderId = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);

// Save order to session for history (optional)
$_SESSION['last_order'] = [
    'order_id' => $orderId,
    'payment_method' => $paymentMethod,
    'subtotal' => $subtotal,
    'shipping' => $shipping,
    'tax' => $tax,
    'total' => $total,
    'original_total' => $originalTotal,
    'discount' => $discountAmount,
    'discount_code' => $promoCode ? $promoCode['code'] : null,
    'shipping_address' => $shippingAddress,
    'cart_items' => $cartItems,
    'timestamp' => time()
];

// Clear cart and promo AFTER saving everything
$_SESSION['cart'] = [];
$_SESSION['promo_code'] = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed | JERSEY WEAR</title>
    <link rel="stylesheet" href="cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .order-confirmation-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .confirmation-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .confirmation-header h1 {
            color: #2ecc71;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .confirmation-icon {
            font-size: 60px;
            color: #2ecc71;
            margin-bottom: 20px;
        }

        .order-id {
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 10px;
            font-family: monospace;
            font-size: 18px;
            color: #3498db;
            margin: 15px 0;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 30px 0;
        }

        .summary-section, .address-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .summary-section h3, .address-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
            font-size: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row.total {
            font-weight: 700;
            font-size: 20px;
            color: #2c3e50;
            padding-top: 15px;
            border-top: 2px solid #e9ecef;
            margin-top: 10px;
        }

        .summary-row.discount {
            color: #e74c3c;
            font-weight: 600;
        }

        .summary-row.original-total {
            color: #7f8c8d;
            font-size: 16px;
            text-decoration: line-through;
        }

        .address-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .address-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .address-item i {
            color: #3498db;
            font-size: 18px;
            width: 24px;
        }

        .address-item strong {
            min-width: 120px;
            color: #495057;
        }

        .order-items {
            margin: 30px 0;
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
        }

        .order-items h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #e9ecef;
        }

        .order-item:last-child {
            margin-bottom: 0;
        }

        .item-name {
            flex: 1;
            font-weight: 600;
            color: #2c3e50;
        }

        .item-quantity {
            color: #7f8c8d;
            margin: 0 20px;
        }

        .item-price {
            font-weight: 700;
            color: #3498db;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .btn {
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #495057;
            border: 2px solid #dee2e6;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.15);
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #e9ecef;
        }

        .payment-method i {
            font-size: 30px;
            color: #3498db;
        }

        .payment-method div h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }

        .payment-method div p {
            margin: 0;
            color: #7f8c8d;
        }

        .email-confirmation {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
            margin: 20px 0;
            text-align: center;
        }

        .empty-cart-message {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            background: white;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }

        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .order-confirmation-container {
                padding: 20px;
                margin: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .address-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .address-item strong {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <div class="order-confirmation-container">
            <div class="confirmation-header">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Order Confirmed!</h1>
                <p class="order-id">Order ID: <?php echo $orderId; ?></p>
                <p>Thank you for your purchase! Your order has been successfully placed.</p>
            </div>

            <!-- Payment Method -->
            <div class="payment-method">
                <i class="fas fa-<?php 
                    switch($paymentMethod) {
                        case 'visa': echo 'credit-card'; break;
                        case 'instapay': echo 'bolt'; break;
                        case 'vodafone': echo 'mobile-alt'; break;
                        default: echo 'money-bill-wave';
                    }
                ?>"></i>
                <div>
                    <h4>Payment Method: 
                        <?php 
                            switch($paymentMethod) {
                                case 'visa': echo 'Visa/Mastercard'; break;
                                case 'instapay': echo 'InstaPay'; break;
                                case 'vodafone': echo 'Vodafone Cash'; break;
                                default: echo 'Cash on Delivery';
                            }
                        ?>
                    </h4>
                    <p>
                        <?php 
                            switch($paymentMethod) {
                                case 'visa': echo 'Payment processed via card'; break;
                                case 'instapay': echo 'Instant bank transfer'; break;
                                case 'vodafone': echo 'Mobile wallet payment'; break;
                                default: echo 'Pay when you receive your order';
                            }
                        ?>
                    </p>
                </div>
            </div>

            <div class="summary-grid">
                <!-- Order Summary -->
                <div class="summary-section">
                    <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>LE <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span><?php echo $shipping == 0 ? 'FREE' : 'LE ' . number_format($shipping, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Tax (14%)</span>
                        <span>LE <?php echo number_format($tax, 2); ?></span>
                    </div>
                    
                    <?php if ($promoCode): ?>
                    <div class="summary-row original-total">
                        <span>Total Before Discount</span>
                        <span>LE <?php echo number_format($originalTotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row discount">
                        <span>Discount (<?php echo $promoCode['code']; ?>)</span>
                        <span>- LE <?php echo number_format($discountAmount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-row total">
                        <span>Final Total</span>
                        <span>LE <?php echo number_format($total, 2); ?></span>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="address-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Shipping Address</h3>
                    
                    <div class="address-details">
                        <div class="address-item">
                            <i class="fas fa-user"></i>
                            <div>
                                <strong>Full Name:</strong>
                                <?php echo htmlspecialchars($shippingAddress['full_name']); ?>
                            </div>
                        </div>
                        
                        <div class="address-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email:</strong>
                                <?php echo htmlspecialchars($shippingAddress['email']); ?>
                            </div>
                        </div>
                        
                        <div class="address-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Phone:</strong>
                                <?php echo htmlspecialchars($shippingAddress['phone']); ?>
                            </div>
                        </div>
                        
                        <div class="address-item">
                            <i class="fas fa-home"></i>
                            <div>
                                <strong>Address:</strong>
                                <?php echo htmlspecialchars($shippingAddress['address']); ?>,
                                <?php echo htmlspecialchars($shippingAddress['city']); ?>
                                <?php echo !empty($shippingAddress['postal_code']) ? ' - ' . htmlspecialchars($shippingAddress['postal_code']) : ''; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="order-items">
                <h3><i class="fas fa-shopping-bag"></i> Order Items (<?php echo $cartItemCount; ?>)</h3>
                
                <?php if (!empty($cartItems)): ?>
                    <?php foreach($cartItems as $item): ?>
                    <div class="order-item">
                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="item-quantity">x<?php echo $item['quantity']; ?></div>
                        <div class="item-price">LE <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-cart-message">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                        <p>No items found in cart.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Email Confirmation -->
            <div class="email-confirmation">
                <i class="fas fa-paper-plane"></i>
                A confirmation email has been sent to <strong><?php echo htmlspecialchars($shippingAddress['email']); ?></strong>. 
                You will receive updates about your order status.
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Continue Shopping
                </a>
                <button class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll to top
        window.scrollTo(0, 0);
        
    </script>
</body>
</html>