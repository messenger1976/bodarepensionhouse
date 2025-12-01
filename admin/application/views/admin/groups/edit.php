<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Group</h5>
        <a href="<?php echo base_url('groups'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
    
    <?php if (validation_errors()): ?>
        <div class="alert alert-danger">
            <?php echo validation_errors(); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php echo form_open('groups/edit/' . $group->id); ?>
        <div class="mb-3">
            <label for="name" class="form-label">Group Name *</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $group->name); ?>" required>
            <small class="form-text text-muted">Unique name for this group</small>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo set_value('description', $group->description); ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="roles" class="form-label">Assign Roles *</label>
            <small class="form-text text-muted d-block mb-2">Select one or more roles to assign to this group</small>
            <?php if (!empty($roles)): ?>
                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($roles as $role): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="<?php echo $role->id; ?>" id="role_<?php echo $role->id; ?>" 
                                <?php echo (in_array($role->id, $selected_role_ids)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="role_<?php echo $role->id; ?>">
                                <strong><?php echo htmlspecialchars($role->name); ?></strong>
                                <?php if ($role->description): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($role->description); ?></small>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    No roles available. Please <a href="<?php echo base_url('roles/add'); ?>">create a role</a> first.
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" <?php echo set_checkbox('status', '1', $group->status == 'active'); ?>>
                <label class="form-check-label" for="status">
                    Active
                </label>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('groups'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Group</button>
        </div>
    <?php echo form_close(); ?>
</div>

