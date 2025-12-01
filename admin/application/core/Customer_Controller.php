<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_Controller extends MY_Controller {
    
    protected $user_id;
    protected $user_data;
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('User_model');
        
        // Check if user is logged in
        if (!$this->session->userdata('user_logged_in')) {
            redirect('login');
        }
        
        // Load user data
        $this->user_id = $this->session->userdata('user_id');
        $this->user_data = $this->User_model->get_user($this->user_id);
        
        if (!$this->user_data || $this->user_data->status != 'active') {
            $this->session->unset_userdata(['user_logged_in', 'user_id', 'user_email', 'user_name']);
            redirect('login');
        }
    }
}

