<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Hotel Events</h5>
        <?php if (!empty($can_add)): ?>
        <a href="<?php echo base_url('events/add'); ?>" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Event</a>
        <?php endif; ?>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $this->session->flashdata('success'); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Event #</th>
                    <th>Event Name</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Organizer</th>
                    <th>Guests</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($events)): foreach ($events as $ev): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ev->event_number ?: 'EV' . str_pad($ev->id, 8, '0', STR_PAD_LEFT)); ?></td>
                    <td><strong><?php echo htmlspecialchars($ev->event_name); ?></strong></td>
                    <td><?php echo ucfirst($ev->event_type); ?></td>
                    <td><?php echo date('M d, Y', strtotime($ev->event_date)); ?></td>
                    <td><?php echo htmlspecialchars($ev->organizer_name); ?></td>
                    <td><?php echo (int) $ev->expected_guests; ?></td>
                    <td>₱<?php echo number_format($ev->total_amount, 2); ?></td>
                    <td>
                        <?php
                        $badge = 'secondary';
                        if ($ev->status === 'confirmed') $badge = 'success';
                        elseif ($ev->status === 'cancelled') $badge = 'danger';
                        elseif ($ev->status === 'completed') $badge = 'primary';
                        ?>
                        <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($ev->status); ?></span>
                    </td>
                    <td>
                        <a href="<?php echo base_url('events/view/' . $ev->id); ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                        <?php if (!empty($can_edit)): ?>
                        <a href="<?php echo base_url('events/edit/' . $ev->id); ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="9" class="text-center text-muted">No events found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
