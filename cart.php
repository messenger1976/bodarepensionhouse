<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Shopping Cart - BODARE Pension House">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Shopping Cart - BODARE Pension House</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="img/logo.png">
    <link rel="icon" type="image/png" href="img/logo.png">
    
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Jost:wght@200;300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>

    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="nav-logo">
                <img src="img/logo.png" alt="Bodare Logo" class="logo-img">
            </a>
            <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php#about" class="nav-link">About</a></li>
                <li class="nav-item"><a href="rooms.php" class="nav-link">Rooms</a></li>
                <li class="nav-item"><a href="amenities.php" class="nav-link">Amenities</a></li>
                <li class="nav-item"><a href="gallery.php" class="nav-link">Gallery</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
            </ul>
            <div class="header-actions">
                <a href="cart.php" id="cart-link" class="cart-link" style="display: flex;" title="Shopping Cart">
                    <i class="bi bi-cart" style="font-size: 1.5rem;"></i>
                    <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                </a>
                <a href="login.php" id="login-account-btn" class="cta-button-secondary" style="display: none;">Login</a>
                <a href="customer-dashboard.php" id="my-account-btn" class="cta-button-secondary" style="display: none;">My Account</a>
                <a href="rooms.php" class="cta-button">Book Now</a>
            </div>
        </nav>
    </header>

    <section class="page-header">
        <div class="page-header-content">
            <h1>Shopping Cart</h1>
            <p>Review your selected rooms and services</p>
        </div>
    </section>

    <main class="content-section">
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
                    <div class="cart-services-section" style="background: #fff; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 2rem;">
                        <h3 style="font-family: var(--font-primary); font-size: 1.5rem; margin-bottom: 1.5rem; color: var(--dark-blue);">Extra Services</h3>
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
                            <a href="checkout.php" class="cta-button" style="width: 100%; margin-top: 1.5rem; text-align: center;">Proceed to Checkout</a>
                            <a href="rooms.php" class="cta-button-secondary" style="width: 100%; margin-top: 0.5rem; text-align: center;">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<footer id="contact" class="site-footer">
    <div class="container">
        <div class="newsletter-section">
            <h3>Join Our Newsletter</h3>
            <p>Sign up to our newsletter to receive our latest news about offers & promotions.</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your email address">
                <button type="submit">Subscribe</button>
            </form>
        </div>

        <div class="footer-grid">
            <div class="footer-column">
                <h4>About Us</h4>
                <p>Bodare and Community Multi-Purpose Cooperative offers comfortable and affordable lodging in the heart of Tagbilaran City, providing a welcoming stay for all our guests.</p>
            </div>
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="rooms.php">Rooms</a></li>
                    <li><a href="amenities.php">Amenities</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Contact</h4>
                <p>
                    123 Luxury Lane<br>
                    Tagbilaran City, Bohol 6300<br>
                    <a href="tel:+63384110000">(038) 411-0000</a><br>
                    <a href="mailto:reservations@bodarecoop.com">reservations@bodarecoop.com</a>
                </p>
            </div>
            <div class="footer-column">
                <h4>Get Social</h4>
                <p>Follow us on social platforms and keep in touch.</p>
                <div class="social-icons">
                    <a href="#">F</a>
                    <a href="#">T</a>
                    <a href="#">I</a>
                    <a href="#">Y</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 Bodare and Community Multi-Purpose Cooperative. All Rights Reserved.</p>
            <div class="payment-methods">
                <span>Payment methods:</span>
                <span>Visa</span>
                <span>Cash</span>
                <span>GCash</span>
            </div>
        </div>
    </div>
</footer>
    
    <script src="api-config.js"></script>
    <script src="script.js"></script>
    <script>
        // Load and display cart items
        function loadCartItems() {
            const cart = getCart();
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
                        ${item.extraBeds > 0 ? `<p><strong>Extra Beds:</strong> ${item.extraBeds}</p>` : ''}
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
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }
        
        function removeCartItem(cartId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                removeFromCart(cartId);
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

