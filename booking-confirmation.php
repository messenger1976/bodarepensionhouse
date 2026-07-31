<?php
$pageSeo = [
    'title' => 'Booking Confirmation | BODARE Pension House',
    'description' => 'Your booking confirmation details for BODARE Pension House.',
    'canonical_path' => 'booking-confirmation.php',
    'robots' => 'noindex,nofollow',
    'include_bootstrap_icons' => false,
];
$enableAds = false;
include __DIR__ . '/includes/site-head.php';
?>
<body>

    <?php
    $headerConfig = [
        'logo_href' => 'index.php',
        'show_cart' => false
    ];
    include __DIR__ . '/includes/site-header.php';
?>


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

    <?php
    $footerConfig = ['variant' => 'minimal'];
    include __DIR__ . '/includes/site-footer.php';
?>
    
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
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
</body>
</html>





