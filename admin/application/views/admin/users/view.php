<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-person-circle"></i> User Details</h5>
        <a href="<?php echo base_url('users'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Basic Information</h6>
                </div>
                <div class="card-body">
                    <!-- Avatar Display -->
                    <div class="text-center mb-3">
                        <?php if (!empty($user->avatar) && file_exists(FCPATH . $user->avatar)): ?>
                            <img src="<?php echo base_url($user->avatar); ?>" alt="Avatar" 
                                class="rounded-circle mb-2" 
                                style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #dee2e6;">
                        <?php else: ?>
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                style="width: 120px; height: 120px; background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%); color: #fff; font-size: 3rem; font-weight: 600; border: 4px solid #dee2e6;">
                                <?php 
                                $display_name = !empty($user->name) ? $user->name : (!empty($user->username) ? $user->username : 'A');
                                echo strtoupper(substr($display_name, 0, 1)); 
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">ID:</th>
                            <td><?php echo $user->id; ?></td>
                        </tr>
                        <tr>
                            <th>Username:</th>
                            <td><?php echo htmlspecialchars($user->username); ?></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><?php echo htmlspecialchars($user->name); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($user->email); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-<?php echo $user->status == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($user->status); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td><?php echo isset($user->created_at) ? date('M d, Y H:i', strtotime($user->created_at)) : 'N/A'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-people-fill"></i> Groups</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($groups)): ?>
                        <?php foreach ($groups as $group): ?>
                            <span class="badge bg-primary me-2 mb-2" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($group->name); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">No groups assigned</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-shield-check"></i> Roles</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                            <div class="mb-2">
                                <strong><?php echo htmlspecialchars($role->name); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($role->slug); ?></small>
                                <?php if ($role->description): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($role->description); ?></small>
                                <?php endif; ?>
                            </div>
                            <hr class="my-2">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">No roles assigned</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-key"></i> Permissions</h6>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <?php if (!empty($permissions)): ?>
                        <?php 
                        $grouped_perms = array();
                        foreach ($permissions as $perm) {
                            $module = $perm->module ? $perm->module : 'general';
                            if (!isset($grouped_perms[$module])) {
                                $grouped_perms[$module] = array();
                            }
                            $grouped_perms[$module][] = $perm;
                        }
                        ?>
                        <?php foreach ($grouped_perms as $module => $module_perms): ?>
                            <div class="mb-3">
                                <strong class="text-primary"><?php echo ucfirst($module); ?>:</strong>
                                <div class="mt-1">
                                    <?php foreach ($module_perms as $perm): ?>
                                        <span class="badge bg-secondary me-1 mb-1"><?php echo htmlspecialchars($perm->name); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">No permissions assigned</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
        <a href="<?php echo base_url('users/edit/' . $user->id); ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit User
        </a>
        <a href="<?php echo base_url('users'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

