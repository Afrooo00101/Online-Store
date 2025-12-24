<?php
session_start();

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart count
$cartCount = count($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JERSEY WEAR</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="test.css">
<link rel="stylesheet" href="cart.css">
<link rel="stylesheet" href="search.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
      <span class="cart-count" id="cartCount" style="<?php echo $cartCount > 0 ? 'display: flex;' : 'display: none;'; ?>">
        <?php echo $cartCount; ?>
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
    <a href="#shop_by_category">
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
  <div class="product-card season2025-card" data-id="1" data-team="barcelona">
    <span class="new-badge">NEW</span>
    <span class="team-badge barcelona-badge">FC Barcelona</span>
    <img src="https://store.fcbarcelona.com/cdn/shop/files/HJ4590-456_415227879_D_A_1X1_2laliga_92b83d62-53fe-4728-b143-3b8653e39427.jpg?v=1763654921&width=1200"
         alt="Barcelona 2025/26 Home Kit">
    <h3>Barcelona 2025/26 Spotify Home Kit</h3>
    <p class="price"><del>LE 2000</del><span>LE 1800</span></p>
    <button class="add-to-cart" 
      data-product='{"id": 1, "name": "Barcelona 2025/26 Spotify Home Kit", "price": 1800, "image": "https://store.fcbarcelona.com/cdn/shop/files/HJ4590-456_415227879_D_A_1X1_2laliga_92b83d62-53fe-4728-b143-3b8653e39427.jpg?v=1763654921&width=1200", "team": "Barcelona", "season": "2025"}'>
      Add to Cart
    </button>
  </div>

  <div class="product-card season2025-card" data-id="2" data-team="liverpool">
    <span class="new-badge">NEW</span>
    <span class="team-badge liverpool-badge">Liverpool FC</span>
    <img src="https://store.liverpoolfc.com/media/catalog/product/cache/6e0c7b53c0ed72fe014b8d12b60d479c/j/y/jy4237_vvdcrop_14-1.png"
         alt="Liverpool 2025/26 Home Kit">
    <h3>Liverpool 2025/26 Home Kit</h3>
    <p class="price"><del>LE 2400</del><span>LE 1650</span></p>
    <button class="add-to-cart"
      data-product='{"id": 2, "name": "Liverpool 2025/26 Home Kit", "price": 1650, "image": "https://store.liverpoolfc.com/media/catalog/product/cache/6e0c7b53c0ed72fe014b8d12b60d479c/j/y/jy4237_vvdcrop_14-1.png", "team": "Liverpool", "season": "2025"}'>
      Add to Cart
    </button>
  </div>
</section>

<!-- HOT OFFERS PREVIEW -->
<section class="section-title">
  <h2>HOT OFFERS</h2>
  <a href="hot_offer.php" class="view-all">View All Hot Offers →</a>
</section>

<section class="products hot-offers-preview">
  <div class="product-card hot-card" data-id="3">
    <span class="hot-badge">🔥 HOT</span>
    <span class="sale">-50%</span>
    <img src="https://shop.mancity.com/dw/image/v2/BDWJ_PRD/on/demandware.static/-/Sites-master-catalog-MAN/default/dw3dbcd6a2/images/large/701241485001_pp_01_mcfc.png?sw=1600&sh=1600&sm=fit"
         alt="Christmas Man City Jersey">
    <h3>Christmas Man City Jersey</h3>
    <p class="price"><del>LE 3000</del><span>LE 1500</span></p>
    <button class="add-to-cart" 
      data-product='{"id": 3, "name": "Christmas Man City Jersey", "price": 1500, "image": "https://shop.mancity.com/dw/image/v2/BDWJ_PRD/on/demandware.static/-/Sites-master-catalog-MAN/default/dw3dbcd6a2/images/large/701241485001_pp_01_mcfc.png?sw=1600&sh=1600&sm=fit"}'>
      Add to Cart
    </button>
  </div>

  <div class="product-card hot-card" data-id="4">
    <span class="hot-badge">🔥 HOT</span>
    <span class="sale">-30%</span>
    <img src="https://mufc-live.cdn.scayle.cloud/images/37253ea8264864e69d9c5dfdd28b8569.jpg?brightness=1&width=576&height=768&quality=70&bg=ffffffp"
         alt="Man United 2025/26 Home Kit">
    <h3>Man United 2025/26 Home Kit</h3>
    <p class="price"><del>LE 2000</del><span>LE 1400</span></p>
    <button class="add-to-cart"
      data-product='{"id": 4, "name": "Man United 2025/26 Home Kit", "price": 1400, "image": "https://mufc-live.cdn.scayle.cloud/images/37253ea8264864e69d9c5dfdd28b8569.jpg?brightness=1&width=576&height=768&quality=70&bg=ffffffp"}'>
      Add to Cart
    </button>
  </div>
</section>

<!-- ICONIC JERSEYS PREVIEW -->
<section class="section-title">
  <h2>ICONIC JERSEYS</h2>
  <a href="iconic.php" class="view-all">View All Iconic Jerseys →</a>
</section>

<section class="products iconic-preview">
  <div class="product-card iconic-card" data-id="5" data-era="2000s">
    <span class="iconic-badge legendary">LEGENDARY</span>
    <img src="https://i.pinimg.com/originals/32/d3/f6/32d3f63602b369123e816e37746e3951.jpg"
         alt="Arsenal 2002/03 Home Jersey">
    <h3>2002/03 Arsenal Home Football Jersey</h3>
    <p class="price"><del>LE 3000</del><span>LE 2100</span></p>
    <button class="add-to-cart"
      data-product='{"id": 5, "name": "2002/03 Arsenal Home Football Jersey", "price": 2100, "image": "https://classic11.com/cdn/shop/files/IMG_20250410_132139_940x.jpg?v=1760599933", "era": "2000s"}'>
      Add to Cart
    </button>
  </div>

  <div class="product-card iconic-card" data-id="6" data-era="1980s">
    <span class="iconic-badge goat">GOAT ERA</span>
    <img src="https://i.pinimg.com/736x/bb/1d/ab/bb1dab870b40c8015f04035552205f17.jpg"
         alt="Argentina 1986 Maradona Jersey">
    <h3>Argentina 1986 Maradona Jersey</h3>
    <p class="price"><del>LE 3200</del><span>LE 2300</span></p>
    <button class="add-to-cart"
      data-product='{"id": 6, "name": "Argentina 1986 Maradona Jersey", "price": 2300, "image": "https://classicfootballshirts.co.uk/cdn/shop/products/1986-Argentina-Home-Shirt_0.jpg?v=1639414354", "era": "1980s"}'>
      Add to Cart
    </button>
  </div>
</section>

<!-- CATEGORIES GRID -->
<section class="section-title" id="shop_by_category">
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

<!-- FEATURED PRODUCTS -->
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
  <div class="product-card season2025-card" data-id="7" data-team="mancity">
    <span class="new-badge">NEW</span>
    <span class="team-badge mancity-badge">Man City</span>
    <img src="https://shop.mancity.com/dw/image/v2/BDWJ_PRD/on/demandware.static/-/Sites-master-catalog-MAN/default/dw53bc1f08/images/large/701237131001_pp_01_mcfc.png?sw=1600&sh=1600&sm=fit"
         alt="Manchester City 2025/26 Home Kit">
    <h3>Manchester City 2025/26 Home Kit</h3>
    <p class="price"><del>LE 2000</del><span>LE 1800</span></p>
    <button class="add-to-cart"
      data-product='{"id": 7, "name": "Manchester City 2025/26 Home Kit", "price": 1800, "image": "https://shop.mancity.com/dw/image/v2/BDWJ_PRD/on/demandware.static/-/Sites-master-catalog-MAN/default/dw53bc1f08/images/large/701237131001_pp_01_mcfc.png?sw=1600&sh=1600&sm=fit", "team": "Manchester City", "season": "2025"}'>
      Add to Cart
    </button>
  </div>

  <div class="product-card hot-card" data-id="8">
    <span class="hot-badge">🔥 HOT</span>
    <span class="sale">-35%</span>
    <img src="https://store.fcbarcelona.com/cdn/shop/files/Mainbannermobile.jpg?v=1751433997&width=1200"
         alt="Barcelona 2025/26 Home Kit">
    <h3>Barcelona 2025/26 Home Kit</h3>
    <p class="price"><del>LE 2000</del><span>LE 1300</span></p>
    <button class="add-to-cart"
      data-product='{"id": 8, "name": "Barcelona 2025/26 Home Kit", "price": 1300, "image": "https://store.fcbarcelona.com/cdn/shop/files/Mainbannermobile.jpg?v=1751433997&width=1200"}'>
      Add to Cart
    </button>
  </div>

  <div class="product-card iconic-card" data-id="9" data-era="1990s">
    <span class="iconic-badge champion">WORLD CHAMPION</span>
    <img src="https://media.gettyimages.com/id/989647398/photo/world-cup-2002-preview-zidane-zinedine-coupe-du-monde-wereld-beker-france-frankrijk.jpg?s=612x612&w=gi&k=20&c=eGLfRBb0Wug8gZMr8AsPzdKP_nq50zgSbDUsz6XcM_A="
         alt="France 1998 Zidane Jersey">
    <h3>France 1998 Zidane Jersey</h3>
    <p class="price"><del>LE 2900</del><span>LE 2000</span></p>
    <button class="add-to-cart"
      data-product='{"id": 9, "name": "France 1998 Zidane Jersey", "price": 2000, "image": "https://media.gettyimages.com/id/989647398/photo/world-cup-2002-preview-zidane-zinedine-coupe-du-monde-wereld-beker-france-frankrijk.jpg?s=612x612&w=gi&k=20&c=eGLfRBb0Wug8gZMr8AsPzdKP_nq50zgSbDUsz6XcM_A=", "era": "1990s"}'>
      Add to Cart
    </button>
  </div>

  <div class="product-card season2025-card" data-id="10" data-team="realmadrid">
    <span class="new-badge">NEW</span>
    <span class="team-badge realmadrid-badge">Real Madrid</span>
    <img src="https://us.shop.realmadrid.com/_next/image?url=https%3A%2F%2Fimages.ctfassets.net%2F7nqb12anqb19%2F17yiNVkP7YE2CPvGHQz4tH%2F2624b451883ee295d2df4a8db646b0b5%2FDESKTOP-MBAPPE.jpg&w=640&q=75"
         alt="Real Madrid 2025/26 Home Kit">
    <h3>Real Madrid 2025/26 Home Kit</h3>
    <p class="price"><del>LE 2200</del><span>LE 1900</span></p>
    <button class="add-to-cart"
      data-product='{"id": 10, "name": "Real Madrid 2025/26 Home Kit", "price": 1900, "image": "https://us.shop.realmadrid.com/_next/image?url=https%3A%2F%2Flegends.broadleafcloud.com%2Fapi%2Fasset%2Fcontent%2FJZ9016_01.jpg%3FcontextRequest%3D%257B%2522forceCatalogForFetch%2522%3Afalse%2C%2522forceFilterByCatalogIncludeInheritance%2522%3Afalse%2C%2522forceFilterByCatalogExcludeInheritance%2522%3Afalse%2C%2522applicationId%2522%3A%252201H4RD9NXMKQBQ1WVKM1181VD8%2522%2C%2522tenantId%2522%3A%2522REAL_MADRID%2522%257D&w=1920&q=75", "team": "Real Madrid", "season": "2025"}'>
      Add to Cart
    </button>
  </div>
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

<!-- Cart Modal -->
<div class="cart-modal" id="cartModal">
    <div class="cart-modal-content">
        <div class="cart-modal-header">
            <h2><i class="fas fa-shopping-cart"></i> Your Cart</h2>
            <button class="close-cart-btn" id="closeCartBtn">×</button>
        </div>
        <div class="cart-modal-body" id="cartModalBody">
            <div class="cart-modal-empty">
                <i class="fas fa-shopping-cart fa-2x"></i>
                <p>Your cart is empty</p>
                <p class="empty-message">Add some products to get started!</p>
            </div>
        </div>
        <div class="cart-modal-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span id="modalCartTotal">LE 0.00</span>
            </div>
            <a href="cart.php" class="view-cart-btn">
                <i class="fas fa-shopping-bag"></i> View Full Cart
            </a>
            <button class="checkout-btn" onclick="window.location.href='cart.php'">
                <i class="fas fa-lock"></i> Proceed to Checkout
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
document.getElementById('filterCategory')?.addEventListener('change', function() {
  const category = this.value;
  filterProductsByCategory(category);
});

document.getElementById('filterPrice')?.addEventListener('change', function() {
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
if (banners.length > 0) {
    setInterval(() => {
        banners[currentBanner]?.classList?.remove('active');
        currentBanner = (currentBanner + 1) % banners.length;
        banners[currentBanner]?.classList?.add('active');
    }, 5000);
}
</script>

</body>
</html>