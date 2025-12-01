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
    
    <div class="card card-bordered">
        <div class="card-inner">
            <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Room Name</th>
                    <th>Type</th>
                    <th>Room Code</th>
                    <th>Price</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rooms)): ?>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?php echo $room->id; ?></td>
                            <td><?php echo htmlspecialchars($room->room_name); ?></td>
                            <td><?php echo htmlspecialchars($room->room_type); ?></td>
                            <td><code><?php echo htmlspecialchars(isset($room->room_code) ? $room->room_code : '-'); ?></code></td>
                            <td>₱<?php echo number_format($room->price, 2); ?></td>
                            <td><?php echo $room->capacity; ?> person(s)</td>
                            <td>
                                <span class="badge bg-<?php echo $room->status == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($room->status); ?>
                                </span>
                            </td>
                            <td>
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
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No rooms found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div>

