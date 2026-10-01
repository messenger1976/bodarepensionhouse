<?php
$fmt_range = function ($start, $end) {
    $s = strtotime($start);
    $e = strtotime($end);
    if ($start === $end) {
        return date('M j, Y', $s);
    }
    if (date('Y', $s) === date('Y', $e)) {
        return date('M j', $s) . ' – ' . date('M j, Y', $e);
    }
    return date('M j, Y', $s) . ' – ' . date('M j, Y', $e);
};
$nights = function ($start, $end) {
    return (new DateTime($start))->diff(new DateTime($end))->days + 1;
};
$units_label = function ($block) {
    $total = (int)$block->available_rooms;
    if ($block->rooms_blocked === null || (int)$block->rooms_blocked >= $total) {
        return 'All ' . $total;
    }
    return (int)$block->rooms_blocked . ' of ' . $total;
};
$today = date('Y-m-d');
?>
<div class="nk-block">
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-calendar-x"></i> Blocked Dates</h3>
                <div class="nk-block-des text-soft">
                    <p>Take rooms off sale for maintenance, renovation, private use or holidays. Blocked rooms cannot be booked online or from the admin.</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li>
                                <a href="<?php echo base_url('rooms/calendar'); ?>" class="btn btn-outline-light">
                                    <i class="bi bi-calendar-check"></i> <span>Availability Calendar</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo base_url('rooms'); ?>" class="btn btn-outline-light">
                                    <i class="bi bi-door-open"></i> <span>Rooms</span>
                                </a>
                            </li>
                            <?php if ($can_manage && $table_ready): ?>
                            <li>
                                <a href="<?php echo base_url('room_blocks/add'); ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> <span>Block Dates</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$table_ready): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            Blocked dates are not set up on this database yet. Run <code>admin/sql/create_room_blocks_table.sql</code> to enable them.
        </div>
    <?php endif; ?>

    <?php foreach (array('success' => 'success', 'warning' => 'warning', 'error' => 'danger') as $key => $cls): ?>
        <?php if ($this->session->flashdata($key)): ?>
            <div class="alert alert-<?php echo $cls; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($this->session->flashdata($key)); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="card card-bordered mb-4">
        <div class="card-inner">
            <form method="get" action="<?php echo base_url('room_blocks'); ?>" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="scope" class="form-label">Show</label>
                    <select id="scope" name="scope" class="form-select" onchange="this.form.submit()">
                        <option value="upcoming" <?php echo $scope === 'upcoming' ? 'selected' : ''; ?>>Current &amp; upcoming</option>
                        <option value="past" <?php echo $scope === 'past' ? 'selected' : ''; ?>>Past</option>
                        <option value="all" <?php echo $scope === 'all' ? 'selected' : ''; ?>>All</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="room_id" class="form-label">Room</label>
                    <select id="room_id" name="room_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All rooms</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo (int)$room->id; ?>" <?php echo (int)$room_id === (int)$room->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($room->room_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-bordered mob-desktop-table mob-desktop-table--xl">
        <div class="card-inner">
            <div class="table-responsive dt-fit-wrap">
                <table class="table table-hover dt-fit-width" style="width:100%">
                    <thead>
                        <tr>
                            <th class="col-name">Room</th>
                            <th>Dates (nights blocked)</th>
                            <th>Rooms blocked</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <?php if ($can_manage): ?><th class="col-actions">Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($blocks)): ?>
                            <?php foreach ($blocks as $block): ?>
                                <?php $n = $nights($block->start_date, $block->end_date); ?>
                                <tr>
                                    <td class="col-name">
                                        <div class="dt-cell-title"><?php echo htmlspecialchars($block->room_name ?: 'Deleted room'); ?></div>
                                        <div class="dt-cell-sub"><?php echo htmlspecialchars((string)$block->room_type); ?></div>
                                    </td>
                                    <td data-order="<?php echo $block->start_date; ?>">
                                        <?php echo $fmt_range($block->start_date, $block->end_date); ?>
                                        <div class="dt-cell-sub"><?php echo $n; ?> night<?php echo $n === 1 ? '' : 's'; ?></div>
                                    </td>
                                    <td><?php echo $units_label($block); ?></td>
                                    <td><?php echo $block->reason !== null && $block->reason !== '' ? htmlspecialchars($block->reason) : '<span class="text-muted">—</span>'; ?></td>
                                    <td>
                                        <?php if ($block->end_date < $today): ?>
                                            <span class="badge bg-secondary">Ended</span>
                                        <?php elseif ($block->start_date <= $today): ?>
                                            <span class="badge bg-danger">In effect</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Upcoming</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($can_manage): ?>
                                    <td class="col-actions">
                                        <a href="<?php echo base_url('room_blocks/edit/' . $block->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?php echo base_url('room_blocks/delete/' . $block->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this block? The dates become bookable again.');" title="Remove">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $can_manage ? 6 : 5; ?>" class="text-center text-muted">No blocked dates</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mob-card-list mob-card-list--xl d-xl-none">
        <?php if (!empty($blocks)): ?>
            <?php foreach ($blocks as $block): ?>
                <?php $n = $nights($block->start_date, $block->end_date); ?>
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="mob-list-card-title"><?php echo htmlspecialchars($block->room_name ?: 'Deleted room'); ?></div>
                            <div class="mob-list-card-meta"><?php echo $fmt_range($block->start_date, $block->end_date); ?> &middot; <?php echo $n; ?> night<?php echo $n === 1 ? '' : 's'; ?></div>
                        </div>
                        <?php if ($block->end_date < $today): ?>
                            <span class="badge bg-secondary flex-shrink-0">Ended</span>
                        <?php elseif ($block->start_date <= $today): ?>
                            <span class="badge bg-danger flex-shrink-0">In effect</span>
                        <?php else: ?>
                            <span class="badge bg-warning flex-shrink-0">Upcoming</span>
                        <?php endif; ?>
                    </div>
                    <div class="mob-list-card-body">
                        <div class="mob-list-card-meta"><i class="bi bi-door-closed"></i> <?php echo $units_label($block); ?> room(s) blocked</div>
                        <?php if ($block->reason !== null && $block->reason !== ''): ?>
                            <div class="mob-list-card-meta"><i class="bi bi-chat-left-text"></i> <?php echo htmlspecialchars($block->reason); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if ($can_manage): ?>
                    <div class="mob-list-card-actions">
                        <a href="<?php echo base_url('room_blocks/edit/' . $block->id); ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                        <a href="<?php echo base_url('room_blocks/delete/' . $block->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this block? The dates become bookable again.');"><i class="bi bi-trash"></i> Remove</a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mob-list-card mob-list-card-empty text-center text-muted py-4">No blocked dates</div>
        <?php endif; ?>
    </div>
</div>
