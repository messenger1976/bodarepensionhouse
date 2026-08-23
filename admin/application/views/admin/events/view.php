<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-calendar-event"></i> <?php echo htmlspecialchars($event->event_name); ?></h5>
        <div>
            <a href="<?php echo base_url('events'); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            <?php if (!empty($can_add_invoice) && (float) $event->total_amount > 0): ?>
            <a href="<?php echo base_url('events/create_invoice/' . $event->id); ?>" class="btn btn-primary" onclick="return confirm('Create invoice for this event?');"><i class="bi bi-receipt"></i> Create Invoice</a>
            <?php endif; ?>
            <?php if (!empty($can_edit)): ?>
            <a href="<?php echo base_url('events/edit/' . $event->id); ?>" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                <tr><th width="40%">Event #</th><td><?php echo htmlspecialchars($event->event_number); ?></td></tr>
                <tr><th>Type</th><td><?php echo ucfirst($event->event_type); ?></td></tr>
                <tr><th>Date</th><td><?php echo date('F d, Y', strtotime($event->event_date)); ?></td></tr>
                <tr><th>Time</th><td><?php echo ($event->start_time ? date('g:i A', strtotime($event->start_time)) : '—') . ' - ' . ($event->end_time ? date('g:i A', strtotime($event->end_time)) : '—'); ?></td></tr>
                <tr><th>Venue</th><td><?php echo htmlspecialchars($event->venue ?: '—'); ?></td></tr>
                <tr><th>Expected Guests</th><td><?php echo (int) $event->expected_guests; ?></td></tr>
                <tr><th>Package Amount</th><td><strong>₱<?php echo number_format($event->total_amount, 2); ?></strong></td></tr>
                <tr><th>Status</th><td><span class="badge bg-primary"><?php echo ucfirst($event->status); ?></span></td></tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-bordered">
                <tr><th width="40%">Organizer</th><td><?php echo htmlspecialchars($event->organizer_name); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($event->organizer_email ?: '—'); ?></td></tr>
                <tr><th>Phone</th><td><?php echo htmlspecialchars($event->organizer_phone ?: '—'); ?></td></tr>
                <tr><th>Linked Booking</th><td><?php echo $event->booking_number ? htmlspecialchars($event->booking_number) : '—'; ?></td></tr>
                <tr><th>Notes</th><td><?php echo $event->notes ? nl2br(htmlspecialchars($event->notes)) : '—'; ?></td></tr>
            </table>
        </div>
    </div>

    <h6 class="text-muted mt-4 mb-3">Related Invoices</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead><tr><th>Invoice #</th><th>Total</th><th>Balance</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
                <tr>
                    <td><?php echo htmlspecialchars($inv->invoice_number); ?></td>
                    <td>₱<?php echo number_format($inv->total_amount, 2); ?></td>
                    <td>₱<?php echo number_format($inv->balance_due, 2); ?></td>
                    <td><?php echo ucfirst($inv->status); ?></td>
                    <td><a href="<?php echo base_url('invoices/view/' . $inv->id); ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="5" class="text-muted">No invoices linked to this event yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
