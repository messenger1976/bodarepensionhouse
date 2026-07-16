<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="View our photo gallery showcasing BODARE Pension House">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Gallery - BODARE Pension House</title>
    
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
            <h1>Our Gallery</h1>
            <p>A glimpse into the comfort and style that awaits you.</p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            <div class="gallery-grid">
                <img src="img/dormitory.jpg" alt="Dormitory" class="gallery-image">
                <img src="img/standard.jpg" alt="Standard Room" class="gallery-image">
                <img src="img/deluxeb.jpg" alt="Deluxe B Room" class="gallery-image">
                <img src="img/deluxea.jpg" alt="Deluxe A Room" class="gallery-image">
                <img src="img/ambassador.jpg" alt="Ambassador Room" class="gallery-image">
                <img src="img/executive.jpg" alt="Executive Room" class="gallery-image">
                </div>
        </div>
    </main>

<div id="lightbox-modal" class="lightbox">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-content" id="lightbox-image">
    </div>

<?php
    include __DIR__ . '/includes/site-footer.php';
?>
    <script src="api-config.js"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
</body>
</html>



