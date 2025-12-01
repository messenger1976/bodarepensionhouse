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
    
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Jost:wght@200;300;400&display=swap" rel="stylesheet">
</head>
<body>

    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="nav-logo">
                <img src="img/logo.png" alt="Bodare Logo" class="logo-img">
            </a>
            <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php#about" class="nav-link">About</a></li>
                <li class="nav-item"><a href="rooms.php" class="nav-link">Rooms</a></li>
                <li class="nav-item"><a href="amenities.php" class="nav-link">Amenities</a></li>
                <li class="nav-item"><a href="gallery.php" class="nav-link">Gallery</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
            </ul>
            <div class="user-menu">
                <span id="user-name-display"></span>
                <button id="logout-btn" class="cta-button-secondary">Logout</button>
            </div>
        </nav>
    </header>

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
            
            <!-- Dashboard Tabs -->
            <div class="dashboard-tabs" style="
                display: flex;
                gap: 0.5rem;
                border-bottom: 2px solid #e0e0e0;
                margin-bottom: 2rem;
                flex-wrap: wrap;
            ">
                <button class="tab-button active" data-tab="bookings" style="
                    padding: 1rem 2rem;
                    background: none;
                    border: none;
                    border-bottom: 3px solid transparent;
                    cursor: pointer;
                    font-size: 1rem;
                    font-weight: 500;
                    color: #666;
                    transition: all 0.3s;
                ">My Bookings</button>
                <button class="tab-button" data-tab="profile" style="
                    padding: 1rem 2rem;
                    background: none;
                    border: none;
                    border-bottom: 3px solid transparent;
                    cursor: pointer;
                    font-size: 1rem;
                    font-weight: 500;
                    color: #666;
                    transition: all 0.3s;
                ">My Profile</button>
                <button class="tab-button" data-tab="inquiry" style="
                    padding: 1rem 2rem;
                    background: none;
                    border: none;
                    border-bottom: 3px solid transparent;
                    cursor: pointer;
                    font-size: 1rem;
                    font-weight: 500;
                    color: #666;
                    transition: all 0.3s;
                ">Send Inquiry</button>
            </div>

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
                                <input type="text" id="profile-city" placeholder="City">
                            </div>
                            <div class="form-group-contact">
                                <input type="text" id="profile-province" placeholder="Province">
                            </div>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group-contact">
                                <input type="text" id="profile-postal-code" placeholder="Postal Code">
                            </div>
                            <div class="form-group-contact">
                                <input type="text" id="profile-country" placeholder="Country" value="Philippines">
                            </div>
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
                        
                        <h3 style="margin: 2rem 0 1rem 0; color: #1a2238; font-size: 1.25rem;">Account Security</h3>
                        <div class="form-group-contact">
                            <input type="password" id="profile-password" placeholder="New Password (leave blank to keep current)">
                        </div>
                        <div class="form-group-contact">
                            <input type="password" id="profile-confirm-password" placeholder="Confirm New Password">
                        </div>
                        
                        <button type="submit" class="cta-button">Update Profile</button>
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

        </div>
    </main>

    <footer id="contact" class="site-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 Bodare and Community Multi-Purpose Cooperative. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="api-config.js"></script>
    <script src="booking-api.js"></script>
    <script src="script.js"></script>
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
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTab = button.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active');
                        btn.style.borderBottomColor = 'transparent';
                        btn.style.color = '#666';
                    });
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                        content.style.display = 'none';
                    });
                    
                    // Add active class to clicked button and corresponding content
                    button.classList.add('active');
                    button.style.borderBottomColor = '#b2945b';
                    button.style.color = '#b2945b';
                    
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
                });
            });
        }
        
        // Verify session and load profile
        async function verifySessionAndLoadProfile() {
            try {
                // First verify the session is still valid
                const sessionCheck = await API.auth.check();
                console.log('Session check result:', sessionCheck);
                
                if (!sessionCheck.success || !sessionCheck.logged_in) {
                    console.warn('Session is not valid, user may need to login again');
                    if (document.getElementById('profile-tab').style.display !== 'none') {
                        showMessage('Your session has expired. Please log in again.', 'error');
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
            const cityEl = document.getElementById('profile-city');
            const provinceEl = document.getElementById('profile-province');
            const postalCodeEl = document.getElementById('profile-postal-code');
            const countryEl = document.getElementById('profile-country');
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
                    if (cityEl) cityEl.value = user.city || '';
                    if (provinceEl) provinceEl.value = user.province || '';
                    if (postalCodeEl) postalCodeEl.value = user.postal_code || '';
                    if (countryEl) countryEl.value = user.country || 'Philippines';
                    if (dateOfBirthEl) dateOfBirthEl.value = user.date_of_birth || '';
                    if (genderEl) genderEl.value = user.gender || '';
                    if (nationalityEl) nationalityEl.value = user.nationality || '';
                    if (idTypeEl) idTypeEl.value = user.id_type || '';
                    if (idNumberEl) idNumberEl.value = user.id_number || '';
                    
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
                
                // Show error but don't block the page
                if (error.message && (error.message.includes('log in') || error.message.includes('session'))) {
                    console.warn('Session expired. User may need to login again.');
                    if (profileTab.style.display !== 'none') {
                        const errorMsg = error.response && error.response.message 
                            ? error.response.message 
                            : 'Your session has expired. Please log in again to view your profile.';
                        showMessage(errorMsg, 'error');
                    }
                } else if (error.status === 401) {
                    // Unauthorized - session issue
                    console.warn('Unauthorized access - session may have expired');
                    if (profileTab.style.display !== 'none') {
                        const errorMsg = error.response && error.response.message 
                            ? error.response.message 
                            : 'Please log in again to view your profile.';
                        showMessage(errorMsg, 'error');
                    }
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
                
                const password = document.getElementById('profile-password').value;
                const confirmPassword = document.getElementById('profile-confirm-password').value;
                
                if (password && password !== confirmPassword) {
                    showMessage('Passwords do not match. Please try again.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    return;
                }
                
                if (password && password.length < 6) {
                    showMessage('Password must be at least 6 characters long.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    return;
                }
                
                const profileData = {
                    first_name: document.getElementById('profile-first-name').value.trim(),
                    last_name: document.getElementById('profile-last-name').value.trim(),
                    email: document.getElementById('profile-email').value.trim(),
                    phone: document.getElementById('profile-phone').value.trim(),
                    address: document.getElementById('profile-address').value.trim(),
                    city: document.getElementById('profile-city')?.value.trim() || '',
                    province: document.getElementById('profile-province')?.value.trim() || '',
                    postal_code: document.getElementById('profile-postal-code')?.value.trim() || '',
                    country: document.getElementById('profile-country')?.value.trim() || 'Philippines',
                    date_of_birth: document.getElementById('profile-date-of-birth')?.value || '',
                    gender: document.getElementById('profile-gender')?.value || '',
                    nationality: document.getElementById('profile-nationality')?.value.trim() || '',
                    id_type: document.getElementById('profile-id-type')?.value || '',
                    id_number: document.getElementById('profile-id-number')?.value.trim() || ''
                };
                
                if (password) {
                    profileData.password = password;
                }
                
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
                        
                        // Clear password fields
                        document.getElementById('profile-password').value = '';
                        document.getElementById('profile-confirm-password').value = '';
                    }
                } catch (error) {
                    showMessage(error.message || 'Failed to update profile. Please try again.', 'error');
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
        .tab-button.active {
            border-bottom-color: #b2945b !important;
            color: #b2945b !important;
        }
        .tab-content {
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
    <script>
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('ServiceWorker registration successful:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('ServiceWorker registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>
