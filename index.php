<?php
$pageSeo = [
    'title' => 'BODARE Pension House | Affordable Lodging in Tagbilaran City, Bohol',
    'description' => 'Stay at BODARE Pension House in Tagbilaran City, Bohol. Comfortable rooms near ICM Mall, free WiFi, free parking, and easy booking for travelers across Bohol.',
    'canonical_path' => '',
    'include_business_schema' => true,
    'og_image' => 'img/og-default.jpg',
    'og_image_alt' => 'BODARE Pension House — comfortable lodging in Tagbilaran City, Bohol',
];
include __DIR__ . '/includes/site-head.php';
?>
<body>

    <?php
    $headerConfig = ['about_href' => '#about'];
    include __DIR__ . '/includes/site-header.php';
?>


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
                            <h3>Prime Location</h3>
                            <p>Walking distance to ICM Mall and soon-to-open SM City Tagbilaran</p>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-wifi"></i>
                            <h3>Free WiFi</h3>
                            <p>Stay connected with complimentary high-speed internet access</p>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <h3>Backup Power</h3>
                            <p>Uninterrupted power supply even during blackouts</p>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-cup-hot-fill"></i>
                            <h3>Restaurants Nearby</h3>
                            <p>Easy access to various dining options and local eateries</p>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-currency-dollar"></i>
                            <h3>Affordable Rates</h3>
                            <p>Budget-friendly accommodation without sacrificing quality</p>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-shield-check"></i>
                            <h3>Safe & Secure</h3>
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
                    <a href="room-detail.php?room=dormitory">
                        <img src="img/dormitory.jpg" alt="Dormitory lodging at BODARE Pension House in Tagbilaran City">
                        <h3>Dormitory</h3>
                    </a>
                    <p class="room-details"><strong>Price:</strong> ₱299 per head<br><strong>Capacity:</strong> Min. 8 persons<br><strong>Services:</strong> Wifi, Television, Bathroom</p>
                </div>
                <div class="room-card">
                    <a href="room-detail.php?room=standard">
                        <img src="img/standard.jpg" alt="Standard guest room at BODARE Pension House">
                        <h3>Standard Room</h3>
                    </a>
                    <p class="room-details"><strong>Price:</strong> ₱999 per night<br><strong>Capacity:</strong> Good for 2 persons<br><strong>Services:</strong> Wifi, Television, Bathroom</p>
                </div> 
                <div class="room-card">
                    <a href="room-detail.php?room=deluxeb">
                        <img src="img/deluxeb.jpg" alt="Deluxe B room at BODARE Pension House">
                        <h3>Deluxe B Room</h3>
                    </a>
                    <p class="room-details"><strong>Price:</strong> ₱1,199 per night<br><strong>Capacity:</strong> Good for 2 persons<br><strong>Services:</strong> Wifi, Television, Bathroom</p>
                </div>
                <div class="room-card">
                    <a href="room-detail.php?room=deluxea">
                        <img src="img/deluxea.jpg" alt="Deluxe A room at BODARE Pension House">
                        <h3>Deluxe A Room</h3>
                    </a>
                    <p class="room-details"><strong>Price:</strong> ₱1,299 per night<br><strong>Capacity:</strong> Good for 3 persons<br><strong>Services:</strong> Wifi, Television, Bathroom</p>
                </div>
                <div class="room-card">
                    <a href="room-detail.php?room=ambassador">
                        <img src="img/ambassador.jpg" alt="Ambassador room at BODARE Pension House">
                        <h3>Ambassador Room</h3>
                    </a>
                    <p class="room-details"><strong>Price:</strong> ₱1,399 per night<br><strong>Capacity:</strong> Good for 3 persons<br><strong>Services:</strong> Wifi, Television, Bathroom</p>
                </div>                                                               
                <div class="room-card">
                    <a href="room-detail.php?room=executive">
                        <img src="img/executive.jpg" alt="Executive room at BODARE Pension House">
                        <h3>Executive Room</h3>
                    </a>
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
    
<?php
    include __DIR__ . '/includes/site-footer.php';
?>

    <script src="api-config.js"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
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
        
    </script>
</body>
</html>


