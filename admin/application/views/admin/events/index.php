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

    <?php
    $event_badge = function ($status) {
        if ($status === 'confirmed') return 'success';
        if ($status === 'cancelled') return 'danger';
        if ($status === 'completed') return 'primary';
        return 'secondary';
    };
    ?>

    <div class="mob-desktop-table mob-desktop-table--xl">
    <div class="table-responsive dt-fit-wrap">
        <table class="table table-hover dt-fit-width" id="eventsTable" style="width:100%">
            <thead>
                <tr>
                    <th>Event #</th>
                    <th class="col-name">Event</th>
                    <th>Date</th>
                    <th class="col-name">Organizer</th>
                    <th>Guests</th>
                    <th class="col-amount">Amount</th>
                    <th>Status</th>
                    <th class="col-actions" data-orderable="false">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($events)): foreach ($events as $ev): ?>
                <?php $event_ts = strtotime($ev->event_date); ?>
                <tr>
                    <td class="fw-semibold"><?php echo htmlspecialchars($ev->event_number ?: 'EV' . str_pad($ev->id, 8, '0', STR_PAD_LEFT)); ?></td>
                    <td class="col-name">
                        <div class="dt-cell-title"><?php echo htmlspecialchars($ev->event_name); ?></div>
                        <div class="dt-cell-sub text-muted"><?php echo htmlspecialchars(ucfirst($ev->event_type)); ?></div>
                    </td>
                    <td data-order="<?php echo date('Y-m-d', $event_ts); ?>"><i class="bi bi-calendar3 text-muted"></i> <?php echo date('M d, Y', $event_ts); ?></td>
                    <td class="col-name"><?php echo htmlspecialchars($ev->organizer_name); ?></td>
                    <td data-order="<?php echo (int) $ev->expected_guests; ?>"><i class="bi bi-people text-muted"></i> <?php echo (int) $ev->expected_guests; ?></td>
                    <td class="col-amount fw-semibold" data-order="<?php echo (float) $ev->total_amount; ?>">₱<?php echo number_format($ev->total_amount, 2); ?></td>
                    <td><span class="badge bg-<?php echo $event_badge($ev->status); ?>"><?php echo ucfirst($ev->status); ?></span></td>
                    <td class="col-actions">
                        <a href="<?php echo base_url('events/view/' . $ev->id); ?>" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye"></i></a>
                        <?php if (!empty($can_edit)): ?>
                        <a href="<?php echo base_url('events/edit/' . $ev->id); ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="8" class="text-center text-muted">No events found</td></tr>
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
                            <?php echo htmlspecialchars($ev->event_number ?: 'EV' . str_pad($ev->id, 8, '0', STR_PAD_LEFT)); ?>
                            &middot; <?php echo htmlspecialchars(ucfirst($ev->event_type)); ?>
                        </div>
                    </div>
                    <span class="badge bg-<?php echo $event_badge($ev->status); ?> flex-shrink-0"><?php echo ucfirst($ev->status); ?></span>
                </div>
                <div class="mob-list-card-body">
                    <div class="mob-list-card-meta">
                        <i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($ev->event_date)); ?>
                    </div>
                    <div class="mob-list-card-meta text-truncate">
                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($ev->organizer_name); ?>
                    </div>
                    <div class="mob-list-card-meta">
                        <i class="bi bi-people"></i> <?php echo (int) $ev->expected_guests; ?> expected guest<?php echo (int) $ev->expected_guests === 1 ? '' : 's'; ?>
                    </div>
                    <div class="mob-list-card-meta mt-2">
                        <span class="fw-semibold fs-6 text-body">&#8369;<?php echo number_format($ev->total_amount, 2); ?></span>
                    </div>
                </div>
                <div class="mob-list-card-actions">
                    <a href="<?php echo base_url('events/view/' . $ev->id); ?>" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye"></i> View</a>
                    <?php if (!empty($can_edit)): ?>
                    <a href="<?php echo base_url('events/edit/' . $ev->id); ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i> Edit</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="mob-list-card mob-list-card-empty text-center text-muted py-4">No events found</div>
        <?php endif; ?>
    </div>
</div>
