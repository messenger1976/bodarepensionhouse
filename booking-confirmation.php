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
        function formatPeso(amount) {
            return `₱${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        function formatBookingDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString + 'T12:00:00');
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function parseBookingServices(booking) {
            if (Array.isArray(booking.extra_services) && booking.extra_services.length > 0) {
                return booking.extra_services.map(service => ({
                    name: service.name || 'Service',
                    cost: service.cost !== null && service.cost !== undefined ? parseFloat(service.cost) : null
                }));
            }
            return [];
        }

        function isExtraBedService(name) {
            return typeof name === 'string' && name.toLowerCase().indexOf('extra bed') === 0;
        }

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
                    const items = Array.isArray(booking.items) ? booking.items : [];
                    const services = parseBookingServices(booking);
                    const extraBedServices = services.filter(service => isExtraBedService(service.name));
                    const otherServices = services.filter(service => !isExtraBedService(service.name));
                    const roomsSubtotal = items.reduce((sum, item) => sum + parseFloat(item.subtotal || 0), 0);
                    const extraBedTotal = extraBedServices.reduce((sum, service) => sum + (service.cost || 0), 0);
                    const otherServicesTotal = otherServices.reduce((sum, service) => sum + (service.cost || 0), 0);

                    const itemsHtml = items.length > 0
                        ? items.map((item, index) => `
                            <div style="padding: 1rem; background: #faf8f4; border: 1px solid #eee; border-radius: 6px; margin-bottom: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.35rem;">
                                    <strong style="color: #333;">${index + 1}. ${item.room_name || 'Room'}</strong>
                                    <span style="font-weight: 600; color: #b2945b;">${formatPeso(item.subtotal)}</span>
                                </div>
                                <div style="color: #666; font-size: 0.9rem; display: flex; flex-wrap: wrap; gap: 0.75rem 1.25rem;">
                                    <span>Check-In: ${formatBookingDate(item.check_in)}</span>
                                    <span>Check-Out: ${formatBookingDate(item.check_out)}</span>
                                    <span>${item.nights || 1} night(s)</span>
                                    <span>${formatPeso(item.price_per_night)}/night</span>
                                </div>
                            </div>
                        `).join('')
                        : `
                            <div style="padding: 1rem; background: #faf8f4; border: 1px solid #eee; border-radius: 6px; margin-bottom: 0.75rem;">
                                <strong>${booking.room_name || 'Room'}</strong>
                                <div style="color: #666; font-size: 0.9rem; margin-top: 0.35rem;">
                                    ${formatBookingDate(booking.check_in)} – ${formatBookingDate(booking.check_out)}
                                </div>
                            </div>
                        `;

                    const extraBedRowsHtml = extraBedServices.length > 0
                        ? extraBedServices.map(service => `
                            <div class="summary-item" style="display: flex; justify-content: space-between; gap: 1rem; padding: 0.65rem 0; border-bottom: 1px solid #eee;">
                                <span style="color: #333;">${service.name}</span>
                                <strong style="color: #b2945b;">${formatPeso(service.cost)}</strong>
                            </div>
                        `).join('')
                        : '';

                    const otherServicesHtml = otherServices.length > 0
                        ? otherServices.map(service => `
                            <div class="summary-item" style="display: flex; justify-content: space-between; gap: 1rem; padding: 0.65rem 0; border-bottom: 1px solid #eee;">
                                <span style="color: #333;">${service.name}</span>
                                <strong style="color: #b2945b;">${service.cost !== null ? formatPeso(service.cost) : ''}</strong>
                            </div>
                        `).join('')
                        : '';
                    
                    container.innerHTML = `
                        <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 2rem; margin-bottom: 2rem; text-align: center;">
                            <h2 style="color: #155724; margin: 0 0 1rem 0;">✓ Your booking has been confirmed!</h2>
                            <p style="color: #155724; margin: 0; font-size: 1.125rem;">Booking Number: <strong>${booking.booking_number}</strong></p>
                        </div>

                        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <h3 style="margin-top: 0; color: #1a2238;">Booking Summary</h3>
                            <div style="margin-bottom: 1.25rem;">
                                <strong style="color: #666; display: block; margin-bottom: 0.75rem;">Rooms</strong>
                                ${itemsHtml}
                            </div>
                            <div style="border-top: 1px solid #eee; padding-top: 1rem;">
                                <div class="summary-item" style="display: flex; justify-content: space-between; gap: 1rem; padding: 0.65rem 0; border-bottom: 1px solid #eee;">
                                    <span style="color: #333;">Room Rate Subtotal</span>
                                    <strong>${formatPeso(roomsSubtotal)}</strong>
                                </div>
                                ${extraBedRowsHtml}
                                ${otherServicesHtml}
                                <div class="summary-item" style="display: flex; justify-content: space-between; gap: 1rem; padding: 1rem 0 0; font-size: 1.125rem;">
                                    <span style="color: #1a2238;"><strong>Total</strong></span>
                                    <strong style="color: #b2945b;">${formatPeso(booking.total_amount)}</strong>
                                </div>
                            </div>
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
</body>
</html>
