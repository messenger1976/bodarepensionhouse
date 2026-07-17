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
                <div id="contact-form-alert" style="display:none;margin-bottom:1rem;padding:0.75rem 1rem;border-radius:4px;"></div>
                <form id="contact-inquiry-form" action="#" class="minimal-form">
                    <div class="form-grid-2">
                        <div class="form-group-contact">
                            <input type="text" id="contact-first-name" name="first_name" placeholder="First Name" required maxlength="75">
                        </div>
                        <div class="form-group-contact">
                            <input type="text" id="contact-last-name" name="last_name" placeholder="Last Name" required maxlength="75">
                        </div>
                    </div>
                    <div class="form-group-contact">
                        <input type="email" id="contact-email" name="email" placeholder="Email Address" required maxlength="255">
                    </div>
                    <div class="form-group-contact">
                        <input type="text" id="contact-subject" name="subject" placeholder="Subject" required maxlength="255">
                    </div>
                    <div class="form-group-contact">
                        <textarea id="contact-message" name="message" placeholder="Your Message" rows="6" required maxlength="5000"></textarea>
                    </div>
                    
                    <button type="submit" class="cta-button" id="contact-submit-btn">Send Message</button>
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
    <script>
    (function () {
        var form = document.getElementById('contact-inquiry-form');
        if (!form || typeof API === 'undefined') return;

        var alertBox = document.getElementById('contact-form-alert');
        var submitBtn = document.getElementById('contact-submit-btn');

        function showAlert(type, message) {
            if (!alertBox) return;
            alertBox.style.display = 'block';
            alertBox.style.background = type === 'success' ? '#e8f5e9' : '#ffebee';
            alertBox.style.color = type === 'success' ? '#1b5e20' : '#b71c1c';
            alertBox.style.border = '1px solid ' + (type === 'success' ? '#a5d6a7' : '#ef9a9a');
            alertBox.textContent = message;
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            var first = (document.getElementById('contact-first-name').value || '').trim();
            var last = (document.getElementById('contact-last-name').value || '').trim();
            var email = (document.getElementById('contact-email').value || '').trim();
            var subject = (document.getElementById('contact-subject').value || '').trim();
            var message = (document.getElementById('contact-message').value || '').trim();
            var name = (first + ' ' + last).trim();

            if (!name || !email || !subject || !message) {
                showAlert('danger', 'Please fill in all fields.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            try {
                var result = await API.request('inquiry/submit', {
                    method: 'POST',
                    body: JSON.stringify({ name: name, email: email, subject: subject, message: message })
                });
                if (result && result.success) {
                    showAlert('success', result.message || 'Thank you! Your message has been sent.');
                    form.reset();
                } else {
                    showAlert('danger', (result && result.message) ? result.message : 'Could not send your message.');
                }
            } catch (err) {
                showAlert('danger', 'Could not send your message. Please try again.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Message';
            }
        });
    })();
    </script>
</body>
</html>



