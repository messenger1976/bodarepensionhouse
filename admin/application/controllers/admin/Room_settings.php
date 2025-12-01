<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Room_settings extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Room_settings_model');
        $this->load->library('form_validation');
    }
    
    /**
     * Room settings page
     */
    public function index() {
        // Require permission to manage rooms
        $this->require_permission('manage_rooms');
        
        $data['title'] = 'Room Settings';
        $data['settings'] = $this->Room_settings_model->get_all_settings();
        
        if ($this->input->post()) {
            $settings_data = array(
                'default_status' => $this->input->post('default_status'),
                'default_capacity' => $this->input->post('default_capacity'),
                'max_capacity' => $this->input->post('max_capacity'),
                'price_currency' => $this->input->post('price_currency'),
                'price_display_format' => $this->input->post('price_display_format'),
                'room_types' => $this->input->post('room_types'),
                'amenities_list' => $this->input->post('amenities_list'),
                'image_upload_path' => $this->input->post('image_upload_path'),
                'max_images_per_room' => $this->input->post('max_images_per_room'),
                'allow_online_booking' => $this->input->post('allow_online_booking') ? '1' : '0',
                'show_availability_calendar' => $this->input->post('show_availability_calendar') ? '1' : '0',
                'room_notes' => $this->input->post('room_notes')
            );
            
            if ($this->Room_settings_model->update_settings($settings_data)) {
                $this->session->set_flashdata('success', 'Room settings updated successfully');
                redirect('room_settings');
            } else {
                $this->session->set_flashdata('error', 'Failed to update settings');
            }
        }
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/rooms/settings', $data);
        $this->load->view('admin/layout/footer');
    }
}

