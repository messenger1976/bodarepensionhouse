<div class="nk-block">
    <!-- Report Header -->
    <div class="print-header" style="display: block; text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #e2e8f0;">
        <h1 style="font-size: 28px; margin: 0 0 5px 0; font-weight: bold; color: #1e293b;">BODARE PENSION HOUSE</h1>
        <div class="report-date" style="font-size: 16px; color: #64748b; margin-bottom: 5px;">Daily Sales Report - <?php echo date('F d, Y', strtotime($selected_date)); ?></div>
        <div style="font-size: 12px; color: #94a3b8;">Generated on: <?php echo date('F d, Y h:i A'); ?></div>
    </div>
    
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-graph-up"></i> Daily Sales Report</h3>
                <div class="nk-block-des text-soft">
                    <p>View sales data and booking statistics for a specific date</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li>
                                <a href="<?php echo base_url('reports/export_excel?date=' . urlencode($selected_date)); ?>" class="btn btn-success">
                                    <i class="bi bi-file-earmark-excel"></i> <span>Export to Excel</span>
                                </a>
                            </li>
                            <li>
                                <button onclick="window.print()" class="btn btn-outline-light">
                                    <i class="bi bi-printer"></i> <span>Print Report</span>
                                </button>
                            </li>
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
    
    <!-- Date Filter -->
    <div class="card card-bordered mb-4">
        <div class="card-inner">
            <form method="get" action="<?php echo base_url('reports/daily_sales'); ?>" class="row g-3">
                <div class="col-md-4">
                    <label for="date" class="form-label">Select Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row g-3 mb-4 no-print">
        <div class="col-md-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="card-title-group">
                        <div class="card-title">
                            <h6 class="title">Total Revenue</h6>
                        </div>
                    </div>
                    <div class="card-amount">
                        <span class="amount">₱<?php echo number_format($total_revenue, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="card-title-group">
                        <div class="card-title">
                            <h6 class="title">Total Bookings</h6>
                        </div>
                    </div>
                    <div class="card-amount">
                        <span class="amount"><?php echo $total_bookings; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="card-title-group">
                        <div class="card-title">
                            <h6 class="title">Confirmed</h6>
                        </div>
                    </div>
                    <div class="card-amount">
                        <span class="amount text-success"><?php echo $confirmed_bookings; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="card-title-group">
                        <div class="card-title">
                            <h6 class="title">Pending</h6>
                        </div>
                    </div>
                    <div class="card-amount">
                        <span class="amount text-warning"><?php echo $pending_bookings; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Summary Table for Print -->
    <div class="summary-table">
        <div class="summary-table-row">
            <div class="summary-table-cell">
                <div class="title">Total Revenue</div>
                <div class="amount">₱<?php echo number_format($total_revenue, 2); ?></div>
            </div>
            <div class="summary-table-cell">
                <div class="title">Total Bookings</div>
                <div class="amount"><?php echo $total_bookings; ?></div>
            </div>
            <div class="summary-table-cell">
                <div class="title">Confirmed</div>
                <div class="amount"><?php echo $confirmed_bookings; ?></div>
            </div>
            <div class="summary-table-cell">
                <div class="title">Pending</div>
                <div class="amount"><?php echo $pending_bookings; ?></div>
            </div>
        </div>
    </div>
    
    <!-- Sales by Room Type -->
    <?php if (!empty($sales_by_room)): ?>
    <div class="card card-bordered mb-4">
        <div class="card-inner">
            <div class="card-title-group">
                <div class="card-title">
                    <h6 class="title">Sales by Room Type</h6>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Room Type</th>
                            <th>Room Name</th>
                            <th>Number of Bookings</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales_by_room as $room_sale): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room_sale->room_type); ?></td>
                                <td><?php echo htmlspecialchars($room_sale->room_name); ?></td>
                                <td><span class="badge bg-info"><?php echo $room_sale->booking_count; ?></span></td>
                                <td><strong>₱<?php echo number_format($room_sale->total_revenue, 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Detailed Bookings List -->
    <div class="card card-bordered">
        <div class="card-inner">
            <div class="card-title-group">
                <div class="card-title">
                    <h6 class="title">Bookings for <?php echo date('F d, Y', strtotime($selected_date)); ?></h6>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="dailySalesTable">
                    <thead>
                        <tr>
                            <th>Booking Number</th>
                            <th>Guest Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Room</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Created At</th>
                            <th class="no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daily_sales)): ?>
                            <?php foreach ($daily_sales as $booking): ?>
                                <tr>
                                    <td>#<?php echo isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($booking->guest_name); ?></td>
                                    <td><?php echo htmlspecialchars($booking->guest_email); ?></td>
                                    <td><?php echo htmlspecialchars($booking->guest_phone); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($booking->room_name); ?>
                                        <?php if (!empty($booking->room_code)): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($booking->room_code); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($booking->check_in)); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking->check_out)); ?></td>
                                    <td><?php echo $booking->guests; ?></td>
                                    <td>
                                        <?php
                                        $badge_class = 'secondary';
                                        if ($booking->status == 'confirmed') $badge_class = 'success';
                                        if ($booking->status == 'cancelled') $badge_class = 'danger';
                                        if ($booking->status == 'pending') $badge_class = 'warning';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucfirst($booking->status); ?></span>
                                    </td>
                                    <td><strong>₱<?php echo number_format($booking->total_amount, 2); ?></strong></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($booking->created_at)); ?></td>
                                    <td class="no-print">
                                        <a href="<?php echo base_url('bookings/view/' . $booking->id); ?>" class="btn btn-sm btn-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted">No bookings found for this date</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f5f5f5; font-weight: bold;">
                            <td colspan="9" style="text-align: right;">TOTAL REVENUE:</td>
                            <td style="text-align: right;">₱<?php echo number_format($total_revenue, 2); ?></td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Print Footer -->
    <div class="print-footer">
        <div>BODARE PENSION HOUSE - Daily Sales Report</div>
        <div>Page 1 of 1</div>
    </div>
