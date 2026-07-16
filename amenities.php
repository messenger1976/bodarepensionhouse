<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Discover the amenities and services at BODARE Pension House">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Amenities - BODARE Pension House</title>
    
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

    <?php
    include __DIR__ . '/includes/site-header.php';
?>


    <section class="page-header" style="background-image: url('img/ambassador.jpg');">
        <div class="page-header-content">
            <h1>Our Amenities</h1>
            <p>Services and facilities designed for your comfort and convenience.</p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            <div class="amenities-grid">
                
                <div class="amenity-card">
                    <span class="icon">📶</span>
                    <h3>High-Speed WiFi</h3>
                    <p>Stay connected with complimentary high-speed internet access available in all rooms and public areas.</p>
                </div>

                <div class="amenity-card">
                    <span class="icon">🅿️</span>
                    <h3>Free Parking</h3>
                    <p>Enjoy the convenience of free, secured on-site parking for all our registered guests.</p>
                </div>

                <div class="amenity-card">
                    <span class="icon">🛎️</span>
                    <h3>24-Hour Front Desk</h3>
                    <p>Our team is available around the clock to assist with check-in, check-out, and any requests you may have.</p>
                </div>

                <div class="amenity-card">
                    <span class="icon">❄️</span>
                    <h3>Air Conditioning</h3>
                    <p>All rooms are equipped with individually controlled air conditioning for your personal comfort.</p>
                </div>

                <div class="amenity-card">
                    <span class="icon">📺</span>
                    <h3>Cable Television</h3>
                    <p>Unwind with a wide selection of local and international channels on your in-room flat-screen TV.</p>
                </div>

                <div class="amenity-card">
                    <span class="icon">🚿</span>
                    <h3>Private Bathrooms</h3>
                    <p>Each room features a clean, private bathroom complete with hot and cold showers and essential toiletries.</p>
                </div>
                
            </div>
        </div>
    </main>

<?php
    include __DIR__ . '/includes/site-footer.php';
?>
    
    <script src="api-config.js"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
</body>
</html>



