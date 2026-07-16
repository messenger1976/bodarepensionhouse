<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Dashboard extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Booking_model');
        $this->load->model('Room_model');
    }
    
    public function index() {
        // Require permission to view dashboard (optional check - can be removed if no permission system)
        // $this->require_permission('view_dashboard');
        
        // Ensure cache headers are set for this page
        $this->output->set_header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');
        $this->output->set_header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        
        $data['title'] = 'Dashboard';
        $data['total_bookings'] = $this->Booking_model->get_bookings_count();
        $data['pending_bookings'] = $this->Booking_model->get_pending_bookings_count();
        $data['confirmed_bookings'] = $this->Booking_model->get_confirmed_bookings_count();
        $data['total_revenue'] = $this->Booking_model->get_total_revenue();
        $data['total_rooms'] = $this->Room_model->get_rooms_count();
        $data['recent_bookings'] = $this->Booking_model->get_all_bookings();
        
        // Get bookings for calendar widget (next 3 months)
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+3 months'));
        $data['calendar_bookings'] = $this->Booking_model->get_bookings_for_calendar($start_date, $end_date);
        
        // Get room availability summary for today (for quick reference)
        $data['room_availability_today'] = $this->Booking_model->get_room_availability_for_date(date('Y-m-d'));
        
        // Get all active rooms for availability display
        $data['all_rooms'] = $this->Room_model->get_active_rooms();

        // Sales and inventory analytics defaults
        $range_info = $this->resolve_analytics_range('30d', null, null);
        $data['analytics_range'] = $range_info;
        $data['booking_revenue_timeseries'] = $this->Booking_model->get_booking_revenue_timeseries($range_info['start_date'], $range_info['end_date']);
        $data['status_analytics'] = $this->Booking_model->get_booking_status_analytics($range_info['start_date'], $range_info['end_date']);
        $data['today_status_analytics'] = $this->Booking_model->get_booking_status_analytics(date('Y-m-d'), date('Y-m-d'));
        $data['top_rooms_analytics'] = $this->Booking_model->get_top_rooms_analytics_by_range($range_info['start_date'], $range_info['end_date'], 6);
        $data['inventory_summary_today'] = $this->Booking_model->get_inventory_summary_for_date(date('Y-m-d'));
        $data['occupancy_forecast'] = $this->Booking_model->get_occupancy_forecast(30, date('Y-m-d'));
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/dashboard/index', $data);
        $this->load->view('admin/layout/footer');
    }

    public function analytics_data() {
        $range = $this->input->get('range', TRUE);
        $start_date = $this->input->get('start_date', TRUE);
        $end_date = $this->input->get('end_date', TRUE);

        $range_info = $this->resolve_analytics_range($range, $start_date, $end_date);
        $timeseries = $this->Booking_model->get_booking_revenue_timeseries($range_info['start_date'], $range_info['end_date']);
        $status = $this->Booking_model->get_booking_status_analytics($range_info['start_date'], $range_info['end_date']);
        $today_status = $this->Booking_model->get_booking_status_analytics(date('Y-m-d'), date('Y-m-d'));
        $top_rooms = $this->Booking_model->get_top_rooms_analytics_by_range($range_info['start_date'], $range_info['end_date'], 6);
        $inventory_summary = $this->Booking_model->get_inventory_summary_for_date(date('Y-m-d'));
        $occupancy_forecast = $this->Booking_model->get_occupancy_forecast(30, date('Y-m-d'));

        $this->output->set_content_type('application/json');
        echo json_encode(array(
            'success' => true,
            'range' => $range_info,
            'timeseries' => $timeseries,
            'status_analytics' => $status,
            'today_status_analytics' => $today_status,
            'top_rooms' => $top_rooms,
            'inventory_summary' => $inventory_summary,
            'occupancy_forecast' => $occupancy_forecast
        ));
    }

    private function resolve_analytics_range($range, $start_date = null, $end_date = null) {
        $today = date('Y-m-d');
        $default_end = date('Y-m-d', strtotime('-1 day'));
        $selected_range = $range ? strtolower(trim($range)) : '30d';

        if ($selected_range === '7d') {
            $end = $default_end;
            $start = date('Y-m-d', strtotime('-6 days', strtotime($end)));
        } elseif ($selected_range === '90d') {
            $end = $default_end;
            $start = date('Y-m-d', strtotime('-89 days', strtotime($end)));
        } elseif ($selected_range === 'ytd') {
            $start = date('Y-01-01');
            $end = $default_end;
        } elseif ($selected_range === 'custom') {
            $start = $this->sanitize_date($start_date);
            $end = $this->sanitize_date($end_date);

            if (!$start || !$end || strtotime($start) > strtotime($end)) {
                $selected_range = '30d';
                $end = $default_end;
                $start = date('Y-m-d', strtotime('-29 days', strtotime($end)));
            }
        } else {
            $selected_range = '30d';
            $end = $default_end;
            $start = date('Y-m-d', strtotime('-29 days', strtotime($end)));
        }

        if (strtotime($end) > strtotime($today)) {
            $end = $today;
        }

        return array(
            'selected' => $selected_range,
            'start_date' => $start,
            'end_date' => $end
        );
    }

    private function sanitize_date($date_value) {
        if (!$date_value) {
            return null;
        }

        $date_value = trim($date_value);
        $date = DateTime::createFromFormat('Y-m-d', $date_value);
        if (!$date || $date->format('Y-m-d') !== $date_value) {
            return null;
        }

        return $date_value;
    }
}

