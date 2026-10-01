<div class="nk-block">
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-calendar-check"></i> Manage Bookings</h3>
                <div class="nk-block-des text-soft">
                    <p>View and manage all booking reservations</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <?php if (isset($can_delete) && $can_delete): ?>
                            <li>
                                <button type="submit" form="bookings-batch-form" id="batch-delete-bookings-btn" class="btn btn-danger" disabled>
                                    <i class="bi bi-trash"></i> <span>Delete Selected<span data-bd-count></span></span>
                                </button>
                            </li>
                            <?php endif; ?>
                            <?php if (isset($can_add) && $can_add): ?>
                            <li>
                                <a href="<?php echo base_url('booking_settings'); ?>" class="btn btn-outline-light">
                                    <i class="bi bi-gear"></i> <span>Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo base_url('bookings/add'); ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> <span>Add New Booking</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <div class="btn-group" role="group" aria-label="Filter by status">
            <a href="<?php echo base_url('bookings'); ?>" class="btn btn-sm <?php echo empty($filter_status) ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
            <?php foreach (array('pending', 'confirmed', 'checked_in', 'checked_out', 'completed', 'cancelled') as $st): ?>
            <a href="<?php echo base_url('bookings?status=' . $st); ?>" class="btn btn-sm <?php echo (isset($filter_status) && $filter_status === $st) ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo ucwords(str_replace('_', ' ', $st)); ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <form id="bookings-batch-form" method="post" action="<?php echo base_url('bookings/batch_delete'); ?>">
    <div class="card card-bordered mob-desktop-table mob-desktop-table--xl">
        <div class="card-inner">
            <div class="table-responsive bookings-table-wrap">
        <table class="table table-hover dt-fit-width" id="bookingsTable" style="width:100%">
            <thead>
                <tr>
                    <?php if (isset($can_delete) && $can_delete): ?>
                    <th class="col-select" data-orderable="false">
                        <input type="checkbox" class="form-check-input" data-bd-select-all="bookings-batch-form" title="Select all">
                    </th>
                    <?php endif; ?>
                    <th class="col-booking">Booking #</th>
                    <th class="col-guest">Guest</th>
                    <th class="col-stay">Stay</th>
                    <th class="col-count">Guests / Rooms</th>
                    <th class="col-status">Status</th>
                    <th class="col-payment">Payment</th>
                    <th class="col-amount">Amount</th>
                    <th class="col-actions" data-orderable="false">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <?php
                        $check_in_ts = strtotime($booking->check_in);
                        $check_out_ts = strtotime($booking->check_out);
                        $nights = max(1, (int) round(($check_out_ts - $check_in_ts) / 86400));
                        $room_count = isset($booking->rooms) ? (int) $booking->rooms : 1;
                        ?>
                        <tr>
                            <?php if (isset($can_delete) && $can_delete): ?>
                            <td class="col-select">
                                <input type="checkbox" class="form-check-input booking-select-checkbox" name="booking_ids[]" value="<?php echo (int) $booking->id; ?>">
                            </td>
                            <?php endif; ?>
                            <td class="col-booking fw-semibold">#<?php echo isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT); ?></td>
                            <td class="col-guest">
                                <div class="bk-guest-name"><?php echo htmlspecialchars($booking->guest_name); ?></div>
                                <div class="bk-guest-email text-muted" title="<?php echo htmlspecialchars($booking->guest_email); ?>"><?php echo htmlspecialchars($booking->guest_email); ?></div>
                            </td>
                            <td class="col-stay" data-order="<?php echo date('Y-m-d', $check_in_ts) . ' ' . (!empty($booking->check_in_time) ? date('H:i', strtotime($booking->check_in_time)) : '00:00'); ?>">
                                <div class="bk-stay-line">
                                    <i class="bi bi-box-arrow-in-right text-success"></i>
                                    <?php echo date('M d, Y', $check_in_ts); ?>
                                    <?php if (!empty($booking->check_in_time)): ?>
                                        <span class="text-muted"><?php echo date('g:i A', strtotime($booking->check_in_time)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bk-stay-line">
                                    <i class="bi bi-box-arrow-right text-danger"></i>
                                    <?php echo date('M d, Y', $check_out_ts); ?>
                                    <?php if (!empty($booking->check_out_time)): ?>
                                        <span class="text-muted"><?php echo date('g:i A', strtotime($booking->check_out_time)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bk-stay-nights text-muted"><?php echo $nights; ?> night<?php echo $nights === 1 ? '' : 's'; ?></div>
                            </td>
                            <td class="col-count" data-order="<?php echo (int) $booking->guests; ?>">
                                <span class="bk-count" title="Guests"><i class="bi bi-people"></i> <?php echo (int) $booking->guests; ?></span>
                                <span class="bk-count" title="Rooms"><i class="bi bi-door-closed"></i> <?php echo $room_count; ?></span>
                            </td>
                            <td class="col-status">
                                <?php
                                $badge_class = 'secondary';
                                if ($booking->status == 'confirmed') $badge_class = 'success';
                                if ($booking->status == 'checked_in') $badge_class = 'info';
                                if ($booking->status == 'checked_out' || $booking->status == 'completed') $badge_class = 'primary';
                                if ($booking->status == 'cancelled') $badge_class = 'danger';
                                if ($booking->status == 'pending') $badge_class = 'warning';
                                ?>
                                <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucwords(str_replace('_', ' ', $booking->status)); ?></span>
                            </td>
                            <td class="col-payment">
                                <?php
                                $ps = isset($payment_status_map[$booking->id]) ? $payment_status_map[$booking->id] : null;
                                if ($ps):
                                ?>
                                    <span class="badge bg-<?php echo $ps['badge']; ?>" title="Paid: ₱<?php echo number_format($ps['amount_paid'], 2); ?> / Balance: ₱<?php echo number_format($ps['balance'], 2); ?>">
                                        <?php echo htmlspecialchars($ps['display']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No Invoice</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-amount fw-semibold" data-order="<?php echo (float) $booking->total_amount; ?>">₱<?php echo number_format($booking->total_amount, 2); ?></td>
                            <td class="col-actions">
                                <a href="<?php echo base_url('bookings/' . $booking->id); ?>" class="btn btn-sm btn-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (isset($can_edit) && $can_edit): ?>
                                <a href="<?php echo base_url('bookings/edit/' . $booking->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (isset($can_delete) && $can_delete): ?>
                                <a href="<?php echo base_url('bookings/delete/' . $booking->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this booking?');" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo (isset($can_delete) && $can_delete) ? '9' : '8'; ?>" class="text-center text-muted">No bookings found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>

    <div class="mob-card-list mob-card-list--xl d-xl-none">
        <?php if (!empty($bookings) && isset($can_delete) && $can_delete): ?>
        <div class="d-flex align-items-center gap-2 mb-2 px-1">
            <input type="checkbox" class="form-check-input" id="bookings-mobile-select-all" data-bd-select-all="bookings-batch-form" title="Select all">
            <label class="form-check-label small text-muted" for="bookings-mobile-select-all">Select all</label>
        </div>
        <?php endif; ?>
        <?php if (!empty($bookings)): ?>
            <?php foreach ($bookings as $booking): ?>
                <?php
                $badge_class = 'secondary';
                if ($booking->status == 'confirmed') $badge_class = 'success';
                if ($booking->status == 'checked_in') $badge_class = 'info';
                if ($booking->status == 'checked_out' || $booking->status == 'completed') $badge_class = 'primary';
                if ($booking->status == 'cancelled') $badge_class = 'danger';
                if ($booking->status == 'pending') $badge_class = 'warning';
                $booking_num = isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT);
                $ps = isset($payment_status_map[$booking->id]) ? $payment_status_map[$booking->id] : null;
                ?>
                <?php
                $check_in_ts = strtotime($booking->check_in);
                $check_out_ts = strtotime($booking->check_out);
                $nights = max(1, (int) round(($check_out_ts - $check_in_ts) / 86400));
                $room_count = isset($booking->rooms) ? (int) $booking->rooms : 1;
                ?>
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <?php if (isset($can_delete) && $can_delete): ?>
                        <input type="checkbox" class="form-check-input booking-select-checkbox me-2 flex-shrink-0" name="booking_ids[]" value="<?php echo (int) $booking->id; ?>" aria-label="Select booking">
                        <?php endif; ?>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="mob-list-card-title"><?php echo htmlspecialchars($booking->guest_name); ?></div>
                            <div class="mob-list-card-meta text-truncate" title="<?php echo htmlspecialchars($booking->guest_email); ?>">#<?php echo $booking_num; ?> &middot; <?php echo htmlspecialchars($booking->guest_email); ?></div>
                        </div>
                        <span class="badge bg-<?php echo $badge_class; ?> flex-shrink-0"><?php echo ucwords(str_replace('_', ' ', $booking->status)); ?></span>
                    </div>
                    <div class="mob-list-card-body">
                        <div class="mob-list-card-meta">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <?php echo date('M d, Y', $check_in_ts); ?>
                            <?php if (!empty($booking->check_in_time)): ?><?php echo date('g:i A', strtotime($booking->check_in_time)); ?><?php endif; ?>
                        </div>
                        <div class="mob-list-card-meta">
                            <i class="bi bi-box-arrow-right"></i>
                            <?php echo date('M d, Y', $check_out_ts); ?>
                            <?php if (!empty($booking->check_out_time)): ?><?php echo date('g:i A', strtotime($booking->check_out_time)); ?><?php endif; ?>
                        </div>
                        <div class="mob-list-card-meta">
                            <i class="bi bi-moon"></i> <?php echo $nights; ?> night<?php echo $nights === 1 ? '' : 's'; ?>
                            &middot; <?php echo (int) $booking->guests; ?> guest<?php echo (int) $booking->guests === 1 ? '' : 's'; ?>
                            &middot; <?php echo $room_count; ?> room<?php echo $room_count === 1 ? '' : 's'; ?>
                        </div>
                        <div class="mob-list-card-meta d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
                            <?php if ($ps): ?>
                                <span class="badge bg-<?php echo $ps['badge']; ?>" title="Paid: &#8369;<?php echo number_format($ps['amount_paid'], 2); ?> / Balance: &#8369;<?php echo number_format($ps['balance'], 2); ?>">
                                    <?php echo htmlspecialchars($ps['display']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No Invoice</span>
                            <?php endif; ?>
                            <span class="fw-semibold fs-6 text-body">&#8369;<?php echo number_format($booking->total_amount, 2); ?></span>
                        </div>
                    </div>
                    <div class="mob-list-card-actions">
                        <a href="<?php echo base_url('bookings/' . $booking->id); ?>" class="btn btn-sm btn-primary" title="View">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <?php if (isset($can_edit) && $can_edit): ?>
                        <a href="<?php echo base_url('bookings/edit/' . $booking->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <?php endif; ?>
                        <?php if (isset($can_delete) && $can_delete): ?>
                        <a href="<?php echo base_url('bookings/delete/' . $booking->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this booking?');" title="Delete">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mob-list-card mob-list-card-empty text-center text-muted py-4">No bookings found</div>
        <?php endif; ?>
    </div>
    </form>

    <?php
    if (!empty($can_delete)) {
        $this->load->view('admin/layout/batch_delete', array(
            'bd_form_id' => 'bookings-batch-form',
            'bd_checkbox_class' => 'booking-select-checkbox',
            'bd_button_id' => 'batch-delete-bookings-btn',
            'bd_label' => 'booking'
        ));
    }
    ?>
</div>


