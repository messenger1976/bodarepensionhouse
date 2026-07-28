<?php
require_once __DIR__ . '/includes/site-config.php';

$site = bodare_site_config();
$h = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$rooms = [];
foreach (bodare_room_codes() as $roomKey) {
    $roomMeta = bodare_live_room($roomKey);
    if ($roomMeta) {
        $rooms[] = $roomMeta;
    }
}

$amenityDetails = [
    [
        'icon' => 'bi-wifi',
        'title' => 'High-Speed WiFi',
        'text' => 'Complimentary high-speed internet in rooms and common areas so you stay connected for work or travel.',
    ],
    [
        'icon' => 'bi-p-circle',
        'title' => 'Free Parking',
        'text' => 'Secured on-site parking for registered guests—no extra fees and easy access to your vehicle.',
    ],
    [
        'icon' => 'bi-clock-history',
        'title' => '24-Hour Front Desk',
        'text' => 'Our team is available around the clock for check-in, check-out, and guest assistance.',
    ],
    [
        'icon' => 'bi-snow',
        'title' => 'Air Conditioning',
        'text' => 'Individually controlled air conditioning in every room for a restful stay in Tagbilaran.',
    ],
    [
        'icon' => 'bi-tv',
        'title' => 'Cable Television',
        'text' => 'Unwind with local and international channels on your in-room flat-screen TV.',
    ],
    [
        'icon' => 'bi-droplet',
        'title' => 'Private Bathrooms',
        'text' => 'Clean private bathrooms with hot and cold showers and essential toiletries.',
    ],
];

$whyStay = [
    [
        'icon' => 'bi-geo-alt-fill',
        'title' => 'Prime Location',
        'text' => 'Walking distance to ICM Mall and the soon-to-open SM City Tagbilaran.',
    ],
    [
        'icon' => 'bi-currency-dollar',
        'title' => 'Affordable Rates',
        'text' => 'Budget-friendly lodging from dormitory beds to spacious family rooms.',
    ],
    [
        'icon' => 'bi-lightning-charge-fill',
        'title' => 'Backup Power',
        'text' => 'Uninterrupted power supply so your stay stays comfortable during outages.',
    ],
    [
        'icon' => 'bi-shield-check',
        'title' => 'Safe & Secure',
        'text' => 'A welcoming, family-friendly environment managed by the Bodare cooperative.',
    ],
];

$faqs = [
    [
        'q' => 'Where is BODARE Pension House located?',
        'a' => 'We are at BODARE MPC & Community Bldg, J.A. Clarin St., Dao District, Tagbilaran City, Bohol, Philippines 6300—near ICM Mall and central city amenities.',
    ],
    [
        'q' => 'What room types are available?',
        'a' => 'We offer Dormitory, Standard, Deluxe A, Deluxe B, Ambassador, and Executive rooms to fit solo travelers, couples, families, and groups.',
    ],
    [
        'q' => 'What amenities are included?',
        'a' => 'Guests enjoy free WiFi, free parking, air conditioning, 24-hour front desk, cable TV, and private bathrooms.',
    ],
    [
        'q' => 'How can I inquire or book a room?',
        'a' => 'Use the inquiry form on this page, call ' . $site['phone_display'] . ', email ' . $site['email'] . ', or book online through our Rooms page.',
    ],
];

$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(static function ($faq) {
        return [
            '@type' => 'Question',
            'name' => $faq['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq['a'],
            ],
        ];
    }, $faqs),
];

$webPageSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => 'Stay at BODARE Pension House in Tagbilaran City',
    'url' => bodare_absolute_url('landing-page.php'),
    'description' => 'Affordable rooms and guest amenities at BODARE Pension House in Tagbilaran City, Bohol. Inquire or book your stay online.',
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $site['name'],
        'url' => bodare_absolute_url(),
    ],
    'about' => [
        '@id' => bodare_absolute_url() . '#lodging',
    ],
];

