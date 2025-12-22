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
<title>JERSEY WEAR</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="script.js" defer></script>
<link rel="stylesheet" href="test.css">
<link rel="stylesheet" href="cart.css">
<link rel="stylesheet" href="search.css">
<script src="cart.js" defer></script>
<script src="search.js" defer></script>
</head>

<body>
<!-- NAVBAR -->
<header class="navbar">
  <div class="menu" id="menuBtn">☰</div>
  <a href="index.php" class="logo">JERSEY<span>Wears</span></a>
  
  <!-- SEARCH BAR -->
  <div class="search-container">
    <form action="search.php" method="GET" class="search-form">
      <input type="text" 
             name="q" 
             id="searchInput" 
             placeholder="Search jerseys, teams, players..."
             autocomplete="off">
      <button type="submit">🔍</button>
      <div class="search-suggestions" id="searchSuggestions"></div>
    </form>
  </div>
  
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
  
  <!-- CATEGORIES & SUBCATEGORIES -->
  <div class="category-menu">
    <div class="category-title" onclick="toggleCategory('seasons')">
      SEASONS ▼
    </div>
    <div class="subcategories" id="seasons">
      <a href="2025.php">2025 SEASON</a>
      <a href="2024.php">2024 SEASON</a>
      <a href="2023.php">2023 SEASON</a>
    </div>
    
    <div class="category-title" onclick="toggleCategory('collections')">
      COLLECTIONS ▼
    </div>
    <div class="subcategories" id="collections">
      <a href="iconic.php">ICONIC JERSEYS</a>
      <a href="national_jersey.php">NATIONAL JERSEYS</a>
      <a href="world_cup.php">WORLD CUP JERSEYS</a>
      <a href="special_edition.php">SPECIAL EDITIONS</a>
      <a href="Flash_Sale.php" class="active">FLASH SALE</a>

    </div>
    
    <div class="category-title" onclick="toggleCategory('offers')">
      OFFERS ▼
    </div>
    <div class="subcategories" id="offers">
      <a href="hot_offer.php">HOT OFFERS</a>
      <a href="best_sellers.php">BEST SELLERS</a>
    </div>
  </div>
  
  <a href="profile.php">Log in</a>
  <div class="socials">
    <span>📸</span>
    <span>📘</span>
  </div>
</div>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <h1>SIGNATURE COLLECTION</h1>
    <a href="#featured">
      <button>Shop Now</button>
    </a>
  </div>
</section>

<!-- PROMOTIONAL BANNERS -->
<section class="promotional-banners">
  <div class="banner-container">
    <div class="banner new-arrivals">
      <h3>NEW ARRIVALS</h3>
      <p>Latest 2025 Kits</p>
      <a href="2025.php">Shop Now →</a>
    </div>
    
    <div class="banner hot-offers">
      <h3>UP TO 50% OFF</h3>
      <p>Limited Time Deals</p>
      <a href="hot_offer.php">Grab Deals →</a>
    </div>
    
    <div class="banner iconic-collection">
      <h3>ICONIC JERSEYS</h3>
      <p>Legends Never Fade</p>
      <a href="iconic.php">Explore →</a>
    </div>
  </div>
</section>

<!-- NEW ARRIVALS FROM 2025 SEASON -->
<section class="section-title">
  <h2>NEW ARRIVALS</h2>
  <a href="2025.php" class="view-all">View All 2025 Season →</a>
</section>

