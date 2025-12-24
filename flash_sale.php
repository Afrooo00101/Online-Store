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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sport T-Shirts Store - Flash Sale</title>
    <link rel="stylesheet" href="test.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="script.js" defer></script>
    <script src="cart.js" defer></script>
</head>

<body>
  <!-- NAVBAR -->
<header class="navbar">
  <div class="menu" id="menuBtn">☰</div>
  <a href="test.php" class="logo">JERSEY<span>Wears</span></a>
  <div class="icons">
    <button id="themeToggle" class="theme-btn">🌙</button>
    <a href="profile.php">👤</a>
    <button id="cartBtn" class="cart-btn">🛒 
      <span class="cart-count" id="cartCount">
        <?php 
        // Display cart count
        $cart_count = 0;
        if(isset($_SESSION['cart'])) {
            foreach($_SESSION['cart'] as $item) {
                $cart_count += $item['quantity'];
            }
        }
        echo $cart_count;
        ?>
      </span>
    </button>
  </div>
</header>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <div class="close-btn" id="closeSidebar">×</div>
  <a href="test.php">HOME</a>
  <a href="2025.php">2025 SEASON</a>
  <a href="2024.php">2024 SEASON</a>
  <a href="2023.php">2023 SEASON</a>
  <a href="iconic.php">ICONIC JERSEYS</a>
  <a href="national.php">NATIONAL JERSEYS</a>
  <a href="world_cup.php">WORLD CUP JERSEYS</a>
  <a href="special_edition.php">SPECIAL EDITIONS</a>
  <a href="hot_offer.php">HOT OFFERS</a>
  <a href="Flash_Sale.php" class="active">FLASH SALE</a>
  <?php if(isset($_SESSION['user_id'])): ?>
    <a href="profile.php">My Account</a>
    <a href="logout.php">Logout</a>
  <?php else: ?>
    <a href="login.php">Log in</a>
    <a href="register.php">Register</a>
  <?php endif; ?>
  <div class="socials">
    <a href="#"><i class="fab fa-instagram"></i></a>
    <a href="#"><i class="fab fa-facebook"></i></a>
  </div>
</div>

