<?php
$a = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$pretty = function ($v) {
    if ($v === NULL) {
        return null;
    }
    $json = json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return $json !== false ? $json : null;
};
$sev = array('info' => 'info', 'warning' => 'warning', 'critical' => 'danger');
?>
<div class="nk-block">
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-clock-history"></i> Activity Log #<?php echo (int) $log->id; ?></h3>
                <div class="nk-block-des text-soft">
                    <p><?php echo $a($log->created_at); ?></p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <a href="<?php echo base_url('activity_logs'); ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back to Logs</a>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $a($this->session->flashdata('error')); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-bordered mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="text-muted small">Type</div>
                    <span class="badge bg-<?php echo $log->log_type === 'crud' ? 'success' : ($log->log_type === 'security' ? 'danger' : ($log->log_type === 'page_view' ? 'secondary' : 'primary')); ?>"><?php echo $a($log->log_type); ?></span>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-muted small">Module</div>
                    <div><code><?php echo $a($log->module); ?></code></div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-muted small">Action</div>
                    <div><code><?php echo $a($log->action); ?></code></div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-muted small">Status / Severity</div>
                    <span class="badge bg-<?php echo $log->status == 'failed' ? 'danger' : 'success'; ?>"><?php echo $a($log->status); ?></span>
                    <span class="badge bg-<?php echo isset($sev[$log->severity]) ? $sev[$log->severity] : 'info'; ?>"><?php echo $a($log->severity); ?></span>
                </div>
            </div>

            <?php if (!empty($log->description)): ?>
            <hr>
            <h6 class="text-muted mt-0">Description</h6>
            <p class="mb-0"><?php echo $a($log->description); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-bordered mb-3">
                <div class="card-header"><i class="bi bi-person-circle"></i> Actor</div>
                <div class="card-body">
                    <table class="table table-sm table-bordered mb-0 no-datatables">
                        <tbody>
                            <tr><th style="width:40%">Actor Type</th><td><span class="badge bg-<?php echo $log->actor_type === 'admin' ? 'primary' : ($log->actor_type === 'customer' ? 'success' : 'secondary'); ?>"><?php echo $a($log->actor_type); ?></span></td></tr>
                            <tr><th>Actor ID</th><td><?php echo $log->actor_id !== null ? (int) $log->actor_id : '—'; ?></td></tr>
                            <tr><th>Actor Name</th><td><?php echo $a($log->actor_name); ?></td></tr>
                            <tr><th>IP Address</th><td><code><?php echo $a($log->ip_address); ?></code></td></tr>
                            <tr><th>Method</th><td><?php echo $a($log->request_method); ?></td></tr>
                            <tr><th>Entity</th><td><?php echo $a($log->entity_type) . ($log->entity_id !== null ? ' #' . (int) $log->entity_id : ''); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-bordered mb-3">
                <div class="card-header"><i class="bi bi-diagram-3"></i> Request</div>
                <div class="card-body">
                    <table class="table table-sm table-bordered mb-0 no-datatables">
                        <tbody>
                            <tr><th style="width:40%">URL</th><td class="break-word small"><?php echo $a($log->request_url); ?></td></tr>
                            <tr><th>Referrer</th><td class="break-word small"><?php echo $a($log->referrer); ?></td></tr>
                            <tr><th>User Agent</th><td class="small"><?php echo $a($log->user_agent); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-bordered mb-3">
                <div class="card-header"><i class="bi bi-arrow-left-circle"></i> Old Values (pre-change)</div>
                <div class="card-body">
                    <?php if ($old_values !== NULL): ?>
                        <pre class="json-pre"><?php echo $a($pretty($old_values)); ?></pre>
                    <?php else: ?>
                        <p class="text-muted mb-0">No previous state captured.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-bordered mb-3">
                <div class="card-header"><i class="bi bi-arrow-right-circle"></i> New Values (post-change)</div>
                <div class="card-body">
                    <?php if ($new_values !== NULL): ?>
                        <pre class="json-pre"><?php echo $a($pretty($new_values)); ?></pre>
                    <?php else: ?>
                        <p class="text-muted mb-0">No new state captured.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($metadata !== NULL): ?>
    <div class="card card-bordered mb-3">
        <div class="card-header"><i class="bi bi-journal-code"></i> Metadata</div>
        <div class="card-body">
            <pre class="json-pre"><?php echo $a($pretty($metadata)); ?></pre>
        </div>
    </div>
    <?php endif; ?>

    <style>
        .json-pre {
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            border-radius: .5rem;
            padding: .75rem 1rem;
            font-size: .8125rem;
            overflow: auto;
            max-height: 340px;
            white-space: pre-wrap;
            word-break: break-word;
            margin: 0;
        }
        body.dark-mode .json-pre {
            background: #0f172a;
            border-color: #334155;
            color: #cbd5e1;
        }
        .break-word {
            word-break: break-word;
        }
    </style>
</div>
