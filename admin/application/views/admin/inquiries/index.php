<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-envelope"></i> Manage Inquiries</h5>
        <div>
            <!-- Status filter -->
            <div class="btn-group" role="group">
                <a href="<?php echo base_url('inquiries'); ?>" class="btn btn-sm <?php echo !isset($current_status) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    All
                </a>
                <a href="<?php echo base_url('inquiries?status=pending'); ?>" class="btn btn-sm <?php echo (isset($current_status) && $current_status == 'pending') ? 'btn-warning' : 'btn-outline-warning'; ?>">
                    Pending
                </a>
                <a href="<?php echo base_url('inquiries?status=in_progress'); ?>" class="btn btn-sm <?php echo (isset($current_status) && $current_status == 'in_progress') ? 'btn-info' : 'btn-outline-info'; ?>">
                    In Progress
                </a>
                <a href="<?php echo base_url('inquiries?status=resolved'); ?>" class="btn btn-sm <?php echo (isset($current_status) && $current_status == 'resolved') ? 'btn-success' : 'btn-outline-success'; ?>">
                    Resolved
                </a>
                <a href="<?php echo base_url('inquiries?status=closed'); ?>" class="btn btn-sm <?php echo (isset($current_status) && $current_status == 'closed') ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                    Closed
                </a>
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
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($inquiries)): ?>
                    <?php foreach ($inquiries as $inquiry): ?>
                        <tr>
                            <td>#<?php echo $inquiry->id; ?></td>
                            <td><?php echo htmlspecialchars($inquiry->name); ?></td>
                            <td><?php echo htmlspecialchars($inquiry->email); ?></td>
                            <td><?php echo htmlspecialchars($inquiry->phone ? $inquiry->phone : 'N/A'); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($inquiry->subject); ?></strong>
                                <?php if (strlen($inquiry->subject) > 50): ?>
                                    <span class="text-muted">...</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badge_class = 'secondary';
                                if ($inquiry->status == 'pending') $badge_class = 'warning';
                                if ($inquiry->status == 'in_progress') $badge_class = 'info';
                                if ($inquiry->status == 'resolved') $badge_class = 'success';
                                if ($inquiry->status == 'closed') $badge_class = 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $inquiry->status)); ?></span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($inquiry->created_at)); ?></td>
                            <td>
                                <a href="<?php echo base_url('inquiries/' . $inquiry->id); ?>" class="btn btn-sm btn-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (isset($can_delete) && $can_delete): ?>
                                <a href="<?php echo base_url('inquiries/delete/' . $inquiry->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this inquiry?');" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No inquiries found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

