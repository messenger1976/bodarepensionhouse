<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Event</h5>
        <a href="<?php echo base_url('events/view/' . $event->id); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

    <form method="post" class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Event Name *</label>
            <input type="text" name="event_name" class="form-control" required value="<?php echo set_value('event_name', $event->event_name); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Event Type</label>
            <select name="event_type" class="form-select">
                <?php foreach (array('meeting','wedding','birthday','conference','banquet','other') as $t): ?>
                <option value="<?php echo $t; ?>" <?php echo set_select('event_type', $t, $event->event_type === $t); ?>><?php echo ucfirst($t); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Event Date *</label>
            <input type="date" name="event_date" class="form-control" required value="<?php echo set_value('event_date', $event->event_date); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Start Time</label>
            <input type="time" name="start_time" class="form-control" value="<?php echo set_value('start_time', $event->start_time); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">End Time</label>
            <input type="time" name="end_time" class="form-control" value="<?php echo set_value('end_time', $event->end_time); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Venue</label>
            <input type="text" name="venue" class="form-control" value="<?php echo set_value('venue', $event->venue); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Link to Room Booking</label>
            <select name="booking_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($bookings as $b): ?>
                <option value="<?php echo $b->id; ?>" <?php echo set_select('booking_id', $b->id, $event->booking_id == $b->id); ?>>
                    <?php echo htmlspecialchars(($b->booking_number ?: 'BK' . $b->id) . ' - ' . $b->guest_name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Organizer Name *</label>
            <input type="text" name="organizer_name" class="form-control" required value="<?php echo set_value('organizer_name', $event->organizer_name); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Organizer Email</label>
            <input type="email" name="organizer_email" class="form-control" value="<?php echo set_value('organizer_email', $event->organizer_email); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Organizer Phone</label>
            <input type="text" name="organizer_phone" class="form-control" value="<?php echo set_value('organizer_phone', $event->organizer_phone); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Expected Guests</label>
            <input type="number" name="expected_guests" class="form-control" value="<?php echo set_value('expected_guests', $event->expected_guests); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Package Amount (₱)</label>
            <input type="number" name="total_amount" class="form-control" step="0.01" value="<?php echo set_value('total_amount', $event->total_amount); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <?php foreach (array('inquiry','confirmed','completed','cancelled') as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo set_select('status', $s, $event->status === $s); ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3"><?php echo set_value('notes', $event->notes); ?></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Event</button>
        </div>
    </form>
</div>
