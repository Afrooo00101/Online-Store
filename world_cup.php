<?php
// Start session
session_start();

// Include database configuration
require_once 'config.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get database connection
$conn = getConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>World Cup Editions | Jersey Wear</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="test.css">
<link rel="stylesheet" href="cart.css">
<script src="script.js" defer></script>
<script src="cart.js" defer></script>
</head>

<body>

<!-- NAVBAR -->
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

<!-- SIDEBAR -->
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

<!-- WORLD CUP HERO -->
<section class="wc-hero">
  <h1>🏆 WORLD CUP EDITIONS</h1>
  <p>Legends. History. Glory.</p>
</section>

<!-- WORLD CUP PRODUCTS -->
<section class="products wc-products">
  <?php
  // Fetch World Cup jerseys from database
  $sql = "SELECT * FROM products WHERE category LIKE '%world cup%' OR category LIKE '%WC%' OR description LIKE '%World Cup%' ORDER BY season DESC";
  $result = $conn->query($sql);
  
  if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
          // Get main image
          $image_sql = "SELECT image_path FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1";
          $stmt = $conn->prepare($image_sql);
          $stmt->bind_param("i", $row['id']);
          $stmt->execute();
          $image_result = $stmt->get_result();
          $image_row = $image_result->fetch_assoc();
          $main_image = $image_row['image_path'] ?? 'default.jpg';
          
          // Get hover image (second image)
          $hover_sql = "SELECT image_path FROM product_images WHERE product_id = ? LIMIT 1 OFFSET 1";
          $stmt2 = $conn->prepare($hover_sql);
          $stmt2->bind_param("i", $row['id']);
          $stmt2->execute();
          $hover_result = $stmt2->get_result();
          $hover_row = $hover_result->fetch_assoc();
          $hover_image = $hover_row['image_path'] ?? $main_image;
          
          $stmt->close();
          $stmt2->close();
          
          // Calculate discount if exists
          $original_price = $row['price'];
          $discount_price = $row['discount_price'];
          $has_discount = $discount_price > 0 && $discount_price < $original_price;
          
          // Determine World Cup year for badge
          $wc_year = '';
          $wc_badge = '🏆 WC';
          if (preg_match('/(\d{4})/', $row['name'], $matches)) {
              $wc_year = $matches[1];
              $wc_badge = "🏆 WC " . $wc_year;
          } elseif (isset($row['season'])) {
              $wc_badge = "🏆 WC " . $row['season'];
          }
          
          // Check stock
          $stock_sql = "SELECT SUM(quantity) as total_stock FROM product_sizes WHERE product_id = ?";
          $stmt3 = $conn->prepare($stock_sql);
          $stmt3->bind_param("i", $row['id']);
          $stmt3->execute();
          $stock_result = $stmt3->get_result();
          $stock_row = $stock_result->fetch_assoc();
          $total_stock = $stock_row['total_stock'] ?? 0;
          $stmt3->close();
          ?>
          
          <div class="product-card wc-card" data-id="<?php echo $row['id']; ?>" data-wc-year="<?php echo $wc_year; ?>">
            <span class="wc-badge"><?php echo $wc_badge; ?></span>
            
            <?php if($has_discount): ?>
            <span class="sale">Sale</span>
            <?php endif; ?>
            
            <?php if($total_stock <= 0): ?>
            <span class="out-of-stock">Out of Stock</span>
            <?php elseif($total_stock < 10): ?>
            <span class="low-stock">Only <?php echo $total_stock; ?> left</span>
            <?php endif; ?>
            
            <a href="product.php?id=<?php echo $row['id']; ?>">
              <img src="<?php echo htmlspecialchars($main_image); ?>"
                   data-hover="<?php echo htmlspecialchars($hover_image); ?>"
                   alt="<?php echo htmlspecialchars($row['name']); ?>">
            </a>
            
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            
            <!-- Country/Team info -->
            <div class="team-info">
              <?php if(isset($row['team'])): ?>
              <small><?php echo htmlspecialchars($row['team']); ?> National Team</small>
              <?php endif; ?>
            </div>
            
            <!-- Quick view of available sizes -->
            <div class="quick-sizes">
              <?php
              $size_sql = "SELECT size FROM product_sizes WHERE product_id = ? AND quantity > 0 LIMIT 3";
              $stmt4 = $conn->prepare($size_sql);
              $stmt4->bind_param("i", $row['id']);
              $stmt4->execute();
              $size_result = $stmt4->get_result();
              $sizes = [];
              while($size_row = $size_result->fetch_assoc()) {
                  $sizes[] = $size_row['size'];
              }
              $stmt4->close();
              
              if(!empty($sizes)) {
                  echo '<small>Available: ' . implode(', ', $sizes) . '</small>';
              }
              ?>
            </div>
            
            <p class="price">
              <?php if($has_discount): ?>
              <del>LE <?php echo number_format($original_price, 2); ?></del>
              <span>LE <?php echo number_format($discount_price, 2); ?></span>
              <?php else: ?>
              <span>LE <?php echo number_format($original_price, 2); ?></span>
              <?php endif; ?>
            </p>
            
            <?php if($total_stock > 0): ?>
            <button class="add-to-cart" 
                    data-product='{
                        "id": <?php echo $row['id']; ?>,
                        "name": "<?php echo addslashes($row['name']); ?>",
                        "price": <?php echo ($has_discount ? $discount_price : $original_price); ?>,
                        "image": "<?php echo htmlspecialchars($main_image); ?>",
                        "team": "<?php echo addslashes($row['team'] ?? ''); ?>",
                        "wc_year": "<?php echo $wc_year; ?>",
                        "category": "world_cup"
                    }'>
              Add to Cart
            </button>
            <?php else: ?>
            <button class="add-to-cart" disabled>Out of Stock</button>
            <?php endif; ?>
            
            <a href="product.php?id=<?php echo $row['id']; ?>" class="quick-view">Quick View</a>
          </div>
          <?php
      }
  } else {
      // Show static World Cup products if database is empty
      ?>
      
      <div class="product-card wc-card" data-id="15" data-wc-year="2010">
        <span class="wc-badge">🏆 WC 2010</span>
        <img src="https://classic11.com/cdn/shop/files/IMG_20250603_123242_940x.jpg?v=1749026665"
            data-hover="https://classic11.com/cdn/shop/files/IMG_20170929_135418_1c61ca7c-ea7f-41bd-8240-0b3756de1a5d_940x.jpg?v=1749026665">
        <h3>Spain 2010 World Cup Jersey</h3>
        <p class="price"><del>LE 2500</del><span>LE 1550</span></p>
        <button class="add-to-cart"
          data-product='{"id": 15, "name": "Spain 2010 World Cup Jersey", "price": 1550, "image": "https://classic11.com/cdn/shop/files/IMG_20250603_123242_940x.jpg?v=1749026665"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card wc-card" data-id="16" data-wc-year="2002">
        <span class="wc-badge">🏆 WC 2002</span>
        <img src="https://classic11.com/cdn/shop/files/IMG_20241017_120525_940x.jpg?v=1729254918"
            data-hover="https://classic11.com/cdn/shop/files/IMG_20241017_120632_940x.jpg?v=1741791610">
        <h3>Brazil 2002 World Cup Jersey (Ronaldo)</h3>
        <p class="price"><del>LE 2800</del><span>LE 1800</span></p>
        <button class="add-to-cart"
          data-product='{"id": 16, "name": "Brazil 2002 World Cup Jersey (Ronaldo)", "price": 1800, "image": "https://classic11.com/cdn/shop/files/IMG_20241017_120525_940x.jpg?v=1729254918"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card wc-card" data-id="10" data-wc-year="1985">
        <span class="wc-badge">🏆 WC 85</span>
        <img src="https://classic11.com/cdn/shop/files/IMG_20251202_123753_940x.jpg?v=1764684621"
            data-hover="https://classic11.com/cdn/shop/files/IMG_20251202_123855_940x.jpg?v=1764684621">
        <h3>1985/86 France Home Football Shirt</h3>
        <p class="price"><del>LE 2800</del><span>LE 1700</span></p>
        <button class="add-to-cart"
          data-product='{"id": 10, "name": "1985/86 France Home Football Shirt", "price": 1700, "image": "https://classic11.com/cdn/shop/files/IMG_20251202_123753_940x.jpg?v=1764684621"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card wc-card" data-id="11" data-wc-year="2014">
        <span class="wc-badge">🏆 WC 2014</span>
        <img src="https://classic11.com/cdn/shop/files/20250916_134051_940x.jpg?v=1758102214"
            data-hover="https://classic11.com/cdn/shop/files/20250916_134059_940x.jpg?v=1764328735">
        <h3>Germany 2014 World Cup Jersey</h3>
        <p class="price"><del>LE 2600</del><span>LE 1600</span></p>
        <button class="add-to-cart"
          data-product='{"id": 11, "name": "Germany 2014 World Cup Jersey", "price": 1600, "image": "https://classic11.com/cdn/shop/files/20250916_134051_940x.jpg?v=1758102214"}'>
          Add to Cart
        </button>
      </div>
      
      <div class="product-card wc-card" data-id="12" data-wc-year="1993">
        <span class="wc-badge">🏆 WC 93</span>
        <img src="https://classic11.com/cdn/shop/files/IMG_20241011_125938_940x.jpg?v=1729071708"
            data-hover="https://classic11.com/cdn/shop/files/IMG_20241011_125941_940x.jpg?v=1755529412">
        <h3>1993/94 Brazil Home Football Shirt</h3>
        <p class="price"><del>LE 2600</del><span>LE 1600</span></p>
        <button class="add-to-cart"
          data-product='{"id": 12, "name": "1993/94 Brazil Home Football Shirt", "price": 1600, "image": "https://classic11.com/cdn/shop/files/IMG_20241011_125938_940x.jpg?v=1729071708"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card wc-card" data-id="13" data-wc-year="2022">
        <span class="wc-badge">🏆 WC 2022</span>
        <img src="https://image-cdn.hypb.st/https://hypebeast.com/image/2022/10/fifa-world-cup-2022-best-kits-uniforms-8.jpg?w=1260&format=jpeg&cbr=1&q=90&fit=max"
             data-hover="https://cdn.mos.cms.futurecdn.net/u8LEabDgwcpRcCYy4MvFo9.jpg">
        <h3>Japan 2022 World Cup Jersey</h3>
        <p class="price"><del>LE 2400</del><span>LE 1500</span></p>
        <button class="add-to-cart"
          data-product='{"id": 13, "name": "Japan 2022 World Cup Jersey", "price": 1500, "image": "https://image-cdn.hypb.st/https://hypebeast.com/image/2022/10/fifa-world-cup-2022-best-kits-uniforms-8.jpg?w=1260&format=jpeg&cbr=1&q=90&fit=max"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card wc-card" data-id="14" data-wc-year="1998">
        <span class="wc-badge">🏆 WC 1998</span>
        <img src="https://classic11.com/cdn/shop/files/IMG_20250728_115345.jpg?v=1753713902&width=1600"
            data-hover="https://classic11.com/cdn/shop/files/IMG_20250728_115400_940x.jpg?v=1753713984">
        <h3>Netherlands 1998 World Cup Jersey</h3>
        <p class="price"><del>LE 2200</del><span>LE 1400</span></p>
        <button class="add-to-cart"
          data-product='{"id": 14, "name": "Netherlands 1998 World Cup Jersey", "price": 1400, "image": "https://classic11.com/cdn/shop/files/IMG_20250728_115345.jpg?v=1753713902&width=1600"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card wc-card" data-id="9" data-wc-year="2026">
        <span class="wc-badge">🏆 WC 2026</span>
        <img src="https://assets.adidas.com/images/h_2000,f_auto,q_auto,fl_lossy,c_fill,g_auto/afa75f31d81e475599f87196a29956e2_9366/Argentina_26_Home_Messi_Jersey_White_KA8117_21_model.jpg"
            data-hover="https://assets.adidas.com/images/h_2000,f_auto,q_auto,fl_lossy,c_fill,g_auto/bd79d9df852a4c7580143efcd8e908cb_9366/Argentina_26_Home_Messi_Jersey_White_KA8117_23_model.jpg">
        <h3>Argentina 2026 World Cup Jersey</h3>
        <p class="price"><del>LE 3000</del><span>LE 2000</span></p>
        <button class="add-to-cart" 
          data-product='{"id": 9, "name": "Argentina 2026 World Cup Jersey", "price": 2000, "image": "https://assets.adidas.com/images/h_2000,f_auto,q_auto,fl_lossy,c_fill,g_auto/afa75f31d81e475599f87196a29956e2_9366/Argentina_26_Home_Messi_Jersey_White_KA8117_21_model.jpg"}'>
          Add to Cart
        </button>
      </div>
      <?php
  }
  ?>