<section class="products new-arrivals-section">
  <?php
  // Fetch 2 products from 2025 season
  $sql = "SELECT * FROM products WHERE season = '2025' OR category LIKE '%2025%' ORDER BY created_at DESC LIMIT 2";
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
          
          $stmt->close();
          
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
            <span class="new-badge">NEW</span>
            <?php if($team_display): ?>
            <span class="team-badge <?php echo $badge_class; ?>"><?php echo $team_display; ?></span>
            <?php endif; ?>
            
            <?php if($has_discount): ?>
            <span class="sale">Sale</span>
            <?php endif; ?>
            
            <a href="product.php?id=<?php echo $row['id']; ?>">
              <img src="<?php echo htmlspecialchars($main_image); ?>" 
                   alt="<?php echo htmlspecialchars($row['name']); ?>">
            </a>
            
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            
            <p class="price">
              <?php if($has_discount): ?>
              <del>LE <?php echo number_format($original_price, 2); ?></del>
              <span>LE <?php echo number_format($discount_price, 2); ?></span>
              <?php else: ?>
              <span>LE <?php echo number_format($original_price, 2); ?></span>
              <?php endif; ?>
            </p>
            
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
          </div>
          <?php
      }
  } else {
      // Show static 2025 products
      ?>
      <div class="product-card season2025-card" data-id="49" data-team="barcelona">
        <span class="new-badge">NEW</span>
        <span class="team-badge barcelona-badge">FC Barcelona</span>
        <img src="https://store.fcbarcelona.com/cdn/shop/files/HJ4590-456_415227879_D_A_1X1_2laliga_92b83d62-53fe-4728-b143-3b8653e39427.jpg?v=1763654921&width=1200"
             alt="Barcelona 2025/26 Home Kit">
        <h3>Barcelona 2025/26 Spotify Home Kit</h3>
        <p class="price"><del>LE 2000</del><span>LE 1800</span></p>
        <button class="add-to-cart" 
          data-product='{"id": 49, "name": "Barcelona 2025/26 Spotify Home Kit", "price": 1800, "image": "https://store.fcbarcelona.com/cdn/shop/files/FCB_ED_Lamine_Jersey1_4x5_b3d54315-cc08-4fa9-b780-a72573ac3ca5.jpg?v=1761211187&width=1200", "team": "Barcelona", "season": "2025"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card season2025-card" data-id="50" data-team="liverpool">
        <span class="new-badge">NEW</span>
        <span class="team-badge liverpool-badge">Liverpool FC</span>
        <img src="https://store.liverpoolfc.com/media/catalog/product/cache/6e0c7b53c0ed72fe014b8d12b60d479c/j/y/jy4237_vvdcrop_14-1.png"
             alt="Liverpool 2025/26 Home Kit">
        <h3>Liverpool 2025/26 Home Kit</h3>
        <p class="price"><del>LE 2400</del><span>LE 1650</span></p>
        <button class="add-to-cart"
          data-product='{"id": 50, "name": "Liverpool 2025/26 Home Kit", "price": 1650, "image": "https://store.liverpoolfc.com/media/catalog/product/cache/6e0c7b53c0ed72fe014b8d12b60d479c/j/y/jy4237_vvdcrop_14-1.png", "team": "Liverpool", "season": "2025"}'>
          Add to Cart
        </button>
      </div>
      <?php
  }
  ?>
</section>

<!-- HOT OFFERS PREVIEW -->
<section class="section-title">
  <h2>HOT OFFERS</h2>
  <a href="hot_offer.php" class="view-all">View All Hot Offers →</a>
</section>

