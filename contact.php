<?php
require_once __DIR__ . '/includes/site-config.php';
$pageSeo = [
    'title' => 'Contact Us | BODARE Pension House Tagbilaran City',
    'description' => 'Contact BODARE Pension House for reservations and inquiries. Visit us at J.A. Clarin St., Dao District, Tagbilaran City, Bohol, or call 0950 533 7480.',
    'canonical_path' => 'contact.php',
    'include_business_schema' => true,
    'og_image' => 'img/og-default.jpg',
    'og_image_alt' => 'Contact BODARE Pension House in Tagbilaran City, Bohol',
    'json_ld' => [
        '@context' => 'https://schema.org',
        '@type' => 'ContactPage',
        'name' => 'Contact BODARE Pension House',
        'url' => bodare_absolute_url('contact.php'),
        'mainEntity' => [
            '@id' => bodare_absolute_url() . '#lodging',
        ],
    ],
];
include __DIR__ . '/includes/site-head.php';
?>
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
                    <p>BODARE MPC &amp; Community Bldg<br>J.A. Clarin St., Dao District<br>Tagbilaran City, Bohol<br>Philippines 6300</p>
                </div>
                <div class="contact-info-card">
                    <span class="icon">📞</span>
                    <strong>Phone</strong>
                    <p>0950 533 7480</p>
                </div>
                <div class="contact-info-card">
                    <span class="icon">✉️</span>
                    <strong>Email</strong>
                    <p>bodarepensionhouse@yahoo.com</p>
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
                    src="https://www.google.com/maps?q=BODARE%20MPC%20%26%20Community%20Bldg%2C%20J.A.%20Clarin%20St.%2C%20Dao%20District%2C%20Tagbilaran%20City%2C%20Bohol%2C%20Philippines%206300&amp;output=embed"
                    class="google-map"
                    title="Map showing BODARE Pension House location on J.A. Clarin St., Dao District, Tagbilaran City"
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



