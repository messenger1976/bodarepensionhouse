<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-people-fill"></i> Manage Groups</h5>
        <?php if (isset($can_manage) && $can_manage): ?>
        <a href="<?php echo base_url('groups/add'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Group
        </a>
        <?php endif; ?>
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
                    <th>Group Name</th>
                    <th>Description</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $group): ?>
                        <tr>
                            <td><?php echo $group->id; ?></td>
                            <td><strong><?php echo htmlspecialchars($group->name); ?></strong></td>
                            <td><?php echo htmlspecialchars($group->description ? $group->description : '-'); ?></td>
                            <td>
                                <?php if (!empty($group->roles)): ?>
                                    <?php foreach ($group->roles as $role): ?>
                                        <span class="badge bg-info me-1"><?php echo htmlspecialchars($role->name); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">No roles assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $group->status == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($group->status); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (isset($can_manage) && $can_manage): ?>
                                <a href="<?php echo base_url('groups/edit/' . $group->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?php echo base_url('groups/delete/' . $group->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this group?');" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No groups found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

