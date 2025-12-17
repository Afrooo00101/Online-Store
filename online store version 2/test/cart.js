// Cart data structure
let cartItems = JSON.parse(localStorage.getItem('jerseyWearCart')) || [];

// DOM elements
const cartBtn = document.getElementById('cartBtn');
const cartModal = document.getElementById('cartModal');
const closeCartBtn = document.getElementById('closeCartBtn');
const cartModalBody = document.getElementById('cartModalBody');
const modalCartTotal = document.getElementById('modalCartTotal');
const cartCount = document.getElementById('cartCount');

// Initialize cart
function initCart() {
    updateCartCount();
    setupEventListeners();
    
    // If on cart page, initialize cart page functions
    if (window.location.pathname.includes('cart.html')) {
        initCartPage();
    }
}

// Initialize cart page (only on cart.html)
function initCartPage() {
    const cartItemsContainer = document.getElementById('cartItems');
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    const itemCountElement = document.querySelector('.item-count');
    const subtotalElement = document.getElementById('subtotal');
    const shippingElement = document.getElementById('shipping');
    const taxElement = document.getElementById('tax');
    const totalElement = document.getElementById('total');
    const clearCartBtn = document.getElementById('clearCart');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const paymentOptions = document.querySelectorAll('input[name="paymentMethod"]');
    const paymentDetails = document.getElementById('paymentDetails');

    if (cartItemsContainer) renderCartItems();
    if (emptyCartMessage) updateEmptyCartMessage();
    if (itemCountElement) updateItemCount();
    if (subtotalElement) updateCartSummary();
    if (clearCartBtn) clearCartBtn.addEventListener('click', clearCart);
    if (checkoutBtn) checkoutBtn.addEventListener('click', processCheckout);
    if (paymentOptions.length > 0) setupPaymentDetails();
}

// Update cart count in header
function updateCartCount() {
    const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
    if (cartCount) {
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
    }
}

// Save cart to localStorage
function saveCartToStorage() {
    localStorage.setItem('jerseyWearCart', JSON.stringify(cartItems));
}

// Add product to cart
function addToCart(product) {
    // Parse product data if it's a string
    if (typeof product === 'string') {
        try {
            product = JSON.parse(product);
        } catch (e) {
            console.error('Error parsing product data:', e);
            return;
        }
    }
    
    // Check if product already exists in cart
    const existingItemIndex = cartItems.findIndex(item => item.id === product.id);
    
    if (existingItemIndex !== -1) {
        // Update quantity if already in cart
        cartItems[existingItemIndex].quantity += 1;
        showNotification(`Updated quantity for ${product.name}`);
    } else {
        // Add new item to cart
        cartItems.push({
            id: product.id,
            name: product.name,
            price: product.price,
            quantity: 1,
            image: product.image || 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
        });
        showNotification(`${product.name} added to cart`);
    }
    
    saveCartToStorage();
    updateCartCount();
    updateModalCart();
    
    // If on cart page, update the page
    if (window.location.pathname.includes('cart.html')) {
        renderCartItems();
        updateCartSummary();
        updateEmptyCartMessage();
    }
}

// Update modal cart display
function updateModalCart() {
    if (!cartModalBody) return;
    
    if (cartItems.length === 0) {
        cartModalBody.innerHTML = `
            <div class="cart-modal-empty">
                <i class="fas fa-shopping-cart fa-2x"></i>
                <p>Your cart is empty</p>
            </div>
        `;
        modalCartTotal.textContent = 'LE 0.00';
        return;
    }
    
    let html = '';
    let total = 0;
    
    cartItems.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        html += `
            <div class="cart-modal-item" data-id="${item.id}">
                <img src="${item.image}" alt="${item.name}">
                <div class="cart-modal-item-details">
                    <div class="cart-modal-item-title">${item.name}</div>
                    <div class="cart-modal-item-price">LE ${item.price.toFixed(2)}</div>
                    <div class="cart-modal-item-quantity">
                        <button class="modal-minus-btn" data-id="${item.id}">-</button>
                        <input type="number" class="modal-quantity-input" data-id="${item.id}" 
                               value="${item.quantity}" min="1" max="99">
                        <button class="modal-plus-btn" data-id="${item.id}">+</button>
                        <button class="remove-modal-item" data-id="${item.id}">×</button>
                    </div>
                </div>
            </div>
        `;
    });
    
    cartModalBody.innerHTML = html;
    modalCartTotal.textContent = `LE ${total.toFixed(2)}`;
    
    // Add event listeners to modal buttons
    cartModalBody.querySelectorAll('.modal-minus-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = parseInt(this.dataset.id);
            updateCartItemQuantity(itemId, -1);
        });
    });
    
    cartModalBody.querySelectorAll('.modal-plus-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = parseInt(this.dataset.id);
            updateCartItemQuantity(itemId, 1);
        });
    });
    
    cartModalBody.querySelectorAll('.modal-quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const itemId = parseInt(this.dataset.id);
            const newQuantity = parseInt(this.value) || 1;
            updateCartItemQuantity(itemId, 0, newQuantity);
        });
    });
    
    cartModalBody.querySelectorAll('.remove-modal-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = parseInt(this.dataset.id);
            removeCartItem(itemId);
        });
    });
}

