<div class="nk-block">
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-people"></i> Manage Admin Users</h3>
                <div class="nk-block-des text-soft">
                    <p>View and manage all admin user accounts</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <?php if (isset($can_delete) && $can_delete): ?>
                            <li>
                                <button type="submit" form="users-batch-form" id="batch-delete-btn" class="btn btn-danger" disabled>
                                    <i class="bi bi-trash"></i> <span>Delete Selected</span>
                                </button>
                            </li>
                            <?php endif; ?>
                            <?php if (isset($can_add) && $can_add): ?>
                            <li>
                                <a href="<?php echo base_url('users/add'); ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> <span>Add New User</span>
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
            <form id="users-batch-form" method="post" action="<?php echo base_url('users/batch_delete'); ?>">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <?php if (isset($can_delete) && $can_delete): ?>
                                <th style="width: 42px;">
                                    <input type="checkbox" class="form-check-input" id="select-all-users" title="Select all">
                                </th>
                                <?php endif; ?>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Groups</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <?php $is_current_user = ($user->id == $this->session->userdata('admin_id')); ?>
                                    <tr>
                                        <?php if (isset($can_delete) && $can_delete): ?>
                                        <td>
                                            <?php if (!$is_current_user): ?>
                                            <input type="checkbox" class="form-check-input user-select-checkbox" name="user_ids[]" value="<?php echo (int) $user->id; ?>">
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                        <td><?php echo $user->id; ?></td>
                                        <td><?php echo htmlspecialchars($user->username); ?></td>
                                        <td><?php echo htmlspecialchars($user->name); ?></td>
                                        <td><?php echo htmlspecialchars($user->email); ?></td>
                                        <td>
                                            <?php if (!empty($user->groups)): ?>
                                                <?php foreach ($user->groups as $group): ?>
                                                    <span class="badge bg-info me-1"><?php echo htmlspecialchars($group->name); ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">No groups</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $user->status == 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($user->status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (isset($can_edit) && $can_edit): ?>
                                            <a href="<?php echo base_url('users/view/' . $user->id); ?>" class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?php echo base_url('users/edit/' . $user->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (isset($can_delete) && $can_delete && !$is_current_user): ?>
                                            <a href="<?php echo base_url('users/delete/' . $user->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo (isset($can_delete) && $can_delete) ? '8' : '7'; ?>" class="text-center text-muted">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($can_delete) && $can_delete): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all-users');
    const checkboxes = Array.from(document.querySelectorAll('.user-select-checkbox'));
    const batchDeleteBtn = document.getElementById('batch-delete-btn');
    const batchForm = document.getElementById('users-batch-form');

    function updateBatchDeleteState() {
        const selectedCount = checkboxes.filter(cb => cb.checked).length;
        if (batchDeleteBtn) {
            batchDeleteBtn.disabled = selectedCount === 0;
            batchDeleteBtn.querySelector('span').textContent = selectedCount > 0
                ? 'Delete Selected (' + selectedCount + ')'
                : 'Delete Selected';
        }
        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && selectedCount === checkboxes.length;
            selectAll.indeterminate = selectedCount > 0 && selectedCount < checkboxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateBatchDeleteState();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBatchDeleteState);
    });

    if (batchForm) {
        batchForm.addEventListener('submit', function(e) {
            const selectedCount = checkboxes.filter(cb => cb.checked).length;
            if (selectedCount === 0) {
                e.preventDefault();
                return;
            }
            const confirmed = window.confirm(
                'Delete ' + selectedCount + ' selected user(s)? This cannot be undone.'
            );
            if (!confirmed) {
                e.preventDefault();
            }
        });
    }

    updateBatchDeleteState();
});
</script>
<?php endif; ?>
