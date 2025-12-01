<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Rooms extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Room_model');
        $this->load->model('Room_image_model');
        $this->load->library('form_validation');
        $this->load->library('upload');
    }
    
    public function index() {
        // Require permission to view rooms
        $this->require_permission('view_rooms');
        
        $data['title'] = 'Manage Rooms';
        $data['rooms'] = $this->Room_model->get_all_rooms();
        $data['can_add'] = $this->has_permission('add_rooms');
        $data['can_edit'] = $this->has_permission('edit_rooms');
        $data['can_delete'] = $this->has_permission('delete_rooms');
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/rooms/index', $data);
        $this->load->view('admin/layout/footer');
    }
    
    public function add() {
        // Require permission to add rooms
        $this->require_permission('add_rooms');
        
        $data['title'] = 'Add New Room';
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('room_name', 'Room Name', 'required');
            $this->form_validation->set_rules('room_type', 'Room Type', 'required');
            $this->form_validation->set_rules('room_code', 'Room Code', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required|numeric');
            $this->form_validation->set_rules('capacity', 'Capacity', 'required|numeric');
            $this->form_validation->set_rules('available_rooms', 'Available Rooms', 'required|numeric|greater_than[0]');
            
            if ($this->form_validation->run() == TRUE) {
                $room_data = array(
                    'room_name' => $this->input->post('room_name'),
                    'room_type' => $this->input->post('room_type'),
                    'room_code' => $this->input->post('room_code'),
                    'price' => $this->input->post('price'),
                    'capacity' => $this->input->post('capacity'),
                    'available_rooms' => $this->input->post('available_rooms'),
                    'description' => $this->input->post('description'),
                    'amenities' => $this->input->post('amenities'),
                    'status' => $this->input->post('status') ? $this->input->post('status') : 'active'
                );
                
                if ($this->Room_model->create_room($room_data)) {
                    $this->session->set_flashdata('success', 'Room added successfully');
                    redirect('rooms');
                } else {
                    $this->session->set_flashdata('error', 'Failed to add room');
                }
            }
        }
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/rooms/add', $data);
        $this->load->view('admin/layout/footer');
    }
    
    public function edit($id) {
        // Require permission to edit rooms
        $this->require_permission('edit_rooms');
        
        $data['title'] = 'Edit Room';
        $data['room'] = $this->Room_model->get_room($id);
        $data['room_images'] = $this->Room_image_model->get_room_images($id);
        
        if (!$data['room']) {
            show_404();
            return;
        }
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('room_name', 'Room Name', 'required');
            $this->form_validation->set_rules('room_type', 'Room Type', 'required');
            $this->form_validation->set_rules('room_code', 'Room Code', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required|numeric');
            $this->form_validation->set_rules('capacity', 'Capacity', 'required|numeric');
            $this->form_validation->set_rules('available_rooms', 'Available Rooms', 'required|numeric|greater_than[0]');
            
            if ($this->form_validation->run() == TRUE) {
                $room_data = array(
                    'room_name' => $this->input->post('room_name'),
                    'room_type' => $this->input->post('room_type'),
                    'room_code' => $this->input->post('room_code'),
                    'price' => $this->input->post('price'),
                    'capacity' => $this->input->post('capacity'),
                    'available_rooms' => $this->input->post('available_rooms'),
                    'description' => $this->input->post('description'),
                    'amenities' => $this->input->post('amenities'),
                    'status' => $this->input->post('status')
                );
                
                if ($this->Room_model->update_room($id, $room_data)) {
                    $this->session->set_flashdata('success', 'Room updated successfully');
                    redirect('rooms');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update room');
                }
            }
        }
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/rooms/edit', $data);
        $this->load->view('admin/layout/footer');
    }
    
    public function delete($id) {
        // Require permission to delete rooms
        $this->require_permission('delete_rooms');
        
        // Delete all room images first
        $images = $this->Room_image_model->get_room_images($id);
        foreach ($images as $image) {
            $this->Room_image_model->delete_image($image->id);
        }
        
        if ($this->Room_model->delete_room($id)) {
            $this->session->set_flashdata('success', 'Room deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete room');
        }
        redirect('rooms');
    }
    
    /**
     * Upload room image
     */
    public function upload_image($room_id) {
        // Require permission to edit rooms
        $this->require_permission('edit_rooms');
        
        header('Content-Type: application/json');
        
        if (!$this->Room_model->room_exists($room_id)) {
            echo json_encode(['success' => false, 'message' => 'Room not found']);
            return;
        }
        
        // Get upload path from settings or use default
        $this->load->model('Room_settings_model');
        $settings = $this->Room_settings_model->get_all_settings();
        $upload_path = isset($settings['image_upload_path']) ? $settings['image_upload_path'] : 'img/rooms/';
        
        // Ensure upload directory exists
        $full_path = FCPATH . $upload_path;
        if (!is_dir($full_path)) {
            mkdir($full_path, 0755, true);
        }
        
        // Configure upload
        $config['upload_path'] = $full_path;
        $config['allowed_types'] = 'gif|jpg|jpeg|png|webp';
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = true;
        
        $this->upload->initialize($config);
        
        if ($this->upload->do_upload('image')) {
            $upload_data = $this->upload->data();
            $image_path = $upload_path . $upload_data['file_name'];
            
            // Get image count to set display order
            $image_count = $this->Room_image_model->get_image_count($room_id);
            
            // Check if this should be primary (first image)
            $is_primary = ($image_count == 0) ? 1 : 0;
            
            // Save to database
            $image_data = array(
                'room_id' => $room_id,
                'image_path' => $image_path,
                'image_name' => $upload_data['orig_name'],
                'alt_text' => $this->input->post('alt_text') ? $this->input->post('alt_text') : '',
                'display_order' => $image_count,
                'is_primary' => $is_primary
            );
            
            $image_id = $this->Room_image_model->add_image($image_data);
            
            if ($image_id) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Image uploaded successfully',
                    'image' => [
                        'id' => $image_id,
                        'path' => base_url($image_path),
                        'alt_text' => $image_data['alt_text'],
                        'is_primary' => $is_primary
                    ]
                ]);
            } else {
                // Delete uploaded file if database insert failed
                @unlink($full_path . $upload_data['file_name']);
                echo json_encode(['success' => false, 'message' => 'Failed to save image to database']);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => $this->upload->display_errors('', '')
            ]);
        }
    }
    
    /**
     * Delete room image
     */
    public function delete_image($image_id) {
        // Require permission to edit rooms
        $this->require_permission('edit_rooms');
        
        header('Content-Type: application/json');
        
        if ($this->Room_image_model->delete_image($image_id)) {
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete image']);
        }
    }
    
    /**
     * Set primary image
     */
    public function set_primary_image($room_id, $image_id) {
        // Require permission to edit rooms
        $this->require_permission('edit_rooms');
        
        header('Content-Type: application/json');
        
        if ($this->Room_image_model->set_primary_image($room_id, $image_id)) {
            echo json_encode(['success' => true, 'message' => 'Primary image updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update primary image']);
        }
    }
    
    /**
     * Calendar availability view
     */
    public function calendar() {
        // Require permission to view rooms
        $this->require_permission('view_rooms');
        
        $data['title'] = 'Room Availability Calendar';
        $data['rooms'] = $this->Room_model->get_all_rooms();
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/rooms/calendar', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Get availability data for calendar (API endpoint)
     */
    public function get_availability_data() {
        // Require permission to view rooms
        $this->require_permission('view_rooms');
        
        header('Content-Type: application/json');
        
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d', strtotime('+2 months'));
        $room_id = $this->input->get('room_id') ? $this->input->get('room_id') : null;
        
        $this->load->model('Booking_model');
        $availability_data = $this->Booking_model->get_room_availability_for_range($start_date, $end_date, $room_id);
        
        echo json_encode([
            'success' => true,
            'data' => $availability_data
        ]);
    }
}