$pageSeo = [
    'title' => 'Stay in Tagbilaran | BODARE Pension House Rooms & Amenities',
    'description' => 'Book affordable lodging at BODARE Pension House in Tagbilaran City, Bohol. Free WiFi, free parking, AC rooms near ICM Mall. Inquire or reserve your stay today.',
    'canonical_path' => 'landing-page.php',
    'include_business_schema' => true,
    'og_image' => 'img/og-default.jpg',
    'og_image_alt' => 'BODARE Pension House — affordable lodging in Tagbilaran City, Bohol',
    'json_ld' => [$webPageSchema, $faqSchema],
    'extra_head' => '<meta http-equiv="Content-Security-Policy" content="default-src \'self\'; base-uri \'self\'; form-action \'self\'; object-src \'none\'; frame-ancestors \'self\'; img-src \'self\' data: https: blob:; font-src \'self\' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com https://cdn.jsdelivr.net; script-src \'self\' \'unsafe-inline\' https://www.google.com https://www.gstatic.com; frame-src https://www.google.com https://maps.google.com; connect-src \'self\' https://www.google.com;">',
];
include __DIR__ . '/includes/site-head.php';
?>
<body class="landing-page-body">

    <?php
    $headerConfig = [
        'about_href' => '#why-stay',
        'book_button_href' => '#inquire',
        'book_button_text' => 'Inquire Now',
    ];
    include __DIR__ . '/includes/site-header.php';
?>

