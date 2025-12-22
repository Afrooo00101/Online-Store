<?php
session_start();

// Sample product data
$products = [
    1 => [
        'name' => 'Football Jersey',
        'price' => 299.99,
        'image' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
        'description' => 'High-quality football jersey with moisture-wicking fabric.'
    ],
    2 => [
        'name' => 'Basketball Jersey',
        'price' => 249.99,
        'image' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
        'description' => 'Comfortable basketball jersey for peak performance.'
    ],
    3 => [
        'name' => 'Running Jersey',
        'price' => 199.99,
        'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
        'description' => 'Lightweight running jersey with breathable mesh.'
    ]
];

$productId = $_GET['id'] ?? 1;
$product = $products[$productId] ?? $products[1];

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    addToCart($productId, $product['name'], $product['price'], $product['image']);
}

function addToCart($id, $name, $price, $image) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if item already exists
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) {
            $item['quantity']++;
            $_SESSION['notification'] = "{$name} added to cart!";
            return;
        }
    }
    
    // Add new item
    $_SESSION['cart'][] = [
        'id' => $id,
        'name' => $name,
        'price' => floatval($price),
        'quantity' => 1,
        'image' => $image
    ];
    
    $_SESSION['notification'] = "{$name} added to cart!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | Jersey Wear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .cart-icon {
            position: relative;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-info h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }
        
        .product-price {
            font-size: 2rem;
            color: #e74c3c;
            font-weight: bold;
            margin: 1rem 0;
        }
        
        .product-description {
            line-height: 1.6;
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .add-to-cart-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .back-btn {
            display: inline-block;
            margin-top: 2rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-btn i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Jersey Wear Store</h1>
        <a href="cart.php" class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <span class="cart-count">
                    <?php 
                    $total = 0;
                    foreach ($_SESSION['cart'] as $item) {
                        $total += $item['quantity'];
                    }
                    echo $total;
                    ?>
                </span>
            <?php endif; ?>
        </a>
    </header>
    
    <main class="product-container">
        <div class="product-image">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        
        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-price">LE <?php echo number_format($product['price'], 2); ?></div>
            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
            
            <form method="POST">
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['image']); ?>">
                <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
            </form>
            
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </main>
    
    <?php if (isset($_SESSION['notification'])): ?>
    <div class="notification" style="position: fixed; bottom: 20px; right: 20px; padding: 15px 25px; background: #2ecc71; color: white; border-radius: 8px; font-weight: 600; z-index: 10000; opacity: 0; transition: opacity 0.5s;">
        <span><?php echo $_SESSION['notification']; ?></span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.querySelector('.notification');
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
    <?php 
    unset($_SESSION['notification']);
    endif; ?>
</body>
</html>