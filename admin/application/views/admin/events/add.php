<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Add Hotel Event</h5>
        <a href="<?php echo base_url('events'); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

    <form method="post" class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Event Name *</label>
            <input type="text" name="event_name" class="form-control" required value="<?php echo set_value('event_name'); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Event Type</label>
            <select name="event_type" class="form-select">
                <?php foreach (array('meeting','wedding','birthday','conference','banquet','other') as $t): ?>
                <option value="<?php echo $t; ?>" <?php echo set_select('event_type', $t, $t === 'other'); ?>><?php echo ucfirst($t); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Start *</label>
            <input type="datetime-local" id="event_start_dt" class="form-control" required
                value="<?php
                    $ev_date = set_value('event_date');
                    $ev_start = set_value('start_time');
                    if ($ev_start && strlen($ev_start) > 5) $ev_start = substr($ev_start, 0, 5);
                    echo htmlspecialchars(($ev_date && $ev_start) ? ($ev_date . 'T' . $ev_start) : ($ev_date ? $ev_date . 'T00:00' : ''), ENT_QUOTES, 'UTF-8');
                ?>">
            <input type="hidden" name="event_date" id="event_date" value="<?php echo htmlspecialchars(set_value('event_date'), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="start_time" id="start_time" value="<?php echo htmlspecialchars(set_value('start_time'), ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">End</label>
            <input type="datetime-local" id="event_end_dt" class="form-control"
                value="<?php
                    $ev_date2 = set_value('event_date');
                    $ev_end = set_value('end_time');
                    if ($ev_end && strlen($ev_end) > 5) $ev_end = substr($ev_end, 0, 5);
                    echo htmlspecialchars(($ev_date2 && $ev_end) ? ($ev_date2 . 'T' . $ev_end) : '', ENT_QUOTES, 'UTF-8');
                ?>">
            <input type="hidden" name="end_time" id="end_time" value="<?php echo htmlspecialchars(set_value('end_time'), ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Venue / Function Room</label>
            <input type="text" name="venue" class="form-control" value="<?php echo set_value('venue'); ?>" placeholder="e.g. Function Hall A">
        </div>
        <div class="col-md-6">
            <label class="form-label">Link to Room Booking</label>
            <select name="booking_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($bookings as $b): ?>
                <option value="<?php echo $b->id; ?>" <?php echo set_select('booking_id', $b->id); ?>>
                    <?php echo htmlspecialchars(($b->booking_number ?: 'BK' . $b->id) . ' - ' . $b->guest_name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Organizer Name *</label>
            <input type="text" name="organizer_name" class="form-control" required value="<?php echo set_value('organizer_name'); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Organizer Email</label>
            <input type="email" name="organizer_email" class="form-control" value="<?php echo set_value('organizer_email'); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Organizer Phone</label>
            <input type="text" name="organizer_phone" class="form-control" value="<?php echo set_value('organizer_phone'); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Expected Guests</label>
            <input type="number" name="expected_guests" class="form-control" min="0" value="<?php echo set_value('expected_guests', '0'); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Package Amount (₱)</label>
            <input type="number" name="total_amount" class="form-control" step="0.01" min="0" value="<?php echo set_value('total_amount', '0'); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <?php foreach (array('inquiry','confirmed','completed','cancelled') as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo set_select('status', $s, $s === 'inquiry'); ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3"><?php echo set_value('notes'); ?></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Event</button>
        </div>
    </form>
</div>
<script>
(function () {
    function splitDt(value) {
        if (!value || String(value).indexOf('T') === -1) return { date: '', time: '' };
        var parts = String(value).split('T');
        return { date: parts[0] || '', time: (parts[1] || '').substring(0, 5) };
    }
    function syncEventDatetimes() {
        var startEl = document.getElementById('event_start_dt');
        var endEl = document.getElementById('event_end_dt');
        var dateEl = document.getElementById('event_date');
        var startTimeEl = document.getElementById('start_time');
        var endTimeEl = document.getElementById('end_time');
        if (!startEl || !dateEl || !startTimeEl) return;
        var start = splitDt(startEl.value);
        dateEl.value = start.date;
        startTimeEl.value = start.time;
        if (endEl && endTimeEl) {
            var end = splitDt(endEl.value);
            endTimeEl.value = end.time || '';
            if (start.date && endEl.value && end.date && end.date !== start.date) {
                // Schema stores a single event_date; keep end time, date follows start
                endEl.value = start.date + 'T' + (end.time || '00:00');
                endTimeEl.value = end.time || '';
            }
        }
    }
    var form = document.querySelector('form');
    var startEl = document.getElementById('event_start_dt');
    var endEl = document.getElementById('event_end_dt');
    if (startEl) {
        startEl.addEventListener('change', function () {
            syncEventDatetimes();
            if (endEl && startEl.value) {
                var start = splitDt(startEl.value);
                var end = splitDt(endEl.value);
                if (!endEl.value) {
                    endEl.value = start.date + 'T' + (start.time || '00:00');
                } else if (start.date) {
                    endEl.value = start.date + 'T' + (end.time || start.time || '00:00');
                }
                syncEventDatetimes();
            }
        });
    }
    if (endEl) endEl.addEventListener('change', syncEventDatetimes);
    if (form) form.addEventListener('submit', syncEventDatetimes);
    syncEventDatetimes();
})();
</script>
