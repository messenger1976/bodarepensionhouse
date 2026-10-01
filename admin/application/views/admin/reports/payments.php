<div class="nk-block">
    <div class="print-header" style="display: block; text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #e2e8f0;">
        <h1 style="font-size: 28px; margin: 0 0 5px 0; font-weight: bold; color: #1e293b;">BODARE PENSION HOUSE</h1>
        <div class="report-date" style="font-size: 16px; color: #64748b; margin-bottom: 5px;">
            Payments Report —
            <?php echo date('F d, Y', strtotime($from_date)); ?>
            <?php if ($from_date !== $to_date): ?> to <?php echo date('F d, Y', strtotime($to_date)); ?><?php endif; ?>
        </div>
        <div style="font-size: 12px; color: #94a3b8;">Generated on: <?php echo date('F d, Y h:i A'); ?></div>
    </div>

    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-cash-coin"></i> Payments Report</h3>
                <div class="nk-block-des text-soft"><p>Cash, card, GCash, and bank transfer collections</p></div>
            </div>
            <div class="nk-block-head-content">
                <ul class="nk-block-tools g-3">
                    <li>
                        <a href="<?php echo base_url('reports/export_payments?from_date=' . urlencode($from_date) . '&to_date=' . urlencode($to_date) . ($filter_status ? '&status=' . urlencode($filter_status) : '')); ?>" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </a>
                    </li>
                    <li>
                        <button onclick="window.print()" class="btn btn-outline-light"><i class="bi bi-printer"></i> Print</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card card-bordered mb-4 no-print">
        <div class="card-inner">
            <form method="get" action="<?php echo base_url('reports/payments'); ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <?php foreach (array('paid','pending','refunded','failed') as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo ($filter_status === $st) ? 'selected' : ''; ?>><?php echo ucfirst($st); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Generate</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4 no-print">
        <div class="col-md-4">
            <div class="card card-bordered border-success">
                <div class="card-inner">
                    <h6 class="title">Total Collected (Paid)</h6>
                    <div class="amount text-success">₱<?php echo number_format($payments_collected, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">Transactions Listed</h6>
                    <div class="amount"><?php echo (int) $payment_count; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">Payment Methods Used</h6>
                    <div class="amount"><?php echo count($payments_by_method); ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($payments_by_method)): ?>
    <div class="card card-bordered mb-4">
        <div class="card-inner">
            <h6 class="title mb-3">By Payment Method (Paid only)</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Method</th><th>Count</th><th>Amount</th></tr></thead>
                    <tbody>
                        <?php foreach ($payments_by_method as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $row->payment_method))); ?></td>
                            <td><span class="badge bg-info"><?php echo (int) $row->payment_count; ?></span></td>
                            <td><strong>₱<?php echo number_format($row->total_amount, 2); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card card-bordered">
        <div class="card-inner">
            <h6 class="title mb-3">Payment Transactions</h6>
            <?php
            $pay_badge = function ($status) {
                if ($status === 'paid') return 'success';
                if ($status === 'failed') return 'danger';
                return 'warning';
            };
            $pay_ts = function ($p) {
                return strtotime($p->payment_date ? $p->payment_date : $p->created_at);
            };
            $pay_reference = function ($p) {
                return $p->reference_number ?: ($p->transaction_id ?: '');
            };
            ?>
            <div class="mob-desktop-table mob-desktop-table--xl">
            <div class="table-responsive dt-fit-wrap">
                <table class="table table-hover dt-fit-width" id="paymentsReportTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="col-name">Guest</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th class="col-amount">Amount</th>
                            <th class="col-actions no-print" data-orderable="false"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments)): foreach ($payments as $p): ?>
                        <?php $ts = $pay_ts($p); $ref = $pay_reference($p); ?>
                        <tr>
                            <td data-order="<?php echo date('Y-m-d H:i:s', $ts); ?>">
                                <div><?php echo date('M d, Y', $ts); ?></div>
                                <?php if ($p->payment_date): ?>
                                <div class="dt-cell-sub text-muted"><?php echo date('h:i A', $ts); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="col-name">
                                <div class="dt-cell-title"><?php echo htmlspecialchars($p->guest_name ?: '—'); ?></div>
                                <div class="dt-cell-sub text-muted">
                                    Booking <?php echo htmlspecialchars($p->booking_number ?: '—'); ?>
                                    &middot; Invoice <?php echo htmlspecialchars($p->invoice_number ?: '—'); ?>
                                </div>
                            </td>
                            <td>
                                <div><?php echo ucfirst(str_replace('_', ' ', $p->payment_method)); ?></div>
                                <?php if ($ref !== ''): ?>
                                <div class="dt-cell-sub text-muted" title="<?php echo htmlspecialchars($ref); ?>">Ref: <?php echo htmlspecialchars($ref); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-<?php echo $pay_badge($p->payment_status); ?>"><?php echo ucfirst($p->payment_status); ?></span></td>
                            <td class="col-amount fw-semibold" data-order="<?php echo (float) $p->amount; ?>">₱<?php echo number_format($p->amount, 2); ?></td>
                            <td class="col-actions no-print"><a href="<?php echo base_url('payments/view/' . $p->id); ?>" class="btn btn-sm btn-outline-primary" title="View payment"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No payments found for this period</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>

            <div class="mob-card-list mob-card-list--xl d-xl-none">
                <?php if (!empty($payments)): foreach ($payments as $p): ?>
                <?php $ts = $pay_ts($p); $ref = $pay_reference($p); ?>
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="mob-list-card-title"><?php echo htmlspecialchars($p->guest_name ?: '—'); ?></div>
                            <div class="mob-list-card-meta"><?php echo $p->payment_date ? date('M d, Y h:i A', $ts) : date('M d, Y', $ts); ?></div>
                        </div>
                        <span class="badge bg-<?php echo $pay_badge($p->payment_status); ?> flex-shrink-0"><?php echo ucfirst($p->payment_status); ?></span>
                    </div>
                    <div class="mob-list-card-body">
                        <div class="mob-list-card-meta"><i class="bi bi-credit-card"></i> <?php echo ucfirst(str_replace('_', ' ', $p->payment_method)); ?></div>
                        <div class="mob-list-card-meta text-truncate">
                            <i class="bi bi-link-45deg"></i>
                            Booking <?php echo htmlspecialchars($p->booking_number ?: '—'); ?>
                            &middot; Invoice <?php echo htmlspecialchars($p->invoice_number ?: '—'); ?>
                        </div>
                        <?php if ($ref !== ''): ?>
                        <div class="mob-list-card-meta"><i class="bi bi-hash"></i> <span class="dt-break"><?php echo htmlspecialchars($ref); ?></span></div>
                        <?php endif; ?>
                        <div class="mob-list-card-meta mt-2">
                            <span class="fw-semibold fs-6 text-body">₱<?php echo number_format($p->amount, 2); ?></span>
                        </div>
                    </div>
                    <div class="mob-list-card-actions">
                        <a href="<?php echo base_url('payments/view/' . $p->id); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="mob-list-card mob-list-card-empty text-center text-muted py-4">No payments found for this period</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, .nk-sidebar, .nk-header, .nk-block-tools { display: none !important; }

    /* Always print the table, never the on-screen card list */
    .mob-desktop-table.mob-desktop-table--xl { display: block !important; }
    .mob-card-list.mob-card-list--xl { display: none !important; }
    table.dt-fit-width .dt-cell-sub { max-width: none !important; white-space: normal !important; overflow: visible !important; }
}
.card-inner .amount { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.card-inner .title { color: #64748b; font-size: 0.85rem; margin-bottom: 0.35rem; }
</style>
