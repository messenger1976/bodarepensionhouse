<div class="nk-block">
    <div class="print-header" style="display: block; text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #e2e8f0;">
        <h1 style="font-size: 28px; margin: 0 0 5px 0; font-weight: bold; color: #1e293b;">BODARE PENSION HOUSE</h1>
        <div class="report-date" style="font-size: 16px; color: #64748b; margin-bottom: 5px;">
            Events Revenue Report —
            <?php echo date('F d, Y', strtotime($from_date)); ?>
            <?php if ($from_date !== $to_date): ?> to <?php echo date('F d, Y', strtotime($to_date)); ?><?php endif; ?>
        </div>
        <div style="font-size: 12px; color: #94a3b8;">Generated on: <?php echo date('F d, Y h:i A'); ?></div>
    </div>

    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-calendar-event"></i> Events Revenue</h3>
                <div class="nk-block-des text-soft"><p>Hotel events, packages, and guest counts for the selected period</p></div>
            </div>
            <div class="nk-block-head-content">
                <button onclick="window.print()" class="btn btn-outline-light"><i class="bi bi-printer"></i> Print</button>
            </div>
        </div>
    </div>

    <div class="card card-bordered mb-4 no-print">
        <div class="card-inner">
            <form method="get" action="<?php echo base_url('reports/events'); ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Generate</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4 no-print">
        <div class="col-md-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">Events</h6>
                    <div class="amount"><?php echo (int) $summary['event_count']; ?></div>
                    <small class="text-muted"><?php echo (int) $summary['confirmed_count']; ?> confirmed · <?php echo (int) $summary['completed_count']; ?> completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">Package Revenue</h6>
                    <div class="amount">₱<?php echo number_format($summary['total_amount'], 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">Expected Guests</h6>
                    <div class="amount"><?php echo (int) $summary['total_guests']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">Inquiries / Cancelled</h6>
                    <div class="amount"><?php echo (int) $summary['inquiry_count']; ?> / <?php echo (int) $summary['cancelled_count']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($by_type)): ?>
    <div class="card card-bordered mb-4">
        <div class="card-inner">
            <h6 class="title mb-3">Revenue by Event Type</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Event Type</th><th>Count</th><th>Package Amount</th></tr></thead>
                    <tbody>
                        <?php foreach ($by_type as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(ucfirst($row->event_type)); ?></td>
                            <td><span class="badge bg-info"><?php echo (int) $row->event_count; ?></span></td>
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
            <h6 class="title mb-3">Events in Period</h6>
            <div class="mob-desktop-table mob-desktop-table--xl">
            <div class="table-responsive dt-fit-wrap">
                <table class="table table-hover dt-fit-width" id="eventsReportTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Event #</th>
                            <th class="col-name">Event</th>
                            <th>Date</th>
                            <th class="col-name">Organizer</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th class="col-amount">Amount</th>
                            <th class="col-actions no-print" data-orderable="false"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($events)): foreach ($events as $ev): ?>
                        <?php $event_ts = strtotime($ev->event_date); ?>
                        <tr>
                            <td class="fw-semibold"><?php echo htmlspecialchars($ev->event_number ?: ('EV' . str_pad($ev->id, 8, '0', STR_PAD_LEFT))); ?></td>
                            <td class="col-name">
                                <div class="dt-cell-title"><?php echo htmlspecialchars($ev->event_name); ?></div>
                                <div class="dt-cell-sub text-muted"><?php echo htmlspecialchars(ucfirst($ev->event_type)); ?></div>
                            </td>
                            <td data-order="<?php echo date('Y-m-d', $event_ts); ?>"><?php echo date('M d, Y', $event_ts); ?></td>
                            <td class="col-name">
                                <div><?php echo htmlspecialchars($ev->organizer_name); ?></div>
                                <?php if ($ev->booking_number): ?>
                                <div class="dt-cell-sub text-muted">Booking <?php echo htmlspecialchars($ev->booking_number); ?></div>
                                <?php endif; ?>
                            </td>
                            <td data-order="<?php echo (int) $ev->expected_guests; ?>"><?php echo (int) $ev->expected_guests; ?></td>
                            <td><span class="badge bg-secondary"><?php echo ucfirst($ev->status); ?></span></td>
                            <td class="col-amount fw-semibold" data-order="<?php echo (float) $ev->total_amount; ?>">₱<?php echo number_format($ev->total_amount, 2); ?></td>
                            <td class="col-actions no-print"><a href="<?php echo base_url('events/view/' . $ev->id); ?>" class="btn btn-sm btn-outline-primary" title="View event"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="8" class="text-center text-muted">No events in this period</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>

            <div class="mob-card-list mob-card-list--xl d-xl-none">
                <?php if (!empty($events)): foreach ($events as $ev): ?>
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="mob-list-card-title"><?php echo htmlspecialchars($ev->event_name); ?></div>
                            <div class="mob-list-card-meta text-truncate">
                                <?php echo htmlspecialchars($ev->event_number ?: ('EV' . str_pad($ev->id, 8, '0', STR_PAD_LEFT))); ?>
                                &middot; <?php echo htmlspecialchars(ucfirst($ev->event_type)); ?>
                            </div>
                        </div>
                        <span class="badge bg-secondary flex-shrink-0"><?php echo ucfirst($ev->status); ?></span>
                    </div>
                    <div class="mob-list-card-body">
                        <div class="mob-list-card-meta"><i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($ev->event_date)); ?></div>
                        <div class="mob-list-card-meta text-truncate"><i class="bi bi-person"></i> <?php echo htmlspecialchars($ev->organizer_name); ?></div>
                        <div class="mob-list-card-meta">
                            <i class="bi bi-people"></i> <?php echo (int) $ev->expected_guests; ?> expected guest<?php echo (int) $ev->expected_guests === 1 ? '' : 's'; ?>
                            <?php if ($ev->booking_number): ?>&middot; Booking <?php echo htmlspecialchars($ev->booking_number); ?><?php endif; ?>
                        </div>
                        <div class="mob-list-card-meta mt-2">
                            <span class="fw-semibold fs-6 text-body">₱<?php echo number_format($ev->total_amount, 2); ?></span>
                        </div>
                    </div>
                    <div class="mob-list-card-actions">
                        <a href="<?php echo base_url('events/view/' . $ev->id); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="mob-list-card mob-list-card-empty text-center text-muted py-4">No events in this period</div>
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
