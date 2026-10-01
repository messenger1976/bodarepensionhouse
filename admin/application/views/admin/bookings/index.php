<?php
$filters = isset($filters) ? $filters : array();
$f_status = isset($filters['status']) ? $filters['status'] : '';
$f_range = isset($filters['range']) ? $filters['range'] : 'all';
$f_field = isset($filters['date_field']) ? $filters['date_field'] : 'stay';
$f_from = isset($filters['date_from']) ? $filters['date_from'] : '';
$f_to = isset($filters['date_to']) ? $filters['date_to'] : '';
$f_q = isset($filters['q']) ? $filters['q'] : '';
$f_sort = isset($filters['sort']) ? $filters['sort'] : 'booking';
$f_dir = isset($filters['dir']) ? $filters['dir'] : 'desc';
$f_per_page = isset($filters['per_page']) ? (int) $filters['per_page'] : 25;
$f_page = isset($filters['page']) ? (int) $filters['page'] : 1;
$status_counts = isset($status_counts) ? $status_counts : array();
$total_rows = isset($total_rows) ? (int) $total_rows : 0;
$total_pages = isset($total_pages) ? (int) $total_pages : 1;
$offset = isset($offset) ? (int) $offset : 0;

$date_field_labels = array(
    'stay'      => 'Stay dates',
    'check_in'  => 'Check-in date',
    'check_out' => 'Check-out date',
    'created'   => 'Booked on',
);
$status_list = array('pending', 'confirmed', 'checked_in', 'checked_out', 'completed', 'cancelled');
$status_badges = array(
    'pending' => 'warning', 'confirmed' => 'success', 'checked_in' => 'info',
    'checked_out' => 'primary', 'completed' => 'primary', 'cancelled' => 'danger',
);

$fmt_range = function ($from, $to) {
    if ($from && $to) {
        if ($from === $to) {
            return date('M j, Y', strtotime($from));
        }
        $s = strtotime($from);
        $e = strtotime($to);
        return (date('Y', $s) === date('Y', $e) ? date('M j', $s) : date('M j, Y', $s)) . ' – ' . date('M j, Y', $e);
    }
    if ($from) {
        return 'from ' . date('M j, Y', strtotime($from));
    }
    if ($to) {
        return 'until ' . date('M j, Y', strtotime($to));
    }
    return '';
};

