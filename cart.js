// cart.js - Enhanced for index.php integration with all fixes

// Cart System
class CartSystem {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updateCartCount();
        this.loadCartModal();
    }

    setupEventListeners() {
        // Cart button - prevent sidebar toggle
        const cartBtn = document.getElementById('cartBtn');
        if (cartBtn) {
            cartBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.openCartModal();
            });
        }

        // Close cart button
        const closeCartBtn = document.getElementById('closeCartBtn');
        if (closeCartBtn) {
            closeCartBtn.addEventListener('click', () => {
                this.closeCartModal();
            });
        }

        // Close modal when clicking overlay
        const overlay = document.querySelector('.cart-modal-overlay');
        if (overlay) {
            overlay.addEventListener('click', () => {
                this.closeCartModal();
            });
        }

        // Add to cart buttons from index.php
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart') || 
                e.target.closest('.add-to-cart')) {
                e.preventDefault();
                e.stopPropagation();
                const button = e.target.classList.contains('add-to-cart') ? 
                              e.target : e.target.closest('.add-to-cart');
                this.handleAddToCart(button);
            }
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.getElementById('menuBtn');
            
            if (sidebar && menuBtn && 
                !sidebar.contains(e.target) && 
                !menuBtn.contains(e.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });

        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                themeToggle.textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
            });
        }

        // Shipping method change
        document.addEventListener('change', (e) => {
            if (e.target.name === 'shipping_method_id') {
                const methodId = e.target.value;
                this.updateShippingMethod(methodId);
            }
        });

        // Prevent sidebar from opening when clicking cart-related elements
        document.querySelectorAll('.add-to-cart, .cart-modal, .cart-btn').forEach(element => {
            element.addEventListener('click', function(e) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && sidebar.classList.contains('active')) {
                    e.stopPropagation();
                }
            });
        });
    }

    handleAddToCart(button) {
        const productData = button.dataset.product;
        
        if (!productData) {
            console.error('No product data found');
            return;
        }

        try {
            const product = JSON.parse(productData);
            
            // Show loading state
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            button.disabled = true;

            this.addToCart(product.id, product.name, product.price, product.image)
                .then(result => {
                    if (result.success) {
                        this.showNotification(result.message);
                        this.updateCartCount();
                        this.loadCartModal();
                        this.openCartModal();
                    } else {
                        this.showNotification(result.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error adding to cart:', error);
                    this.showNotification('Failed to add item to cart', 'error');
                })
                .finally(() => {
                    // Restore button state
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }, 500);
                });

        } catch (error) {
            console.error('Error parsing product data:', error);
            this.showNotification('Invalid product data', 'error');
        }
    }

    async addToCart(productId, name, price, image) {
        try {
            const response = await fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'ajax': 'true',
                    'action': 'add',
                    'product_id': productId,
                    'name': name,
                    'price': price,
                    'image': image
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Add to cart error:', error);
            return { success: false, message: 'Network error' };
        }
    }

    async updateCartCount() {
        try {
            const response = await fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'ajax': 'true',
                    'action': 'get_cart_summary'
                })
            });

            if (response.ok) {
                const result = await response.json();
                const cartCount = document.getElementById('cartCount');
                
                if (cartCount && result.data) {
                    cartCount.textContent = result.data.item_count || 0;
                    cartCount.style.display = result.data.item_count > 0 ? 'flex' : 'none';
                }
            }
        } catch (error) {
            console.error('Update cart count error:', error);
        }
    }

    async loadCartModal() {
        const cartModalBody = document.getElementById('cartModalBody');
        if (!cartModalBody) return;

        try {
            const response = await fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'ajax': 'true',
                    'action': 'get_cart_summary'
                })
            });

            if (response.ok) {
                const result = await response.json();
                
                if (result.success && result.data && result.cart_items && result.cart_items.length > 0) {
                    this.updateCartModalContent(result.data, result.cart_items);
                } else {
                    cartModalBody.innerHTML = `
                        <div class="cart-modal-empty">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                            <p>Your cart is empty</p>
                            <p class="empty-message">Add some products to get started!</p>
                        </div>
                    `;
                }
            }
        } catch (error) {
            console.error('Load cart modal error:', error);
        }
    }

    updateCartModalContent(data, cartItems) {
        const cartModalBody = document.getElementById('cartModalBody');
        const cartTotal = document.getElementById('modalCartTotal');
        
        if (!cartModalBody || !cartTotal) return;

        let html = '<div id="cartItemsContainer">';
        let total = data.total || 0;

        cartItems.forEach(item => {
            const itemTotal = item.price * item.quantity;

            html += `
                <div class="cart-modal-item" data-id="${item.id}">
                    <img src="${item.image}" alt="${item.name}">
                    <div class="cart-modal-item-details">
                        <div class="cart-modal-item-title">${item.name}</div>
                        <div class="cart-modal-item-price">LE ${item.price.toFixed(2)}</div>
                        <div class="cart-modal-item-quantity">
                            <button class="modal-quantity-btn minus-btn" onclick="window.cartSystem.updateQuantity(${item.id}, -1)">-</button>
                            <input type="number" class="modal-quantity-input" value="${item.quantity}" min="1" max="99"
                                   onchange="window.cartSystem.updateQuantityDirect(${item.id}, this.value)">
                            <button class="modal-quantity-btn plus-btn" onclick="window.cartSystem.updateQuantity(${item.id}, 1)">+</button>
                            <button class="remove-modal-item" onclick="window.cartSystem.removeItem(${item.id})">×</button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        cartModalBody.innerHTML = html;
        cartTotal.textContent = `LE ${total.toFixed(2)}`;
    }

    async updateQuantity(productId, change) {
        try {
            const input = document.querySelector(`.modal-quantity-input[onchange*="${productId}"]`);
            let currentQuantity = input ? parseInt(input.value) : 1;
            let newQuantity = Math.max(1, currentQuantity + change);

            const response = await fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'ajax': 'true',
                    'action': 'update',
                    'product_id': productId,
                    'quantity': newQuantity
                })
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    this.showNotification(result.message);
                    this.updateCartCount();
                    this.loadCartModal();
                    
                    // Update totals if summary is returned
                    if (result.summary) {
                        this.updateTotals(result.summary);
                    }
                }
            }
        } catch (error) {
            console.error('Update quantity error:', error);
        }
    }

    async updateQuantityDirect(productId, quantity) {
        try {
            const response = await fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'ajax': 'true',
                    'action': 'update',
                    'product_id': productId,
                    'quantity': quantity
                })
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    this.showNotification(result.message);
                    this.updateCartCount();
                    this.loadCartModal();
                    
                    // Update totals if summary is returned
                    if (result.summary) {
                        this.updateTotals(result.summary);
                    }
                }
            }
        } catch (error) {
            console.error('Update quantity direct error:', error);
        }
    }

    async removeItem(productId) {
        if (!confirm('Are you sure you want to remove this item?')) return;

        try {
            const response = await fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'ajax': 'true',
                    'action': 'remove',
                    'product_id': productId
                })
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    this.showNotification(result.message, 'info');
                    this.updateCartCount();
                    this.loadCartModal();
                    
                    // Update totals if summary is returned
                    if (result.summary) {
                        this.updateTotals(result.summary);
                    }
                }
            }
        } catch (error) {
            console.error('Remove item error:', error);
        }
    }

    async updateShippingMethod(methodId) {
        try {
            const response = await fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'ajax': 'true',
                    'action': 'update_shipping',
                    'shipping_method_id': methodId
                })
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    // Update totals if summary is returned
                    if (result.summary) {
                        this.updateTotals(result.summary);
                    }
                    this.showNotification(result.message, 'info');
                }
            }
        } catch (error) {
            console.error('Update shipping error:', error);
        }
    }

    updateTotals(summary) {
        // Update totals on the page
        const subtotalElement = document.getElementById('subtotal');
        const shippingElement = document.getElementById('shipping');
        const taxElement = document.getElementById('tax');
        const totalElement = document.getElementById('total');
        const discountElement = document.getElementById('discount');
        const modalTotalElement = document.getElementById('modalCartTotal');
        
        if (subtotalElement) subtotalElement.textContent = `LE ${summary.subtotal.toFixed(2)}`;
        if (shippingElement) shippingElement.textContent = summary.shipping === 0 ? 'FREE' : `LE ${summary.shipping.toFixed(2)}`;
        if (taxElement) taxElement.textContent = `LE ${summary.tax.toFixed(2)}`;
        if (totalElement) totalElement.textContent = `LE ${summary.total.toFixed(2)}`;
        if (modalTotalElement) modalTotalElement.textContent = `LE ${summary.total.toFixed(2)}`;
        
        // Update discount if exists
        if (summary.discount > 0) {
            if (!discountElement) {
                // Create discount element if it doesn't exist
                const promoApplied = document.getElementById('promoApplied');
                if (promoApplied) {
                    const discountSpan = promoApplied.querySelector('span:nth-child(2)');
                    if (discountSpan) {
                        discountSpan.textContent = `- LE ${summary.discount.toFixed(2)}`;
                    }
                }
            } else {
                discountElement.textContent = `- LE ${summary.discount.toFixed(2)}`;
            }
        }
    }

    openCartModal() {
        const cartModal = document.getElementById('cartModal');
        const overlay = document.querySelector('.cart-modal-overlay');
        
        if (cartModal) {
            cartModal.classList.add('active');
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    closeCartModal() {
        const cartModal = document.getElementById('cartModal');
        const overlay = document.querySelector('.cart-modal-overlay');
        
        if (cartModal) {
            cartModal.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    showNotification(message, type = 'success') {
        // Remove existing notification
        const existingNotification = document.querySelector('.cart-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Create new notification
        const notification = document.createElement('div');
        notification.className = `cart-notification ${type}`;
        notification.textContent = message;
        
        // Style based on type
        if (type === 'error') {
            notification.style.backgroundColor = '#e74c3c';
        } else if (type === 'info') {
            notification.style.backgroundColor = '#3498db';
        } else {
            notification.style.backgroundColor = '#2ecc71';
        }

        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideDown 0.3s ease reverse';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// Sidebar functionality
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

// Search functionality
function initSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    
    if (searchInput && searchSuggestions) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchSuggestions.style.display = 'none';
                return;
            }
            
            // Fetch search suggestions
            fetch(`search.php?q=${encodeURIComponent(query)}&type=suggestions`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.suggestions.length > 0) {
                        searchSuggestions.innerHTML = '';
                        data.suggestions.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'search-suggestion';
                            div.textContent = item;
                            div.addEventListener('click', function() {
                                searchInput.value = this.textContent;
                                searchSuggestions.style.display = 'none';
                            });
                            searchSuggestions.appendChild(div);
                        });
                        searchSuggestions.style.display = 'block';
                    } else {
                        searchSuggestions.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    searchSuggestions.style.display = 'none';
                });
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                searchSuggestions.style.display = 'none';
            }
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize cart system
    window.cartSystem = new CartSystem();
    
    // Setup sidebar toggle
    const menuBtn = document.getElementById('menuBtn');
    const closeSidebar = document.getElementById('closeSidebar');
    
    if (menuBtn) {
        menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });
    }
    
    if (closeSidebar) {
        closeSidebar.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });
    }
    
    // Initialize search
    initSearch();
    
    // Add overlay for cart modal
    let overlay = document.querySelector('.cart-modal-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'cart-modal-overlay';
        document.body.appendChild(overlay);
    }
    
    // Add event listener to close modal when clicking overlay
    overlay.addEventListener('click', () => {
        window.cartSystem.closeCartModal();
    });
    
    // Prevent cart clicks from affecting sidebar
    const cartBtn = document.getElementById('cartBtn');
    const sidebar = document.getElementById('sidebar');
    
    if (cartBtn && sidebar) {
        cartBtn.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});

// Make functions available globally
window.toggleSidebar = toggleSidebar;
window.updateCartCount = function() {
    if (window.cartSystem) {
        window.cartSystem.updateCartCount();
    }
};

// Update cart modal content
window.updateCartModal = function() {
    if (window.cartSystem) {
        window.cartSystem.loadCartModal();
    }
};
// Function to handle checkout and save cart items
async function handleCheckout() {
    try {
        // First, save current cart state to session
        const response = await fetch('save-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'ajax': 'true',
                'action': 'save_cart_for_checkout',
                'cart_data': JSON.stringify(window.cartSystem?.cartItems || [])
            })
        });
        
        if (response.ok) {
            // Proceed with checkout form submission
            document.getElementById('checkoutForm').submit();
        }
    } catch (error) {
        console.error('Error saving cart for checkout:', error);
        // Fallback: submit form anyway
        document.getElementById('checkoutForm').submit();
    }
}