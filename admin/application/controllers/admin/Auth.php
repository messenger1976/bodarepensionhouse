<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Admin_model');
        $this->load->helper('url');
        $this->load->helper('form');
    }
    
    public function index() {
        // Redirect to login if index is accessed
        $this->login();
    }
    
    public function login() {
        // If already logged in, redirect to dashboard
        if ($this->session->userdata('admin_logged_in')) {
            redirect('dashboard');
        }
        
        if ($this->input->post()) {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('username', 'Username', 'required');
            $this->form_validation->set_rules('password', 'Password', 'required');
            
            if ($this->form_validation->run() == TRUE) {
                $username = $this->input->post('username');
                $password = $this->input->post('password');
                
                $admin = $this->Admin_model->login($username, $password);
                
                if ($admin) {
                    // Regenerate session ID to prevent session fixation and ensure clean session
                    // This ensures each login gets a fresh session ID
                    // sess_regenerate with TRUE parameter destroys old session
                    $this->session->sess_regenerate(TRUE);
                    
                    // Set new session data
                    $session_data = array(
                        'admin_id' => $admin->id,
                        'admin_username' => $admin->username,
                        'admin_name' => $admin->name,
                        'admin_logged_in' => TRUE
                    );
                    $this->session->set_userdata($session_data);
                    
                    redirect('dashboard');
                } else {
                    $this->session->set_flashdata('error', 'Invalid username or password');
                }
            }
        }
        
        $data['title'] = 'Admin Login';
        $this->load->view('admin/auth/login', $data);
    }
    
    public function logout() {
        $this->session->sess_destroy();
        redirect('login');
    }
}

