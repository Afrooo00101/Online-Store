// checkout.js
document.addEventListener('DOMContentLoaded', function() {
    // Initialize
    initCheckout();
    setupEventListeners();
    loadSavedAddress();
    
    // Dark mode toggle
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }
    
    // Initialize payment method display
    showPaymentDetails('cod');
});

function initCheckout() {
    // Calculate initial totals
    updateShippingPrice();
    updateOrderTotals();
}

function setupEventListeners() {
    // Shipping method change
    document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updateShippingPrice();
            updateOrderTotals();
        });
    });
    
    // Payment method change
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            showPaymentDetails(this.value);
        });
    });
    
    // Apply promo code
    document.getElementById('applyPromo')?.addEventListener('click', applyPromoCode);
    document.getElementById('promoCode')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyPromoCode();
        }
    });
    
    // Save cart for later
    document.getElementById('saveCartBtn')?.addEventListener('click', saveCartForLater);
    
    // Form submission
    document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return;
        }
        
        // Process payment based on method
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        if (paymentMethod === 'card' && !validateCardDetails()) {
            e.preventDefault();
            showNotification('Please enter valid card details', 'error');
            return;
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
        }
    });
    
    // Apple Pay/Google Pay buttons
    document.getElementById('applePayBtn')?.addEventListener('click', processApplePay);
    document.getElementById('googlePayBtn')?.addEventListener('click', processGooglePay);
    
    // Card input formatting
    document.getElementById('cardNumber')?.addEventListener('input', formatCardNumber);
    document.getElementById('cardExpiry')?.addEventListener('input', formatCardExpiry);
    document.getElementById('cardCvv')?.addEventListener('input', formatCardCVV);
}

function updateShippingPrice() {
    const shippingMethod = document.querySelector('input[name="shipping_method"]:checked').value;
    let shippingPrice = 50;
    
    switch(shippingMethod) {
        case 'express':
            shippingPrice = 100;
            break;
        case 'next_day':
            shippingPrice = 150;
            break;
    }
    
    document.getElementById('shippingDisplay').textContent = `LE ${shippingPrice.toFixed(2)}`;
}

function updateOrderTotals() {
    // Get values from display (in a real app, calculate from cart data)
    const subtotal = parseFloat(document.getElementById('subtotalDisplay').textContent.replace('LE ', ''));
    const shipping = parseFloat(document.getElementById('shippingDisplay').textContent.replace('LE ', ''));
    const tax = subtotal * 0.14;
    const total = subtotal + shipping + tax;
    
    document.getElementById('taxDisplay').textContent = `LE ${tax.toFixed(2)}`;
    document.getElementById('totalDisplay').textContent = `LE ${total.toFixed(2)}`;
    
    // Update hidden fields for form submission
    const totalInput = document.getElementById('orderTotal');
    if (!totalInput) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.id = 'orderTotal';
        input.name = 'order_total';
        input.value = total;
        document.getElementById('checkoutForm').appendChild(input);
    } else {
        totalInput.value = total;
    }
}

function showPaymentDetails(method) {
    // Hide all payment details
    document.querySelectorAll('.payment-details').forEach(el => {
        el.style.display = 'none';
        el.classList.remove('active');
    });
    
    // Show selected payment details
    const detailsId = method + 'Details';
    const detailsElement = document.getElementById(detailsId);
    if (detailsElement) {
        detailsElement.style.display = 'block';
        detailsElement.classList.add('active');
    }
}