<!-- Flash Sale Section -->
<div class="flash-sale-container">
    <div class="sale-notification">
        <i class="fas fa-bolt"></i> FLASH SALE ENDS IN: <span id="countdown-display">72:00:00</span> - LIMITED TIME OFFER!
    </div>
    
    <div class="sale-header">
        <div class="sale-title">
            <h2>FOOTBALL T-SHIRT FLASH SALE</h2>
            <p>Up to 60% OFF on Premium Football T-Shirts - Limited Time Only!</p>
        </div>
        
        <div class="countdown-container">
            <div class="countdown-title">HURRY UP! OFFER ENDS IN:</div>
            <div class="countdown">
                <div class="countdown-item">
                    <span class="countdown-value" id="hours">72</span>
                    <span class="countdown-label">Hours</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-value" id="minutes">00</span>
                    <span class="countdown-label">Minutes</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-value" id="seconds">00</span>
                    <span class="countdown-label">Seconds</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="sale-progress">
        <div class="progress-text">
            <span>Limited Stock: Only <span id="stock-left">35</span> items left</span>
            <span><span id="sold-percent">65</span>% sold</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill"></div>
        </div>
    </div>
    
    <div class="sale-products">
        <?php if(!empty($flash_sale_products)): ?>
            <?php foreach($flash_sale_products as $product): 
                // Calculate discount percentage
                $original_price = $product['price'];
                $discount_price = $product['discount_price'] ?? $original_price;
                $discount_percent = $product['discount_percent'] ?? 0;
                
                if($original_price > 0 && $discount_price < $original_price) {
                    $discount_percent = round((($original_price - $discount_price) / $original_price) * 100);
                }
                
                // Calculate stock left (random for demo)
                $stock_left = rand(5, 50);
                $sold_percent = rand(30, 80);
            ?>
            <div class="sale-product">
                <div class="product-image" style="background-image: url('<?php echo $product['image_path'] ?? 'https://via.placeholder.com/300x400'; ?>');">
                    <div class="product-badge">-<?php echo $discount_percent; ?>%</div>
                    <?php if($stock_left <= 10): ?>
                    <div class="low-stock-badge">Low Stock!</div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-category">
                        <?php 
                        // Get category name
                        $cat_sql = "SELECT name FROM categories WHERE id = " . $product['category_id'];
                        $cat_result = $conn->query($cat_sql);
                        if($cat_result && $cat_result->num_rows > 0) {
                            $cat_row = $cat_result->fetch_assoc();
                            echo htmlspecialchars($cat_row['name']);
                        }
                        ?>
                    </p>
                    <div class="product-price">
                        <?php if($discount_price < $original_price): ?>
                        <span class="original-price"><?php echo number_format($original_price, 2); ?> LE</span>
                        <?php endif; ?>
                        <span class="sale-price"><?php echo number_format($discount_price, 2); ?> LE</span>
                        <?php if($discount_percent > 0): ?>
                        <span class="discount-percent"><?php echo $discount_percent; ?>% OFF</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-stock">
                        <i class="fas fa-box"></i> Only <?php echo $stock_left; ?> left in stock
                    </div>
                    <form method="POST" action="add_to_cart.php" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                        <input type="hidden" name="product_price" value="<?php echo $discount_price; ?>">
                        <input type="hidden" name="product_image" value="<?php echo $product['image_path']; ?>">
                        <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback products if database is empty -->
            <?php
            $fallback_products = [
                [
                    'name' => 'Arsenal Away Shirt 2025 2026 Adults',
                    'image' => 'https://www.sportsdirect.com/images/imgzoom/37/37783118_xxl.jpg',
                    'original_price' => 6062,
                    'sale_price' => 4280,
                    'discount_percent' => 30
                ],
                [
                    'name' => 'Benfica Away Shirt 2024 2025 Adults',
                    'image' => 'https://www.sportsdirect.com/images/imgzoom/36/36736103_xxl.jpg',
                    'original_price' => 6420,
                    'sale_price' => 3210,
                    'discount_percent' => 50
                ],
                [
                    'name' => 'Manchester United Away Long Sleeve Shirt Adults',
                    'image' => 'https://www.sportsdirect.com/images/imgzoom/36/36739618_xxl.jpg',
                    'original_price' => 6062,
                    'sale_price' => 3210,
                    'discount_percent' => 47
                ],
                [
                    'name' => 'Internazionale Away Shirt 1990/1991 Mens',
                    'image' => 'https://www.sportsdirect.com/images/imgzoom/37/37981701_xxl.jpg',
                    'original_price' => 2852,
                    'sale_price' => 1997,
                    'discount_percent' => 30
                ]
            ];
            ?>
            
            <?php foreach($fallback_products as $product): ?>
            <div class="sale-product">
                <div class="product-image" style="background-image: url('<?php echo $product['image']; ?>');">
                    <div class="product-badge">-<?php echo $product['discount_percent']; ?>%</div>
                </div>
                <div class="product-info">
                    <h3 class="product-title"><?php echo $product['name']; ?></h3>
                    <div class="product-price">
                        <span class="original-price"><?php echo number_format($product['original_price'], 0); ?> LE</span>
                        <span class="sale-price"><?php echo number_format($product['sale_price'], 0); ?> LE</span>
                        <span class="discount-percent"><?php echo $product['discount_percent']; ?>% OFF</span>
                    </div>
                    <div class="product-stock">
                        <i class="fas fa-bolt"></i> Flash Sale Item
                    </div>
                    <form method="POST" action="add_to_cart.php">
                        <input type="hidden" name="product_name" value="<?php echo $product['name']; ?>">
                        <input type="hidden" name="product_price" value="<?php echo $product['sale_price']; ?>">
                        <input type="hidden" name="product_image" value="<?php echo $product['image']; ?>">
                        <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Sale Statistics -->
    <div class="sale-statistics">
        <div class="stat-item">
            <i class="fas fa-users"></i>
            <div class="stat-content">
                <h3>152+</h3>
                <p>People viewing this sale</p>
            </div>
        </div>
        <div class="stat-item">
            <i class="fas fa-shopping-cart"></i>
            <div class="stat-content">
                <h3>89</h3>
                <p>Items sold in last hour</p>
            </div>
        </div>
        <div class="stat-item">
            <i class="fas fa-clock"></i>
            <div class="stat-content">
                <h3 id="dynamic-hours">72</h3>
                <p>Hours remaining</p>
            </div>
        </div>
    </div>
</div>

<!-- Timer Script -->
<script>
// Countdown timer
let hours = 72;
let minutes = 0;
let seconds = 0;

