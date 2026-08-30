<?php
$pageSeo = [
    'title' => 'My Dashboard | BODARE Pension House',
    'description' => 'Manage your BODARE Pension House account, bookings, and inquiries.',
    'canonical_path' => 'customer-dashboard.php',
    'robots' => 'noindex,nofollow',
];
$enableAds = false;
include __DIR__ . '/includes/site-head.php';
?>
<body>

    <?php
    $headerConfig = [
        'logo_href' => 'index.php',
    ];
    include __DIR__ . '/includes/site-header.php';
?>


    <section class="app-page-hero">
        <div class="page-header-content app-section" style="padding-top:0;padding-bottom:0;">
            <h1>My Dashboard</h1>
            <p>Manage your account, bookings, and inquiries</p>
        </div>
    </section>

    <main class="content-section app-section">
        <div class="container">
            
            <!-- No JavaScript Warning -->
            <noscript>
                <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                    <strong>JavaScript Required:</strong> This page requires JavaScript to function properly. Please enable JavaScript in your browser settings.
                </div>
            </noscript>
            
            <div class="customer-dashboard-layout">
                <?php
                $customerSidebarActiveTab = isset($_GET['tab']) ? $_GET['tab'] : 'bookings';
                if (!in_array($customerSidebarActiveTab, ['bookings', 'invoices', 'profile', 'security', 'inquiry'], true)) {
                    $customerSidebarActiveTab = 'bookings';
                }
                include __DIR__ . '/includes/customer-sidebar.php';
                ?>

                <section class="customer-dashboard-content">

            <!-- Bookings Tab Content -->
            <div id="bookings-tab" class="tab-content active">
                <div id="bookings-container">
                    <div class="loading-message" style="text-align: center; padding: 2rem; color: #666;">
                        <p>Loading your bookings...</p>
                    </div>
                </div>
            </div>

            <!-- Invoices Tab Content -->
            <div id="invoices-tab" class="tab-content" style="display: none;">
                <div id="invoices-list-container">
                    <div class="loading-message" style="text-align: center; padding: 2rem; color: #666;">
                        <p>Loading your invoices...</p>
                    </div>
                </div>
                <div id="invoice-detail-panel" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                        <h2 id="invoice-detail-title" style="margin: 0; color: #1a2238;">Invoice Details</h2>
                        <button type="button" id="invoice-back-btn" class="cta-button" style="background: #6c757d; border-color: #6c757d; color: #fff; padding: 0.5rem 1.25rem;">Back to List</button>
                    </div>
                    <div id="invoice-detail-content"></div>
                </div>
            </div>
            
            <!-- Error message container -->
            <div id="dashboard-error" style="display: none; text-align: center; padding: 2rem; background: #f8d7da; color: #721c24; border-radius: 8px; margin: 1rem 0;">
                <p id="error-message"></p>
            </div>

            <!-- Profile Tab Content -->
            <div id="profile-tab" class="tab-content" style="display: none;">
                <div class="registration-container">
                    <h2>Update Your Information</h2>
                    <form id="profile-form" class="minimal-form">
                        <h3 style="margin-bottom: 1rem; color: #1a2238; font-size: 1.25rem;">Personal Information</h3>
                        <div class="form-grid-2">
                            <div class="form-group-contact">
                                <input type="text" id="profile-first-name" placeholder="First Name *" required>
                            </div>
                            <div class="form-group-contact">
                                <input type="text" id="profile-last-name" placeholder="Last Name *" required>
                            </div>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group-contact">
                                <input type="email" id="profile-email" placeholder="Email Address *" required>
                            </div>
                            <div class="form-group-contact">
                                <input type="tel" id="profile-phone" placeholder="Phone Number *" required>
                            </div>
                        </div>
                        <div class="form-grid-3">
                            <div class="form-group-contact">
                                <input type="date" id="profile-date-of-birth" placeholder="Date of Birth">
                                <label for="profile-date-of-birth" style="display: block; margin-top: 0.5rem; font-size: 0.875rem; color: #666;">Date of Birth</label>
                            </div>
                            <div class="form-group-contact">
                                <select id="profile-gender" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group-contact">
                                <input type="text" id="profile-nationality" placeholder="Nationality (e.g., Filipino)">
                            </div>
                        </div>
                        
                        <h3 style="margin: 2rem 0 1rem 0; color: #1a2238; font-size: 1.25rem;">Address Information</h3>
                        <div class="form-group-contact">
                            <textarea id="profile-address" placeholder="Street Address *" rows="2" required></textarea>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group-contact">
                                <select id="profile-country" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;">
                                    <option value="Philippines">Philippines</option>
                                </select>
                            </div>
                            <div class="form-group-contact">
                                <select id="profile-province-select" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;">
                                    <option value="">Select Province</option>
                                </select>
                                <input type="text" id="profile-province-manual" placeholder="Province" style="display: none; margin-top: 0.5rem;">
                            </div>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group-contact">
                                <select id="profile-city-select" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;">
                                    <option value="">Select Town/City</option>
                                </select>
                                <input type="text" id="profile-city-manual" placeholder="Town/City" style="display: none; margin-top: 0.5rem;">
                            </div>
                            <div class="form-group-contact">
                                <select id="profile-barangay-select" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;">
                                    <option value="">Select Barangay</option>
                                </select>
                                <input type="text" id="profile-barangay-manual" placeholder="Barangay" style="display: none; margin-top: 0.5rem;">
                            </div>
                        </div>
                        <div class="form-group-contact">
                            <input type="text" id="profile-postal-code" placeholder="Postal Code">
                        </div>
                        
                        <h3 style="margin: 2rem 0 1rem 0; color: #1a2238; font-size: 1.25rem;">Identification (Optional)</h3>
                        <div class="form-grid-2">
                            <div class="form-group-contact">
                                <select id="profile-id-type" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;">
                                    <option value="">Select ID Type</option>
                                    <option value="passport">Passport</option>
                                    <option value="driver_license">Driver's License</option>
                                    <option value="national_id">National ID</option>
                                    <option value="philhealth">PhilHealth ID</option>
                                    <option value="sss">SSS ID</option>
                                    <option value="tin">TIN ID</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group-contact">
                                <input type="text" id="profile-id-number" placeholder="ID Number">
                            </div>
                        </div>
                        
                        <button type="submit" class="cta-button">Update Profile</button>
                    </form>
                </div>
            </div>

            <!-- Security Tab Content -->
            <div id="security-tab" class="tab-content" style="display: none;">
                <div class="registration-container">
                    <h2>Account Security</h2>
                    <p style="color: #666; margin-bottom: 1.5rem;">Update your password separately from your profile information.</p>
                    <form id="security-form" class="minimal-form">
                        <div class="form-group-contact">
                            <input type="password" id="security-current-password" placeholder="Current Password" required>
                        </div>
                        <div class="form-group-contact">
                            <input type="password" id="security-new-password" placeholder="New Password" required>
                        </div>
                        <div class="form-group-contact">
                            <input type="password" id="security-confirm-password" placeholder="Confirm New Password" required>
                        </div>
                        <button type="submit" class="cta-button">Update Password</button>
                    </form>
                </div>
            </div>

            <!-- Inquiry Tab Content -->
            <div id="inquiry-tab" class="tab-content" style="display: none;">
                <div class="registration-container">
                    <h2>Send Us an Inquiry</h2>
                    <p style="color: #666; margin-bottom: 1.5rem;">Have a question or need assistance? Fill out the form below and we'll get back to you as soon as possible.</p>
                    <form id="inquiry-form" class="minimal-form" novalidate>
                        <input type="hidden" id="inquiry-csrf-token" name="csrf_token" value="">
                        <div class="hp-field" aria-hidden="true" style="position:absolute!important;left:-10000px!important;top:auto!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;pointer-events:none!important;">
                            <label for="inquiry-company-url">Company Website</label>
                            <input type="text" id="inquiry-company-url" name="company_url" value="" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="form-group-contact">
                            <label for="inquiry-subject">Subject</label>
                            <input type="text" id="inquiry-subject" placeholder="What is your inquiry about?" required maxlength="255">
                        </div>
                        <div class="form-group-contact">
                            <label for="inquiry-message">Message</label>
                            <textarea id="inquiry-message" rows="6" placeholder="Please provide details about your inquiry..." required maxlength="5000" style="
                                width: 100%;
                                padding: 0.75rem;
                                border: 1px solid #ddd;
                                border-radius: 4px;
                                font-family: inherit;
                                font-size: 1rem;
                                resize: vertical;
                            "></textarea>
                        </div>
                        
                        <button type="submit" class="cta-button">Send Inquiry</button>
                    </form>
                </div>
            </div>

                </section>
            </div>

        </div>
    </main>

    <?php
    $footerConfig = ['variant' => 'minimal'];
    include __DIR__ . '/includes/site-footer.php';
