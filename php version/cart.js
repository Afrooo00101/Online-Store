// cart.js for main page (index.php)
document.addEventListener('DOMContentLoaded', function() {
    const cartBtn = document.getElementById('cartBtn');
    const cartModal = document.getElementById('cartModal');
    const closeCartBtn = document.getElementById('closeCartBtn');
    const cartCountElement = document.getElementById('cartCount');
    const cartModalBody = document.getElementById('cartModalBody');
    
    // Initialize theme toggle
    initThemeToggle();
    
    // Initialize cart
    updateCartCount();
    setupEventListeners();
    
    // Toggle cart modal
    if (cartBtn && cartModal) {
        cartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            cartModal.classList.add('active');
            loadCartModal();
        });
    }
    
    // Close cart modal
    if (closeCartBtn && cartModal) {
        closeCartBtn.addEventListener('click', function() {
            cartModal.classList.remove('active');
        });
    }
    
    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target === cartModal) {
            cartModal.classList.remove('active');
        }
    });
    
    // Add to cart buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart') || 
            e.target.closest('.add-to-cart')) {
            const button = e.target.classList.contains('add-to-cart') ? 
                          e.target : e.target.closest('.add-to-cart');
            const productData = JSON.parse(button.dataset.product);
            
            addToCart(productData);
        }
    });
    
    // Theme toggle function
    function initThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                const isDark = document.body.classList.contains('dark-mode');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                this.textContent = isDark ? '☀️' : '🌙';
            });
            
            // Load saved theme
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark-mode');
                themeToggle.textContent = '☀️';
            }
        }
    }
    
    // Function to add item to cart
    function addToCart(product) {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', product.id);
        
        fetch('cart.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount();
                showNotification(`${product.name} added to cart!`);
                
                // Update modal if it's open
                if (cartModal.classList.contains('active')) {
                    loadCartModal();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error adding item to cart', 'error');
        });
    }
    
    // Update cart count
    function updateCartCount() {
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                if (cartCountElement) {
                    cartCountElement.textContent = data.count;
                    cartCountElement.style.display = data.count > 0 ? 'flex' : 'none';
                }
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
    }
    
    // Load cart modal content
    function loadCartModal() {
        fetch('get_cart_modal.php')
            .then(response => response.text())
            .then(html => {
                if (cartModalBody) {
                    cartModalBody.innerHTML = html;
                    attachModalEventListeners();
                }
            })
            .catch(error => {
                console.error('Error loading cart modal:', error);
                cartModalBody.innerHTML = '<p>Error loading cart. Please try again.</p>';
            });
    }
    
    // Attach event listeners to modal items
    function attachModalEventListeners() {
        // Quantity buttons
        cartModalBody.querySelectorAll('.modal-minus-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.id;
                updateCartItemQuantity(productId, -1);
            });
        });
        
        cartModalBody.querySelectorAll('.modal-plus-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.id;
                updateCartItemQuantity(productId, 1);
            });
        });
        
        // Remove buttons
        cartModalBody.querySelectorAll('.remove-modal-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.id;
                removeCartItem(productId);
            });
        });
        
        // Quantity inputs
        cartModalBody.querySelectorAll('.modal-quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.dataset.id;
                const newQuantity = parseInt(this.value) || 1;
                updateCartItemQuantity(productId, 0, newQuantity);
            });
        });
    }
    
    // Update cart item quantity
    function updateCartItemQuantity(productId, change, newQuantity = null) {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('product_id', productId);
        
        if (newQuantity !== null) {
            formData.append('quantity', newQuantity);
        } else {
            const currentInput = document.querySelector(`.modal-quantity-input[data-id="${productId}"]`);
            if (currentInput) {
                const currentValue = parseInt(currentInput.value) || 1;
                const newValue = Math.max(1, currentValue + change);
                formData.append('quantity', newValue);
            }
        }
        
        fetch('cart.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount();
                loadCartModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating quantity', 'error');
        });
    }
    
    // Remove item from cart
    function removeCartItem(productId) {
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('product_id', productId);
        
        fetch('cart.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount();
                loadCartModal();
                showNotification('Item removed from cart', 'info');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error removing item', 'error');
        });
    }
    
    // Show notification
    function showNotification(message, type = 'success') {
        // Create notification element if it doesn't exist
        let notification = document.getElementById('notification');
        
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'notification';
            notification.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 15px 25px;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                z-index: 10000;
                transform: translateY(100px);
                opacity: 0;
                transition: all 0.5s ease;
            `;
            document.body.appendChild(notification);
        }
        
        // Set notification content and style
        notification.textContent = message;
        notification.style.backgroundColor = type === 'error' ? '#e74c3c' : 
                                          type === 'info' ? '#3498db' : '#2ecc71';
        
        // Show notification
        notification.style.transform = 'translateY(0)';
        notification.style.opacity = '1';
        
        // Hide after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateY(100px)';
            notification.style.opacity = '0';
        }, 3000);
    }
    // In your cart.js, update the addProductToCart function to include team and season
function addProductToCart(productData) {
    // Parse the product data
    const product = typeof productData === 'string' ? JSON.parse(productData) : productData;
    
    // Add team and season if they exist
    if (!product.team) product.team = '';
    if (!product.season) product.season = '';
    
    // Rest of your cart logic remains the same
    // ...
}

// Update the displayCartItems function to show team info
function displayCartItem(item) {
    return `
        <div class="cart-modal-item" data-id="${item.id}">
            <img src="${item.image}" alt="${item.name}">
            <div class="cart-modal-item-details">
                <div class="cart-modal-item-title">${item.name}</div>
                ${item.team ? `<div class="cart-modal-item-team">${item.team}</div>` : ''}
                <div class="cart-modal-item-price">LE ${item.price.toFixed(2)}</div>
                <div class="cart-modal-item-quantity">
                    <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                    <input type="number" value="${item.quantity}" min="1" 
                           onchange="updateQuantity(${item.id}, this.value)">
                    <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                </div>
            </div>
            <button class="remove-modal-item" onclick="removeFromCart(${item.id})">×</button>
        </div>
    `;
}
    
    // Setup other event listeners
    function setupEventListeners() {
        // Sidebar menu (if exists)
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const closeSidebar = document.getElementById('closeSidebar');
        
        if (menuBtn && sidebar) {
            menuBtn.addEventListener('click', function() {
                sidebar.classList.add('active');
            });
        }
        
        if (closeSidebar && sidebar) {
            closeSidebar.addEventListener('click', function() {
                sidebar.classList.remove('active');
            });
        }
        
        // Image hover effect
        document.querySelectorAll('.product-card img[data-hover]').forEach(img => {
            const originalSrc = img.src;
            const hoverSrc = img.dataset.hover;
            
            img.addEventListener('mouseenter', function() {
                this.src = hoverSrc;
            });
            
            img.addEventListener('mouseleave', function() {
                this.src = originalSrc;
            });
        });
    }
});