<section class="products hot-offers-preview">
  <?php
  // Fetch 2 hot offer products
  $sql = "SELECT * FROM products 
          WHERE discount_price > 0 
          AND discount_price < price 
          AND ((price - discount_price) / price * 100) >= 20 
          ORDER BY (price - discount_price) DESC 
          LIMIT 2";
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
          
          $stmt->close();
          
          // Calculate discount percentage
          $original_price = $row['price'];
          $discount_price = $row['discount_price'];
          $discount_percent = round((($original_price - $discount_price) / $original_price) * 100);
          ?>
          
          <div class="product-card hot-card" data-id="<?php echo $row['id']; ?>">
            <span class="hot-badge">🔥 HOT</span>
            <span class="sale">-<?php echo $discount_percent; ?>%</span>
            
            <a href="product.php?id=<?php echo $row['id']; ?>">
              <img src="<?php echo htmlspecialchars($main_image); ?>"
                   alt="<?php echo htmlspecialchars($row['name']); ?>">
            </a>
            
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            
            <p class="price">
              <del>LE <?php echo number_format($original_price, 2); ?></del>
              <span>LE <?php echo number_format($discount_price, 2); ?></span>
            </p>
            
            <div class="savings-info">
              Save LE <?php echo number_format(($original_price - $discount_price), 2); ?>
            </div>
            
            <button class="add-to-cart" 
                    data-product='{
                        "id": <?php echo $row['id']; ?>,
                        "name": "<?php echo addslashes($row['name']); ?>",
                        "price": <?php echo $discount_price; ?>,
                        "image": "<?php echo htmlspecialchars($main_image); ?>",
                        "original_price": <?php echo $original_price; ?>,
                        "discount_percent": <?php echo $discount_percent; ?>
                    }'>
              Add to Cart
            </button>
          </div>
          <?php
      }
  } else {
      // Show static hot offers
      ?>
      <div class="product-card hot-card" data-id="25">
        <span class="hot-badge">🔥 HOT</span>
        <span class="sale">-50%</span>
        <img src="https://shop.mancity.com/dw/image/v2/BDWJ_PRD/on/demandware.static/-/Sites-master-catalog-MAN/default/dw3dbcd6a2/images/large/701241485001_pp_01_mcfc.png?sw=1600&sh=1600&sm=fit"
             alt="Christmas Man City Jersey">
        <h3>Christmas Man City Jersey</h3>
        <p class="price"><del>LE 3000</del><span>LE 1500</span></p>
        <button class="add-to-cart" 
          data-product='{"id": 25, "name": "Christmas Man City Jersey", "price": 1500, "image": "https://shop.mancity.com/dw/image/v2/BDWJ_PRD/on/demandware.static/-/Sites-master-catalog-MAN/default/dw3dbcd6a2/images/large/701241485001_pp_01_mcfc.png?sw=1600&sh=1600&sm=fit"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card hot-card" data-id="26">
        <span class="hot-badge">🔥 HOT</span>
        <span class="sale">-30%</span>
        <img src="https://mufc-live.cdn.scayle.cloud/images/37253ea8264864e69d9c5dfdd28b8569.jpg?brightness=1&width=576&height=768&quality=70&bg=ffffffp"
             alt="Man United 2025/26 Home Kit">
        <h3>Man United 2025/26 Home Kit</h3>
        <p class="price"><del>LE 2000</del><span>LE 1400</span></p>
        <button class="add-to-cart"
          data-product='{"id": 26, "name": "Man United 2025/26 Home Kit", "price": 1400, "image": "https://mufc-live.cdn.scayle.cloud/images/37253ea8264864e69d9c5dfdd28b8569.jpg?brightness=1&width=576&height=768&quality=70&bg=ffffffp"}'>
          Add to Cart
        </button>
      </div>
      <?php
  }
  ?>
</section>

<!-- ICONIC JERSEYS PREVIEW -->
<section class="section-title">
  <h2>ICONIC JERSEYS</h2>
  <a href="iconic.php" class="view-all">View All Iconic Jerseys →</a>
</section>

