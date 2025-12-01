<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-envelope"></i> Inquiry Details</h5>
        <div>
            <a href="<?php echo base_url('inquiries'); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
            <?php if (isset($can_delete) && $can_delete): ?>
            <a href="<?php echo base_url('inquiries/delete/' . $inquiry->id); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this inquiry?');">
                <i class="bi bi-trash"></i> Delete
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-muted mb-3">Contact Information</h6>
            <table class="table table-bordered">
                <tr>
                    <th width="40%">Name:</th>
                    <td><?php echo htmlspecialchars($inquiry->name); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>
                        <a href="mailto:<?php echo htmlspecialchars($inquiry->email); ?>">
                            <?php echo htmlspecialchars($inquiry->email); ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td><?php echo htmlspecialchars($inquiry->phone ? $inquiry->phone : 'N/A'); ?></td>
                </tr>
                <?php if (isset($user) && $user): ?>
                <tr>
                    <th>User Account:</th>
                    <td>
                        <a href="<?php echo base_url('users/view/' . $user->id); ?>">
                            View User Profile
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="text-muted mb-3">Inquiry Information</h6>
            <table class="table table-bordered">
                <tr>
                    <th width="40%">Inquiry ID:</th>
                    <td>#<?php echo $inquiry->id; ?></td>
                </tr>
                <tr>
                    <th>Subject:</th>
                    <td><strong><?php echo htmlspecialchars($inquiry->subject); ?></strong></td>
                </tr>
                <tr>
                    <th>Status:</th>
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
                </tr>
                <tr>
                    <th>Submitted:</th>
                    <td><?php echo date('F d, Y h:i A', strtotime($inquiry->created_at)); ?></td>
                </tr>
                <?php if ($inquiry->updated_at && $inquiry->updated_at != $inquiry->created_at): ?>
                <tr>
                    <th>Last Updated:</th>
                    <td><?php echo date('F d, Y h:i A', strtotime($inquiry->updated_at)); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
    <div class="mt-4">
        <h6 class="text-muted mb-3">Message</h6>
        <div class="alert alert-light border">
            <?php echo nl2br(htmlspecialchars($inquiry->message)); ?>
        </div>
    </div>
    
    <?php if (isset($can_edit) && $can_edit): ?>
    <div class="mt-4">
        <h6 class="text-muted mb-3">Update Status</h6>
        <form method="post" action="<?php echo base_url('inquiries/update_status/' . $inquiry->id); ?>">
            <div class="row">
                <div class="col-md-6">
                    <select name="status" class="form-select" required>
                        <option value="pending" <?php echo $inquiry->status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $inquiry->status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $inquiry->status == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $inquiry->status == 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Update Status
                    </button>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

