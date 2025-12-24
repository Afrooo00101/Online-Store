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
<title>Iconic Jerseys | Jersey Wear</title>
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

<!-- ICONIC HERO -->
<section class="iconic-hero">
  <h1>🏆 ICONIC JERSEYS</h1>
  <p>Legends never fade</p>
</section>

<!-- ICONIC PRODUCTS -->
<section class="products iconic-products">
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
          
          // Determine badge type based on era or status
          $badge_class = 'classic';
          $badge_text = 'CLASSIC';
          
          if (isset($row['era'])) {
              if (strpos($row['era'], '1960') !== false || strpos($row['era'], '1970') !== false) {
                  $badge_class = 'legendary';
                  $badge_text = 'LEGENDARY';
              } elseif (strpos($row['era'], '1980') !== false) {
                  $badge_class = 'goat';
                  $badge_text = 'GOAT ERA';
              } elseif (strpos($row['era'], '1990') !== false) {
                  $badge_class = 'champion';
                  $badge_text = strtoupper($row['team'] ?? '') . ' GLORY';
              } elseif (strpos($row['era'], '2000') !== false) {
                  if (strpos($row['name'], 'Champions') !== false || strpos($row['category'], 'UCL') !== false) {
                      $badge_class = 'ucl';
                      $badge_text = 'UCL LEGEND';
                  } else {
                      $badge_class = 'legendary';
                      $badge_text = strtoupper($row['team'] ?? '') . ' LEGEND';
                  }
              }
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
          
          <div class="product-card iconic-card" data-id="<?php echo $row['id']; ?>" data-era="<?php echo htmlspecialchars($row['era'] ?? ''); ?>">
            <span class="iconic-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
            
            <?php if($has_discount): ?>
            <span class="sale">Sale</span>
            <?php endif; ?>
            
            <?php if($total_stock <= 0): ?>
            <span class="out-of-stock">Out of Stock</span>
            <?php elseif($total_stock < 5): ?>
            <span class="low-stock">Only <?php echo $total_stock; ?> left</span>
            <?php endif; ?>
            
            <a href="product.php?id=<?php echo $row['id']; ?>">
              <img src="<?php echo htmlspecialchars($main_image); ?>"
                   data-hover="<?php echo htmlspecialchars($hover_image); ?>"
                   alt="<?php echo htmlspecialchars($row['name']); ?>">
            </a>
            
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            
            <!-- Era info -->
            <div class="era-info">
              <?php if(isset($row['era'])): ?>
              <small><?php echo htmlspecialchars($row['era']); ?> ERA</small>
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
                        "era": "<?php echo addslashes($row['era'] ?? ''); ?>",
                        "status": "iconic"
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
      // Show static iconic products if database is empty
      ?>
      
      <!-- Brazil 1970 -->
      <div class="product-card iconic-card" data-id="41" data-era="2000s">
        <span class="iconic-badge legendary">LEGENDARY</span>
        <img src="https://i.pinimg.com/originals/32/d3/f6/32d3f63602b369123e816e37746e3951.jpg"
             data-hover="https://classic11.com/cdn/shop/files/IMG_20251117_111516_940x.jpg?v=1763462528">
        <h3>2002/03 Arsenal Home Football Jersey</h3>
        <p class="price"><del>LE 3000</del><span>LE 2100</span></p>
        <button class="add-to-cart"
          data-product='{"id": 41, "name": "2002/03 Arsenal Home Football Jersey", "price": 2100, "image": "https://classic11.com/cdn/shop/files/IMG_20250410_132139_940x.jpg?v=1760599933", "era": "2000s"}'>
          Add to Cart
        </button>
      </div>

      <!-- Argentina 1986 -->
      <div class="product-card iconic-card" data-id="42" data-era="1980s">
        <span class="iconic-badge goat">GOAT ERA</span>
        <img src="https://i.pinimg.com/736x/bb/1d/ab/bb1dab870b40c8015f04035552205f17.jpg"
             data-hover="https://i.etsystatic.com/21538045/r/il/166a55/2300421076/il_1588xN.2300421076_ktd4.jpg">
        <h3>Argentina 1986 Maradona Jersey</h3>
        <p class="price"><del>LE 3200</del><span>LE 2300</span></p>
        <button class="add-to-cart"
          data-product='{"id": 42, "name": "Argentina 1986 Maradona Jersey", "price": 2300, "image": "https://classicfootballshirts.co.uk/cdn/shop/products/1986-Argentina-Home-Shirt_0.jpg?v=1639414354", "era": "1980s"}'>
          Add to Cart
        </button>
      </div>

      <!-- France 1998 -->
      <div class="product-card iconic-card" data-id="43" data-era="1990s">
        <span class="iconic-badge champion">WORLD CHAMPION</span>
        <img src="https://media.gettyimages.com/id/989647398/photo/world-cup-2002-preview-zidane-zinedine-coupe-du-monde-wereld-beker-france-frankrijk.jpg?s=612x612&w=gi&k=20&c=eGLfRBb0Wug8gZMr8AsPzdKP_nq50zgSbDUsz6XcM_A="
             data-hover="https://classic11.com/cdn/shop/files/IMG_20250916_114930_940x.jpg?v=1758031390">
        <h3>France 1998 Zidane Jersey</h3>
        <p class="price"><del>LE 2900</del><span>LE 2000</span></p>
        <button class="add-to-cart"
          data-product='{"id": 43, "name": "France 1998 Zidane Jersey", "price": 2000, "image": "https://media.gettyimages.com/id/989647398/photo/world-cup-2002-preview-zidane-zinedine-coupe-du-monde-wereld-beker-france-frankrijk.jpg?s=612x612&w=gi&k=20&c=eGLfRBb0Wug8gZMr8AsPzdKP_nq50zgSbDUsz6XcM_A=", "era": "1990s"}'>
          Add to Cart
        </button>
      </div>

      <!-- AC Milan 2007 -->
      <div class="product-card iconic-card" data-id="44" data-era="2000s">
        <span class="iconic-badge ucl">UCL LEGEND</span>
        <img src="https://editorial01.shutterstock.com/preview/8153401q/62f8b7a5/Shutterstock_8153401q.jpg"
             data-hover="https://i.etsystatic.com/27428654/r/il/df74dd/4428272588/il_fullxfull.4428272588_ipvw.jpg">
        <h3>AC Milan 2007 Champions Ronaldinho Jersey</h3>
        <p class="price"><del>LE 2800</del><span>LE 1950</span></p>
        <button class="add-to-cart"
          data-product='{"id": 44, "name": "AC Milan 2007 Champions Jersey", "price": 1950, "image": "https://editorial01.shutterstock.com/preview/8153401q/62f8b7a5/Shutterstock_8153401q.jpg", "era": "2000s"}'>
          Add to Cart
        </button>
      </div>

      <!-- Man United 1999 -->
      <div class="product-card iconic-card" data-id="47" data-era="1990s">
        <span class="iconic-badge champion">MAN UNITED GLORY</span>
        <img src="https://pbs.twimg.com/media/C-z3oLJW0AAcsni.jpg"
             data-hover="https://tse1.mm.bing.net/th/id/OIP.GT5rdOGx5qJS3ibe4i0jWQHaJB?cb=ucfimg2&ucfimg=1&w=887&h=1080&rs=1&pid=ImgDetMain&o=7&rm=3">
        <h3>Man United 1999 Beckham Treble Jersey</h3>
        <p class="price"><del>LE 2850</del><span>LE 2050</span></p>
        <button class="add-to-cart"
          data-product='{"id": 47, "name": "Man United 1999 Beckham Treble Jersey", "price": 2050, "image": "https://classicfootballshirts.co.uk/cdn/shop/products/1999-Manchester-United-Home-Shirt_0.jpg?v=1639414125", "era": "1990s"}'>
          Add to Cart
        </button>
      </div>

      <!-- Barcelona 2009 -->
      <div class="product-card iconic-card" data-id="46" data-era="2000s">
        <span class="iconic-badge legendary">BARCELONA LEGEND</span>
        <img src="https://tse4.mm.bing.net/th/id/OIP.ORJGCRpFFR7mFiXIU6_6DgHaIM?cb=ucfimg2&ucfimg=1&rs=1&pid=ImgDetMain&o=7&rm=3"
             data-hover="https://www.foreversoccerjerseys.com/cdn/shop/products/messi-barcelona-treble-season-2008-2009-uefa-final-home-soccer-jersey-shirt-l-sku-286784-655-669994_1024x1024.jpg?v=1696390667">
        <h3>Barcelona 2009 Messi Jersey</h3>
        <p class="price"><del>LE 2700</del><span>LE 1900</span></p>
        <button class="add-to-cart"
          data-product='{"id": 46, "name": "Barcelona 2009 Messi Jersey", "price": 1900, "image": "https://tse4.mm.bing.net/th/id/OIP.ORJGCRpFFR7mFiXIU6_6DgHaIM?cb=ucfimg2&ucfimg=1&rs=1&pid=ImgDetMain&o=7&rm=3", "era": "2000s"}'>
          Add to Cart
        </button>
      </div>

      <!-- England 1966 -->
      <div class="product-card iconic-card" data-id="45" data-era="1960s">
        <span class="iconic-badge classic">CLASSIC</span>
        <img src="https://www.scoredraw.com/siteimg/extrapics/4117.jpg"
             data-hover="https://tse3.mm.bing.net/th/id/OIP.R5aa8LB9bQecMa9upfRGoQHaHa?cb=ucfimg2&pid=ImgDet&ucfimg=1&w=474&h=474&rs=1&o=7&rm=3">
        <h3>England 1966 World Cup Jersey</h3>
        <p class="price"><del>LE 3100</del><span>LE 2200</span></p>
        <button class="add-to-cart"
          data-product='{"id": 45, "name": "England 1966 World Cup Jersey", "price": 2200, "image": "https://classicfootballshirts.co.uk/cdn/shop/products/1966-England-Home-Shirt_0.jpg?v=1639414212", "era": "1960s"}'>
          Add to Cart
        </button>
      </div>

      <!-- Italy 2006 -->
      <div class="product-card iconic-card" data-id="48" data-era="2000s">
        <span class="iconic-badge classic">ITALIAN MASTERPIECE</span>
        <img src="https://jersiretro.store/wp-content/uploads/2022/10/Totti-2006-world-cup-win.jpg"
             data-hover="https://championfc.net/wp-content/uploads/2022/04/53d9d2f6.jpg">
        <h3>Italy 2006 World Cup Jersey</h3>
        <p class="price"><del>LE 2950</del><span>LE 2150</span></p>
        <button class="add-to-cart"
          data-product='{"id": 48, "name": "Italy 2006 World Cup Jersey", "price": 2150, "image": "https://classicfootballshirts.co.uk/cdn/shop/products/2006-Italy-Home-Shirt_0.jpg?v=1639414083", "era": "2000s"}'>
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