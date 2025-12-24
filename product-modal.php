<?php
// product-modal.php - Product Quick View Modal
session_start();

// Database connection
try {
    require_once 'config.php';
    $db_connected = isDatabaseConnected();
} catch (Exception $e) {
    $db_connected = false;
    error_log("Config error: " . $e->getMessage());
}

// Get product ID from AJAX request
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$product_images = [];

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
            $image_sql = "SELECT filename, is_main FROM product_images WHERE product_id = ? ORDER BY is_main DESC, id ASC LIMIT 4";
            $image_stmt = $conn->prepare($image_sql);
            $image_stmt->bind_param("i", $product_id);
            $image_stmt->execute();
            $image_result = $image_stmt->get_result();
            
            while($image_row = $image_result->fetch_assoc()) {
                $product_images[] = $image_row;
            }
            $image_stmt->close();
        }
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Product query error: " . $e->getMessage());
    }
}

// Function to get image path
function getImagePath($filename) {
    if (empty($filename)) return 'https://via.placeholder.com/600x800?text=No+Image';
    $file_path = 'uploads/images/' . $filename;
    if (file_exists($file_path)) {
        return $file_path;
    }
    return 'https://via.placeholder.com/600x800?text=Image+Not+Found';
}
?>

<?php if ($product): ?>
<div class="product-quickview-modal" id="productQuickView">
    <div class="quickview-overlay" onclick="closeQuickView()"></div>
    <div class="quickview-content">
        <!-- Close Button -->
        <button class="quickview-close" onclick="closeQuickView()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="quickview-container">
            <!-- Left Column - Product Images -->
            <div class="quickview-images">
                <!-- Main Image -->
                <div class="quickview-main-image">
                    <img id="quickviewMainImage" 
                         src="<?php echo getImagePath($product['main_image'] ?? ''); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                    
                    <!-- Badges -->
                    <?php if ($product['season'] == '2025'): ?>
                        <span class="quickview-badge new">NEW</span>
                    <?php endif; ?>
                    <?php if ($product['discount_price'] > 0 && $product['discount_price'] < $product['price']): ?>
                        <?php $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>
                        <span class="quickview-badge sale">-<?php echo $discount_percent; ?>%</span>
                    <?php endif; ?>
                </div>
                
                <!-- Thumbnails -->
                <div class="quickview-thumbnails">
                    <?php foreach ($product_images as $index => $image): ?>
                        <img src="<?php echo getImagePath($image['filename']); ?>" 
                             alt="Thumbnail <?php echo $index + 1; ?>"
                             class="quickview-thumbnail <?php echo $image['is_main'] ? 'active' : ''; ?>"
                             onclick="changeQuickViewImage(this.src, this)">
                    <?php endforeach; ?>
                </div>
                
                <!-- View Full Details Link -->
                <a href="product.php?id=<?php echo $product_id; ?>" class="view-full-details">
                    <i class="fas fa-external-link-alt"></i> View Full Details
                </a>
            </div>
            
            <!-- Right Column - Product Info -->
            <div class="quickview-info">
                <!-- Product Header -->
                <div class="quickview-header">
                    <h2 class="quickview-title"><?php echo htmlspecialchars($product['name']); ?></h2>
                    
                    <div class="quickview-meta">
                        <?php if (!empty($product['team'])): ?>
                            <span class="quickview-team"><?php echo htmlspecialchars($product['team']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($product['season'])): ?>
                            <span class="quickview-season"><?php echo htmlspecialchars($product['season']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Rating -->
                    <div class="quickview-rating">
                        <div class="quickview-stars">
                            <?php
                            $rating = $product['rating'] ?? 4.5;
                            $fullStars = floor($rating);
                            for ($i = 0; $i < 5; $i++) {
                                if ($i < $fullStars) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <span class="quickview-rating-text"><?php echo number_format($rating, 1); ?> (<?php echo $product['review_count'] ?? '124'; ?> reviews)</span>
                    </div>
                </div>
                
                <!-- Price -->
                <div class="quickview-price">
                    <?php if ($product['discount_price'] > 0 && $product['discount_price'] < $product['price']): ?>
                        <div class="quickview-original-price">LE <?php echo number_format($product['price'], 2); ?></div>
                        <div class="quickview-current-price">LE <?php echo number_format($product['discount_price'], 2); ?></div>
                        <div class="quickview-save">Save <?php echo number_format($product['price'] - $product['discount_price'], 2); ?> LE</div>
                    <?php else: ?>
                        <div class="quickview-current-price">LE <?php echo number_format($product['price'], 2); ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Description -->
                <div class="quickview-description">
                    <p><?php echo nl2br(htmlspecialchars(substr($product['description'] ?? 'Official licensed jersey.', 0, 150) . '...')); ?></p>
                </div>
                
                <!-- Size Selection -->
                <div class="quickview-size">
                    <h3>Size</h3>
                    <div class="size-selector">
                        <?php
                        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                        foreach ($sizes as $size):
                            $available = true; // Check stock from database
                        ?>
                            <label class="size-option <?php echo !$available ? 'out-of-stock' : ''; ?>">
                                <input type="radio" 
                                       name="quickview_size" 
                                       value="<?php echo $size; ?>" 
                                       <?php echo !$available ? 'disabled' : ''; ?>
                                       <?php echo $size === 'M' ? 'checked' : ''; ?>>
                                <span><?php echo $size; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <a href="#" class="size-guide-link">
                        <i class="fas fa-ruler"></i> Size Guide
                    </a>
                </div>
                
                <!-- Stock Status -->
                <div class="quickview-stock">
                    <?php if ($product['stock'] > 10): ?>
                        <span class="in-stock">
                            <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)
                        </span>
                    <?php elseif ($product['stock'] > 0 && $product['stock'] <= 10): ?>
                        <span class="low-stock">
                            <i class="fas fa-exclamation-triangle"></i> Only <?php echo $product['stock']; ?> left!
                        </span>
                    <?php else: ?>
                        <span class="out-of-stock">
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Quantity & Actions -->
                <div class="quickview-actions">
                    <div class="quantity-control">
                        <label for="quickview-quantity">Quantity:</label>
                        <div class="quantity-input">
                            <button type="button" class="qty-minus" onclick="updateQuickViewQuantity(-1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" 
                                   id="quickview-quantity" 
                                   value="1" 
                                   min="1" 
                                   max="<?php echo $product['stock']; ?>"
                                   onchange="validateQuickViewQuantity()">
                            <button type="button" class="qty-plus" onclick="updateQuickViewQuantity(1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="add-to-cart-btn quickview-add" 
                                onclick="addFromQuickView(<?php echo $product_id; ?>)"
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i>
                            <span>Add to Cart</span>
                        </button>
                        
                        <button class="buy-now-btn quickview-buy" 
                                onclick="buyNowFromQuickView(<?php echo $product_id; ?>)"
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-bolt"></i>
                            <span>Buy Now</span>
                        </button>
                    </div>
                </div>
                
                <!-- Features -->
                <div class="quickview-features">
                    <h3>Features:</h3>
                    <ul>
                        <li><i class="fas fa-check"></i> Official Licensed Product</li>
                        <li><i class="fas fa-check"></i> Premium Quality Fabric</li>
                        <li><i class="fas fa-check"></i> 30-Day Return Policy</li>
                        <li><i class="fas fa-check"></i> Free Shipping over LE 1000</li>
                    </ul>
                </div>
                
                <!-- Share & Wishlist -->
                <div class="quickview-social">
                    <button class="wishlist-btn" onclick="addToWishlistFromQuickView(<?php echo $product_id; ?>)">
                        <i class="far fa-heart"></i> Add to Wishlist
                    </button>
                    
                    <div class="share-options">
                        <span>Share:</span>
                        <button class="share-btn facebook" onclick="shareProduct('facebook', <?php echo $product_id; ?>)">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button class="share-btn twitter" onclick="shareProduct('twitter', <?php echo $product_id; ?>)">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button class="share-btn whatsapp" onclick="shareProduct('whatsapp', <?php echo $product_id; ?>)">
                            <i class="fab fa-whatsapp"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="quickview-error">
    <p>Product not found. Please try again.</p>
</div>
<?php endif; ?>

<?php
// Close connection
if (isset($conn) && $db_connected) {
    $conn->close();
}
?>