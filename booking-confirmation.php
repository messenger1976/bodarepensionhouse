<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Your booking has been confirmed at BODARE Pension House">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Booking Confirmation - BODARE Pension House</title>
    
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
            <div class="header-actions">
                <a href="login.php" id="login-account-btn" class="cta-button-secondary" style="display: none;">Login</a>
                <a href="customer-dashboard.php" id="my-account-btn" class="cta-button-secondary" style="display: none;">My Account</a>
                <a href="rooms.php" class="cta-button">Book Now</a>
            </div>
        </nav>
    </header>

    <section class="page-header">
        <div class="page-header-content">
            <h1>Booking Confirmed!</h1>
            <p>Thank you for your reservation</p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            <div id="confirmation-container" style="max-width: 800px; margin: 0 auto;">
                <div class="loading-message">Loading booking details...</div>
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
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const params = new URLSearchParams(window.location.search);
            const bookingNumber = params.get('booking') || localStorage.getItem('booking_number');
            
            if (!bookingNumber) {
                document.getElementById('confirmation-container').innerHTML = `
                    <div style="text-align: center; padding: 3rem; color: #d32f2f;">
                        <h3>Booking not found</h3>
                        <p>Invalid booking number.</p>
                        <a href="rooms.php" class="cta-button" style="margin-top: 1rem; display: inline-block;">Browse Rooms</a>
                    </div>
                `;
                return;
            }
            
            try {
                const response = await API.booking.getByNumber(bookingNumber);
                
                if (response.success && response.booking) {
                    const booking = response.booking;
                    const container = document.getElementById('confirmation-container');
                    
                    container.innerHTML = `
                        <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 2rem; margin-bottom: 2rem; text-align: center;">
                            <h2 style="color: #155724; margin: 0 0 1rem 0;">✓ Your booking has been confirmed!</h2>
                            <p style="color: #155724; margin: 0; font-size: 1.125rem;">Booking Number: <strong>${booking.booking_number}</strong></p>
                        </div>
                        
                        <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 2rem; margin-bottom: 2rem;">
                            <h3 style="margin-top: 0;">Booking Details</h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div>
                                    <strong>Room:</strong><br>
                                    ${booking.room_name} (${booking.room_type})
                                </div>
                                <div>
                                    <strong>Check-In:</strong><br>
                                    ${new Date(booking.check_in).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                                </div>
                                <div>
                                    <strong>Check-Out:</strong><br>
                                    ${new Date(booking.check_out).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                                </div>
                                <div>
                                    <strong>Guests:</strong><br>
                                    ${booking.guests} guest(s)
                                </div>
                                <div>
                                    <strong>Status:</strong><br>
                                    <span class="status-badge status-${booking.status}" style="
                                        padding: 0.25rem 0.75rem;
                                        border-radius: 15px;
                                        font-size: 0.875rem;
                                        font-weight: 600;
                                        text-transform: uppercase;
                                    ">${booking.status}</span>
                                </div>
                                <div>
                                    <strong>Total Amount:</strong><br>
                                    <span style="font-size: 1.25rem; font-weight: 600; color: #333;">₱${parseFloat(booking.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                </div>
                            </div>
                            
                            <div style="border-top: 1px solid #ddd; padding-top: 1.5rem; margin-top: 1.5rem;">
                                <h4>Guest Information</h4>
                                <p><strong>Name:</strong> ${booking.guest_name}</p>
                                <p><strong>Email:</strong> ${booking.guest_email}</p>
                                <p><strong>Phone:</strong> ${booking.guest_phone}</p>
                            </div>
                            
                            ${booking.notes ? `
                                <div style="border-top: 1px solid #ddd; padding-top: 1.5rem; margin-top: 1.5rem;">
                                    <h4>Special Requests</h4>
                                    <p>${booking.notes}</p>
                                </div>
                            ` : ''}
                        </div>
                        
                        <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
                            <h4 style="margin-top: 0; color: #856404;">Important Information</h4>
                            <ul style="color: #856404; margin: 0; padding-left: 1.5rem;">
                                <li>Your booking is currently <strong>${booking.status}</strong>. You will receive a confirmation email shortly.</li>
                                <li>Please arrive at the hotel during check-in hours (typically 2:00 PM).</li>
                                <li>If you need to modify or cancel your booking, please contact us using your booking number.</li>
                                <li>Payment will be collected at the hotel upon check-in.</li>
                            </ul>
                        </div>
                        
                        <div style="text-align: center;">
                            <a href="customer-dashboard.php" class="cta-button" style="margin-right: 1rem;">View All Bookings</a>
                            <a href="index.php" class="cta-button-secondary">Back to Home</a>
                        </div>
                    `;
                } else {
                    throw new Error('Booking not found');
                }
            } catch (error) {
                document.getElementById('confirmation-container').innerHTML = `
                    <div style="text-align: center; padding: 3rem; color: #d32f2f;">
                        <h3>Error loading booking</h3>
                        <p>${error.message || 'Please try again later.'}</p>
                        <a href="rooms.php" class="cta-button" style="margin-top: 1rem; display: inline-block;">Browse Rooms</a>
                    </div>
                `;
            }
        });
    </script>
    <style>
        .status-badge {
            display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
    </style>
    <script src="api-config.js"></script>
    <script src="script.js"></script>
    <script>
        // Check login status and update header button
        async function updateLoginButton() {
            const loginBtn = document.getElementById('login-account-btn');
            const accountBtn = document.getElementById('my-account-btn');
            
            if (!loginBtn || !accountBtn) return;
            
            try {
                if (typeof API !== 'undefined') {
                    const response = await API.auth.check();
                    if (response.success && response.logged_in) {
                        loginBtn.style.display = 'none';
                        accountBtn.style.display = 'inline-block';
                    } else {
                        loginBtn.style.display = 'inline-block';
                        accountBtn.style.display = 'none';
                    }
                } else {
                    loginBtn.style.display = 'inline-block';
                    accountBtn.style.display = 'none';
                }
            } catch (error) {
                loginBtn.style.display = 'inline-block';
                accountBtn.style.display = 'none';
            }
        }
        
        if (typeof API !== 'undefined') {
            updateLoginButton();
        } else {
            window.addEventListener('load', () => {
                if (typeof API !== 'undefined') {
                    updateLoginButton();
                }
            });
        }
        
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

