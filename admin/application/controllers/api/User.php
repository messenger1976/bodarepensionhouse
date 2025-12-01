<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('User_model');
        $this->load->model('Customer_model');
        $this->load->library('form_validation');
        header('Content-Type: application/json');
    }
    
    /**
     * Get user profile
     */
    public function profile() {
        // Get the origin from the request
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        
        // Handle OPTIONS preflight request
        if ($this->input->method() === 'options') {
            exit;
        }
        
        $session_logged_in = $this->session->userdata('user_logged_in');
        $session_user_id = $this->session->userdata('user_id');
        
        if (!$session_logged_in) {
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'message' => 'Please log in to view your profile. Your session may have expired.'
            ]);
            return;
        }
        
        $user_id = $session_user_id;
        if (!$user_id) {
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'message' => 'User session is invalid. Please log in again.'
            ]);
            return;
        }
        
        $user = $this->User_model->get_user($user_id);
        
        if ($user) {
            // Try to get customer data for additional fields
            $customer = $this->Customer_model->get_customer_by_email($user->email);
            
            $userData = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => isset($user->phone) ? $user->phone : '',
                'address' => isset($user->address) ? $user->address : ''
            ];
            
            // Add customer data if available
            if ($customer) {
                $userData['city'] = $customer->city ? $customer->city : '';
                $userData['province'] = $customer->province ? $customer->province : '';
                $userData['postal_code'] = $customer->postal_code ? $customer->postal_code : '';
                $userData['country'] = $customer->country ? $customer->country : 'Philippines';
                $userData['date_of_birth'] = $customer->date_of_birth ? $customer->date_of_birth : '';
                $userData['gender'] = $customer->gender ? $customer->gender : '';
                $userData['nationality'] = $customer->nationality ? $customer->nationality : '';
                $userData['id_type'] = $customer->id_type ? $customer->id_type : '';
                $userData['id_number'] = $customer->id_number ? $customer->id_number : '';
            } else {
                // Set defaults if no customer record
                $userData['city'] = '';
                $userData['province'] = '';
                $userData['postal_code'] = '';
                $userData['country'] = 'Philippines';
                $userData['date_of_birth'] = '';
                $userData['gender'] = '';
                $userData['nationality'] = '';
                $userData['id_type'] = '';
                $userData['id_number'] = '';
            }
            
            echo json_encode([
                'success' => true,
                'user' => $userData
            ]);
        } else {
            $this->output->set_status_header(404);
            echo json_encode([
                'success' => false,
                'message' => 'User profile not found.'
            ]);
        }
    }
    
    /**
     * Update user profile
     */
    public function update() {
        // Get the origin from the request
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Credentials: true');
        
        if ($this->input->method() === 'options') {
            exit;
        }
        
        if (!$this->session->userdata('user_logged_in')) {
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'message' => 'Please log in to update your profile.'
            ]);
            return;
        }
        
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $this->input->post();
        }
        
        $user_id = $this->session->userdata('user_id');
        
        // Validation
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('phone', 'Phone', 'required|trim');
        $this->form_validation->set_rules('address', 'Address', 'required|trim');
        
        // Optional fields validation
        if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
            $this->form_validation->set_rules('date_of_birth', 'Date of Birth', 'trim');
        }
        if (isset($data['gender']) && !empty($data['gender'])) {
            $this->form_validation->set_rules('gender', 'Gender', 'trim|in_list[male,female,other]');
        }
        
        // Check if email is being changed and if it's already taken
        $current_user = $this->User_model->get_user($user_id);
        if ($current_user && $current_user->email !== $data['email']) {
            if ($this->User_model->email_exists($data['email'], $user_id)) {
                $this->output->set_status_header(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'This email address is already registered. Please use a different email.'
                ]);
                return;
            }
            // Also check customers table
            if ($this->Customer_model->email_exists($data['email'])) {
                $customer = $this->Customer_model->get_customer_by_email($data['email']);
                if ($customer && $customer->id) {
                    $this->output->set_status_header(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'This email address is already registered. Please use a different email.'
                    ]);
                    return;
                }
            }
        }
        
        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please check the form fields and ensure all information is entered correctly.',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }
        
        // Prepare user update data
        $user_update_data = array(
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address']
        );
        
        // Only update password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $this->output->set_status_header(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Password must be at least 6 characters long.'
                ]);
                return;
            }
            $user_update_data['password'] = $data['password'];
        }
        
        // Prepare customer update data
        $customer_update_data = array(
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'city' => isset($data['city']) ? trim($data['city']) : null,
            'province' => isset($data['province']) ? trim($data['province']) : null,
            'postal_code' => isset($data['postal_code']) ? trim($data['postal_code']) : null,
            'country' => isset($data['country']) && !empty($data['country']) ? trim($data['country']) : 'Philippines',
            'date_of_birth' => isset($data['date_of_birth']) && !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            'gender' => isset($data['gender']) && !empty($data['gender']) ? $data['gender'] : null,
            'nationality' => isset($data['nationality']) ? trim($data['nationality']) : null,
            'id_type' => isset($data['id_type']) && !empty($data['id_type']) ? $data['id_type'] : null,
            'id_number' => isset($data['id_number']) ? trim($data['id_number']) : null
        );
        
        // Start transaction
        $this->db->trans_start();
        
        // Update user
        $user_result = $this->User_model->update_user($user_id, $user_update_data);
        
        if (!$user_result) {
            $this->db->trans_rollback();
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We encountered an issue updating your profile. Please try again.'
            ]);
            return;
        }
        
        // Update or create customer record
        $customer = $this->Customer_model->get_customer_by_email($current_user->email);
        if ($customer) {
            // Update existing customer
            $customer_result = $this->Customer_model->update($customer->id, $customer_update_data);
        } else {
            // Create new customer record if it doesn't exist
            $customer_update_data['status'] = 'active';
            $customer_result = $this->Customer_model->create($customer_update_data);
        }
        
        if (!$customer_result) {
            $this->db->trans_rollback();
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We encountered an issue updating your profile. Please try again.'
            ]);
            return;
        }
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We encountered an issue updating your profile. Please try again.'
            ]);
            return;
        }
        
        // Get updated user
        $updated_user = $this->User_model->get_user($user_id);
        
        // Get updated customer data
        $updated_customer = $this->Customer_model->get_customer_by_email($updated_user->email);
        
        // Update session
        $this->session->set_userdata([
            'user_email' => $updated_user->email,
            'user_name' => $updated_user->first_name . ' ' . $updated_user->last_name
        ]);
        
        // Prepare response with all user data
        $response_user = [
            'id' => $updated_user->id,
            'name' => $updated_user->first_name . ' ' . $updated_user->last_name,
            'email' => $updated_user->email,
            'first_name' => $updated_user->first_name,
            'last_name' => $updated_user->last_name,
            'phone' => $updated_user->phone,
            'address' => $updated_user->address
        ];
        
        // Add customer data if available
        if ($updated_customer) {
            $response_user['city'] = $updated_customer->city ? $updated_customer->city : '';
            $response_user['province'] = $updated_customer->province ? $updated_customer->province : '';
            $response_user['postal_code'] = $updated_customer->postal_code ? $updated_customer->postal_code : '';
            $response_user['country'] = $updated_customer->country ? $updated_customer->country : 'Philippines';
            $response_user['date_of_birth'] = $updated_customer->date_of_birth ? $updated_customer->date_of_birth : '';
            $response_user['gender'] = $updated_customer->gender ? $updated_customer->gender : '';
            $response_user['nationality'] = $updated_customer->nationality ? $updated_customer->nationality : '';
            $response_user['id_type'] = $updated_customer->id_type ? $updated_customer->id_type : '';
            $response_user['id_number'] = $updated_customer->id_number ? $updated_customer->id_number : '';
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Your profile has been updated successfully!',
            'user' => $response_user
        ]);
    }
}

