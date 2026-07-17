<?php
$filter_status = isset($filter_status) ? $filter_status : '';
$filter_range = isset($filter_range) ? $filter_range : 'today';
$filter_date_from = isset($filter_date_from) ? $filter_date_from : date('Y-m-d');
$filter_date_to = isset($filter_date_to) ? $filter_date_to : date('Y-m-d');
$date_query = isset($date_query) ? $date_query : ('range=today&date_from=' . date('Y-m-d') . '&date_to=' . date('Y-m-d'));
$can_delete = !empty($can_delete);
$can_edit = !empty($can_edit);

$statusLinkBase = base_url('inquiries');
$buildStatusUrl = function ($status = '') use ($statusLinkBase, $date_query) {
    $params = array();
    if ($status !== '') {
        $params[] = 'status=' . urlencode($status);
    }
    if ($date_query !== '') {
        $params[] = $date_query;
    }
    return $statusLinkBase . (!empty($params) ? ('?' . implode('&', $params)) : '');
};

$fetchRedirect = 'inquiries';
$fetchParams = array();
if ($filter_status) {
    $fetchParams[] = 'status=' . urlencode($filter_status);
}
if ($date_query) {
    $fetchParams[] = $date_query;
}
if (!empty($fetchParams)) {
    $fetchRedirect .= '?' . implode('&', $fetchParams);
}
?>
<div class="content-card">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="mb-1"><i class="bi bi-envelope"></i> Contact Inquiries (<?php echo (int) $counts['all']; ?>)</h5>
            <p class="text-muted mb-0 small">Latest inquiry on <?php echo getCreateDate('inquiryid', 'inquiry'); ?></p>
        </div>
        <?php if ($can_edit): ?>
        <form action="<?php echo base_url('inquiries/fetchinbound'); ?>" method="post" class="m-0">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($fetchRedirect, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="btn btn-sm btn-info">
                <i class="bi bi-arrow-repeat"></i> Check Email Replies
            </button>
        </form>
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

    <div class="mb-3 d-flex flex-wrap gap-1">
        <a href="<?php echo $buildStatusUrl(''); ?>" class="btn btn-sm <?php echo empty($filter_status) ? 'btn-primary' : 'btn-outline-primary'; ?>">All (<?php echo (int) $counts['all']; ?>)</a>
        <a href="<?php echo $buildStatusUrl('new'); ?>" class="btn btn-sm <?php echo $filter_status === 'new' ? 'btn-danger' : 'btn-outline-danger'; ?>">New (<?php echo (int) $counts['new']; ?>)</a>
        <a href="<?php echo $buildStatusUrl('guest_replied'); ?>" class="btn btn-sm <?php echo $filter_status === 'guest_replied' ? 'btn-primary' : 'btn-outline-primary'; ?>">Guest Replied (<?php echo (int) $counts['guest_replied']; ?>)</a>
        <a href="<?php echo $buildStatusUrl('read'); ?>" class="btn btn-sm <?php echo $filter_status === 'read' ? 'btn-warning' : 'btn-outline-warning'; ?>">Read (<?php echo (int) $counts['read']; ?>)</a>
        <a href="<?php echo $buildStatusUrl('replied'); ?>" class="btn btn-sm <?php echo $filter_status === 'replied' ? 'btn-success' : 'btn-outline-success'; ?>">Replied (<?php echo (int) $counts['replied']; ?>)</a>
        <a href="<?php echo $buildStatusUrl('closed'); ?>" class="btn btn-sm <?php echo $filter_status === 'closed' ? 'btn-secondary' : 'btn-outline-secondary'; ?>">Closed (<?php echo (int) $counts['closed']; ?>)</a>
    </div>

    <form id="inquiry-date-filter" class="row g-2 align-items-end mb-3" method="get" action="<?php echo base_url('inquiries'); ?>">
        <?php if ($filter_status) { ?>
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status, ENT_QUOTES, 'UTF-8'); ?>">
        <?php } ?>
        <div class="col-auto">
            <label class="form-label mb-0 small" for="inquiry-range">Date range</label>
            <select name="range" id="inquiry-range" class="form-select form-select-sm">
                <option value="today" <?php echo $filter_range === 'today' ? 'selected' : ''; ?>>Today</option>
                <option value="7" <?php echo $filter_range === '7' ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="30" <?php echo $filter_range === '30' ? 'selected' : ''; ?>>Last 30 Days</option>
                <option value="custom" <?php echo $filter_range === 'custom' ? 'selected' : ''; ?>>Custom</option>
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label mb-0 small" for="inquiry-date-from">From</label>
            <input type="date" class="form-control form-control-sm" name="date_from" id="inquiry-date-from" value="<?php echo htmlspecialchars($filter_date_from, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filter_range !== 'custom' ? 'readonly' : ''; ?>>
        </div>
        <div class="col-auto">
            <label class="form-label mb-0 small" for="inquiry-date-to">To</label>
            <input type="date" class="form-control form-control-sm" name="date_to" id="inquiry-date-to" value="<?php echo htmlspecialchars($filter_date_to, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filter_range !== 'custom' ? 'readonly' : ''; ?>>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover dtInquiry" id="inquiriesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                foreach ($inquiries as $row) {
                    $i++;
                    $badge = 'secondary';
                    if ($row->status === 'new') $badge = 'danger';
                    elseif ($row->status === 'guest_replied') $badge = 'primary';
                    elseif ($row->status === 'read') $badge = 'warning';
                    elseif ($row->status === 'replied') $badge = 'success';
                    elseif ($row->status === 'closed') $badge = 'info';
                    ?>
                    <tr class="inquiry-row-link" data-href="<?php echo base_url('inquiries/' . (int) $row->inquiryid); ?>" style="cursor:pointer;<?php echo in_array($row->status, array('new', 'guest_replied'), TRUE) ? 'font-weight:600;' : ''; ?>">
                        <td><?php echo $i; ?></td>
                        <td><?php echo htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row->email, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(character_limiter($row->subject, 40), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><span class="badge bg-<?php echo $badge; ?>"><?php echo ucwords(str_replace('_', ' ', $row->status)); ?></span></td>
                        <td><?php echo !empty($row->created_at) ? date('M j, Y g:i A', strtotime($row->created_at)) : $row->cdate; ?></td>
                        <td class="inquiry-row-actions" onclick="event.stopPropagation();">
                            <a href="<?php echo base_url('inquiries/' . (int) $row->inquiryid); ?>" class="btn btn-sm btn-primary" title="View"><i class="bi bi-eye"></i></a>
                            <?php if ($can_delete): ?>
                            <a href="<?php echo base_url('inquiries/delete/' . (int) $row->inquiryid); ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this inquiry permanently?');"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var range = document.getElementById('inquiry-range');
    var from = document.getElementById('inquiry-date-from');
    var to = document.getElementById('inquiry-date-to');
    if (range) {
        range.addEventListener('change', function () {
            var custom = range.value === 'custom';
            from.readOnly = !custom;
            to.readOnly = !custom;
        });
    }
    document.querySelectorAll('.inquiry-row-link').forEach(function (row) {
        row.addEventListener('click', function () {
            if (row.getAttribute('data-href')) {
                window.location = row.getAttribute('data-href');
            }
        });
    });
});
</script>
