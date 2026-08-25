<?php
require_once __DIR__ . '/includes/site-config.php';

$bookingNumber = isset($_GET['booking']) ? trim((string) $_GET['booking']) : '';
$confirmation = $bookingNumber !== '' ? bodare_get_booking_confirmation($bookingNumber) : null;
$paymentFlag = isset($_GET['payment']) ? strtolower(trim((string) $_GET['payment'])) : '';
$sessionId = isset($_GET['session_id']) ? trim((string) $_GET['session_id']) : '';
$awaitingPaymentVerify = ($paymentFlag === 'success' && $bookingNumber !== '');

$pageSeo = [
    'title' => 'Booking Confirmation | BODARE Pension House',
    'description' => 'Your booking confirmation details for BODARE Pension House.',
    'canonical_path' => 'booking-confirmation.php',
    'robots' => 'noindex,nofollow',
    'include_bootstrap_icons' => false,
];
$enableAds = false;
include __DIR__ . '/includes/site-head.php';

function bodare_confirmation_date($dateString)
{
    if (!$dateString) {
        return '-';
    }
    $timestamp = strtotime($dateString);
    return $timestamp ? date('M j, Y', $timestamp) : htmlspecialchars((string) $dateString, ENT_QUOTES, 'UTF-8');
}
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
            <h1 id="confirmation-page-title"><?php echo $awaitingPaymentVerify ? 'Payment Processing' : 'Booking Confirmed!'; ?></h1>
            <p id="confirmation-page-subtitle"><?php echo $awaitingPaymentVerify ? 'Confirming your GCash payment…' : 'Thank you for your reservation'; ?></p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            <div id="confirmation-container" style="max-width: 800px; margin: 0 auto;">
                <?php if (!$confirmation): ?>
                    <div style="text-align: center; padding: 3rem; color: #d32f2f;">
                        <h3>Booking not found</h3>
                        <p>Invalid or missing booking number.</p>
                        <a href="rooms.php" class="cta-button" style="margin-top: 1rem; display: inline-block;">Browse Rooms</a>
                    </div>
                <?php else:
                    $booking = $confirmation['booking'];
                    $items = $confirmation['items'];
                    $extraBedLines = $confirmation['extra_bed_lines'];
                    $otherServices = $confirmation['other_services'];
                    $roomsSubtotal = $confirmation['rooms_subtotal'];
                    $extraBedTotal = $confirmation['extra_bed_total'];
                    $displayTotal = $confirmation['display_total'];
                    $status = htmlspecialchars((string) ($booking['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8');
                    $bookingNumberSafe = htmlspecialchars((string) $booking['booking_number'], ENT_QUOTES, 'UTF-8');
                ?>
                    <div id="confirmation-banner" style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 2rem; margin-bottom: 2rem; text-align: center;">
                        <h2 id="confirmation-banner-title" style="color: #155724; margin: 0 0 1rem 0;">
                            <?php echo $awaitingPaymentVerify ? '⏳ Confirming your GCash payment…' : '✓ Your booking has been confirmed!'; ?>
                        </h2>
                        <p style="color: #155724; margin: 0; font-size: 1.125rem;">Booking Number: <strong><?php echo $bookingNumberSafe; ?></strong></p>
                        <p id="confirmation-payment-status" style="color: #155724; margin: 0.75rem 0 0; font-size: 0.95rem;">
                            <?php if ($awaitingPaymentVerify): ?>
                                Please wait while we verify your PayMongo payment.
                            <?php elseif (strtolower((string) ($booking['status'] ?? '')) === 'pending'): ?>
                                Status: Pending — our team will confirm your reservation shortly.
                            <?php endif; ?>
                        </p>
                    </div>

                    <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <h3 style="margin-top: 0; color: #1a2238;">Booking Summary</h3>

                        <div style="margin-bottom: 1.25rem;">
                            <strong style="color: #666; display: block; margin-bottom: 0.75rem;">Rooms</strong>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $index => $item): ?>
                                    <div style="padding: 1rem; background: #faf8f4; border: 1px solid #eee; border-radius: 6px; margin-bottom: 0.75rem;">
                                        <div style="display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.35rem;">
                                            <strong style="color: #333;"><?php echo ($index + 1) . '. ' . htmlspecialchars((string) $item['room_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <span style="font-weight: 600; color: #b2945b;"><?php echo bodare_format_peso($item['subtotal'] ?? 0); ?></span>
                                        </div>
                                        <div style="color: #666; font-size: 0.9rem; display: flex; flex-wrap: wrap; gap: 0.75rem 1.25rem;">
                                            <span>Check-In: <?php echo bodare_confirmation_date($item['check_in'] ?? ''); ?></span>
                                            <span>Check-Out: <?php echo bodare_confirmation_date($item['check_out'] ?? ''); ?></span>
                                            <span><?php echo (int) ($item['nights'] ?? 1); ?> night(s)</span>
                                            <span><?php echo bodare_format_peso($item['price_per_night'] ?? 0); ?>/night</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="padding: 1rem; background: #faf8f4; border: 1px solid #eee; border-radius: 6px; margin-bottom: 0.75rem;">
                                    <strong><?php echo htmlspecialchars((string) ($booking['room_name'] ?? 'Room'), ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <div style="color: #666; font-size: 0.9rem; margin-top: 0.35rem;">
                                        <?php echo bodare_confirmation_date($booking['check_in'] ?? ''); ?> – <?php echo bodare_confirmation_date($booking['check_out'] ?? ''); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="price-breakdown" style="border-top: 1px solid #eee; padding-top: 1rem;">
                            <div class="summary-item" style="display: flex; justify-content: space-between; gap: 1rem; padding: 0.65rem 0; border-bottom: 1px solid #eee;">
                                <span style="color: #333;">Room Rate Subtotal</span>
                                <strong id="rooms-subtotal"><?php echo bodare_format_peso($roomsSubtotal); ?></strong>
                            </div>

                            <div id="extra-bed-rows">
                                <?php foreach ($extraBedLines as $line): ?>
                                    <div class="summary-item extra-bed-row" style="display: flex; justify-content: space-between; gap: 1rem; padding: 0.65rem 0; border-bottom: 1px solid #eee;">
                                        <span style="color: #333;"><?php echo htmlspecialchars((string) $line['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <strong style="color: #b2945b;"><?php echo bodare_format_peso($line['cost'] ?? 0); ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php foreach ($otherServices as $service): ?>
                                <div class="summary-item" style="display: flex; justify-content: space-between; gap: 1rem; padding: 0.65rem 0; border-bottom: 1px solid #eee;">
                                    <span style="color: #333;"><?php echo htmlspecialchars((string) $service['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <strong style="color: #b2945b;"><?php echo $service['cost'] !== null ? bodare_format_peso($service['cost']) : ''; ?></strong>
                                </div>
                            <?php endforeach; ?>

                            <div class="summary-item" style="display: flex; justify-content: space-between; gap: 1rem; padding: 1rem 0 0; font-size: 1.125rem;">
                                <span style="color: #1a2238;"><strong>Total</strong></span>
                                <strong id="booking-total" style="color: #b2945b;" data-server-total="<?php echo htmlspecialchars((string) ($booking['total_amount'] ?? $displayTotal), ENT_QUOTES, 'UTF-8'); ?>" data-display-total="<?php echo htmlspecialchars((string) $displayTotal, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo bodare_format_peso(max((float) ($booking['total_amount'] ?? 0), $displayTotal)); ?>
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
                        <h4 style="margin-top: 0; color: #856404;">Important Information</h4>
                        <ul style="color: #856404; margin: 0; padding-left: 1.5rem;">
                            <li>Your booking is currently <strong><?php echo $status; ?></strong>. You will receive a confirmation email shortly.</li>
                            <li>Please arrive at the hotel during check-in hours (typically 2:00 PM).</li>
                            <li>If you need to modify or cancel your booking, please contact us using your booking number.</li>
                            <li>Payment will be collected at the hotel upon check-in.</li>
                        </ul>
                    </div>

                    <div style="text-align: center;">
                        <a href="customer-dashboard.php" class="cta-button" style="margin-right: 1rem;">View All Bookings</a>
                        <a href="index.php" class="cta-button-secondary">Back to Home</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php
    $footerConfig = ['variant' => 'minimal'];
    include __DIR__ . '/includes/site-footer.php';
?>

    <?php if ($confirmation): ?>
    <script src="api-config.js"></script>
    <script>
        (function () {
            const bookingNumber = <?php echo json_encode($bookingNumber); ?>;
            const sessionId = <?php echo json_encode($sessionId); ?>;
            const shouldVerifyPayment = <?php echo $awaitingPaymentVerify ? 'true' : 'false'; ?>;

            async function verifyPaymongoPayment() {
                if (!shouldVerifyPayment || typeof API === 'undefined' || !API.payment) {
                    return;
                }

                const banner = document.getElementById('confirmation-banner');
                const bannerTitle = document.getElementById('confirmation-banner-title');
                const statusEl = document.getElementById('confirmation-payment-status');
                const pageTitle = document.getElementById('confirmation-page-title');
                const pageSubtitle = document.getElementById('confirmation-page-subtitle');

                try {
                    const result = await API.payment.verify({
                        booking_number: bookingNumber,
                        session_id: sessionId || sessionStorage.getItem('paymongo_session_id') || ''
                    });

                    if (result && result.paid) {
                        if (pageTitle) pageTitle.textContent = 'Payment Successful!';
                        if (pageSubtitle) pageSubtitle.textContent = 'Your GCash payment was received';
                        if (banner) {
                            banner.style.background = '#d4edda';
                            banner.style.borderColor = '#c3e6cb';
                        }
                        if (bannerTitle) {
                            bannerTitle.style.color = '#155724';
                            bannerTitle.textContent = '✓ GCash payment received — booking confirmed!';
                        }
                        if (statusEl) {
                            statusEl.style.color = '#155724';
                            statusEl.textContent = 'Paid securely via PayMongo. A receipt may also be sent to your email.';
                        }
                        sessionStorage.removeItem('paymongo_session_id');
                        sessionStorage.removeItem('paymongo_booking_number');
                        try { localStorage.removeItem('bookingCart'); } catch (e) {}
                        try { localStorage.removeItem('cartServices'); } catch (e) {}
                    } else {
                        if (pageTitle) pageTitle.textContent = 'Booking Received';
                        if (pageSubtitle) pageSubtitle.textContent = 'Payment still pending';
                        if (banner) {
                            banner.style.background = '#fff3cd';
                            banner.style.borderColor = '#ffeeba';
                        }
                        if (bannerTitle) {
                            bannerTitle.style.color = '#856404';
                            bannerTitle.textContent = '⚠ Payment not confirmed yet';
                        }
                        if (statusEl) {
                            statusEl.style.color = '#856404';
                            statusEl.textContent = 'If you completed GCash payment, it may take a moment. Keep your booking number and contact us if needed.';
                        }
                    }
                } catch (error) {
                    console.error('Payment verify failed', error);
                    if (statusEl) {
                        statusEl.textContent = 'We could not verify payment automatically. Your booking number is saved — please contact us if you already paid.';
                    }
                }
            }

            verifyPaymongoPayment();

            const hasExtraBedRows = <?php echo !empty($extraBedLines) ? 'true' : 'false'; ?>;
            if (hasExtraBedRows) {
                return;
            }

            let stored;
            try {
                stored = JSON.parse(localStorage.getItem('bookingResult') || 'null');
            } catch (error) {
                stored = null;
            }

            if (!stored || stored.booking_number !== bookingNumber || !Array.isArray(stored.items)) {
                return;
            }

            const extraBedItems = stored.items.filter(item => (parseInt(item.extraBeds, 10) || 0) > 0 && (parseFloat(item.extraBedTotal) || 0) > 0);
            if (extraBedItems.length === 0) {
                return;
            }

            const extraBedRows = document.getElementById('extra-bed-rows');
            const totalEl = document.getElementById('booking-total');
            if (!extraBedRows || !totalEl) {
                return;
            }

            const formatPeso = (amount) => `₱${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            let extraBedTotal = 0;

            extraBedItems.forEach(item => {
                const extraBeds = parseInt(item.extraBeds, 10) || 0;
                const extraBedCost = parseFloat(item.extraBedCost) || 0;
                const itemExtraTotal = parseFloat(item.extraBedTotal) || 0;
                extraBedTotal += itemExtraTotal;

                const row = document.createElement('div');
                row.className = 'summary-item extra-bed-row';
                row.style.cssText = 'display: flex; justify-content: space-between; gap: 1rem; padding: 0.65rem 0; border-bottom: 1px solid #eee;';
                row.innerHTML = `
                    <span style="color: #333;">Extra Bed (${extraBeds} bed${extraBeds > 1 ? 's' : ''} × ${item.nights} night${item.nights > 1 ? 's' : ''} @ ${formatPeso(extraBedCost)} — ${item.roomName || 'Room'})</span>
                    <strong style="color: #b2945b;">${formatPeso(itemExtraTotal)}</strong>
                `;
                extraBedRows.appendChild(row);
            });

            const roomsSubtotal = parseFloat(document.getElementById('rooms-subtotal')?.textContent.replace(/[₱,]/g, '') || '0');
            const serverTotal = parseFloat(totalEl.dataset.serverTotal || '0');
            const displayTotal = parseFloat(totalEl.dataset.displayTotal || '0');
            const baseTotal = Math.max(serverTotal, displayTotal, roomsSubtotal);
            const computedTotal = roomsSubtotal + extraBedTotal;

            if (computedTotal > baseTotal) {
                totalEl.textContent = formatPeso(computedTotal);
            }
        })();
    </script>
    <?php endif; ?>
</body>
</html>
