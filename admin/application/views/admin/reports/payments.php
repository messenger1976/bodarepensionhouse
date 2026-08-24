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
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Guest</th>
                            <th>Booking</th>
                            <th>Invoice</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments)): foreach ($payments as $p): ?>
                        <tr>
                            <td><?php echo $p->payment_date ? date('M d, Y h:i A', strtotime($p->payment_date)) : date('M d, Y', strtotime($p->created_at)); ?></td>
                            <td><?php echo htmlspecialchars($p->guest_name ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($p->booking_number ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($p->invoice_number ?: '—'); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $p->payment_method)); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $p->payment_status === 'paid' ? 'success' : ($p->payment_status === 'failed' ? 'danger' : 'warning'); ?>">
                                    <?php echo ucfirst($p->payment_status); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($p->reference_number ?: ($p->transaction_id ?: '—')); ?></td>
                            <td class="text-end"><strong>₱<?php echo number_format($p->amount, 2); ?></strong></td>
                            <td><a href="<?php echo base_url('payments/view/' . $p->id); ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="9" class="text-center text-muted">No payments found for this period</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, .nk-sidebar, .nk-header, .nk-block-tools { display: none !important; }
}
.card-inner .amount { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.card-inner .title { color: #64748b; font-size: 0.85rem; margin-bottom: 0.35rem; }
</style>
