<?php
require_once __DIR__ . '/includes/site-config.php';
$enableAds = false;
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
    'extra_head' => '<meta http-equiv="Content-Security-Policy" content="default-src \'self\'; base-uri \'self\'; form-action \'self\'; object-src \'none\'; frame-ancestors \'self\'; img-src \'self\' data: https: blob:; font-src \'self\' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdn.tailwindcss.com; script-src \'self\' \'unsafe-inline\' https://www.google.com https://www.gstatic.com https://cdn.tailwindcss.com https://unpkg.com; frame-src https://www.google.com https://maps.google.com; connect-src \'self\' https://www.google.com https://cdn.tailwindcss.com;">',
];
include __DIR__ . '/includes/site-head.php';
?>
<body>

    <?php
    include __DIR__ . '/includes/site-header.php';
?>


    <section class="app-page-hero">
        <div class="page-header-content app-section" style="padding-top:0;padding-bottom:0;">
            <h1>Get In Touch</h1>
            <p>We're here to help. Contact us with any questions or for special requests.</p>
        </div>
    </section>

    <main class="content-section app-section">
        <div class="container" style="max-width:80rem;padding:0;">
            
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
                <div id="contact-form-alert" style="display:none;margin-bottom:1rem;padding:0.75rem 1rem;border-radius:4px;" role="alert"></div>
                <form id="contact-inquiry-form" action="#" class="minimal-form" novalidate autocomplete="on">
                    <input type="hidden" name="csrf_token" id="contact-csrf-token" value="">
                    <!-- Honeypot: leave empty. Hidden from humans, filled by many bots. -->
                    <div class="hp-field" aria-hidden="true" style="position:absolute!important;left:-10000px!important;top:auto!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;pointer-events:none!important;">
                        <label for="company_url">Company Website</label>
                        <input type="text" id="company_url" name="company_url" value="" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group-contact">
                            <input type="text" id="contact-first-name" name="first_name" placeholder="First Name" required maxlength="75" autocomplete="given-name">
                        </div>
                        <div class="form-group-contact">
                            <input type="text" id="contact-last-name" name="last_name" placeholder="Last Name" required maxlength="75" autocomplete="family-name">
                        </div>
                    </div>
                    <div class="form-group-contact">
                        <input type="email" id="contact-email" name="email" placeholder="Email Address" required maxlength="255" autocomplete="email">
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

            <div class="contact-map-section" id="location">
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
    
    <script src="api-config.js?v=<?php echo filemtime(__DIR__ . '/api-config.js'); ?>"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
    <script>
    (function () {
        var form = document.getElementById('contact-inquiry-form');
        if (!form) return;

        var alertBox = document.getElementById('contact-form-alert');
        var submitBtn = document.getElementById('contact-submit-btn');
        var csrfInput = document.getElementById('contact-csrf-token');
        var security = {
            csrf_token: '',
            honeypot_field: 'company_url',
            recaptcha_enabled: false,
            recaptcha_site_key: '',
            recaptcha_action: 'contact_submit',
            ready: false
        };

        function showAlert(type, message) {
            if (!alertBox) return;
            alertBox.style.display = 'block';
            alertBox.style.background = type === 'success' ? '#e8f5e9' : '#ffebee';
            alertBox.style.color = type === 'success' ? '#1b5e20' : '#b71c1c';
            alertBox.style.border = '1px solid ' + (type === 'success' ? '#a5d6a7' : '#ef9a9a');
            alertBox.textContent = message;
        }

        function loadRecaptcha(siteKey) {
            return new Promise(function (resolve, reject) {
                if (window.grecaptcha && window.grecaptcha.execute) {
                    resolve();
                    return;
                }
                var existing = document.querySelector('script[data-recaptcha-v3]');
                if (existing) {
                    existing.addEventListener('load', function () { resolve(); });
                    existing.addEventListener('error', function () { reject(new Error('Failed to load CAPTCHA.')); });
                    return;
                }
                var s = document.createElement('script');
                s.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(siteKey);
                s.async = true;
                s.defer = true;
                s.setAttribute('data-recaptcha-v3', '1');
                s.onload = function () { resolve(); };
                s.onerror = function () { reject(new Error('Failed to load CAPTCHA.')); };
                document.head.appendChild(s);
            });
        }

        async function getRecaptchaToken() {
            if (!security.recaptcha_enabled || !security.recaptcha_site_key) {
                return '';
            }
            await loadRecaptcha(security.recaptcha_site_key);
            return await new Promise(function (resolve, reject) {
                window.grecaptcha.ready(function () {
                    window.grecaptcha.execute(security.recaptcha_site_key, { action: security.recaptcha_action || 'contact_submit' })
                        .then(resolve)
                        .catch(reject);
                });
            });
        }

        async function refreshSecurity() {
            if (typeof API === 'undefined' || !API.inquiry || !API.inquiry.csrf) {
                throw new Error('API not loaded. Please refresh.');
            }
            var result = await API.inquiry.csrf();
            if (!result || !result.success || !result.csrf_token) {
                throw new Error('Could not initialize form security. Please refresh.');
            }
            security.csrf_token = result.csrf_token;
            security.honeypot_field = result.honeypot_field || 'company_url';
            security.recaptcha_enabled = !!result.recaptcha_enabled;
            security.recaptcha_site_key = result.recaptcha_site_key || '';
            security.recaptcha_action = result.recaptcha_action || 'contact_submit';
            security.ready = true;
            if (csrfInput) csrfInput.value = security.csrf_token;
            if (security.recaptcha_enabled) {
                try { await loadRecaptcha(security.recaptcha_site_key); } catch (e) { /* loaded on submit */ }
            }
        }

        refreshSecurity().catch(function (err) {
            showAlert('danger', (err && err.message) ? err.message : 'Could not initialize form security.');
            if (submitBtn) submitBtn.disabled = true;
        });

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (typeof API === 'undefined') {
                showAlert('danger', 'API not loaded. Please refresh.');
                return;
            }

            var honeypot = document.getElementById('company_url');
            if (honeypot && honeypot.value) {
                showAlert('success', 'Thank you! Your message has been sent.');
                form.reset();
                return;
            }

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
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showAlert('danger', 'Please enter a valid email address.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            try {
                if (!security.ready || !security.csrf_token) {
                    await refreshSecurity();
                }
                var payload = {
                    name: name,
                    email: email,
                    subject: subject,
                    message: message,
                    csrf_token: security.csrf_token,
                    recaptcha_token: await getRecaptchaToken()
                };
                payload[security.honeypot_field || 'company_url'] = honeypot ? honeypot.value : '';

                var result = await API.inquiry.submit(payload);
                if (result && result.success) {
                    showAlert('success', result.message || 'Thank you! Your message has been sent.');
                    form.reset();
                } else {
                    showAlert('danger', (result && result.message) ? result.message : 'Could not send your message.');
                }
            } catch (err) {
                showAlert('danger', (err && err.message) ? err.message : 'Could not send your message. Please try again.');
            } finally {
                try { await refreshSecurity(); } catch (e) { /* ignore */ }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Message';
            }
        });
    })();
    </script>
</body>
</html>
