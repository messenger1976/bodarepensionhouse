<?php
$pageSeo = [
    'title' => 'Create Account | BODARE Pension House',
    'description' => 'Create a guest account to book rooms and manage reservations at BODARE Pension House.',
    'canonical_path' => 'registration.php',
    'robots' => 'noindex,nofollow',
];
$enableAds = false;
include __DIR__ . '/includes/site-head.php';
?>
<body>

    <?php
    $headerConfig = ['show_cart' => false];
    include __DIR__ . '/includes/site-header.php';
?>


    <section class="app-page-hero">
        <div class="page-header-content app-section" style="padding-top:0;padding-bottom:0;">
            <h1 id="page-title">Create an Account</h1>
            <p id="page-subtitle">Sign up to make your future bookings even faster.</p>
        </div>
    </section>

    <main class="content-section app-section">
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
                        <img id="summary-room-image" src="" alt="Selected room for booking at BODARE Pension House" style="width: 200px; height: 150px; object-fit: cover; border-radius: 8px;">
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

<?php
    include __DIR__ . '/includes/site-footer.php';
?>
    
    <script src="api-config.js?v=<?php echo @filemtime(__DIR__ . '/api-config.js') ?: time(); ?>"></script>
    <script src="booking-api.js?v=<?php echo @filemtime(__DIR__ . '/booking-api.js') ?: time(); ?>"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
</body>
</html>



