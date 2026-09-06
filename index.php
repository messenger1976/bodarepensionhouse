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
require_once __DIR__ . '/includes/site-config.php';
$homeRooms = [];
foreach (bodare_room_codes() as $roomKey) {
    $meta = bodare_live_room($roomKey);
    if ($meta) {
        $homeRooms[] = $meta;
    }
}
$h = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<body>

    <?php
    $headerConfig = ['about_href' => '#about'];
    include __DIR__ . '/includes/site-header.php';
?>

    <section class="home-mobile-hero app-section">
        <div class="home-search-card">
            <div>
                <span class="home-search-badge">Heart of Tagbilaran • Near ICM &amp; Terminal</span>
                <h2>Clean, Safe &amp; Budget Lodging</h2>
                <p class="lead">Your home in Tagbilaran City with aircon rooms &amp; parking. Minutes from ICM, City Hall, and Dao Bus Terminal.</p>
            </div>
            <form class="booking-form home-search-panel">
                <div class="home-date-row">
                    <div class="form-group">
                        <label for="checkin">Check-In</label>
                        <input type="date" id="checkin" name="checkin" required>
                    </div>
                    <div class="form-group">
                        <label for="checkout">Check-Out</label>
                        <input type="date" id="checkout" name="checkout" required>
                    </div>
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
                <button type="submit" class="cta-button form-button app-cta-gold">Check Availability</button>
            </form>
        </div>
    </section>

    <section class="home-mobile-hero app-section">
        <div class="chip-row no-scrollbar">
            <a href="rooms.php" class="chip is-active">All Rooms</a>
            <?php foreach ($homeRooms as $roomMeta): ?>
                <a href="room-detail.php?room=<?php echo $h($roomMeta['code']); ?>" class="chip"><?php echo $h($roomMeta['title']); ?></a>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="about" class="app-section">
        <h2 class="text-xl font-black text-emerald-950 mb-3">Welcome to BODARE Pension House</h2>
        <p class="lead-text text-sm text-gray-600 mb-3">
            Experience comfort, convenience, and affordability at BODARE Pension House, your ideal home away from home in the heart of Tagbilaran City.
        </p>
        <p class="text-sm text-gray-600 mb-3">
            Strategically located within walking distance of ICM Mall and the soon-to-open SM City Tagbilaran, BODARE Pension House offers you the perfect base for exploring Bohol's capital city. Whether you're here for business, leisure, or a quick stopover, our prime location puts you right where you need to be.
        </p>
        <p class="text-sm text-gray-600 mb-4">
            At BODARE Pension House, we believe that quality accommodation shouldn't break the bank. We offer affordable rates without compromising on comfort and essential amenities. Our well-appointed rooms provide a peaceful retreat after a day of exploring the beautiful island of Bohol.
        </p>
        <div class="home-features">
            <div class="feature-tile">
                <i class="bi bi-geo-alt-fill text-yellow-500"></i>
                <h3>Prime Location</h3>
                <p>Walking distance to ICM Mall and soon-to-open SM City Tagbilaran</p>
            </div>
            <div class="feature-tile">
                <i class="bi bi-wifi text-emerald-700"></i>
                <h3>Free WiFi</h3>
                <p>Stay connected with complimentary high-speed internet access</p>
            </div>
            <div class="feature-tile">
                <i class="bi bi-lightning-charge-fill text-emerald-700"></i>
                <h3>Backup Power</h3>
                <p>Uninterrupted power supply even during blackouts</p>
            </div>
            <div class="feature-tile">
                <i class="bi bi-cup-hot-fill text-emerald-700"></i>
                <h3>Restaurants Nearby</h3>
                <p>Easy access to various dining options and local eateries</p>
            </div>
            <div class="feature-tile">
                <i class="bi bi-currency-dollar text-emerald-700"></i>
                <h3>Affordable Rates</h3>
                <p>Budget-friendly accommodation without sacrificing quality</p>
            </div>
            <div class="feature-tile">
                <i class="bi bi-shield-check text-emerald-700"></i>
                <h3>Safe &amp; Secure</h3>
                <p>24/7 security and a welcoming, family-friendly environment</p>
            </div>
        </div>
        <p class="closing-text text-sm text-gray-600 mt-4">
            Managed by Bodare and Community Multi-Purpose Cooperative, we take pride in providing exceptional service and creating memorable experiences for all our guests. Book your stay with us today and discover why BODARE Pension House is the preferred choice for travelers in Tagbilaran City.
        </p>
    </section>

    <?php adsense_render_unit('in_content', 'adsense-in-content container'); ?>

    <section id="rooms" class="app-section">
        <h2 class="text-xs font-bold text-emerald-950 uppercase tracking-wider mb-3">Available Accommodations</h2>
        <div class="space-y-4 md:grid md:grid-cols-3 md:gap-6 md:space-y-0">
            <?php foreach ($homeRooms as $roomMeta): ?>
                <article class="room-card-app mb-4 md:mb-0">
                    <a href="room-detail.php?room=<?php echo $h($roomMeta['code']); ?>" class="media">
                        <img src="<?php echo $h($roomMeta['image']); ?>" alt="<?php echo $h($roomMeta['title'] . ' at BODARE Pension House'); ?>">
                        <span class="badge-cap"><?php echo $h($roomMeta['capacity']); ?></span>
                        <span class="badge-price">₱<?php echo number_format($roomMeta['price']); ?> <?php echo $h($roomMeta['price_unit']); ?></span>
                    </a>
                    <div class="body">
                        <h3><?php echo $h($roomMeta['title']); ?></h3>
                        <p class="desc"><?php echo $h($roomMeta['description']); ?></p>
                        <p class="text-[11px] text-gray-600 mb-3">Wifi · Television · Bathroom</p>
                        <a href="room-detail.php?room=<?php echo $h($roomMeta['code']); ?>" class="app-cta">Reserve Room</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="video" class="app-section">
        <h2 class="text-xl font-black text-emerald-950 mb-2">A Cinematic Journey</h2>
        <p class="text-sm text-gray-600 mb-3">
            Immerse yourself in the BODARE Pension House experience. Press play to discover the moments, the details, and the unforgettable atmosphere that awaits you.
        </p>
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

    <script src="api-config.js?v=<?php echo @filemtime(__DIR__ . '/api-config.js') ?: time(); ?>"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkinInput = document.getElementById('checkin');
            const checkoutInput = document.getElementById('checkout');
            const bookingForm = document.querySelector('.booking-form');

            if (checkinInput && checkoutInput) {
                const today = new Date();
                const todayString = today.toISOString().split('T')[0];
                checkinInput.setAttribute('min', todayString);
                checkoutInput.setAttribute('min', todayString);

                checkinInput.addEventListener('change', () => {
                    const checkinDate = new Date(checkinInput.value);
                    const checkoutDate = new Date(checkoutInput.value);
                    const minCheckoutDate = new Date(checkinDate);
                    minCheckoutDate.setDate(minCheckoutDate.getDate() + 1);
                    const minCheckoutString = minCheckoutDate.toISOString().split('T')[0];
                    checkoutInput.setAttribute('min', minCheckoutString);
                    if (checkoutInput.value && checkoutDate <= checkinDate) {
                        checkoutInput.value = '';
                    }
                });

                checkoutInput.addEventListener('change', () => {
                    const checkinDate = new Date(checkinInput.value);
                    const checkoutDate = new Date(checkoutInput.value);
                    if (checkinInput.value && checkoutDate <= checkinDate) {
                        alert('Check-out date must be after check-in date.');
                        checkoutInput.value = '';
                        checkoutInput.focus();
                    }
                });

                if (bookingForm) {
                    bookingForm.addEventListener('submit', async (e) => {
                        e.preventDefault();

                        const checkinDate = new Date(checkinInput.value);
                        const checkoutDate = new Date(checkoutInput.value);
                        const todayBound = new Date();
                        todayBound.setHours(0, 0, 0, 0);

                        if (checkinDate < todayBound) {
                            alert('Check-in date cannot be in the past.');
                            checkinInput.focus();
                            return false;
                        }

                        if (checkoutDate < todayBound) {
                            alert('Check-out date cannot be in the past.');
                            checkoutInput.focus();
                            return false;
                        }

                        if (checkoutDate <= checkinDate) {
                            alert('Check-out date must be after check-in date.');
                            checkoutInput.focus();
                            return false;
                        }

                        const checkIn = checkinInput.value;
                        const checkOut = checkoutInput.value;
                        const guests = document.getElementById('guests').value;

                        const submitButton = bookingForm.querySelector('button[type="submit"]');
                        const originalButtonText = submitButton.textContent;
                        submitButton.disabled = true;
                        submitButton.textContent = 'Checking Availability...';

                        try {
                            if (typeof API === 'undefined') {
                                throw new Error('API configuration not loaded. Please refresh the page.');
                            }

                            const response = await API.booking.checkAvailability(checkIn, checkOut, guests);

                            if (response.success) {
                                localStorage.setItem('availabilityData', JSON.stringify({
                                    checkIn: checkIn,
                                    checkOut: checkOut,
                                    guests: guests,
                                    rooms: response.rooms || [],
                                    timestamp: new Date().toISOString()
                                }));

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
