<?php
// Helper closures for safe output inside the view.
$a = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$badge = function ($type) {
    $map = array(
        'page_view' => 'secondary',
        'auth'      => 'primary',
        'crud'      => 'success',
        'api'       => 'info',
        'system'    => 'warning',
        'security'  => 'danger',
    );
    return isset($map[$type]) ? $map[$type] : 'secondary';
};
$sev = function ($v) {
    $map = array('info' => 'info', 'warning' => 'warning', 'critical' => 'danger');
    return isset($map[$v]) ? $map[$v] : 'info';
};
$actor = function ($v) {
    $map = array('admin' => 'primary', 'customer' => 'success', 'guest' => 'secondary', 'api' => 'info', 'system' => 'dark', 'vendor' => 'dark');
    return isset($map[$v]) ? $map[$v] : 'secondary';
};
?>
<div class="nk-block">
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-clock-history"></i> System Activity Logs</h3>
                <div class="nk-block-des text-soft">
                    <p>Audit trail of actions, page views and system events</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="nk-block-tools">
                    <a href="<?php echo base_url('activity_logs/export' . ($qs ? '?' . $qs : '')); ?>" class="btn btn-light">
                        <i class="bi bi-download"></i> <span>Export CSV</span>
                    </a>
                    <?php if (isset($can_delete) && $can_delete): ?>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                        <i class="bi bi-trash"></i> <span>Clear Logs</span>
                    </button>
                    <?php endif; ?>
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

    <!-- Filters -->
    <div class="card card-bordered mb-3">
        <div class="card-body">
            <form method="get" action="<?php echo base_url('activity_logs'); ?>" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Type</label>
                    <select name="log_type" class="form-select form-select-sm">
                        <option value="">All types</option>
                        <?php foreach ($log_types as $t): ?>
                            <option value="<?php echo $a($t); ?>" <?php echo ($filters['log_type'] === $t) ? 'selected' : ''; ?>><?php echo $a($t); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Module</label>
                    <select name="module" class="form-select form-select-sm">
                        <option value="">All modules</option>
                        <?php foreach ($modules as $m): ?>
                            <option value="<?php echo $a($m); ?>" <?php echo ($filters['module'] === $m) ? 'selected' : ''; ?>><?php echo $a($m); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Actor</label>
                    <select name="actor_type" class="form-select form-select-sm">
                        <option value="">All actors</option>
                        <?php foreach ($actor_types as $at): ?>
                            <option value="<?php echo $a($at); ?>" <?php echo ($filters['actor_type'] === $at) ? 'selected' : ''; ?>><?php echo $a($at); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?php echo $a($s); ?>" <?php echo ($filters['status'] === $s) ? 'selected' : ''; ?>><?php echo $a($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Severity</label>
                    <select name="severity" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach ($severities as $s): ?>
                            <option value="<?php echo $a($s); ?>" <?php echo ($filters['severity'] === $s) ? 'selected' : ''; ?>><?php echo $a($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i> Filter</button>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date from</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo $a($filters['date_from']); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date to</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo $a($filters['date_to']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search description, module, actor, IP, URL…" value="<?php echo $a($filters['search']); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">&nbsp;</label>
                    <a href="<?php echo base_url('activity_logs'); ?>" class="btn btn-light btn-sm w-100"><i class="bi bi-x-circle"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-bordered">
        <div class="card-inner">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small"><?php echo number_format($total); ?> record<?php echo $total == 1 ? '' : 's'; ?> found</span>
                <?php if ($total > $per_page): ?>
                    <span class="text-muted small">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Actor</th>
                            <th>Status</th>
                            <th>Severity</th>
                            <th style="width: 60px;">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo (int) $log->id; ?></td>
                                    <td class="text-nowrap"><?php echo $a(date('M j, Y H:i', strtotime($log->created_at))); ?></td>
                                    <td><span class="badge bg-<?php echo $badge($log->log_type); ?>"><?php echo $a($log->log_type); ?></span></td>
                                    <td><?php echo $a($log->module); ?></td>
                                    <td><code><?php echo $a($log->action); ?></code></td>
                                    <td><?php echo $a(mb_strimwidth((string) $log->description, 0, 80, '…')); ?></td>
                                    <td>
                                        <?php if ($log->actor_name): ?>
                                            <span class="badge bg-<?php echo $actor($log->actor_type); ?>"><?php echo $a($log->actor_type); ?></span>
                                            <div class="small text-muted mt-1"><?php echo $a($log->actor_name); ?></div>
                                        <?php else: ?>
                                            <span class="badge bg-<?php echo $actor($log->actor_type); ?>"><?php echo $a($log->actor_type); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-<?php echo $log->status == 'failed' ? 'danger' : 'success'; ?>"><?php echo $a($log->status); ?></span></td>
                                    <td><span class="badge bg-<?php echo $sev($log->severity); ?>"><?php echo $a($log->severity); ?></span></td>
                                    <td>
                                        <a href="<?php echo base_url('activity_logs/view/' . (int) $log->id); ?>" class="btn btn-sm btn-info" title="View details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">No activity logs found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo base_url('activity_logs?page=' . ($page - 1) . ($qs ? '&' . $qs : '')); ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 3); $i <= min($total_pages, $page + 3); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo base_url('activity_logs?page=' . $i . ($qs ? '&' . $qs : '')); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo base_url('activity_logs?page=' . ($page + 1) . ($qs ? '&' . $qs : '')); ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (isset($can_delete) && $can_delete): ?>
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?php echo base_url('activity_logs/clear'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger"></i> Clear Activity Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Delete activity log entries. This cannot be undone.</p>
                    <div class="mb-3">
                        <label class="form-label">Delete entries older than this date (leave blank to delete everything)</label>
                        <input type="date" name="before" class="form-control">
                        <div class="form-text">Entries with a timestamp <em>before</em> this date are removed.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('This permanently deletes activity logs. Continue?');">
                        <i class="bi bi-trash"></i> Delete Logs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
