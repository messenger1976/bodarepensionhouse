<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $this->session->flashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<style>
    .analytics-chart-wrap {
        position: relative;
        width: 100%;
    }

    .analytics-chart-wrap.trend {
        height: 260px;
    }

    .analytics-chart-wrap.compact {
        height: 240px;
    }

    .analytics-chart-wrap.wide {
        height: 300px;
    }

    .analytics-chart-wrap.forecast {
        height: 280px;
    }
</style>

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

<?php
$status_totals = array('pending' => 0, 'confirmed' => 0, 'cancelled' => 0, 'completed' => 0);
if (!empty($today_status_analytics) && is_array($today_status_analytics)) {
    foreach ($status_totals as $status_key => $status_value) {
        if (isset($today_status_analytics[$status_key]['bookings_count'])) {
            $status_totals[$status_key] = (int)$today_status_analytics[$status_key]['bookings_count'];
        }
    }
}

$inventory_summary_today = isset($inventory_summary_today) && is_array($inventory_summary_today)
    ? $inventory_summary_today
    : array('total_units' => 0, 'booked_units' => 0, 'utilization_rate' => 0);
?>

<div class="nk-block">
    <div class="card card-bordered mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Analytics Range</label>
                    <select class="form-select" id="analyticsRange">
                        <option value="7d" <?php echo (isset($analytics_range['selected']) && $analytics_range['selected'] === '7d') ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30d" <?php echo (!isset($analytics_range['selected']) || $analytics_range['selected'] === '30d') ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="90d" <?php echo (isset($analytics_range['selected']) && $analytics_range['selected'] === '90d') ? 'selected' : ''; ?>>Last 90 Days</option>
                        <option value="ytd" <?php echo (isset($analytics_range['selected']) && $analytics_range['selected'] === 'ytd') ? 'selected' : ''; ?>>Year to Date</option>
                        <option value="custom" <?php echo (isset($analytics_range['selected']) && $analytics_range['selected'] === 'custom') ? 'selected' : ''; ?>>Custom</option>
                    </select>
                </div>
                <div class="col-md-3" id="customStartWrap" style="display: <?php echo (isset($analytics_range['selected']) && $analytics_range['selected'] === 'custom') ? 'block' : 'none'; ?>;">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="analyticsStartDate" value="<?php echo isset($analytics_range['start_date']) ? $analytics_range['start_date'] : ''; ?>">
                </div>
                <div class="col-md-3" id="customEndWrap" style="display: <?php echo (isset($analytics_range['selected']) && $analytics_range['selected'] === 'custom') ? 'block' : 'none'; ?>;">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" id="analyticsEndDate" value="<?php echo isset($analytics_range['end_date']) ? $analytics_range['end_date'] : ''; ?>">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary w-100" id="applyAnalyticsRange">
                        <i class="bi bi-funnel"></i> Apply Range
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" id="quickLast30Days">
                        <i class="bi bi-arrow-counterclockwise"></i> Use Last 30 Days
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-gs mb-4">
        <div class="col-md-4">
            <div class="card card-bordered h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-bar-chart-line"></i> Sales Trend</span>
                    <div class="d-flex align-items-center gap-2">
                        <select class="form-select form-select-sm" id="salesTrendMode" style="width: auto;">
                            <option value="daily" selected>Daily</option>
                            <option value="avg">7d Avg</option>
                            <option value="both">Both</option>
                        </select>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary chart-export-btn" data-chart="salesTrendChart" data-format="csv">CSV</button>
                            <button class="btn btn-outline-secondary chart-export-btn" data-chart="salesTrendChart" data-format="png">PNG</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="analytics-chart-wrap trend">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                    <small class="text-muted d-none" id="salesTrendHint">No confirmed revenue in the selected date range.</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-bordered h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-pie-chart"></i> Booking Status Mix</span>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary chart-export-btn" data-chart="statusMixChart" data-format="csv">CSV</button>
                        <button class="btn btn-outline-secondary chart-export-btn" data-chart="statusMixChart" data-format="png">PNG</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="analytics-chart-wrap compact">
                        <canvas id="statusMixChart"></canvas>
                    </div>
                    <small class="text-muted d-none" id="statusMixHint">No booking status data in the selected date range.</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-bordered h-100">
                <div class="card-header"><i class="bi bi-door-open"></i> Inventory Snapshot (Today)</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Room Units</span>
                        <strong id="invTotalUnits"><?php echo (int)$inventory_summary_today['total_units']; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Booked Units</span>
                        <strong id="invBookedUnits"><?php echo (int)$inventory_summary_today['booked_units']; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Utilization Rate</span>
                        <strong id="invUtilizationText"><?php echo number_format((float)$inventory_summary_today['utilization_rate'], 1); ?>%</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" id="invUtilizationBar" role="progressbar" style="width: <?php echo min(100, max(0, (float)$inventory_summary_today['utilization_rate'])); ?>%; transition: none;"></div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted d-block">Confirmed Today</small>
                            <strong class="text-success" id="statusConfirmedCount"><?php echo (int)$status_totals['confirmed']; ?></strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Pending Today</small>
                            <strong class="text-warning" id="statusPendingCount"><?php echo (int)$status_totals['pending']; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-gs">
        <div class="col-md-8">
            <div class="card card-bordered">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-graph-up-arrow"></i> Booking Volume vs Revenue</span>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary chart-export-btn" data-chart="bookingRevenueChart" data-format="csv">CSV</button>
                        <button class="btn btn-outline-secondary chart-export-btn" data-chart="bookingRevenueChart" data-format="png">PNG</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="analytics-chart-wrap wide">
                        <canvas id="bookingRevenueChart"></canvas>
                    </div>
                    <small class="text-muted d-none" id="bookingRevenueHint">No booking or revenue data in the selected date range.</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-bordered h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-trophy"></i> Top Rooms by Revenue</span>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary chart-export-btn" data-chart="topRoomsChart" data-format="csv">CSV</button>
                        <button class="btn btn-outline-secondary chart-export-btn" data-chart="topRoomsChart" data-format="png">PNG</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="analytics-chart-wrap compact">
                        <canvas id="topRoomsChart"></canvas>
                    </div>
                    <small class="text-muted d-none" id="topRoomsHint">No room sales data in the selected date range.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-gs mt-1">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-activity"></i> Occupancy Forecast (Next 30 Days)</span>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary chart-export-btn" data-chart="occupancyForecastChart" data-format="csv">CSV</button>
                        <button class="btn btn-outline-secondary chart-export-btn" data-chart="occupancyForecastChart" data-format="png">PNG</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="analytics-chart-wrap forecast">
                        <canvas id="occupancyForecastChart"></canvas>
                    </div>
                </div>
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
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart !== 'undefined') {
                Chart.defaults.animation = false;
                if (Chart.defaults.transitions && Chart.defaults.transitions.active) {
                    Chart.defaults.transitions.active.animation = false;
                }
                if (Chart.defaults.transitions && Chart.defaults.transitions.resize) {
                    Chart.defaults.transitions.resize.animation = false;
                }
            }

            var analyticsEndpoint = '<?php echo base_url("dashboard/analytics_data"); ?>';
            var currentRange = <?php echo json_encode(isset($analytics_range) ? $analytics_range : array('selected' => '30d', 'start_date' => date('Y-m-d', strtotime('-29 days')), 'end_date' => date('Y-m-d'))); ?>;
            var latestPayload = {
                range: currentRange,
                timeseries: <?php echo json_encode(isset($booking_revenue_timeseries) ? $booking_revenue_timeseries : array()); ?>,
                status_analytics: <?php echo json_encode(isset($status_analytics) ? $status_analytics : array()); ?>,
                today_status_analytics: <?php echo json_encode(isset($today_status_analytics) ? $today_status_analytics : array()); ?>,
                top_rooms: <?php echo json_encode(isset($top_rooms_analytics) ? $top_rooms_analytics : array()); ?>,
                inventory_summary: <?php echo json_encode(isset($inventory_summary_today) ? $inventory_summary_today : array('total_units' => 0, 'booked_units' => 0, 'utilization_rate' => 0)); ?>,
                occupancy_forecast: <?php echo json_encode(isset($occupancy_forecast) ? $occupancy_forecast : array()); ?>
            };

            var chartInstances = {};
            var rangeSelect = document.getElementById('analyticsRange');
            var startInput = document.getElementById('analyticsStartDate');
            var endInput = document.getElementById('analyticsEndDate');
            var startWrap = document.getElementById('customStartWrap');
            var endWrap = document.getElementById('customEndWrap');
            var applyButton = document.getElementById('applyAnalyticsRange');
            var quickLast30DaysButton = document.getElementById('quickLast30Days');
            var salesTrendModeSelect = document.getElementById('salesTrendMode');

            function formatCurrency(value) {
                return 'PHP ' + Number(value || 0).toLocaleString();
            }

            function formatCurrencyCompact(value) {
                var numeric = Number(value || 0);
                if (numeric >= 1000000) {
                    return 'PHP ' + (numeric / 1000000).toFixed(1) + 'M';
                }
                if (numeric >= 1000) {
                    return 'PHP ' + (numeric / 1000).toFixed(1) + 'k';
                }
                return 'PHP ' + numeric.toFixed(0);
            }

            function toggleCustomDateInputs() {
                var isCustom = rangeSelect && rangeSelect.value === 'custom';
                if (startWrap) startWrap.style.display = isCustom ? 'block' : 'none';
                if (endWrap) endWrap.style.display = isCustom ? 'block' : 'none';
            }

            function getStatusValues(statusAnalytics) {
                var keys = ['pending', 'confirmed', 'cancelled', 'completed'];
                return keys.map(function(key) {
                    return statusAnalytics && statusAnalytics[key] ? Number(statusAnalytics[key].bookings_count || 0) : 0;
                });
            }

            function updateInventoryCards(payload) {
                var statusAnalytics = payload.today_status_analytics || {};
                var inventory = payload.inventory_summary || { total_units: 0, booked_units: 0, utilization_rate: 0 };

                var totalUnitsEl = document.getElementById('invTotalUnits');
                var bookedUnitsEl = document.getElementById('invBookedUnits');
                var utilizationTextEl = document.getElementById('invUtilizationText');
                var utilizationBarEl = document.getElementById('invUtilizationBar');
                var confirmedEl = document.getElementById('statusConfirmedCount');
                var pendingEl = document.getElementById('statusPendingCount');

                if (totalUnitsEl) totalUnitsEl.textContent = Number(inventory.total_units || 0);
                if (bookedUnitsEl) bookedUnitsEl.textContent = Number(inventory.booked_units || 0);
                if (utilizationTextEl) utilizationTextEl.textContent = Number(inventory.utilization_rate || 0).toFixed(1) + '%';
                if (utilizationBarEl) utilizationBarEl.style.width = Math.min(100, Math.max(0, Number(inventory.utilization_rate || 0))) + '%';
                if (confirmedEl) confirmedEl.textContent = statusAnalytics.confirmed ? Number(statusAnalytics.confirmed.bookings_count || 0) : 0;
                if (pendingEl) pendingEl.textContent = statusAnalytics.pending ? Number(statusAnalytics.pending.bookings_count || 0) : 0;
            }

            function computeMovingAverage(values, windowSize) {
                var size = Math.max(1, Number(windowSize || 7));
                return values.map(function(_, index) {
                    var start = Math.max(0, index - size + 1);
                    var subset = values.slice(start, index + 1);
                    if (!subset.length) {
                        return 0;
                    }
                    var sum = subset.reduce(function(total, value) {
                        return total + Number(value || 0);
                    }, 0);
                    return Number((sum / subset.length).toFixed(2));
                });
            }

            function upsertChart(chartId, config) {
                if (typeof Chart === 'undefined') {
                    return;
                }

                var chartEl = document.getElementById(chartId);
                if (!chartEl) {
                    return;
                }

                if (chartInstances[chartId]) {
                    chartInstances[chartId].data = config.data;
                    chartInstances[chartId].options = config.options || chartInstances[chartId].options;
                    chartInstances[chartId].update('none');
                } else {
                    chartInstances[chartId] = new Chart(chartEl, config);
                }
            }

            function renderCharts(payload) {
                var timeseries = payload.timeseries || [];
                var statusAnalytics = payload.status_analytics || {};
                var topRooms = payload.top_rooms || [];
                var occupancyForecast = payload.occupancy_forecast || [];

                var labels = timeseries.map(function(item) { return item.label; });
                var revenue = timeseries.map(function(item) { return Number(item.confirmed_revenue || 0); });
                var revenueAvg7 = computeMovingAverage(revenue, 7);
                var maxRevenueValue = Math.max.apply(null, [0].concat(revenue).concat(revenueAvg7));
                var salesNoRevenue = maxRevenueValue <= 0;
                var bookings = timeseries.map(function(item) { return Number(item.bookings_count || 0); });
                var maxBookingsValue = Math.max.apply(null, [0].concat(bookings));
                var statusValues = getStatusValues(statusAnalytics);
                var statusTotal = statusValues.reduce(function(total, value) { return total + Number(value || 0); }, 0);
                var topRoomLabels = topRooms.map(function(item) { return item.room_name; });
                var topRoomRevenue = topRooms.map(function(item) { return Number(item.revenue || 0); });
                var topRoomUnits = topRooms.map(function(item) { return Number(item.sold_units || 0); });
                var maxTopRoomRevenue = Math.max.apply(null, [0].concat(topRoomRevenue));
                var topRoomsHasData = topRoomRevenue.some(function(v) { return v > 0; }) || topRoomUnits.some(function(v) { return v > 0; });
                var bookingRevenueHasData = maxBookingsValue > 0 || maxRevenueValue > 0;
                var forecastLabels = occupancyForecast.map(function(item) { return item.label; });
                var forecastRates = occupancyForecast.map(function(item) { return Number(item.occupancy_rate || 0); });

                var trendMode = salesTrendModeSelect ? salesTrendModeSelect.value : 'daily';
                var salesTrendDatasets = [];

                if (trendMode === 'daily' || trendMode === 'both') {
                    salesTrendDatasets.push({
                        type: 'bar',
                        label: 'Revenue',
                        data: revenue,
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.6)',
                        borderWidth: 1,
                        borderRadius: 4,
                        maxBarThickness: 26
                    });
                }

                if (trendMode === 'avg' || trendMode === 'both') {
                    salesTrendDatasets.push({
                        type: 'line',
                        label: '7-day Avg',
                        data: revenueAvg7,
                        borderColor: '#0f766e',
                        backgroundColor: 'rgba(15, 118, 110, 0.15)',
                        borderWidth: 2,
                        tension: 0.25,
                        pointRadius: 2,
                        fill: trendMode === 'avg'
                    });
                }

                upsertChart('salesTrendChart', {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: salesTrendDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMax: salesNoRevenue ? 1000 : Math.ceil(maxRevenueValue * 1.1),
                                grace: '10%',
                                ticks: {
                                    callback: function(value) { return formatCurrencyCompact(value); }
                                }
                            }
                        }
                    }
                });

                var salesTrendHint = document.getElementById('salesTrendHint');
                if (salesTrendHint) {
                    salesTrendHint.classList.toggle('d-none', !salesNoRevenue);
                }

                upsertChart('statusMixChart', {
                    type: 'bar',
                    data: {
                        labels: ['Pending', 'Confirmed', 'Cancelled', 'Completed'],
                        datasets: [{
                            label: 'Bookings',
                            data: statusValues,
                            backgroundColor: ['rgba(245, 158, 11, 0.75)', 'rgba(16, 185, 129, 0.75)', 'rgba(239, 68, 68, 0.75)', 'rgba(101, 118, 255, 0.75)'],
                            borderColor: ['#f59e0b', '#10b981', '#ef4444', '#6576ff'],
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grace: '10%',
                                ticks: {
                                    precision: 0,
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });

                var statusMixHint = document.getElementById('statusMixHint');
                if (statusMixHint) {
                    statusMixHint.classList.toggle('d-none', statusTotal > 0);
                }

                upsertChart('bookingRevenueChart', {
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Bookings',
                                data: bookings,
                                backgroundColor: 'rgba(101, 118, 255, 0.55)',
                                borderColor: '#6576ff',
                                borderWidth: 1,
                                yAxisID: 'yBookings'
                            },
                            {
                                type: 'line',
                                label: 'Revenue',
                                data: revenue,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.12)',
                                tension: 0.3,
                                yAxisID: 'yRevenue'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        scales: {
                            yBookings: {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true,
                                suggestedMax: maxBookingsValue > 0 ? Math.ceil(maxBookingsValue * 1.1) : 5,
                                grace: '10%',
                                ticks: {
                                    precision: 0,
                                    stepSize: 1
                                }
                            },
                            yRevenue: {
                                type: 'linear',
                                position: 'right',
                                beginAtZero: true,
                                suggestedMax: maxRevenueValue > 0 ? Math.ceil(maxRevenueValue * 1.1) : 1000,
                                grace: '10%',
                                grid: { drawOnChartArea: false },
                                ticks: {
                                    callback: function(value) { return formatCurrencyCompact(value); }
                                }
                            }
                        }
                    }
                });

                var bookingRevenueHint = document.getElementById('bookingRevenueHint');
                if (bookingRevenueHint) {
                    bookingRevenueHint.classList.toggle('d-none', bookingRevenueHasData);
                }

                upsertChart('topRoomsChart', {
                    type: 'bar',
                    data: {
                        labels: topRoomLabels,
                        datasets: [{
                            label: 'Revenue',
                            data: topRoomRevenue,
                            backgroundColor: 'rgba(239, 68, 68, 0.65)',
                            borderColor: '#ef4444',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                suggestedMax: maxTopRoomRevenue > 0 ? Math.ceil(maxTopRoomRevenue * 1.1) : 1000,
                                ticks: {
                                    callback: function(value) { return formatCurrencyCompact(value); }
                                }
                            }
                        }
                    }
                });

                var topRoomsHint = document.getElementById('topRoomsHint');
                if (topRoomsHint) {
                    topRoomsHint.classList.toggle('d-none', topRoomsHasData);
                }

                upsertChart('occupancyForecastChart', {
                    type: 'line',
                    data: {
                        labels: forecastLabels,
                        datasets: [{
                            label: 'Occupancy %',
                            data: forecastRates,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.15)',
                            borderWidth: 2,
                            tension: 0.25,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    callback: function(value) { return Number(value).toFixed(0) + '%'; }
                                }
                            }
                        }
                    }
                });

                updateInventoryCards(payload);
            }

            function triggerAnalyticsRefresh() {
                if (!rangeSelect || !applyButton) {
                    return;
                }

                var params = new URLSearchParams();
                params.set('range', rangeSelect.value);
                if (rangeSelect.value === 'custom') {
                    if (!startInput.value || !endInput.value) {
                        alert('Please select both start and end dates for custom range.');
                        return;
                    }
                    params.set('start_date', startInput.value);
                    params.set('end_date', endInput.value);
                }

                applyButton.disabled = true;
                applyButton.innerHTML = '<i class="bi bi-arrow-repeat"></i> Loading...';

                fetch(analyticsEndpoint + '?' + params.toString())
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (!data.success) {
                            throw new Error('Failed to refresh analytics data.');
                        }
                        latestPayload = data;
                        renderCharts(latestPayload);
                    })
                    .catch(function(error) {
                        console.error('Analytics refresh error:', error);
                        alert('Unable to refresh analytics right now. Please try again.');
                    })
                    .finally(function() {
                        applyButton.disabled = false;
                        applyButton.innerHTML = '<i class="bi bi-funnel"></i> Apply Range';
                    });
            }

            function exportChartAsPng(chartId) {
                if (!chartInstances[chartId]) {
                    return;
                }
                var link = document.createElement('a');
                link.href = chartInstances[chartId].toBase64Image('image/png', 1);
                link.download = chartId + '-' + new Date().toISOString().slice(0, 10) + '.png';
                link.click();
            }

            function exportRowsAsCsv(filename, headers, rows) {
                var lines = [headers.join(',')];
                rows.forEach(function(row) {
                    lines.push(row.map(function(value) {
                        var stringValue = String(value === null || value === undefined ? '' : value);
                        return '"' + stringValue.replace(/"/g, '""') + '"';
                    }).join(','));
                });

                var blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                link.click();
                URL.revokeObjectURL(link.href);
            }

            function exportChartAsCsv(chartId) {
                var rows = [];
                var headers = [];

                if (chartId === 'statusMixChart') {
                    headers = ['Status', 'Bookings'];
                    var statusMap = latestPayload.status_analytics || {};
                    [['pending', 'Pending'], ['confirmed', 'Confirmed'], ['cancelled', 'Cancelled'], ['completed', 'Completed']].forEach(function(item) {
                        rows.push([item[1], statusMap[item[0]] ? Number(statusMap[item[0]].bookings_count || 0) : 0]);
                    });
                } else if (chartId === 'topRoomsChart') {
                    headers = ['Room', 'Sold Units', 'Revenue'];
                    (latestPayload.top_rooms || []).forEach(function(item) {
                        rows.push([item.room_name || '-', Number(item.sold_units || 0), Number(item.revenue || 0)]);
                    });
                } else if (chartId === 'occupancyForecastChart') {
                    headers = ['Date', 'Booked Units', 'Total Units', 'Occupancy Rate'];
                    (latestPayload.occupancy_forecast || []).forEach(function(item) {
                        rows.push([item.date, Number(item.booked_units || 0), Number(item.total_units || 0), Number(item.occupancy_rate || 0)]);
                    });
                } else {
                    headers = ['Period', 'Bookings', 'Confirmed Revenue'];
                    (latestPayload.timeseries || []).forEach(function(item) {
                        rows.push([item.period || item.label, Number(item.bookings_count || 0), Number(item.confirmed_revenue || 0)]);
                    });
                }

                exportRowsAsCsv(chartId + '-' + new Date().toISOString().slice(0, 10) + '.csv', headers, rows);
            }

            if (rangeSelect) {
                rangeSelect.addEventListener('change', toggleCustomDateInputs);
                toggleCustomDateInputs();
            }

            if (applyButton) {
                applyButton.addEventListener('click', triggerAnalyticsRefresh);
            }

            if (quickLast30DaysButton) {
                quickLast30DaysButton.addEventListener('click', function() {
                    if (!rangeSelect) {
                        return;
                    }
                    rangeSelect.value = '30d';
                    toggleCustomDateInputs();
                    triggerAnalyticsRefresh();
                });
            }

            if (salesTrendModeSelect) {
                salesTrendModeSelect.addEventListener('change', function() {
                    renderCharts(latestPayload);
                });
            }

            document.querySelectorAll('.chart-export-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    var chartId = this.getAttribute('data-chart');
                    var format = this.getAttribute('data-format');
                    if (format === 'png') {
                        exportChartAsPng(chartId);
                    } else {
                        exportChartAsCsv(chartId);
                    }
                });
            });

            renderCharts(latestPayload);

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

