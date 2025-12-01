<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Inquiries extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Inquiry_model');
        $this->load->model('User_model');
        $this->load->library('form_validation');
    }
    
    public function index() {
        // Require permission to view inquiries
        $this->require_permission('view_inquiries');
        
        $status = $this->input->get('status');
        $data['title'] = 'Manage Inquiries';
        $data['inquiries'] = $this->Inquiry_model->get_all($status);
        $data['can_edit'] = $this->has_permission('edit_inquiries');
        $data['can_delete'] = $this->has_permission('delete_inquiries');
        $data['current_status'] = $status;
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/inquiries/index', $data);
        $this->load->view('admin/layout/footer');
    }
    
    public function view($id) {
        // Require permission to view inquiries
        $this->require_permission('view_inquiries');
        
        $data['title'] = 'Inquiry Details';
        $data['inquiry'] = $this->Inquiry_model->get_inquiry($id);
        
        if (!$data['inquiry']) {
            show_404();
            return;
        }
        
        // Get user info if available
        if ($data['inquiry']->user_id) {
            $data['user'] = $this->User_model->get_user($data['inquiry']->user_id);
        }
        
        $data['can_edit'] = $this->has_permission('edit_inquiries');
        $data['can_delete'] = $this->has_permission('delete_inquiries');
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/inquiries/view', $data);
        $this->load->view('admin/layout/footer');
    }
    
    public function update_status($id) {
        // Require permission to edit inquiries
        $this->require_permission('edit_inquiries');
        
        $status = $this->input->post('status');
        
        if (!in_array($status, ['pending', 'in_progress', 'resolved', 'closed'])) {
            $this->session->set_flashdata('error', 'Invalid status selected.');
            redirect('inquiries/view/' . $id);
            return;
        }
        
        $result = $this->Inquiry_model->update_inquiry($id, ['status' => $status]);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Inquiry status updated successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to update inquiry status.');
        }
        
        redirect('inquiries/view/' . $id);
    }
    
    public function delete($id) {
        // Require permission to delete inquiries
        $this->require_permission('delete_inquiries');
        
        $inquiry = $this->Inquiry_model->get_inquiry($id);
        
        if (!$inquiry) {
            show_404();
            return;
        }
        
        $result = $this->Inquiry_model->delete_inquiry($id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Inquiry deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete inquiry.');
        }
        
        redirect('inquiries');
    }
}

