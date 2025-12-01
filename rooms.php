<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Browse our comfortable rooms and suites at BODARE Pension House">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Rooms - BODARE Pension House</title>
    
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
            <a href="https://bodarempc.com/" class="nav-logo">
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
                <a href="cart.php" id="cart-link" class="cart-link" style="display: flex; color: white !important;" title="Shopping Cart">
                    <i class="bi bi-cart" style="font-size: 1.5rem; color: white !important;"></i>
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
            <h1>Our Rooms</h1>
            <p>Find the perfect space for your stay.</p>
        </div>
    </section>

    <main class="rooms-page-section">
        <div class="container">
            <!-- Availability Results Banner -->
            <div id="availability-banner" style="display: none; background: #f0f0f0; padding: 20px; margin-bottom: 30px; border-radius: 8px; border-left: 4px solid #b2945b;">
                <h3 style="margin-top: 0; color: #b2945b;">Availability Results</h3>
                <p id="availability-message" style="margin-bottom: 10px;"></p>
                <p id="availability-dates" style="font-size: 0.9em; color: #666;"></p>
            </div>

            <!-- Rooms will be dynamically loaded here -->
            <div id="rooms-container">
                <div class="text-center" style="padding: 40px;">
                    <p>Loading rooms...</p>
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
        // Load and display rooms dynamically
        async function loadRooms() {
            const roomsContainer = document.getElementById('rooms-container');
            
            try {
                if (typeof API === 'undefined') {
                    throw new Error('API configuration not loaded');
                }
                
                // Fetch all active rooms
                const response = await API.booking.getRooms();
                
                if (response.success && response.rooms && response.rooms.length > 0) {
                    roomsContainer.innerHTML = '';
                    
                    // Display each room
                    response.rooms.forEach(room => {
                        const roomCode = room.room_code || room.room_name.toLowerCase().replace(/\s+/g, '');
                        const imagePath = `img/${roomCode}.jpg`;
                        
                        // Format price
                        const priceDisplay = room.price ? `₱${parseFloat(room.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} per night` : 'Price on request';
                        
                        // Format capacity
                        const capacityText = room.capacity ? `Good for ${room.capacity} person${room.capacity > 1 ? 's' : ''}` : 'Capacity varies';
                        
                        // Format amenities
                        const amenitiesText = room.amenities || 'Complimentary Wifi, Television, Private Bathroom';
                        
                        // Description
                        const description = room.description || `${room.room_name} offers a comfortable and well-appointed space for your stay.`;
                        
                        // Create room card
                        const roomCard = document.createElement('div');
                        roomCard.className = 'room-detail-card';
                        roomCard.innerHTML = `
                            <img src="${imagePath}" alt="${room.room_name}" onerror="this.src='img/default-room.jpg'">
                            <div class="room-info">
                                <h2>${room.room_name || 'Room'}</h2>
                                <p class="room-price">${priceDisplay}</p>
                                <p>${description}</p>
                                <ul>
                                    <li><strong>Capacity:</strong> ${capacityText}</li>
                                    <li><strong>Bed Type:</strong> Normal Beds</li>
                                    <li><strong>Services:</strong> ${amenitiesText}</li>
                                </ul>
                                <a href="room-detail.php?room=${roomCode}" class="cta-button">View Details</a>
                            </div>
                        `;
                        
                        roomsContainer.appendChild(roomCard);
                    });
                } else {
                    roomsContainer.innerHTML = '<div class="text-center" style="padding: 40px;"><p>No rooms available at the moment. Please check back later.</p></div>';
                }
            } catch (error) {
                console.error('Error loading rooms:', error);
                roomsContainer.innerHTML = '<div class="text-center" style="padding: 40px;"><p>Unable to load rooms. Please refresh the page or contact us for assistance.</p></div>';
            }
        }
        
        // Display availability results if redirected from index page
        document.addEventListener('DOMContentLoaded', () => {
            // Load rooms first
            loadRooms();
            const availabilityBanner = document.getElementById('availability-banner');
            const availabilityMessage = document.getElementById('availability-message');
            const availabilityDates = document.getElementById('availability-dates');
            
            // Check for availability data in localStorage
            const availabilityData = localStorage.getItem('availabilityData');
            const urlParams = new URLSearchParams(window.location.search);
            
            if (availabilityData || (urlParams.get('checkin') && urlParams.get('checkout'))) {
                let checkIn, checkOut, guests, rooms = [];
                
                if (availabilityData) {
                    try {
                        const data = JSON.parse(availabilityData);
                        checkIn = data.checkIn;
                        checkOut = data.checkOut;
                        guests = data.guests;
                        rooms = data.rooms || [];
                        
                        // Clear the data after displaying
                        localStorage.removeItem('availabilityData');
                    } catch (e) {
                        console.error('Error parsing availability data:', e);
                    }
                } else {
                    // Get from URL parameters
                    checkIn = urlParams.get('checkin');
                    checkOut = urlParams.get('checkout');
                    guests = urlParams.get('guests');
                }
                
                if (checkIn && checkOut) {
                    // Format dates for display
                    const checkInDate = new Date(checkIn + 'T00:00:00');
                    const checkOutDate = new Date(checkOut + 'T00:00:00');
                    const formattedCheckIn = checkInDate.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    const formattedCheckOut = checkOutDate.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    
                    // Calculate nights
                    const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
                    
                    // Display availability information
                    availabilityDates.textContent = `Check-in: ${formattedCheckIn} | Check-out: ${formattedCheckOut} | ${nights} night${nights !== 1 ? 's' : ''} | ${guests || '2'} guest${guests !== '1' ? 's' : ''}`;
                    
                    if (rooms && rooms.length > 0) {
                        const roomNames = rooms.map(room => room.room_name || room.room_type || 'Room').join(', ');
                        availabilityMessage.innerHTML = `<strong>${rooms.length} room${rooms.length !== 1 ? 's' : ''} available:</strong> ${roomNames}`;
                        availabilityBanner.style.borderLeftColor = '#4caf50';
                    } else {
                        availabilityMessage.innerHTML = '<strong>Checking availability...</strong> Please wait while we verify room availability for your selected dates.';
                        availabilityBanner.style.borderLeftColor = '#ff9800';
                        
                        // If we have URL params but no rooms data, try to fetch it
                        if (urlParams.get('checkin') && typeof API !== 'undefined') {
                            API.booking.checkAvailability(checkIn, checkOut, guests)
                                .then(response => {
                                    if (response.success && response.rooms) {
                                        const roomNames = response.rooms.map(room => room.room_name || room.room_type || 'Room').join(', ');
                                        if (response.rooms.length > 0) {
                                            availabilityMessage.innerHTML = `<strong>${response.rooms.length} room${response.rooms.length !== 1 ? 's' : ''} available:</strong> ${roomNames}`;
                                            availabilityBanner.style.borderLeftColor = '#4caf50';
                                        } else {
                                            availabilityMessage.innerHTML = '<strong>No rooms available</strong> for the selected dates. Please try different dates.';
                                            availabilityBanner.style.borderLeftColor = '#f44336';
                                        }
                                    }
                                })
                                .catch(error => {
                                    console.error('Error checking availability:', error);
                                    availabilityMessage.innerHTML = '<strong>Unable to check availability.</strong> Please try again or contact us.';
                                    availabilityBanner.style.borderLeftColor = '#f44336';
                                });
                        }
                    }
                    
                    availabilityBanner.style.display = 'block';
                    
                    // Scroll to banner
                    availabilityBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        });
        
        // Check login status and update header button
        async function updateLoginButton() {
            const loginBtn = document.getElementById('login-account-btn');
            const accountBtn = document.getElementById('my-account-btn');
            
            if (!loginBtn || !accountBtn) return;
            
            try {
                if (typeof API !== 'undefined') {
                    const response = await API.auth.check();
                    if (response.success && response.logged_in) {
                        loginBtn.style.display = 'none';
                        accountBtn.style.display = 'inline-block';
                    } else {
                        loginBtn.style.display = 'inline-block';
                        accountBtn.style.display = 'none';
                    }
                } else {
                    loginBtn.style.display = 'inline-block';
                    accountBtn.style.display = 'none';
                }
            } catch (error) {
                loginBtn.style.display = 'inline-block';
                accountBtn.style.display = 'none';
            }
        }
        
        if (typeof API !== 'undefined') {
            updateLoginButton();
        } else {
            window.addEventListener('load', () => {
                if (typeof API !== 'undefined') {
                    updateLoginButton();
                }
            });
        }
        
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('ServiceWorker registration successful:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('ServiceWorker registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>