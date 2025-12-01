<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Reports extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Booking_model');
        $this->load->model('Room_model');
    }
    
    /**
     * Index - redirect to daily sales report
     */
    public function index() {
        redirect('reports/daily_sales');
    }
    
    /**
     * Daily Sales Report
     * Shows sales data for bookings on a specific date
     */
    public function daily_sales() {
        // Require permission to view reports (allows super admin if permission doesn't exist)
        if ($this->db->table_exists('permissions')) {
            $this->require_permission('view_reports');
        } else {
            // If permission system doesn't exist, only allow super admin
            if (!$this->is_super_admin()) {
                $this->session->set_flashdata('error', 'You do not have permission to access this page.');
                redirect('dashboard');
            }
        }
        
        $data['title'] = 'Daily Sales Report';
        
        // Get date from query string or use today's date
        $date = $this->input->get('date') ? $this->input->get('date') : date('Y-m-d');
        $data['selected_date'] = $date;
        
        // Get daily sales data
        $data['daily_sales'] = $this->Booking_model->get_daily_sales($date);
        $data['total_revenue'] = $this->Booking_model->get_daily_total_revenue($date);
        $data['total_bookings'] = $this->Booking_model->get_daily_bookings_count($date);
        $data['confirmed_bookings'] = $this->Booking_model->get_daily_confirmed_bookings_count($date);
        $data['pending_bookings'] = $this->Booking_model->get_daily_pending_bookings_count($date);
        
        // Get bookings by room type for the day
        $data['sales_by_room'] = $this->Booking_model->get_daily_sales_by_room($date);
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/daily_sales', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Export Daily Sales Report to Excel (CSV format)
     */
    public function export_excel() {
        // Require permission to view reports (allows super admin if permission doesn't exist)
        if ($this->db->table_exists('permissions')) {
            $this->require_permission('view_reports');
        } else {
            // If permission system doesn't exist, only allow super admin
            if (!$this->is_super_admin()) {
                $this->session->set_flashdata('error', 'You do not have permission to access this page.');
                redirect('dashboard');
            }
        }
        
        // Get date from query string or use today's date
        $date = $this->input->get('date') ? $this->input->get('date') : date('Y-m-d');
        
        // Get daily sales data
        $daily_sales = $this->Booking_model->get_daily_sales($date);
        $total_revenue = $this->Booking_model->get_daily_total_revenue($date);
        $total_bookings = $this->Booking_model->get_daily_bookings_count($date);
        $confirmed_bookings = $this->Booking_model->get_daily_confirmed_bookings_count($date);
        $pending_bookings = $this->Booking_model->get_daily_pending_bookings_count($date);
        $sales_by_room = $this->Booking_model->get_daily_sales_by_room($date);
        
        // Set headers for Excel download
        $filename = 'Daily_Sales_Report_' . date('Y-m-d', strtotime($date)) . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Report Header
        fputcsv($output, array('BODARE PENSION HOUSE'));
        fputcsv($output, array('Daily Sales Report - ' . date('F d, Y', strtotime($date))));
        fputcsv($output, array('Generated on: ' . date('F d, Y h:i A')));
        fputcsv($output, array('')); // Empty row
        
        // Summary Section
        fputcsv($output, array('SUMMARY'));
        fputcsv($output, array('Total Revenue', 'Total Bookings', 'Confirmed', 'Pending'));
        fputcsv($output, array(
            '₱' . number_format($total_revenue, 2),
            $total_bookings,
            $confirmed_bookings,
            $pending_bookings
        ));
        fputcsv($output, array('')); // Empty row
        
        // Sales by Room Type
        if (!empty($sales_by_room)) {
            fputcsv($output, array('SALES BY ROOM TYPE'));
            fputcsv($output, array('Room Type', 'Room Name', 'Number of Bookings', 'Total Revenue'));
            foreach ($sales_by_room as $room_sale) {
                fputcsv($output, array(
                    $room_sale->room_type,
                    $room_sale->room_name,
                    $room_sale->booking_count,
                    '₱' . number_format($room_sale->total_revenue, 2)
                ));
            }
            fputcsv($output, array('')); // Empty row
        }
        
        // Detailed Bookings
        fputcsv($output, array('DETAILED BOOKINGS'));
        fputcsv($output, array(
            'Booking Number',
            'Guest Name',
            'Email',
            'Phone',
            'Room',
            'Room Code',
            'Check-In',
            'Check-Out',
            'Guests',
            'Status',
            'Amount',
            'Created At'
        ));
        
        if (!empty($daily_sales)) {
            foreach ($daily_sales as $booking) {
                $booking_number = isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT);
                $room_code = isset($booking->room_code) ? $booking->room_code : '';
                
                fputcsv($output, array(
                    $booking_number,
                    $booking->guest_name,
                    $booking->guest_email,
                    $booking->guest_phone,
                    $booking->room_name,
                    $room_code,
                    date('M d, Y', strtotime($booking->check_in)),
                    date('M d, Y', strtotime($booking->check_out)),
                    $booking->guests,
                    ucfirst($booking->status),
                    '₱' . number_format($booking->total_amount, 2),
                    date('M d, Y h:i A', strtotime($booking->created_at))
                ));
            }
        } else {
            fputcsv($output, array('No bookings found for this date'));
        }
        
        // Total Revenue Footer
        fputcsv($output, array('')); // Empty row
        fputcsv($output, array('TOTAL REVENUE', '', '', '', '', '', '', '', '', '', '₱' . number_format($total_revenue, 2)));
        
        fclose($output);
        exit;
    }
}

