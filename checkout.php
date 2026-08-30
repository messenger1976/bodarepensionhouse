<?php
$pageSeo = [
    'title' => 'Booking Summary | BODARE Pension House',
    'description' => 'Review and confirm your booking at BODARE Pension House.',
    'canonical_path' => 'checkout.php',
    'robots' => 'noindex,nofollow',
    'extra_head' => '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">',
];
$enableAds = false;
include __DIR__ . '/includes/site-head.php';
?>
<body>
<script>document.documentElement.classList.add('checkout-is-guest');</script>

    <?php
    $headerConfig = ['show_book_button' => false];
    include __DIR__ . '/includes/site-header.php';
?>


    <section class="app-page-hero" id="checkout-hero-guest">
        <div class="page-header-content app-section" style="padding-top:0;padding-bottom:0;">
            <h1>Login to Your Account</h1>
            <p>Access your bookings and manage your reservations</p>
        </div>
    </section>

    <section class="app-page-hero" id="checkout-hero-authed" style="display: none;">
        <div class="page-header-content app-section" style="padding-top:0;padding-bottom:0;">
            <h1>Booking Summary</h1>
            <p>Please review your details and confirm your reservation.</p>
        </div>
    </section>

    <main class="content-section app-section" id="checkout-main">
        <div class="container">
            <div class="checkout-layout" id="checkout-layout">
                
                <div class="checkout-form">
                    <form action="#" id="checkout-login-form" class="minimal-form" novalidate>
                        <div id="checkout-guest-login" class="checkout-guest-login">
                            <div class="registration-container app-card checkout-login-card">
                                <h2 class="checkout-login-title">Login to Your Account</h2>
                                <p class="checkout-login-lead">Enter your credentials to access your dashboard</p>
                                <div id="login-fields">
                                    <div class="form-group-contact">
                                        <label for="login-email">Email Address</label>
                                        <input type="email" id="login-email" placeholder="Enter your email address" required autocomplete="email">
                                    </div>
                                    <div class="form-group-contact">
                                        <label for="login-password">Password</label>
                                        <input type="password" id="login-password" placeholder="Enter your password" required autocomplete="current-password">
                                    </div>

                                    <button type="button" id="login-button" class="cta-button">Login</button>

                                    <p class="form-subtext checkout-login-subtext">
                                        <a href="forgot-password.php">Forgot your password?</a>
                                    </p>

                                    <p class="form-subtext checkout-login-subtext">
                                        Don't have an account? <a href="registration.php?redirect=checkout.php">Create one here</a>
                                    </p>
                                </div>
                            </div>
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
                                    <option value="gcash">GCash / QR Ph</option>
                                </select>
                            </div>
                            
                            <!-- Card / PayMongo Hosted Checkout Message -->
                            <div id="card-payment-fields" style="display: none; margin-top: 1.5rem;">
                                <div style="
                                    background: #eef2ff;
                                    border: 1px solid #c7d2fe;
                                    color: #312e81;
                                    padding: 1rem;
                                    border-radius: 4px;
                                ">
                                    <strong>Pay by Credit/Debit Card (PayMongo):</strong>
                                    <span id="card-payment-message-text">
                                        After you confirm, your reservation will be created and you will be redirected to PayMongo&rsquo;s secure checkout to enter your card details.
                                        We never store your full card number. Your booking stays pending until payment is completed.
                                    </span>
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

                            <!-- GCash / PayMongo Message -->
                            <div id="gcash-payment-message" style="
                                display: none;
                                background: #e8f4fd;
                                border: 1px solid #b6d9f2;
                                color: #0c5460;
                                padding: 1rem;
                                border-radius: 4px;
                                margin-top: 1rem;
                            ">
                                <strong>Pay with GCash / QR Ph:</strong>
                                <span id="gcash-payment-message-text">
                                    After you confirm, your reservation and invoice will be created. You will see a QR Ph code on the next page to scan with GCash (or any QR Ph app). Your booking stays pending until payment is completed.
                                </span>
                            </div>
                            
                            <button type="submit" id="confirm-reservation-btn" class="cta-button" style="margin-top: 1.5rem;">Confirm Reservation</button>
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

<?php
    include __DIR__ . '/includes/site-footer.php';