function applyPromoCode() {
    const promoInput = document.getElementById('promoCode');
    const promoCode = promoInput.value.trim().toUpperCase();
    
    if (!promoCode) {
        showNotification('Please enter a promo code', 'error');
        return;
    }
    
    // Valid promo codes
    const validPromos = {
        'JERSEY10': 0.1,  // 10% off
        'WELCOME20': 0.2, // 20% off
        'FREESHIP': 50,   // Free shipping
        'SAVE50': 50      // LE 50 off
    };
    
    if (validPromos[promoCode]) {
        const discount = validPromos[promoCode];
        const subtotalElement = document.getElementById('subtotalDisplay');
        let subtotal = parseFloat(subtotalElement.textContent.replace('LE ', ''));
        
        if (promoCode === 'FREESHIP') {
            // Free shipping
            document.getElementById('shippingDisplay').textContent = 'LE 0.00';
        } else if (promoCode === 'SAVE50') {
            // LE 50 off subtotal
            subtotal = Math.max(0, subtotal - 50);
            subtotalElement.textContent = `LE ${subtotal.toFixed(2)}`;
        } else {
            // Percentage discount
            const discountAmount = subtotal * discount;
            subtotal -= discountAmount;
            subtotalElement.textContent = `LE ${subtotal.toFixed(2)}`;
            
            // Show discount applied
            const discountRow = document.querySelector('.discount-row') || createDiscountRow();
            discountRow.innerHTML = `
                <span>Discount (${promoCode})</span>
                <span style="color: #2ecc71;">-LE ${discountAmount.toFixed(2)}</span>
            `;
        }
        
        updateOrderTotals();
        showNotification(`Promo code "${promoCode}" applied successfully!`, 'success');
        promoInput.value = '';
        
        // Save to session
        sessionStorage.setItem('appliedPromo', promoCode);
    } else {
        showNotification('Invalid promo code', 'error');
    }
}

function createDiscountRow() {
    const totalsDiv = document.querySelector('.order-totals');
    const totalRow = totalsDiv.querySelector('.grand-total');
    
    const discountRow = document.createElement('div');
    discountRow.className = 'total-row discount-row';
    totalsDiv.insertBefore(discountRow, totalRow);
    
    return discountRow;
}

function saveCartForLater() {
    const cartData = {
        items: getCartFromSession(),
        savedAt: new Date().toISOString()
    };
    
    localStorage.setItem('savedCart', JSON.stringify(cartData));
    showNotification('Cart saved for later! You can restore it anytime from your profile.', 'success');
    
    // Optional: Clear current cart
    if (confirm('Would you like to clear your current cart?')) {
        fetch('cart.php?action=clear', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(() => {
            window.location.href = 'index.php';
        });
    }
}

function getCartFromSession() {
    // This would fetch cart items from your backend
    // For now, return dummy data
    return JSON.parse(localStorage.getItem('jerseyWearCart') || '[]');
}

function loadSavedAddress() {
    const savedAddress = localStorage.getItem('savedAddress');
    if (savedAddress) {
        const address = JSON.parse(savedAddress);
        document.getElementById('full_name').value = address.full_name || '';
        document.getElementById('email').value = address.email || '';
        document.getElementById('phone').value = address.phone || '';
        document.getElementById('address').value = address.address || '';
        document.getElementById('city').value = address.city || '';
        document.getElementById('postal_code').value = address.postal_code || '';
        document.getElementById('country').value = address.country || '';
    }
}

function validateForm() {
    const requiredFields = document.querySelectorAll('#checkoutForm [required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#e74c3c';
            
            // Add error message
            if (!field.nextElementSibling?.classList.contains('error-message')) {
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.style.cssText = 'color: #e74c3c; font-size: 12px; margin-top: 5px;';
                errorMsg.textContent = 'This field is required';
                field.parentNode.appendChild(errorMsg);
            }
        } else {
            field.style.borderColor = '#ddd';
            const errorMsg = field.parentNode.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        }
    });
    
    if (!document.getElementById('terms').checked) {
        isValid = false;
        showNotification('Please agree to the Terms and Conditions', 'error');
    }
    
    return isValid;
}

