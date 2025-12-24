<?php
session_start();
require_once 'config.php';

$product_id = $_GET['id'] ?? 0;
if (!$product_id) {
    header('Location: index.php');
    exit;
}

$conn = getConnection();

// Get product details
$product_sql = "SELECT p.*, 
                c.name as category_name,
                (SELECT COUNT(*) FROM order_items WHERE product_id = p.id) as sold_count,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?";
$stmt = $conn->prepare($product_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Get product images
$images_sql = "SELECT * FROM product_images WHERE product_id = ?";
$stmt = $conn->prepare($images_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$images_result = $stmt->get_result();
$images = [];
while ($row = $images_result->fetch_assoc()) {
    $images[] = $row;
}
$stmt->close();

// Get product variants (sizes)
$variants_sql = "SELECT * FROM product_variants WHERE product_id = ?";
$stmt = $conn->prepare($variants_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$variants_result = $stmt->get_result();
$variants = [];
while ($row = $variants_result->fetch_assoc()) {
    $variants[] = $row;
}
$stmt->close();

// Get related products
$related_sql = "SELECT p.*, 
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as image
                FROM products p
                WHERE p.category_id = ? AND p.id != ?
                LIMIT 4";
$stmt = $conn->prepare($related_sql);
$stmt->bind_param("ii", $product['category_id'], $product_id);
$stmt->execute();
$related_result = $stmt->get_result();
$related_products = [];
while ($row = $related_result->fetch_assoc()) {
    $related_products[] = $row;
}
$stmt->close();

// Get reviews
$reviews_sql = "SELECT r.*, u.full_name 
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC
                LIMIT 5";
$stmt = $conn->prepare($reviews_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

closeConnection($conn);

// Calculate price
$price = $product['discount_price'] > 0 ? $product['discount_price'] : $product['price'];
$has_discount = $product['discount_price'] > 0;
$discount_percent = $has_discount ? round((($product['price'] - $price) / $product['price']) * 100) : 0;

// Size chart data
$size_chart = [
    'Sizes' => ['S', 'M', 'L', 'XL', '2XL', '3XL'],
    'Chest (cm)' => ['91-96', '97-102', '103-108', '109-114', '115-120', '121-126'],
    'Length (cm)' => ['69', '71', '73', '75', '77', '79'],
    'Fit' => ['Regular', 'Regular', 'Regular', 'Regular', 'Loose', 'Loose']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> | JERSEY WEAR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="test.css">
    <link rel="stylesheet" href="enhanced.css">
    <script src="script.js" defer></script>
    <script src="enhanced.js" defer></script>
</head>
<body>
    <!-- Your existing NAVBAR -->
    <header class="navbar">
      <div class="menu" id="menuBtn">☰</div>
      <a href="index.php" class="logo">JERSEY<span>Wears</span></a>
      <div class="icons">
        <button id="themeToggle" class="theme-btn">🌙</button>
        <a href="profile.php">👤</a>
        <button id="cartBtn" class="cart-btn">🛒 
          <span class="cart-count" id="cartCount">
            <?php echo count($_SESSION['cart']); ?>
          </span>
        </button>
      </div>
    </header>

    <!-- Your existing SIDEBAR -->
    <div class="sidebar" id="sidebar">
      <div class="close-btn" id="closeSidebar">×</div>
      <a href="index.php">HOME</a>
      <a href="2025.php">2025 SEASON</a>
      <a href="2024.php">2024 SEASON</a>
      <a href="2023.php">2023 SEASON</a>
      <a href="iconic.php">ICONIC JERSEYS</a>
      <a href="national_jersey.php">NATIONAL JERSEYS</a>
      <a href="world_cup.php">WORLD CUP JERSEYS</a>
      <a href="special_edition.php">SPECIAL EDITIONS</a>
      <a href="hot_offer.php">HOT OFFERS</a>
      <a href="profile.php">Log in</a>
      <div class="socials">
        <span>📸</span>
        <span>📘</span>
      </div>
    </div>

    <!-- Product Detail Page -->
    <main class="product-page">
        <!-- Breadcrumb -->
        <div style="padding: 20px 50px;">
            <a href="index.php">Home</a> / 
            <a href="category.php?id=<?php echo $product['category_id']; ?>">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a> / 
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; padding: 0 50px 50px;">
            <!-- Left Column: Product Images -->
            <div class="product-gallery">
                <div class="thumbnail-images">
                    <?php foreach($images as $index => $image): ?>
                    <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                         data-index="<?php echo $index; ?>">
                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                             alt="Thumbnail <?php echo $index + 1; ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="main-image">
                    <?php if(!empty($images)): ?>
                    <img src="<?php echo htmlspecialchars($images[0]['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         id="mainProductImage"
                         data-zoom="<?php echo htmlspecialchars($images[0]['image_path']); ?>">
                    <div class="zoom-overlay">Click to zoom</div>
                    <?php endif; ?>
                    
                    <?php if($product['video']): ?>
                    <div style="margin-top: 20px;">
                        <button class="add-to-cart" onclick="playProductVideo()" style="width: 100%;">
                            <i class="fas fa-play"></i> Watch Product Video
                        </button>
                        <video id="productVideo" style="display: none; width: 100%; margin-top: 10px;" controls>
                            <source src="<?php echo htmlspecialchars($product['video']); ?>" type="video/mp4">
                        </video>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Product Info -->
            <div>
                <h1 style="font-size: 32px; margin-bottom: 10px; color: #333;">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>
                
                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                    <div style="color: #ffd700; font-size: 18px;">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php if($i <= floor($product['avg_rating'])): ?>
                        ★
                        <?php elseif($i - 0.5 <= $product['avg_rating']): ?>
                        ☆
                        <?php else: ?>
                        ☆
                        <?php endif; ?>
                        <?php endfor; ?>
                        <span style="color: #666; font-size: 14px; margin-left: 5px;">
                            (<?php echo $product['review_count']; ?> reviews)
                        </span>
                    </div>
                    <div style="color: #666; font-size: 14px;">
                        <i class="fas fa-shopping-bag"></i> <?php echo $product['sold_count']; ?> sold
                    </div>
                    <div style="color: #666; font-size: 14px;">
                        SKU: <?php echo htmlspecialchars($product['sku']); ?>
                    </div>
                </div>
                
                <!-- Price -->
                <div style="margin-bottom: 30px;">
                    <?php if($has_discount): ?>
                    <div style="background: #ff416c; color: white; padding: 5px 10px; border-radius: 5px; display: inline-block; margin-bottom: 10px;">
                        -<?php echo $discount_percent; ?>%
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 32px; font-weight: bold; color: #ff416c;">
                            LE <?php echo number_format($price, 2); ?>
                        </span>
                        <span style="font-size: 20px; color: #999; text-decoration: line-through;">
                            LE <?php echo number_format($product['price'], 2); ?>
                        </span>
                    </div>
                    <?php else: ?>
                    <span style="font-size: 32px; font-weight: bold; color: #ff416c;">
                        LE <?php echo number_format($price, 2); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <!-- Size Selection -->
                <div class="size-selection">
                    <h3 style="margin-bottom: 15px;">Select Size</h3>
                    <div class="size-options">
                        <?php 
                        $sizes = ['S', 'M', 'L', 'XL', '2XL', '3XL'];
                        foreach($sizes as $size): 
                            $variant = array_filter($variants, function($v) use ($size) {
                                return $v['variant_name'] === 'Size' && $v['variant_value'] === $size;
                            });
                            $variant = reset($variant);
                            $available = $variant && $variant['stock'] > 0;
                        ?>
                        <div class="size-option <?php echo !$available ? 'unavailable' : ''; ?>"
                             data-size="<?php echo $size; ?>"
                             <?php if(!$available) echo 'title="Out of stock"'; ?>>
                            <?php echo $size; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Stock Availability -->
                <div style="margin: 20px 0; padding: 15px; border-radius: 5px; 
                            background: <?php echo $product['stock'] > 10 ? '#d4edda' : ($product['stock'] > 0 ? '#fff3cd' : '#f8d7da'); ?>;">
                    <?php if($product['stock'] > 10): ?>
                    <span style="color: #155724;">
                        <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)
                    </span>
                    <?php elseif($product['stock'] > 0): ?>
                    <span style="color: #856404;">
                        <i class="fas fa-exclamation-triangle"></i> Low Stock (Only <?php echo $product['stock']; ?> left)
                    </span>
                    <?php else: ?>
                    <span style="color: #721c24;">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </span>
                    <?php endif; ?>
                </div>
                
                <!-- Add to Cart -->
                <div style="margin: 30px 0;">
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <button class="add-to-cart" style="flex: 1;" 
                                data-product='<?php echo json_encode([
                                    'id' => $product['id'],
                                    'name' => $product['name'],
                                    'price' => $price,
                                    'image' => $images[0]['image_path'] ?? ''
                                ]); ?>'
                                id="addToCartBtn">
                            Add to Cart
                        </button>
                        <button class="add-to-cart" style="flex: 1; background: #2c3e50;">
                            Buy Now
                        </button>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button class="add-to-cart" style="background: #f8f8f8; color: #333;">
                            <i class="far fa-heart"></i> Wishlist
                        </button>
                        <button class="add-to-cart" style="background: #f8f8f8; color: #333;">
                            <i class="fas fa-exchange-alt"></i> Compare
                        </button>
                    </div>
                </div>
                
                <!-- Quick Info -->
                <div style="border-top: 1px solid #eee; padding-top: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                        <div>
                            <strong>Free Shipping</strong>
                            <p style="color: #666; font-size: 14px;">On orders over LE 1000</p>
                        </div>
                        <div>
                            <strong>30-Day Returns</strong>
                            <p style="color: #666; font-size: 14px;">Easy return policy</p>
                        </div>
                        <div>
                            <strong>Authentic Product</strong>
                            <p style="color: #666; font-size: 14px;">100% genuine</p>
                        </div>
                        <div>
                            <strong>Secure Payment</strong>
                            <p style="color: #666; font-size: 14px;">SSL encrypted</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Tabs -->
        <div class="product-tabs" style="padding: 0 50px 50px;">
            <div class="tabs-header">
                <button class="tab-btn active" data-tab="description">Description</button>
                <button class="tab-btn" data-tab="specifications">Specifications</button>
                <button class="tab-btn" data-tab="size-chart">Size Chart</button>
                <button class="tab-btn" data-tab="reviews">Reviews (<?php echo $product['review_count']; ?>)</button>
            </div>
            
            <div style="padding: 30px 0;">
                <!-- Description -->
                <div class="tab-content active" id="description">
                    <h3 style="margin-bottom: 20px;">Product Description</h3>
                    <div style="line-height: 1.6; color: #666;">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                </div>
                
                <!-- Specifications -->
                <div class="tab-content" id="specifications">
                    <h3 style="margin-bottom: 20px;">Product Specifications</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px; font-weight: bold; width: 200px;">Material</td>
                            <td style="padding: 10px;">100% Polyester</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px; font-weight: bold;">Fit</td>
                            <td style="padding: 10px;">Regular Fit</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px; font-weight: bold;">Care Instructions</td>
                            <td style="padding: 10px;">Machine wash cold, tumble dry low</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px; font-weight: bold;">Country of Origin</td>
                            <td style="padding: 10px;">Thailand</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px; font-weight: bold;">SKU</td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($product['sku']); ?></td>
                        </tr>