<section class="products iconic-preview">
  <?php
  // Fetch 2 iconic jerseys
  $sql = "SELECT * FROM products WHERE category LIKE '%iconic%' OR category LIKE '%classic%' OR era IS NOT NULL ORDER BY RAND() LIMIT 2";
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
          
          $stmt->close();
          
          // Determine badge type
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
                  $badge_text = 'CHAMPION';
              } elseif (strpos($row['era'], '2000') !== false) {
                  $badge_class = 'legendary';
                  $badge_text = 'LEGENDARY';
              }
          }
          ?>
          
          <div class="product-card iconic-card" data-id="<?php echo $row['id']; ?>" data-era="<?php echo htmlspecialchars($row['era'] ?? ''); ?>">
            <span class="iconic-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
            
            <a href="product.php?id=<?php echo $row['id']; ?>">
              <img src="<?php echo htmlspecialchars($main_image); ?>"
                   alt="<?php echo htmlspecialchars($row['name']); ?>">
            </a>
            
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            
            <?php if(isset($row['era'])): ?>
            <div class="era-info">
              <small><?php echo htmlspecialchars($row['era']); ?> ERA</small>
            </div>
            <?php endif; ?>
            
            <?php 
            $original_price = $row['price'];
            $discount_price = $row['discount_price'];
            $has_discount = $discount_price > 0 && $discount_price < $original_price;
            ?>
            
            <p class="price">
              <?php if($has_discount): ?>
              <del>LE <?php echo number_format($original_price, 2); ?></del>
              <span>LE <?php echo number_format($discount_price, 2); ?></span>
              <?php else: ?>
              <span>LE <?php echo number_format($original_price, 2); ?></span>
              <?php endif; ?>
            </p>
            
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
          </div>
          <?php
      }
  } else {
      // Show static iconic jerseys
      ?>
      <div class="product-card iconic-card" data-id="41" data-era="2000s">
        <span class="iconic-badge legendary">LEGENDARY</span>
        <img src="https://i.pinimg.com/originals/32/d3/f6/32d3f63602b369123e816e37746e3951.jpg"
             alt="Arsenal 2002/03 Home Jersey">
        <h3>2002/03 Arsenal Home Football Jersey</h3>
        <p class="price"><del>LE 3000</del><span>LE 2100</span></p>
        <button class="add-to-cart"
          data-product='{"id": 41, "name": "2002/03 Arsenal Home Football Jersey", "price": 2100, "image": "https://classic11.com/cdn/shop/files/IMG_20250410_132139_940x.jpg?v=1760599933", "era": "2000s"}'>
          Add to Cart
        </button>
      </div>

      <div class="product-card iconic-card" data-id="42" data-era="1980s">
        <span class="iconic-badge goat">GOAT ERA</span>
        <img src="https://i.pinimg.com/736x/bb/1d/ab/bb1dab870b40c8015f04035552205f17.jpg"
             alt="Argentina 1986 Maradona Jersey">
        <h3>Argentina 1986 Maradona Jersey</h3>
        <p class="price"><del>LE 3200</del><span>LE 2300</span></p>
        <button class="add-to-cart"
          data-product='{"id": 42, "name": "Argentina 1986 Maradona Jersey", "price": 2300, "image": "https://classicfootballshirts.co.uk/cdn/shop/products/1986-Argentina-Home-Shirt_0.jpg?v=1639414354", "era": "1980s"}'>
          Add to Cart
        </button>
      </div>
      <?php
  }
  ?>
</section>

<!-- CATEGORIES GRID -->
<section class="section-title">
  <h2>SHOP BY CATEGORY</h2>
</section>

<section class="categories-grid">
  <a href="2025.php" class="category-card">
    <img src="https://store.fcbarcelona.com/cdn/shop/files/Mainbannermobile.jpg?v=1751433997&width=1200" alt="2025 Season">
    <h3>2025 SEASON</h3>
    <p>Latest Kits</p>
  </a>
  
  <a href="national_jersey.php" class="category-card">
    <img src="https://footballfashion.org/wordpress/wp-content/uploads/2025/01/Portugal-2025-2026-PUMA-Kit-7-1000x600.jpg" alt="National Jerseys">
    <h3>NATIONAL TEAMS</h3>
    <p>Country Jerseys</p>
  </a>
  
  <a href="iconic.php" class="category-card">
    <img src="https://static0.givemesportimages.com/wordpress/wp-content/uploads/2025/02/mosticonic.jpg" alt="Iconic Jerseys">
    <h3>ICONIC JERSEYS</h3>
    <p>Classic Designs</p>
  </a>
  
  <a href="special_edition.php" class="category-card">
    <img src="https://www.sportsdirect.com/images/marketing/Ajax-Away-hero-767x600.jpg" alt="Special Editions">
    <h3>SPECIAL EDITIONS</h3>
    <p>Limited Releases</p>
  </a>
</section>

