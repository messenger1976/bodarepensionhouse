<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Customer_Controller
if (!class_exists('Customer_Controller', FALSE)) {
    require_once(APPPATH.'core/Customer_Controller.php');
}

class Dashboard extends Customer_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Booking_model');
        $this->load->model('Room_model');
    }
    
    /**
     * Customer dashboard - view bookings
     */
    public function index() {
        $data['title'] = 'My Bookings';
        $data['user'] = $this->user_data;
        $data['bookings'] = $this->User_model->get_user_bookings($this->user_id);
        
        // Load a simple view or redirect to frontend dashboard
        // For now, we'll return JSON or redirect
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $this->user_data->id,
                'name' => $this->user_data->first_name . ' ' . $this->user_data->last_name,
                'email' => $this->user_data->email
            ],
            'bookings' => $data['bookings']
        ]);
    }
    
    /**
     * View booking details
     */
    public function booking($booking_number) {
        $booking = $this->Booking_model->get_booking_by_number($booking_number);
        
        if (!$booking) {
            show_404();
            return;
        }
        
        // Check if booking belongs to user
        if ($booking->user_id != $this->user_id) {
            show_error('You do not have permission to view this booking.', 403);
            return;
        }
        
        $data['title'] = 'Booking Details';
        $data['booking'] = $booking;
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'booking' => $booking
        ]);
    }
}

