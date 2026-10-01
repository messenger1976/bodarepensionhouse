<?php
$action = $is_edit ? 'room_blocks/edit/' . (int)$block->id : 'room_blocks/add';
$mode = $block->rooms_blocked === null ? 'all' : 'some';
?>
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-calendar-x"></i> <?php echo $is_edit ? 'Edit Blocked Dates' : 'Block Dates'; ?></h5>
        <a href="<?php echo base_url('room_blocks'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $err): ?>
                <div><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php echo form_open($action, array('id' => 'room-block-form')); ?>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="room_id" class="form-label">Room *</label>
                <select class="form-select" id="room_id" name="room_id" required>
                    <option value="">Choose a room…</option>
                    <?php if (!$is_edit): ?>
                        <option value="all" data-units="" <?php echo $block->room_id === 'all' ? 'selected' : ''; ?>>All active room types</option>
                    <?php endif; ?>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo (int)$room->id; ?>" data-units="<?php echo (int)$room->available_rooms; ?>"
                            <?php echo $block->room_id !== 'all' && (int)$block->room_id === (int)$room->id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($room->room_name); ?> (<?php echo (int)$room->available_rooms; ?> room<?php echo (int)$room->available_rooms === 1 ? '' : 's'; ?>)<?php echo $room->status !== 'active' ? ' — inactive' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="start_date" class="form-label">From (first night) *</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($block->start_date); ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label for="end_date" class="form-label">To (last night) *</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($block->end_date); ?>" required>
            </div>
            <div class="col-md-6 mb-3 d-flex align-items-end">
                <small class="form-text text-muted" id="block-summary"></small>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label d-block">How many rooms to block *</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="block_mode" id="block_mode_all" value="all" <?php echo $mode === 'all' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="block_mode_all">All rooms of this type (fully closed)</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="block_mode" id="block_mode_some" value="some" <?php echo $mode === 'some' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="block_mode_some">Only some</label>
            </div>
            <div class="mt-2" id="rooms-blocked-wrap" style="max-width: 220px;">
                <input type="number" class="form-control" id="rooms_blocked" name="rooms_blocked" min="1" value="<?php echo $block->rooms_blocked !== null ? (int)$block->rooms_blocked : 1; ?>">
                <small class="form-text text-muted" id="rooms-blocked-help"></small>
            </div>
        </div>

        <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <input type="text" class="form-control" id="reason" name="reason" maxlength="255" value="<?php echo htmlspecialchars((string)$block->reason); ?>" placeholder="e.g., Aircon repair, Renovation, Private event">
            <small class="form-text text-muted">Shown to staff on the calendar only — guests never see it.</small>
        </div>

        <div class="alert alert-info small">
            <i class="bi bi-info-circle"></i>
            Blocking does not cancel existing bookings. If bookings already overlap these dates you will be warned after saving.
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('room_blocks'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Save Changes' : 'Block Dates'; ?></button>
        </div>
    <?php echo form_close(); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var roomSel = document.getElementById('room_id');
    var start = document.getElementById('start_date');
    var end = document.getElementById('end_date');
    var modeAll = document.getElementById('block_mode_all');
    var modeSome = document.getElementById('block_mode_some');
    var units = document.getElementById('rooms_blocked');
    var unitsWrap = document.getElementById('rooms-blocked-wrap');
    var unitsHelp = document.getElementById('rooms-blocked-help');
    var summary = document.getElementById('block-summary');

    function parseYmd(v) {
        var p = (v || '').split('-');
        return p.length === 3 ? new Date(+p[0], +p[1] - 1, +p[2]) : null;
    }

    function refresh() {
        var opt = roomSel.options[roomSel.selectedIndex];
        var total = opt ? parseInt(opt.getAttribute('data-units') || '0', 10) : 0;

        unitsWrap.style.display = modeSome.checked ? '' : 'none';
        units.required = modeSome.checked;
        if (total > 0) {
            units.max = total;
            unitsHelp.textContent = 'This room type has ' + total + ' room(s).';
        } else {
            units.removeAttribute('max');
            unitsHelp.textContent = roomSel.value === 'all' ? 'Capped at each room type\u2019s own count.' : '';
        }

        end.min = start.value || '';
        var s = parseYmd(start.value), e = parseYmd(end.value);
        if (s && e && e >= s) {
            var nights = Math.round((e - s) / 86400000) + 1;
            var checkout = new Date(e.getFullYear(), e.getMonth(), e.getDate() + 1);
            summary.textContent = nights + ' night' + (nights === 1 ? '' : 's') + ' blocked. Guests can check in again on ' +
                checkout.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) + '.';
        } else {
            summary.textContent = '';
        }
    }

    start.addEventListener('change', function () {
        if (end.value && start.value && end.value < start.value) {
            end.value = start.value;
        }
        refresh();
    });
    [roomSel, end, modeAll, modeSome].forEach(function (el) { el.addEventListener('change', refresh); });
    refresh();
});
</script>
