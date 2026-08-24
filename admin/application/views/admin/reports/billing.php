<div class="nk-block">
    <div class="print-header" style="display: block; text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #e2e8f0;">
        <h1 style="font-size: 28px; margin: 0 0 5px 0; font-weight: bold; color: #1e293b;">BODARE PENSION HOUSE</h1>
        <div class="report-date" style="font-size: 16px; color: #64748b; margin-bottom: 5px;">
            Billing &amp; Collections Report —
            <?php echo date('F d, Y', strtotime($from_date)); ?>
            <?php if ($from_date !== $to_date): ?> to <?php echo date('F d, Y', strtotime($to_date)); ?><?php endif; ?>
        </div>
        <div style="font-size: 12px; color: #94a3b8;">Generated on: <?php echo date('F d, Y h:i A'); ?></div>
    </div>

    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-receipt"></i> Billing &amp; Collections</h3>
                <div class="nk-block-des text-soft">
                    <p>Invoices billed, payments collected, and outstanding balances</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <ul class="nk-block-tools g-3">
                    <li>
                        <a href="<?php echo base_url('reports/export_billing?from_date=' . urlencode($from_date) . '&to_date=' . urlencode($to_date)); ?>" class="btn btn-success">
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
            <form method="get" action="<?php echo base_url('reports/billing'); ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4 no-print">
        <div class="col-md-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">Invoices</h6>
                    <div class="amount"><?php echo (int) $invoice_summary['invoice_count']; ?></div>
                    <small class="text-muted"><?php echo (int) $invoice_summary['paid_count']; ?> paid · <?php echo (int) $invoice_summary['partial_count']; ?> partial · <?php echo (int) $invoice_summary['unpaid_count']; ?> unpaid</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">Amount Billed</h6>
                    <div class="amount">₱<?php echo number_format($invoice_summary['total_billed'], 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bordered border-success">
                <div class="card-inner">
                    <h6 class="title">Payments Collected</h6>
                    <div class="amount text-success">₱<?php echo number_format($payments_collected, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">Outstanding Balance</h6>
                    <div class="amount text-danger">₱<?php echo number_format($invoice_summary['total_balance'], 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php if (!empty($payments_by_method)): ?>
        <div class="col-md-6">
            <div class="card card-bordered h-100">
                <div class="card-inner">
                    <h6 class="title mb-3">Collections by Payment Method</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead><tr><th>Method</th><th>Count</th><th>Amount</th></tr></thead>
                            <tbody>
                                <?php foreach ($payments_by_method as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $row->payment_method))); ?></td>
                                    <td><?php echo (int) $row->payment_count; ?></td>
                                    <td><strong>₱<?php echo number_format($row->total_amount, 2); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($item_totals)): ?>
        <div class="col-md-6">
            <div class="card card-bordered h-100">
                <div class="card-inner">
                    <h6 class="title mb-3">Billed by Charge Type</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead><tr><th>Type</th><th>Items</th><th>Amount</th></tr></thead>
                            <tbody>
                                <?php foreach ($item_totals as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $row->item_type))); ?></td>
                                    <td><?php echo (int) $row->item_count; ?></td>
                                    <td><strong>₱<?php echo number_format($row->total_amount, 2); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="card card-bordered mb-4">
        <div class="card-inner">
            <h6 class="title mb-3">Invoices in Period</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Guest</th>
                            <th>Booking / Event</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Due</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($inv->invoice_number); ?></strong></td>
                            <td><?php echo htmlspecialchars($inv->guest_name); ?></td>
                            <td>
                                <?php if ($inv->booking_number): ?><span class="badge bg-info"><?php echo htmlspecialchars($inv->booking_number); ?></span><?php endif; ?>
                                <?php if (!empty($inv->event_name)): ?><span class="badge bg-secondary"><?php echo htmlspecialchars($inv->event_name); ?></span><?php endif; ?>
                                <?php if (!$inv->booking_number && empty($inv->event_name)): ?>—<?php endif; ?>
                            </td>
                            <td><span class="badge bg-secondary"><?php echo ucfirst($inv->status); ?></span></td>
                            <td>₱<?php echo number_format($inv->total_amount, 2); ?></td>
                            <td>₱<?php echo number_format($inv->amount_paid, 2); ?></td>
                            <td><strong>₱<?php echo number_format($inv->balance_due, 2); ?></strong></td>
                            <td><?php echo $inv->due_date ? date('M d, Y', strtotime($inv->due_date)) : '—'; ?></td>
                            <td><a href="<?php echo base_url('invoices/view/' . $inv->id); ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="9" class="text-center text-muted">No invoices in this period</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (!empty($outstanding)): ?>
    <div class="card card-bordered mb-4">
        <div class="card-inner">
            <h6 class="title mb-3 text-danger">Outstanding Receivables (All Dates)</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Guest</th>
                            <th>Status</th>
                            <th>Balance Due</th>
                            <th>Due Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($outstanding as $inv): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($inv->invoice_number); ?></td>
                            <td><?php echo htmlspecialchars($inv->guest_name); ?></td>
                            <td><span class="badge bg-warning text-dark"><?php echo ucfirst($inv->status); ?></span></td>
                            <td><strong class="text-danger">₱<?php echo number_format($inv->balance_due, 2); ?></strong></td>
                            <td><?php echo $inv->due_date ? date('M d, Y', strtotime($inv->due_date)) : '—'; ?></td>
                            <td><a href="<?php echo base_url('invoices/view/' . $inv->id); ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
@media print {
    .no-print, .nk-sidebar, .nk-header, .nk-block-tools { display: none !important; }
    .print-header { display: block !important; }
}
.card-inner .amount { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.card-inner .title { color: #64748b; font-size: 0.85rem; margin-bottom: 0.35rem; }
</style>
