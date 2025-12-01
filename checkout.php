<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Review and confirm your booking at BODARE Pension House">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Booking Summary - BODARE Pension House</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="img/logo.png">
    <link rel="icon" type="image/png" href="img/logo.png">
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                <a href="cart.php" id="cart-link" class="cart-link" style="display: flex;" title="Shopping Cart">
                    <i class="bi bi-cart" style="font-size: 1.5rem;"></i>
                    <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                </a>
                <a href="login.php" id="login-account-btn" class="cta-button-secondary" style="display: none;">Login</a>
                <a href="customer-dashboard.php" id="my-account-btn" class="cta-button-secondary" style="display: none;">My Account</a>
            </div>
        </nav>
    </header>

    <section class="page-header">
        <div class="page-header-content">
            <h1>Booking Summary</h1>
            <p>Please review your details and confirm your reservation.</p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            <div class="checkout-layout">
                
                <div class="checkout-form">
                    
                    <div class="checkout-auth-header">
                        <h2 id="auth-header-title">Login to Continue</h2>
                        <div id="auth-buttons" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <a href="login.php" id="login-link" class="cta-button-secondary">Login</a>
                            <a href="registration.php" id="register-link" class="cta-button-secondary">Register</a>
                        </div>
                        <div id="logged-in-info" style="display: none;">
                            <p style="color: #666; margin-bottom: 0.5rem;">Logged in as: <strong id="logged-in-name"></strong></p>
                            <button type="button" id="logout-button" class="cta-button-secondary">Logout</button>
                        </div>
                    </div>

                    <form action="#" id="checkout-login-form" class="minimal-form" novalidate>
                        
                        <div id="login-fields">
                            <div class="form-group-contact">
                                <label for="login-email">Email Address</label>
                                <input type="email" id="login-email" placeholder="Email Address" required>
                            </div>
                            <div class="form-group-contact">
                                <label for="login-password">Password</label>
                                <input type="password" id="login-password" placeholder="Password" required>
                            </div>
                            
                            <button type="button" id="login-button" class="cta-button">Login</button>
                        </div>
                        <!-- Account Information Section (shown when logged in) -->
                        <div id="account-info-section" style="display: none;">
                            <h2 style="margin-top: 2rem;">Account Information</h2>
                            <p style="color: #666; margin-bottom: 1rem;">Please review and update your information if needed.</p>
                            <div class="account-info-form">
                                <h3 style="margin-bottom: 1rem; color: #1a2238; font-size: 1.25rem;">Personal Information</h3>
                                <div class="form-grid-2">
                                    <div class="form-group-contact">
                                        <label for="account-first-name">First Name</label>
                                        <input type="text" id="account-first-name" placeholder="First Name *" required>
                                    </div>
                                    <div class="form-group-contact">
                                        <label for="account-last-name">Last Name</label>
                                        <input type="text" id="account-last-name" placeholder="Last Name *" required>
                                    </div>
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group-contact">
                                        <label for="account-email">Email Address</label>
                                        <input type="email" id="account-email" placeholder="Email Address *" required>
                                    </div>
                                    <div class="form-group-contact">
                                        <label for="account-phone">Contact Number</label>
                                        <input type="tel" id="account-phone" placeholder="Phone Number *" required>
                                    </div>
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group-contact">
                                        <label for="account-gender">Gender</label>
                                        <select id="account-gender">
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group-contact">
                                        <label for="account-nationality">Nationality</label>
                                        <input type="text" id="account-nationality" placeholder="Nationality (e.g., Filipino)">
                                    </div>
                                </div>
                                
                                <h3 style="margin: 2rem 0 1rem 0; color: #1a2238; font-size: 1.25rem;">Address Information</h3>
                                <div class="form-group-contact">
                                    <label for="account-address">Street Address</label>
                                    <textarea id="account-address" placeholder="Street Address *" rows="2" required></textarea>
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group-contact">
                                        <label for="account-city">City *</label>
                                        <input type="text" id="account-city" placeholder="City *" required>
                                    </div>
                                    <div class="form-group-contact">
                                        <label for="account-province">Province *</label>
                                        <input type="text" id="account-province" placeholder="Province *" required>
                                    </div>
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group-contact">
                                        <label for="account-postal-code">Zip/Postal Code *</label>
                                        <input type="text" id="account-postal-code" placeholder="Zip/Postal Code *" required>
                                    </div>
                                    <div class="form-group-contact">
                                        <label for="account-country">Country *</label>
                                        <input type="text" id="account-country" placeholder="Country *" value="Philippines" required>
                                    </div>
                                </div>
                                
                                <h3 style="margin: 2rem 0 1rem 0; color: #1a2238; font-size: 1.25rem;">Identification (Optional)</h3>
                                <div class="form-grid-2">
                                    <div class="form-group-contact">
                                        <label for="account-id-type">ID Type</label>
                                        <select id="account-id-type">
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
                                        <label for="account-id-number">ID Number</label>
                                        <input type="text" id="account-id-number" placeholder="ID Number">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="payment-section" style="display: none;">
                            <h2 style="margin-top: 2rem;">Payment Information</h2>
                            
                            <div class="form-group-contact">
                                <label for="payment-method">Payment Method</label>
                                <select id="payment-method" class="form-control" style="
                                    width: 100%;
                                    padding: 0.75rem;
                                    border: 1px solid #ddd;
                                    border-radius: 4px;
                                    font-family: inherit;
                                    font-size: 1rem;
                                ">
                                    <option value="pay_at_hotel">Pay at the Hotel</option>
                                    <option value="card">Credit/Debit Card</option>
                                    <option value="gcash">GCash</option>
                                </select>
                            </div>
                            
                            <!-- Card Payment Fields (shown when card is selected) -->
                            <div id="card-payment-fields" style="display: none; margin-top: 1.5rem;">
                                <p style="color: #666; margin-bottom: 1rem;">Please enter your card details below.</p>
                                <div class="form-group-contact">
                                    <label for="card-number">Card Number</label>
                                    <input type="text" id="card-number" placeholder="Card Number (XXXX XXXX XXXX XXXX)" maxlength="19">
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group-contact">
                                        <label for="card-expiry">Expiry Date</label>
                                        <input type="text" id="card-expiry" placeholder="Expiry Date (MM / YY)" maxlength="5">
                                    </div>
                                    <div class="form-group-contact">
                                        <label for="card-cvc">CVC</label>
                                        <input type="text" id="card-cvc" placeholder="CVC (XXX)" maxlength="3">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pay at Hotel Message -->
                            <div id="pay-at-hotel-message" style="
                                background: #d4edda;
                                border: 1px solid #c3e6cb;
                                color: #155724;
                                padding: 1rem;
                                border-radius: 4px;
                                margin-top: 1rem;
                            ">
                                <strong>Pay at the Hotel:</strong> You will pay upon arrival at the hotel. We require this information to hold your room.
                            </div>
                            
                            <button type="submit" class="cta-button" style="margin-top: 1.5rem;">Confirm Reservation</button>
                        </div>
                        </form>
                    
                </div>
                
                <aside class="checkout-summary">
                    <div id="checkout-summary-content">
                        <!-- Summary will be populated from cart -->
                    </div>
                </aside>

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
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="api-config.js"></script>
    <script src="booking-api.js"></script>
    <script src="script.js"></script>
    <script>
        // Check if user is logged in and update UI
        document.addEventListener('DOMContentLoaded', async () => {
            // Check if API is loaded
            if (typeof API === 'undefined') {
                console.error('API configuration not loaded!');
                return;
            }
            
            const userStr = localStorage.getItem('user');
            let isLoggedIn = false;
            let currentUser = null;
            
            if (userStr) {
                try {
                    currentUser = JSON.parse(userStr);
                    // Verify session is still valid
                    try {
                        const response = await API.auth.check();
                        if (response.success && response.logged_in) {
                            isLoggedIn = true;
                        } else {
                            // Session expired, clear localStorage
                            localStorage.removeItem('user');
                            currentUser = null;
                        }
                    } catch (error) {
                        // API check failed, but user data exists - assume logged in for now
                        isLoggedIn = true;
                    }
                } catch (parseError) {
                    console.error('Error parsing user data:', parseError);
                    localStorage.removeItem('user');
                }
            }
            
            // Update UI based on login status
            const authHeaderTitle = document.getElementById('auth-header-title');
            const authButtons = document.getElementById('auth-buttons');
            const loginLink = document.getElementById('login-link');
            const registerLink = document.getElementById('register-link');
            const loggedInInfo = document.getElementById('logged-in-info');
            const loggedInName = document.getElementById('logged-in-name');
            const logoutButton = document.getElementById('logout-button');
            const loginFields = document.getElementById('login-fields');
            
            if (isLoggedIn && currentUser) {
                // User is logged in - update UI
                if (authHeaderTitle) {
                    authHeaderTitle.textContent = 'Continue with Your Booking';
                }
                
                // Hide login/register buttons
                if (authButtons) {
                    authButtons.style.display = 'none';
                }
                
                // Show logged in info
                if (loggedInInfo) {
                    loggedInInfo.style.display = 'block';
                }
                
                // Set user name
                if (loggedInName && currentUser.name) {
                    loggedInName.textContent = currentUser.name;
                }
                
                // Hide login form fields
                if (loginFields) {
                    loginFields.style.display = 'none';
                }
                
                // Load and display user profile information
                loadUserAccountInfo();
                
                // Show account info and payment sections
                const accountInfoSection = document.getElementById('account-info-section');
                const paymentSection = document.getElementById('payment-section');
                if (accountInfoSection) {
                    accountInfoSection.style.display = 'block';
                    // Add required attributes when section is shown
                    const firstName = document.getElementById('account-first-name');
                    const lastName = document.getElementById('account-last-name');
                    const email = document.getElementById('account-email');
                    const phone = document.getElementById('account-phone');
                    const address = document.getElementById('account-address');
                    const city = document.getElementById('account-city');
                    const province = document.getElementById('account-province');
                    const postalCode = document.getElementById('account-postal-code');
                    const country = document.getElementById('account-country');
                    if (firstName) firstName.setAttribute('required', 'required');
                    if (lastName) lastName.setAttribute('required', 'required');
                    if (email) email.setAttribute('required', 'required');
                    if (phone) phone.setAttribute('required', 'required');
                    if (address) address.setAttribute('required', 'required');
                    if (city) city.setAttribute('required', 'required');
                    if (province) province.setAttribute('required', 'required');
                    if (postalCode) postalCode.setAttribute('required', 'required');
                    if (country) country.setAttribute('required', 'required');
                }
                if (paymentSection) {
                    paymentSection.style.display = 'block';
                    // Add required attribute to payment method when section is shown
                    const paymentMethod = document.getElementById('payment-method');
                    if (paymentMethod) paymentMethod.setAttribute('required', 'required');
                }
                
                // Setup logout button
                if (logoutButton) {
                    logoutButton.addEventListener('click', async () => {
                        try {
                            await API.auth.logout();
                        } catch (error) {
                            console.error('Logout error:', error);
                        } finally {
                            localStorage.removeItem('user');
                            // Reload page to show login form
                            window.location.reload();
                        }
                    });
                }
            } else {
                // User is not logged in - show login form
                if (authHeaderTitle) {
                    authHeaderTitle.textContent = 'Login to Continue';
                }
                
                // Show login/register buttons
                if (authButtons) {
                    authButtons.style.display = 'flex';
                }
                
                // Hide logged in info
                if (loggedInInfo) {
                    loggedInInfo.style.display = 'none';
                }
                
                // Show login form fields
                if (loginFields) {
                    loginFields.style.display = 'block';
                }
                
                // Remove required attributes from hidden account info fields
                const accountInfoSection = document.getElementById('account-info-section');
                const paymentSection = document.getElementById('payment-section');
                if (accountInfoSection && accountInfoSection.style.display === 'none') {
                    const requiredFields = [
                        'account-first-name', 'account-last-name', 'account-email', 
                        'account-phone', 'account-address', 'account-city', 
                        'account-province', 'account-postal-code', 'account-country'
                    ];
                    requiredFields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) field.removeAttribute('required');
                    });
                }
                if (paymentSection && paymentSection.style.display === 'none') {
                    const paymentMethod = document.getElementById('payment-method');
                    if (paymentMethod) paymentMethod.removeAttribute('required');
                }
            }
        });
        
        // Load user account information
        async function loadUserAccountInfo() {
            try {
                // Try to get full profile from API
                const response = await API.user.getProfile();
                if (response.success && response.user) {
                    const user = response.user;
                    displayAccountInfo(user);
                } else {
                    // Fallback to localStorage data
                    const userStr = localStorage.getItem('user');
                    if (userStr) {
                        const user = JSON.parse(userStr);
                        displayAccountInfo(user);
                    }
                }
            } catch (error) {
                console.error('Error loading user profile:', error);
                // Fallback to localStorage data
                const userStr = localStorage.getItem('user');
                if (userStr) {
                    const user = JSON.parse(userStr);
                    displayAccountInfo(user);
                }
            }
        }
        
        // Display account information in editable fields
        function displayAccountInfo(user) {
            const firstNameEl = document.getElementById('account-first-name');
            const lastNameEl = document.getElementById('account-last-name');
            const emailEl = document.getElementById('account-email');
            const phoneEl = document.getElementById('account-phone');
            const genderEl = document.getElementById('account-gender');
            const nationalityEl = document.getElementById('account-nationality');
            const addressEl = document.getElementById('account-address');
            const cityEl = document.getElementById('account-city');
            const provinceEl = document.getElementById('account-province');
            const postalCodeEl = document.getElementById('account-postal-code');
            const countryEl = document.getElementById('account-country');
            const idTypeEl = document.getElementById('account-id-type');
            const idNumberEl = document.getElementById('account-id-number');
            
            // Split name if we have full name but not first/last
            if (user.first_name || user.last_name) {
                if (firstNameEl) firstNameEl.value = user.first_name || '';
                if (lastNameEl) lastNameEl.value = user.last_name || '';
            } else if (user.name) {
                // Try to split the name
                const nameParts = user.name.trim().split(' ');
                if (nameParts.length >= 2) {
                    if (firstNameEl) firstNameEl.value = nameParts[0];
                    if (lastNameEl) lastNameEl.value = nameParts.slice(1).join(' ');
                } else {
                    if (firstNameEl) firstNameEl.value = user.name;
                    if (lastNameEl) lastNameEl.value = '';
                }
            }
            
            if (emailEl) {
                emailEl.value = user.email || '';
            }
            
            if (phoneEl) {
                phoneEl.value = user.phone || '';
            }
            
            if (genderEl) {
                genderEl.value = user.gender || '';
            }
            
            if (nationalityEl) {
                nationalityEl.value = user.nationality || '';
            }
            
            if (addressEl) {
                addressEl.value = user.address || '';
            }
            
            if (cityEl) {
                cityEl.value = user.city || '';
            }
            
            if (provinceEl) {
                provinceEl.value = user.province || '';
            }
            
            if (postalCodeEl) {
                postalCodeEl.value = user.postal_code || '';
            }
            
            if (countryEl) {
                countryEl.value = user.country || 'Philippines';
            }
            
            if (idTypeEl) {
                idTypeEl.value = user.id_type || '';
            }
            
            if (idNumberEl) {
                idNumberEl.value = user.id_number || '';
            }
        }
        
        // Handle payment method selection
        document.addEventListener('DOMContentLoaded', () => {
            const paymentMethod = document.getElementById('payment-method');
            const cardFields = document.getElementById('card-payment-fields');
            const payAtHotelMessage = document.getElementById('pay-at-hotel-message');
            
            if (paymentMethod) {
                paymentMethod.addEventListener('change', (e) => {
                    const method = e.target.value;
                    
                    if (method === 'card') {
                        if (cardFields) cardFields.style.display = 'block';
                        if (payAtHotelMessage) payAtHotelMessage.style.display = 'none';
                    } else {
                        if (cardFields) cardFields.style.display = 'none';
                        if (payAtHotelMessage) payAtHotelMessage.style.display = 'block';
                    }
                });
                
                // Set initial state
                if (paymentMethod.value === 'pay_at_hotel') {
                    if (cardFields) cardFields.style.display = 'none';
                    if (payAtHotelMessage) payAtHotelMessage.style.display = 'block';
                }
            }
            
            // Format card number input
            const cardNumber = document.getElementById('card-number');
            if (cardNumber) {
                cardNumber.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/\s/g, '');
                    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                    if (formattedValue.length > 19) formattedValue = formattedValue.slice(0, 19);
                    e.target.value = formattedValue;
                });
            }
            
            // Format expiry date input
            const cardExpiry = document.getElementById('card-expiry');
            if (cardExpiry) {
                cardExpiry.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length >= 2) {
                        value = value.slice(0, 2) + ' / ' + value.slice(2, 4);
                    }
                    e.target.value = value;
                });
            }
            
            // Format CVC input (numbers only)
            const cardCvc = document.getElementById('card-cvc');
            if (cardCvc) {
                cardCvc.addEventListener('input', (e) => {
                    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3);
                });
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