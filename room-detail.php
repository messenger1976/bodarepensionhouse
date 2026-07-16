<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="View room details and book at BODARE Pension House">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Room Details - BODARE Pension House</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="img/logo.png">
    <link rel="icon" type="image/png" href="img/logo.png">
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/confirmDate/confirmDate.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/confirmDate/confirmDate.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Jost:wght@200;300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>

    <?php
    $headerConfig = [
        'book_button_href' => '#booking-widget',
        'book_button_text' => 'Reserve'
    ];
    include __DIR__ . '/includes/site-header.php';
?>


    <main>
        <section class="room-hero" style="background-image: url('https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=2070&auto=format&fit=crop');">
            </section>

        <section class="room-content-section">
            <div class="container room-layout">
                <div class="room-details-main">
                    <h1 id="room-title">Executive Room</h1>
                    <div class="room-specs">
                        <span>👥 <span id="room-capacity">Good for 4 persons</span></span>
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

                <aside class="booking-widget" id="booking-widget">
                    <div class="widget-header">
                        <h2>Reserve</h2>
                        <p>From <span id="room-price-display"><strong>₱1,999</strong> / night</span></p>
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
                            <span id="total-cost-display">₱1,999</span>
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
        <img class="lightbox-content" id="lightbox-image">
    </div>
    <script src="api-config.js"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
</body>
</html>



