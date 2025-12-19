<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="BODARE Pension House - Comfortable and affordable lodging in the heart of Tagbilaran City">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>BODARE Pension House</title>
    
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
                <img src="img/logo.png" alt="BODARE Logo" class="logo-img">
            </a>
            <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu">
                <li class="nav-item"><a href="#about" class="nav-link">About</a></li>
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

<section class="hero">
    <div class="hero-content">
        <h1 class="hero-title">BODARE PENSION HOUSE</h1>
        <p class="hero-subtitle">Your serene escape awaits.</p>

        <div class="hero-booking-container">
            <form class="booking-form">
                <div class="form-group">
                    <label for="checkin">Check-In</label>
                    <input type="date" id="checkin" name="checkin" required>
                </div>
                <div class="form-group">
                    <label for="checkout">Check-Out</label>
                    <input type="date" id="checkout" name="checkout" required>
                </div>
                <div class="form-group">
                    <label for="guests">Guests</label>
                    <select id="guests" name="guests">
                        <option value="1">1 Guest</option>
                        <option value="2" selected>2 Guests</option>
                        <option value="3">3 Guests</option>
                        <option value="4">4 Guests</option>
                    </select>
                </div>
                <button type="submit" class="cta-button form-button">Check Availability</button>
            </form>
        </div>
        </div>
</section>

    <section id="about" class="content-section">
        <div class="container">
            <h2 class="section-title">Welcome to BODARE Pension House</h2>
            <div class="about-content">
                <div class="about-text">
                    <p class="lead-text">
                        Experience comfort, convenience, and affordability at BODARE Pension House, your ideal home away from home in the heart of Tagbilaran City.
                    </p>
                    <p>
                        Strategically located within walking distance of ICM Mall and the soon-to-open SM City Tagbilaran, BODARE Pension House offers you the perfect base for exploring Bohol's capital city. Whether you're here for business, leisure, or a quick stopover, our prime location puts you right where you need to be.
                    </p>
                    <p>
                        At BODARE Pension House, we believe that quality accommodation shouldn't break the bank. We offer affordable rates without compromising on comfort and essential amenities. Our well-appointed rooms provide a peaceful retreat after a day of exploring the beautiful island of Bohol.
                    </p>
                    <div class="features-grid">
                        <div class="feature-item">
                            <i class="bi bi-geo-alt-fill"></i>
                            <h4>Prime Location</h4>
                            <p>Walking distance to ICM Mall and soon-to-open SM City Tagbilaran</p>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-wifi"></i>
                            <h4>Free WiFi</h4>
                            <p>Stay connected with complimentary high-speed internet access</p>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <h4>Backup Power</h4>
                            <p>Uninterrupted power supply even during blackouts</p>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-cup-hot-fill"></i>
                            <h4>Restaurants Nearby</h4>
                            <p>Easy access to various dining options and local eateries</p>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-currency-dollar"></i>
                            <h4>Affordable Rates</h4>
                            <p>Budget-friendly accommodation without sacrificing quality</p>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-shield-check"></i>
                            <h4>Safe & Secure</h4>
                            <p>24/7 security and a welcoming, family-friendly environment</p>
                        </div>
                    </div>
                    <p class="closing-text">
                        Managed by Bodare and Community Multi-Purpose Cooperative, we take pride in providing exceptional service and creating memorable experiences for all our guests. Book your stay with us today and discover why BODARE Pension House is the preferred choice for travelers in Tagbilaran City.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="rooms" class="content-section bg-light">
        <div class="container">
            <h2 class="section-title">Rooms & Suites</h2>
            <div class="room-grid">
                <div class="room-card">
                    <img src="img/dormitory.jpg" alt="Dormitory Room">
                    <h3>Dormitory</h3>
                    <p class="room-details"><strong>Price:</strong> ₱299 per head<br><strong>Capacity:</strong> Min. 8 persons<br><strong>Services:</strong> Wifi, Television, Bathroom</p>
                </div>
                <div class="room-card">
                    <img src="img/standard.jpg" alt="Standard Room">
                    <h3>Standard Room</h3>
                    <p class="room-details"><strong>Price:</strong> ₱999 per night<br><strong>Capacity:</strong> Good for 2 persons<br><strong>Services:</strong> Wifi, Television, Bathroom</p>
                </div> 
                <div class="room-card">
                    <img src="img/deluxeb.jpg" alt="Deluxe B Room">
                    <h3>Deluxe B Room</h3>
                    <p class="room-details"><strong>Price:</strong> ₱1,199 per night<br><strong>Capacity:</strong> Good for 2 persons<br><strong>Services:</strong> Wifi, Television, Bathroom</p>
                </div>
                <div class="room-card">
                    <img src="img/deluxea.jpg" alt="Deluxe A Room">
                    <h3>Deluxe A Room</h3>
                    <p class="room-details"><strong>Price:</strong> ₱1,299 per night<br><strong>Capacity:</strong> Good for 3 persons<br><strong>Services:</strong> Wifi, Television, Bathroom</p>
                </div>
                <div class="room-card">
                    <img src="img/ambassador.jpg" alt="Ambassador Room">
                    <h3>Ambassador Room</h3>
                    <p class="room-details"><strong>Price:</strong> ₱1,399 per night<br><strong>Capacity:</strong> Good for 3 persons<br><strong>Services:</strong> Wifi, Television, Bathroom</p>
                </div>                                                               
                <div class="room-card">
                    <img src="img/executive.jpg" alt="Executive Room">
                    <h3>Executive Room</h3>
                    <p class="room-details"><strong>Price:</strong> ₱1,999 per night<br><strong>Capacity:</strong> Good for 4 persons<br><strong>Services:</strong> Wifi, Television, Bathroom</p>
                </div>
            </div>
        </div>
    </section>

    <section id="video" class="content-section video-section-full-width">
    <div class="container">
        <h2 class="section-title">A Cinematic Journey</h2>
        <p class="section-text">
            Immerse yourself in the BODARE Pension House experience. Press play to discover the moments, the details, and the unforgettable atmosphere that awaits you.
        </p>
    </div>