</div>

<script>
    // Initialize DataTable if available
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#dailySalesTable').DataTable({
                order: [[10, 'desc']], // Sort by Created At descending
                pageLength: 25,
                responsive: true
            });
        }
    });
</script>

<style>
    @media print {
        /* Hide navigation and UI elements */
        .nk-sidebar, .nk-header, .nk-block-tools, .btn, 
        .card-title-group .card-title:last-child, 
        .alert, form, .no-print {
            display: none !important;
        }
        
        /* Full width layout */
        .nk-content {
            margin-left: 0 !important;
            padding: 20px !important;
            width: 100% !important;
        }
        
        body {
            background: white !important;
            font-size: 12px !important;
        }
        
        /* Print header */
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000 !important;
        }
        
        .print-header h1 {
            font-size: 24px;
            margin: 0 0 5px 0;
            font-weight: bold;
            color: #000 !important;
        }
        
        .print-header .report-date {
            font-size: 14px;
            color: #666 !important;
        }
        
        .print-header div {
            color: #666 !important;
        }
        
        /* Summary section as table */
        .summary-table {
            display: table !important;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        
        .summary-table-row {
            display: table-row !important;
        }
        
        .summary-table-cell {
            display: table-cell !important;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }
        
        .summary-table-cell .title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .summary-table-cell .amount {
            font-size: 16px;
            font-weight: bold;
        }
        
        /* Cards styling */
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .card-inner {
            padding: 15px !important;
        }
        
        .card-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        /* Tables */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-bottom: 15px;
            page-break-inside: auto;
        }
        
        table thead {
            display: table-header-group !important;
        }
        
        table tbody {
            display: table-row-group !important;
        }
        
        table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        table th, table td {
            border: 1px solid #ddd !important;
            padding: 8px !important;
            text-align: left;
            font-size: 11px;
        }
        
        table th {
            background-color: #f5f5f5 !important;
            font-weight: bold;
            text-align: center;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9 !important;
        }
        
        /* Badges */
        .badge {
            padding: 4px 8px;
            border: 1px solid #333;
            background: white !important;
            color: #333 !important;
            font-weight: normal;
        }
        
        /* Hide DataTables elements */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            display: none !important;
        }
        
        /* Print footer */
        .print-footer {
            display: block !important;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    }
    
    /* Print header styling */
    .print-header {
        display: block;
    }
    
    .print-footer {
        display: none;
    }
    
    /* Summary table (hidden on screen, shown in print) */
    .summary-table {
        display: none;
    }
    
    @media print {
        .summary-table {
            display: table !important;
        }
    }
</style>


