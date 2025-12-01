<div class="content-card">
    <div class="mb-4">
        <h5 class="mb-0"><i class="bi bi-check-circle text-success"></i> Module Generation Complete</h5>
        <p class="text-muted mt-2">Module: <strong><?php echo htmlspecialchars($module_name); ?></strong></p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <h6>Errors:</h6>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($generated)): ?>
        <div class="alert alert-success">
            <h6>Generated Files:</h6>
            <ul class="mb-0">
                <?php foreach ($generated as $file): ?>
                    <li><?php echo htmlspecialchars($file); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- SQL Table Structure -->
    <?php if (!empty($sql)): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Database Table SQL</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="copySQL()">
                    <i class="bi bi-clipboard"></i> Copy SQL
                </button>
            </div>
            <div class="card-body">
                <pre id="sql-code" class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code><?php echo htmlspecialchars($sql); ?></code></pre>
                <p class="text-muted mt-2 small">
                    <i class="bi bi-info-circle"></i> 
                    Copy and execute this SQL in your database to create the table.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Permissions Created -->
    <?php if (!empty($permissions_created)): ?>
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-shield-check"></i> Permissions Automatically Created</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">The following permissions have been automatically created in your database:</p>
                <ul class="list-group">
                    <?php foreach ($permissions_created as $perm): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($perm['name']); ?></strong>
                                <br><small class="text-muted"><code><?php echo htmlspecialchars($perm['slug']); ?></code></small>
                            </div>
                            <span class="badge bg-<?php echo $perm['status'] == 'created' ? 'success' : 'info'; ?>">
                                <?php echo $perm['status'] == 'created' ? 'Created' : 'Already Existed'; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Permissions Assigned -->
    <?php if (!empty($permissions_assigned)): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-person-check"></i> Permissions Assigned to Super Admin</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">The following permissions have been automatically assigned to the Super Admin role:</p>
                <ul class="list-group">
                    <?php foreach ($permissions_assigned as $assigned): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?php echo htmlspecialchars($assigned['permission']); ?></span>
                            <span class="badge bg-<?php 
                                echo $assigned['status'] == 'assigned' ? 'success' : 
                                    ($assigned['status'] == 'already_assigned' ? 'info' : 'danger'); 
                            ?>">
                                <?php 
                                    echo $assigned['status'] == 'assigned' ? 'Assigned' : 
                                        ($assigned['status'] == 'already_assigned' ? 'Already Assigned' : 'Failed'); 
                                ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Note:</strong> You can assign these permissions to other roles through the <a href="<?php echo base_url('roles'); ?>" class="alert-link">Roles Management</a> page.
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Next Steps -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Next Steps</h6>
        </div>
        <div class="card-body">
            <ol>
                <li class="mb-2">
                    <strong>Execute SQL:</strong> Copy the SQL above and run it in your database (phpMyAdmin, MySQL command line, etc.)
                </li>
                <?php if (empty($permissions_created)): ?>
                <li class="mb-2">
                    <strong>Set Permissions:</strong> Add the following permissions to your permissions table:
                    <ul class="mt-1">
                        <li><code>view_<?php echo strtolower($module_name); ?>s</code></li>
                        <li><code>add_<?php echo strtolower($module_name); ?>s</code></li>
                        <li><code>edit_<?php echo strtolower($module_name); ?>s</code></li>
                        <li><code>delete_<?php echo strtolower($module_name); ?>s</code></li>
                    </ul>
                </li>
                <?php endif; ?>
                <li class="mb-2">
                    <strong>Test the Module:</strong> Navigate to 
                    <code><?php echo base_url(strtolower($module_name)); ?></code> to test your new module
                </li>
                <li class="mb-2">
                    <strong>Customize:</strong> Review and customize the generated files as needed:
                    <ul class="mt-1">
                        <li>Controller: <code>application/controllers/admin/<?php echo $module_name; ?>.php</code></li>
                        <li>Model: <code>application/models/<?php echo $module_name; ?>_model.php</code></li>
                        <li>Views: <code>application/views/admin/<?php echo strtolower($module_name); ?>/</code></li>
                    </ul>
                </li>
                <li class="mb-2">
                    <strong>Add to Navigation:</strong> Update your sidebar/navigation menu to include the new module
                </li>
            </ol>
        </div>
    </div>

    <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
        <a href="<?php echo base_url('module_generator'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Generate Another Module
        </a>
        <a href="<?php echo base_url(strtolower($module_name)); ?>" class="btn btn-primary">
            <i class="bi bi-eye"></i> View Module
        </a>
    </div>
</div>

<script>
function copySQL() {
    const sqlCode = document.getElementById('sql-code').textContent;
    
    // Create a temporary textarea element
    const textarea = document.createElement('textarea');
    textarea.value = sqlCode;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    
    // Select and copy
    textarea.select();
    try {
        document.execCommand('copy');
        alert('SQL copied to clipboard!');
    } catch (err) {
        alert('Failed to copy SQL. Please select and copy manually.');
    }
    
    // Remove textarea
    document.body.removeChild(textarea);
}
</script>

