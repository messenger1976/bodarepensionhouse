<?php
$a = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$can_create = !empty($can_create);
$can_upload = !empty($can_upload);
$can_delete = !empty($can_delete);
$can_restore = !empty($can_restore);
$backups = isset($backups) && is_array($backups) ? $backups : array();
?>
<div class="nk-block">
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-database-down"></i> Backup Database</h3>
                <div class="nk-block-des text-soft">
                    <p>Create, upload, download, delete, or restore <code>.sql</code> backups of the live database.</p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $a($this->session->flashdata('success')); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $a($this->session->flashdata('error')); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="alert alert-warning" role="alert">
        <strong>Caution:</strong> Restore overwrites the live database. Prefer downloading a fresh backup before any restore.
        Files are stored under <code><?php echo $a(isset($vault_hint) ? $vault_hint : 'application/backups/'); ?></code> (not publicly downloadable).
    </div>

    <div class="row g-3 mb-3">
        <?php if ($can_create): ?>
        <div class="col-md-6">
            <div class="card card-bordered h-100">
                <div class="card-body">
                    <h6 class="title mb-2"><i class="bi bi-plus-circle"></i> Create Backup</h6>
                    <p class="text-soft small mb-3">Generate a full <code>.sql</code> dump of the current database into the vault.</p>
                    <form method="post" action="<?php echo base_url('database_backup/create'); ?>" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<span class=\'spinner-border spinner-border-sm\'></span> Creating…';">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-database-add"></i> Create Backup Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($can_upload): ?>
        <div class="col-md-6">
            <div class="card card-bordered h-100">
                <div class="card-body">
                    <h6 class="title mb-2"><i class="bi bi-upload"></i> Upload Backup</h6>
                    <p class="text-soft small mb-3">Upload an existing <code>.sql</code> file (max <?php echo $a(isset($max_upload_label) ? $max_upload_label : '50 MB'); ?>).</p>
                    <form method="post" action="<?php echo base_url('database_backup/upload'); ?>" enctype="multipart/form-data">
                        <div class="input-group">
                            <input type="file" name="backup_file" class="form-control" accept=".sql,application/sql,text/plain" required>
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-cloud-upload"></i> Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="card card-bordered">
        <div class="card-body">
            <div class="card-title-group align-start mb-3">
                <div class="card-title">
                    <h6 class="title mb-0"><i class="bi bi-folder2-open"></i> Backup Files</h6>
                </div>
                <div class="card-tools">
                    <span class="badge bg-secondary"><?php echo count($backups); ?> file(s)</span>
                </div>
            </div>

            <?php if (empty($backups)): ?>
                <div class="text-center text-soft py-5">
                    <i class="bi bi-inbox" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0">No backup files yet. Create or upload a <code>.sql</code> backup to get started.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $file): ?>
                            <tr>
                                <td><code><?php echo $a($file['name']); ?></code></td>
                                <td><?php echo $a($file['size_label']); ?></td>
                                <td><?php echo $a($file['mtime_label']); ?></td>
                                <td class="text-end text-nowrap">
                                    <a href="<?php echo base_url('database_backup/download/' . rawurlencode($file['name'])); ?>" class="btn btn-sm btn-light" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <?php if ($can_restore): ?>
                                    <button type="button"
                                        class="btn btn-sm btn-warning"
                                        title="Restore"
                                        data-bs-toggle="modal"
                                        data-bs-target="#restoreBackupModal"
                                        data-filename="<?php echo $a($file['name']); ?>">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($can_delete): ?>
                                    <button type="button"
                                        class="btn btn-sm btn-danger"
                                        title="Delete"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteBackupModal"
                                        data-filename="<?php echo $a($file['name']); ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($can_restore): ?>
<div class="modal fade" id="restoreBackupModal" tabindex="-1" aria-labelledby="restoreBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="restoreBackupForm" action="#">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restoreBackupModalLabel"><i class="bi bi-exclamation-triangle text-warning"></i> Restore Database</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">This will <strong>overwrite the live database</strong> with:</p>
                    <p><code id="restoreFilenameLabel"></code></p>
                    <label class="form-label" for="confirm_restore">Type <strong>RESTORE</strong> to confirm</label>
                    <input type="text" class="form-control" name="confirm_restore" id="confirm_restore" autocomplete="off" required placeholder="RESTORE">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Restore Now</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($can_delete): ?>
<div class="modal fade" id="deleteBackupModal" tabindex="-1" aria-labelledby="deleteBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="deleteBackupForm" action="#">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteBackupModalLabel"><i class="bi bi-trash text-danger"></i> Delete Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Permanently delete this backup file from the vault:</p>
                    <p><code id="deleteFilenameLabel"></code></p>
                    <label class="form-label" for="confirm_delete">Type <strong>DELETE</strong> to confirm</label>
                    <input type="text" class="form-control" name="confirm_delete" id="confirm_delete" autocomplete="off" required placeholder="DELETE">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete File</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    var restoreModal = document.getElementById('restoreBackupModal');
    if (restoreModal) {
        restoreModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            var name = btn ? btn.getAttribute('data-filename') : '';
            document.getElementById('restoreFilenameLabel').textContent = name || '';
            document.getElementById('confirm_restore').value = '';
            document.getElementById('restoreBackupForm').action =
                '<?php echo base_url('database_backup/restore/'); ?>' + encodeURIComponent(name);
        });
    }

    var deleteModal = document.getElementById('deleteBackupModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            var name = btn ? btn.getAttribute('data-filename') : '';
            document.getElementById('deleteFilenameLabel').textContent = name || '';
            document.getElementById('confirm_delete').value = '';
            document.getElementById('deleteBackupForm').action =
                '<?php echo base_url('database_backup/delete/'); ?>' + encodeURIComponent(name);
        });
    }
})();
</script>
