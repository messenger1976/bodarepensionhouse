<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Contact BODARE Pension House for reservations and inquiries">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Contact Us - BODARE Pension House</title>
    
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


    <section class="page-header" style="background-image: url('img/executive.jpg');">
        <div class="page-header-content">
            <h1>Get In Touch</h1>
            <p>We're here to help. Contact us with any questions or for special requests.</p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            
            <div class="contact-info-grid">
                <div class="contact-info-card">
                    <span class="icon">📍</span>
                    <strong>Address</strong>
                    <p>123 Luxury Lane<br>Tagbilaran City, Bohol 6300</p>
                </div>
                <div class="contact-info-card">
                    <span class="icon">📞</span>
                    <strong>Phone</strong>
                    <p>(038) 411-0000</p>
                </div>
                <div class="contact-info-card">
                    <span class="icon">✉️</span>
                    <strong>Email</strong>
                    <p>reservations@bodarecoop.com</p>
                </div>
            </div>

            <div class="contact-form-centered">
                <h2>Send Us a Message</h2>
                <form action="#" class="minimal-form">
                    <div class="form-grid-2">
                        <div class="form-group-contact">
                            <input type="text" id="contact-first-name" placeholder="First Name" required>
                        </div>
                        <div class="form-group-contact">
                            <input type="text" id="contact-last-name" placeholder="Last Name" required>
                        </div>
                    </div>
                    <div class="form-group-contact">
                        <input type="email" id="contact-email" placeholder="Email Address" required>
                    </div>
                    <div class="form-group-contact">
                        <input type="text" id="contact-subject" placeholder="Subject" required>
                    </div>
                    <div class="form-group-contact">
                        <textarea id="contact-message" placeholder="Your Message" rows="6" required></textarea>
                    </div>
                    
                    <button type="submit" class="cta-button">Send Message</button>
                </form>
            </div>

            <div class="contact-map-section">
                <h2>Our Location</h2>
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15720.198338965688!2d123.84650532997193!3d9.65651921313175!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33aa4db4591a2e79%3A0x869b0c74b1f6365!2sTagbilaran%20City%2C%20Bohol!5e0!3m2!1sen!2sph!4v1729352771569!5m2!1sen!2sph" 
                    class="google-map"
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
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



