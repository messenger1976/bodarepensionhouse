<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Manage your account, bookings, and inquiries at BODARE Pension House">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>My Dashboard - BODARE Pension House</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="img/logo.png">
    <link rel="icon" type="image/png" href="img/logo.png">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Jost:wght@200;300;400&display=swap" rel="stylesheet">
</head>
<body>

    <?php
    $headerConfig = [
        'logo_href' => 'index.php',
        'show_user_menu' => true
    ];
    include __DIR__ . '/includes/site-header.php';
?>


    <section class="page-header">
        <div class="page-header-content">
            <h1>My Dashboard</h1>
            <p>Manage your account, bookings, and inquiries</p>
        </div>
    </section>

    <main class="content-section">
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
                if (!in_array($customerSidebarActiveTab, ['bookings', 'profile', 'security', 'inquiry'], true)) {
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
                    <form id="inquiry-form" class="minimal-form">
                        <div class="form-group-contact">
                            <label for="inquiry-subject">Subject</label>
                            <input type="text" id="inquiry-subject" placeholder="What is your inquiry about?" required>
                        </div>
                        <div class="form-group-contact">
                            <label for="inquiry-message">Message</label>
                            <textarea id="inquiry-message" rows="6" placeholder="Please provide details about your inquiry..." required style="
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
                
                // Setup tab switching
                setupTabs();
                
                // Setup forms
                setupProfileForm();
                setupSecurityForm();
                setupInquiryForm();
                
                // Setup logout
                const logoutBtn = document.getElementById('logout-btn');
                if (logoutBtn) {
                    logoutBtn.addEventListener('click', async () => {
                        try {
                            await API.auth.logout();
                        } catch (error) {
                            console.error('Logout error:', error);
                        } finally {
                            localStorage.removeItem('user');
                            if (typeof clearBookingCartData === 'function') {
                                clearBookingCartData();
                            } else {
                                localStorage.removeItem('bookingCart');
                                localStorage.removeItem('cartServices');
                                localStorage.removeItem('bookingDetails');
                            }
                            window.location.href = 'index.php';
                        }
                    });
                }
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

                    // Load profile data when profile tab is clicked
                    if (targetTab === 'profile') {
                        verifySessionAndLoadProfile().catch(err => {
                            console.error('Profile load error on tab click:', err);
                        });
                    }
                }
            }

            // Activate initial tab from URL
            const params = new URLSearchParams(window.location.search);
            const initialTab = params.get('tab');
            if (initialTab && ['bookings', 'profile', 'security', 'inquiry'].includes(initialTab)) {
                activateTab(initialTab);
            } else {
                activateTab('bookings');
            }
            
            tabButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetTab = button.getAttribute('data-tab');
                    activateTab(targetTab);

                    const updatedUrl = new URL(window.location.href);
                    updatedUrl.searchParams.set('tab', targetTab);
                    window.history.replaceState({}, '', updatedUrl.toString());
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
        
        // Setup inquiry form
        function setupInquiryForm() {
            const form = document.getElementById('inquiry-form');
            if (!form) return;
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';
                
                const inquiryData = {
                    subject: document.getElementById('inquiry-subject').value.trim(),
                    message: document.getElementById('inquiry-message').value.trim()
                };
                
                try {
                    const response = await API.inquiry.submit(inquiryData);
                    if (response.success) {
                        showMessage('Your inquiry has been sent successfully! We will get back to you soon.', 'success');
                        form.reset();
                    }
                } catch (error) {
                    showMessage(error.message || 'Failed to send inquiry. Please try again.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
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
                    container.innerHTML = response.bookings.map(booking => `
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
                                    <h3 style="margin: 0 0 0.5rem 0; color: #333;">${booking.room_name || 'Room'}</h3>
                                    <p style="color: #666; margin: 0; font-size: 0.9rem;">Booking #${booking.booking_number || booking.id}</p>
                                </div>
                                <span class="status-badge status-${booking.status}" style="
                                    padding: 0.5rem 1rem;
                                    border-radius: 20px;
                                    font-size: 0.875rem;
                                    font-weight: 600;
                                    text-transform: uppercase;
                                ">${booking.status || 'pending'}</span>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem;">Check-In:</strong>
                                    <span>${new Date(booking.check_in).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                                </div>
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem;">Check-Out:</strong>
                                    <span>${new Date(booking.check_out).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                                </div>
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem;">Guests:</strong>
                                    <span>${booking.guests || 1} person(s)</span>
                                </div>
                                <div>
                                    <strong style="color: #666; display: block; margin-bottom: 0.25rem;">Total Amount:</strong>
                                    <span style="font-weight: 600; color: #b2945b;">₱${parseFloat(booking.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                </div>
                            </div>
                            ${booking.notes ? `<p style="color: #666; font-style: italic; margin: 0; padding-top: 1rem; border-top: 1px solid #eee;"><strong>Notes:</strong> ${booking.notes}</p>` : ''}
                        </div>
                    `).join('');
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
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        #user-name-display {
            color: #ff8c00;
            font-weight: 600;
        }
        .tab-content {
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</body>
</html>



