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
    
    <div class="mob-desktop-table mob-desktop-table--xl">
    <div class="table-responsive dt-fit-wrap">
        <table class="table table-hover dt-fit-width" id="groupsTable" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th class="col-name">Group</th>
                    <th class="col-name">Roles</th>
                    <th>Status</th>
                    <th class="col-actions" data-orderable="false">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $group): ?>
                        <tr>
                            <td class="text-muted"><?php echo (int) $group->id; ?></td>
                            <td class="col-name">
                                <div class="dt-cell-title"><?php echo htmlspecialchars($group->name); ?></div>
                                <?php if (!empty($group->description)): ?>
                                <div class="dt-cell-sub text-muted" title="<?php echo htmlspecialchars($group->description); ?>"><?php echo htmlspecialchars($group->description); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="col-name">
                                <?php if (!empty($group->roles)): ?>
                                    <?php foreach ($group->roles as $role): ?>
                                        <span class="badge bg-info me-1 mb-1"><?php echo htmlspecialchars($role->name); ?></span>
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
                            <td class="col-actions">
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
                        <td colspan="5" class="text-center text-muted">No groups found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>

    <div class="mob-card-list mob-card-list--xl d-xl-none">
        <?php if (!empty($groups)): ?>
            <?php foreach ($groups as $group): ?>
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="mob-list-card-title"><?php echo htmlspecialchars($group->name); ?></div>
                            <div class="mob-list-card-meta">#<?php echo (int) $group->id; ?></div>
                        </div>
                        <span class="badge bg-<?php echo $group->status == 'active' ? 'success' : 'secondary'; ?> flex-shrink-0">
                            <?php echo ucfirst($group->status); ?>
                        </span>
                    </div>
                    <div class="mob-list-card-body">
                        <?php if (!empty($group->description)): ?>
                        <div class="mob-list-card-meta">
                            <i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($group->description); ?>
                        </div>
                        <?php endif; ?>
                        <div class="mob-list-card-meta">
                            <i class="bi bi-shield-lock"></i>
                            <?php if (!empty($group->roles)): ?>
                                <?php foreach ($group->roles as $role): ?>
                                    <span class="badge bg-info me-1 mb-1"><?php echo htmlspecialchars($role->name); ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">No roles assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (isset($can_manage) && $can_manage): ?>
                    <div class="mob-list-card-actions">
                        <a href="<?php echo base_url('groups/edit/' . $group->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="<?php echo base_url('groups/delete/' . $group->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this group?');" title="Delete">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mob-list-card mob-list-card-empty text-center text-muted py-4">No groups found</div>
        <?php endif; ?>
    </div>
</div>

