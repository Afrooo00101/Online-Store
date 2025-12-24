// quickview.js - Product Quick View Modal Functionality

let currentProductId = null;

// Open Quick View Modal
function openQuickView(productId) {
    currentProductId = productId;
    
    // Show loading state
    document.body.style.overflow = 'hidden';
    
    // Create modal container if it doesn't exist
    let modalContainer = document.getElementById('quickviewContainer');
    if (!modalContainer) {
        modalContainer = document.createElement('div');
        modalContainer.id = 'quickviewContainer';
        document.body.appendChild(modalContainer);
    }
    
    // Fetch product data via AJAX
    fetch(`product-modal.php?id=${productId}`)
        .then(response => response.text())
        .then(html => {
            modalContainer.innerHTML = html;
            
            // Show modal with animation
            setTimeout(() => {
                const modal = document.querySelector('.product-quickview-modal');
                if (modal) {
                    modal.style.display = 'flex';
                    // Focus on first input
                    const firstInput = modal.querySelector('input, button, select');
                    if (firstInput) firstInput.focus();
                }
            }, 10);
            
            // Add event listeners
            setupQuickViewEvents();
        })
        .catch(error => {
            console.error('Error loading quick view:', error);
            showNotification('Error loading product details', 'error');
        });
}

// Close Quick View Modal
function closeQuickView() {
    const modal = document.querySelector('.product-quickview-modal');
    if (modal) {
        modal.style.animation = 'slideDown 0.3s ease forwards';
        
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            document.getElementById('quickviewContainer').innerHTML = '';
        }, 300);
    }
}

// Setup Quick View Event Listeners
function setupQuickViewEvents() {
    // Close modal when clicking overlay
    const overlay = document.querySelector('.quickview-overlay');
    if (overlay) {
        overlay.addEventListener('click', closeQuickView);
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQuickView();
        }
    });
    
    // Prevent modal closing when clicking inside content
    const content = document.querySelector('.quickview-content');
    if (content) {
        content.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

// Change main image in quick view
function changeQuickViewImage(src, element) {
    const mainImage = document.getElementById('quickviewMainImage');
    if (mainImage) {
        mainImage.src = src;
        
        // Update active thumbnail
        document.querySelectorAll('.quickview-thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });
        element.classList.add('active');
    }
}

// Update quantity in quick view
function updateQuickViewQuantity(change) {
    const quantityInput = document.getElementById('quickview-quantity');
    if (quantityInput) {
        let currentQty = parseInt(quantityInput.value) || 1;
        const maxQty = parseInt(quantityInput.max) || 999;
        const minQty = parseInt(quantityInput.min) || 1;
        
        let newQty = currentQty + change;
        if (newQty >= minQty && newQty <= maxQty) {
            quantityInput.value = newQty;
        }
    }
}

// Validate quantity input
function validateQuickViewQuantity() {
    const quantityInput = document.getElementById('quickview-quantity');
    if (quantityInput) {
        let value = parseInt(quantityInput.value) || 1;
        const max = parseInt(quantityInput.max) || 999;
        const min = parseInt(quantityInput.min) || 1;
        
        if (value < min) quantityInput.value = min;
        if (value > max) quantityInput.value = max;
    }
}

// Add to cart from quick view
function addFromQuickView(productId) {
    const size = document.querySelector('input[name="quickview_size"]:checked')?.value || 'M';
    const quantity = parseInt(document.getElementById('quickview-quantity').value) || 1;
    
    // Get product name and price from modal
    const titleElement = document.querySelector('.quickview-title');
    const priceElement = document.querySelector('.quickview-current-price');
    const imageElement = document.getElementById('quickviewMainImage');
    
    if (!titleElement || !priceElement || !imageElement) {
        showNotification('Error adding product to cart', 'error');
        return;
    }
    
    const productName = titleElement.textContent.trim();
    const priceText = priceElement.textContent.replace('LE ', '').replace(',', '');
    const price = parseFloat(priceText);
    const imageSrc = imageElement.src;
    
    const productData = {
        id: productId,
        name: productName,
        price: price,
        size: size,
        quantity: quantity,
        image: imageSrc
    };
    
    // Add to cart using your existing cart system
    addToCart(productData);
    
    // Show success notification
    showNotification(`Added ${productName} to cart!`, 'success');
    
    // Update cart count
    updateCartCount();
    
    // Close quick view after a delay
    setTimeout(() => {
        closeQuickView();
    }, 1000);
}

// Buy now from quick view
function buyNowFromQuickView(productId) {
    addFromQuickView(productId);
    
    // Redirect to checkout after a short delay
    setTimeout(() => {
        window.location.href = 'checkout.php';
    }, 500);
}

// Add to wishlist from quick view
function addToWishlistFromQuickView(productId) {
    // Implement wishlist functionality
    showNotification('Added to wishlist!', 'success');
    
    // Change button appearance
    const wishlistBtn = document.querySelector('.wishlist-btn');
    if (wishlistBtn) {
        wishlistBtn.innerHTML = '<i class="fas fa-heart"></i> Added to Wishlist';
        wishlistBtn.style.color = '#ff416c';
        wishlistBtn.style.borderColor = '#ff416c';
        wishlistBtn.disabled = true;
    }
}

// Share product from quick view
function shareProduct(platform, productId) {
    const productUrl = `${window.location.origin}/product.php?id=${productId}`;
    const productTitle = document.querySelector('.quickview-title')?.textContent || 'Check out this jersey!';
    
    let shareUrl = '';
    
    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(productUrl)}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(productUrl)}&text=${encodeURIComponent(productTitle)}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${encodeURIComponent(productTitle + ' ' + productUrl)}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

// Show notification
function showNotification(message, type) {
    // Remove existing notification
    const existingNotification = document.querySelector('.quickview-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create new notification
    const notification = document.createElement('div');
    notification.className = `quickview-notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;
    
    // Style the notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#2ecc71' : '#e74c3c'};
        color: white;
        border-radius: 8px;
        font-weight: 600;
        z-index: 10000;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease forwards';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add CSS animations for notification
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    @keyframes slideDown {
        from { transform: translateY(0); opacity: 1; }
        to { transform: translateY(100px); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Initialize quick view functionality when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Add click event to all quick view buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.quick-view-btn')) {
            e.preventDefault();
            const productId = e.target.closest('.product-card')?.dataset?.id;
            if (productId) {
                openQuickView(productId);
            }
        }
    });
    
    // Add click event to "Quick View" links
    document.addEventListener('click', function(e) {
        if (e.target.closest('.quick-view') && e.target.closest('.quick-view').tagName === 'A') {
            e.preventDefault();
            const href = e.target.closest('.quick-view').getAttribute('href');
            const productId = href.split('id=')[1];
            if (productId) {
                openQuickView(productId);
            }
        }
    });
});