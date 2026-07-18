<?php
require_once __DIR__ . '/includes/site-config.php';

$roomCatalog = bodare_room_catalog();
$roomKey = isset($_GET['room']) ? strtolower(preg_replace('/[^a-z0-9_-]/i', '', (string) $_GET['room'])) : '';
$room = ($roomKey !== '' && isset($roomCatalog[$roomKey])) ? $roomCatalog[$roomKey] : null;

if ($room) {
    $pageSeo = [
        'title' => $room['title'] . ' | BODARE Pension House Tagbilaran',
        'description' => $room['description'],
        'canonical_path' => 'room-detail.php?room=' . rawurlencode($roomKey),
        'og_image' => $room['image'],
        'og_image_alt' => $room['title'] . ' at BODARE Pension House',
        'json_ld' => [
            '@context' => 'https://schema.org',
            '@type' => 'HotelRoom',
            'name' => $room['title'],
            'description' => $room['description'],
            'image' => bodare_absolute_url($room['image']),
            'url' => bodare_absolute_url('room-detail.php?room=' . rawurlencode($roomKey)),
            'occupancy' => [
                '@type' => 'QuantitativeValue',
                'description' => $room['capacity'],
            ],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'PHP',
                'price' => $room['price'],
                'description' => '₱' . number_format($room['price']) . ' ' . $room['price_unit'],
                'availability' => 'https://schema.org/InStock',
                'url' => bodare_absolute_url('room-detail.php?room=' . rawurlencode($roomKey)),
            ],
            'containedInPlace' => [
                '@id' => bodare_absolute_url() . '#lodging',
            ],
        ],
        'extra_head' => '
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/confirmDate/confirmDate.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/confirmDate/confirmDate.js"></script>',
    ];
} else {
    $pageSeo = [
        'title' => 'Room Details | BODARE Pension House',
        'description' => 'View room details and book your stay at BODARE Pension House in Tagbilaran City, Bohol.',
        'canonical_path' => 'room-detail.php',
        'robots' => 'noindex,follow',
        'extra_head' => '
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/confirmDate/confirmDate.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/confirmDate/confirmDate.js"></script>',
    ];
}

include __DIR__ . '/includes/site-head.php';

$heroImage = $room ? $room['image'] : 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=2070&auto=format&fit=crop';
$initialTitle = $room ? $room['title'] : 'Executive Room';
$initialCapacity = $room ? $room['capacity'] : 'Good for 4 persons';
$initialPriceHtml = $room
    ? '<strong>₱' . number_format($room['price']) . '</strong> / ' . htmlspecialchars($room['price_unit'], ENT_QUOTES, 'UTF-8')
    : '<strong>₱1,999</strong> / night';
?>
<body>

    <?php
    $headerConfig = [
        'book_button_href' => '#booking-widget',
        'book_button_text' => 'Reserve'
    ];
    include __DIR__ . '/includes/site-header.php';
?>


    <main>
        <section class="room-hero" style="background-image: url('<?php echo htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8'); ?>');" role="img" aria-label="<?php echo htmlspecialchars($initialTitle . ' at BODARE Pension House', ENT_QUOTES, 'UTF-8'); ?>">
            </section>

        <section class="room-content-section">
            <div class="container room-layout">
                <div class="room-details-main">
                    <h1 id="room-title"><?php echo htmlspecialchars($initialTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <div class="room-specs">
                        <span>👥 <span id="room-capacity"><?php echo htmlspecialchars($initialCapacity, ENT_QUOTES, 'UTF-8'); ?></span></span>
                        <span>📏 10ft Size</span>
                        <span>🛏️ Normal Beds</span>
                    </div>
                    <div class="room-image-grid" id="room-image-grid-container">
                    </div>
                    <p class="room-description">
                        Spacious and elegantly appointed, the Executive Room is designed for guests seeking extra comfort and space. It provides a relaxing sanctuary with modern amenities, perfect for families or business travelers who appreciate a higher standard of accommodation.
                    </p>

                    <h3>Room Amenities</h3>
                    <div class="amenities-grid">
                        <div class="amenity-item">📺 Cable TV</div>
                        <div class="amenity-item">🚿 Shower</div>
                        <div class="amenity-item">🔒 Safe box</div>
                        <div class="amenity-item">📶 Free WiFi</div>
                        <div class="amenity-item">💼 Work Desk</div>
                        <div class="amenity-item">🛁 Bathtub</div>
                    </div>
                    

                </div>

                <aside class="booking-widget" id="booking-widget"<?php if ($room): ?> data-base-price="<?php echo (int) $room['price']; ?>"<?php endif; ?>>
                    <div class="widget-header">
                        <h2>Reserve</h2>
                        <p>From <span id="room-price-display"><?php echo $initialPriceHtml; ?></span></p>
                    </div>
                    <form class="widget-form" id="booking-form-widget">
                        <div class="date-inputs">
                            <div>
                                <label for="checkin-widget">Check In</label>
                                <input type="date" id="checkin-widget" required>
                            </div>
                            <div>
                                <label for="checkout-widget">Check Out</label>
                                <input type="date" id="checkout-widget" required>
                            </div>
                        </div>
                        <div id="date-error-message" style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem; padding: 0.5rem; background: #f8d7da; border-radius: 4px;"></div>

                        <div class="counters-grid">
                            <div class="guest-inputs">
                                <label>Adults</label>
                                <div class="counter" data-min="1">
                                    <button type="button">-</button>
                                    <input type="text" value="1" readonly id="adults-count">
                                    <button type="button">+</button>
                                </div>
                            </div>
                            <div class="guest-inputs">
                                <label>Children</label>
                                <div class="counter" data-min="0">
                                    <button type="button">-</button>
                                    <input type="text" value="0" readonly id="children-count">
                                    <button type="button">+</button>
                                </div>
                            </div>
                            <div class="guest-inputs">
                                <label>Rooms</label>
                                <div class="counter" data-min="1">
                                    <button type="button">-</button>
                                    <input type="text" value="1" readonly id="room-count-input">
                                    <button type="button">+</button>
                                </div>
                            </div>
                            <div class="guest-inputs">
                                <label>Extra Bed</label>
                                <div class="counter" data-min="0" data-cost="500">
                                    <button type="button">-</button>
                                    <input type="text" value="0" readonly>
                                    <button type="button">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="total-cost">
                            <h3>Total Cost</h3>
                            <span id="total-cost-display"><?php echo $room ? '₱' . number_format($room['price']) : '₱1,999'; ?></span>
                        </div>

                        <button type="submit" class="cta-button">Add to Cart</button>
                    </form>
                </aside>
            </div>
        </section>
    </main>
    
<?php
    include __DIR__ . '/includes/site-footer.php';
?>
    <div id="lightbox-modal" class="lightbox">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-content" id="lightbox-image" alt="Enlarged room photo">
    </div>
    <script src="api-config.js"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
</body>
</html>