$sort_link = function ($key, $label) use ($f_sort, $f_dir) {
    $active = $f_sort === $key;
    $next_dir = ($active && $f_dir === 'desc') ? 'asc' : 'desc';
    if (!$active && in_array($key, array('guest', 'status'), true)) {
        $next_dir = 'asc';
    }
    $icon = $active ? ($f_dir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill') : 'bi-chevron-expand';
    return '<a href="' . base_url('bookings?sort=' . $key . '&dir=' . $next_dir) . '" class="bk-sort' . ($active ? ' is-active' : '') . '">'
        . $label . ' <i class="bi ' . $icon . '"></i></a>';
};

$has_date_filter = ($f_from !== '' || $f_to !== '');
$has_any_filter = $f_status !== '' || $has_date_filter || $f_q !== '';
$first_row = $total_rows > 0 ? $offset + 1 : 0;
$last_row = $offset + (isset($bookings) ? count($bookings) : 0);
?>
<div class="nk-block">
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-calendar-check"></i> Manage Bookings</h3>
                <div class="nk-block-des text-soft">
                    <p>View and manage all booking reservations</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <?php if (isset($can_delete) && $can_delete): ?>
                            <li>
                                <button type="submit" form="bookings-batch-form" id="batch-delete-bookings-btn" class="btn btn-danger" disabled>
                                    <i class="bi bi-trash"></i> <span>Delete Selected<span data-bd-count></span></span>
                                </button>
                            </li>
                            <?php endif; ?>
                            <?php if (isset($can_add) && $can_add): ?>
                            <li>
                                <a href="<?php echo base_url('booking_settings'); ?>" class="btn btn-outline-light">
                                    <i class="bi bi-gear"></i> <span>Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo base_url('bookings/add'); ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> <span>Add New Booking</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
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

    <div class="card card-bordered mb-3 bk-filters">
        <div class="card-inner">
            <div class="bk-status-tabs mb-3" role="group" aria-label="Filter by status">
                <a href="<?php echo base_url('bookings?status='); ?>" class="btn btn-sm <?php echo $f_status === '' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    All <span class="bk-count-pill"><?php echo isset($status_counts['all']) ? (int) $status_counts['all'] : 0; ?></span>
                </a>
                <?php foreach ($status_list as $st): ?>
                <a href="<?php echo base_url('bookings?status=' . $st); ?>" class="btn btn-sm <?php echo $f_status === $st ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    <?php echo ucwords(str_replace('_', ' ', $st)); ?>
                    <span class="bk-count-pill"><?php echo isset($status_counts[$st]) ? (int) $status_counts[$st] : 0; ?></span>
                </a>
                <?php endforeach; ?>
            </div>

            <form method="get" action="<?php echo base_url('bookings'); ?>" id="bookings-filter-form" class="row g-2 align-items-end">
                <div class="col-12 col-lg-3">
                    <label for="bk-q" class="form-label small mb-1">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" id="bk-q" name="q" class="form-control" maxlength="100"
                               value="<?php echo htmlspecialchars($f_q); ?>" placeholder="Booking #, guest, email, phone">
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label for="bk-date-field" class="form-label small mb-1">Date applies to</label>
                    <select id="bk-date-field" name="date_field" class="form-select">
                        <?php foreach ($date_field_labels as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $f_field === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label for="bk-range" class="form-label small mb-1">Date range</label>
                    <select id="bk-range" name="range" class="form-select">
                        <?php foreach ($range_presets as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $f_range === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label for="bk-from" class="form-label small mb-1">From</label>
                    <input type="date" id="bk-from" name="date_from" class="form-control" value="<?php echo htmlspecialchars($f_from); ?>">
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label for="bk-to" class="form-label small mb-1">To</label>
                    <input type="date" id="bk-to" name="date_to" class="form-control" value="<?php echo htmlspecialchars($f_to); ?>">
                </div>
                <div class="col-12 col-lg-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1" title="Apply filters">
                        <i class="bi bi-funnel"></i><span class="d-lg-none ms-1">Apply</span>
                    </button>
                    <a href="<?php echo base_url('bookings?reset=1'); ?>" class="btn btn-outline-light" title="Reset filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>

            <?php if ($has_any_filter): ?>
            <div class="bk-active-filters mt-3">
                <span class="text-soft small me-1">Active filters:</span>
                <?php if ($f_status !== ''): ?>
                    <a href="<?php echo base_url('bookings?status='); ?>" class="badge rounded-pill bg-light text-dark border" title="Clear status">
                        Status: <?php echo ucwords(str_replace('_', ' ', $f_status)); ?> <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
                <?php if ($has_date_filter): ?>
                    <a href="<?php echo base_url('bookings?range=all'); ?>" class="badge rounded-pill bg-light text-dark border" title="Clear date range">
                        <?php echo $date_field_labels[$f_field]; ?>: <?php echo htmlspecialchars($fmt_range($f_from, $f_to)); ?> <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
                <?php if ($f_q !== ''): ?>
                    <a href="<?php echo base_url('bookings?q='); ?>" class="badge rounded-pill bg-light text-dark border" title="Clear search">
                        Search: “<?php echo htmlspecialchars($f_q); ?>” <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 bk-list-toolbar">
        <div class="text-soft small">
            <?php if ($total_rows > 0): ?>
                Showing <strong><?php echo number_format($first_row); ?>–<?php echo number_format($last_row); ?></strong>
                of <strong><?php echo number_format($total_rows); ?></strong> booking<?php echo $total_rows === 1 ? '' : 's'; ?>
            <?php else: ?>
                No bookings to show
            <?php endif; ?>
        </div>
        <form method="get" action="<?php echo base_url('bookings'); ?>" class="d-flex align-items-center gap-2">
            <label for="bk-per-page" class="small text-soft mb-0">Show</label>
            <select id="bk-per-page" name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <?php foreach (array(10, 25, 50, 100) as $n): ?>
                <option value="<?php echo $n; ?>" <?php echo $f_per_page === $n ? 'selected' : ''; ?>><?php echo $n; ?></option>
                <?php endforeach; ?>
            </select>
            <span class="small text-soft">per page</span>
            <noscript><button type="submit" class="btn btn-sm btn-outline-light">Go</button></noscript>
        </form>
    </div>

    <form id="bookings-batch-form" method="post" action="<?php echo base_url('bookings/batch_delete'); ?>">
    <div class="card card-bordered mob-desktop-table mob-desktop-table--xl">
        <div class="card-inner">
            <div class="table-responsive bookings-table-wrap">
        <table class="table table-hover dt-fit-width no-datatables mb-0" id="bookingsTable" style="width:100%">
            <thead>
                <tr>
                    <?php if (isset($can_delete) && $can_delete): ?>
                    <th class="col-select">
                        <input type="checkbox" class="form-check-input" data-bd-select-all="bookings-batch-form" title="Select all on this page">
                    </th>
                    <?php endif; ?>
                    <th class="col-booking"><?php echo $sort_link('booking', 'Booking #'); ?></th>
                    <th class="col-guest"><?php echo $sort_link('guest', 'Guest'); ?></th>
                    <th class="col-stay"><?php echo $sort_link('stay', 'Stay'); ?></th>
                    <th class="col-count"><?php echo $sort_link('guests', 'Guests / Rooms'); ?></th>
                    <th class="col-status"><?php echo $sort_link('status', 'Status'); ?></th>
                    <th class="col-payment">Payment</th>
                    <th class="col-amount"><?php echo $sort_link('amount', 'Amount'); ?></th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <?php
                        $check_in_ts = strtotime($booking->check_in);
                        $check_out_ts = strtotime($booking->check_out);
                        $nights = max(1, (int) round(($check_out_ts - $check_in_ts) / 86400));
                        $room_count = isset($booking->rooms) ? (int) $booking->rooms : 1;
                        $badge_class = isset($status_badges[$booking->status]) ? $status_badges[$booking->status] : 'secondary';
                        $ps = isset($payment_status_map[$booking->id]) ? $payment_status_map[$booking->id] : null;
                        ?>
                        <tr>
                            <?php if (isset($can_delete) && $can_delete): ?>
                            <td class="col-select">
                                <input type="checkbox" class="form-check-input booking-select-checkbox" name="booking_ids[]" value="<?php echo (int) $booking->id; ?>">
                            </td>
                            <?php endif; ?>
                            <td class="col-booking fw-semibold">
                                <a href="<?php echo base_url('bookings/' . $booking->id); ?>">#<?php echo isset($booking->booking_number) ? htmlspecialchars($booking->booking_number) : str_pad($booking->id, 6, '0', STR_PAD_LEFT); ?></a>
                            </td>
                            <td class="col-guest">
                                <div class="bk-guest-name"><?php echo htmlspecialchars($booking->guest_name); ?></div>
                                <div class="bk-guest-email text-muted" title="<?php echo htmlspecialchars($booking->guest_email); ?>"><?php echo htmlspecialchars($booking->guest_email); ?></div>
                            </td>
                            <td class="col-stay">
                                <div class="bk-stay-line">
                                    <i class="bi bi-box-arrow-in-right text-success"></i>
                                    <?php echo date('M d, Y', $check_in_ts); ?>
                                    <?php if (!empty($booking->check_in_time)): ?>
                                        <span class="text-muted"><?php echo date('g:i A', strtotime($booking->check_in_time)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bk-stay-line">
                                    <i class="bi bi-box-arrow-right text-danger"></i>
                                    <?php echo date('M d, Y', $check_out_ts); ?>
                                    <?php if (!empty($booking->check_out_time)): ?>
                                        <span class="text-muted"><?php echo date('g:i A', strtotime($booking->check_out_time)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bk-stay-nights text-muted"><?php echo $nights; ?> night<?php echo $nights === 1 ? '' : 's'; ?></div>
                            </td>
                            <td class="col-count">
                                <span class="bk-count" title="Guests"><i class="bi bi-people"></i> <?php echo (int) $booking->guests; ?></span>
                                <span class="bk-count" title="Rooms"><i class="bi bi-door-closed"></i> <?php echo $room_count; ?></span>
                            </td>
                            <td class="col-status">
                                <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucwords(str_replace('_', ' ', $booking->status)); ?></span>
                            </td>
                            <td class="col-payment">
                                <?php if ($ps): ?>
                                    <span class="badge bg-<?php echo $ps['badge']; ?>" title="Paid: ₱<?php echo number_format($ps['amount_paid'], 2); ?> / Balance: ₱<?php echo number_format($ps['balance'], 2); ?>">
                                        <?php echo htmlspecialchars($ps['display']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No Invoice</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-amount fw-semibold">₱<?php echo number_format($booking->total_amount, 2); ?></td>
                            <td class="col-actions">
                                <a href="<?php echo base_url('bookings/' . $booking->id); ?>" class="btn btn-sm btn-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (isset($can_edit) && $can_edit): ?>
                                <a href="<?php echo base_url('bookings/edit/' . $booking->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (isset($can_delete) && $can_delete): ?>
                                <a href="<?php echo base_url('bookings/delete/' . $booking->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this booking?');" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo (isset($can_delete) && $can_delete) ? '9' : '8'; ?>" class="text-center text-muted py-4">
                            <?php if ($has_any_filter): ?>
                                No bookings match these filters. <a href="<?php echo base_url('bookings?reset=1'); ?>">Reset filters</a>
                            <?php else: ?>
                                No bookings found
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>

    <div class="mob-card-list mob-card-list--xl d-xl-none">
        <?php if (!empty($bookings) && isset($can_delete) && $can_delete): ?>
        <div class="d-flex align-items-center gap-2 mb-2 px-1">
            <input type="checkbox" class="form-check-input" id="bookings-mobile-select-all" data-bd-select-all="bookings-batch-form" title="Select all">
            <label class="form-check-label small text-muted" for="bookings-mobile-select-all">Select all on this page</label>
        </div>
        <?php endif; ?>
        <?php if (!empty($bookings)): ?>
            <?php foreach ($bookings as $booking): ?>
                <?php
                $badge_class = isset($status_badges[$booking->status]) ? $status_badges[$booking->status] : 'secondary';
                $booking_num = isset($booking->booking_number) ? htmlspecialchars($booking->booking_number) : str_pad($booking->id, 6, '0', STR_PAD_LEFT);
                $ps = isset($payment_status_map[$booking->id]) ? $payment_status_map[$booking->id] : null;
                $check_in_ts = strtotime($booking->check_in);
                $check_out_ts = strtotime($booking->check_out);
                $nights = max(1, (int) round(($check_out_ts - $check_in_ts) / 86400));
                $room_count = isset($booking->rooms) ? (int) $booking->rooms : 1;
                ?>
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <?php if (isset($can_delete) && $can_delete): ?>
                        <input type="checkbox" class="form-check-input booking-select-checkbox me-2 flex-shrink-0" name="booking_ids[]" value="<?php echo (int) $booking->id; ?>" aria-label="Select booking">
                        <?php endif; ?>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="mob-list-card-title"><?php echo htmlspecialchars($booking->guest_name); ?></div>
                            <div class="mob-list-card-meta text-truncate" title="<?php echo htmlspecialchars($booking->guest_email); ?>">#<?php echo $booking_num; ?> &middot; <?php echo htmlspecialchars($booking->guest_email); ?></div>
                        </div>
                        <span class="badge bg-<?php echo $badge_class; ?> flex-shrink-0"><?php echo ucwords(str_replace('_', ' ', $booking->status)); ?></span>
                    </div>
                    <div class="mob-list-card-body">
                        <div class="mob-list-card-meta">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <?php echo date('M d, Y', $check_in_ts); ?>
                            <?php if (!empty($booking->check_in_time)): ?><?php echo date('g:i A', strtotime($booking->check_in_time)); ?><?php endif; ?>
                        </div>
                        <div class="mob-list-card-meta">
                            <i class="bi bi-box-arrow-right"></i>
                            <?php echo date('M d, Y', $check_out_ts); ?>
                            <?php if (!empty($booking->check_out_time)): ?><?php echo date('g:i A', strtotime($booking->check_out_time)); ?><?php endif; ?>
                        </div>
                        <div class="mob-list-card-meta">
                            <i class="bi bi-moon"></i> <?php echo $nights; ?> night<?php echo $nights === 1 ? '' : 's'; ?>
                            &middot; <?php echo (int) $booking->guests; ?> guest<?php echo (int) $booking->guests === 1 ? '' : 's'; ?>
                            &middot; <?php echo $room_count; ?> room<?php echo $room_count === 1 ? '' : 's'; ?>
                        </div>
                        <div class="mob-list-card-meta d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
                            <?php if ($ps): ?>
                                <span class="badge bg-<?php echo $ps['badge']; ?>" title="Paid: &#8369;<?php echo number_format($ps['amount_paid'], 2); ?> / Balance: &#8369;<?php echo number_format($ps['balance'], 2); ?>">
                                    <?php echo htmlspecialchars($ps['display']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No Invoice</span>
                            <?php endif; ?>
                            <span class="fw-semibold fs-6 text-body">&#8369;<?php echo number_format($booking->total_amount, 2); ?></span>
                        </div>
                    </div>
                    <div class="mob-list-card-actions">
                        <a href="<?php echo base_url('bookings/' . $booking->id); ?>" class="btn btn-sm btn-primary" title="View">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <?php if (isset($can_edit) && $can_edit): ?>
                        <a href="<?php echo base_url('bookings/edit/' . $booking->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <?php endif; ?>
                        <?php if (isset($can_delete) && $can_delete): ?>
                        <a href="<?php echo base_url('bookings/delete/' . $booking->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this booking?');" title="Delete">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mob-list-card mob-list-card-empty text-center text-muted py-4">
                <?php if ($has_any_filter): ?>
                    No bookings match these filters. <a href="<?php echo base_url('bookings?reset=1'); ?>">Reset filters</a>
                <?php else: ?>
                    No bookings found
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    </form>

    <?php if ($total_pages > 1): ?>
    <?php
    $window = 2;
    $start = max(1, $f_page - $window);
    $end = min($total_pages, $f_page + $window);
    $page_url = function ($p) { return base_url('bookings?page=' . (int) $p); };
    ?>
    <nav class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3" aria-label="Bookings pages">
        <div class="small text-soft">Page <?php echo $f_page; ?> of <?php echo $total_pages; ?></div>
        <ul class="pagination pagination-sm mb-0 flex-wrap">
            <li class="page-item <?php echo $f_page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo $page_url($f_page - 1); ?>" aria-label="Previous"><i class="bi bi-chevron-left"></i></a>
            </li>
            <?php if ($start > 1): ?>
                <li class="page-item"><a class="page-link" href="<?php echo $page_url(1); ?>">1</a></li>
                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
            <?php endif; ?>
            <?php for ($p = $start; $p <= $end; $p++): ?>
                <li class="page-item <?php echo $p === $f_page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo $page_url($p); ?>"><?php echo $p; ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
                <li class="page-item"><a class="page-link" href="<?php echo $page_url($total_pages); ?>"><?php echo $total_pages; ?></a></li>
            <?php endif; ?>
            <li class="page-item <?php echo $f_page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo $page_url($f_page + 1); ?>" aria-label="Next"><i class="bi bi-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    <?php
    if (!empty($can_delete)) {
        $this->load->view('admin/layout/batch_delete', array(
            'bd_form_id' => 'bookings-batch-form',
            'bd_checkbox_class' => 'booking-select-checkbox',
            'bd_button_id' => 'batch-delete-bookings-btn',
            'bd_label' => 'booking'
        ));
    }
    ?>
</div>

<style>
    .bk-status-tabs { display: flex; flex-wrap: wrap; gap: .375rem; }
    .bk-status-tabs .btn { display: inline-flex; align-items: center; gap: .375rem; }
    .bk-count-pill {
        display: inline-block; min-width: 1.5rem; padding: 0 .4rem; border-radius: 1rem;
        font-size: .7rem; line-height: 1.25rem; text-align: center;
        background: rgba(0, 0, 0, .08);
    }
    .bk-status-tabs .btn-primary .bk-count-pill { background: rgba(255, 255, 255, .25); }
    .bk-active-filters { display: flex; flex-wrap: wrap; align-items: center; gap: .375rem; }
    .bk-active-filters .badge { font-weight: 500; text-decoration: none; padding: .35em .7em; }
    .bk-active-filters .badge:hover { background: #e9ecef !important; }
    #bookingsTable thead th { white-space: nowrap; }
    #bookingsTable .bk-sort { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: .25rem; }
    #bookingsTable .bk-sort .bi { font-size: .7rem; opacity: .45; }
    #bookingsTable .bk-sort.is-active .bi { opacity: 1; }
    #bookingsTable .bk-sort:hover { color: var(--bs-primary, #6576ff); }
    body.dark-mode .bk-count-pill { background: rgba(255, 255, 255, .12); }
    body.dark-mode .bk-active-filters .badge { background: transparent !important; color: inherit !important; }
</style>

<script>
(function () {
    var rangeDates = <?php echo json_encode(isset($range_dates) ? $range_dates : array()); ?>;
    var form = document.getElementById('bookings-filter-form');
    if (!form) { return; }
    var range = document.getElementById('bk-range');
    var from = document.getElementById('bk-from');
    var to = document.getElementById('bk-to');

    range.addEventListener('change', function () {
        if (range.value === 'custom') {
            from.focus();
            return;
        }
        var d = rangeDates[range.value] || ['', ''];
        from.value = d[0];
        to.value = d[1];
        form.submit();
    });

    function markCustom() {
        range.value = (from.value || to.value) ? 'custom' : 'all';
    }
    from.addEventListener('change', markCustom);
    to.addEventListener('change', markCustom);

    document.getElementById('bk-date-field').addEventListener('change', function () {
        if (from.value || to.value) { form.submit(); }
    });
})();
</script>
