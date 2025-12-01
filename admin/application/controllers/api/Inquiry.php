<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inquiry extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Inquiry_model');
        $this->load->model('User_model');
        $this->load->library('form_validation');
        header('Content-Type: application/json');
    }
    
    /**
     * Submit an inquiry
     */
    public function submit() {
        // Start output buffering to catch any accidental output
        ob_start();
        
        // Get the origin from the request
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        
        if ($this->input->method() === 'options') {
            ob_end_clean();
            exit;
        }
        
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        // Check if user is logged in
        if (!$this->session->userdata('user_logged_in')) {
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'message' => 'Please log in to submit an inquiry.'
            ]);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $this->input->post();
        }
        
        // Validation
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('subject', 'Subject', 'required|trim|max_length[200]');
        $this->form_validation->set_rules('message', 'Message', 'required|trim');
        
        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please check the form fields and ensure all information is entered correctly.',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'message' => 'User session is invalid. Please log in again.'
            ]);
            return;
        }
        
        $user = $this->User_model->get_user($user_id);
        if (!$user) {
            $this->output->set_status_header(404);
            echo json_encode([
                'success' => false,
                'message' => 'User not found. Please log in again.'
            ]);
            return;
        }
        
        // Prepare inquiry data
        $inquiry_data = array(
            'user_id' => $user_id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email,
            'phone' => isset($user->phone) ? $user->phone : '',
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        );
        
        // Create inquiry
        try {
            // Clear any accidental output
            ob_clean();
            
            $inquiry_id = $this->Inquiry_model->create($inquiry_data);
            
            if ($inquiry_id) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Your inquiry has been sent successfully! We will get back to you soon.',
                    'inquiry_id' => $inquiry_id
                ]);
            } else {
                // Check for database errors
                $db_error = $this->db->error();
                if (!empty($db_error['message'])) {
                    log_message('error', 'Inquiry database error: ' . $db_error['message']);
                }
                
                $this->output->set_status_header(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'We encountered an issue sending your inquiry. Please try again.'
                ]);
            }
        } catch (Exception $e) {
            ob_clean();
            log_message('error', 'Inquiry creation error: ' . $e->getMessage());
            log_message('error', 'Inquiry creation stack trace: ' . $e->getTraceAsString());
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We encountered an issue sending your inquiry. Please try again.'
            ]);
        } finally {
            ob_end_flush();
        }
    }
}

