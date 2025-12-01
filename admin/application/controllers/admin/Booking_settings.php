<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Booking_settings extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Booking_settings_model');
        $this->load->library('form_validation');
    }
    
    /**
     * Booking settings page
     */
    public function index() {
        // Require permission to manage bookings
        $this->require_permission('manage_bookings');
        
        $data['title'] = 'Booking Settings';
        $data['settings'] = $this->Booking_settings_model->get_all_settings();
        
        if ($this->input->post()) {
            $settings_data = array(
                'default_status' => $this->input->post('default_status'),
                'booking_number_prefix' => $this->input->post('booking_number_prefix'),
                'min_booking_days' => $this->input->post('min_booking_days'),
                'max_booking_days' => $this->input->post('max_booking_days'),
                'check_in_time' => $this->input->post('check_in_time'),
                'check_out_time' => $this->input->post('check_out_time'),
                'cancellation_hours' => $this->input->post('cancellation_hours'),
                'require_payment' => $this->input->post('require_payment') ? '1' : '0',
                'send_email_notifications' => $this->input->post('send_email_notifications') ? '1' : '0',
                'auto_confirm_bookings' => $this->input->post('auto_confirm_bookings') ? '1' : '0',
                'tax_rate' => $this->input->post('tax_rate'),
                'service_charge' => $this->input->post('service_charge'),
                'booking_notes' => $this->input->post('booking_notes')
            );
            
            if ($this->Booking_settings_model->update_settings($settings_data)) {
                $this->session->set_flashdata('success', 'Booking settings updated successfully');
                redirect('booking_settings');
            } else {
                $this->session->set_flashdata('error', 'Failed to update settings');
            }
        }
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/bookings/settings', $data);
        $this->load->view('admin/layout/footer');
    }
}

