<?php
$pageSeo = [
    'title' => 'Photo Gallery | BODARE Pension House Tagbilaran',
    'description' => 'Explore photos of rooms and spaces at BODARE Pension House in Tagbilaran City, Bohol — from dormitory lodging to executive suites.',
    'canonical_path' => 'gallery.php',
    'og_image' => 'img/ambassador.jpg',
];
include __DIR__ . '/includes/site-head.php';
?>
<body>

    <?php
    include __DIR__ . '/includes/site-header.php';
?>


    <section class="app-page-hero">
        <div class="page-header-content app-section" style="padding-top:0;padding-bottom:0;">
            <h1>Our Gallery</h1>
            <p>A glimpse into the comfort and style that awaits you.</p>
        </div>
    </section>

    <main class="content-section app-section">
        <div class="container" style="max-width:80rem;padding:0;">
            <div class="gallery-grid">
                <img src="img/dormitory.jpg" alt="Dormitory lodging at BODARE Pension House in Tagbilaran City" class="gallery-image">
                <img src="img/standard.jpg" alt="Standard guest room at BODARE Pension House" class="gallery-image">
                <img src="img/deluxeb.jpg" alt="Deluxe B room interior at BODARE Pension House" class="gallery-image">
                <img src="img/deluxea.jpg" alt="Deluxe A room interior at BODARE Pension House" class="gallery-image">
                <img src="img/ambassador.jpg" alt="Ambassador room at BODARE Pension House" class="gallery-image">
                <img src="img/executive.jpg" alt="Executive room at BODARE Pension House" class="gallery-image">
                </div>
        </div>
    </main>

<div id="lightbox-modal" class="lightbox">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-content" id="lightbox-image" alt="Enlarged gallery photo of BODARE Pension House">
    </div>

<?php
    include __DIR__ . '/includes/site-footer.php';
?>
    <script src="api-config.js"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
</body>
</html>



