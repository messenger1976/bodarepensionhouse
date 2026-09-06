<?php
$pageSeo = [
    'title' => 'Shopping Cart | BODARE Pension House',
    'description' => 'Review rooms in your booking cart at BODARE Pension House.',
    'canonical_path' => 'cart.php',
    'robots' => 'noindex,nofollow',
];
$enableAds = false;
include __DIR__ . '/includes/site-head.php';
?>
<body>

    <?php
    $headerConfig = ['logo_href' => 'index.php'];
    include __DIR__ . '/includes/site-header.php';
?>


    <section class="app-page-hero">
        <div class="page-header-content app-section" style="padding-top:0;padding-bottom:0;">
            <h1>Shopping Cart</h1>
            <p>Review your selected rooms and services</p>
        </div>
    </section>

    <main class="content-section app-section">
        <div class="container">
            <div id="cart-container">
                <!-- Cart items will be loaded here -->
                <div id="empty-cart-message" style="text-align: center; padding: 3rem; display: none;">
                    <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h2>Your cart is empty</h2>
                    <p style="color: #666; margin-bottom: 2rem;">Start adding rooms to your cart to continue.</p>
                    <a href="rooms.php" class="cta-button">Browse Rooms</a>
                </div>
                
                <div id="cart-items-container" style="display: none;">
                    <div class="cart-items">
                        <!-- Cart items will be inserted here -->
                    </div>
                    
                    <!-- Global Extra Services Section -->
                    <div class="cart-services-section app-card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.15rem; margin-bottom: 1rem; color: #022c22;">Extra Services</h3>
                        <div class="services-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                            <div class="service-item-cart">
                                <input type="checkbox" id="service-pet" data-name="Pet-Friendly Amenities" data-cost="500" onchange="updateCartServices()">
                                <label for="service-pet">
                                    <strong>Pet-Friendly Amenities</strong>
                                    <span>₱500 / stay</span>
                                </label>
                            </div>
                            <div class="service-item-cart">
                                <input type="checkbox" id="service-spa" data-name="Spa Services" data-cost="1000" onchange="updateCartServices()">
                                <label for="service-spa">
                                    <strong>Spa Services</strong>
                                    <span>₱1,000 / person</span>
                                </label>
                            </div>
                            <div class="service-item-cart">
                                <input type="checkbox" id="service-laundry" data-name="Laundry and Cleaning" data-cost="250" onchange="updateCartServices()">
                                <label for="service-laundry">
                                    <strong>Laundry and Cleaning</strong>
                                    <span>₱250 / stay</span>
                                </label>
                            </div>
                        </div>
                        <div id="services-total" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee; font-size: 1.1rem;">
                            <strong>Services Total: <span id="services-total-amount">₱0.00</span></strong>
                        </div>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3>Order Summary</h3>
                            <div class="summary-row">
                                <span>Rooms Subtotal</span>
                                <span id="cart-subtotal">₱0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Services</span>
                                <span id="cart-services-total">₱0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Total Items</span>
                                <span id="cart-item-count">0</span>
                            </div>
                            <div class="summary-total-row">
                                <span>Total</span>
                                <strong id="cart-total">₱0.00</strong>
                            </div>
                            <div class="cart-summary-actions">
                                <a href="checkout.php" class="cta-button">Proceed to Checkout</a>
                                <a href="rooms.php" class="cta-button-secondary">Continue Shopping</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php
    include __DIR__ . '/includes/site-footer.php';
