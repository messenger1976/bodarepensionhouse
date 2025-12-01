<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $this->session->flashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="nk-block">
    <div class="row g-gs">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-icon"><i class="bi bi-calendar-check"></i></div>
                <div class="stat-card-value"><?php echo $total_bookings; ?></div>
                <div class="stat-card-label">Total Bookings</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card warning">
                <div class="stat-card-icon"><i class="bi bi-clock-history"></i></div>
                <div class="stat-card-value"><?php echo $pending_bookings; ?></div>
                <div class="stat-card-label">Pending</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card secondary">
                <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-card-value"><?php echo $confirmed_bookings; ?></div>
                <div class="stat-card-label">Confirmed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card info">
                <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
                <div class="stat-card-value">₱<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-card-label">Total Revenue</div>
            </div>
        </div>
    </div>
</div>

<div class="nk-block">
    <div class="row g-gs">
        <div class="col-md-6">
            <div class="card card-bordered">
                <div class="card-header">
                    <i class="bi bi-calendar-event"></i> Booking Calendar
                </div>
                <div class="card-body">
                    <div id="booking-calendar"></div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Hover over calendar days to see room availability. Click on bookings to view details.
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-bordered mb-4">
                <div class="card-header">
                    <i class="bi bi-door-open"></i> Room Availability (Today)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Available</th>
                                    <th>Booked</th>
                                    <th>Remaining</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($room_availability_today)): ?>
                                    <?php foreach ($room_availability_today as $room_avail): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($room_avail['room_name']); ?></td>
                                            <td><?php echo $room_avail['available']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $room_avail['booked'] > 0 ? 'warning' : 'secondary'; ?>">
                                                    <?php echo $room_avail['booked']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $room_avail['remaining'] > 0 ? 'success' : 'danger'; ?>">
                                                    <?php echo $room_avail['remaining']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No rooms available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card card-bordered">
                <div class="card-header">
                    <i class="bi bi-list-ul"></i> Recent Bookings
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Guest Name</th>
                                    <th>Earliest Check-In</th>
                                    <th>Latest Check-Out</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_bookings)): ?>
                                    <?php foreach (array_slice($recent_bookings, 0, 10) as $booking): ?>
                                        <tr>
                                            <td>#<?php echo isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($booking->guest_name); ?></td>
                                            <td><?php echo date('M d, Y', strtotime(isset($booking->earliest_checkin) ? $booking->earliest_checkin : $booking->check_in)); ?></td>
                                            <td><?php echo date('M d, Y', strtotime(isset($booking->latest_checkout) ? $booking->latest_checkout : $booking->check_out)); ?></td>
                                            <td>
                                                <?php
                                                $badge_class = 'secondary';
                                                if ($booking->status == 'confirmed') $badge_class = 'success';
                                                if ($booking->status == 'cancelled') $badge_class = 'danger';
                                                if ($booking->status == 'pending') $badge_class = 'warning';
                                                ?>
                                                <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucfirst($booking->status); ?></span>
                                            </td>
                                            <td>₱<?php echo number_format($booking->total_amount, 2); ?></td>
                                            <td>
                                                <a href="<?php echo base_url('bookings/' . $booking->id); ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No bookings found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="<?php echo base_url('bookings'); ?>" class="btn btn-primary">View All Bookings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('booking-calendar');
            if (calendarEl) {
                // Prepare booking events for calendar
                var calendarEvents = [
                    <?php if (!empty($calendar_bookings)): ?>
                        <?php foreach ($calendar_bookings as $booking): ?>
                        {
                            id: '<?php echo $booking->id; ?>',
                            title: '<?php echo htmlspecialchars($booking->guest_name, ENT_QUOTES); ?> - <?php echo htmlspecialchars($booking->room_name, ENT_QUOTES); ?>',
                            start: '<?php echo date('Y-m-d', strtotime($booking->check_in)); ?>',
                            end: '<?php echo date('Y-m-d', strtotime($booking->check_out . ' +1 day')); ?>',
                            className: '<?php echo $booking->status; ?>',
                            extendedProps: {
                                bookingId: '<?php echo $booking->id; ?>',
                                bookingNumber: '<?php echo isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT); ?>',
                                guestName: '<?php echo htmlspecialchars($booking->guest_name, ENT_QUOTES); ?>',
                                roomName: '<?php echo htmlspecialchars($booking->room_name, ENT_QUOTES); ?>',
                                roomCode: '<?php echo htmlspecialchars(isset($booking->room_code) ? $booking->room_code : '-', ENT_QUOTES); ?>',
                                status: '<?php echo $booking->status; ?>',
                                amount: '<?php echo number_format($booking->total_amount, 2); ?>',
                                rooms: '<?php echo isset($booking->rooms) ? $booking->rooms : 1; ?>'
                            }
                        },
                        <?php endforeach; ?>
                    <?php endif; ?>
                ];
                
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: calendarEvents,
                    eventClick: function(info) {
                        // Open booking details when event is clicked
                        var bookingId = info.event.extendedProps.bookingId;
                        if (bookingId) {
                            window.location.href = '<?php echo base_url("bookings/"); ?>' + bookingId;
                        }
                    },
                    eventMouseEnter: function(info) {
                        // Show tooltip on hover
                        var tooltip = document.createElement('div');
                        tooltip.className = 'booking-tooltip';
                        var roomsText = info.event.extendedProps.rooms ? ' (' + info.event.extendedProps.rooms + ' room' + (info.event.extendedProps.rooms > 1 ? 's' : '') + ')' : '';
                        tooltip.innerHTML = '<strong>' + info.event.extendedProps.guestName + '</strong><br>' +
                                          'Room: ' + info.event.extendedProps.roomName + roomsText + '<br>' +
                                          'Status: ' + info.event.extendedProps.status.charAt(0).toUpperCase() + info.event.extendedProps.status.slice(1) + '<br>' +
                                          'Amount: ₱' + info.event.extendedProps.amount;
                        tooltip.style.position = 'absolute';
                        tooltip.style.background = '#333';
                        tooltip.style.color = '#fff';
                        tooltip.style.padding = '8px 12px';
                        tooltip.style.borderRadius = '4px';
                        tooltip.style.zIndex = '10000';
                        tooltip.style.fontSize = '12px';
                        tooltip.style.pointerEvents = 'none';
                        tooltip.style.boxShadow = '0 2px 8px rgba(0,0,0,0.3)';
                        document.body.appendChild(tooltip);
                        
                        var updateTooltipPosition = function(e) {
                            tooltip.style.left = (e.pageX + 10) + 'px';
                            tooltip.style.top = (e.pageY + 10) + 'px';
                        };
                        
                        document.addEventListener('mousemove', updateTooltipPosition);
                        info.el.addEventListener('mouseleave', function() {
                            document.body.removeChild(tooltip);
                            document.removeEventListener('mousemove', updateTooltipPosition);
                        });
                    },
                    dayCellDidMount: function(info) {
                        // Add room availability info to each day cell
                        var dateStr = info.date.toISOString().split('T')[0];
                        
                        // Fetch room availability for this date via AJAX
                        fetch('<?php echo base_url("api/booking/get_availability"); ?>?date=' + dateStr)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.availability) {
                                    var availabilityHtml = '<div class="room-availability-info" style="font-size: 0.7rem; margin-top: 2px; color: #666;">';
                                    var hasAvailability = false;
                                    
                                    for (var roomId in data.availability) {
                                        var room = data.availability[roomId];
                                        if (room.remaining > 0) {
                                            hasAvailability = true;
                                            availabilityHtml += '<span style="display: block; margin: 1px 0;">' + 
                                                room.room_name + ': ' + 
                                                '<strong style="color: ' + (room.remaining > 0 ? '#28a745' : '#dc3545') + ';">' + 
                                                room.remaining + '/' + room.available + 
                                                '</strong></span>';
                                        }
                                    }
                                    
                                    if (!hasAvailability) {
                                        availabilityHtml += '<span style="color: #dc3545;">Fully Booked</span>';
                                    }
                                    
                                    availabilityHtml += '</div>';
                                    
                                    // Add to day cell
                                    var dayNumber = info.dayNumberEl;
                                    if (dayNumber && dayNumber.parentElement) {
                                        var existingInfo = dayNumber.parentElement.querySelector('.room-availability-info');
                                        if (existingInfo) {
                                            existingInfo.remove();
                                        }
                                        dayNumber.parentElement.insertAdjacentHTML('beforeend', availabilityHtml);
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching availability:', error);
                            });
                    },
                    height: 'auto',
                    contentHeight: 'auto'
                });
                
                calendar.render();
            }
        });
    </script>