?>
    
    <script src="api-config.js?v=<?php echo filemtime(__DIR__ . '/api-config.js'); ?>"></script>
    <script src="booking-api.js?v=<?php echo filemtime(__DIR__ . '/booking-api.js'); ?>"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
    <script>
        // Check if API is loaded
        if (typeof API === 'undefined') {
            console.error('API configuration not loaded! Check if api-config.js is accessible.');
            document.addEventListener('DOMContentLoaded', () => {
                showDashboardError('API configuration failed to load. Please check your internet connection and refresh the page.');
            });
        }
        
        // Customer Dashboard Script
        let currentUser = null;
        let profileLocationInitialized = false;

        const PH_PROVINCE_POSTAL_CODES = {
            "Abra": "2800",
            "Agusan del Norte": "8600",
            "Agusan del Sur": "8500",
            "Aklan": "5600",
            "Albay": "4500",
            "Antique": "5700",
            "Apayao": "3800",
            "Aurora": "3200",
            "Basilan": "7300",
            "Bataan": "2100",
            "Batanes": "3900",
            "Batangas": "4200",
            "Benguet": "2600",
            "Biliran": "6549",
            "Bohol": "6300",
            "Bukidnon": "8700",
            "Bulacan": "3000",
            "Cagayan": "3500",
            "Camarines Norte": "4600",
            "Camarines Sur": "4400",
            "Camiguin": "9100",
            "Capiz": "5800",
            "Catanduanes": "4800",
            "Cavite": "4100",
            "Cebu": "6000",
            "Cotabato": "9400",
            "Davao de Oro": "8800",
            "Davao del Norte": "8100",
            "Davao del Sur": "8000",
            "Davao Occidental": "8012",
            "Davao Oriental": "8200",
            "Dinagat Islands": "8414",
            "Eastern Samar": "6800",
            "Guimaras": "5045",
            "Ifugao": "3600",
            "Ilocos Norte": "2900",
            "Ilocos Sur": "2700",
            "Iloilo": "5000",
            "Isabela": "3300",
            "Kalinga": "3800",
            "La Union": "2500",
            "Laguna": "4000",
            "Lanao del Norte": "9200",
            "Lanao del Sur": "9700",
            "Leyte": "6500",
            "Maguindanao del Norte": "9600",
            "Maguindanao del Sur": "9600",
            "Marinduque": "4900",
            "Masbate": "5400",
            "Metro Manila": "1000",
            "Misamis Occidental": "7200",
            "Misamis Oriental": "9000",
            "Mountain Province": "2619",
            "Negros Occidental": "6100",
            "Negros Oriental": "6200",
            "Northern Samar": "6400",
            "Nueva Ecija": "3100",
            "Nueva Vizcaya": "3700",
            "Occidental Mindoro": "5100",
            "Oriental Mindoro": "5200",
            "Palawan": "5300",
            "Pampanga": "2000",
            "Pangasinan": "2400",
            "Quezon": "4300",
            "Quirino": "3400",
            "Rizal": "1900",
            "Romblon": "5500",
            "Samar": "6700",
            "Sarangani": "9500",
            "Siquijor": "6225",
            "Sorsogon": "4700",
            "South Cotabato": "9500",
            "Southern Leyte": "6600",
            "Sultan Kudarat": "9800",
            "Sulu": "7400",
            "Surigao del Norte": "8400",
            "Surigao del Sur": "8300",
            "Tarlac": "2300",
            "Tawi-Tawi": "7500",
            "Zambales": "2200",
            "Zamboanga del Norte": "7100",
            "Zamboanga del Sur": "7000",
            "Zamboanga Sibugay": "7001"
        };
        
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                // Check if user is logged in
                const userStr = localStorage.getItem('user');
                if (!userStr) {
                    // Show message and redirect after a delay
                    showMessage('Please log in to access your dashboard. Redirecting...', 'error');
                    showDashboardError('You need to be logged in to view your dashboard. Redirecting to registration page...');
                    setTimeout(() => {
                        window.location.href = 'registration.php';
                    }, 3000);
                    return;
                }
                
                try {
                    currentUser = JSON.parse(userStr);
                } catch (parseError) {
                    console.error('Error parsing user data:', parseError);
                    localStorage.removeItem('user');
                    showMessage('Invalid session data. Please log in again.', 'error');
                    setTimeout(() => {
                        window.location.href = 'registration.php';
                    }, 2000);
                    return;
                }
                
                // Set user name display
                const userNameDisplay = document.getElementById('user-name-display');
                if (userNameDisplay) {
                    userNameDisplay.textContent = currentUser.name || 'User';
                }
                
                await initializeProfileLocationFields();

                // Verify session first, then load profile
                verifySessionAndLoadProfile();
                
                // Load bookings (don't block if it fails)
                loadBookings().catch(err => {
                    console.error('Bookings load error:', err);
                });

                setupInvoiceBackButton();
                
                // Setup tab switching
                setupTabs();
                
                // Setup forms
                setupProfileForm();
                setupSecurityForm();
                setupInquiryForm();
            } catch (error) {
                console.error('Dashboard initialization error:', error);
                showMessage('An error occurred loading the dashboard. Please refresh the page.', 'error');
                showDashboardError('An error occurred: ' + (error.message || 'Unknown error'));
            }
        });
        
        // Setup tab switching
        function setupTabs() {
            const tabButtons = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');

            function activateTab(targetTab) {
                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => {
                    btn.classList.remove('active');
                });
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    content.style.display = 'none';
                });

                // Add active class to matching nav link and corresponding content
                const activeButton = document.querySelector(`.tab-link[data-tab="${targetTab}"]`);
                if (activeButton) {
                    activeButton.classList.add('active');
                }

                const targetContent = document.getElementById(targetTab + '-tab');
                if (targetContent) {
                    targetContent.classList.add('active');
                    targetContent.style.display = 'block';

                    if (targetTab === 'profile') {
                        verifySessionAndLoadProfile().catch(err => {
                            console.error('Profile load error on tab click:', err);
                        });
                    }
                    if (targetTab === 'invoices') {
                        const invoiceId = new URLSearchParams(window.location.search).get('id');
                        if (invoiceId) {
                            loadInvoiceDetail(invoiceId).catch(err => {
                                console.error('Invoice detail load error:', err);
                            });
                        } else {
                            loadInvoices().catch(err => {
                                console.error('Invoices load error:', err);
                            });
                        }
                    }
                }
            }

            // Activate initial tab from URL
            const params = new URLSearchParams(window.location.search);
            const initialTab = params.get('tab');
            if (initialTab && ['bookings', 'invoices', 'profile', 'security', 'inquiry'].includes(initialTab)) {
                activateTab(initialTab);
            } else {
                activateTab('bookings');
            }
            
            tabButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetTab = button.getAttribute('data-tab');
                    const updatedUrl = new URL(window.location.href);
                    updatedUrl.searchParams.set('tab', targetTab);
                    if (targetTab !== 'invoices') {
                        updatedUrl.searchParams.delete('id');
                    }
                    window.history.replaceState({}, '', updatedUrl.toString());
                    activateTab(targetTab);
                });
            });
        }

        function getLocationFieldValue(fieldName) {
            const selectEl = document.getElementById(`profile-${fieldName}-select`);
            const manualEl = document.getElementById(`profile-${fieldName}-manual`);

            if (manualEl && manualEl.style.display !== 'none') {
                return manualEl.value.trim();
            }
            if (selectEl) {
                return (selectEl.value || '').trim();
            }

            const directEl = document.getElementById(`profile-${fieldName}`);
            return directEl ? directEl.value.trim() : '';
        }

        function showManualLocationInput(fieldName, value = '', placeholder = '') {
            const selectEl = document.getElementById(`profile-${fieldName}-select`);
            const manualEl = document.getElementById(`profile-${fieldName}-manual`);
            if (!manualEl) return;

            if (selectEl) {
                selectEl.style.display = 'none';
            }
            manualEl.style.display = 'block';
            if (placeholder) {
                manualEl.placeholder = placeholder;
            }
            manualEl.value = value || '';
        }

        function showSelectLocationInput(fieldName, placeholder = '') {
            const selectEl = document.getElementById(`profile-${fieldName}-select`);
            const manualEl = document.getElementById(`profile-${fieldName}-manual`);
            if (!selectEl) return;

            selectEl.style.display = 'block';
            if (manualEl) {
                manualEl.style.display = 'none';
                manualEl.value = '';
            }

            if (placeholder && selectEl.options.length > 0) {
                selectEl.options[0].textContent = placeholder;
            }
        }

        function setSelectOptions(selectEl, items, placeholderText) {
            if (!selectEl) return;

            const selectedBefore = selectEl.value;
            selectEl.innerHTML = '';

            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = placeholderText;
            selectEl.appendChild(placeholderOption);

            items.forEach(item => {
                const option = document.createElement('option');
                if (typeof item === 'string') {
                    option.value = item;
                    option.textContent = item;
                } else {
                    option.value = item.value;
                    option.textContent = item.label;
                    if (item.code) {
                        option.dataset.code = item.code;
                    }
                }
                selectEl.appendChild(option);
            });

            if (selectedBefore && Array.from(selectEl.options).some(opt => opt.value === selectedBefore)) {
                selectEl.value = selectedBefore;
            }
        }

        async function fetchJsonWithTimeout(url, options = {}, timeoutMs = 10000) {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), timeoutMs);

            try {
                const response = await fetch(url, {
                    ...options,
                    signal: controller.signal
                });
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return await response.json();
            } finally {
                clearTimeout(timeout);
            }
        }

        async function loadCountryOptions() {
            const countryEl = document.getElementById('profile-country');
            if (!countryEl) return;

            const existingValue = countryEl.value || 'Philippines';
            try {
                const countries = await fetchJsonWithTimeout('https://restcountries.com/v3.1/all?fields=name');
                const countryNames = countries
                    .map(c => c && c.name && c.name.common ? c.name.common : null)
                    .filter(Boolean)
                    .sort((a, b) => a.localeCompare(b));

                setSelectOptions(countryEl, countryNames, 'Select Country');
            } catch (error) {
                console.warn('Unable to load full country list, using fallback list:', error);
                const fallbackCountries = [
                    'Philippines', 'United States', 'Canada', 'Australia', 'United Kingdom',
                    'Japan', 'South Korea', 'Singapore', 'Malaysia', 'Thailand',
                    'Indonesia', 'India', 'France', 'Germany', 'Italy', 'Spain',
                    'United Arab Emirates', 'Saudi Arabia', 'Qatar'
                ];
                setSelectOptions(countryEl, fallbackCountries, 'Select Country');
            }

            countryEl.value = Array.from(countryEl.options).some(opt => opt.value === existingValue)
                ? existingValue
                : 'Philippines';
        }

        function autoFillPostalCodeFromProvince(provinceValue, keepExisting = true) {
            const postalCodeEl = document.getElementById('profile-postal-code');
            if (!postalCodeEl) return;

            if (keepExisting && postalCodeEl.value.trim()) {
                return;
            }

            const postalCode = PH_PROVINCE_POSTAL_CODES[provinceValue] || '';
            if (postalCode) {
                postalCodeEl.value = postalCode;
            }
        }

        async function loadProvinceOptionsByCountry(countryName) {
            if (!countryName) return [];

            if (countryName === 'Philippines') {
                const provinces = await fetchJsonWithTimeout('https://psgc.gitlab.io/api/provinces/');
                return (Array.isArray(provinces) ? provinces : [])
                    .map(p => ({ label: p.name, value: p.name, code: p.code }))
                    .sort((a, b) => a.label.localeCompare(b.label));
            }

            const response = await fetchJsonWithTimeout('https://countriesnow.space/api/v0.1/countries/states', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ country: countryName })
            });

            const states = response && response.data && Array.isArray(response.data.states)
                ? response.data.states
                : [];

            return states
                .map(s => ({ label: s.name, value: s.name }))
                .sort((a, b) => a.label.localeCompare(b.label));
        }

        async function loadCitiesByCountryAndProvince(countryName, provinceOption) {
            if (!countryName || !provinceOption) return [];

            if (countryName === 'Philippines') {
                const provinceCode = provinceOption.dataset && provinceOption.dataset.code
                    ? provinceOption.dataset.code
                    : '';
                if (!provinceCode) return [];

                const cities = await fetchJsonWithTimeout(`https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`);
                return (Array.isArray(cities) ? cities : [])
                    .map(c => ({ label: c.name, value: c.name, code: c.code }))
                    .sort((a, b) => a.label.localeCompare(b.label));
            }

            const response = await fetchJsonWithTimeout('https://countriesnow.space/api/v0.1/countries/state/cities', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ country: countryName, state: provinceOption.value })
            });

            const cities = response && response.data && Array.isArray(response.data)
                ? response.data
                : [];

            return cities
                .map(name => ({ label: name, value: name }))
                .sort((a, b) => a.label.localeCompare(b.label));
        }

        async function loadBarangaysByCity(countryName, cityOption) {
            if (countryName !== 'Philippines') {
                return [];
            }

            const cityCode = cityOption && cityOption.dataset && cityOption.dataset.code
                ? cityOption.dataset.code
                : '';
            if (!cityCode) return [];

            const barangays = await fetchJsonWithTimeout(`https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`);
            return (Array.isArray(barangays) ? barangays : [])
                .map(b => ({ label: b.name, value: b.name }))
                .sort((a, b) => a.label.localeCompare(b.label));
        }

        async function onCountryChange(preferredProvince = '', preferredCity = '', preferredBarangay = '') {
            const countryEl = document.getElementById('profile-country');
            const provinceSelect = document.getElementById('profile-province-select');
            const citySelect = document.getElementById('profile-city-select');
            const barangaySelect = document.getElementById('profile-barangay-select');
            if (!countryEl || !provinceSelect || !citySelect || !barangaySelect) return;

            const country = countryEl.value.trim();
            setSelectOptions(citySelect, [], 'Select Town/City');
            setSelectOptions(barangaySelect, [], 'Select Barangay');

            try {
                const provinces = await loadProvinceOptionsByCountry(country);
                if (provinces.length > 0) {
                    showSelectLocationInput('province', 'Select Province');
                    setSelectOptions(provinceSelect, provinces, 'Select Province');
                    const hasPreferred = preferredProvince && provinces.some(p => p.value === preferredProvince);
                    if (hasPreferred) {
                        provinceSelect.value = preferredProvince;
                        await onProvinceChange(preferredCity, preferredBarangay);
                    } else {
                        provinceSelect.value = '';
                        showSelectLocationInput('city', 'Select Town/City');
                        showSelectLocationInput('barangay', 'Select Barangay');
                        setSelectOptions(citySelect, [], 'Select Town/City');
                        setSelectOptions(barangaySelect, [], 'Select Barangay');
                    }
                } else {
                    showManualLocationInput('province', preferredProvince, 'Province');
                    showManualLocationInput('city', preferredCity, 'Town/City');
                    showManualLocationInput('barangay', preferredBarangay, 'Barangay');
                }
            } catch (error) {
                console.warn('Failed to load provinces for country:', country, error);
                showManualLocationInput('province', preferredProvince, 'Province');
                showManualLocationInput('city', preferredCity, 'Town/City');
                showManualLocationInput('barangay', preferredBarangay, 'Barangay');
            }
        }

        async function onProvinceChange(preferredCity = '', preferredBarangay = '') {
            const countryEl = document.getElementById('profile-country');
            const provinceSelect = document.getElementById('profile-province-select');
            const citySelect = document.getElementById('profile-city-select');
            const barangaySelect = document.getElementById('profile-barangay-select');
            if (!countryEl || !citySelect || !barangaySelect) return;

            const country = countryEl.value.trim();
            const provinceOption = provinceSelect && provinceSelect.style.display !== 'none'
                ? provinceSelect.options[provinceSelect.selectedIndex]
                : null;

            setSelectOptions(barangaySelect, [], 'Select Barangay');

            if (provinceOption && provinceOption.value) {
                autoFillPostalCodeFromProvince(provinceOption.value, false);
            }

            try {
                const cities = await loadCitiesByCountryAndProvince(country, provinceOption);
                if (cities.length > 0) {
                    showSelectLocationInput('city', 'Select Town/City');
                    setSelectOptions(citySelect, cities, 'Select Town/City');
                    const hasPreferred = preferredCity && cities.some(c => c.value === preferredCity);
                    if (hasPreferred) {
                        citySelect.value = preferredCity;
                        await onCityChange(preferredBarangay);
                    } else {
                        citySelect.value = '';
                        showManualLocationInput('barangay', '', 'Barangay');
                    }
                } else {
                    showManualLocationInput('city', preferredCity, 'Town/City');
                    showManualLocationInput('barangay', preferredBarangay, 'Barangay');
                }
            } catch (error) {
                console.warn('Failed to load cities:', error);
                showManualLocationInput('city', preferredCity, 'Town/City');
                showManualLocationInput('barangay', preferredBarangay, 'Barangay');
            }
        }

        async function onCityChange(preferredBarangay = '') {
            const countryEl = document.getElementById('profile-country');
            const citySelect = document.getElementById('profile-city-select');
            const barangaySelect = document.getElementById('profile-barangay-select');
            if (!countryEl || !citySelect || !barangaySelect) return;

            const country = countryEl.value.trim();
            const cityOption = citySelect.style.display !== 'none'
                ? citySelect.options[citySelect.selectedIndex]
                : null;

            try {
                const barangays = await loadBarangaysByCity(country, cityOption);
                if (barangays.length > 0) {
                    showSelectLocationInput('barangay', 'Select Barangay');
                    setSelectOptions(barangaySelect, barangays, 'Select Barangay');
                    if (preferredBarangay && barangays.some(b => b.value === preferredBarangay)) {
                        barangaySelect.value = preferredBarangay;
                    }
                } else {
                    showManualLocationInput('barangay', preferredBarangay, 'Barangay');
                }
            } catch (error) {
                console.warn('Failed to load barangays:', error);
                showManualLocationInput('barangay', preferredBarangay, 'Barangay');
            }
        }

        async function initializeProfileLocationFields() {
            if (profileLocationInitialized) return;

            const countryEl = document.getElementById('profile-country');
            const provinceSelect = document.getElementById('profile-province-select');
            const citySelect = document.getElementById('profile-city-select');
            if (!countryEl || !provinceSelect || !citySelect) return;

            profileLocationInitialized = true;
            await loadCountryOptions();

            countryEl.addEventListener('change', () => {
                onCountryChange();
            });

            provinceSelect.addEventListener('change', () => {
                onProvinceChange();
            });

            citySelect.addEventListener('change', () => {
                onCityChange();
            });

            await onCountryChange('');
        }

        async function applyProfileLocationFromUser(user) {
            const countryEl = document.getElementById('profile-country');
            const provinceSelect = document.getElementById('profile-province-select');
            const citySelect = document.getElementById('profile-city-select');
            const postalCodeEl = document.getElementById('profile-postal-code');
            if (!countryEl || !provinceSelect || !citySelect || !postalCodeEl) return;

            const country = user.country || 'Philippines';
            if (!Array.from(countryEl.options).some(opt => opt.value === country)) {
                const option = document.createElement('option');
                option.value = country;
                option.textContent = country;
                countryEl.appendChild(option);
            }
            countryEl.value = country;

            await onCountryChange(user.province || '', user.city || '', user.barangay || '');

            // If saved value wasn't available in dropdown data, preserve with manual entry.
            if ((user.province || '') && provinceSelect.style.display !== 'none' && !provinceSelect.value) {
                showManualLocationInput('province', user.province, 'Province');
            }
            if ((user.city || '') && citySelect.style.display !== 'none' && !citySelect.value) {
                showManualLocationInput('city', user.city, 'Town/City');
            }

            postalCodeEl.value = user.postal_code || postalCodeEl.value || '';
        }
        
        // Verify session and load profile
        async function verifySessionAndLoadProfile() {
            try {
                // First verify the session is still valid
                const sessionCheck = await API.auth.check();
                console.log('Session check result:', sessionCheck);
                
                if (!sessionCheck.success || !sessionCheck.logged_in) {
                    console.warn('Session is not valid, logging out account');
                    showMessage('Your session has expired. Please log in again.', 'error');
                    if (typeof API !== 'undefined' && API.auth && typeof API.auth.forceLogout === 'function') {
                        await API.auth.forceLogout('login.php');
                    } else {
                        localStorage.removeItem('user');
                        window.location.href = 'login.php';
                    }
                    return;
                }
                
                // Session is valid, load profile
                await loadUserProfile();
            } catch (error) {
                console.error('Session verification error:', error);
                // Still try to load profile in case it works
                loadUserProfile().catch(err => {
                    console.error('Profile load error:', err);
                });
            }
        }
        
        // Load user profile
        async function loadUserProfile() {
            const firstNameEl = document.getElementById('profile-first-name');
            const lastNameEl = document.getElementById('profile-last-name');
            const emailEl = document.getElementById('profile-email');
            const phoneEl = document.getElementById('profile-phone');
            const addressEl = document.getElementById('profile-address');
            const postalCodeEl = document.getElementById('profile-postal-code');
            const dateOfBirthEl = document.getElementById('profile-date-of-birth');
            const genderEl = document.getElementById('profile-gender');
            const nationalityEl = document.getElementById('profile-nationality');
            const idTypeEl = document.getElementById('profile-id-type');
            const idNumberEl = document.getElementById('profile-id-number');
            
            // Show loading state
            const profileTab = document.getElementById('profile-tab');
            let loadingMsg = profileTab.querySelector('.profile-loading-message');
            if (!loadingMsg && profileTab.style.display !== 'none') {
                loadingMsg = document.createElement('div');
                loadingMsg.className = 'profile-loading-message';
                loadingMsg.style.cssText = 'text-align: center; padding: 1rem; color: #666; margin-bottom: 1rem;';
                loadingMsg.textContent = 'Loading your profile...';
                const form = document.getElementById('profile-form');
                if (form && form.parentNode) {
                    form.parentNode.insertBefore(loadingMsg, form);
                }
            }
            
            try {
                console.log('Loading user profile...');
                const response = await API.user.getProfile();
                console.log('Profile API response:', response);
                
                if (response.success && response.user) {
                    const user = response.user;
                    console.log('Setting profile data:', user);
                    
                    if (firstNameEl) firstNameEl.value = user.first_name || '';
                    if (lastNameEl) lastNameEl.value = user.last_name || '';
                    if (emailEl) emailEl.value = user.email || '';
                    if (phoneEl) phoneEl.value = user.phone || '';
                    if (addressEl) addressEl.value = user.address || '';
                    if (postalCodeEl) postalCodeEl.value = user.postal_code || '';
                    if (dateOfBirthEl) dateOfBirthEl.value = user.date_of_birth || '';
                    if (genderEl) genderEl.value = user.gender || '';
                    if (nationalityEl) nationalityEl.value = user.nationality || '';
                    if (idTypeEl) idTypeEl.value = user.id_type || '';
                    if (idNumberEl) idNumberEl.value = user.id_number || '';
                    await applyProfileLocationFromUser(user);
                    
                    // Remove loading message
                    if (loadingMsg) loadingMsg.remove();
                    
                    console.log('Profile loaded successfully');
                } else {
                    console.warn('API profile load failed - response:', response);
                    // If API fails, try to get from localStorage
                    if (currentUser) {
                        console.warn('API profile load failed, trying cached data');
                        // Try to populate from currentUser if available
                        if (firstNameEl && currentUser.first_name) firstNameEl.value = currentUser.first_name;
                        if (lastNameEl && currentUser.last_name) lastNameEl.value = currentUser.last_name;
                        if (emailEl && currentUser.email) emailEl.value = currentUser.email;
                    }
                    
                    // Remove loading message
                    if (loadingMsg) loadingMsg.remove();
                    
                    // Show error message in profile tab
                    if (profileTab.style.display !== 'none') {
                        showMessage('Unable to load your profile information. Please try refreshing the page or contact support if the problem persists.', 'error');
                    }
                }
            } catch (error) {
                console.error('Error loading profile:', error);
                console.error('Error details:', {
                    message: error.message,
                    status: error.status,
                    response: error.response
                });
                
                // Remove loading message
                if (loadingMsg) loadingMsg.remove();
                
                // Session missing/expired — logout the account
                if (error.status === 401 || (error.message && (error.message.includes('log in') || error.message.includes('session')))) {
                    console.warn('Session expired. Logging out account.');
                    showMessage('Your session has expired. Please log in again.', 'error');
                    if (typeof API !== 'undefined' && API.auth && typeof API.auth.forceLogout === 'function') {
                        await API.auth.forceLogout('login.php');
                    } else {
                        localStorage.removeItem('user');
                        window.location.href = 'login.php';
                    }
                    return;
                } else {
                    // Try to populate from localStorage as fallback
                    if (currentUser) {
                        console.log('Using cached user data as fallback');
                        // Try to split name if we only have full name
                        if (currentUser.name && !currentUser.first_name) {
                            const nameParts = currentUser.name.split(' ');
                            if (nameParts.length >= 2) {
                                if (firstNameEl) firstNameEl.value = nameParts[0];
                                if (lastNameEl) lastNameEl.value = nameParts.slice(1).join(' ');
                            }
                        } else {
                            if (firstNameEl && currentUser.first_name) firstNameEl.value = currentUser.first_name;
                            if (lastNameEl && currentUser.last_name) lastNameEl.value = currentUser.last_name;
                        }
                        if (emailEl && currentUser.email) emailEl.value = currentUser.email;
                    }
                    
                    if (profileTab.style.display !== 'none') {
                        const errorMsg = error.response && error.response.message 
                            ? error.response.message 
                            : 'Unable to load your profile information. You can still update your profile manually.';
                        showMessage(errorMsg, 'error');
                    }
                }
            }
        }
        
        // Setup profile form
        function setupProfileForm() {
            const form = document.getElementById('profile-form');
            if (!form) return;
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Updating...';
                
                const profileData = {
                    first_name: document.getElementById('profile-first-name').value.trim(),
                    last_name: document.getElementById('profile-last-name').value.trim(),
                    email: document.getElementById('profile-email').value.trim(),
                    phone: document.getElementById('profile-phone').value.trim(),
                    address: document.getElementById('profile-address').value.trim(),
                    city: getLocationFieldValue('city'),
                    province: getLocationFieldValue('province'),
                    barangay: getLocationFieldValue('barangay'),
                    postal_code: document.getElementById('profile-postal-code')?.value.trim() || '',
                    country: document.getElementById('profile-country')?.value.trim() || 'Philippines',
                    date_of_birth: document.getElementById('profile-date-of-birth')?.value || '',
                    gender: document.getElementById('profile-gender')?.value || '',
                    nationality: document.getElementById('profile-nationality')?.value.trim() || '',
                    id_type: document.getElementById('profile-id-type')?.value || '',
                    id_number: document.getElementById('profile-id-number')?.value.trim() || ''
                };
                
                try {
                    const response = await API.user.updateProfile(profileData);
                    if (response.success) {
                        showMessage('Your profile has been updated successfully!', 'success');
                        
                        // Update user in localStorage
                        if (response.user) {
                            localStorage.setItem('user', JSON.stringify(response.user));
                            currentUser = response.user;
                            document.getElementById('user-name-display').textContent = response.user.name || currentUser.name;
                        }
                    }
                } catch (error) {
                    showMessage(error.message || 'Failed to update profile. Please try again.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        }

        // Setup security form
        function setupSecurityForm() {
            const form = document.getElementById('security-form');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Updating...';

                const currentPassword = document.getElementById('security-current-password').value;
                const newPassword = document.getElementById('security-new-password').value;
                const confirmPassword = document.getElementById('security-confirm-password').value;

                if (!currentPassword || !newPassword || !confirmPassword) {
                    showMessage('Please fill in all password fields.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    return;
                }

                if (newPassword !== confirmPassword) {
                    showMessage('New password and confirmation do not match.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    return;
                }

                if (newPassword.length < 6) {
                    showMessage('New password must be at least 6 characters long.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    return;
                }

                try {
                    const response = await API.user.changePassword({
                        current_password: currentPassword,
                        new_password: newPassword,
                        confirm_password: confirmPassword
                    });

                    if (response.success) {
                        showMessage('Your password has been updated successfully.', 'success');
                        form.reset();
                    }
                } catch (error) {
                    showMessage(error.message || 'Failed to update password. Please try again.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        }
        
        // Setup inquiry form (CSRF + honeypot + optional reCAPTCHA)
        function setupInquiryForm() {
            const form = document.getElementById('inquiry-form');
            if (!form) return;

            const csrfInput = document.getElementById('inquiry-csrf-token');
            const honeypotInput = document.getElementById('inquiry-company-url');
            let security = {
                csrf_token: '',
                honeypot_field: 'company_url',
                recaptcha_enabled: false,
                recaptcha_site_key: '',
                recaptcha_action: 'contact_submit',
                ready: false
            };

            function loadRecaptcha(siteKey) {
                return new Promise((resolve, reject) => {
                    if (window.grecaptcha && window.grecaptcha.execute) {
                        resolve();
                        return;
                    }
                    const existing = document.querySelector('script[data-recaptcha-v3]');
                    if (existing) {
                        existing.addEventListener('load', () => resolve());
                        existing.addEventListener('error', () => reject(new Error('Failed to load CAPTCHA.')));
                        return;
                    }
                    const s = document.createElement('script');
                    s.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(siteKey);
                    s.async = true;
                    s.defer = true;
                    s.setAttribute('data-recaptcha-v3', '1');
                    s.onload = () => resolve();
                    s.onerror = () => reject(new Error('Failed to load CAPTCHA.'));
                    document.head.appendChild(s);
                });
            }

            async function getRecaptchaToken() {
                if (!security.recaptcha_enabled || !security.recaptcha_site_key) return '';
                await loadRecaptcha(security.recaptcha_site_key);
                return await new Promise((resolve, reject) => {
                    window.grecaptcha.ready(() => {
                        window.grecaptcha.execute(security.recaptcha_site_key, {
                            action: security.recaptcha_action || 'contact_submit'
                        }).then(resolve).catch(reject);
                    });
                });
            }

            async function refreshSecurity() {
                const result = await API.inquiry.csrf();
                if (!result || !result.success || !result.csrf_token) {
                    throw new Error('Could not initialize form security. Please refresh.');
                }
                security = {
                    csrf_token: result.csrf_token,
                    honeypot_field: result.honeypot_field || 'company_url',
                    recaptcha_enabled: !!result.recaptcha_enabled,
                    recaptcha_site_key: result.recaptcha_site_key || '',
                    recaptcha_action: result.recaptcha_action || 'contact_submit',
                    ready: true
                };
                if (csrfInput) csrfInput.value = security.csrf_token;
                if (security.recaptcha_enabled) {
                    try { await loadRecaptcha(security.recaptcha_site_key); } catch (e) { /* loaded on submit */ }
                }
            }

            refreshSecurity().catch((err) => {
                console.error(err);
            });
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';

                if (honeypotInput && honeypotInput.value) {
                    showMessage('Your inquiry has been sent successfully! We will get back to you soon.', 'success');
                    form.reset();
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    return;
                }
                
                try {
                    if (!security.ready || !security.csrf_token) {
                        await refreshSecurity();
                    }
                    const inquiryData = {
                        subject: document.getElementById('inquiry-subject').value.trim(),
                        message: document.getElementById('inquiry-message').value.trim(),
                        csrf_token: security.csrf_token,
                        recaptcha_token: await getRecaptchaToken()
                    };
                    inquiryData[security.honeypot_field || 'company_url'] = honeypotInput ? honeypotInput.value : '';

                    const response = await API.inquiry.submit(inquiryData);
                    if (response.success) {
                        showMessage('Your inquiry has been sent successfully! We will get back to you soon.', 'success');
                        form.reset();
                    }
                } catch (error) {
                    showMessage(error.message || 'Failed to send inquiry. Please try again.', 'error');
                } finally {
                    try { await refreshSecurity(); } catch (e) { /* ignore */ }
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        }
        
        // Load invoices
        let invoicesLoaded = false;

        function setupInvoiceBackButton() {
            const backBtn = document.getElementById('invoice-back-btn');
            if (!backBtn) return;
            backBtn.addEventListener('click', () => {
                const detailPanel = document.getElementById('invoice-detail-panel');
                const listContainer = document.getElementById('invoices-list-container');
                if (detailPanel) detailPanel.style.display = 'none';
                if (listContainer) listContainer.style.display = 'block';
                const url = new URL(window.location.href);
                url.searchParams.delete('id');
                url.searchParams.set('tab', 'invoices');
                window.history.replaceState({}, '', url.toString());
                if (!invoicesLoaded) {
                    loadInvoices().catch(err => console.error(err));
                }
            });
        }

        async function loadInvoices() {
            const listContainer = document.getElementById('invoices-list-container');
            const detailPanel = document.getElementById('invoice-detail-panel');
            if (!listContainer) return;

            if (detailPanel) detailPanel.style.display = 'none';
            listContainer.style.display = 'block';
            listContainer.innerHTML = '<div class="loading-message" style="text-align: center; padding: 2rem; color: #666;"><p>Loading your invoices...</p></div>';

            try {
                const response = await API.invoice.getMyInvoices();
                if (!response.success) {
                    throw new Error(response.message || 'Failed to load invoices');
                }

                const invoices = response.invoices || [];
                invoicesLoaded = true;

                if (invoices.length === 0) {
                    listContainer.innerHTML = `
                        <div style="text-align: center; padding: 3rem;">
                            <h3 style="color: #666;">No invoices yet</h3>
                            <p style="color: #999;">Invoices for your bookings will appear here once issued by the hotel.</p>
                            <a href="rooms.php" class="cta-button" style="display: inline-block; margin-top: 1rem;">Browse Rooms</a>
                        </div>`;
                    return;
                }

                listContainer.innerHTML = invoices.map(inv => {
                    const statusClass = getInvoiceStatusClass(inv.status);
                    return `
                    <div class="invoice-card" style="border:1px solid #ddd;border-radius:8px;padding:1.5rem;margin-bottom:1rem;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,0.06);">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
                            <div>
                                <h3 style="margin:0 0 0.25rem;color:#333;">${escapeHtml(inv.invoice_number)}</h3>
                                <p style="margin:0;color:#666;font-size:0.9rem;">Issued: ${formatInvoiceDate(inv.issued_at || inv.created_at)}</p>
                                ${inv.booking_number ? `<p style="margin:0.25rem 0 0;color:#888;font-size:0.85rem;">Booking: ${escapeHtml(inv.booking_number)}</p>` : ''}
                            </div>
                            <span class="status-badge ${statusClass}" style="padding:0.4rem 0.9rem;border-radius:20px;font-size:0.8rem;font-weight:600;text-transform:uppercase;">${escapeHtml(inv.status)}</span>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:1rem;">
                            <div><strong style="color:#666;display:block;font-size:0.85rem;">Total</strong>₱${formatInvoiceMoney(inv.total_amount)}</div>
                            <div><strong style="color:#666;display:block;font-size:0.85rem;">Paid</strong><span style="color:#059669;">₱${formatInvoiceMoney(inv.amount_paid)}</span></div>
                            <div><strong style="color:#666;display:block;font-size:0.85rem;">Balance</strong><span style="color:#c62828;font-weight:600;">₱${formatInvoiceMoney(inv.balance_due)}</span></div>
                            <div><strong style="color:#666;display:block;font-size:0.85rem;">Due Date</strong>${inv.due_date ? formatInvoiceDate(inv.due_date) : '—'}</div>
                        </div>
                        <button type="button" class="cta-button view-invoice-btn" data-id="${inv.id}" style="padding:0.5rem 1.25rem;font-size:0.9rem;">View Details</button>
                    </div>`;
                }).join('');

                listContainer.querySelectorAll('.view-invoice-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.getAttribute('data-id');
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', 'invoices');
                        url.searchParams.set('id', id);
                        window.history.replaceState({}, '', url.toString());
                        loadInvoiceDetail(id);
                    });
                });
            } catch (error) {
                listContainer.innerHTML = `<div style="text-align:center;padding:3rem;color:#c62828;"><h3>Unable to load invoices</h3><p>${escapeHtml(error.message || 'Please try again later.')}</p></div>`;
            }
        }

        async function loadInvoiceDetail(id) {
            const listContainer = document.getElementById('invoices-list-container');
            const detailPanel = document.getElementById('invoice-detail-panel');
            const detailContent = document.getElementById('invoice-detail-content');
            const detailTitle = document.getElementById('invoice-detail-title');
            if (!detailPanel || !detailContent) return;

            if (listContainer) listContainer.style.display = 'none';
            detailPanel.style.display = 'block';
            detailContent.innerHTML = '<p style="color:#666;">Loading invoice...</p>';

            try {
                const response = await API.invoice.getInvoice(id);
                if (!response.success) {
                    throw new Error(response.message || 'Invoice not found');
                }

                const inv = response.invoice;
                const items = response.items || [];
                const payments = response.payments || [];
                const onlinePayment = response.online_payment || null;

                if (detailTitle) detailTitle.textContent = 'Invoice ' + inv.invoice_number;

                const itemsHtml = items.map(item => `
                    <tr>
                        <td style="padding:0.75rem;border-bottom:1px solid #eee;">${escapeHtml(item.description)}</td>
                        <td style="padding:0.75rem;border-bottom:1px solid #eee;text-align:right;">${item.quantity}</td>
                        <td style="padding:0.75rem;border-bottom:1px solid #eee;text-align:right;">₱${formatInvoiceMoney(item.unit_price)}</td>
                        <td style="padding:0.75rem;border-bottom:1px solid #eee;text-align:right;">₱${formatInvoiceMoney(item.total_price)}</td>
                    </tr>
                `).join('');

                const paymentsHtml = payments.length > 0
                    ? payments.map(p => `
                        <tr>
                            <td style="padding:0.5rem 0;">${p.payment_date ? formatInvoiceDate(p.payment_date) : '—'}</td>
                            <td style="padding:0.5rem 0;">${escapeHtml(p.payment_method)}</td>
                            <td style="padding:0.5rem 0;text-align:right;">₱${formatInvoiceMoney(p.amount)}</td>
                        </tr>`).join('')
                    : '<tr><td colspan="3" style="padding:0.5rem 0;color:#999;">No payments recorded yet</td></tr>';

                let qrphHtml = '';
                const balanceDue = parseFloat(inv.balance_due || 0);
                if (balanceDue > 0 && onlinePayment && onlinePayment.method === 'qrph') {
                    const hasQr = !!onlinePayment.qr_image_url;
                    qrphHtml = `
                        <div id="invoice-qrph-panel" style="background:#e8f4fd;border:1px solid #b6d9f2;border-radius:8px;padding:1.25rem;margin-bottom:1.5rem;text-align:center;">
                            <h4 style="margin:0 0 0.5rem;color:#0c5460;">Pay with GCash / QR Ph</h4>
                            <p style="margin:0 0 1rem;color:#0c5460;font-size:0.95rem;">Scan this code in GCash or any QR Ph app. Booking stays pending until paid.</p>
                            ${hasQr
                                ? `<img id="invoice-qrph-image" src="${escapeHtml(onlinePayment.qr_image_url)}" alt="QR Ph payment" style="max-width:260px;width:100%;height:auto;border:1px solid #fff;border-radius:8px;background:#fff;">`
                                : `<p id="invoice-qrph-missing" style="color:#856404;margin:0 0 1rem;">QR code expired or unavailable.</p>`}
                            <p style="margin:0.75rem 0 0;font-weight:700;color:#b2945b;">Amount due: ₱${formatInvoiceMoney(onlinePayment.amount != null ? onlinePayment.amount : inv.balance_due)}</p>
                            ${onlinePayment.expires_at ? `<p style="margin:0.35rem 0 0;color:#666;font-size:0.85rem;">Expires: ${escapeHtml(onlinePayment.expires_at)}</p>` : ''}
                            <p id="invoice-qrph-poll" style="margin:0.75rem 0 0;color:#0c5460;font-size:0.9rem;">Waiting for payment…</p>
                            <button type="button" id="invoice-qrph-regenerate" class="cta-button" style="margin-top:1rem;background:#1a2238;border-color:#1a2238;color:#fff;">${hasQr ? 'Refresh / New QR' : 'Generate QR Code'}</button>
                        </div>`;
                }

                detailContent.innerHTML = `
                    <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:1.5rem;">
                        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
                            <div>
                                <p style="margin:0 0 0.25rem;"><strong>Bill To:</strong> ${escapeHtml(inv.guest_name)}</p>
                                ${inv.guest_email ? `<p style="margin:0;color:#666;">${escapeHtml(inv.guest_email)}</p>` : ''}
                            </div>
                            <div style="text-align:right;">
                                <p style="margin:0;"><strong>Status:</strong> <span id="invoice-detail-status">${escapeHtml(inv.status)}</span></p>
                                <p style="margin:0.25rem 0 0;color:#666;">Due: ${inv.due_date ? formatInvoiceDate(inv.due_date) : 'Upon receipt'}</p>
                            </div>
                        </div>
                        ${qrphHtml}
                        <div style="overflow-x:auto;margin-bottom:1.5rem;">
                            <table style="width:100%;border-collapse:collapse;font-size:0.95rem;">
                                <thead>
                                    <tr style="background:#f8f9fa;">
                                        <th style="padding:0.75rem;text-align:left;">Description</th>
                                        <th style="padding:0.75rem;text-align:right;">Qty</th>
                                        <th style="padding:0.75rem;text-align:right;">Unit Price</th>
                                        <th style="padding:0.75rem;text-align:right;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>${itemsHtml}</tbody>
                                <tfoot>
                                    <tr><td colspan="3" style="padding:0.5rem;text-align:right;">Subtotal</td><td style="padding:0.5rem;text-align:right;">₱${formatInvoiceMoney(inv.subtotal)}</td></tr>
                                    ${inv.discount_amount > 0 ? `<tr><td colspan="3" style="padding:0.5rem;text-align:right;">Discount</td><td style="padding:0.5rem;text-align:right;color:#c62828;">-₱${formatInvoiceMoney(inv.discount_amount)}</td></tr>` : ''}
                                    ${inv.tax_amount > 0 ? `<tr><td colspan="3" style="padding:0.5rem;text-align:right;">Tax</td><td style="padding:0.5rem;text-align:right;">₱${formatInvoiceMoney(inv.tax_amount)}</td></tr>` : ''}
                                    ${inv.service_charge_amount > 0 ? `<tr><td colspan="3" style="padding:0.5rem;text-align:right;">Service Charge</td><td style="padding:0.5rem;text-align:right;">₱${formatInvoiceMoney(inv.service_charge_amount)}</td></tr>` : ''}
                                    <tr><td colspan="3" style="padding:0.75rem;text-align:right;font-weight:bold;">Total</td><td style="padding:0.75rem;text-align:right;font-weight:bold;">₱${formatInvoiceMoney(inv.total_amount)}</td></tr>
                                    <tr><td colspan="3" style="padding:0.5rem;text-align:right;color:#059669;">Amount Paid</td><td id="invoice-amount-paid" style="padding:0.5rem;text-align:right;color:#059669;">₱${formatInvoiceMoney(inv.amount_paid)}</td></tr>
                                    <tr><td colspan="3" style="padding:0.75rem;text-align:right;font-weight:bold;color:#c62828;">Balance Due</td><td id="invoice-balance-due" style="padding:0.75rem;text-align:right;font-weight:bold;color:#c62828;">₱${formatInvoiceMoney(inv.balance_due)}</td></tr>
                                </tfoot>
                            </table>
                        </div>
                        <h4 style="color:#666;font-size:1rem;margin-bottom:0.75rem;">Payment History</h4>
                        <table style="width:100%;font-size:0.9rem;margin-bottom:1rem;">
                            <thead><tr style="color:#666;"><th style="text-align:left;padding-bottom:0.5rem;">Date</th><th style="text-align:left;padding-bottom:0.5rem;">Method</th><th style="text-align:right;padding-bottom:0.5rem;">Amount</th></tr></thead>
                            <tbody>${paymentsHtml}</tbody>
                        </table>
                        ${inv.notes ? `<p style="color:#666;font-size:0.9rem;margin:0;"><strong>Notes:</strong> ${escapeHtml(inv.notes)}</p>` : ''}
                        <p style="margin-top:1.5rem;font-size:0.85rem;color:#888;">For payment inquiries, please contact the front desk or reply to your invoice email.</p>
                    </div>`;

                if (qrphHtml && typeof API !== 'undefined' && API.payment) {
                    bindInvoiceQrphHandlers(inv, onlinePayment);
                }
            } catch (error) {
                detailContent.innerHTML = `<div style="color:#c62828;padding:2rem;text-align:center;"><p>${escapeHtml(error.message || 'Failed to load invoice')}</p></div>`;
            }
        }

        function bindInvoiceQrphHandlers(inv, onlinePayment) {
            const regenBtn = document.getElementById('invoice-qrph-regenerate');
            const pollEl = document.getElementById('invoice-qrph-poll');
            let intentId = onlinePayment && onlinePayment.payment_intent_id ? onlinePayment.payment_intent_id : '';
            let pollTimer = null;

            const stopPoll = () => { if (pollTimer) { clearInterval(pollTimer); pollTimer = null; } };

            const markInvoicePaid = async () => {
                stopPoll();
                if (pollEl) pollEl.textContent = 'Payment received! Refreshing invoice…';
                try {
                    await loadInvoiceDetail(inv.id);
                } catch (e) {
                    const statusEl = document.getElementById('invoice-detail-status');
                    if (statusEl) statusEl.textContent = 'paid';
                    const panel = document.getElementById('invoice-qrph-panel');
                    if (panel) {
                        panel.innerHTML = '<h4 style="margin:0;color:#155724;">Payment complete</h4><p style="margin:0.5rem 0 0;color:#155724;">This invoice is paid.</p>';
                        panel.style.background = '#d4edda';
                        panel.style.borderColor = '#c3e6cb';
                    }
                }
            };

            const poll = async () => {
                try {
                    const result = await API.payment.verify({
                        booking_number: onlinePayment.booking_number || '',
                        payment_intent_id: intentId
                    });
                    if (result && result.paid) {
                        await markInvoicePaid();
                    } else if (pollEl) {
                        pollEl.textContent = 'Waiting for payment… ' + (result.intent_status || inv.status || '');
                    }
                } catch (e) {
                    console.error(e);
                }
            };

            if (intentId || (onlinePayment && onlinePayment.booking_number)) {
                poll();
                pollTimer = setInterval(poll, 5000);
            }

            if (regenBtn) {
                regenBtn.addEventListener('click', async () => {
                    regenBtn.disabled = true;
                    regenBtn.textContent = 'Generating…';
                    try {
                        const result = await API.payment.createQrph({
                            invoice_id: inv.id,
                            booking_number: onlinePayment.booking_number || '',
                            regenerate: true
                        });
                        if (result.already_paid) {
                            await markInvoicePaid();
                            return;
                        }
                        const payment = result.payment || result;
                        intentId = payment.payment_intent_id || intentId;
                        const img = document.getElementById('invoice-qrph-image');
                        const missing = document.getElementById('invoice-qrph-missing');
                        if (payment.qr_image_url) {
                            if (img) {
                                img.src = payment.qr_image_url;
                                img.style.display = 'inline-block';
                            } else if (missing) {
                                missing.outerHTML = `<img id="invoice-qrph-image" src="${payment.qr_image_url.replace(/"/g, '&quot;')}" alt="QR Ph payment" style="max-width:260px;width:100%;height:auto;border:1px solid #fff;border-radius:8px;background:#fff;">`;
                            }
                        }
                        if (pollEl) pollEl.textContent = 'Waiting for payment… this page updates automatically.';
                        stopPoll();
                        pollTimer = setInterval(poll, 5000);
                    } catch (error) {
                        alert(error.message || 'Unable to generate QR code.');
                    } finally {
                        regenBtn.disabled = false;
                        regenBtn.textContent = 'Refresh / New QR';
                    }
                });
            }
        }

        function formatInvoiceMoney(value) {
            return parseFloat(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function formatInvoiceDate(value) {
            if (!value) return '—';
            return new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text == null ? '' : String(text);
            return div.innerHTML;
        }

        function getInvoiceStatusClass(status) {
            const map = {
                paid: 'status-confirmed',
                partial: 'status-pending',
                issued: 'status-pending',
                overdue: 'status-cancelled'
            };
            return map[status] || 'status-pending';
        }

        // Load bookings
        async function loadBookings() {
            const container = document.getElementById('bookings-container');
            if (!container) {
                console.error('Bookings container not found');
                return;
            }
            
            try {
                const response = await API.booking.getMyBookings();
                
                if (response.success && response.bookings && response.bookings.length > 0) {
                    container.innerHTML = response.bookings.map(booking => {
                        const bookingNumber = booking.booking_number || booking.id;
                        const status = (booking.status || 'pending').toLowerCase();
                        const items = Array.isArray(booking.items) ? booking.items : [];
                        const guestsLabel = formatBookingGuests(booking);
                        const roomsCount = booking.rooms || items.length || 1;

                        const itemsHtml = items.length > 0
                            ? `
                                <div style="margin-bottom: 1rem;">
                                    <strong style="color: #666; display: block; margin-bottom: 0.75rem;">Room Details:</strong>
                                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                        ${items.map((item, index) => `
                                            <div style="
                                                padding: 0.85rem 1rem;
                                                background: #faf8f4;
                                                border: 1px solid #eee;
                                                border-radius: 6px;
                                            ">
                                                <div style="display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.35rem;">
                                                    <strong style="color: #333;">${index + 1}. ${item.room_name || 'Room'}</strong>
                                                    <span style="font-weight: 600; color: #b2945b;">₱${parseFloat(item.subtotal || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                                </div>
                                                <div style="color: #666; font-size: 0.9rem; display: flex; flex-wrap: wrap; gap: 0.75rem 1.25rem;">
                                                    <span>Check-In: ${formatBookingDate(item.check_in)}</span>
                                                    <span>Check-Out: ${formatBookingDate(item.check_out)}</span>
                                                    <span>${item.nights || 1} night(s)</span>
                                                    <span>₱${parseFloat(item.price_per_night || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}/night</span>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `
                            : `
                                <div style="margin-bottom: 1rem; color: #666;">
                                    <strong style="display: block; margin-bottom: 0.25rem;">Room:</strong>
                                    <span>${booking.room_name || 'Room'}</span>
                                </div>
                            `;

                        const services = parseBookingServices(booking);
                        const servicesHtml = services.length > 0
                            ? `
                                <div style="margin-bottom: 1rem;">
                                    <strong style="color: #666; display: block; margin-bottom: 0.75rem;">Extra Services:</strong>
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        ${services.map(service => `
                                            <div style="
                                                padding: 0.75rem 1rem;
                                                background: #f7f9fc;
                                                border: 1px solid #eee;
                                                border-radius: 6px;
                                                display: flex;
                                                justify-content: space-between;
                                                gap: 1rem;
                                                flex-wrap: wrap;
                                            ">
                                                <span style="color: #333;">${service.name}</span>
                                                ${service.cost !== null
                                                    ? `<span style="font-weight: 600; color: #b2945b;">₱${service.cost.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>`
                                                    : ''}
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `
                            : '';

                        const cancelButton = status === 'pending'
                            ? `<button type="button" class="cancel-booking-btn" data-booking-id="${booking.id}" data-booking-number="${bookingNumber}" style="
                                    padding: 0.45rem 1rem;
                                    border: 1px solid #c62828;
                                    background: #fff;
                                    color: #c62828;
                                    border-radius: 20px;
                                    font-size: 0.875rem;
                                    font-weight: 600;
                                    cursor: pointer;
                                ">Cancel</button>`
                            : '';

                        return `
                        <div class="booking-card" style="
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            padding: 1.5rem;
                            margin-bottom: 1rem;
                            background: white;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        ">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                                <div>
                                    <h3 style="margin: 0; color: #333;">Booking #${bookingNumber}</h3>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                                    ${cancelButton}
                                    <span class="status-badge status-${status}" style="
                                        padding: 0.5rem 1rem;
                                        border-radius: 20px;
                                        font-size: 0.875rem;
                                        font-weight: 600;
                                        text-transform: uppercase;
                                    ">${status}</span>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem;">Check-In:</strong>
                                    <span>${formatBookingDate(booking.check_in)}</span>
                                </div>
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem;">Check-Out:</strong>
                                    <span>${formatBookingDate(booking.check_out)}</span>
                                </div>
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem;">Guests:</strong>
                                    <span>${guestsLabel}</span>
                                </div>
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem;">Total Rooms:</strong>
                                    <span>${roomsCount}</span>
                                </div>
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem;">Total Amount:</strong>
                                    <span style="font-weight: 600; color: #b2945b;">₱${parseFloat(booking.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                </div>
                            </div>
                            ${itemsHtml}
                            ${servicesHtml}
                            ${booking.notes ? `<p style="color: #666; font-style: italic; margin: 0; padding-top: 1rem; border-top: 1px solid #eee;"><strong>Notes:</strong> ${booking.notes}</p>` : ''}
                        </div>
                    `;
                    }).join('');

                    container.querySelectorAll('.cancel-booking-btn').forEach(btn => {
                        btn.addEventListener('click', () => cancelBooking(btn.dataset.bookingId, btn.dataset.bookingNumber, btn));
                    });
                } else {
                    container.innerHTML = `
                        <div style="text-align: center; padding: 3rem;">
                            <h3 style="color: #666;">No bookings found</h3>
                            <p style="color: #999; margin-bottom: 1.5rem;">You haven't made any bookings yet.</p>
                            <a href="rooms.php" class="cta-button" style="display: inline-block;">Browse Rooms</a>
                        </div>
                    `;
                }
            } catch (error) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 3rem; color: #d32f2f;">
                        <h3>Error loading bookings</h3>
                        <p>${error.message || 'Please try again later.'}</p>
                    </div>
                `;
            }
        }

        function formatBookingDate(dateValue) {
            if (!dateValue) return '—';
            return new Date(dateValue).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        }

        function formatBookingGuests(booking) {
            const notes = booking.notes || '';
            const guestMatch = notes.match(/Guests:\s*(\d+)\s*Adult\(s\),\s*(\d+)\s*Child\(ren\)/i);
            if (guestMatch) {
                const adults = parseInt(guestMatch[1], 10) || 0;
                const children = parseInt(guestMatch[2], 10) || 0;
                const total = adults + children;
                return `${total} person(s) (${adults} Adult(s), ${children} Child(ren))`;
            }
            return `${booking.guests || 1} person(s)`;
        }

        function parseBookingServices(booking) {
            if (Array.isArray(booking.extra_services) && booking.extra_services.length > 0) {
                return booking.extra_services.map(service => ({
                    name: service.name || 'Service',
                    cost: service.cost === null || service.cost === undefined || service.cost === ''
                        ? null
                        : parseFloat(service.cost)
                })).filter(service => !!service.name);
            }

            const notes = booking.notes || '';
            const servicesMatch = notes.match(/\|\s*Services:\s*(.+?)(?:\s*\|\s*|$)/i);
            if (!servicesMatch) return [];

            // Strip trailing card note if present, e.g. "(Card ending in 1234)"
            const servicesText = servicesMatch[1].replace(/\s*\(Card ending in.*$/i, '').trim();
            if (!servicesText) return [];

            return servicesText.split(',').map(part => {
                const trimmed = part.trim();
                if (!trimmed) return null;

                const withCost = trimmed.match(/^(.+?)\s*\(₱\s*([\d,]+(?:\.\d{1,2})?)\)\s*$/);
                if (withCost) {
                    return {
                        name: withCost[1].trim(),
                        cost: parseFloat(withCost[2].replace(/,/g, ''))
                    };
                }

                return { name: trimmed, cost: null };
            }).filter(Boolean);
        }

        async function cancelBooking(bookingId, bookingNumber, buttonEl) {
            if (!bookingId) return;
            const confirmed = window.confirm(`Cancel Booking #${bookingNumber}? This cannot be undone.`);
            if (!confirmed) return;

            const originalText = buttonEl ? buttonEl.textContent : 'Cancel';
            if (buttonEl) {
                buttonEl.disabled = true;
                buttonEl.textContent = 'Cancelling...';
            }

            try {
                const response = await API.booking.cancel(parseInt(bookingId, 10));
                if (response.success) {
                    showMessage(response.message || 'Booking cancelled successfully.', 'success');
                    await loadBookings();
                } else {
                    throw new Error(response.message || 'Failed to cancel booking.');
                }
            } catch (error) {
                showMessage(error.message || 'Failed to cancel booking. Please try again.', 'error');
                if (buttonEl) {
                    buttonEl.disabled = false;
                    buttonEl.textContent = originalText;
                }
            }
        }
        
        // Message display helper (reuse from booking-api.js)
        function showMessage(message, type = 'info') {
            const existing = document.querySelector('.api-message');
            if (existing) existing.remove();
            
            const messageEl = document.createElement('div');
            messageEl.className = `api-message ${type}`;
            messageEl.textContent = message;
            messageEl.style.cssText = `
                padding: 1rem;
                margin: 1rem 0;
                border-radius: 4px;
                background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
                color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
                border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
            `;
            
            const container = document.querySelector('.container');
            if (container) {
                container.insertBefore(messageEl, container.firstChild);
                
                // For error messages, show longer
                const timeout = type === 'error' ? 8000 : 5000;
                setTimeout(() => {
                    messageEl.remove();
                }, timeout);
            }
        }
        
        // Show error in dashboard error container
        function showDashboardError(message) {
            const errorContainer = document.getElementById('dashboard-error');
            const errorMessage = document.getElementById('error-message');
            if (errorContainer && errorMessage) {
                errorMessage.textContent = message;
                errorContainer.style.display = 'block';
            }
        }
    </script>
    <style>
        .status-badge {
            display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .tab-content {
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        #invoice-back-btn.cta-button:hover {
            background: #5a6268;
            border-color: #5a6268;
            color: #fff;
        }
        #invoice-qrph-regenerate.cta-button:hover {
            background: #2a3350;
            border-color: #2a3350;
            color: #fff;
        }
    </style>
</body>
</html>