<!-- FEATURED PRODUCTS (Mixed from all categories) -->
<section class="section-title" id="featured">
  <h2>FEATURED PRODUCTS</h2>
  <div class="filters">
    <select id="filterCategory">
      <option value="">All Categories</option>
      <option value="2025">2025 Season</option>
      <option value="iconic">Iconic Jerseys</option>
      <option value="hot">Hot Offers</option>
      <option value="national">National Teams</option>
    </select>
    
    <select id="filterPrice">
      <option value="">Filter by Price</option>
      <option value="0-1000">Under LE 1000</option>
      <option value="1000-2000">LE 1000 - 2000</option>
      <option value="2000-3000">LE 2000 - 3000</option>
      <option value="3000+">Over LE 3000</option>
    </select>
  </div>
</section>

<section class="products featured-products">
  <?php
  // Fetch 8 featured products from various categories
  $sql = "SELECT * FROM products WHERE featured = 1 ORDER BY RAND() LIMIT 8";
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
          
          $stmt->close();
          
          // Determine category and styling
          $category_class = '';
          $badge_text = '';
          
          if (strpos($row['category'], '2025') !== false || $row['season'] == '2025') {
              $category_class = 'season2025-card';
              $badge_text = 'NEW';
          } elseif (strpos($row['category'], 'iconic') !== false || strpos($row['category'], 'classic') !== false) {
              $category_class = 'iconic-card';
              $badge_text = 'ICONIC';
          } elseif ($row['discount_price'] > 0 && (($row['price'] - $row['discount_price']) / $row['price'] * 100) >= 20) {
              $category_class = 'hot-card';
              $badge_text = 'HOT';
          } elseif (strpos($row['category'], 'national') !== false) {
              $category_class = 'nation-card';
              $badge_text = 'NATIONAL';
          }
          
          // Calculate discount if exists
          $original_price = $row['price'];
          $discount_price = $row['discount_price'];
          $has_discount = $discount_price > 0 && $discount_price < $original_price;
          ?>
          
          <div class="product-card <?php echo $category_class; ?>" 
               data-id="<?php echo $row['id']; ?>"
               data-price="<?php echo ($has_discount ? $discount_price : $original_price); ?>"
               data-category="<?php echo htmlspecialchars($row['category']); ?>">
            
            <?php if($badge_text == 'NEW'): ?>
            <span class="new-badge">NEW</span>
            <?php elseif($badge_text == 'HOT'): ?>
            <span class="hot-badge">🔥 HOT</span>
            <?php elseif($badge_text == 'ICONIC'): ?>
            <span class="iconic-badge classic">ICONIC</span>
            <?php elseif($badge_text == 'NATIONAL'): ?>
            <span class="nation-badge">NATIONAL</span>
            <?php endif; ?>
            
            <?php if($has_discount && $badge_text != 'HOT'): ?>
            <span class="sale">Sale</span>
            <?php endif; ?>
            
            <a href="product.php?id=<?php echo $row['id']; ?>">
              <img src="<?php echo htmlspecialchars($main_image); ?>"
                   alt="<?php echo htmlspecialchars($row['name']); ?>">
            </a>
            
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            
            <?php if(isset($row['era'])): ?>
            <div class="era-info">
              <small><?php echo htmlspecialchars($row['era']); ?> ERA</small>
            </div>
            <?php endif; ?>
            
            <p class="price">
              <?php if($has_discount): ?>
              <del>LE <?php echo number_format($original_price, 2); ?></del>
              <span>LE <?php echo number_format($discount_price, 2); ?></span>
              <?php else: ?>
              <span>LE <?php echo number_format($original_price, 2); ?></span>
              <?php endif; ?>
            </p>
            
            <button class="add-to-cart" 
                    data-product='{
                        "id": <?php echo $row['id']; ?>,
                        "name": "<?php echo addslashes($row['name']); ?>",
                        "price": <?php echo ($has_discount ? $discount_price : $original_price); ?>,
                        "image": "<?php echo htmlspecialchars($main_image); ?>"
                    }'>
              Add to Cart
            </button>
            
            <a href="product.php?id=<?php echo $row['id']; ?>" class="quick-view">Quick View</a>
          </div>
          <?php
      }
  } else {
      // Show mixed featured products
      ?>
      <!-- 2025 Season Product -->
      <div class="product-card season2025-card" data-id="51" data-team="mancity">
        <span class="new-badge">NEW</span>
        <span class="team-badge mancity-badge">Man City</span>
        <img src="https://shop.mancity.com/dw/image/v2/BDWJ_PRD/on/demandware.static/-/Sites-master-catalog-MAN/default/dw53bc1f08/images/large/701237131001_pp_01_mcfc.png?sw=1600&sh=1600&sm=fit"
             alt="Manchester City 2025/26 Home Kit">
        <h3>Manchester City 2025/26 Home Kit</h3>
        <p class="price"><del>LE 2000</del><span>LE 1800</span></p>
        <button class="add-to-cart"
          data-product='{"id": 51, "name": "Manchester City 2025/26 Home Kit", "price": 1800, "image": "https://shop.mancity.com/dw/image/v2/BDWJ_PRD/on/demandware.static/-/Sites-master-catalog-MAN/default/dw53bc1f08/images/large/701237131001_pp_01_mcfc.png?sw=1600&sh=1600&sm=fit", "team": "Manchester City", "season": "2025"}'>
          Add to Cart
        </button>
      </div>

      <!-- Hot Offer Product -->
      <div class="product-card hot-card" data-id="27">
        <span class="hot-badge">🔥 HOT</span>
        <span class="sale">-35%</span>
        <img src="https://store.fcbarcelona.com/cdn/shop/files/Mainbannermobile.jpg?v=1751433997&width=1200"
             alt="Barcelona 2025/26 Home Kit">
        <h3>Barcelona 2025/26 Home Kit</h3>
        <p class="price"><del>LE 2000</del><span>LE 1300</span></p>
        <button class="add-to-cart"
          data-product='{"id": 27, "name": "Barcelona 2025/26 Home Kit", "price": 1300, "image": "https://store.fcbarcelona.com/cdn/shop/files/Mainbannermobile.jpg?v=1751433997&width=1200"}'>
          Add to Cart
        </button>
      </div>

      <!-- Iconic Jersey -->
      <div class="product-card iconic-card" data-id="43" data-era="1990s">
        <span class="iconic-badge champion">WORLD CHAMPION</span>
        <img src="https://media.gettyimages.com/id/989647398/photo/world-cup-2002-preview-zidane-zinedine-coupe-du-monde-wereld-beker-france-frankrijk.jpg?s=612x612&w=gi&k=20&c=eGLfRBb0Wug8gZMr8AsPzdKP_nq50zgSbDUsz6XcM_A="
             alt="France 1998 Zidane Jersey">
        <h3>France 1998 Zidane Jersey</h3>
        <p class="price"><del>LE 2900</del><span>LE 2000</span></p>
        <button class="add-to-cart"
          data-product='{"id": 43, "name": "France 1998 Zidane Jersey", "price": 2000, "image": "https://media.gettyimages.com/id/989647398/photo/world-cup-2002-preview-zidane-zinedine-coupe-du-monde-wereld-beker-france-frankrijk.jpg?s=612x612&w=gi&k=20&c=eGLfRBb0Wug8gZMr8AsPzdKP_nq50zgSbDUsz6XcM_A=", "era": "1990s"}'>
          Add to Cart
        </button>
      </div>

      <!-- 2025 Season Product -->
      <div class="product-card season2025-card" data-id="52" data-team="realmadrid">
        <span class="new-badge">NEW</span>
        <span class="team-badge realmadrid-badge">Real Madrid</span>
        <img src="https://us.shop.realmadrid.com/_next/image?url=https%3A%2F%2Fimages.ctfassets.net%2F7nqb12anqb19%2F17yiNVkP7YE2CPvGHQz4tH%2F2624b451883ee295d2df4a8db646b0b5%2FDESKTOP-MBAPPE.jpg&w=640&q=75"
             alt="Real Madrid 2025/26 Home Kit">
        <h3>Real Madrid 2025/26 Home Kit</h3>
        <p class="price"><del>LE 2200</del><span>LE 1900</span></p>
        <button class="add-to-cart"
          data-product='{"id": 52, "name": "Real Madrid 2025/26 Home Kit", "price": 1900, "image": "https://us.shop.realmadrid.com/_next/image?url=https%3A%2F%2Flegends.broadleafcloud.com%2Fapi%2Fasset%2Fcontent%2FJZ9016_01.jpg%3FcontextRequest%3D%257B%2522forceCatalogForFetch%2522%3Afalse%2C%2522forceFilterByCatalogIncludeInheritance%2522%3Afalse%2C%2522forceFilterByCatalogExcludeInheritance%2522%3Afalse%2C%2522applicationId%2522%3A%252201H4RD9NXMKQBQ1WVKM1181VD8%2522%2C%2522tenantId%2522%3A%2522REAL_MADRID%2522%257D&w=1920&q=75", "team": "Real Madrid", "season": "2025"}'>
          Add to Cart
        </button>
      </div>
      <?php
  }
  ?>
