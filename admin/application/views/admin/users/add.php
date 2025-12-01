<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Add New Admin User</h5>
        <a href="<?php echo base_url('users'); ?>" class="btn btn-secondary">
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
    
    <?php echo form_open('users/add'); ?>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="username" class="form-label">Username *</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo set_value('username'); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo set_value('email'); ?>" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Full Name *</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name'); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password *</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <small class="form-text text-muted">Minimum 6 characters</small>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="groups" class="form-label">Assign Groups *</label>
            <small class="form-text text-muted d-block mb-2">Select one or more groups to assign to this user</small>
            <?php if (!empty($groups)): ?>
                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                    <?php foreach ($groups as $group): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="groups[]" value="<?php echo $group->id; ?>" id="group_<?php echo $group->id; ?>" <?php echo set_checkbox('groups[]', $group->id); ?>>
                            <label class="form-check-label" for="group_<?php echo $group->id; ?>">
                                <strong><?php echo htmlspecialchars($group->name); ?></strong>
                                <?php if ($group->description): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($group->description); ?></small>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    No groups available. Please <a href="<?php echo base_url('groups/add'); ?>">create a group</a> first.
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" <?php echo set_checkbox('status', '1', TRUE); ?>>
                <label class="form-check-label" for="status">
                    Active
                </label>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('users'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create User</button>
        </div>
    <?php echo form_close(); ?>
</div>