<div class="video-wrapper">
    <video autoplay loop muted playsinline>
        <source src="video/sample.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>
</section>
    
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
        // Date validation for booking form
        document.addEventListener('DOMContentLoaded', () => {
            const checkinInput = document.getElementById('checkin');
            const checkoutInput = document.getElementById('checkout');
            const bookingForm = document.querySelector('.booking-form');
            
            if (checkinInput && checkoutInput) {
                // Set minimum date to today for both inputs
                const today = new Date();
                const todayString = today.toISOString().split('T')[0];
                checkinInput.setAttribute('min', todayString);
                checkoutInput.setAttribute('min', todayString);
                
                // Update check-out minimum date when check-in changes
                checkinInput.addEventListener('change', () => {
                    const checkinDate = new Date(checkinInput.value);
                    const checkoutDate = new Date(checkoutInput.value);
                    
                    // Set minimum check-out date to check-in date + 1 day
                    const minCheckoutDate = new Date(checkinDate);
                    minCheckoutDate.setDate(minCheckoutDate.getDate() + 1);
                    const minCheckoutString = minCheckoutDate.toISOString().split('T')[0];
                    checkoutInput.setAttribute('min', minCheckoutString);
                    
                    // If current check-out is before or equal to check-in, clear it
                    if (checkoutInput.value && checkoutDate <= checkinDate) {
                        checkoutInput.value = '';
                    }
                });
                
                // Validate check-out date when it changes
                checkoutInput.addEventListener('change', () => {
                    const checkinDate = new Date(checkinInput.value);
                    const checkoutDate = new Date(checkoutInput.value);
                    
                    if (checkinInput.value && checkoutDate <= checkinDate) {
                        alert('Check-out date must be after check-in date.');
                        checkoutInput.value = '';
                        checkoutInput.focus();
                    }
                });
                
                // Handle form submission - check availability
                if (bookingForm) {
                    bookingForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        
                        const checkinDate = new Date(checkinInput.value);
                        const checkoutDate = new Date(checkoutInput.value);
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        
                        // Check if dates are in the past
                        if (checkinDate < today) {
                            alert('Check-in date cannot be in the past.');
                            checkinInput.focus();
                            return false;
                        }
                        
                        if (checkoutDate < today) {
                            alert('Check-out date cannot be in the past.');
                            checkoutInput.focus();
                            return false;
                        }
                        
                        // Check if check-out is after check-in
                        if (checkoutDate <= checkinDate) {
                            alert('Check-out date must be after check-in date.');
                            checkoutInput.focus();
                            return false;
                        }
                        
                        // Get form values
                        const checkIn = checkinInput.value;
                        const checkOut = checkoutInput.value;
                        const guests = document.getElementById('guests').value;
                        
                        // Show loading state
                        const submitButton = bookingForm.querySelector('button[type="submit"]');
                        const originalButtonText = submitButton.textContent;
                        submitButton.disabled = true;
                        submitButton.textContent = 'Checking Availability...';
                        
                        try {
                            // Check if API is available
                            if (typeof API === 'undefined') {
                                throw new Error('API configuration not loaded. Please refresh the page.');
                            }
                            
                            // Call API to check availability
                            const response = await API.booking.checkAvailability(checkIn, checkOut, guests);
                            
                            if (response.success) {
                                // Store availability data in localStorage
                                localStorage.setItem('availabilityData', JSON.stringify({
                                    checkIn: checkIn,
                                    checkOut: checkOut,
                                    guests: guests,
                                    rooms: response.rooms || [],
                                    timestamp: new Date().toISOString()
                                }));
                                            
                                // Redirect to rooms page with dates as URL parameters
                                const params = new URLSearchParams({
                                    checkin: checkIn,
                                    checkout: checkOut,
                                    guests: guests
                                });
                                window.location.href = `rooms.php?${params.toString()}`;
                            } else {
                                alert(response.message || 'Failed to check availability. Please try again.');
                                submitButton.disabled = false;
                                submitButton.textContent = originalButtonText;
                            }
                        } catch (error) {
                            console.error('Availability check error:', error);
                            alert(error.message || 'An error occurred while checking availability. Please try again.');
                            submitButton.disabled = false;
                            submitButton.textContent = originalButtonText;
                        }
                    });
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
                    // If API not loaded, show login button
                    loginBtn.style.display = 'inline-block';
                    accountBtn.style.display = 'none';
                }
            } catch (error) {
                // On error, show login button
                loginBtn.style.display = 'inline-block';
                accountBtn.style.display = 'none';
            }
        }
        
        // Update login button on page load
        if (typeof API !== 'undefined') {
            updateLoginButton();
        } else {
            // Wait for API to load
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
