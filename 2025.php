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
<title>2025 Season Jerseys | Jersey Wear</title>
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

<!-- 2025 SEASON HERO -->
<section class="season2025-hero">
  <h1>🌟 2025 SEASON 🌟</h1>
  <p>Latest kits for the new season</p>
</section>

<!-- 2025 SEASON PRODUCTS -->
<section class="products season2025-products">
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
          
          // Determine team for badge
          $team = strtolower($row['team'] ?? '');
          $badge_class = $team ? $team . '-badge' : '';
          
          // Get team name for display
          $team_names = [
              'barcelona' => 'FC Barcelona',
              'liverpool' => 'Liverpool FC',
              'mancity' => 'Man City',
              'realmadrid' => 'Real Madrid',
              'arsenal' => 'Arsenal',
              'bayern' => 'Bayern Munich',
              'psg' => 'Paris Saint-Germain',
              'chelsea' => 'Chelsea FC'
          ];
          $team_display = $team_names[$team] ?? $row['team'] ?? '';
          ?>
          
          <div class="product-card season2025-card" data-id="<?php echo $row['id']; ?>" data-team="<?php echo $team; ?>">
            <?php if($team_display): ?>
            <span class="team-badge <?php echo $badge_class; ?>"><?php echo $team_display; ?></span>
            <?php endif; ?>
            
            <?php if($has_discount): ?>
            <span class="sale">Sale</span>
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
              $stmt3 = $conn->prepare($size_sql);
              $stmt3->bind_param("i", $row['id']);
              $stmt3->execute();
              $size_result = $stmt3->get_result();
              $sizes = [];
              while($size_row = $size_result->fetch_assoc()) {
                  $sizes[] = $size_row['size'];
              }
              $stmt3->close();
              
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
            
            <?php 
            // Check stock
            $stock_sql = "SELECT SUM(quantity) as total_stock FROM product_sizes WHERE product_id = ?";
            $stmt4 = $conn->prepare($stock_sql);
            $stmt4->bind_param("i", $row['id']);
            $stmt4->execute();
            $stock_result = $stmt4->get_result();
            $stock_row = $stock_result->fetch_assoc();
            $total_stock = $stock_row['total_stock'] ?? 0;
            $stmt4->close();
            ?>
            
            <?php if($total_stock > 0): ?>
            <button class="add-to-cart" 
                    data-product='{
                        "id": <?php echo $row['id']; ?>,
                        "name": "<?php echo addslashes($row['name']); ?>",
                        "price": <?php echo ($has_discount ? $discount_price : $original_price); ?>,
                        "image": "<?php echo htmlspecialchars($main_image); ?>",
                        "team": "<?php echo addslashes($row['team'] ?? ''); ?>",
                        "season": "2025"
                    }'>
              Add to Cart
            </button>
            <?php else: ?>
            <button class="add-to-cart" disabled>Out of Stock</button>
            <?php endif; ?>
          </div>
          <?php
      }
  } else {
      // Show static 2025 products if database is empty
      ?>
      
      <!-- Barcelona 2025 -->
      <div class="product-card season2025-card" data-id="49" data-team="barcelona">
        <span class="team-badge barcelona-badge">FC Barcelona</span>
        <img src="https://store.fcbarcelona.com/cdn/shop/files/HJ4590-456_415227879_D_A_1X1_2laliga_92b83d62-53fe-4728-b143-3b8653e39427.jpg?v=1763654921&width=1200"
             data-hover="https://store.fcbarcelona.com/cdn/shop/files/HJ4590-456_415227879_D_B_1X1_2f6fc8bb-b155-4c63-98b5-73f13ed84d05.jpg?v=1763654921&width=1200">
        <h3>Barcelona 2025/26 Spotify Home Kit</h3>
        <p class="price"><del>LE 2000</del><span>LE 1800</span></p>
        <button class="add-to-cart" 
          data-product='{"id": 49, "name": "Barcelona 2025/26 Spotify Home Kit", "price": 1800, "image": "https://store.fcbarcelona.com/cdn/shop/files/FCB_ED_Lamine_Jersey1_4x5_b3d54315-cc08-4fa9-b780-a72573ac3ca5.jpg?v=1761211187&width=1200", "team": "Barcelona", "season": "2025"}'>
          Add to Cart
        </button>
      </div>

      <!-- Liverpool 2025 -->
      <div class="product-card season2025-card" data-id="50" data-team="liverpool">
        <span class="team-badge liverpool-badge">Liverpool FC</span>
        <img src="https://store.liverpoolfc.com/media/catalog/product/cache/6e0c7b53c0ed72fe014b8d12b60d479c/j/y/jy4237_vvdcrop_14-1.png"
             data-hover="https://store.liverpoolfc.com/media/catalog/product/cache/6e0c7b53c0ed72fe014b8d12b60d479c/j/y/jy4237_00118_1.jpg">
        <h3>Liverpool 2025/26 Home Kit</h3>
        <p class="price"><del>LE 2400</del><span>LE 1650</span></p>
        <button class="add-to-cart"
          data-product='{"id": 50, "name": "Liverpool 2025/26 Home Kit", "price": 1650, "image": "https://store.liverpoolfc.com/media/catalog/product/cache/6e0c7b53c0ed72fe014b8d12b60d479c/j/y/jy4237_vvdcrop_14-1.png", "team": "Liverpool", "season": "2025"}'>
          Add to Cart
        </button>
      </div>

      <!-- Manchester City 2025 -->
      <div class="product-card season2025-card" data-id="51" data-team="mancity">
        <span class="team-badge mancity-badge">Man City</span>
        <img src="https://shop.mancity.com/dw/image/v2/BDWJ_PRD/on/demandware.static/-/Sites-master-catalog-MAN/default/dw53bc1f08/images/large/701237131001_pp_01_mcfc.png?sw=1600&sh=1600&sm=fit"
             data-hover="https://shop.mancity.com/dw/image/v2/BDWJ_PRD/on/demandware.static/-/Sites-master-catalog-MAN/default/dw0027749d/images/large/701237131001_pp_02_mcfc.png?sw=1600&sh=1600&sm=fit">
        <h3>Manchester City 2025/26 Home Kit</h3>
        <p class="price"><del>LE 2000</del><span>LE 1800</span></p>
        <button class="add-to-cart"
          data-product='{"id": 51, "name": "Manchester City 2025/26 Home Kit", "price": 1800, "image": "https://shop.mancity.com/dw/image/v2/BDWJ_PRD/on/demandware.static/-/Sites-master-catalog-MAN/default/dw53bc1f08/images/large/701237131001_pp_01_mcfc.png?sw=1600&sh=1600&sm=fit", "team": "Manchester City", "season": "2025"}'>
          Add to Cart
        </button>
      </div>

      <!-- Real Madrid 2025 -->
      <div class="product-card season2025-card" data-id="52" data-team="realmadrid">
        <span class="team-badge realmadrid-badge">Real Madrid</span>
        <img src="https://us.shop.realmadrid.com/_next/image?url=https%3A%2F%2Fimages.ctfassets.net%2F7nqb12anqb19%2F17yiNVkP7YE2CPvGHQz4tH%2F2624b451883ee295d2df4a8db646b0b5%2FDESKTOP-MBAPPE.jpg&w=640&q=75"
             data-hover="https://us.shop.realmadrid.com/_next/image?url=https%3A%2F%2Fimages.ctfassets.net%2F7nqb12anqb19%2F7Kz93Q0LjMfoIawQ4rXEz%2F070c767fe8bf72c4d43c4af961cc5cef%2F0.png&w=256&q=75">
        <h3>Real Madrid 2025/26 Home Kit</h3>
        <p class="price"><del>LE 2200</del><span>LE 1900</span></p>
        <button class="add-to-cart"
          data-product='{"id": 52, "name": "Real Madrid 2025/26 Home Kit", "price": 1900, "image": "https://us.shop.realmadrid.com/_next/image?url=https%3A%2F%2Flegends.broadleafcloud.com%2Fapi%2Fasset%2Fcontent%2FJZ9016_01.jpg%3FcontextRequest%3D%257B%2522forceCatalogForFetch%2522%3Afalse%2C%2522forceFilterByCatalogIncludeInheritance%2522%3Afalse%2C%2522forceFilterByCatalogExcludeInheritance%2522%3Afalse%2C%2522applicationId%2522%3A%252201H4RD9NXMKQBQ1WVKM1181VD8%2522%2C%2522tenantId%2522%3A%2522REAL_MADRID%2522%257D&w=1920&q=75", "team": "Real Madrid", "season": "2025"}'>
          Add to Cart
        </button>
      </div>

      <!-- Arsenal 2025 -->
      <div class="product-card season2025-card" data-id="53" data-team="arsenal">
        <span class="team-badge arsenal-badge">Arsenal</span>
        <img src="https://i1.adis.ws/i/ArsenalDirect/mji9516_f?$pdpMainImage$"
             data-hover="https://i1.adis.ws/i/ArsenalDirect/mji9516_b?$pdpMainZoomImage$">
        <h3>Arsenal 2025/26 Home Kit</h3>
        <p class="price"><del>LE 2100</del><span>LE 1750</span></p>
        <button class="add-to-cart"
          data-product='{"id": 53, "name": "Arsenal 2025/26 Home Kit", "price": 1750, "image": "https://i1.adis.ws/i/ArsenalDirect/mkb1825_f?$pdpMainZoomImage$", "team": "Arsenal", "season": "2025"}'>
          Add to Cart
        </button>
      </div>

      <!-- Bayern Munich 2025 -->
      <div class="product-card season2025-card" data-id="54" data-team="bayern">
        <span class="team-badge bayern-badge">Bayern Munich</span>
        <img src="https://shop.fcbayern.com/dw/image/v2/BDVB_PRD/on/demandware.static/-/Sites-master-catalog/default/dwefef9ecb/images/large/701242072001_pp_01_fcb.jpg?sw=1600&sh=1600&sm=fit"
             data-hover="https://shop.fcbayern.com/dw/image/v2/BDVB_PRD/on/demandware.static/-/Sites-master-catalog/default/dw74f2c862/images/large/701242072001_pp_02_fcb.jpg?sw=1600&sh=1600&sm=fit">
        <h3>Bayern Munich 2025/26 Home Kit</h3>
        <p class="price"><del>LE 2150</del><span>LE 1850</span></p>
        <button class="add-to-cart"
          data-product='{"id": 54, "name": "Bayern Munich 2025/26 Home Kit", "price": 1850, "image": "https://shop.fcbayern.com/dw/image/v2/BDVB_PRD/on/demandware.static/-/Sites-master-catalog/default/dwefef9ecb/images/large/701242072001_pp_01_fcb.jpg?sw=1600&sh=1600&sm=fit", "team": "Bayern Munich", "season": "2025"}'>
          Add to Cart
        </button>
      </div>

      <!-- Paris Saint-Germain 2025 -->
      <div class="product-card season2025-card" data-id="55" data-team="psg">
        <span class="team-badge psg-badge">Paris Saint-Germain</span>
        <img src="https://store.psg.fr/on/demandware.static/-/Sites-psg-master-catalog/default/dw62c4a0d7/images/large/701243234001_pp_01_psg.jpg"
             data-hover="https://store.psg.fr/on/demandware.static/-/Sites-psg-master-catalog/default/dwe6b7e5d5/images/large/701243234001_pp_02_psg.jpg">
        <h3>PSG 2025/26 Home Kit</h3>
        <p class="price"><del>LE 2050</del><span>LE 1700</span></p>
        <button class="add-to-cart"
          data-product='{"id": 55, "name": "PSG 2025/26 Home Kit", "price": 1700, "image": "https://store.psg.fr/on/demandware.static/-/Sites-psg-master-catalog/default/dw62c4a0d7/images/large/701243234001_pp_01_psg.jpg", "team": "PSG", "season": "2025"}'>
          Add to Cart
        </button>
      </div>

      <!-- Chelsea 2025 -->
      <div class="product-card season2025-card" data-id="56" data-team="chelsea">
        <span class="team-badge chelsea-badge">Chelsea FC</span>
        <img src="https://store.fcbarcelona.com/cdn/shop/files/VO250811A26788_med.jpg?v=1757940168&width=1200"
             data-hover="https://store.fcbarcelona.com/cdn/shop/files/BARCA1-25209.jpg?v=1757940168&width=1200">
        <h3>Chelsea 2025/26 Home Kit</h3>
        <p class="price"><del>LE 1950</del><span>LE 1600</span></p>
        <button class="add-to-cart"
          data-product='{"id": 56, "name": "Chelsea 2025/26 Home Kit", "price": 1600, "image": "https://store.fcbarcelona.com/cdn/shop/files/VO250811A26788_med.jpg?v=1757940168&width=1200", "team": "Chelsea", "season": "2025"}'>
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