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
<title>Special Editions | Jersey Wear</title>
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

<!-- SPECIAL HERO -->
<section class="special-hero">
  <h1>🌟 SPECIAL EDITIONS 🌟</h1>
  <p>Exclusive jerseys for true fans</p>
</section>

<!-- SPECIAL PRODUCTS -->
<section class="products special-products">
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
          
          // Determine badge type
          $badge_text = 'Premium';
          if (strpos(strtolower($row['name']), 'exclusive') !== false) {
              $badge_text = 'Exclusive';
          } elseif (strpos(strtolower($row['name']), 'limited') !== false || strpos(strtolower($row['name']), 'collector') !== false) {
              $badge_text = 'Limited';
          } elseif (strpos(strtolower($row['name']), 'rare') !== false) {
              $badge_text = 'Rare';
          }
          
          // Check stock (special editions usually have limited stock)
          $stock_sql = "SELECT SUM(quantity) as total_stock FROM product_sizes WHERE product_id = ?";
          $stmt3 = $conn->prepare($stock_sql);
          $stmt3->bind_param("i", $row['id']);
          $stmt3->execute();
          $stock_result = $stmt3->get_result();
          $stock_row = $stock_result->fetch_assoc();
          $total_stock = $stock_row['total_stock'] ?? 0;
          $stmt3->close();
          ?>
          
          <div class="product-card special-card" data-id="<?php echo $row['id']; ?>">
            <span class="premium-badge">🌟 <?php echo $badge_text; ?></span>
            
            <?php if($has_discount): ?>
            <span class="sale">-<?php echo round((($original_price - $discount_price) / $original_price) * 100); ?>%</span>
            <?php endif; ?>
            
            <?php if($total_stock <= 0): ?>
            <span class="out-of-stock">Sold Out</span>
            <?php elseif($total_stock < 5): ?>
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
            
            <div class="special-features">
              <small>⭐ Limited Edition | ⭐ Premium Material</small>
            </div>
            
            <?php if($total_stock > 0): ?>
            <button class="add-to-cart" 
                    data-product='{
                        "id": <?php echo $row['id']; ?>,
                        "name": "<?php echo addslashes($row['name']); ?>",
                        "price": <?php echo ($has_discount ? $discount_price : $original_price); ?>,
                        "image": "<?php echo htmlspecialchars($main_image); ?>",
                        "category": "special",
                        "badge": "<?php echo $badge_text; ?>"
                    }'>
              Add to Cart
            </button>
            <?php else: ?>
            <button class="add-to-cart" disabled>Sold Out</button>
            <?php endif; ?>
            
            <a href="product.php?id=<?php echo $row['id']; ?>" class="quick-view">Quick View</a>
          </div>
          <?php
      }
  } else {
      // Show static special edition products if database is empty
      ?>
      
      <div class="product-card special-card" data-id="17">
        <span class="premium-badge">🌟 Premium</span>
        <img src="https://store.fcbarcelona.com/cdn/shop/files/BZ3A8998.jpg?v=1761032162&width=1200"
            data-hover="https://store.fcbarcelona.com/cdn/shop/files/BZ3A9007.jpg?v=1761032172&width=1200">
        <h3>Christmas Barcelona Blover</h3>
        <p class="price"><del>LE 4500</del><span>LE 3800</span></p>
        <button class="add-to-cart" 
          data-product='{"id": 17, "name": "Christmas Barcelona Blover", "price": 3800, "image": "https://store.fcbarcelona.com/cdn/shop/files/BZ3A8998.jpg?v=1761032162&width=1200"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card special-card" data-id="18">
        <span class="premium-badge">🌟 Premium</span>
        <img src="https://store.liverpoolfc.com/media/catalog/product/cache/a8585741965541bd35c89e2a8929f2a6/j/v/jv6423_5_apparel_on_model_walking_view_white.jpg"
             data-hover="https://store.liverpoolfc.com/media/catalog/product/a/d/adobe_express_-_file_42__1.png">
        <h3>Liverpool Premium Champions League Edition Jersey</h3>
        <p class="price"><del>LE 4000</del><span>LE 3200</span></p>
        <button class="add-to-cart"
          data-product='{"id": 18, "name": "Liverpool Premium Champions League Edition Jersey", "price": 3200, "image": "https://store.liverpoolfc.com/media/catalog/product/cache/a8585741965541bd35c89e2a8929f2a6/j/v/jv6423_5_apparel_on_model_walking_view_white.jpg"}'>
          Add to Cart
        </button>
      </div>
      
      <div class="product-card special-card" data-id="19">
        <span class="premium-badge">🌟 Premium</span>
        <img src="https://us.shop.realmadrid.com/_next/image?url=https%3A%2F%2Flegends.broadleafcloud.com%2Fapi%2Fasset%2Fcontent%2FJZ9016_01.jpg%3FcontextRequest%3D%257B%2522forceCatalogForFetch%2522%3Afalse%2C%2522forceFilterByCatalogIncludeInheritance%2522%3Afalse%2C%2522forceFilterByCatalogExcludeInheritance%2522%3Afalse%2C%2522applicationId%2522%3A%252201H4RD9NXMKQBQ1WVKM1181VD8%2522%2C%2522tenantId%2522%3A%2522REAL_MADRID%2522%257D&w=1920&q=75"
             data-hover="https://us.shop.realmadrid.com/_next/image?url=https%3A%2F%2Flegends.broadleafcloud.com%2Fapi%2Fasset%2Fcontent%2FRMCFYZ0102_03.jpg%3FcontextRequest%3D%257B%2522forceCatalogForFetch%2522%3Afalse%2C%2522forceFilterByCatalogIncludeInheritance%2522%3Afalse%2C%2522forceFilterByCatalogExcludeInheritance%2522%3Afalse%2C%2522applicationId%2522%3A%252201H4RD9NXMKQBQ1WVKM1181VD8%2522%2C%2522tenantId%2522%3A%2522REAL_MADRID%2522%257D&w=1920&q=75">
        <h3>Real Madrid x Marvel Edition</h3>
        <p class="price"><del>LE 4000</del><span>LE 3200</span></p>
        <button class="add-to-cart"
          data-product='{"id": 19, "name": "Real Madrid x Marvel Edition", "price": 3200, "image": "https://us.shop.realmadrid.com/_next/image?url=https%3A%2F%2Flegends.broadleafcloud.com%2Fapi%2Fasset%2Fcontent%2FJZ9016_01.jpg%3FcontextRequest%3D%257B%2522forceCatalogForFetch%2522%3Afalse%2C%2522forceFilterByCatalogIncludeInheritance%2522%3Afalse%2C%2522forceFilterByCatalogExcludeInheritance%2522%3Afalse%2C%2522applicationId%2522%3A%252201H4RD9NXMKQBQ1WVKM1181VD8%2522%2C%2522tenantId%2522%3A%2522REAL_MADRID%2522%257D&w=1920&q=75"}'>
          Add to Cart
        </button>
      </div>
      
      <div class="product-card special-card" data-id="20">
        <span class="premium-badge">🌟 Premium</span>
        <img src="https://i1.adis.ws/i/ArsenalDirect/mkb1825_f?$pdpMainZoomImage$"
             data-hover="https://i1.adis.ws/i/ArsenalDirect/mkb1825_b?$pdpMainZoomImage$">
        <h3>Arsenal Training Jersey</h3>
        <p class="price"><del>LE 4000</del><span>LE 3200</span></p>
        <button class="add-to-cart"
          data-product='{"id": 20, "name": "Arsenal Training Jersey", "price": 3200, "image": "https://i1.adis.ws/i/ArsenalDirect/mkb1825_f?$pdpMainZoomImage$"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card special-card" data-id="21">
        <span class="premium-badge">🌟 Exclusive</span>
        <img src="https://i0.wp.com/thefootballshirt.com/wp-content/uploads/2023/07/Milan-2023-24-Fourth-Shirt-Black-White-Gradient-3-1.jpg"
             data-hover="https://i0.wp.com/thefootballshirt.com/wp-content/uploads/2023/07/Milan-2023-24-Fourth-Shirt-Black-White-Gradient.jpg">
        <h3>AC Milan Gradient Edition</h3>
        <p class="price"><del>LE 3500</del><span>LE 2900</span></p>
        <button class="add-to-cart"
          data-product='{"id": 21, "name": "AC Milan Gradient Edition", "price": 2900, "image": "https://i0.wp.com/thefootballshirt.com/wp-content/uploads/2023/07/Milan-2023-24-Fourth-Shirt-Black-White-Gradient-3-1.jpg"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card special-card" data-id="22">
        <span class="premium-badge">🌟 Limited</span>
        <img src="https://cdn.shopify.com/s/files/1/0788/3561/5725/products/Chelsea2024-25Homekit3.jpg"
             data-hover="https://cdn.shopify.com/s/files/1/0788/3561/5725/products/Chelsea2024-25Homekit1.jpg">
        <h3>Chelsea Anniversary Edition</h3>
        <p class="price"><del>LE 3800</del><span>LE 3100</span></p>
        <button class="add-to-cart"
          data-product='{"id": 22, "name": "Chelsea Anniversary Edition", "price": 3100, "image": "https://cdn.shopify.com/s/files/1/0788/3561/5725/products/Chelsea2024-25Homekit3.jpg"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card special-card" data-id="23">
        <span class="premium-badge">🌟 Collector's</span>
        <img src="https://anticajerseys.com/cdn/shop/files/11CA334E-2F47-476B-B595-F9A144B03266.jpg?v=1747428435&width=990"
             data-hover="https://anticajerseys.com/cdn/shop/files/413D9BAA-1728-416A-9EA1-2B1B2625BA8D.jpg?v=1747428435&width=990">
        <h3>Japan "Itachi" Edition Jersey</h3>
        <p class="price"><del>LE 4200</del><span>LE 3400</span></p>
        <button class="add-to-cart"
            data-product='{"id": 23, "name": "Japan \"Itachi\" Edition Jersey", "price": 3400, "image": "https://anticajerseys.com/cdn/shop/files/11CA334E-2F47-476B-B595-F9A144B03266.jpg?v=1747428435&width=990"}'>
            Add to Cart
          </button>
      </div>

      <div class="product-card special-card" data-id="24">
        <span class="premium-badge">🌟 Rare</span>
        <img src="https://anticajerseys.com/cdn/shop/files/Ajax_bob_marley_special_edition_jersey.jpg?v=1722691736&width=990"
             data-hover="https://anticajerseys.com/cdn/shop/files/ajaxbob.jpg?v=1721428551&width=990">
        <h3>Ajax Bob Marley Special Edition</h3>
        <p class="price"><del>LE 3900</del><span>LE 3300</span></p>
        <button class="add-to-cart"
          data-product='{"id": 24, "name": "Ajax Bob Marley Special Edition", "price": 3300, "image": "https://anticajerseys.com/cdn/shop/files/Ajax_bob_marley_special_edition_jersey.jpg?v=1722691736&width=990"}'>
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