function updateCountdown() {
    seconds--;
    if (seconds < 0) {
        seconds = 59;
        minutes--;
        if (minutes < 0) {
            minutes = 59;
            hours--;
            if (hours < 0) {
                hours = 0;
                minutes = 0;
                seconds = 0;
                // Sale ended
                document.querySelector('.sale-notification').innerHTML = 
                    '<i class="fas fa-bolt"></i> FLASH SALE HAS ENDED!';
                return;
            }
        }
    }
    
    // Update display
    document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
    document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
    document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
    document.getElementById('countdown-display').textContent = 
        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('dynamic-hours').textContent = hours;
    
    // Update progress bar (for demo)
    let totalSeconds = 72 * 3600;
    let currentSeconds = hours * 3600 + minutes * 60 + seconds;
    let progressPercent = 100 - ((currentSeconds / totalSeconds) * 100);
    document.getElementById('progress-fill').style.width = progressPercent + '%';
    
    // Update stock (for demo)
    let stockLeft = Math.max(1, Math.floor(35 * (currentSeconds / totalSeconds)));
    let soldPercent = 100 - Math.floor((stockLeft / 35) * 100);
    document.getElementById('stock-left').textContent = stockLeft;
    document.getElementById('sold-percent').textContent = soldPercent;
}

// Start countdown
updateCountdown();
setInterval(updateCountdown, 1000);

// Add to cart functionality
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const form = this.closest('form');
            if(form) {
                // Submit form via AJAX
                e.preventDefault();
                const formData = new FormData(form);
                
                fetch('add_to_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Update cart count
                        document.getElementById('cartCount').textContent = data.cart_count;
                        
                        // Show notification
                        showNotification(data.message, 'success');
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
            }
        });
    });
});

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}
</script>

<!-- CART MODAL -->
<div class="cart-modal" id="cartModal">
  <div class="cart-modal-content">
    <div class="cart-modal-header">
      <h2>Your Cart</h2>
      <button class="close-cart-btn" id="closeCartBtn">×</button>
    </div>
    <div class="cart-modal-body" id="cartModalBody">
      <!-- Cart items will be loaded here -->
      <?php if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
        <?php foreach($_SESSION['cart'] as $item): ?>
        <div class="cart-modal-item">
          <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
          <div class="cart-modal-item-details">
            <div class="cart-modal-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
            <div class="cart-modal-item-price"><?php echo number_format($item['price'], 2); ?> LE</div>
            <div class="cart-modal-item-quantity">
              <form method="POST" action="update_cart.php" style="display: inline;">
                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                <input type="hidden" name="action" value="decrease">
                <button type="submit">-</button>
              </form>
              <span><?php echo $item['quantity']; ?></span>
              <form method="POST" action="update_cart.php" style="display: inline;">
                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                <input type="hidden" name="action" value="increase">
                <button type="submit">+</button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-cart-message">
          <i class="fas fa-shopping-cart"></i>
          <p>Your cart is empty</p>
        </div>
      <?php endif; ?>
    </div>
    <div class="cart-modal-footer">
      <div class="cart-total">
        <span>Total:</span>
        <span id="modalCartTotal">
          <?php
          $total = 0;
          if(isset($_SESSION['cart'])) {
              foreach($_SESSION['cart'] as $item) {
                  $total += $item['price'] * $item['quantity'];
              }
          }
          echo number_format($total, 2) . ' LE';
          ?>
        </span>
      </div>
      <button class="view-cart-btn" onclick="window.location.href='cart.php'">
        View Full Cart
      </button>
      <button class="checkout-btn" onclick="window.location.href='checkout.php'">
        Checkout
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
      <a href="test.php">HOME</a>
      <a href="2025.php">2025 SEASON</a>
      <a href="2024.php">2024 SEASON</a>
      <a href="2023.php">2023 SEASON</a>
      <a href="iconic.php">ICONIC JERSEYS</a>
      <a href="national.php">NATIONAL JERSEYS</a>
      <a href="world_cup.php">WORLD CUP JERSEYS</a>
      <a href="special_edition.php">SPECIAL EDITIONS</a>
      <a href="hot_offer.php">HOT OFFERS</a>
      <a href="Flash_Sale.php">FLASH SALE</a>
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="profile.php">My Account</a>
      <?php else: ?>
        <a href="login.php">Log in</a>
      <?php endif; ?>
    </div>

    <!-- NEWSLETTER -->
    <div class="footer-newsletter">
      <h4>SUBSCRIBE TO OUR NEWSLETTER</h4>
      <p>Be the first to know about our newest arrivals,<br>
         special offers and store events near you!</p>
      <form method="POST" action="subscribe.php" class="newsletter-form">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">✉</button>
      </form>
      <div class="footer-socials">
        <a href="#"><i class="fab fa-facebook"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© <?php echo date('Y'); ?> Jersey Wears</p>
    <p>Powered by Loly</p>
  </div>
</footer>

</body>
</html>