// Update cart item quantity
function updateCartItemQuantity(itemId, change = 0, newQuantity = null) {
    const itemIndex = cartItems.findIndex(item => item.id === itemId);
    
    if (itemIndex !== -1) {
        if (newQuantity !== null) {
            cartItems[itemIndex].quantity = Math.max(1, newQuantity);
        } else {
            cartItems[itemIndex].quantity = Math.max(1, cartItems[itemIndex].quantity + change);
        }
        
        saveCartToStorage();
        updateCartCount();
        updateModalCart();
        
        // If on cart page, update the page
        if (window.location.pathname.includes('cart.html')) {
            renderCartItems();
            updateCartSummary();
        }
        
        showNotification(`Quantity updated`);
    }
}

// Remove item from cart
function removeCartItem(itemId) {
    const itemName = cartItems.find(item => item.id === itemId)?.name;
    cartItems = cartItems.filter(item => item.id !== itemId);
    
    saveCartToStorage();
    updateCartCount();
    updateModalCart();
    
    // If on cart page, update the page
    if (window.location.pathname.includes('cart.html')) {
        renderCartItems();
        updateCartSummary();
        updateEmptyCartMessage();
    }
    
    showNotification(`${itemName} removed from cart`, 'info');
}

// Render cart items on cart.html
function renderCartItems() {
    const cartItemsContainer = document.getElementById('cartItems');
    if (!cartItemsContainer) return;
    
    if (cartItems.length === 0) {
        cartItemsContainer.innerHTML = '';
        return;
    }
    
    cartItemsContainer.innerHTML = cartItems.map(item => `
        <div class="cart-item" data-id="${item.id}">
            <div class="item-image">
                <img src="${item.image}" alt="${item.name}">
            </div>
            <div class="item-details">
                <h3 class="item-title">${item.name}</h3>
                <div class="item-price">LE ${item.price.toFixed(2)}</div>
            </div>
            <div class="item-quantity">
                <button class="quantity-btn minus-btn" data-id="${item.id}">
                    <i class="fas fa-minus"></i>
                </button>
                <input type="number" class="quantity-input" data-id="${item.id}" 
                       value="${item.quantity}" min="1" max="99">
                <button class="quantity-btn plus-btn" data-id="${item.id}">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="item-total">LE ${(item.price * item.quantity).toFixed(2)}</div>
            <button class="remove-item-btn" data-id="${item.id}">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
    
    updateItemCount();
}

// Update cart summary on cart.html
function updateCartSummary() {
    const subtotalElement = document.getElementById('subtotal');
    const shippingElement = document.getElementById('shipping');
    const taxElement = document.getElementById('tax');
    const totalElement = document.getElementById('total');
    
    if (!subtotalElement) return;
    
    const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const shipping = subtotal > 1000 ? 0 : 50; // Free shipping for orders over LE 1000
    const tax = subtotal * 0.14; // 14% tax (Egyptian VAT)
    const total = subtotal + shipping + tax;
    
    subtotalElement.textContent = `LE ${subtotal.toFixed(2)}`;
    shippingElement.textContent = shipping === 0 ? 'FREE' : `LE ${shipping.toFixed(2)}`;
    taxElement.textContent = `LE ${tax.toFixed(2)}`;
    totalElement.textContent = `LE ${total.toFixed(2)}`;
}

// Update item count on cart.html
function updateItemCount() {
    const itemCountElement = document.querySelector('.item-count');
    if (!itemCountElement) return;
    
    const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
    itemCountElement.textContent = `${totalItems} ${totalItems === 1 ? 'item' : 'items'} in your cart`;
}

// Show/hide empty cart message on cart.html
function updateEmptyCartMessage() {
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    const cartItemsContainer = document.getElementById('cartItems');
    
    if (!emptyCartMessage || !cartItemsContainer) return;
    
    if (cartItems.length === 0) {
        emptyCartMessage.style.display = 'block';
        cartItemsContainer.style.display = 'none';
    } else {
        emptyCartMessage.style.display = 'none';
        cartItemsContainer.style.display = 'flex';
    }
}

// Clear entire cart
function clearCart() {
    if (cartItems.length === 0) {
        showNotification('Cart is already empty', 'info');
        return;
    }
    
    if (confirm('Are you sure you want to clear your cart?')) {
        cartItems = [];
        saveCartToStorage();
        updateCartCount();
        updateModalCart();
        
        if (window.location.pathname.includes('cart.html')) {
            renderCartItems();
            updateCartSummary();
            updateEmptyCartMessage();
        }
        
        showNotification('Cart cleared successfully', 'info');
    }
}

// Setup payment details on cart.html
function setupPaymentDetails() {
    const paymentOptions = document.querySelectorAll('input[name="paymentMethod"]');
    const paymentDetails = document.getElementById('paymentDetails');
    const totalElement = document.getElementById('total');
    
    if (!paymentOptions.length || !paymentDetails) return;
    
    paymentOptions.forEach(option => {
        option.addEventListener('change', function() {
            const paymentMethod = this.value;
            let paymentHTML = '';
            
            switch(paymentMethod) {
                case 'cash':
                    paymentHTML = `
                        <div class="payment-instruction">
                            <h4><i class="fas fa-money-bill-wave"></i> Cash on Delivery</h4>
                            <p>You'll pay with cash when your order is delivered. No extra fees.</p>
                            <p>Please have exact change ready for the delivery person.</p>
                        </div>
                    `;
                    break;
                case 'visa':
                    paymentHTML = `
                        <div class="payment-card-form">
                            <h4><i class="fab fa-cc-visa"></i> Card Details</h4>
                            <div class="form-group">
                                <label>Card Number</label>
                                <input type="text" class="card-input" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Expiry Date</label>
                                    <input type="text" class="card-input" placeholder="MM/YY">
                                </div>
                                <div class="form-group">
                                    <label>CVV</label>
                                    <input type="text" class="card-input" placeholder="123">
                                </div>
                            </div>
                        </div>
                    `;
                    break;
                case 'instapay':
                    paymentHTML = `
                        <div class="payment-instruction">
                            <h4><i class="fas fa-bolt"></i> InstaPay Instructions</h4>
                            <p>1. Open your banking app and select "InstaPay"</p>
                            <p>2. Send payment to: <strong>Bank XYZ - Account: 1234567890</strong></p>
                            <p>3. Use order ID: <strong>ORD-${Date.now().toString().slice(-6)}</strong> as reference</p>
                            <p>4. Upload payment receipt after completion</p>
                        </div>
                    `;
                    break;
                case 'vodafone':
                    paymentHTML = `
                        <div class="payment-instruction">
                            <h4><i class="fas fa-mobile-alt"></i> Vodafone Cash</h4>
                            <p>1. Open Vodafone Cash app on your phone</p>
                            <p>2. Send payment to: <strong>0100 123 4567</strong></p>
                            <p>3. Total amount: <strong>${totalElement.textContent}</strong></p>
                            <p>4. Take a screenshot of the payment confirmation</p>
                        </div>
                    `;
                    break;
                default:
                    paymentHTML = `<div class="payment-instruction"><p>Select a payment method to proceed</p></div>`;
            }
            
            paymentDetails.innerHTML = paymentHTML;
        });
    });
}

// Process checkout on cart.html
function processCheckout() {
    if (cartItems.length === 0) {
        showNotification('Your cart is empty. Add items before checkout.', 'error');
        return;
    }
    
    const selectedPayment = document.querySelector('input[name="paymentMethod"]:checked');
    if (!selectedPayment) {
        showNotification('Please select a payment method', 'error');
        return;
    }
    
    // Validate card details if Visa is selected
    if (selectedPayment.value === 'visa') {
        const cardNumber = document.querySelector('.card-input')?.value;
        if (!cardNumber || cardNumber.replace(/\s/g, '').length !== 16) {
            showNotification('Please enter a valid 16-digit card number', 'error');
            return;
        }
    }
    
    const paymentMethodText = {
        'cash': 'Cash on Delivery',
        'visa': 'Visa/Mastercard',
        'instapay': 'InstaPay',
        'vodafone': 'Vodafone Cash'
    }[selectedPayment.value];
    
    showNotification(`Order placed successfully! Payment method: ${paymentMethodText}.`);
    
    // In a real app, you would send this data to your backend
    const orderData = {
        items: cartItems,
        paymentMethod: selectedPayment.value,
        timestamp: new Date().toISOString()
    };
    
    console.log('Order data:', orderData);
    
    // Clear cart after successful order
    setTimeout(() => {
        cartItems = [];
        saveCartToStorage();
        updateCartCount();
        updateModalCart();
        
        if (window.location.pathname.includes('cart.html')) {
            renderCartItems();
            updateCartSummary();
            updateEmptyCartMessage();
        }
    }, 2000);
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
    
    if (type === 'error') {
        notification.style.backgroundColor = '#e74c3c';
    } else if (type === 'info') {
        notification.style.backgroundColor = '#3498db';
    } else {
        notification.style.backgroundColor = '#2ecc71';
    }
    
    // Show notification
    notification.style.transform = 'translateY(0)';
    notification.style.opacity = '1';
    
    // Hide after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateY(100px)';
        notification.style.opacity = '0';
    }, 3000);
}

// Setup event listeners
function setupEventListeners() {
    // Cart modal open/close
    if (cartBtn) {
        cartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            cartModal.classList.add('active');
            updateModalCart();
        });
    }
    
    if (closeCartBtn) {
        closeCartBtn.addEventListener('click', function() {
            cartModal.classList.remove('active');
        });
    }
    
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (cartModal.classList.contains('active') && 
            !cartModal.contains(e.target) && 
            e.target !== cartBtn && 
            !cartBtn.contains(e.target)) {
            cartModal.classList.remove('active');
        }
    });
    
    // Add to cart buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart') || 
            e.target.closest('.add-to-cart')) {
            const button = e.target.classList.contains('add-to-cart') ? 
                          e.target : e.target.closest('.add-to-cart');
            const productData = button.dataset.product;
            
            if (productData) {
                addToCart(productData);
            }
        }
    });
    
    // Cart page event delegation (only on cart.html)
    if (window.location.pathname.includes('cart.html')) {
        const cartItemsContainer = document.getElementById('cartItems');
        
        if (cartItemsContainer) {
            cartItemsContainer.addEventListener('click', function(e) {
                const target = e.target;
                const itemId = parseInt(target.closest('button')?.dataset.id || 
                                        target.closest('input')?.dataset.id);
                
                if (!itemId) return;
                
                // Handle minus button
                if (target.closest('.minus-btn')) {
                    updateCartItemQuantity(itemId, -1);
                }
                
                // Handle plus button
                if (target.closest('.plus-btn')) {
                    updateCartItemQuantity(itemId, 1);
                }
                
                // Handle remove button
                if (target.closest('.remove-item-btn')) {
                    removeCartItem(itemId);
                }
            });
            
            // Handle direct quantity input change
            cartItemsContainer.addEventListener('change', function(e) {
                if (e.target.classList.contains('quantity-input')) {
                    const itemId = parseInt(e.target.dataset.id);
                    const newQuantity = parseInt(e.target.value) || 1;
                    updateCartItemQuantity(itemId, 0, newQuantity);
                }
            });
        }
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initCart);

// Export functions for integration with other pages
window.cartFunctions = {
    addToCart: addToCart,
    getCartItems: function() {
        return cartItems;
    },
    getCartTotal: function() {
        const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = subtotal > 1000 ? 0 : 50;
        const tax = subtotal * 0.14;
        return subtotal + shipping + tax;
    },
    clearCart: clearCart
};