<div class="nk-block">
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-door-open"></i> Manage Rooms</h3>
                <div class="nk-block-des text-soft">
                    <p>View and manage all room types and availability</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li>
                                <a href="<?php echo base_url('rooms/calendar'); ?>" class="btn btn-outline-light">
                                    <i class="bi bi-calendar-check"></i> <span>View Calendar</span>
                                </a>
                            </li>
                            <?php if (isset($can_add) && $can_add): ?>
                            <li>
                                <a href="<?php echo base_url('room_settings'); ?>" class="btn btn-outline-light">
                                    <i class="bi bi-gear"></i> <span>Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo base_url('rooms/add'); ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> <span>Add New Room</span>
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
    
    <div class="card card-bordered mob-desktop-table mob-desktop-table--xl">
        <div class="card-inner">
            <div class="table-responsive dt-fit-wrap">
        <table class="table table-hover dt-fit-width" id="roomsTable" style="width:100%">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-name">Room</th>
                    <th>Type</th>
                    <th class="col-amount">Price</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th class="col-actions" data-orderable="false">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rooms)): ?>
                    <?php foreach ($rooms as $room): ?>
                        <?php $room_code = !empty($room->room_code) ? $room->room_code : null; ?>
                        <tr>
                            <td class="col-id text-muted"><?php echo (int) $room->id; ?></td>
                            <td class="col-name">
                                <div class="dt-cell-title"><?php echo htmlspecialchars($room->room_name); ?></div>
                                <?php if ($room_code): ?>
                                <div class="dt-cell-sub"><code><?php echo htmlspecialchars($room_code); ?></code></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($room->room_type); ?></td>
                            <td class="col-amount fw-semibold" data-order="<?php echo (float) $room->price; ?>">₱<?php echo number_format($room->price, 2); ?></td>
                            <td data-order="<?php echo (int) $room->capacity; ?>"><i class="bi bi-people text-muted"></i> <?php echo (int) $room->capacity; ?> person(s)</td>
                            <td>
                                <span class="badge bg-<?php echo $room->status == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($room->status); ?>
                                </span>
                            </td>
                            <td class="col-actions">
                                <?php if (isset($can_edit) && $can_edit): ?>
                                <a href="<?php echo base_url('rooms/edit/' . $room->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (isset($can_delete) && $can_delete): ?>
                                <a href="<?php echo base_url('rooms/delete/' . $room->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this room?');" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (isset($can_add) && $can_add): ?>
                                <a href="<?php echo base_url('rooms/duplicate/' . $room->id); ?>" class="btn btn-sm btn-info" onclick="return confirm('Duplicate this room?');" title="Duplicate">
                                    <i class="bi bi-copy"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No rooms found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>

    <div class="mob-card-list mob-card-list--xl d-xl-none">
        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $room): ?>
                <?php $room_code = !empty($room->room_code) ? $room->room_code : null; ?>
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="mob-list-card-title"><?php echo htmlspecialchars($room->room_name); ?></div>
                            <div class="mob-list-card-meta text-truncate">
                                #<?php echo (int) $room->id; ?>
                                <?php if ($room_code): ?>&middot; <code><?php echo htmlspecialchars($room_code); ?></code><?php endif; ?>
                            </div>
                        </div>
                        <span class="badge bg-<?php echo $room->status == 'active' ? 'success' : 'secondary'; ?> flex-shrink-0">
                            <?php echo ucfirst($room->status); ?>
                        </span>
                    </div>
                    <div class="mob-list-card-body">
                        <div class="mob-list-card-meta">
                            <i class="bi bi-tag"></i> <?php echo htmlspecialchars($room->room_type); ?>
                        </div>
                        <div class="mob-list-card-meta">
                            <i class="bi bi-people"></i> <?php echo (int) $room->capacity; ?> person(s)
                        </div>
                        <div class="mob-list-card-meta mt-2">
                            <span class="fw-semibold fs-6 text-body">&#8369;<?php echo number_format($room->price, 2); ?></span>
                        </div>
                    </div>
                    <?php if ((isset($can_edit) && $can_edit) || (isset($can_delete) && $can_delete) || (isset($can_add) && $can_add)): ?>
                    <div class="mob-list-card-actions">
                        <?php if (isset($can_edit) && $can_edit): ?>
                        <a href="<?php echo base_url('rooms/edit/' . $room->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <?php endif; ?>
                        <?php if (isset($can_add) && $can_add): ?>
                        <a href="<?php echo base_url('rooms/duplicate/' . $room->id); ?>" class="btn btn-sm btn-info" onclick="return confirm('Duplicate this room?');" title="Duplicate">
                            <i class="bi bi-copy"></i> Duplicate
                        </a>
                        <?php endif; ?>
                        <?php if (isset($can_delete) && $can_delete): ?>
                        <a href="<?php echo base_url('rooms/delete/' . $room->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this room?');" title="Delete">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mob-list-card mob-list-card-empty text-center text-muted py-4">No rooms found</div>
        <?php endif; ?>
    </div>
</div>

