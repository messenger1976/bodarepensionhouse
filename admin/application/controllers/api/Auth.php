<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('User_model');
        $this->load->model('Customer_model');
        $this->load->model('Password_reset_model');
        $this->load->library('form_validation');
        header('Content-Type: application/json');
    }
    
    /**
     * Register new user
     */
    public function register() {
        // Allow CORS for frontend
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        
        if ($this->input->method() === 'options') {
            exit;
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
        
        // Validate we have data
        if (empty($data)) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'No data received. Please check your request.'
            ]);
            return;
        }
        
        // Validation
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('phone', 'Phone', 'required|trim');
        $this->form_validation->set_rules('address', 'Address', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
        
        // Optional fields validation
        if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
            $this->form_validation->set_rules('date_of_birth', 'Date of Birth', 'trim');
        }
        if (isset($data['gender']) && !empty($data['gender'])) {
            $this->form_validation->set_rules('gender', 'Gender', 'trim|in_list[male,female,other]');
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
        
        // Normalize email (trim and lowercase)
        $email = trim(strtolower($data['email']));
        
        // Check if email exists in users table
        if ($this->User_model->email_exists($email)) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'This email address is already registered. Please use a different email or try logging in instead.'
            ]);
            return;
        }
        
        // Check if email exists in customers table
        if ($this->Customer_model->email_exists($email)) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'This email address is already registered. Please use a different email or try logging in instead.'
            ]);
            return;
        }
        
        // Prepare user data (for authentication)
        $user_data = array(
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => $email, // Use normalized email
            'phone' => trim($data['phone']),
            'address' => trim($data['address']),
            'password' => $data['password'],
            'status' => 'active'
        );
        
        // Prepare customer data (for detailed customer records)
        $customer_data = array(
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => $email,
            'phone' => trim($data['phone']),
            'address' => trim($data['address']),
            'city' => isset($data['city']) ? trim($data['city']) : null,
            'province' => isset($data['province']) ? trim($data['province']) : null,
            'postal_code' => isset($data['postal_code']) ? trim($data['postal_code']) : null,
            'country' => isset($data['country']) && !empty($data['country']) ? trim($data['country']) : 'Philippines',
            'date_of_birth' => isset($data['date_of_birth']) && !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            'gender' => isset($data['gender']) && !empty($data['gender']) ? $data['gender'] : null,
            'nationality' => isset($data['nationality']) ? trim($data['nationality']) : null,
            'id_type' => isset($data['id_type']) && !empty($data['id_type']) ? $data['id_type'] : null,
            'id_number' => isset($data['id_number']) ? trim($data['id_number']) : null,
            'status' => 'active'
        );
        
        // Start transaction to ensure both records are created
        $this->db->trans_start();
        
        // Create user (for authentication)
        $user_id = $this->User_model->register($user_data);
        
        if ($user_id) {
            // Create customer record (for detailed customer management)
            $customer_id = $this->Customer_model->create($customer_data);
            
            if (!$customer_id) {
                // Rollback if customer creation fails
                $this->db->trans_rollback();
                $this->output->set_status_header(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'We encountered an issue creating your account. Our team has been notified. Please try again in a few moments.'
                ]);
                return;
            }
        } else {
            $this->db->trans_rollback();
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We encountered an issue creating your account. Our team has been notified. Please try again in a few moments.'
            ]);
            return;
        }
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We encountered an issue creating your account. Our team has been notified. Please try again in a few moments.'
            ]);
            return;
        }
        
        // Auto login after registration
        $user = $this->User_model->get_user($user_id);
        $this->session->set_userdata([
            'user_logged_in' => true,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->first_name . ' ' . $user->last_name
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Your account has been created successfully! Welcome to BODARE Pension House.',
            'user' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email
            ]
        ]);
    }
    
    /**
     * Login user
     */
    public function login() {
        // Allow CORS
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        
        if ($this->input->method() === 'options') {
            exit;
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
        
        // Validate we have data
        if (empty($data)) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'No data received. Please check your request.'
            ]);
            return;
        }
        
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please enter a valid email address and password.',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }
        
        // Trim email for consistency
        $email = trim(strtolower($data['email']));
        $password = $data['password'];
        
        // Check if user exists first
        $user_exists = $this->User_model->get_user_by_email($email);
        
        if (!$user_exists) {
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'message' => 'No account found with this email address. Please register first or check your email.'
            ]);
            return;
        }
        
        // Check if user is active
        if ($user_exists->status != 'active') {
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'message' => 'Your account is not active. Please contact support for assistance.'
            ]);
            return;
        }
        
        // Try to login
        $user = $this->User_model->login($email, $password);
        
        if ($user) {
            $this->session->set_userdata([
                'user_logged_in' => true,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->first_name . ' ' . $user->last_name
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Welcome back! You have successfully logged in.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email
                ]
            ]);
        } else {
            // More detailed error checking
            $user_exists = $this->User_model->get_user_by_email($email);
            
            if (!$user_exists) {
                $this->output->set_status_header(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'No account found with this email address. Please register first or check your email.'
                ]);
            } else if ($user_exists->status != 'active') {
                $this->output->set_status_header(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Your account is not active. Please contact support for assistance.'
                ]);
            } else {
                // User exists and is active, but password is wrong
                $this->output->set_status_header(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'The password you entered is incorrect. Please try again. If you forgot your password, please contact support.'
                ]);
            }
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        
        $this->session->unset_userdata(['user_logged_in', 'user_id', 'user_email', 'user_name']);
        $this->session->sess_destroy();
        
        echo json_encode([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }
    
    /**
     * Check if user is logged in
     */
    public function check() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        
        if ($this->session->userdata('user_logged_in')) {
            $user_id = $this->session->userdata('user_id');
            $user = $this->User_model->get_user($user_id);
            
            if ($user) {
                echo json_encode([
                    'success' => true,
                    'logged_in' => true,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'logged_in' => false,
                    'message' => 'User session exists but user not found'
                ]);
            }
        } else {
            echo json_encode([
                'success' => true,
                'logged_in' => false
            ]);
        }
    }
    
    /**
     * Forgot password - send reset link
     */
    public function forgot_password() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        
        if ($this->input->method() === 'options') {
            exit;
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
        
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        
        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please enter a valid email address.'
            ]);
            return;
        }
        
        $email = trim(strtolower($data['email']));
        
        // Check if user exists
        $user = $this->User_model->get_user_by_email($email);
        
        if (!$user) {
            // Don't reveal if email exists for security
            echo json_encode([
                'success' => true,
                'message' => 'If an account exists with this email, a password reset link has been sent.'
            ]);
            return;
        }
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Save token
        if ($this->Password_reset_model->create_token($email, $token, $expires_at)) {
            // Build reset URL - get the frontend URL
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
            $path = dirname($_SERVER['SCRIPT_NAME']);
            // Remove /admin from path if present
            $path = str_replace('/admin', '', $path);
            if (substr($path, -1) !== '/') {
                $path .= '/';
            }
            $reset_url = $protocol . $host . $path . 'reset-password.php?token=' . $token;
            
            // In production, send email here
            // For now, return the URL in development mode
            $response = [
                'success' => true,
                'message' => 'Password reset instructions have been sent to your email address. Please check your inbox and follow the link to reset your password.'
            ];
            
            // Include token in development mode (remove in production)
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $response['token'] = $token;
                $response['reset_url'] = $reset_url;
            }
            
            echo json_encode($response);
        } else {
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'Unable to process your request. Please try again later.'
            ]);
        }
    }
    
    /**
     * Verify reset token
     */
    public function verify_reset_token() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        
        if ($this->input->method() === 'options') {
            exit;
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
        
        if (empty($data['token'])) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Reset token is required.'
            ]);
            return;
        }
        
        $token = $data['token'];
        $reset_token = $this->Password_reset_model->get_token($token);
        
        if (!$reset_token) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'This reset link is invalid or has expired. Please request a new password reset link.'
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Token is valid.',
            'email' => $reset_token->email
        ]);
    }
    
    /**
     * Reset password with token
     */
    public function reset_password() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        
        if ($this->input->method() === 'options') {
            exit;
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
        
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('token', 'Token', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Password must be at least 6 characters long.',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }
        
        $token = $data['token'];
        $password = $data['password'];
        
        // Verify token
        $reset_token = $this->Password_reset_model->get_token($token);
        
        if (!$reset_token) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'This reset link is invalid or has expired. Please request a new password reset link.'
            ]);
            return;
        }
        
        // Get user
        $user = $this->User_model->get_user_by_email($reset_token->email);
        
        if (!$user) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'User account not found.'
            ]);
            return;
        }
        
        // Update password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_data = array('password' => $hashed_password);
        
        if ($this->User_model->update_user($user->id, $update_data)) {
            // Mark token as used
            $this->Password_reset_model->mark_as_used($token);
            
            echo json_encode([
                'success' => true,
                'message' => 'Your password has been reset successfully. You can now login with your new password.'
            ]);
        } else {
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'Unable to reset password. Please try again later.'
            ]);
        }
    }
}

