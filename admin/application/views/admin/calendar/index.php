<?php
$summary = isset($today_summary) ? $today_summary : array(
    'check_ins' => 0,
    'check_outs' => 0,
    'in_house' => 0,
    'events_today' => 0,
    'pending_bookings' => 0
);
?>
<div class="nk-block">
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-calendar3"></i> Calendar</h3>
                <div class="nk-block-des text-soft">
                    <p>Unified view of room stays and hotel events — planned like a hotel booking engine</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <?php if (!empty($can_add_bookings)): ?>
                            <li>
                                <a href="<?php echo base_url('bookings/add'); ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i> <span>New Booking</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($can_add_events)): ?>
                            <li>
                                <a href="<?php echo base_url('events/add'); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-calendar-plus"></i> <span>New Event</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($can_view_bookings)): ?>
                            <li>
                                <a href="<?php echo base_url('rooms/calendar'); ?>" class="btn btn-outline-light">
                                    <i class="bi bi-door-open"></i> <span>Availability</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Day ops summary -->
    <div class="row g-3 mb-4" id="calendar-summary-cards">
        <div class="col-6 col-md">
            <div class="card card-bordered h-100">
                <div class="card-inner py-3">
                    <div class="text-soft small text-uppercase mb-1">Check-ins</div>
                    <div class="fs-4 fw-bold text-success" id="sum-check-ins"><?php echo (int) $summary['check_ins']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card card-bordered h-100">
                <div class="card-inner py-3">
                    <div class="text-soft small text-uppercase mb-1">Check-outs</div>
                    <div class="fs-4 fw-bold text-danger" id="sum-check-outs"><?php echo (int) $summary['check_outs']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card card-bordered h-100">
                <div class="card-inner py-3">
                    <div class="text-soft small text-uppercase mb-1">In-house</div>
                    <div class="fs-4 fw-bold text-primary" id="sum-in-house"><?php echo (int) $summary['in_house']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card card-bordered h-100">
                <div class="card-inner py-3">
                    <div class="text-soft small text-uppercase mb-1">Events today</div>
                    <div class="fs-4 fw-bold text-info" id="sum-events"><?php echo (int) $summary['events_today']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card card-bordered h-100">
                <div class="card-inner py-3">
                    <div class="text-soft small text-uppercase mb-1">Pending stays</div>
                    <div class="fs-4 fw-bold text-warning" id="sum-pending"><?php echo (int) $summary['pending_bookings']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card card-bordered mb-4">
        <div class="card-inner">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="cal-type-filter" class="form-label">Show</label>
                    <select id="cal-type-filter" class="form-select">
                        <option value="all">Room stays &amp; events</option>
                        <option value="room">Room bookings only</option>
                        <option value="event">Hotel events only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cal-status-filter" class="form-label">Status</label>
                    <select id="cal-status-filter" class="form-select">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="checked_in">Checked In</option>
                        <option value="checked_out">Checked Out</option>
                        <option value="completed">Completed</option>
                        <option value="inquiry">Inquiry (events)</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cal-room-filter" class="form-label">Room</label>
                    <select id="cal-room-filter" class="form-select">
                        <option value="">All rooms</option>
                        <?php if (!empty($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo (int) $room->id; ?>">
                                    <?php echo htmlspecialchars($room->room_name); ?>
                                    <?php if (!empty($room->room_code)): ?>
                                        (<?php echo htmlspecialchars($room->room_code); ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="cal-include-cancelled">
                        <label class="form-check-label" for="cal-include-cancelled">Include cancelled</label>
                    </div>
                    <button type="button" id="cal-refresh" class="btn btn-primary w-100 mt-2">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card card-bordered mb-4">
        <div class="card-inner">
            <h6 class="mb-3"><i class="bi bi-info-circle"></i> Legend</h6>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center">
                        <span class="legend-badge cal-legend-room me-2"></span>
                        <span class="text-base">Room stay</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center">
                        <span class="legend-badge cal-legend-event me-2"></span>
                        <span class="text-base">Hotel event</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center">
                        <span class="legend-badge legend-success me-2"></span>
                        <span class="text-base">Confirmed</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center">
                        <span class="legend-badge legend-warning me-2"></span>
                        <span class="text-base">Pending / Inquiry</span>
                    </div>
                </div>
                <div class="col-12 mt-1">
                    <small class="text-soft">Check-in day shades from the right (from check-in time); checkout day shades to the left (until check-out time).</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="card card-bordered">
        <div class="card-inner">
            <div id="calendar-feed-error" class="alert alert-warning" style="display:none;"></div>
            <div id="hotel-calendar"></div>
        </div>
    </div>
</div>

<!-- Detail modal -->
<div class="modal fade" id="calendarDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calendarDetailTitle">Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="calendarDetailBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="calendarDetailLink" target="_self">Open record</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('hotel-calendar');
    if (!calendarEl || typeof FullCalendar === 'undefined') {
        return;
    }

    // Path-only URLs stay same-origin (avoids http/https mixed-content behind proxies)
    var calendarFeedUrl = <?php
        $feed_url = site_url('calendar/feed');
        $feed_path = parse_url($feed_url, PHP_URL_PATH);
        echo json_encode($feed_path ? $feed_path : '/admin/calendar/feed');
    ?>;
    var calendarSummaryUrl = <?php
        $summary_url = site_url('calendar/summary');
        $summary_path = parse_url($summary_url, PHP_URL_PATH);
        echo json_encode($summary_path ? $summary_path : '/admin/calendar/summary');
    ?>;

    var initialEvents = <?php
        $json_flags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $json_flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }
        echo json_encode(isset($initial_calendar_events) ? array_values($initial_calendar_events) : array(), $json_flags);
    ?>;
    var typeFilter = document.getElementById('cal-type-filter');
    var statusFilter = document.getElementById('cal-status-filter');
    var roomFilter = document.getElementById('cal-room-filter');
    var includeCancelled = document.getElementById('cal-include-cancelled');
    var refreshBtn = document.getElementById('cal-refresh');
    var detailModalEl = document.getElementById('calendarDetailModal');
    var detailModal = detailModalEl ? new bootstrap.Modal(detailModalEl) : null;

    function buildFeedUrl(info) {
        var params = new URLSearchParams();
        params.set('start', info.startStr);
        params.set('end', info.endStr);
        params.set('type', typeFilter.value || 'all');
        if (statusFilter.value) {
            params.set('status', statusFilter.value);
        }
        if (roomFilter.value) {
            params.set('room_id', roomFilter.value);
        }
        if (includeCancelled.checked) {
            params.set('include_cancelled', '1');
        }
        return calendarFeedUrl + '?' + params.toString();
    }

    function formatMoney(amount) {
        return '₱' + (amount || '0.00');
    }

    function capitalize(str) {
        if (!str) return '';
        return String(str).replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
    }

    function decorateEvents(events) {
        return (events || []).map(function(event) {
            var next = Object.assign({}, event);
            var classes = next.classNames || [];
            if (classes.indexOf('cal-status-confirmed') !== -1 || (next.extendedProps && next.extendedProps.status === 'confirmed')) {
                next.backgroundColor = next.backgroundColor || '#16a34a';
                next.borderColor = next.borderColor || '#15803d';
                next.textColor = next.textColor || '#ffffff';
            } else if (classes.indexOf('cal-status-pending') !== -1 || (next.extendedProps && next.extendedProps.status === 'pending')) {
                next.backgroundColor = next.backgroundColor || '#d97706';
                next.borderColor = next.borderColor || '#b45309';
                next.textColor = next.textColor || '#ffffff';
            }
            return next;
        });
    }

    function showDetail(event) {
        var p = event.extendedProps || {};
        var body = '';
        var title = event.title;
        var link = p.url || '#';

        if (p.source === 'room') {
            title = 'Room Booking';
            body =
                '<div class="info-row"><span class="info-label">Guest</span><span class="info-value"><strong>' + escapeHtml(p.guestName || '-') + '</strong></span></div>' +
                '<div class="info-row"><span class="info-label">Booking #</span><span class="info-value"><code>' + escapeHtml(p.bookingNumber || '-') + '</code></span></div>' +
                '<div class="info-row"><span class="info-label">Room</span><span class="info-value">' + escapeHtml(p.roomName || '-') + ' <span class="text-soft">(' + escapeHtml(p.roomCode || '-') + ')</span></span></div>' +
                '<div class="info-row"><span class="info-label">Type</span><span class="info-value">' + escapeHtml(p.roomType || '-') + '</span></div>' +
                '<div class="info-row"><span class="info-label">Stay</span><span class="info-value">' +
                    escapeHtml(p.checkIn || '') +
                    (p.checkInTime ? (' <span class="text-soft">' + escapeHtml(formatTime12(p.checkInTime)) + '</span>') : '') +
                    ' → ' +
                    escapeHtml(p.checkOut || '') +
                    (p.checkOutTime ? (' <span class="text-soft">' + escapeHtml(formatTime12(p.checkOutTime)) + '</span>') : '') +
                '</span></div>' +
                '<div class="info-row"><span class="info-label">Guests</span><span class="info-value">' + (p.guests || 1) + '</span></div>' +
                '<div class="info-row"><span class="info-label">Status</span><span class="info-value"><span class="badge bg-' + statusBadge(p.status) + '">' + capitalize(p.status) + '</span></span></div>' +
                '<div class="info-row"><span class="info-label">Amount</span><span class="info-value"><strong>' + formatMoney(p.amount) + '</strong></span></div>';
        } else {
            title = 'Hotel Event';
            body =
                '<div class="info-row"><span class="info-label">Event</span><span class="info-value"><strong>' + escapeHtml(p.eventName || '-') + '</strong></span></div>' +
                '<div class="info-row"><span class="info-label">Event #</span><span class="info-value"><code>' + escapeHtml(p.eventNumber || '-') + '</code></span></div>' +
                '<div class="info-row"><span class="info-label">Type</span><span class="info-value">' + capitalize(p.eventType || 'other') + '</span></div>' +
                '<div class="info-row"><span class="info-label">Venue</span><span class="info-value">' + escapeHtml(p.venue || '-') + '</span></div>' +
                '<div class="info-row"><span class="info-label">Date</span><span class="info-value">' + escapeHtml(p.eventDate || '') +
                    (p.startTime ? (' · ' + escapeHtml(p.startTime) + (p.endTime ? ('–' + escapeHtml(p.endTime)) : '')) : '') +
                '</span></div>' +
                '<div class="info-row"><span class="info-label">Organizer</span><span class="info-value">' + escapeHtml(p.organizerName || '-') + '</span></div>' +
                '<div class="info-row"><span class="info-label">Guests</span><span class="info-value">' + (p.guests || 0) + '</span></div>' +
                '<div class="info-row"><span class="info-label">Status</span><span class="info-value"><span class="badge bg-' + statusBadge(p.status) + '">' + capitalize(p.status) + '</span></span></div>' +
                '<div class="info-row"><span class="info-label">Amount</span><span class="info-value"><strong>' + formatMoney(p.amount) + '</strong></span></div>' +
                (p.linkedBooking ? '<div class="info-row"><span class="info-label">Linked booking</span><span class="info-value"><code>' + escapeHtml(p.linkedBooking) + '</code></span></div>' : '');
        }

        document.getElementById('calendarDetailTitle').textContent = title;
        document.getElementById('calendarDetailBody').innerHTML = '<div class="room-details-popover">' + body + '</div>';
        document.getElementById('calendarDetailLink').href = link;
        if (detailModal) {
            detailModal.show();
        }
    }

    function statusBadge(status) {
        switch (status) {
            case 'confirmed':
                return 'success';
            case 'checked_in':
                return 'info';
            case 'checked_out':
            case 'completed':
                return 'primary';
            case 'pending':
            case 'inquiry':
                return 'warning';
            case 'cancelled':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function formatTime12(timeStr) {
        if (!timeStr) return '';
        var parts = String(timeStr).split(':');
        var hours = parseInt(parts[0], 10);
        var minutes = parts.length > 1 ? parseInt(parts[1], 10) : 0;
        if (isNaN(hours)) return timeStr;
        if (isNaN(minutes)) minutes = 0;
        var suffix = hours >= 12 ? 'PM' : 'AM';
        var h12 = hours % 12;
        if (h12 === 0) h12 = 12;
        return h12 + ':' + (minutes < 10 ? '0' : '') + minutes + ' ' + suffix;
    }

    function timeToDayPercent(timeStr) {
        if (!timeStr) return 0;
        var parts = String(timeStr).split(':');
        var hours = parseInt(parts[0], 10);
        var minutes = parts.length > 1 ? parseInt(parts[1], 10) : 0;
        if (isNaN(hours)) return 0;
        if (isNaN(minutes)) minutes = 0;
        return Math.max(0, Math.min(100, ((hours * 60 + minutes) / (24 * 60)) * 100));
    }

    function countHarnessDays(harness) {
        if (!harness || !calendarEl) return 1;
        var dayCell = calendarEl.querySelector('.fc-daygrid-day');
        if (!dayCell) return 1;
        var dayWidth = dayCell.getBoundingClientRect().width;
        var harnessWidth = harness.getBoundingClientRect().width;
        if (dayWidth < 1) return 1;
        return Math.max(1, Math.round(harnessWidth / dayWidth));
    }

    /**
     * Check-in day: shade right half (from check-in time).
     * Checkout day: shade left half (until check-out time).
     * Insets are scaled by segment day count so multi-day bars stay aligned.
     */
    function applyPartialDayBar(info) {
        var p = info.event.extendedProps || {};
        if (p.source !== 'room') return;
        if (!info.view || String(info.view.type).indexOf('dayGrid') !== 0) return;

        var el = info.el;
        if (!el) return;

        var startPct = timeToDayPercent(p.checkInTime || '14:00');
        var endPct = timeToDayPercent(p.checkOutTime || '12:00');
        var isStart = el.classList.contains('fc-event-start');
        var isEnd = el.classList.contains('fc-event-end');
        if (!isStart && !isEnd) {
            el.style.marginLeft = '';
            el.style.width = '';
            el.style.maxWidth = '';
            return;
        }

        var harness = el.closest ? el.closest('.fc-daygrid-event-harness') : el.parentElement;
        var days = countHarnessDays(harness);
        var leftInset = isStart ? (startPct / days) : 0;
        var rightInset = isEnd ? ((100 - endPct) / days) : 0;
        var width = Math.max(100 - leftInset - rightInset, 6);

        el.style.boxSizing = 'border-box';
        el.style.marginLeft = leftInset + '%';
        el.style.width = width + '%';
        el.style.maxWidth = width + '%';
    }

    function refreshSummary(dateStr) {
        fetch(calendarSummaryUrl + '?date=' + encodeURIComponent(dateStr || '<?php echo date("Y-m-d"); ?>'), { credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success || !data.summary) return;
                var s = data.summary;
                document.getElementById('sum-check-ins').textContent = s.check_ins;
                document.getElementById('sum-check-outs').textContent = s.check_outs;
                document.getElementById('sum-in-house').textContent = s.in_house;
                document.getElementById('sum-events').textContent = s.events_today;
                document.getElementById('sum-pending').textContent = s.pending_bookings;
            })
            .catch(function() {});
    }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        height: 'auto',
        timeZone: 'Asia/Manila',
        editable: false,
        navLinks: true,
        dayMaxEvents: true,
        nowIndicator: true,
        eventDisplay: 'block',
        displayEventTime: false,
        nextDayThreshold: '00:00:00',
        events: function(info, successCallback, failureCallback) {
            var errorEl = document.getElementById('calendar-feed-error');
            fetch(buildFeedUrl(info), { credentials: 'same-origin' })
                .then(function(response) {
                    if (response.redirected && response.url.indexOf('login') !== -1) {
                        throw new Error('Your admin session expired. Please refresh the page and log in again.');
                    }
                    if (!response.ok) {
                        throw new Error('Calendar feed failed (HTTP ' + response.status + ')');
                    }
                    return response.text();
                })
                .then(function(text) {
                    var data;
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        throw new Error('Calendar feed returned invalid JSON. Open /admin/calendar/feed in your browser while logged in to debug.');
                    }
                    if (errorEl) {
                        errorEl.style.display = 'none';
                        errorEl.textContent = '';
                    }
                    var events = Array.isArray(data) ? data : [];
                    if (events.length === 0 && initialEvents.length > 0) {
                        events = initialEvents;
                    }
                    successCallback(decorateEvents(events));
                })
                .catch(function(err) {
                    if (errorEl) {
                        errorEl.style.display = 'block';
                        errorEl.textContent = (err && err.message)
                            ? err.message
                            : 'Unable to load calendar bookings. Click Refresh or check your admin session.';
                    }
                    if (initialEvents.length > 0) {
                        successCallback(decorateEvents(initialEvents));
                        return;
                    }
                    failureCallback(err);
                });
        },
        eventDidMount: function(info) {
            // Layout width is needed to scale half-day insets across multi-day spans
            requestAnimationFrame(function() {
                applyPartialDayBar(info);
            });
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            showDetail(info.event);
        },
        datesSet: function(info) {
            // Keep ops cards on "today" unless viewing a single day
            if (info.view.type === 'timeGridDay') {
                refreshSummary(info.startStr.substring(0, 10));
            }
        }
    });

    calendar.render();

    function refetch() {
        calendar.refetchEvents();
    }

    typeFilter.addEventListener('change', function() {
        // Room filter only applies to room stays
        roomFilter.disabled = typeFilter.value === 'event';
        refetch();
    });
    statusFilter.addEventListener('change', refetch);
    roomFilter.addEventListener('change', refetch);
    includeCancelled.addEventListener('change', refetch);
    refreshBtn.addEventListener('click', function() {
        refetch();
        refreshSummary('<?php echo date("Y-m-d"); ?>');
    });
});
</script>
