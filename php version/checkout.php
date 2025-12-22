<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['checkout'])) {
    header('Location: cart.php');
    exit();
}

// Validate cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['notification'] = 'Your cart is empty.';
    header('Location: cart.php');
    exit();
}

// Validate payment method
$paymentMethod = $_POST['paymentMethod'] ?? '';
if (!in_array($paymentMethod, ['cash', 'visa', 'instapay', 'vodafone'])) {
    $_SESSION['notification'] = 'Please select a valid payment method.';
    header('Location: cart.php');
    exit();
}

// Validate card details if Visa is selected
if ($paymentMethod === 'visa') {
    $cardNumber = $_POST['card_number'] ?? '';
    $cardExpiry = $_POST['card_expiry'] ?? '';
    $cardCVV = $_POST['card_cvv'] ?? '';
    
    if (empty($cardNumber) || empty($cardExpiry) || empty($cardCVV)) {
        $_SESSION['notification'] = 'Please fill all card details.';
        header('Location: cart.php');
        exit();
    }
}

// Process order
$orderData = [
    'order_id' => 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999),
    'items' => $_SESSION['cart'],
    'subtotal' => $_POST['subtotal'] ?? 0,
    'shipping' => $_POST['shipping'] ?? 0,
    'tax' => $_POST['tax'] ?? 0,
    'total' => $_POST['total'] ?? 0,
    'payment_method' => $paymentMethod,
    'payment_method_text' => [
        'cash' => 'Cash on Delivery',
        'visa' => 'Visa/Mastercard',
        'instapay' => 'InstaPay',
        'vodafone' => 'Vodafone Cash'
    ][$paymentMethod],
    'timestamp' => date('Y-m-d H:i:s'),
    'customer_email' => $_SESSION['user_email'] ?? 'guest@example.com'
];

// In a real application, you would:
// 1. Save to database
// 2. Process payment
// 3. Send confirmation email
// 4. Update inventory

// For demo purposes, we'll just save to session and clear cart
$_SESSION['last_order'] = $orderData;
$cartItems = $_SESSION['cart'];
$_SESSION['cart'] = [];

// Generate order summary for display
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation | Jersey Wear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .confirmation-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 800px;
            width: 100%;
            text-align: center;
        }
        
        .success-icon {
            color: #2ecc71;
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .order-id {
            color: #667eea;
            font-size: 1.2rem;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-row.total {
            font-weight: bold;
            font-size: 1.2rem;
            color: #2c3e50;
            border-bottom: none;
            padding-top: 20px;
        }
        
        .order-items {
            margin-top: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-name {
            flex: 2;
        }
        
        .item-qty {
            flex: 1;
            text-align: center;
        }
        
        .item-price {
            flex: 1;
            text-align: right;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #495057;
            border: 2px solid #dee2e6;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
            color: #6c757d;
        }
        
        .payment-method i {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Order Confirmed!</h1>
        <p class="order-id">Order ID: <?php echo $orderData['order_id']; ?></p>
        <p>Thank you for your purchase! Your order has been successfully placed.</p>
        
        <div class="payment-method">
            <?php
            $paymentIcons = [
                'cash' => 'fa-money-bill-wave',
                'visa' => 'fa-cc-visa',
                'instapay' => 'fa-bolt',
                'vodafone' => 'fa-mobile-alt'
            ];
            ?>
            <i class="fas <?php echo $paymentIcons[$paymentMethod]; ?>"></i>
            <span>Payment Method: <?php echo $orderData['payment_method_text']; ?></span>
        </div>
        
        <div class="order-summary">
            <h3>Order Summary</h3>
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>LE <?php echo number_format($orderData['subtotal'], 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span><?php echo $orderData['shipping'] == 0 ? 'FREE' : 'LE ' . number_format($orderData['shipping'], 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Tax (14%):</span>
                <span>LE <?php echo number_format($orderData['tax'], 2); ?></span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>LE <?php echo number_format($orderData['total'], 2); ?></span>
            </div>
            
            <?php if (!empty($cartItems)): ?>
            <div class="order-items">
                <h4 style="margin-top: 20px; margin-bottom: 15px;">Order Items:</h4>
                <?php foreach ($cartItems as $item): ?>
                <div class="order-item">
                    <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                    <span class="item-qty">x<?php echo $item['quantity']; ?></span>
                    <span class="item-price">LE <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <p style="color: #6c757d; margin: 20px 0;">
            A confirmation email has been sent to <?php echo $orderData['customer_email']; ?>. 
            You will receive updates about your order status.
        </p>
        
        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="cart.php" class="btn btn-secondary">
                <i class="fas fa-shopping-cart"></i> View Cart
            </a>
        </div>
    </div>
</body>
</html>