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
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/dashboard/index', $data);
        $this->load->view('admin/layout/footer');
    }
}

