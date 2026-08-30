<?php
require_once __DIR__ . '/includes/site-config.php';

$bookingNumber = isset($_GET['booking']) ? trim((string) $_GET['booking']) : '';
$confirmation = $bookingNumber !== '' ? bodare_get_booking_confirmation($bookingNumber) : null;
$paymentFlag = isset($_GET['payment']) ? strtolower(trim((string) $_GET['payment'])) : '';
$sessionId = isset($_GET['session_id']) ? trim((string) $_GET['session_id']) : '';
$invoiceIdParam = isset($_GET['invoice']) ? trim((string) $_GET['invoice']) : '';
$paymentStatusParam = isset($_GET['status']) ? strtolower(trim((string) $_GET['status'])) : '';
$showQrph = ($paymentFlag === 'qrph' && $bookingNumber !== '');
$awaitingPaymentVerify = (
    ($paymentFlag === 'success' && $bookingNumber !== '')
    || ($paymentFlag === 'card' && $paymentStatusParam === 'success' && $bookingNumber !== '')
);
$isPendingBooking = $confirmation && strtolower((string) ($confirmation['booking']['status'] ?? '')) === 'pending';

$pageSeo = [
    'title' => 'Booking Confirmation | BODARE Pension House',
    'description' => 'Your booking confirmation details for BODARE Pension House.',
    'canonical_path' => 'booking-confirmation.php',
    'robots' => 'noindex,nofollow',
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


    <section class="app-page-hero">
        <div class="page-header-content app-section" style="padding-top:0;padding-bottom:0;">
            <h1 id="confirmation-page-title"><?php
                if ($showQrph) {
                    echo 'Scan to Pay';
                } elseif ($awaitingPaymentVerify) {
                    echo 'Payment Processing';
                } else {
                    echo 'Booking Confirmed!';
                }
            ?></h1>
            <p id="confirmation-page-subtitle"><?php
                if ($showQrph) {
                    echo 'Use GCash or any QR Ph app to complete payment';
                } elseif ($awaitingPaymentVerify) {
                    echo 'Confirming your payment…';
                } else {
                    echo 'Thank you for your reservation';
                }
            ?></p>
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
                    <div id="confirmation-banner" style="background: <?php echo $showQrph ? '#e8f4fd' : '#d4edda'; ?>; border: 1px solid <?php echo $showQrph ? '#b6d9f2' : '#c3e6cb'; ?>; border-radius: 8px; padding: 2rem; margin-bottom: 2rem; text-align: center;">
                        <h2 id="confirmation-banner-title" style="color: <?php echo $showQrph ? '#0c5460' : '#155724'; ?>; margin: 0 0 1rem 0;">
                            <?php
                            if ($showQrph) {
                                echo 'Scan the QR Ph code to pay with GCash';
                            } elseif ($awaitingPaymentVerify) {
                                echo 'Confirming your payment…';
                            } else {
                                echo '✓ Your booking has been confirmed!';
                            }
                            ?>
                        </h2>
                        <p style="color: <?php echo $showQrph ? '#0c5460' : '#155724'; ?>; margin: 0; font-size: 1.125rem;">Booking Number: <strong><?php echo $bookingNumberSafe; ?></strong></p>
                        <p id="confirmation-payment-status" style="color: <?php echo $showQrph ? '#0c5460' : '#155724'; ?>; margin: 0.75rem 0 0; font-size: 0.95rem;">
                            <?php if ($showQrph): ?>
                                Your booking is pending until QR Ph payment is completed.
                            <?php elseif ($awaitingPaymentVerify): ?>
                                Please wait while we verify your PayMongo payment.
                            <?php elseif (strtolower((string) ($booking['status'] ?? '')) === 'pending'): ?>
                                Status: Pending — our team will confirm your reservation shortly.
                            <?php endif; ?>
                        </p>
                    </div>

                    <div id="qrph-payment-panel" style="display: <?php echo $showQrph ? 'block' : 'none'; ?>; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <h3 style="margin-top: 0; color: #1a2238;">QR Ph Payment</h3>
                        <p style="color: #666; margin-bottom: 1rem;">Open GCash → Scan QR, or use any bank / e-wallet that supports QR Ph.</p>
                        <div id="qrph-image-wrap" style="min-height: 220px; display: flex; align-items: center; justify-content: center;">
                            <p id="qrph-loading" style="color: #666;">Loading QR code…</p>
                            <img id="qrph-image" alt="QR Ph payment code" style="display:none; max-width: 280px; width: 100%; height: auto; border: 1px solid #eee; border-radius: 8px;">
                        </div>
                        <p id="qrph-amount" style="font-size: 1.25rem; font-weight: 700; color: #b2945b; margin: 1rem 0 0.5rem;"></p>
                        <p id="qrph-expiry" style="color: #666; font-size: 0.9rem; margin: 0;"></p>
                        <p id="qrph-poll-status" style="color: #0c5460; font-size: 0.95rem; margin: 0.75rem 0 0;">Waiting for payment…</p>
                        <div id="qrph-test-panel" style="display:none; margin-top: 1rem; padding: 1rem; background: #fff8e6; border: 1px dashed #c9a227; border-radius: 8px; text-align: left;">
                            <strong style="color: #856404; display:block; margin-bottom: 0.35rem;">Test mode</strong>
                            <p style="color: #856404; font-size: 0.9rem; margin: 0 0 0.75rem;">Do not scan this QR with a real banking app. Open PayMongo’s simulator and choose Authorize / Paid or Fail.</p>
                            <a id="qrph-test-url" href="#" target="_blank" rel="noopener" class="cta-button" style="display:inline-block; text-decoration:none;">Simulate QR Ph payment</a>
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: center; margin-top: 1.25rem;">
                            <button type="button" id="qrph-regenerate-btn" class="cta-button-secondary" style="display:none;">Generate New QR</button>
                            <a id="qrph-invoice-link" href="customer-dashboard.php?tab=invoices" class="cta-button" style="background: #fff; color: #1a2238; border: 1px solid #cfc4b0; text-decoration: none;">View invoice / pay later</a>
                        </div>
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
                            <li>Your booking is currently <strong><?php echo $status; ?></strong>.<?php echo $showQrph ? ' Complete QR Ph payment above to confirm.' : ' You will receive a confirmation email shortly.'; ?></li>
                            <li>Please arrive at the hotel during check-in hours (typically 2:00 PM).</li>
                            <li>If you need to modify or cancel your booking, please contact us using your booking number.</li>
                            <li><?php echo $showQrph ? 'You can also pay later from My Account → My Invoices.' : 'Payment will be collected at the hotel upon check-in.'; ?></li>
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
            const invoiceIdParam = <?php echo json_encode($invoiceIdParam); ?>;
            const shouldVerifyPayment = <?php echo $awaitingPaymentVerify ? 'true' : 'false'; ?>;
            const showQrph = <?php echo $showQrph ? 'true' : 'false'; ?>;
            const displayTotal = <?php echo json_encode((float) ($confirmation['display_total'] ?? 0)); ?>;

            const banner = document.getElementById('confirmation-banner');
            const bannerTitle = document.getElementById('confirmation-banner-title');
            const statusEl = document.getElementById('confirmation-payment-status');
            const pageTitle = document.getElementById('confirmation-page-title');
            const pageSubtitle = document.getElementById('confirmation-page-subtitle');

            const isCardReturn = <?php echo ($paymentFlag === 'card') ? 'true' : 'false'; ?>;

            function markPaidUI() {
                const methodLabel = isCardReturn ? 'Card' : 'QR Ph';
                if (pageTitle) pageTitle.textContent = 'Payment Successful!';
                if (pageSubtitle) pageSubtitle.textContent = 'Your ' + methodLabel + ' payment was received';
                if (banner) {
                    banner.style.background = '#d4edda';
                    banner.style.borderColor = '#c3e6cb';
                }
                if (bannerTitle) {
                    bannerTitle.style.color = '#155724';
                    bannerTitle.textContent = '✓ Payment received — booking confirmed!';
                }
                if (statusEl) {
                    statusEl.style.color = '#155724';
                    statusEl.textContent = 'Paid securely via PayMongo ' + methodLabel + '.';
                }
                const panel = document.getElementById('qrph-payment-panel');
                if (panel) {
                    panel.style.display = 'block';
                    panel.innerHTML = '<h3 style="margin-top:0;color:#155724;">Payment complete</h3><p style="color:#155724;">Thank you. Your invoice is now marked as paid.</p>';
                }
                sessionStorage.removeItem('paymongo_session_id');
                sessionStorage.removeItem('paymongo_booking_number');
                sessionStorage.removeItem('paymongo_payment_intent_id');
                sessionStorage.removeItem('paymongo_qrph_payment');
                try { localStorage.removeItem('bookingCart'); } catch (e) {}
                try { localStorage.removeItem('cartServices'); } catch (e) {}
            }

            function formatPeso(amount) {
                return '₱' + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function renderQrph(payment) {
                const panel = document.getElementById('qrph-payment-panel');
                const img = document.getElementById('qrph-image');
                const loading = document.getElementById('qrph-loading');
                const amountEl = document.getElementById('qrph-amount');
                const expiryEl = document.getElementById('qrph-expiry');
                const pollEl = document.getElementById('qrph-poll-status');
                const regenBtn = document.getElementById('qrph-regenerate-btn');
                const invoiceLink = document.getElementById('qrph-invoice-link');
                if (!panel) return;

                panel.style.display = 'block';
                const qrUrl = payment.qr_image_url || payment.qrImageUrl;
                if (img && qrUrl) {
                    img.src = qrUrl;
                    img.style.display = 'block';
                    if (loading) loading.style.display = 'none';
                } else if (loading) {
                    loading.textContent = 'QR code unavailable. Tap Generate New QR.';
                }

                if (amountEl) {
                    amountEl.textContent = 'Amount due: ' + formatPeso(payment.amount != null ? payment.amount : displayTotal);
                }
                if (expiryEl && payment.expires_at) {
                    expiryEl.textContent = 'QR expires: ' + new Date(payment.expires_at.replace(' ', 'T')).toLocaleString();
                    const expTs = Date.parse(payment.expires_at.replace(' ', 'T'));
                    if (regenBtn) {
                        regenBtn.style.display = (!expTs || expTs <= Date.now()) ? 'inline-block' : 'none';
                    }
                } else if (regenBtn) {
                    regenBtn.style.display = payment.can_regenerate ? 'inline-block' : 'none';
                }

                const invId = payment.invoice_id || invoiceIdParam;
                if (invoiceLink) {
                    invoiceLink.href = invId
                        ? ('customer-dashboard.php?tab=invoices&id=' + encodeURIComponent(invId))
                        : 'customer-dashboard.php?tab=invoices';
                }
                if (pollEl) pollEl.textContent = 'Waiting for payment… this page updates automatically.';

                if (payment.payment_intent_id) {
                    sessionStorage.setItem('paymongo_payment_intent_id', payment.payment_intent_id);
                }

                const testPanel = document.getElementById('qrph-test-panel');
                const testLink = document.getElementById('qrph-test-url');
                if (testPanel && testLink) {
                    if (payment.test_url) {
                        testLink.href = payment.test_url;
                        testPanel.style.display = 'block';
                    } else {
                        testPanel.style.display = 'none';
                    }
                }
            }

            let pollTimer = null;
            async function pollQrphPayment(intentId) {
                if (typeof API === 'undefined' || !API.payment) return;
                try {
                    const result = await API.payment.verify({
                        booking_number: bookingNumber,
                        payment_intent_id: intentId || sessionStorage.getItem('paymongo_payment_intent_id') || ''
                    });
                    if (result && result.paid) {
                        if (pollTimer) clearInterval(pollTimer);
                        markPaidUI();
                        return;
                    }
                    const pollEl = document.getElementById('qrph-poll-status');
                    if (pollEl) {
                        pollEl.textContent = 'Waiting for payment… status: ' + (result.intent_status || result.status || 'pending');
                    }
                    if (result && result.online_payment && result.online_payment.status === 'expired') {
                        const regenBtn = document.getElementById('qrph-regenerate-btn');
                        if (regenBtn) regenBtn.style.display = 'inline-block';
                        if (pollEl) pollEl.textContent = 'QR code expired. Generate a new QR to continue.';
                    }
                } catch (error) {
                    console.error('QRPH poll failed', error);
                }
            }

            async function initQrph() {
                if (!showQrph || typeof API === 'undefined' || !API.payment) {
                    return;
                }

                let payment = null;
                try {
                    payment = JSON.parse(sessionStorage.getItem('paymongo_qrph_payment') || 'null');
                } catch (e) {
                    payment = null;
                }

                if (!payment || !payment.qr_image_url) {
                    try {
                        const result = await API.payment.createQrph({ booking_number: bookingNumber });
                        if (result.already_paid) {
                            markPaidUI();
                            return;
                        }
                        payment = result.payment || result;
                        try { sessionStorage.setItem('paymongo_qrph_payment', JSON.stringify(payment)); } catch (e) {}
                    } catch (error) {
                        const loading = document.getElementById('qrph-loading');
                        if (loading) loading.textContent = error.message || 'Unable to load QR code.';
                        const regenBtn = document.getElementById('qrph-regenerate-btn');
                        if (regenBtn) regenBtn.style.display = 'inline-block';
                        return;
                    }
                }

                renderQrph(payment);
                const intentId = payment.payment_intent_id || '';
                pollQrphPayment(intentId);
                pollTimer = setInterval(function () { pollQrphPayment(intentId); }, 5000);

                const regenBtn = document.getElementById('qrph-regenerate-btn');
                if (regenBtn) {
                    regenBtn.addEventListener('click', async function () {
                        regenBtn.disabled = true;
                        regenBtn.textContent = 'Generating…';
                        try {
                            const result = await API.payment.createQrph({ booking_number: bookingNumber, regenerate: true });
                            if (result.already_paid) {
                                markPaidUI();
                                return;
                            }
                            payment = result.payment || result;
                            try { sessionStorage.setItem('paymongo_qrph_payment', JSON.stringify(payment)); } catch (e) {}
                            renderQrph(payment);
                            if (pollTimer) clearInterval(pollTimer);
                            pollTimer = setInterval(function () {
                                pollQrphPayment(payment.payment_intent_id || '');
                            }, 5000);
                        } catch (error) {
                            alert(error.message || 'Unable to regenerate QR.');
                        } finally {
                            regenBtn.disabled = false;
                            regenBtn.textContent = 'Generate New QR';
                        }
                    });
                }
            }

            let verifyPollTimer = null;
            let verifyAttempts = 0;
            const maxVerifyAttempts = 24; // ~2 minutes at 5s

            async function verifyPaymongoPayment() {
                if (!shouldVerifyPayment || typeof API === 'undefined' || !API.payment) {
                    return false;
                }
                try {
                    const result = await API.payment.verify({
                        booking_number: bookingNumber,
                        session_id: sessionId || sessionStorage.getItem('paymongo_session_id') || '',
                        payment_intent_id: sessionStorage.getItem('paymongo_payment_intent_id') || ''
                    });
                    if (result && result.paid) {
                        if (verifyPollTimer) clearInterval(verifyPollTimer);
                        markPaidUI();
                        // Soft-refresh status text from server booking page if status badge exists
                        const statusBadge = document.getElementById('booking-status-display');
                        if (statusBadge && result.status) {
                            statusBadge.textContent = result.status;
                        }
                        return true;
                    }

                    if (pageTitle) pageTitle.textContent = 'Booking Received';
                    if (pageSubtitle) pageSubtitle.textContent = 'Payment still pending';
                    if (banner) {
                        banner.style.background = '#fff3cd';
                        banner.style.borderColor = '#ffeeba';
                    }
                    if (bannerTitle) {
                        bannerTitle.style.color = '#856404';
                        bannerTitle.textContent = 'Payment not confirmed yet';
                    }
                    if (statusEl) {
                        statusEl.style.color = '#856404';
                        statusEl.textContent = isCardReturn
                            ? 'Card payment is processing. This page will update automatically…'
                            : 'If you already paid, it may take a moment. You can also pay from My Invoices.';
                    }
                    return false;
                } catch (error) {
                    console.error('Payment verify failed', error);
                    if (statusEl) {
                        statusEl.textContent = 'We could not verify payment automatically. Your booking number is saved — please contact us if you already paid.';
                    }
                    return false;
                }
            }

            initQrph();
            verifyPaymongoPayment().then(function (paid) {
                if (paid || !shouldVerifyPayment) return;
                verifyPollTimer = setInterval(async function () {
                    verifyAttempts += 1;
                    const done = await verifyPaymongoPayment();
                    if (done || verifyAttempts >= maxVerifyAttempts) {
                        clearInterval(verifyPollTimer);
                        if (!done && statusEl && isCardReturn) {
                            statusEl.textContent = 'Still confirming payment. Refresh this page in a moment, or contact us with your booking number if you were charged.';
                        }
                    }
                }, 5000);
            });

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

            const formatPesoLocal = (amount) => `₱${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
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
                    <span style="color: #333;">Extra Bed (${extraBeds} bed${extraBeds > 1 ? 's' : ''} × ${item.nights} night${item.nights > 1 ? 's' : ''} @ ${formatPesoLocal(extraBedCost)} — ${item.roomName || 'Room'})</span>
                    <strong style="color: #b2945b;">${formatPesoLocal(itemExtraTotal)}</strong>
                `;
                extraBedRows.appendChild(row);
            });

            const roomsSubtotal = parseFloat(document.getElementById('rooms-subtotal')?.textContent.replace(/[₱,]/g, '') || '0');
            const serverTotal = parseFloat(totalEl.dataset.serverTotal || '0');
            const displayTotalStored = parseFloat(totalEl.dataset.displayTotal || '0');
            const baseTotal = Math.max(serverTotal, displayTotalStored, roomsSubtotal);
            const computedTotal = roomsSubtotal + extraBedTotal;

            if (computedTotal > baseTotal) {
                totalEl.textContent = formatPesoLocal(computedTotal);
            }
        })();
    </script>
    <?php endif; ?>
</body>
</html>