<main class="lp" id="top">

    <section class="lp-hero" aria-label="Welcome to BODARE Pension House">
        <div class="lp-hero-media" aria-hidden="true"></div>
        <div class="lp-hero-overlay"></div>
        <div class="lp-hero-inner lp-reveal">
            <p class="lp-eyebrow">Tagbilaran City, Bohol</p>
            <h1 class="lp-hero-brand">BODARE Pension House</h1>
            <p class="lp-hero-lead">
                Comfortable, affordable lodging near ICM Mall—your home base for exploring Tagbilaran and Bohol.
            </p>
            <div class="lp-hero-actions">
                <a href="#rooms" class="cta-button lp-btn-primary">View Rooms</a>
                <a href="#inquire" class="lp-btn-secondary">Send Inquiry</a>
            </div>
        </div>
        <a href="#why-stay" class="lp-scroll-hint" aria-label="Scroll to learn more">
            <i class="bi bi-chevron-down"></i>
        </a>
    </section>

    <section class="lp-trust" aria-label="Guest highlights">
        <div class="container lp-trust-grid">
            <div class="lp-trust-item lp-reveal">
                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                <span>Near ICM Mall</span>
            </div>
            <div class="lp-trust-item lp-reveal" style="--lp-delay: 0.08s">
                <i class="bi bi-wifi" aria-hidden="true"></i>
                <span>Free WiFi</span>
            </div>
            <div class="lp-trust-item lp-reveal" style="--lp-delay: 0.16s">
                <i class="bi bi-p-circle" aria-hidden="true"></i>
                <span>Free Parking</span>
            </div>
            <div class="lp-trust-item lp-reveal" style="--lp-delay: 0.24s">
                <i class="bi bi-cash-coin" aria-hidden="true"></i>
                <span>From ₱299 / head</span>
            </div>
            <div class="lp-trust-item lp-reveal" style="--lp-delay: 0.32s">
                <i class="bi bi-headset" aria-hidden="true"></i>
                <span>24-Hour Desk</span>
            </div>
        </div>
    </section>

    <section id="why-stay" class="lp-section">
        <div class="container">
            <div class="lp-section-head lp-reveal">
                <p class="lp-eyebrow">Why stay with us</p>
                <h2>A calm base in the heart of Tagbilaran</h2>
                <p class="lp-section-text">
                    BODARE Pension House offers clean, well-appointed rooms without the high price tag.
                    Whether you are here for business, leisure, or a stopover, we put comfort and convenience within easy reach of the city’s main destinations.
                </p>
            </div>
            <div class="lp-why-grid">
                <?php foreach ($whyStay as $i => $item): ?>
                    <article class="lp-why-card lp-reveal" style="--lp-delay: <?php echo $h(number_format($i * 0.08, 2)); ?>s">
                        <i class="bi <?php echo $h($item['icon']); ?>" aria-hidden="true"></i>
                        <h3><?php echo $h($item['title']); ?></h3>
                        <p><?php echo $h($item['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="lp-closing lp-reveal">
                Managed by <?php echo $h($site['legal_name']); ?>, we take pride in friendly service and memorable stays for every guest.
            </p>
        </div>
    </section>

    <section id="rooms" class="lp-section lp-section-alt">
        <div class="container">
            <div class="lp-section-head lp-reveal">
                <p class="lp-eyebrow">Rooms &amp; rates</p>
                <h2>Find the room that fits your trip</h2>
                <p class="lp-section-text">
                    From budget dormitory beds to spacious executive rooms, every option includes the essentials for a restful stay in Tagbilaran City.
                </p>
            </div>
            <div class="lp-room-grid">
                <?php foreach ($rooms as $i => $room): ?>
                    <?php
                    $services = !empty($room['amenities'])
                        ? implode(', ', array_slice($room['amenities'], 0, 4))
                        : 'WiFi, Television, Bathroom';
                    $detailUrl = 'room-detail.php?room=' . rawurlencode($room['code']);
                    ?>
                    <article class="lp-room-card lp-reveal" style="--lp-delay: <?php echo $h(number_format(($i % 3) * 0.08, 2)); ?>s">
                        <a class="lp-room-media" href="<?php echo $h($detailUrl); ?>">
                            <img
                                src="<?php echo $h($room['image']); ?>"
                                alt="<?php echo $h($room['title'] . ' at BODARE Pension House in Tagbilaran City'); ?>"
                                loading="lazy"
                                width="640"
                                height="420"
                                onerror="this.onerror=null;this.src='img/og-default.jpg';"
                            >
                        </a>
                        <div class="lp-room-body">
                            <h3><a href="<?php echo $h($detailUrl); ?>"><?php echo $h($room['title']); ?></a></h3>
                            <p class="lp-room-price">
                                <strong>₱<?php echo $h(number_format((float) $room['price'])); ?></strong>
                                <?php echo $h($room['price_unit']); ?>
                            </p>
                            <p class="lp-room-meta"><?php echo $h($room['capacity']); ?></p>
                            <p class="lp-room-desc"><?php echo $h($room['description']); ?></p>
                            <p class="lp-room-services"><span>Includes:</span> <?php echo $h($services); ?></p>
                            <div class="lp-room-actions">
                                <a href="<?php echo $h($detailUrl); ?>" class="cta-button">View Details</a>
                                <a href="rooms.php" class="lp-text-link">Compare rooms</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="lp-mid-cta lp-reveal">
                <p>Ready to reserve? Check live availability and book online.</p>
                <a href="rooms.php" class="cta-button">Browse All Rooms</a>
            </div>
        </div>
    </section>

    <section id="amenities" class="lp-section">
        <div class="container">
            <div class="lp-section-head lp-reveal">
                <p class="lp-eyebrow">Guest amenities</p>
                <h2>Everything you need for a comfortable stay</h2>
                <p class="lp-section-text">
                    Essential facilities designed for convenience—so you can focus on your trip, not the logistics.
                </p>
            </div>
            <div class="lp-amenity-grid">
                <?php foreach ($amenityDetails as $i => $amenity): ?>
                    <article class="lp-amenity-card lp-reveal" style="--lp-delay: <?php echo $h(number_format(($i % 3) * 0.08, 2)); ?>s">
                        <span class="lp-amenity-icon" aria-hidden="true">
                            <i class="bi <?php echo $h($amenity['icon']); ?>"></i>
                        </span>
                        <h3><?php echo $h($amenity['title']); ?></h3>
                        <p><?php echo $h($amenity['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="lp-section-foot lp-reveal">
                <a href="amenities.php" class="lp-text-link">See full amenities list</a>
            </p>
        </div>
    </section>

    <section class="lp-banner lp-reveal" aria-label="Call to action">
        <div class="container lp-banner-inner">
            <div>
                <h2>Plan your Tagbilaran stay today</h2>
                <p>Tell us your dates and room preference—we’ll help you find the right fit.</p>
            </div>
            <div class="lp-banner-actions">
                <a href="#inquire" class="cta-button">Inquire Now</a>
                <a href="tel:<?php echo $h($site['phone_e164']); ?>" class="lp-btn-secondary lp-btn-on-dark">
                    Call <?php echo $h($site['phone_display']); ?>
                </a>
            </div>
        </div>
    </section>

    <section id="inquire" class="lp-section lp-section-alt">
        <div class="container lp-inquire-layout">
            <div class="lp-inquire-copy lp-reveal">
                <p class="lp-eyebrow">Contact &amp; inquiries</p>
                <h2>Send us a message</h2>
                <p class="lp-section-text">
                    Have questions about rates, group bookings, or room availability? Reach out and our team will respond as soon as possible.
                </p>
                <ul class="lp-contact-list">
                    <li>
                        <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                        <span>
                            <?php echo $h($site['street_address']); ?><br>
                            <?php echo $h($site['address_locality'] . ', ' . $site['address_region'] . ' ' . $site['postal_code']); ?>
                        </span>
                    </li>
                    <li>
                        <i class="bi bi-telephone-fill" aria-hidden="true"></i>
                        <a href="tel:<?php echo $h($site['phone_e164']); ?>"><?php echo $h($site['phone_display']); ?></a>
                    </li>
                    <li>
                        <i class="bi bi-envelope-fill" aria-hidden="true"></i>
                        <a href="mailto:<?php echo $h($site['email']); ?>"><?php echo $h($site['email']); ?></a>
                    </li>
                    <li>
                        <i class="bi bi-facebook" aria-hidden="true"></i>
                        <a href="<?php echo $h($site['facebook_url']); ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
                        &nbsp;·&nbsp;
                        <a href="<?php echo $h($site['messenger_url']); ?>" target="_blank" rel="noopener noreferrer">Messenger</a>
                    </li>
                </ul>
            </div>

            <div class="lp-inquire-form-wrap lp-reveal" style="--lp-delay: 0.12s">
                <div id="contact-form-alert" class="lp-form-alert" style="display:none;" role="alert"></div>
                <form id="contact-inquiry-form" class="lp-form" action="#" novalidate autocomplete="on">
                    <input type="hidden" name="csrf_token" id="contact-csrf-token" value="">
                    <div class="hp-field" aria-hidden="true" style="position:absolute!important;left:-10000px!important;top:auto!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;pointer-events:none!important;">
                        <label for="company_url">Company Website</label>
                        <input type="text" id="company_url" name="company_url" value="" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="lp-form-row">
                        <div class="lp-field">
                            <label for="contact-first-name">First Name</label>
                            <input type="text" id="contact-first-name" name="first_name" required maxlength="75" autocomplete="given-name" placeholder="Juan">
                        </div>
                        <div class="lp-field">
                            <label for="contact-last-name">Last Name</label>
                            <input type="text" id="contact-last-name" name="last_name" required maxlength="75" autocomplete="family-name" placeholder="Dela Cruz">
                        </div>
                    </div>
                    <div class="lp-field">
                        <label for="contact-email">Email</label>
                        <input type="email" id="contact-email" name="email" required maxlength="255" autocomplete="email" placeholder="you@email.com">
                    </div>
                    <div class="lp-field">
                        <label for="contact-subject">Subject</label>
                        <input type="text" id="contact-subject" name="subject" required maxlength="255" placeholder="Room inquiry for next weekend">
                    </div>
                    <div class="lp-field">
                        <label for="contact-message">Message</label>
                        <textarea id="contact-message" name="message" rows="5" required maxlength="5000" placeholder="Tell us your preferred dates, number of guests, and room type."></textarea>
                    </div>
                    <button type="submit" class="cta-button lp-submit" id="contact-submit-btn">Send Inquiry</button>
                    <p class="lp-form-note">Or visit our <a href="contact.php">full contact page</a> for the map and more options.</p>
                </form>
            </div>
        </div>
    </section>

    <section id="location" class="lp-section">
        <div class="container">
            <div class="lp-section-head lp-reveal">
                <p class="lp-eyebrow">Find us</p>
                <h2>Easy to reach in Tagbilaran City</h2>
                <p class="lp-section-text">
                    Located on J.A. Clarin St., Dao District—close to shopping, dining, and city transport.
                </p>
            </div>
            <div class="lp-map-wrap lp-reveal">
                <iframe
                    src="https://www.google.com/maps?q=BODARE%20MPC%20%26%20Community%20Bldg%2C%20J.A.%20Clarin%20St.%2C%20Dao%20District%2C%20Tagbilaran%20City%2C%20Bohol%2C%20Philippines%206300&amp;output=embed"
                    class="lp-map"
                    title="Map showing BODARE Pension House location on J.A. Clarin St., Dao District, Tagbilaran City"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>

    <section id="faq" class="lp-section lp-section-alt">
        <div class="container lp-faq">
            <div class="lp-section-head lp-reveal">
                <p class="lp-eyebrow">FAQ</p>
                <h2>Common questions from guests</h2>
            </div>
            <div class="lp-faq-list">
                <?php foreach ($faqs as $i => $faq): ?>
                    <details class="lp-faq-item lp-reveal" style="--lp-delay: <?php echo $h(number_format($i * 0.06, 2)); ?>s" <?php echo $i === 0 ? 'open' : ''; ?>>
                        <summary><?php echo $h($faq['q']); ?></summary>
                        <p><?php echo $h($faq['a']); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="lp-final-cta">
        <div class="container lp-final-inner lp-reveal">
            <h2>Your Tagbilaran stay starts here</h2>
            <p>Browse rooms, send an inquiry, or call us—we’re ready to welcome you.</p>
            <div class="lp-hero-actions">
                <a href="rooms.php" class="cta-button">Book a Room</a>
                <a href="#inquire" class="lp-btn-secondary lp-btn-on-dark">Ask a Question</a>
            </div>
        </div>
    </section>

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
            alertBox.className = 'lp-form-alert ' + (type === 'success' ? 'is-success' : 'is-error');
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
                submitBtn.textContent = 'Send Inquiry';
            }
        });
    })();
    </script>
</body>
</html>
