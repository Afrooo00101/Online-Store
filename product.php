<?php
// product.php - Product Detail Page
session_start();

// Database connection
try {
    require_once 'config.php';
    $db_connected = isDatabaseConnected();
} catch (Exception $e) {
    $db_connected = false;
    error_log("Config error: " . $e->getMessage());
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$product_images = [];
$related_products = [];

if ($db_connected && $product_id > 0) {
    try {
        // Fetch product details
        $sql = "SELECT p.*, 
                       (SELECT filename FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image
                FROM products p 
                WHERE p.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            
            // Fetch all product images
            $image_sql = "SELECT filename, is_main FROM product_images WHERE product_id = ? ORDER BY is_main DESC, id ASC";
            $image_stmt = $conn->prepare($image_sql);
            $image_stmt->bind_param("i", $product_id);
            $image_stmt->execute();
            $image_result = $image_stmt->get_result();
            
            while($image_row = $image_result->fetch_assoc()) {
                $product_images[] = $image_row;
            }
            $image_stmt->close();
            
            // Fetch related products (same category/team)
            $related_sql = "SELECT p.* FROM products p 
                           WHERE p.id != ? 
                           AND (p.category = ? OR p.team = ?) 
                           ORDER BY RAND() LIMIT 4";
            $related_stmt = $conn->prepare($related_sql);
            $related_stmt->bind_param("iss", $product_id, $product['category'], $product['team']);
            $related_stmt->execute();
            $related_result = $related_stmt->get_result();
            
            while($related_row = $related_result->fetch_assoc()) {
                $related_products[] = $related_row;
            }
            $related_stmt->close();
        }
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Product query error: " . $e->getMessage());
    }
}

// If no product found, redirect to homepage
if (!$product) {
    header("Location: index.php");
    exit();
}

// Function to get image path
function getImagePath($filename) {
    $file_path = 'uploads/images/' . $filename;
    if (file_exists($file_path)) {
        return $file_path;
    }
    return 'https://via.placeholder.com/600x800?text=Image+Not+Found';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name'] ?? 'Product'); ?> - JERSEY WEAR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="test.css">
    <link rel="stylesheet" href="product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="script.js" defer></script>
    <script src="cart.js" defer></script>
</head>
<body>
    <!-- NAVBAR (same as index.php) -->
    <header class="navbar">
        <div class="menu" id="menuBtn">☰</div>
        <a href="index.php" class="logo">JERSEY<span>Wears</span></a>
        
        <div class="search-container">
            <form action="search.php" method="GET" class="search-form">
                <input type="text" 
                       name="q" 
                       id="searchInput" 
                       placeholder="Search jerseys, teams, players..."
                       autocomplete="off">
                <button type="submit">🔍</button>
            </form>
        </div>
        
        <div class="icons">
            <button id="themeToggle" class="theme-btn">🌙</button>
            <a href="profile.php">👤</a>
            <button id="cartBtn" class="cart-btn">🛒 
                <span class="cart-count" id="cartCount">
                    <?php echo count($_SESSION['cart'] ?? []); ?>
                </span>
            </button>
        </div>
    </header>

    <!-- BREADCRUMB NAVIGATION -->
    <nav class="breadcrumb">
        <a href="index.php">Home</a> &gt;
        <?php if (!empty($product['season'])): ?>
            <a href="<?php echo $product['season']; ?>.php"><?php echo $product['season']; ?> Season</a> &gt;
        <?php endif; ?>
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </nav>

    <!-- MAIN PRODUCT SECTION -->
    <main class="product-detail-container">
        <div class="product-detail">
            <!-- LEFT COLUMN - IMAGES -->
            <div class="product-images">
                <!-- Main Image -->
                <div class="main-image">
                    <img id="mainProductImage" 
                         src="<?php echo getImagePath($product['main_image'] ?? ''); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                    
                    <!-- Badges -->
                    <?php if ($product['season'] == '2025'): ?>
                        <span class="product-badge new">NEW</span>
                    <?php endif; ?>
                    <?php if ($product['discount_price'] > 0 && $product['discount_price'] < $product['price']): ?>
                        <?php $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>
                        <span class="product-badge sale">-<?php echo $discount_percent; ?>%</span>
                    <?php endif; ?>
                    <?php if (!empty($product['era'])): ?>
                        <span class="product-badge iconic"><?php echo htmlspecialchars($product['era']); ?> ERA</span>
                    <?php endif; ?>
                </div>
                
                <!-- Thumbnail Images -->
                <div class="thumbnail-images">
                    <?php foreach ($product_images as $index => $image): ?>
                        <img src="<?php echo getImagePath($image['filename']); ?>" 
                             alt="Thumbnail <?php echo $index + 1; ?>"
                             class="thumbnail <?php echo $image['is_main'] ? 'active' : ''; ?>"
                             onclick="changeMainImage(this.src, this)">
                    <?php endforeach; ?>
                    
                    <!-- Add placeholder if no additional images -->
                    <?php if (count($product_images) <= 1): ?>
                        <div class="thumbnail-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="thumbnail-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="thumbnail-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Video Section (if available) -->
                <?php if (!empty($product['video_url'])): ?>
                <div class="product-video">
                    <h3>Watch in Action</h3>
                    <div class="video-container">
                        <iframe src="<?php echo htmlspecialchars($product['video_url']); ?>" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen></iframe>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- RIGHT COLUMN - PRODUCT INFO -->
            <div class="product-info">
                <!-- Product Header -->
                <div class="product-header">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-meta">
                        <?php if (!empty($product['team'])): ?>
                            <span class="team-tag"><?php echo htmlspecialchars($product['team']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($product['season'])): ?>
                            <span class="season-tag"><?php echo htmlspecialchars($product['season']); ?> Season</span>
                        <?php endif; ?>
                        <?php if (!empty($product['category'])): ?>
                            <span class="category-tag"><?php echo htmlspecialchars($product['category']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Rating -->
                    <div class="product-rating">
                        <div class="stars">
                            <?php
                            $rating = $product['rating'] ?? 4.5;
                            $fullStars = floor($rating);
                            $hasHalfStar = ($rating - $fullStars) >= 0.5;
                            $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                            
                            for ($i = 0; $i < $fullStars; $i++) {
                                echo '<i class="fas fa-star"></i>';
                            }
                            if ($hasHalfStar) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            }
                            for ($i = 0; $i < $emptyStars; $i++) {
                                echo '<i class="far fa-star"></i>';
                            }
                            ?>
                        </div>
                        <span class="rating-text"><?php echo number_format($rating, 1); ?> (<?php echo $product['review_count'] ?? '124'; ?> reviews)</span>
                    </div>
                </div>

                <!-- Price Section -->
                <div class="price-section">
                    <?php if ($product['discount_price'] > 0 && $product['discount_price'] < $product['price']): ?>
                        <div class="original-price">LE <?php echo number_format($product['price'], 2); ?></div>
                        <div class="current-price">LE <?php echo number_format($product['discount_price'], 2); ?></div>
                        <div class="save-amount">
                            <i class="fas fa-tag"></i>
                            Save LE <?php echo number_format($product['price'] - $product['discount_price'], 2); ?>
                        </div>
                    <?php else: ?>
                        <div class="current-price">LE <?php echo number_format($product['price'], 2); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Size Selection -->
                <div class="size-selection">
                    <h3>Select Size</h3>
                    <div class="size-options">
                        <?php
                        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
                        foreach ($sizes as $size):
                            $available = true; // You can check stock for each size from database
                        ?>
                            <label class="size-option <?php echo !$available ? 'out-of-stock' : ''; ?>">
                                <input type="radio" name="size" value="<?php echo $size; ?>" 
                                       <?php echo !$available ? 'disabled' : ''; ?>>
                                <span><?php echo $size; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <a href="#" class="size-guide">
                        <i class="fas fa-ruler"></i> Size Guide
                    </a>
                </div>

                <!-- Stock Status -->
                <div class="stock-status">
                    <?php if ($product['stock'] > 10): ?>
                        <span class="in-stock"><i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)</span>
                    <?php elseif ($product['stock'] > 0 && $product['stock'] <= 10): ?>
                        <span class="low-stock"><i class="fas fa-exclamation-triangle"></i> Only <?php echo $product['stock']; ?> left!</span>
                    <?php else: ?>
                        <span class="out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                    <?php endif; ?>
                    
                    <?php if ($product['delivery_days'] ?? false): ?>
                        <span class="delivery-info">
                            <i class="fas fa-shipping-fast"></i>
                            Delivery in <?php echo $product['delivery_days']; ?> days
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Quantity & Add to Cart -->
                <div class="action-buttons">
                    <div class="quantity-selector">
                        <button class="qty-btn minus" onclick="updateQuantity(-1)">-</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button class="qty-btn plus" onclick="updateQuantity(1)">+</button>
                    </div>
                    
                    <button class="add-to-cart-btn" 
                            onclick="addToCartDetail(<?php echo $product_id; ?>)"
                            <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart"></i>
                        Add to Cart
                    </button>
                    
                    <button class="buy-now-btn" 
                            onclick="buyNow(<?php echo $product_id; ?>)"
                            <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-bolt"></i>
                        Buy Now
                    </button>
                </div>

                <!-- Product Features -->
                <div class="product-features">
                    <h3>Features & Details</h3>
                    <ul>
                        <li><i class="fas fa-check"></i> Official Licensed Product</li>
                        <li><i class="fas fa-check"></i> Authentic Team Colors & Logos</li>
                        <li><i class="fas fa-check"></i> Premium Quality Fabric</li>
                        <li><i class="fas fa-check"></i> Moisture-Wicking Technology</li>
                        <li><i class="fas fa-check"></i> Player Name & Number Available</li>
                        <li><i class="fas fa-check"></i> 30-Day Return Policy</li>
                    </ul>
                </div>

                <!-- Share & Wishlist -->
                <div class="social-actions">
                    <button class="wishlist-btn" onclick="addToWishlist(<?php echo $product_id; ?>)">
                        <i class="far fa-heart"></i> Add to Wishlist
                    </button>
                    
                    <div class="share-buttons">
                        <span>Share:</span>
                        <button class="share-btn facebook"><i class="fab fa-facebook-f"></i></button>
                        <button class="share-btn twitter"><i class="fab fa-twitter"></i></button>
                        <button class="share-btn whatsapp"><i class="fab fa-whatsapp"></i></button>
                        <button class="share-btn copy" onclick="copyProductLink()"><i class="fas fa-link"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRODUCT DESCRIPTION & DETAILS -->
        <div class="product-details-tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="openTab('description')">Description</button>
                <button class="tab-btn" onclick="openTab('specifications')">Specifications</button>
                <button class="tab-btn" onclick="openTab('reviews')">Reviews</button>
                <button class="tab-btn" onclick="openTab('shipping')">Shipping & Returns</button>
            </div>
            
            <div class="tab-content">
                <!-- Description Tab -->
                <div id="description" class="tab-pane active">
                    <h3>Product Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'Official licensed jersey from ' . ($product['team'] ?? 'the team') . '. Made with premium materials for ultimate comfort and performance.')); ?></p>
                    
                    <div class="highlights">
                        <h4>Product Highlights:</h4>
                        <div class="highlight-grid">
                            <div class="highlight">
                                <i class="fas fa-tshirt"></i>
                                <h5>Authentic Design</h5>
                                <p>Official team colors, logos, and sponsor branding</p>
                            </div>
                            <div class="highlight">
                                <i class="fas fa-wind"></i>
                                <h5>Breathable Fabric</h5>
                                <p>Moisture-wicking technology keeps you cool and dry</p>
                            </div>
                            <div class="highlight">
                                <i class="fas fa-expand"></i>
                                <h5>Perfect Fit</h5>
                                <p>Regular fit designed for maximum comfort and movement</p>
                            </div>
                            <div class="highlight">
                                <i class="fas fa-medal"></i>
                                <h5>Premium Quality</h5>
                                <p>Durable construction for long-lasting wear</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Specifications Tab -->
                <div id="specifications" class="tab-pane">
                    <h3>Product Specifications</h3>
                    <table class="specs-table">
                        <tr>
                            <th>Material</th>
                            <td>100% Polyester (Recycled Materials)</td>
                        </tr>
                        <tr>
                            <th>Fit</th>
                            <td>Regular Fit</td>
                        </tr>
                        <tr>
                            <th>Care Instructions</th>
                            <td>Machine wash cold, do not bleach, tumble dry low</td>
                        </tr>
                        <tr>
                            <th>Origin</th>
                            <td>Officially Licensed Product</td>
                        </tr>
                        <tr>
                            <th>Availability</th>
                            <td>Ships within 24 hours</td>
                        </tr>
                        <?php if (!empty($product['era'])): ?>
                        <tr>
                            <th>Era</th>
                            <td><?php echo htmlspecialchars($product['era']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <!-- Reviews Tab -->
                <div id="reviews" class="tab-pane">
                    <h3>Customer Reviews</h3>
                    <div class="review-summary">
                        <div class="overall-rating">
                            <div class="rating-number"><?php echo number_format($product['rating'] ?? 4.5, 1); ?></div>
                            <div class="rating-stars">
                                <?php for ($i = 0; $i < 5; $i++) echo '<i class="fas fa-star"></i>'; ?>
                            </div>
                            <p>Based on <?php echo $product['review_count'] ?? '124'; ?> reviews</p>
                        </div>
                        
                        <button class="write-review-btn" onclick="openReviewForm()">
                            <i class="fas fa-pen"></i> Write a Review
                        </button>
                    </div>
                    
                    <!-- Sample Reviews -->
                    <div class="review-list">
                        <div class="review">
                            <div class="review-header">
                                <div class="reviewer">Ahmed M.</div>
                                <div class="review-rating">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                            </div>
                            <p class="review-text">"Perfect fit and amazing quality! The colors are vibrant just like on TV."</p>
                            <span class="review-date">2 days ago</span>
                        </div>
                        <!-- Add more reviews here -->
                    </div>
                </div>
                
                <!-- Shipping Tab -->
                <div id="shipping" class="tab-pane">
                    <h3>Shipping & Returns</h3>
                    <div class="shipping-info">
                        <div class="shipping-item">
                            <i class="fas fa-shipping-fast"></i>
                            <div>
                                <h5>Free Shipping</h5>
                                <p>Free shipping on all orders over LE 1000</p>
                            </div>
                        </div>
                        <div class="shipping-item">
                            <i class="fas fa-exchange-alt"></i>
                            <div>
                                <h5>30-Day Returns</h5>
                                <p>Easy returns within 30 days of purchase</p>
                            </div>
                        </div>
                        <div class="shipping-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <h5>Secure Payment</h5>
                                <p>Your payment information is secure with us</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RELATED PRODUCTS -->
        <section class="related-products">
            <h2>You May Also Like</h2>
            <div class="related-products-grid">
                <?php if (!empty($related_products)): ?>
                    <?php foreach ($related_products as $related): ?>
                        <div class="related-product-card">
                            <a href="product.php?id=<?php echo $related['id']; ?>">
                                <img src="<?php echo getImagePath($related['main_image'] ?? ''); ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>">
                            </a>
                            <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                            <div class="price">
                                <?php if ($related['discount_price'] > 0 && $related['discount_price'] < $related['price']): ?>
                                    <span class="original">LE <?php echo number_format($related['price'], 2); ?></span>
                                    <span class="current">LE <?php echo number_format($related['discount_price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="current">LE <?php echo number_format($related['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="product.php?id=<?php echo $related['id']; ?>" class="view-product">View Product</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback related products -->
                    <p>No related products found. Check out our <a href="index.php">featured products</a>.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- CART MODAL (Same as index.php) -->
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

    <!-- FOOTER (Same as index.php) -->
    <footer class="footer">
        <!-- Same footer content as index.php -->
    </footer>

    <!-- JavaScript for Product Page -->
    <script>
    // Product Image Gallery
    function changeMainImage(src, element) {
        document.getElementById('mainProductImage').src = src;
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });
        element.classList.add('active');
    }
    
    // Quantity Selector
    function updateQuantity(change) {
        const quantityInput = document.getElementById('quantity');
        let currentQty = parseInt(quantityInput.value);
        const maxQty = parseInt(quantityInput.max);
        const minQty = parseInt(quantityInput.min);
        
        let newQty = currentQty + change;
        if (newQty >= minQty && newQty <= maxQty) {
            quantityInput.value = newQty;
        }
    }
    
    // Tab Switching
    function openTab(tabName) {
        // Hide all tab panes
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('active');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected tab and mark button as active
        document.getElementById(tabName).classList.add('active');
        event.currentTarget.classList.add('active');
    }
    
    // Add to Cart for Product Detail Page
    function addToCartDetail(productId) {
        const size = document.querySelector('input[name="size"]:checked')?.value || 'M';
        const quantity = parseInt(document.getElementById('quantity').value);
        
        // Get product data from hidden fields or fetch via AJAX
        const productData = {
            id: productId,
            name: '<?php echo addslashes($product['name']); ?>',
            price: <?php echo ($product['discount_price'] > 0) ? $product['discount_price'] : $product['price']; ?>,
            size: size,
            quantity: quantity,
            image: '<?php echo getImagePath($product['main_image'] ?? ''); ?>',
            stock: <?php echo $product['stock']; ?>
        };
        
        // Add to cart logic (using your existing cart system)
        addToCart(productData);
        
        // Show success message
        showNotification('Product added to cart!', 'success');
    }
    
    // Buy Now function
    function buyNow(productId) {
        addToCartDetail(productId);
        setTimeout(() => {
            window.location.href = 'checkout.php';
        }, 500);
    }
    
    // Wishlist function
    function addToWishlist(productId) {
        // Implement wishlist logic
        showNotification('Added to wishlist!', 'success');
    }
    
    // Copy product link
    function copyProductLink() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            showNotification('Link copied to clipboard!', 'success');
        });
    }
    
    // Show notification
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Review form
    function openReviewForm() {
        // Implement review form modal
        alert('Review form coming soon!');
    }
    
    // Update quantity input directly
    document.getElementById('quantity').addEventListener('change', function() {
        let value = parseInt(this.value);
        const max = parseInt(this.max);
        const min = parseInt(this.min);
        
        if (value < min) this.value = min;
        if (value > max) this.value = max;
    });
    
    // Share buttons
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const platform = this.classList[1];
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent('<?php echo addslashes($product['name']); ?>');
            
            let shareUrl = '';
            switch(platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${title}%20${url}`;
                    break;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        });
    });
    </script>
</body>
</html>

<?php
// Close connection
if (isset($conn) && $db_connected) {
    $conn->close();
}
?>