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
            <?php
            $inv_refs = function ($inv) {
                $html = '';
                if ($inv->booking_number) {
                    $html .= '<span class="badge bg-info me-1">' . htmlspecialchars($inv->booking_number) . '</span>';
                }
                if (!empty($inv->event_name)) {
                    $html .= '<span class="badge bg-secondary">' . htmlspecialchars($inv->event_name) . '</span>';
                }
                return $html !== '' ? $html : '—';
            };
            ?>
            <div class="mob-desktop-table mob-desktop-table--xl">
            <div class="table-responsive dt-fit-wrap">
                <table class="table table-hover dt-fit-width" id="billingInvoicesTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th class="col-name">Guest</th>
                            <th>Status</th>
                            <th class="col-amount">Total</th>
                            <th class="col-amount">Paid</th>
                            <th class="col-amount">Balance</th>
                            <th>Due</th>
                            <th class="col-actions no-print" data-orderable="false"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
                        <tr>
                            <td class="fw-semibold"><?php echo htmlspecialchars($inv->invoice_number); ?></td>
                            <td class="col-name">
                                <div class="dt-cell-title"><?php echo htmlspecialchars($inv->guest_name); ?></div>
                                <div class="mt-1"><?php echo $inv_refs($inv); ?></div>
                            </td>
                            <td><span class="badge bg-secondary"><?php echo ucfirst($inv->status); ?></span></td>
                            <td class="col-amount" data-order="<?php echo (float) $inv->total_amount; ?>">₱<?php echo number_format($inv->total_amount, 2); ?></td>
                            <td class="col-amount" data-order="<?php echo (float) $inv->amount_paid; ?>">₱<?php echo number_format($inv->amount_paid, 2); ?></td>
                            <td class="col-amount fw-semibold" data-order="<?php echo (float) $inv->balance_due; ?>">₱<?php echo number_format($inv->balance_due, 2); ?></td>
                            <td data-order="<?php echo $inv->due_date ? date('Y-m-d', strtotime($inv->due_date)) : ''; ?>"><?php echo $inv->due_date ? date('M d, Y', strtotime($inv->due_date)) : '—'; ?></td>
                            <td class="col-actions no-print"><a href="<?php echo base_url('invoices/view/' . $inv->id); ?>" class="btn btn-sm btn-outline-primary" title="View invoice"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="8" class="text-center text-muted">No invoices in this period</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>

            <div class="mob-card-list mob-card-list--xl d-xl-none">
                <?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="mob-list-card-title"><?php echo htmlspecialchars($inv->guest_name); ?></div>
                            <div class="mob-list-card-meta text-truncate"><?php echo htmlspecialchars($inv->invoice_number); ?></div>
                        </div>
                        <span class="badge bg-secondary flex-shrink-0"><?php echo ucfirst($inv->status); ?></span>
                    </div>
                    <div class="mob-list-card-body">
                        <div class="mob-list-card-meta"><i class="bi bi-link-45deg"></i> <?php echo $inv_refs($inv); ?></div>
                        <div class="mob-list-card-meta"><i class="bi bi-calendar-x"></i> Due <?php echo $inv->due_date ? date('M d, Y', strtotime($inv->due_date)) : '—'; ?></div>
                        <div class="mob-list-card-meta d-flex justify-content-between mt-2">
                            <span>Total</span><span>₱<?php echo number_format($inv->total_amount, 2); ?></span>
                        </div>
                        <div class="mob-list-card-meta d-flex justify-content-between">
                            <span>Paid</span><span>₱<?php echo number_format($inv->amount_paid, 2); ?></span>
                        </div>
                        <div class="mob-list-card-meta d-flex justify-content-between fw-semibold text-body">
                            <span>Balance</span><span>₱<?php echo number_format($inv->balance_due, 2); ?></span>
                        </div>
                    </div>
                    <div class="mob-list-card-actions">
                        <a href="<?php echo base_url('invoices/view/' . $inv->id); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="mob-list-card mob-list-card-empty text-center text-muted py-4">No invoices in this period</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($outstanding)): ?>
    <div class="card card-bordered mb-4">
        <div class="card-inner">
            <h6 class="title mb-3 text-danger">Outstanding Receivables (All Dates)</h6>
            <div class="mob-desktop-table mob-desktop-table--xl">
            <div class="table-responsive dt-fit-wrap">
                <table class="table table-hover dt-fit-width" id="billingOutstandingTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th class="col-name">Guest</th>
                            <th>Status</th>
                            <th class="col-amount">Balance Due</th>
                            <th>Due Date</th>
                            <th class="col-actions no-print" data-orderable="false"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($outstanding as $inv): ?>
                        <tr>
                            <td class="fw-semibold"><?php echo htmlspecialchars($inv->invoice_number); ?></td>
                            <td class="col-name"><?php echo htmlspecialchars($inv->guest_name); ?></td>
                            <td><span class="badge bg-warning text-dark"><?php echo ucfirst($inv->status); ?></span></td>
                            <td class="col-amount" data-order="<?php echo (float) $inv->balance_due; ?>"><strong class="text-danger">₱<?php echo number_format($inv->balance_due, 2); ?></strong></td>
                            <td data-order="<?php echo $inv->due_date ? date('Y-m-d', strtotime($inv->due_date)) : ''; ?>"><?php echo $inv->due_date ? date('M d, Y', strtotime($inv->due_date)) : '—'; ?></td>
                            <td class="col-actions no-print"><a href="<?php echo base_url('invoices/view/' . $inv->id); ?>" class="btn btn-sm btn-outline-primary" title="View invoice"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            </div>

            <div class="mob-card-list mob-card-list--xl d-xl-none">
                <?php foreach ($outstanding as $inv): ?>
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="mob-list-card-title"><?php echo htmlspecialchars($inv->guest_name); ?></div>
                            <div class="mob-list-card-meta text-truncate"><?php echo htmlspecialchars($inv->invoice_number); ?></div>
                        </div>
                        <span class="badge bg-warning text-dark flex-shrink-0"><?php echo ucfirst($inv->status); ?></span>
                    </div>
                    <div class="mob-list-card-body">
                        <div class="mob-list-card-meta"><i class="bi bi-calendar-x"></i> Due <?php echo $inv->due_date ? date('M d, Y', strtotime($inv->due_date)) : '—'; ?></div>
                        <div class="mob-list-card-meta d-flex justify-content-between fw-semibold mt-2">
                            <span>Balance Due</span><span class="text-danger">₱<?php echo number_format($inv->balance_due, 2); ?></span>
                        </div>
                    </div>
                    <div class="mob-list-card-actions">
                        <a href="<?php echo base_url('invoices/view/' . $inv->id); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
@media print {
    .no-print, .nk-sidebar, .nk-header, .nk-block-tools { display: none !important; }
    .print-header { display: block !important; }

    /* Always print the tables, never the on-screen card lists */
    .mob-desktop-table.mob-desktop-table--xl { display: block !important; }
    .mob-card-list.mob-card-list--xl { display: none !important; }
}
.card-inner .amount { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.card-inner .title { color: #64748b; font-size: 0.85rem; margin-bottom: 0.35rem; }
</style>
