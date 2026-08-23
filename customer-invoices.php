<?php
$pageSeo = [
    'title' => 'My Invoices | BODARE Pension House',
    'description' => 'View your room billing invoices and payment status at BODARE Pension House.',
    'canonical_path' => 'customer-invoices.php',
    'robots' => 'noindex,nofollow',
];
$enableAds = false;
include __DIR__ . '/includes/site-head.php';
?>
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
        <h1>My Invoices</h1>
        <p>View your billing statements and payment balance</p>
    </div>
</section>

<main class="content-section">
    <div class="container">
        <div class="customer-dashboard-layout">
            <?php
            $customerSidebarActiveTab = 'invoices';
            include __DIR__ . '/includes/customer-sidebar.php';
            ?>

            <section class="customer-dashboard-content">
                <div id="invoices-list-container">
                    <div class="loading-message" style="text-align: center; padding: 2rem; color: #666;">
                        <p>Loading your invoices...</p>
                    </div>
                </div>

                <div id="invoice-detail-panel" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                        <h2 id="invoice-detail-title" style="margin: 0; color: #1a2238;">Invoice Details</h2>
                        <button type="button" id="invoice-back-btn" class="cta-button" style="background: #6c757d; padding: 0.5rem 1.25rem;">Back to List</button>
                    </div>
                    <div id="invoice-detail-content"></div>
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
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const listContainer = document.getElementById('invoices-list-container');
    const detailPanel = document.getElementById('invoice-detail-panel');
    const detailContent = document.getElementById('invoice-detail-content');
    const detailTitle = document.getElementById('invoice-detail-title');
    const backBtn = document.getElementById('invoice-back-btn');

    if (!localStorage.getItem('user')) {
        listContainer.innerHTML = '<div style="text-align:center;padding:3rem;"><p>Please log in to view your invoices.</p><a href="login.php" class="cta-button">Log In</a></div>';
        return;
    }

    backBtn.addEventListener('click', () => {
        detailPanel.style.display = 'none';
        listContainer.style.display = 'block';
    });

    const params = new URLSearchParams(window.location.search);
    const viewId = params.get('id');

    try {
        const session = await API.auth.check();
        if (!session.success || !session.logged_in) {
            window.location.href = 'login.php';
            return;
        }
    } catch (e) {
        window.location.href = 'login.php';
        return;
    }

    if (viewId) {
        await loadInvoiceDetail(viewId);
    } else {
        await loadInvoices();
    }

    async function loadInvoices() {
        try {
            const response = await API.invoice.getMyInvoices();
            if (!response.success) {
                throw new Error(response.message || 'Failed to load invoices');
            }

            const invoices = response.invoices || [];
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
                const statusClass = getStatusClass(inv.status);
                return `
                <div class="invoice-card" style="border:1px solid #ddd;border-radius:8px;padding:1.5rem;margin-bottom:1rem;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,0.06);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
                        <div>
                            <h3 style="margin:0 0 0.25rem;color:#333;">${escapeHtml(inv.invoice_number)}</h3>
                            <p style="margin:0;color:#666;font-size:0.9rem;">Issued: ${formatDate(inv.issued_at || inv.created_at)}</p>
                            ${inv.booking_number ? `<p style="margin:0.25rem 0 0;color:#888;font-size:0.85rem;">Booking: ${escapeHtml(inv.booking_number)}</p>` : ''}
                        </div>
                        <span class="status-badge ${statusClass}" style="padding:0.4rem 0.9rem;border-radius:20px;font-size:0.8rem;font-weight:600;text-transform:uppercase;">${escapeHtml(inv.status)}</span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:1rem;">
                        <div><strong style="color:#666;display:block;font-size:0.85rem;">Total</strong>₱${formatMoney(inv.total_amount)}</div>
                        <div><strong style="color:#666;display:block;font-size:0.85rem;">Paid</strong><span style="color:#059669;">₱${formatMoney(inv.amount_paid)}</span></div>
                        <div><strong style="color:#666;display:block;font-size:0.85rem;">Balance</strong><span style="color:#c62828;font-weight:600;">₱${formatMoney(inv.balance_due)}</span></div>
                        <div><strong style="color:#666;display:block;font-size:0.85rem;">Due Date</strong>${inv.due_date ? formatDate(inv.due_date) : '—'}</div>
                    </div>
                    <button type="button" class="cta-button view-invoice-btn" data-id="${inv.id}" style="padding:0.5rem 1.25rem;font-size:0.9rem;">View Details</button>
                </div>`;
            }).join('');

            listContainer.querySelectorAll('.view-invoice-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    const url = new URL(window.location.href);
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
        listContainer.style.display = 'none';
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

            detailTitle.textContent = 'Invoice ' + inv.invoice_number;

            const itemsHtml = items.map(item => `
                <tr>
                    <td style="padding:0.75rem;border-bottom:1px solid #eee;">${escapeHtml(item.description)}</td>
                    <td style="padding:0.75rem;border-bottom:1px solid #eee;text-align:right;">${item.quantity}</td>
                    <td style="padding:0.75rem;border-bottom:1px solid #eee;text-align:right;">₱${formatMoney(item.unit_price)}</td>
                    <td style="padding:0.75rem;border-bottom:1px solid #eee;text-align:right;">₱${formatMoney(item.total_price)}</td>
                </tr>
            `).join('');

            const paymentsHtml = payments.length > 0
                ? payments.map(p => `
                    <tr>
                        <td style="padding:0.5rem 0;">${p.payment_date ? formatDate(p.payment_date) : '—'}</td>
                        <td style="padding:0.5rem 0;">${escapeHtml(p.payment_method)}</td>
                        <td style="padding:0.5rem 0;text-align:right;">₱${formatMoney(p.amount)}</td>
                    </tr>`).join('')
                : '<tr><td colspan="3" style="padding:0.5rem 0;color:#999;">No payments recorded yet</td></tr>';

            detailContent.innerHTML = `
                <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:1.5rem;">
                    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
                        <div>
                            <p style="margin:0 0 0.25rem;"><strong>Bill To:</strong> ${escapeHtml(inv.guest_name)}</p>
                            ${inv.guest_email ? `<p style="margin:0;color:#666;">${escapeHtml(inv.guest_email)}</p>` : ''}
                        </div>
                        <div style="text-align:right;">
                            <p style="margin:0;"><strong>Status:</strong> ${escapeHtml(inv.status)}</p>
                            <p style="margin:0.25rem 0 0;color:#666;">Due: ${inv.due_date ? formatDate(inv.due_date) : 'Upon receipt'}</p>
                        </div>
                    </div>
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
                                <tr><td colspan="3" style="padding:0.5rem;text-align:right;">Subtotal</td><td style="padding:0.5rem;text-align:right;">₱${formatMoney(inv.subtotal)}</td></tr>
                                ${inv.discount_amount > 0 ? `<tr><td colspan="3" style="padding:0.5rem;text-align:right;">Discount</td><td style="padding:0.5rem;text-align:right;color:#c62828;">-₱${formatMoney(inv.discount_amount)}</td></tr>` : ''}
                                ${inv.tax_amount > 0 ? `<tr><td colspan="3" style="padding:0.5rem;text-align:right;">Tax</td><td style="padding:0.5rem;text-align:right;">₱${formatMoney(inv.tax_amount)}</td></tr>` : ''}
                                ${inv.service_charge_amount > 0 ? `<tr><td colspan="3" style="padding:0.5rem;text-align:right;">Service Charge</td><td style="padding:0.5rem;text-align:right;">₱${formatMoney(inv.service_charge_amount)}</td></tr>` : ''}
                                <tr><td colspan="3" style="padding:0.75rem;text-align:right;font-weight:bold;">Total</td><td style="padding:0.75rem;text-align:right;font-weight:bold;">₱${formatMoney(inv.total_amount)}</td></tr>
                                <tr><td colspan="3" style="padding:0.5rem;text-align:right;color:#059669;">Amount Paid</td><td style="padding:0.5rem;text-align:right;color:#059669;">₱${formatMoney(inv.amount_paid)}</td></tr>
                                <tr><td colspan="3" style="padding:0.75rem;text-align:right;font-weight:bold;color:#c62828;">Balance Due</td><td style="padding:0.75rem;text-align:right;font-weight:bold;color:#c62828;">₱${formatMoney(inv.balance_due)}</td></tr>
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
        } catch (error) {
            detailContent.innerHTML = `<div style="color:#c62828;padding:2rem;text-align:center;"><p>${escapeHtml(error.message || 'Failed to load invoice')}</p></div>`;
        }
    }

    function formatMoney(value) {
        return parseFloat(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(value) {
        if (!value) return '—';
        return new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function getStatusClass(status) {
        const map = {
            paid: 'status-confirmed',
            partial: 'status-pending',
            issued: 'status-pending',
            overdue: 'status-cancelled'
        };
        return map[status] || 'status-pending';
    }
});
</script>
<style>
    .status-badge.status-confirmed { background: #d4edda; color: #155724; }
    .status-badge.status-pending { background: #fff3cd; color: #856404; }
    .status-badge.status-cancelled { background: #f8d7da; color: #721c24; }
</style>
</body>
</html>