</section>

<!-- CART MODAL -->
<div class="cart-modal" id="cartModal">
  <div class="cart-modal-content">
    <div class="cart-modal-header">
      <h2>Your Cart</h2>
      <button class="close-cart-btn" id="closeCartBtn">×</button>
    </div>
    <div class="cart-modal-body" id="cartModalBody">
      <?php if(empty($_SESSION['cart'])): ?>
        <div class="cart-modal-empty">
          <p>Your cart is empty</p>
        </div>
      <?php else: ?>
        <div id="cartItemsContainer">
          <!-- Items will be loaded via JavaScript -->
        </div>
      <?php endif; ?>
    </div>
    <div class="cart-modal-footer">
      <div class="cart-total">
        <span>Total:</span>
        <span id="modalCartTotal">LE 0.00</span>
      </div>
      <button class="view-cart-btn" onclick="window.location.href='cart.php'">
        View Full Cart
      </button>
    </div>
  </div>
</div>

<!-- FOOTER SECTION -->
<footer class="footer">
  <div class="footer-container">
    <!-- BRAND -->
    <div class="footer-brand">
      <h2>JERSEY<span>Wear</span></h2>
    </div>

    <!-- MENU -->
    <div class="footer-menu">
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
    </div>

    <!-- NEWSLETTER -->
    <div class="footer-newsletter">
      <h4>SUBSCRIBE TO OUR NEWSLETTER</h4>
      <p>Be the first to know about our newest arrivals,<br>
         special offers and store events near you!</p>
      <form method="POST" action="subscribe.php">
        <div class="newsletter-box">
          <input type="email" name="email" placeholder="Enter your email" required>
          <button type="submit">✉</button>
        </div>
      </form>
      <div class="footer-socials">
        <span>f</span>
        <span>📘</span>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© 2025 Jersey Wears</p>
    <p>Powered by Afroto</p>
  </div>
</footer>

<?php
// Close database connection
$conn->close();
?>
</body>
</html>