?>
    
    <script src="api-config.js?v=<?php echo @filemtime(__DIR__ . '/api-config.js') ?: time(); ?>"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
    <script>
        // Load and display cart items
        function loadCartItems() {
            let cart = getCart();
            if (Array.isArray(cart) && cart.length > 0 && typeof applyCartItemPricing === 'function') {
                cart = cart.map(item => applyCartItemPricing(item));
                saveCart(cart);
            }
            const cartItemsContainer = document.querySelector('.cart-items');
            const emptyCartMessage = document.getElementById('empty-cart-message');
            const cartItemsDiv = document.getElementById('cart-items-container');
            
            if (!cart || cart.length === 0) {
                emptyCartMessage.style.display = 'block';
                cartItemsDiv.style.display = 'none';
                return;
            }
            
            emptyCartMessage.style.display = 'none';
            cartItemsDiv.style.display = 'block';
            cartItemsContainer.innerHTML = '';
            
            cart.forEach(item => {
                const cartItem = createCartItemElement(item);
                cartItemsContainer.appendChild(cartItem);
            });
            
            updateCartSummary();
        }
        
        function createCartItemElement(item) {
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.dataset.cartId = item.cartId;
            
            const servicesList = item.services && item.services.length > 0 
                ? item.services.map(s => `${s.name} (₱${s.cost.toLocaleString()})`).join(', ')
                : 'None';

            const roomSubtotal = typeof getCartItemRoomSubtotal === 'function'
                ? getCartItemRoomSubtotal(item)
                : (item.totalAmount || 0);
            const extraBedTotal = typeof getCartItemExtraBedTotal === 'function'
                ? getCartItemExtraBedTotal(item)
                : 0;
            const extraBeds = parseInt(item.extraBeds, 10) || 0;
            const extraBedCost = parseFloat(item.extraBedCost) || (typeof getDefaultExtraBedPrice === 'function' ? getDefaultExtraBedPrice() : 199);
            const extraBedControls = `
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem;">
                    <strong>Extra Bed (₱${extraBedCost.toLocaleString()}/night):</strong>
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid #ddd; border-radius: 4px; padding: 0.25rem 0.5rem;">
                        <button type="button" onclick="changeCartExtraBeds('${item.cartId}', -1)" style="border: none; background: none; cursor: pointer; font-size: 1rem;">-</button>
                        <span>${extraBeds}</span>
                        <button type="button" onclick="changeCartExtraBeds('${item.cartId}', 1)" style="border: none; background: none; cursor: pointer; font-size: 1rem;">+</button>
                    </div>
                </div>
            `;
            const extraBedLine = extraBedTotal > 0
                ? `<p><strong>Extra Bed Total:</strong> ₱${extraBedTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>`
                : '';
            
            div.innerHTML = `
                <div class="cart-item-image">
                    <img src="${item.imageUrl}" alt="${item.roomName}">
                </div>
                <div class="cart-item-details">
                    <h3>${item.roomName}</h3>
                    <div class="cart-item-info">
                        <p><strong>Check-in:</strong> ${formatDateDisplay(item.checkin)}</p>
                        <p><strong>Check-out:</strong> ${formatDateDisplay(item.checkout)}</p>
                        <p><strong>Nights:</strong> ${item.nights}</p>
                        <p><strong>Guests:</strong> ${item.adults} Adult(s), ${item.children} Child(ren)</p>
                        <p><strong>Rooms:</strong> ${item.rooms}</p>
                        <p><strong>Room Rate:</strong> ₱${roomSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                        ${extraBedControls}
                        ${extraBedLine}
                        <p style="color: #999; font-size: 0.85rem;"><em>Services can be added below</em></p>
                    </div>
                </div>
                <div class="cart-item-price">
                    <strong>${item.total}</strong>
                    <button class="remove-item-btn" onclick="removeCartItem('${item.cartId}')" title="Remove item">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            
            return div;
        }
        
        function formatDateDisplay(dateString) {
            if (typeof bodareParseServerDate === 'function' && /[ T]\d{2}:\d{2}/.test(String(dateString || ''))) {
                const parsed = bodareParseServerDate(dateString);
                if (parsed) {
                    return parsed.toLocaleString('en-US', {
                        year: 'numeric', month: 'short', day: 'numeric',
                        hour: '2-digit', minute: '2-digit', hour12: true,
                        timeZone: window.BODARE_TIMEZONE || 'Asia/Manila'
                    });
                }
            }
            if (typeof parseDateLocal === 'function') {
                const parsed = parseDateLocal(dateString);
                if (parsed) {
                    const hasTime = /[ T]\d{2}:\d{2}/.test(String(dateString || ''));
                    return parsed.toLocaleString('en-US', hasTime
                        ? { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }
                        : { year: 'numeric', month: 'short', day: 'numeric' });
                }
            }
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', timeZone: 'Asia/Manila' });
        }
        
        function removeCartItem(cartId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                removeFromCart(cartId);
                loadCartItems();
            }
        }

        function changeCartExtraBeds(cartId, delta) {
            if (typeof updateCartExtraBeds === 'function') {
                updateCartExtraBeds(cartId, delta);
                loadCartItems();
            }
        }
        
        // Available services
        const availableServices = [
            { id: 'service-pet', name: 'Pet-Friendly Amenities', cost: 500 },
            { id: 'service-spa', name: 'Spa Services', cost: 1000 },
            { id: 'service-laundry', name: 'Laundry and Cleaning', cost: 250 }
        ];
        
        function updateCartServices() {
            const selectedServices = [];
            availableServices.forEach(service => {
                const checkbox = document.getElementById(service.id);
                if (checkbox && checkbox.checked) {
                    selectedServices.push({
                        name: service.name,
                        cost: service.cost
                    });
                }
            });
            
            // Save services to localStorage
            localStorage.setItem('cartServices', JSON.stringify(selectedServices));

            // Also attach services to every cart room so they survive login/session refresh.
            try {
                const cart = typeof getCart === 'function' ? getCart() : JSON.parse(localStorage.getItem('bookingCart') || '[]');
                if (Array.isArray(cart) && cart.length > 0) {
                    const updatedCart = cart.map(item => ({
                        ...item,
                        services: selectedServices
                    }));
                    if (typeof withExpectedCartMutation === 'function') {
                        withExpectedCartMutation('update-cart-services', () => {
                            localStorage.setItem('bookingCart', JSON.stringify(updatedCart));
                        });
                    } else {
                        localStorage.setItem('bookingCart', JSON.stringify(updatedCart));
                    }
                }
            } catch (e) {
                console.warn('Unable to attach services to cart items:', e);
            }
            
            // Update services total display
            const servicesTotal = selectedServices.reduce((sum, s) => sum + s.cost, 0);
            const servicesTotalEl = document.getElementById('services-total-amount');
            const cartServicesTotalEl = document.getElementById('cart-services-total');
            
            if (servicesTotalEl) {
                servicesTotalEl.textContent = `₱${servicesTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            }
            if (cartServicesTotalEl) {
                cartServicesTotalEl.textContent = `₱${servicesTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            }
            
            // Update cart total
            updateCartSummary();
        }
        
        function loadCartServices() {
            const savedServices = localStorage.getItem('cartServices');
            if (savedServices) {
                const services = JSON.parse(savedServices);
                availableServices.forEach(service => {
                    const checkbox = document.getElementById(service.id);
                    if (checkbox) {
                        const isSelected = services.some(s => s.name === service.name);
                        checkbox.checked = isSelected;
                    }
                });
            }
            updateCartServices();
        }
        
        function updateCartSummary() {
            const cart = getCart();
            const roomsSubtotal = getCartTotal();
            
            // Get services total
            const savedServices = localStorage.getItem('cartServices');
            let servicesTotal = 0;
            if (savedServices) {
                const services = JSON.parse(savedServices);
                servicesTotal = services.reduce((sum, s) => sum + s.cost, 0);
            }
            
            const total = roomsSubtotal + servicesTotal;
            const itemCount = cart.length;
            
            document.getElementById('cart-subtotal').textContent = `₱${roomsSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('cart-services-total').textContent = `₱${servicesTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('cart-item-count').textContent = itemCount;
            document.getElementById('cart-total').textContent = `₱${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
        
        // Load cart on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadCartItems();
            loadCartServices();
            updateCartBadge();
        });
    </script>
</body>
</html>



