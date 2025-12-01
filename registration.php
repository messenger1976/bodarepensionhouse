<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Create an account at BODARE Pension House">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Register Account - BODARE Pension House</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="img/logo.png">
    <link rel="icon" type="image/png" href="img/logo.png">
    
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Jost:wght@200;300;400&display=swap" rel="stylesheet">
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
                <a href="login.php" id="login-account-btn" class="cta-button-secondary" style="display: none;">Login</a>
                <a href="customer-dashboard.php" id="my-account-btn" class="cta-button-secondary" style="display: none;">My Account</a>
                <a href="rooms.php" class="cta-button">Book Now</a>
            </div>
        </nav>
    </header>

    <section class="page-header">
        <div class="page-header-content">
            <h1 id="page-title">Create an Account</h1>
            <p id="page-subtitle">Sign up to make your future bookings even faster.</p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            
            <!-- Registration Form Section -->
            <div id="registration-section" class="registration-container">
                <h2>Register</h2>
                <form action="#" id="registration-form" class="minimal-form">
                    <h3 style="margin-bottom: 1rem; color: #1a2238; font-size: 1.25rem;">Personal Information</h3>
                    <div class="form-grid-2">
                        <div class="form-group-contact">
                            <input type="text" id="reg-first-name" placeholder="First Name *" required>
                        </div>
                        <div class="form-group-contact">
                            <input type="text" id="reg-last-name" placeholder="Last Name *" required>
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group-contact">
                            <input type="email" id="reg-email" placeholder="Email Address *" required>
                        </div>
                        <div class="form-group-contact">
                            <input type="tel" id="reg-phone" placeholder="Phone Number *" required>
                        </div>
                    </div>
                    <div class="form-grid-3">
                        <div class="form-group-contact">
                            <input type="date" id="reg-date-of-birth" placeholder="Date of Birth">
                            <label for="reg-date-of-birth" style="display: block; margin-top: 0.5rem; font-size: 0.875rem; color: #666;">Date of Birth</label>
                        </div>
                        <div class="form-group-contact">
                            <select id="reg-gender" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group-contact">
                            <input type="text" id="reg-nationality" placeholder="Nationality (e.g., Filipino)">
                        </div>
                    </div>
                    
                    <h3 style="margin: 2rem 0 1rem 0; color: #1a2238; font-size: 1.25rem;">Address Information</h3>
                    <div class="form-group-contact">
                        <textarea id="reg-address" placeholder="Street Address *" rows="2" required></textarea>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group-contact">
                            <input type="text" id="reg-city" placeholder="City">
                        </div>
                        <div class="form-group-contact">
                            <input type="text" id="reg-province" placeholder="Province">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group-contact">
                            <input type="text" id="reg-postal-code" placeholder="Postal Code">
                        </div>
                        <div class="form-group-contact">
                            <input type="text" id="reg-country" placeholder="Country" value="Philippines">
                        </div>
                    </div>
                    
                    <h3 style="margin: 2rem 0 1rem 0; color: #1a2238; font-size: 1.25rem;">Identification (Optional)</h3>
                    <div class="form-grid-2">
                        <div class="form-group-contact">
                            <select id="reg-id-type" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;">
                                <option value="">Select ID Type</option>
                                <option value="passport">Passport</option>
                                <option value="driver_license">Driver's License</option>
                                <option value="national_id">National ID</option>
                                <option value="philhealth">PhilHealth ID</option>
                                <option value="sss">SSS ID</option>
                                <option value="tin">TIN ID</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group-contact">
                            <input type="text" id="reg-id-number" placeholder="ID Number">
                        </div>
                    </div>
                    
                    <h3 style="margin: 2rem 0 1rem 0; color: #1a2238; font-size: 1.25rem;">Account Security</h3>
                    <div class="form-group-contact">
                        <input type="password" id="reg-password" placeholder="Password *" required>
                    </div>
                    <div class="form-group-contact">
                        <input type="password" id="reg-confirm-password" placeholder="Confirm Password *" required>
                    </div>
                    
                    <button type="submit" class="cta-button">Create Account</button>

                    <p class="form-subtext">
                        Already have an account? <a href="login.php">Login here</a>
                    </p>
                </form>
            </div>

            <!-- Customer Information Section (shown after registration) -->
            <div id="customer-info-section" class="registration-container" style="display: none;">
                <h2>Your Information</h2>
                <div class="customer-info-display" style="background: #f8f9fa; padding: 2rem; border-radius: 8px; margin-bottom: 2rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                        <div>
                            <strong style="color: #666; display: block; margin-bottom: 0.5rem;">Full Name</strong>
                            <span id="customer-name">-</span>
                        </div>
                        <div>
                            <strong style="color: #666; display: block; margin-bottom: 0.5rem;">Email</strong>
                            <span id="customer-email">-</span>
                        </div>
                        <div>
                            <strong style="color: #666; display: block; margin-bottom: 0.5rem;">Phone</strong>
                            <span id="customer-phone">-</span>
                        </div>
                        <div>
                            <strong style="color: #666; display: block; margin-bottom: 0.5rem;">Address</strong>
                            <span id="customer-address">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Summary Section (shown if booking exists) -->
            <div id="booking-summary-section" class="registration-container" style="display: none;">
                <h2>Booking Summary</h2>
                <div class="booking-summary-display" style="background: #f8f9fa; padding: 2rem; border-radius: 8px; margin-bottom: 2rem;">
                    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                        <img id="summary-room-image" src="" alt="Room Image" style="width: 200px; height: 150px; object-fit: cover; border-radius: 8px;">
                        <div style="flex: 1; min-width: 250px;">
                            <h3 id="summary-room-name" style="margin-top: 0; margin-bottom: 1rem;">-</h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem; font-size: 0.875rem;">Check-In</strong>
                                    <span id="summary-checkin">-</span>
                                </div>
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem; font-size: 0.875rem;">Check-Out</strong>
                                    <span id="summary-checkout">-</span>
                                </div>
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem; font-size: 0.875rem;">Guests</strong>
                                    <span id="summary-guests">-</span>
                                </div>
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem; font-size: 0.875rem;">Rooms</strong>
                                    <span id="summary-rooms">-</span>
                                </div>
                            </div>
                            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid #ddd;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="font-size: 1.125rem;">Total Amount</strong>
                                    <span id="summary-total" style="font-size: 1.5rem; font-weight: 600; color: #b2945b;">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Section (shown if booking exists) -->
            <div id="payment-section" class="registration-container" style="display: none;">
                <h2>Payment Information</h2>
                <p style="color: #666; margin-bottom: 1.5rem;">You will pay at the hotel. We require this information to hold your room.</p>
                <form action="#" id="payment-form" class="minimal-form">
                    <div class="form-group-contact">
                        <label for="card-number">Card Number</label>
                        <input type="text" id="card-number" placeholder="Card Number (XXXX XXXX XXXX XXXX)" maxlength="19">
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group-contact">
                            <label for="card-expiry">Expiry Date</label>
                            <input type="text" id="card-expiry" placeholder="MM / YY" maxlength="5">
                        </div>
                        <div class="form-group-contact">
                            <label for="card-cvc">CVC</label>
                            <input type="text" id="card-cvc" placeholder="CVC (XXX)" maxlength="3">
                        </div>
                    </div>
                    <button type="submit" class="cta-button">Confirm Reservation</button>
                </form>
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
    <script src="booking-api.js"></script>
    <script src="script.js"></script>
    <script>
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
        
        // Format card number input
        document.addEventListener('DOMContentLoaded', () => {
            const cardNumber = document.getElementById('card-number');
            if (cardNumber) {
                cardNumber.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/\s/g, '');
                    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                    if (formattedValue.length <= 19) {
                        e.target.value = formattedValue;
                    }
                });
            }
            
            // Format expiry date
            const cardExpiry = document.getElementById('card-expiry');
            if (cardExpiry) {
                cardExpiry.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length >= 2) {
                        value = value.substring(0, 2) + ' / ' + value.substring(2, 4);
                    }
                    e.target.value = value;
                });
            }
            
            // Format CVC (numbers only)
            const cardCvc = document.getElementById('card-cvc');
            if (cardCvc) {
                cardCvc.addEventListener('input', (e) => {
                    e.target.value = e.target.value.replace(/\D/g, '');
                });
            }
        });
    </script>
</body>
</html>