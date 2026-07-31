<?php
$pageSeo = [
    'title' => 'Amenities & Guest Services | BODARE Pension House',
    'description' => 'Enjoy free WiFi, free parking, air conditioning, 24-hour front desk, cable TV, and private bathrooms at BODARE Pension House in Tagbilaran City.',
    'canonical_path' => 'amenities.php',
    'og_image' => 'img/ambassador.jpg',
];
include __DIR__ . '/includes/site-head.php';
?>
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
            <h2 class="section-title" style="text-align:center;margin-bottom:2rem;">What Guests Enjoy</h2>
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
            <?php adsense_render_unit('in_content', 'adsense-in-content'); ?>
        </div>
    </main>

<?php
    include __DIR__ . '/includes/site-footer.php';
?>
    
    <script src="api-config.js"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
</body>
</html>



