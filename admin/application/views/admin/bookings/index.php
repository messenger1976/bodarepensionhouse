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

    <div class="card card-bordered mob-desktop-table">
        <div class="card-inner">
            <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Booking Number</th>
                    <th>Guest Name</th>
                    <th>Email</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Guests</th>
                    <th>Rooms</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>#<?php echo isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($booking->guest_name); ?></td>
                            <td><?php echo htmlspecialchars($booking->guest_email); ?></td>
                            <td>
                                <?php echo date('M d, Y', strtotime($booking->check_in)); ?>
                                <?php if (!empty($booking->check_in_time)): ?>
                                    <span class="text-muted"><?php echo date('g:i A', strtotime($booking->check_in_time)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($booking->check_out)); ?>
                                <?php if (!empty($booking->check_out_time)): ?>
                                    <span class="text-muted"><?php echo date('g:i A', strtotime($booking->check_out_time)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $booking->guests; ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo isset($booking->rooms) ? $booking->rooms : 1; ?>
                                </span>
                            </td>
                            <td>
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
                            <td>
                                <?php
                                $ps = isset($payment_status_map[$booking->id]) ? $payment_status_map[$booking->id] : null;
                                if ($ps):
                                ?>
                                    <span class="badge bg-<?php echo $ps['badge']; ?>" title="Paid: â‚±<?php echo number_format($ps['amount_paid'], 2); ?> / Balance: â‚±<?php echo number_format($ps['balance'], 2); ?>">
                                        <?php echo htmlspecialchars($ps['display']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No Invoice</span>
                                <?php endif; ?>
                            </td>
                            <td>â‚±<?php echo number_format($booking->total_amount, 2); ?></td>
                            <td>
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
                        <td colspan="11" class="text-center text-muted">No bookings found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>

    <div class="mob-card-list d-lg-none">
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
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <div>
                            <div class="mob-list-card-title">#<?php echo $booking_num; ?> &middot; <?php echo htmlspecialchars($booking->guest_name); ?></div>
                            <div class="mob-list-card-meta"><?php echo htmlspecialchars($booking->guest_email); ?></div>
                        </div>
                        <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucwords(str_replace('_', ' ', $booking->status)); ?></span>
                    </div>
                    <div class="mob-list-card-meta">
                        <i class="bi bi-calendar-event"></i>
                        <?php echo date('M d, Y', strtotime($booking->check_in)); ?>
                        &ndash;
                        <?php echo date('M d, Y', strtotime($booking->check_out)); ?>
                    </div>
                    <div class="mob-list-card-meta d-flex flex-wrap align-items-center gap-2">
                        <?php if ($ps): ?>
                            <span class="badge bg-<?php echo $ps['badge']; ?>" title="Paid: &#8369;<?php echo number_format($ps['amount_paid'], 2); ?> / Balance: &#8369;<?php echo number_format($ps['balance'], 2); ?>">
                                <?php echo htmlspecialchars($ps['display']); ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No Invoice</span>
                        <?php endif; ?>
                        <span class="fw-semibold">&#8369;<?php echo number_format($booking->total_amount, 2); ?></span>
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
            <div class="mob-list-card text-center text-muted py-4">No bookings found</div>
        <?php endif; ?>
    </div>
</div>