function validateCardDetails() {
    const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
    const cardExpiry = document.getElementById('cardExpiry').value;
    const cardCvv = document.getElementById('cardCvv').value;
    
    if (cardNumber.length !== 16 || !/^\d+$/.test(cardNumber)) {
        return false;
    }
    
    if (!/^\d{2}\/\d{2}$/.test(cardExpiry)) {
        return false;
    }
    
    if (cardCvv.length < 3 || cardCvv.length > 4 || !/^\d+$/.test(cardCvv)) {
        return false;
    }
    
    return true;
}

function formatCardNumber(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    e.target.value = value.substring(0, 19);
}

function formatCardExpiry(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value.substring(0, 5);
}

function formatCardCVV(e) {
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value.substring(0, 4);
}

async function processApplePay() {
    if (!window.ApplePaySession) {
        showNotification('Apple Pay is not available on this device', 'error');
        return;
    }
    
    const paymentRequest = {
        countryCode: 'EG',
        currencyCode: 'EGP',
        total: {
            label: 'Jersey Wear',
            amount: document.getElementById('totalDisplay').textContent.replace('LE ', '')
        }
    };
    
    const session = new ApplePaySession(3, paymentRequest);
    
    session.onvalidatemerchant = async event => {
        // Validate merchant with your backend
        try {
            const response = await fetch('validate_apple_pay.php', {
                method: 'POST',
                body: JSON.stringify({ validationURL: event.validationURL })
            });
            const merchantSession = await response.json();
            session.completeMerchantValidation(merchantSession);
        } catch (error) {
            session.abort();
            showNotification('Payment failed', 'error');
        }
    };
    
    session.onpaymentauthorized = event => {
        // Process payment
        session.completePayment(ApplePaySession.STATUS_SUCCESS);
        document.querySelector('input[name="payment_method"][value="wallet"]').checked = true;
        showPaymentDetails('wallet');
        showNotification('Apple Pay payment authorized!', 'success');
    };
    
    session.begin();
}

async function processGooglePay() {
    if (!window.PaymentRequest) {
        showNotification('Google Pay is not available on this device', 'error');
        return;
    }
    
    const supportedMethods = [{
        supportedMethods: 'https://google.com/pay',
        data: {
            environment: 'TEST',
            apiVersion: 2,
            apiVersionMinor: 0,
            merchantInfo: {
                merchantId: 'BCR2DN6TZ6W4HZ55',
                merchantName: 'Jersey Wear'
            },
            allowedPaymentMethods: [{
                type: 'CARD',
                parameters: {
                    allowedAuthMethods: ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
                    allowedCardNetworks: ['MASTERCARD', 'VISA']
                }
            }]
        }
    }];
    
    const paymentDetails = {
        total: {
            label: 'Total',
            amount: {
                currency: 'EGP',
                value: document.getElementById('totalDisplay').textContent.replace('LE ', '')
            }
        }
    };
    
    const request = new PaymentRequest(supportedMethods, paymentDetails);
    
    try {
        const response = await request.show();
        await response.complete('success');
        document.querySelector('input[name="payment_method"][value="wallet"]').checked = true;
        showPaymentDetails('wallet');
        showNotification('Google Pay payment successful!', 'success');
    } catch (error) {
        showNotification('Payment cancelled', 'info');
    }
}

function showNotification(message, type = 'success') {
    // Create notification element if it doesn't exist
    let notification = document.getElementById('checkoutNotification');
    
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'checkoutNotification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            transform: translateY(-100px);
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
        notification.style.transform = 'translateY(-100px)';
        notification.style.opacity = '0';
    }, 3000);
}

// Auto-save address when leaving form
window.addEventListener('beforeunload', function() {
    if (document.getElementById('save_address')?.checked) {
        const address = {
            full_name: document.getElementById('full_name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            address: document.getElementById('address').value,
            city: document.getElementById('city').value,
            postal_code: document.getElementById('postal_code').value,
            country: document.getElementById('country').value
        };
        localStorage.setItem('savedAddress', JSON.stringify(address));
    }
});