?>
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="api-config.js?v=<?php echo @filemtime(__DIR__ . '/api-config.js') ?: time(); ?>"></script>
    <script src="booking-api.js?v=<?php echo @filemtime(__DIR__ . '/booking-api.js') ?: time(); ?>"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
    <script>
        function setCheckoutGuestMode(isGuest) {
            document.documentElement.classList.toggle('checkout-is-guest', isGuest);

            const guestHero = document.getElementById('checkout-hero-guest');
            const authedHero = document.getElementById('checkout-hero-authed');
            const guestLogin = document.getElementById('checkout-guest-login');
            const loginFields = document.getElementById('login-fields');
            const accountInfoSection = document.getElementById('account-info-section');
            const paymentSection = document.getElementById('payment-section');

            if (guestHero) guestHero.style.display = isGuest ? '' : 'none';
            if (authedHero) authedHero.style.display = isGuest ? 'none' : '';
            if (guestLogin) guestLogin.style.display = isGuest ? '' : 'none';
            if (loginFields) loginFields.style.display = isGuest ? '' : 'none';
            if (accountInfoSection) accountInfoSection.style.display = isGuest ? 'none' : 'block';
            if (paymentSection) paymentSection.style.display = isGuest ? 'none' : 'block';
        }

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
                            // Session missing/expired — logout the local account
                            if (typeof API.auth.clearLocalSession === 'function') {
                                API.auth.clearLocalSession();
                            } else {
                                localStorage.removeItem('user');
                            }
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
            const loginFields = document.getElementById('login-fields');
            
            if (isLoggedIn && currentUser) {
                setCheckoutGuestMode(false);
                
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
            } else {
                setCheckoutGuestMode(true);
                
                if (loginFields) {
                    loginFields.style.display = '';
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
            const gcashMessage = document.getElementById('gcash-payment-message');
            const confirmBtn = document.getElementById('confirm-reservation-btn');

            const syncPaymentMethodUI = (method) => {
                if (cardFields) cardFields.style.display = method === 'card' ? 'block' : 'none';
                if (payAtHotelMessage) payAtHotelMessage.style.display = method === 'pay_at_hotel' ? 'block' : 'none';
                if (gcashMessage) gcashMessage.style.display = method === 'gcash' ? 'block' : 'none';
                const gcashText = document.getElementById('gcash-payment-message-text');
                const cardText = document.getElementById('card-payment-message-text');
                const retryBooking = sessionStorage.getItem('paymongo_retry_booking');
                const retryMethod = sessionStorage.getItem('paymongo_retry_method') || 'gcash';
                if (confirmBtn) {
                    if (method === 'gcash') {
                        confirmBtn.textContent = (retryBooking && retryMethod === 'gcash')
                            ? ('Retry QR Payment (' + retryBooking + ')')
                            : 'Continue to QR Payment';
                    } else if (method === 'card') {
                        confirmBtn.textContent = (retryBooking && retryMethod === 'card')
                            ? ('Retry Card Payment (' + retryBooking + ')')
                            : 'Continue to Secure Card Payment';
                    } else {
                        confirmBtn.textContent = 'Confirm Reservation';
                    }
                }
                if (gcashText) {
                    gcashText.textContent = (retryBooking && retryMethod === 'gcash')
                        ? (' Booking ' + retryBooking + ' is saved. Click the button below to open the QR Ph code again and finish payment with GCash.')
                        : ' After you confirm, your reservation and invoice will be created. You will see a QR Ph code on the next page to scan with GCash (or any QR Ph app). Your booking stays pending until payment is completed.';
                }
                if (cardText) {
                    cardText.textContent = (retryBooking && retryMethod === 'card')
                        ? (' Booking ' + retryBooking + ' is saved. Click the button below to open PayMongo again and finish paying by card.')
                        : ' After you confirm, your reservation will be created and you will be redirected to PayMongo’s secure checkout to enter your card details. We never store your full card number. Your booking stays pending until payment is completed.';
                }
            };
            
            if (paymentMethod) {
                paymentMethod.addEventListener('change', (e) => {
                    syncPaymentMethodUI(e.target.value);
                    if (e.target.value !== 'gcash' && e.target.value !== 'card') {
                        sessionStorage.removeItem('paymongo_retry_booking');
                        sessionStorage.removeItem('paymongo_retry_method');
                    }
                });
                syncPaymentMethodUI(paymentMethod.value);
            }

            // Show notice if guest cancelled / returned without paying
            const params = new URLSearchParams(window.location.search);
            if (params.get('payment') === 'cancelled') {
                const bookingRef = params.get('booking') || '';
                const cancelledMethod = (params.get('method') || 'gcash').toLowerCase();
                const msg = bookingRef
                    ? (cancelledMethod === 'card'
                        ? `Card payment was not completed. Your booking ${bookingRef} is still reserved as pending. Select Credit/Debit Card and click Continue to retry, or choose Pay at the Hotel.`
                        : `QR payment was not completed. Your booking ${bookingRef} is still reserved as pending. Select GCash / QR Ph and click Continue to retry, or choose Pay at the Hotel.`)
                    : 'Payment was not completed. You can try again or choose another payment method.';
                if (typeof showMessage === 'function') {
                    showMessage(msg, 'error');
                } else {
                    alert(msg);
                }
                if (bookingRef) {
                    sessionStorage.setItem('paymongo_retry_booking', bookingRef);
                    sessionStorage.setItem('paymongo_retry_method', cancelledMethod === 'card' ? 'card' : 'gcash');
                    if (paymentMethod) {
                        paymentMethod.value = cancelledMethod === 'card' ? 'card' : 'gcash';
                        syncPaymentMethodUI(paymentMethod.value);
                    }
                }
            }

            // Retry PayMongo for an existing pending booking (QRPH or card)
            const checkoutForm = document.getElementById('checkout-login-form');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', async (e) => {
                    const retryBooking = sessionStorage.getItem('paymongo_retry_booking');
                    const retryMethod = sessionStorage.getItem('paymongo_retry_method') || 'gcash';
                    const method = paymentMethod ? paymentMethod.value : '';
                    if (!retryBooking || typeof API === 'undefined' || !API.payment) {
                        return;
                    }

                    if (method === 'gcash' && retryMethod === 'gcash') {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        if (confirmBtn) {
                            confirmBtn.disabled = true;
                            confirmBtn.textContent = 'Opening QR payment…';
                        }
                        try {
                            const result = await API.payment.createQrph({
                                booking_number: retryBooking,
                                regenerate: true
                            });
                            if (result.already_paid) {
                                sessionStorage.removeItem('paymongo_retry_booking');
                                sessionStorage.removeItem('paymongo_retry_method');
                                window.location.href = `booking-confirmation.php?booking=${encodeURIComponent(retryBooking)}&payment=success`;
                                return;
                            }
                            if (result.success && (result.qr_image_url || (result.payment && result.payment.qr_image_url))) {
                                sessionStorage.removeItem('paymongo_retry_booking');
                                sessionStorage.removeItem('paymongo_retry_method');
                                const payment = result.payment || result;
                                try {
                                    sessionStorage.setItem('paymongo_qrph_payment', JSON.stringify(payment));
                                } catch (err) {}
                                if (payment.payment_intent_id) {
                                    sessionStorage.setItem('paymongo_payment_intent_id', payment.payment_intent_id);
                                }
                                const invQs = payment.invoice_id ? `&invoice=${encodeURIComponent(payment.invoice_id)}` : '';
                                window.location.href = `booking-confirmation.php?booking=${encodeURIComponent(retryBooking)}&payment=qrph${invQs}`;
                                return;
                            }
                            throw new Error(result.message || 'Unable to restart QR Ph payment.');
                        } catch (err) {
                            if (typeof showMessage === 'function') {
                                showMessage(err.message || 'Unable to restart QR Ph payment.', 'error');
                            } else {
                                alert(err.message || 'Unable to restart QR Ph payment.');
                            }
                            if (confirmBtn) {
                                confirmBtn.disabled = false;
                                syncPaymentMethodUI('gcash');
                            }
                        }
                        return;
                    }

                    if (method === 'card' && retryMethod === 'card') {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        if (confirmBtn) {
                            confirmBtn.disabled = true;
                            confirmBtn.textContent = 'Opening card checkout…';
                        }
                        try {
                            const result = await API.payment.createCardCheckout({
                                booking_number: retryBooking,
                                regenerate: true
                            });
                            if (result.already_paid) {
                                sessionStorage.removeItem('paymongo_retry_booking');
                                sessionStorage.removeItem('paymongo_retry_method');
                                window.location.href = `booking-confirmation.php?booking=${encodeURIComponent(retryBooking)}&payment=success`;
                                return;
                            }
                            const checkoutUrl = result.checkout_url || (result.payment && result.payment.checkout_url);
                            if (result.success && checkoutUrl) {
                                sessionStorage.removeItem('paymongo_retry_booking');
                                sessionStorage.removeItem('paymongo_retry_method');
                                window.location.href = checkoutUrl;
                                return;
                            }
                            throw new Error(result.message || 'Unable to restart card checkout.');
                        } catch (err) {
                            if (typeof showMessage === 'function') {
                                showMessage(err.message || 'Unable to restart card checkout.', 'error');
                            } else {
                                alert(err.message || 'Unable to restart card checkout.');
                            }
                            if (confirmBtn) {
                                confirmBtn.disabled = false;
                                syncPaymentMethodUI('card');
                            }
                        }
                    }
                }, true);
            }
        });
    </script>
</body>
</html>


