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
    
    <div class="card card-bordered">
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
                            <td><?php echo date('M d, Y', strtotime($booking->check_in)); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking->check_out)); ?></td>
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
                                if ($booking->status == 'cancelled') $badge_class = 'danger';
                                if ($booking->status == 'pending') $badge_class = 'warning';
                                ?>
                                <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucfirst($booking->status); ?></span>
                            </td>
                            <td>₱<?php echo number_format($booking->total_amount, 2); ?></td>
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
                        <td colspan="10" class="text-center text-muted">No bookings found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div>

