<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Profile extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Admin_model');
        $this->load->library('form_validation');
        $this->load->library('upload');
    }
    
    /**
     * Display admin profile page
     */
    public function index() {
        $data['title'] = 'My Profile';
        $data['admin'] = $this->admin_data;
        $data['admin_id'] = $this->admin_id;
        
        // Get admin groups
        $data['groups'] = $this->Admin_model->get_admin_groups($this->admin_id);
        
        // Get admin roles
        $data['roles'] = $this->Admin_model->get_admin_roles($this->admin_id);
        
        // Get admin permissions
        $data['permissions'] = $this->Admin_model->get_admin_permissions($this->admin_id);
        
        // Check if super admin
        $data['is_super_admin'] = $this->Admin_model->is_super_admin($this->admin_id);
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/profile/index', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Update admin profile
     */
    public function update() {
        if ($this->input->method() !== 'post') {
            redirect('profile');
        }
        
        // Validation rules
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('username', 'Username', 'required|trim|min_length[3]');
        
        // Check if password is being changed
        $password = $this->input->post('password');
        if (!empty($password)) {
            $this->form_validation->set_rules('password', 'Password', 'min_length[6]');
            $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'matches[password]');
        }
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('profile');
        }
        
        // Prepare update data
        $update_data = array(
            'name' => $this->input->post('name'),
            'email' => $this->input->post('email'),
            'username' => $this->input->post('username')
        );
        
        // Add password if provided
        if (!empty($password)) {
            $update_data['password'] = $password;
        }
        
        // Handle avatar upload
        if (!empty($_FILES['avatar']['name'])) {
            $upload_path = 'admin/img/avatars/';
            $full_path = FCPATH . $upload_path;
            
            // Ensure upload directory exists
            if (!is_dir($full_path)) {
                mkdir($full_path, 0755, true);
            }
            
            // Configure upload
            $config['upload_path'] = $full_path;
            $config['allowed_types'] = 'gif|jpg|jpeg|png|webp';
            $config['max_size'] = 2048; // 2MB
            $config['encrypt_name'] = true;
            
            $this->upload->initialize($config);
            
            if ($this->upload->do_upload('avatar')) {
                $upload_data = $this->upload->data();
                $avatar_path = $upload_path . $upload_data['file_name'];
                
                // Delete old avatar if exists
                if (!empty($this->admin_data->avatar) && file_exists(FCPATH . $this->admin_data->avatar)) {
                    @unlink(FCPATH . $this->admin_data->avatar);
                }
                
                $update_data['avatar'] = $avatar_path;
            } else {
                $this->session->set_flashdata('error', 'Avatar upload failed: ' . $this->upload->display_errors('', ''));
                redirect('profile');
                return;
            }
        }
        
        // Update admin
        if ($this->Admin_model->update($this->admin_id, $update_data)) {
            $this->session->set_flashdata('success', 'Profile updated successfully!');
            
            // Refresh session data
            $admin = $this->Admin_model->get_admin($this->admin_id);
            if ($admin) {
                $session_data = array(
                    'admin_id' => $admin->id,
                    'admin_username' => $admin->username,
                    'admin_name' => $admin->name ? $admin->name : $admin->username,
                    'admin_logged_in' => TRUE
                );
                $this->session->set_userdata($session_data);
            }
        } else {
            $this->session->set_flashdata('error', 'Failed to update profile. Please try again.');
        }
        
        redirect('profile');
    }
}

