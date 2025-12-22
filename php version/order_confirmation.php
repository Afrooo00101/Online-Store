<?php
session_start();

// Check if order was just placed
if (!isset($_SESSION['order_confirmation'])) {
    header('Location: index.php');
    exit;
}

$order = $_SESSION['order_confirmation'];
unset($_SESSION['order_confirmation']); // Clear after display
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation | JERSEY WEAR</title>
    <link rel="stylesheet" href="checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="confirmation-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Order Confirmed!</h1>
                <p>Thank you for your purchase</p>
            </div>
            
            <div class="order-details">
                <div class="detail-card">
                    <h3><i class="fas fa-receipt"></i> Order Details</h3>
                    <div class="detail-row">
                        <span>Order Number:</span>
                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Order Date:</span>
                        <span><?php echo date('F j, Y, g:i a'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Payment Method:</span>
                        <span><?php echo htmlspecialchars($order['payment_method']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Total Amount:</span>
                        <strong>LE <?php echo number_format($order['total'], 2); ?></strong>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-shipping-fast"></i> Next Steps</h3>
                    <ol class="steps-list">
                        <li>
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Order Confirmation</strong>
                                <p>We've sent a confirmation email with your order details</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-cog"></i>
                            <div>
                                <strong>Order Processing</strong>
                                <p>We're preparing your order for shipment</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-truck"></i>
                            <div>
                                <strong>Shipping</strong>
                                <p>Your order will be shipped within 1-2 business days</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-home"></i>
                            <div>
                                <strong>Delivery</strong>
                                <p>Expected delivery: 3-5 business days</p>
                            </div>
                        </li>
                    </ol>
                </div>
                
                <?php if($order['payment_method'] === 'cod'): ?>
                <div class="detail-card payment-instructions">
                    <h3><i class="fas fa-money-bill-wave"></i> Cash on Delivery Instructions</h3>
                    <p>Please have exact change ready for the delivery person</p>
                    <p>Total amount to pay on delivery: <strong>LE <?php echo number_format($order['total'], 2); ?></strong></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="confirmation-actions">
                <a href="index.php" class="btn-primary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <a href="profile.php?view=orders" class="btn-secondary">
                    <i class="fas fa-clipboard-list"></i> View My Orders
                </a>
                <button onclick="window.print()" class="btn-outline">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
            
            <div class="customer-support">
                <h3><i class="fas fa-headset"></i> Need Help?</h3>
                <p>Contact our customer support team:</p>
                <p><i class="fas fa-phone"></i> +20 100 123 4567</p>
                <p><i class="fas fa-envelope"></i> support@jerseywear.com</p>
            </div>
        </div>
    </div>

    <style>
        .confirmation-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .confirmation-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .confirmation-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .success-icon {
            font-size: 80px;
            color: #2ecc71;
            margin-bottom: 20px;
        }
        
        .confirmation-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .confirmation-header p {
            color: #7f8c8d;
            font-size: 18px;
        }
        
        .order-details {
            display: grid;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .detail-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            border-left: 4px solid #3498db;
        }
        
        .detail-card h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .steps-list {
            list-style: none;
            padding: 0;
        }
        
        .steps-list li {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .steps-list li i {
            font-size: 24px;
            color: #3498db;
            width: 40px;
        }
        
        .steps-list li:last-child {
            margin-bottom: 0;
        }
        
        .steps-list li strong {
            color: #2c3e50;
            display: block;
            margin-bottom: 5px;
        }
        
        .steps-list li p {
            color: #7f8c8d;
            margin: 0;
        }
        
        .payment-instructions {
            border-left-color: #2ecc71;
        }
        
        .payment-instructions h3 i {
            color: #2ecc71;
        }
        
        .confirmation-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        
        .btn-primary, .btn-secondary, .btn-outline {
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            flex: 1;
            min-width: 200px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(37, 117, 252, 0.3);
        }
        
        .btn-secondary {
            background: #2c3e50;
            color: white;
            border: none;
        }
        
        .btn-outline {
            background: white;
            color: #3498db;
            border: 2px solid #3498db;
        }
        
        .btn-outline:hover {
            background: #3498db;
            color: white;
        }
        
        .customer-support {
            text-align: center;
            padding-top: 30px;
            border-top: 2px solid #eee;
        }
        
        .customer-support h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .customer-support p {
            color: #7f8c8d;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .confirmation-card {
                padding: 20px;
            }
            
            .confirmation-actions {
                flex-direction: column;
            }
            
            .btn-primary, .btn-secondary, .btn-outline {
                min-width: 100%;
            }
        }
    </style>
</body>
</html>