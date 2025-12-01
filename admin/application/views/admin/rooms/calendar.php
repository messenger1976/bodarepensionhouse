<div class="nk-block">
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-calendar-check"></i> Room Availability Calendar</h3>
                <div class="nk-block-des text-soft">
                    <p>View room availability and bookings in calendar format</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li>
                                <a href="<?php echo base_url('rooms'); ?>" class="btn btn-outline-light">
                                    <i class="bi bi-arrow-left"></i> <span>Back to Rooms</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="card card-bordered mb-4">
        <div class="card-inner">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="room-filter" class="form-label">Filter by Room</label>
                    <select id="room-filter" class="form-select">
                        <option value="">All Rooms</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room->id; ?>">
                                <?php echo htmlspecialchars($room->room_name); ?> 
                                (<?php echo htmlspecialchars($room->room_type); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date-range-start" class="form-label">Start Date</label>
                    <input type="date" id="date-range-start" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-3">
                    <label for="date-range-end" class="form-label">End Date</label>
                    <input type="date" id="date-range-end" class="form-control" value="<?php echo date('Y-m-d', strtotime('+2 months')); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" id="refresh-calendar" class="btn btn-primary w-100">
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
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <span class="legend-badge legend-success me-2"></span>
                        <span class="text-base">Available (All rooms free)</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <span class="legend-badge legend-warning me-2"></span>
                        <span class="text-base">Partially Booked</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <span class="legend-badge legend-danger me-2"></span>
                        <span class="text-base">Fully Booked</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <span class="legend-badge legend-secondary me-2"></span>
                        <span class="text-base">Inactive Room</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Calendar -->
    <div class="card card-bordered">
        <div class="card-inner">
            <div id="availability-calendar"></div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    let calendar;
    let availabilityData = {};
    const roomFilter = document.getElementById('room-filter');
    const dateRangeStart = document.getElementById('date-range-start');
    const dateRangeEnd = document.getElementById('date-range-end');
    const refreshBtn = document.getElementById('refresh-calendar');
    
    // Initialize calendar
    const calendarEl = document.getElementById('availability-calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 'auto',
        editable: false,
        dayMaxEvents: true,
        events: [],
        eventDidMount: function(info) {
            // Tooltip on hover
            info.el.setAttribute('title', info.event.extendedProps.description || '');
        },
        eventClick: function(info) {
            const roomId = info.event.extendedProps.roomId;
            const date = info.event.startStr;
            
            if (roomId && availabilityData[date] && availabilityData[date][roomId]) {
                const roomData = availabilityData[date][roomId];
                showRoomDetails(roomData, date);
            }
        },
        datesSet: function(info) {
            // Load data when calendar view changes
            loadAvailabilityData(info.startStr, info.endStr);
        }
    });
    
    calendar.render();
    
    // Load availability data
    function loadAvailabilityData(startDate, endDate) {
        const roomId = roomFilter.value || null;
        
        // Update date inputs
        if (!dateRangeStart.value || dateRangeStart.value < startDate) {
            dateRangeStart.value = startDate;
        }
        if (!dateRangeEnd.value || dateRangeEnd.value > endDate) {
            dateRangeEnd.value = endDate;
        }
        
        const actualStart = dateRangeStart.value || startDate;
        const actualEnd = dateRangeEnd.value || endDate;
        
        fetch('<?php echo base_url("rooms/get_availability_data"); ?>?start_date=' + actualStart + '&end_date=' + actualEnd + (roomId ? '&room_id=' + roomId : ''))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    availabilityData = data.data;
                    updateCalendarEvents(data.data);
                } else {
                    console.error('Failed to load availability data');
                }
            })
            .catch(error => {
                console.error('Error loading availability data:', error);
            });
    }
    
    // Update calendar events from availability data
    function updateCalendarEvents(data) {
        const events = [];
        const rooms = <?php echo json_encode($rooms); ?>;
        
        // Create events for each room and date
        Object.keys(data).forEach(date => {
            Object.keys(data[date]).forEach(roomId => {
                const roomData = data[date][roomId];
                const room = rooms.find(r => r.id == roomId);
                
                if (!room) return;
                
                let className = 'room-event-inactive';
                let title = roomData.room_name + ': ';
                
                if (roomData.status === 'inactive' || (room && room.status !== 'active')) {
                    className = 'room-event-inactive';
                    title += 'Inactive';
                } else if (roomData.status === 'available') {
                    className = 'room-event-available';
                    title += 'Available (' + roomData.remaining + '/' + roomData.total_available + ')';
                } else if (roomData.status === 'partial') {
                    className = 'room-event-partial';
                    title += 'Partially Booked (' + roomData.remaining + '/' + roomData.total_available + ' left)';
                } else {
                    className = 'room-event-booked';
                    title += 'Fully Booked';
                }
                
                events.push({
                    title: title,
                    start: date,
                    allDay: true,
                    className: className,
                    roomId: roomId,
                    extendedProps: {
                        roomId: roomId,
                        description: roomData.room_name + '\n' +
                                   'Type: ' + roomData.room_type + '\n' +
                                   'Total: ' + roomData.total_available + '\n' +
                                   'Booked: ' + roomData.booked + '\n' +
                                   'Remaining: ' + roomData.remaining + '\n' +
                                   'Status: ' + roomData.status
                    }
                });
            });
        });
        
        calendar.removeAllEvents();
        calendar.addEventSource(events);
    }
    
    // Show room details popover
    function showRoomDetails(roomData, date) {
        const content = `
            <div class="room-details-popover">
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-building"></i> Room Name:</span>
                    <span class="info-value"><strong>${roomData.room_name}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-tag"></i> Type:</span>
                    <span class="info-value">${roomData.room_type}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-code-square"></i> Room Code:</span>
                    <span class="info-value"><code>${roomData.room_code || 'N/A'}</code></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-calendar-event"></i> Date:</span>
                    <span class="info-value">${new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</span>
                </div>
                <hr>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-box-seam"></i> Total Available:</span>
                    <span class="info-value"><strong>${roomData.total_available}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-calendar-check"></i> Booked:</span>
                    <span class="info-value">
                        <span class="badge bg-${roomData.booked > 0 ? 'warning' : 'secondary'}">${roomData.booked}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-check-circle"></i> Remaining:</span>
                    <span class="info-value">
                        <span class="badge bg-${roomData.is_available ? 'success' : 'danger'}">${roomData.remaining}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-info-circle"></i> Status:</span>
                    <span class="info-value">
                        <span class="badge bg-${roomData.status === 'available' ? 'success' : (roomData.status === 'partial' ? 'warning' : (roomData.status === 'inactive' ? 'secondary' : 'danger'))}">
                            ${roomData.status.charAt(0).toUpperCase() + roomData.status.slice(1)}
                        </span>
                    </span>
                </div>
                ${roomData.room_status && roomData.room_status !== 'active' ? `
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-power"></i> Room Status:</span>
                    <span class="info-value">
                        <span class="badge bg-secondary">${roomData.room_status.charAt(0).toUpperCase() + roomData.room_status.slice(1)}</span>
                    </span>
                </div>
                ` : ''}
            </div>
        `;
        
        // Use Dashlite styled modal
        const modalHtml = `
            <div class="modal fade" id="roomDetailsModal" tabindex="-1" aria-labelledby="roomDetailsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="roomDetailsModalLabel">
                                <i class="bi bi-door-open"></i> Room Availability Details
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('roomDetailsModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('roomDetailsModal'));
        modal.show();
        
        // Remove modal from DOM after hiding
        document.getElementById('roomDetailsModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }
    
    // Event listeners
    roomFilter.addEventListener('change', function() {
        const startDate = dateRangeStart.value || calendar.view.activeStart.toISOString().split('T')[0];
        const endDate = dateRangeEnd.value || calendar.view.activeEnd.toISOString().split('T')[0];
        loadAvailabilityData(startDate, endDate);
    });
    
    refreshBtn.addEventListener('click', function() {
        const startDate = dateRangeStart.value || calendar.view.activeStart.toISOString().split('T')[0];
        const endDate = dateRangeEnd.value || calendar.view.activeEnd.toISOString().split('T')[0];
        loadAvailabilityData(startDate, endDate);
    });
    
    // Load initial data
    const today = new Date();
    const nextMonth = new Date(today);
    nextMonth.setMonth(nextMonth.getMonth() + 2);
    
    loadAvailabilityData(
        today.toISOString().split('T')[0],
        nextMonth.toISOString().split('T')[0]
    );
});
</script>