</section>

<!-- NEWSLETTER SIGNUP -->
<section class="newsletter-section">
  <div class="newsletter-content">
    <h2>Stay Updated</h2>
    <p>Subscribe to our newsletter and be the first to know about new arrivals, special offers, and exclusive deals.</p>
    <form method="POST" action="subscribe.php" class="newsletter-form">
      <input type="email" name="email" placeholder="Enter your email address" required>
      <button type="submit">Subscribe</button>
    </form>
  </div>
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

<script>
// Category toggle for sidebar
function toggleCategory(categoryId) {
  const subcategories = document.getElementById(categoryId);
  subcategories.style.display = subcategories.style.display === 'block' ? 'none' : 'block';
}

// Filter functionality for featured products
document.getElementById('filterCategory').addEventListener('change', function() {
  const category = this.value;
  filterProductsByCategory(category);
});

document.getElementById('filterPrice').addEventListener('change', function() {
  const priceRange = this.value;
  filterProductsByPrice(priceRange);
});

function filterProductsByCategory(category) {
  const allProducts = document.querySelectorAll('.featured-products .product-card');
  
  allProducts.forEach(product => {
    if (!category) {
      product.style.display = 'block';
      return;
    }
    
    const productCategory = product.classList.value;
    if (category === '2025' && productCategory.includes('season2025-card')) {
      product.style.display = 'block';
    } else if (category === 'iconic' && productCategory.includes('iconic-card')) {
      product.style.display = 'block';
    } else if (category === 'hot' && productCategory.includes('hot-card')) {
      product.style.display = 'block';
    } else if (category === 'national' && productCategory.includes('nation-card')) {
      product.style.display = 'block';
    } else {
      product.style.display = 'none';
    }
  });
}

function filterProductsByPrice(priceRange) {
  if (!priceRange) {
    document.querySelectorAll('.product-card').forEach(card => {
      card.style.display = 'block';
    });
    return;
  }
  
  const [min, max] = priceRange === '3000+' ? [3000, Infinity] : priceRange.split('-').map(Number);
  
  document.querySelectorAll('.product-card').forEach(card => {
    const priceElement = card.querySelector('.price span');
    if (!priceElement) return;
    
    const priceText = priceElement.textContent.replace('LE ', '').replace(',', '');
    const productPrice = parseFloat(priceText);
    
    if ((max === Infinity && productPrice >= min) || 
        (productPrice >= min && productPrice <= max)) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }
  });
}

// Auto-slide banners
let currentBanner = 0;
const banners = document.querySelectorAll('.banner');
setInterval(() => {
  banners[currentBanner].classList.remove('active');
  currentBanner = (currentBanner + 1) % banners.length;
  banners[currentBanner].classList.add('active');
}, 5000);
</script>

<?php
// Close database connection
$conn->close();
?>
</body>
</html>