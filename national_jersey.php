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
<title>National Jerseys | Jersey Wear</title>
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

<!-- NATIONAL HERO -->
<section class="nation-hero">
  <h1>🌍 NATIONAL JERSEYS</h1>
  <p>Wear your country with pride</p>
</section>

<!-- NATIONAL PRODUCTS -->
<section class="products nation-products">
  <?php
  // Fetch hot offer products (products with discount > 20%)
  $sql = "SELECT * FROM products 
          WHERE discount_price > 0 
          AND discount_price < price 
          AND ((price - discount_price) / price * 100) >= 20 
          ORDER BY (price - discount_price) DESC";
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
          
          // Determine country flag and badge class
          $country = strtolower($row['team'] ?? '');
          $badge_class = $country ? $country . '-badge' : '';
          
          // Get country flag emoji
          $country_flags = [
              'brazil' => '🇧🇷 Brazil',
              'france' => '🇫🇷 France',
              'germany' => '🇩🇪 Germany',
              'argentina' => '🇦🇷 Argentina',
              'spain' => '🇪🇸 Spain',
              'england' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿 England',
              'portugal' => '🇵🇹 Portugal',
              'netherlands' => '🇳🇱 Netherlands',
              'italy' => '🇮🇹 Italy',
              'belgium' => '🇧🇪 Belgium'
          ];
          $country_display = $country_flags[$country] ?? $row['team'] ?? '';
          
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
          
          <div class="product-card nation-card" data-id="<?php echo $row['id']; ?>" data-country="<?php echo $country; ?>">
            <span class="nation-badge <?php echo $badge_class; ?>"><?php echo $country_display; ?></span>
            
            <?php if($has_discount): ?>
            <span class="sale">-<?php echo round((($original_price - $discount_price) / $original_price) * 100); ?>%</span>
            <?php endif; ?>
            
            <?php if($total_stock <= 0): ?>
            <span class="out-of-stock">Out of Stock</span>
            <?php elseif($total_stock < 15): ?>
            <span class="low-stock">Only <?php echo $total_stock; ?> left</span>
            <?php endif; ?>
            
            <a href="product.php?id=<?php echo $row['id']; ?>">
              <img src="<?php echo htmlspecialchars($main_image); ?>"
                   data-hover="<?php echo htmlspecialchars($hover_image); ?>"
                   alt="<?php echo htmlspecialchars($row['name']); ?>">
            </a>
            
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            
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
            
            <div class="country-info">
              <small>⭐ Official National Team Jersey</small>
            </div>
            
            <?php if($total_stock > 0): ?>
            <button class="add-to-cart" 
                    data-product='{
                        "id": <?php echo $row['id']; ?>,
                        "name": "<?php echo addslashes($row['name']); ?>",
                        "price": <?php echo ($has_discount ? $discount_price : $original_price); ?>,
                        "image": "<?php echo htmlspecialchars($main_image); ?>",
                        "country": "<?php echo addslashes($row['team'] ?? ''); ?>",
                        "category": "national"
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
      // Show static national team products if database is empty
      ?>
      
      <div class="product-card nation-card" data-id="33" data-country="brazil">
        <span class="nation-badge brazil-badge">🇧🇷 Brazil</span>
        <img src="https://classic11.com/cdn/shop/files/IMG_20240626_132034_940x.jpg?v=1719489444"
             data-hover="https://classic11.com/cdn/shop/files/IMG_20240626_132041_940x.jpg?v=1719489444">
        <h3>Brazil National Team Jersey</h3>
        <p class="price"><del>LE 2400</del><span>LE 1700</span></p>
        <button class="add-to-cart"
          data-product='{"id": 33, "name": "Brazil National Team Jersey", "price": 1700, "image": "https://classic11.com/cdn/shop/files/IMG_20240626_132034_940x.jpg?v=1719489444", "country": "Brazil"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card nation-card" data-id="34" data-country="france">
        <span class="nation-badge france-badge">🇫🇷 France</span>
        <img src="https://static.nike.com/a/images/t_default/6c5fd5b3-7e27-4b74-9bb0-1bca8b1b4b0c/france-2022-home-stadium-shirt.png"
             data-hover="https://assets.adidas.com/images/h_840,f_auto,q_auto,fl_lossy,c_fill,g_auto/49f43b963c9147c6a788afda00afe327_9366/France_22_Home_Jersey_Blue_HF0568_01_laydown.jpg">
        <h3>France National Team Jersey</h3>
        <p class="price"><del>LE 2400</del><span>LE 1650</span></p>
        <button class="add-to-cart"
          data-product='{"id": 34, "name": "France National Team Jersey", "price": 1650, "image": "https://static.nike.com/a/images/t_default/6c5fd5b3-7e27-4b74-9bb0-1bca8b1b4b0c/france-2022-home-stadium-shirt.png", "country": "France"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card nation-card" data-id="35" data-country="germany">
        <span class="nation-badge germany-badge">🇩🇪 Germany</span>
        <img src="https://classic11.com/cdn/shop/files/20251125_111509_940x.jpg?v=1764148845"
             data-hover="https://classic11.com/cdn/shop/files/20251125_111512_940x.jpg?v=1764148859">
        <h3>Germany National Team Jersey</h3>
        <p class="price"><del>LE 2300</del><span>LE 1600</span></p>
        <button class="add-to-cart"
          data-product='{"id": 35, "name": "Germany National Team Jersey", "price": 1600, "image": "https://classic11.com/cdn/shop/files/20251125_111509_940x.jpg?v=1764148845", "country": "Germany"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card nation-card" data-id="36" data-country="argentina">
        <span class="nation-badge argentina-badge">🇦🇷 Argentina</span>
        <img src="https://assets.adidas.com/images/w_600,f_auto,q_auto/5b569d0de97d4b05838aae71011245da_9366/Argentina_22_Home_Jersey_White_HF1457_01_laydown.jpg"
             data-hover="https://assets.adidas.com/images/h_840,f_auto,q_auto,fl_lossy,c_fill,g_auto/3f8c68fb4a1146c4853faf8e002f8fe3_9366/Argentina_22_Away_Jersey_Purple_HF1459_01_laydown.jpg">
        <h3>Argentina National Team Jersey</h3>
        <p class="price"><del>LE 2500</del><span>LE 1800</span></p>
        <button class="add-to-cart"
          data-product='{"id": 36, "name": "Argentina National Team Jersey", "price": 1800, "image": "https://assets.adidas.com/images/w_600,f_auto,q_auto/5b569d0de97d4b05838aae71011245da_9366/Argentina_22_Home_Jersey_White_HF1457_01_laydown.jpg", "country": "Argentina"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card nation-card" data-id="37" data-country="spain">
        <span class="nation-badge spain-badge">🇪🇸 Spain</span>
        <img src="https://assets.adidas.com/images/w_600,f_auto,q_auto/149ea3e8cbc34db5834caec400e75ba5_9366/Spain_22_Home_Jersey_Red_HF1478_01_laydown.jpg"
             data-hover="https://assets.adidas.com/images/h_840,f_auto,q_auto,fl_lossy,c_fill,g_auto/83eb03bf68b343f4b9c2af8e002fb386_9366/Spain_22_Away_Jersey_Blue_HF1479_01_laydown.jpg">
        <h3>Spain National Team Jersey</h3>
        <p class="price"><del>LE 2200</del><span>LE 1550</span></p>
        <button class="add-to-cart"
          data-product='{"id": 37, "name": "Spain National Team Jersey", "price": 1550, "image": "https://assets.adidas.com/images/w_600,f_auto,q_auto/149ea3e8cbc34db5834caec400e75ba5_9366/Spain_22_Home_Jersey_Red_HF1478_01_laydown.jpg", "country": "Spain"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card nation-card" data-id="38" data-country="england">
        <span class="nation-badge england-badge">🏴󠁧󠁢󠁥󠁮󠁧󠁿 England</span>
        <img src="https://static.nike.com/a/images/t_default/87e2d1b8-8b01-4f85-a8d7-9d1dfa17052e/england-2022-home-stadium-shirt.png"
             data-hover="https://assets.adidas.com/images/h_840,f_auto,q_auto,fl_lossy,c_fill,g_auto/52ae1e7ccd5a478ca015b112010e65ab_9366/England_2020_Home_Jersey_White_EF2783_01_laydown.jpg">
        <h3>England National Team Jersey</h3>
        <p class="price"><del>LE 2300</del><span>LE 1650</span></p>
        <button class="add-to-cart"
          data-product='{"id": 38, "name": "England National Team Jersey", "price": 1650, "image": "https://static.nike.com/a/images/t_default/87e2d1b8-8b01-4f85-a8d7-9d1dfa17052e/england-2022-home-stadium-shirt.png", "country": "England"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card nation-card" data-id="39" data-country="portugal">
        <span class="nation-badge portugal-badge">🇵🇹 Portugal</span>
        <img src="https://assets.adidas.com/images/w_600,f_auto,q_auto/09c7eb83180b4c518029af8e002fc95b_9366/Portugal_22_Home_Jersey_Red_HF1498_01_laydown.jpg"
             data-hover="https://assets.adidas.com/images/h_840,f_auto,q_auto,fl_lossy,c_fill,g_auto/0c6c8d07b4e6449f89c5af8e002fcfe6_9366/Portugal_22_Away_Jersey_White_HF1499_01_laydown.jpg">
        <h3>Portugal National Team Jersey</h3>
        <p class="price"><del>LE 2250</del><span>LE 1600</span></p>
        <button class="add-to-cart"
          data-product='{"id": 39, "name": "Portugal National Team Jersey", "price": 1600, "image": "https://assets.adidas.com/images/w_600,f_auto,q_auto/09c7eb83180b4c518029af8e002fc95b_9366/Portugal_22_Home_Jersey_Red_HF1498_01_laydown.jpg", "country": "Portugal"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card nation-card" data-id="40" data-country="netherlands">
        <span class="nation-badge netherlands-badge">🇳🇱 Netherlands</span>
        <img src="https://assets.adidas.com/images/w_600,f_auto,q_auto/bac2b3b57a724ee18a53af8e002fd670_9366/Netherlands_22_Home_Jersey_Orange_HF1477_01_laydown.jpg"
             data-hover="https://assets.adidas.com/images/h_840,f_auto,q_auto,fl_lossy,c_fill,g_auto/67358aa8ac024f4f82a1af8e002fdaa0_9366/Netherlands_22_Away_Jersey_Blue_HF1476_01_laydown.jpg">
        <h3>Netherlands National Team Jersey</h3>
        <p class="price"><del>LE 2200</del><span>LE 1550</span></p>
        <button class="add-to-cart"
          data-product='{"id": 40, "name": "Netherlands National Team Jersey", "price": 1550, "image": "https://assets.adidas.com/images/w_600,f_auto,q_auto/bac2b3b57a724ee18a53af8e002fd670_9366/Netherlands_22_Home_Jersey_Orange_HF1477_01_laydown.jpg", "country": "Netherlands"}'>
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

<!-- FOOTER -->
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