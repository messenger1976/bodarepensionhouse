<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('User_model');
        $this->load->model('Customer_model');
        $this->load->model('Password_reset_model');
        $this->load->model('Email_verification_model');
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
        
        // Prepare user data (for authentication) — inactive until email is confirmed
        $user_data = array(
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => $email, // Use normalized email
            'phone' => trim($data['phone']),
            'address' => trim($data['address']),
            'password' => $data['password'],
            'email_verified' => 0,
            'status' => 'inactive'
        );
        
        // Prepare customer data (for detailed customer records)
        $customer_data = array(
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => $email,
            'phone' => trim($data['phone']),
            'address' => trim($data['address']),
            'city' => isset($data['city']) ? trim($data['city']) : null,
            'barangay' => isset($data['barangay']) ? trim($data['barangay']) : null,
            'province' => isset($data['province']) ? trim($data['province']) : null,
            'postal_code' => isset($data['postal_code']) ? trim($data['postal_code']) : null,
            'country' => isset($data['country']) && !empty($data['country']) ? trim($data['country']) : 'Philippines',
            'date_of_birth' => isset($data['date_of_birth']) && !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            'gender' => isset($data['gender']) && !empty($data['gender']) ? $data['gender'] : null,
            'nationality' => isset($data['nationality']) ? trim($data['nationality']) : null,
            'id_type' => isset($data['id_type']) && !empty($data['id_type']) ? $data['id_type'] : null,
            'id_number' => isset($data['id_number']) ? trim($data['id_number']) : null,
            'status' => 'inactive'
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

        $user = $this->User_model->get_user($user_id);
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

        if (!$this->Email_verification_model->create_token($email, $token, $expires_at)) {
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'Your account was created, but we could not prepare the confirmation email. Please contact support.'
            ]);
            return;
        }

        $activate_url = $this->build_frontend_activate_url($token);
        $name = trim($user->first_name . ' ' . $user->last_name);
        if ($name === '') {
            $name = $email;
        }

        $title = 'BODARE Pension House';
        $subject = 'Confirm your email - ' . $title;
        $message = $this->build_activation_email($title, $name, $activate_url);

        $this->load->library('coop_mail');
        $this->coop_mail->set_profile('account');

        if (!$this->coop_mail->send($email, $subject, $message)) {
            $smtp_error = $this->coop_mail->get_last_error();
            log_message('error', 'Registration confirmation email failed: ' . $smtp_error);

            $response = [
                'success' => false,
                'message' => 'Your account was created, but we could not send the confirmation email. Please try again later or contact support.'
            ];

            $this->output->set_status_header(500);
            echo json_encode($response);
            return;
        }

        echo json_encode([
            'success' => true,
            'requires_verification' => true,
            'message' => 'Your account has been created. Please check your email and click the confirmation link to activate your account before logging in.'
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
        
        // Check if user is active / email-confirmed
        if ($user_exists->status != 'active') {
            $pending_verification = isset($user_exists->email_verified) && (int) $user_exists->email_verified === 0;
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'message' => $pending_verification
                    ? 'Please confirm your email first. Check your inbox for the activation link we sent when you registered.'
                    : 'Your account is not active. Please contact support for assistance.'
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
                $pending_verification = isset($user_exists->email_verified) && (int) $user_exists->email_verified === 0;
                $this->output->set_status_header(401);
                echo json_encode([
                    'success' => false,
                    'message' => $pending_verification
                        ? 'Please confirm your email first. Check your inbox for the activation link we sent when you registered.'
                        : 'Your account is not active. Please contact support for assistance.'
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
            
            if ($user && (!isset($user->status) || $user->status === 'active')) {
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
                // Account missing/inactive — clear server session so client can logout account
                $this->session->unset_userdata(['user_logged_in', 'user_id', 'user_email', 'user_name']);
                echo json_encode([
                    'success' => true,
                    'logged_in' => false,
                    'message' => 'User session is no longer valid'
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

        // Same message whether or not the account exists (avoid email enumeration).
        $neutral_message = 'If an account exists with this email, a password reset link has been sent. Please check your inbox and spam folder.';

        try {
            // Look in the login accounts first, then fall back to customer records
            // (guests created from bookings may not have a users row yet).
            $account = $this->User_model->get_user_by_email($email);
            if (!$account || (isset($account->status) && $account->status !== 'active')) {
                $account = $this->Customer_model->get_customer_by_email($email);
            }

            if (!$account || (isset($account->status) && $account->status !== 'active')) {
                echo json_encode([
                    'success' => true,
                    'message' => $neutral_message
                ]);
                return;
            }

            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            if (!$this->Password_reset_model->create_token($email, $token, $expires_at)) {
                $this->output->set_status_header(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unable to process your request. Please try again later.'
                ]);
                return;
            }

            $reset_url = $this->build_frontend_reset_url($token);
            $name = trim(
                (!empty($account->first_name) ? $account->first_name : '') . ' ' .
                (!empty($account->last_name) ? $account->last_name : '')
            );
            if ($name === '') {
                $name = !empty($account->email) ? $account->email : 'there';
            }

            $title = 'BODARE Pension House';
            $subject = 'Reset your password - ' . $title;
            $message = $this->build_reset_email($title, $name, $reset_url);

            $this->load->library('coop_mail');
            $this->coop_mail->set_profile('account');

            if (!$this->coop_mail->send($email, $subject, $message)) {
                $smtp_error = $this->coop_mail->get_last_error();
                log_message('error', 'Password reset email failed for account mailer: ' . $smtp_error);

                $response = [
                    'success' => false,
                    'message' => 'We could not send the reset email right now. Please try again later.'
                ];

                $this->Password_reset_model->delete_by_email($email);
                $this->output->set_status_header(500);
                echo json_encode($response);
                return;
            }

            log_message('info', 'Password reset email sent via account mailer.');

            echo json_encode([
                'success' => true,
                'message' => 'If an account exists with this email, a password reset link has been sent. Please check your inbox and spam/junk folder.'
            ]);
        } catch (Exception $e) {
            log_message('error', 'Forgot password failed: ' . $e->getMessage());
            if (isset($email)) {
                $this->Password_reset_model->delete_by_email($email);
            }
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We could not process your password reset request right now. Please try again later.'
            ]);
        } catch (Error $e) {
            log_message('error', 'Forgot password failed: ' . $e->getMessage());
            if (isset($email)) {
                $this->Password_reset_model->delete_by_email($email);
            }
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We could not process your password reset request right now. Please try again later.'
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
        
        // Get user login account
        $user = $this->User_model->get_user_by_email($reset_token->email);

        if ($user) {
            $saved = $this->User_model->update_user($user->id, array('password' => $password));
        } else {
            // Customer-only record (e.g. created from a booking): create the login account now.
            $customer = $this->Customer_model->get_customer_by_email($reset_token->email);

            if (!$customer) {
                $this->output->set_status_header(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'User account not found.'
                ]);
                return;
            }

            $saved = (bool) $this->User_model->register(array(
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => strtolower(trim($customer->email)),
                'phone' => $customer->phone,
                'address' => $customer->address,
                'password' => $password,
                'email_verified' => 1,
                'status' => 'active'
            ));
        }

        if ($saved) {
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

    /**
     * Activate account from email confirmation link
     */
    public function activate_account() {
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

        $token = isset($data['token']) ? trim($data['token']) : '';
        if ($token === '') {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid or missing activation link. Please use the link from your confirmation email.'
            ]);
            return;
        }

        $verification = $this->Email_verification_model->get_token($token);
        if (!$verification) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'This activation link is invalid or has expired. Please register again or contact support.'
            ]);
            return;
        }

        $user = $this->User_model->get_user_by_email($verification->email);
        if (!$user) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Account not found for this activation link.'
            ]);
            return;
        }

        $this->db->trans_start();

        $this->User_model->update_user($user->id, array(
            'email_verified' => 1,
            'status' => 'active'
        ));

        $customer = $this->Customer_model->get_customer_by_email($verification->email);
        if ($customer) {
            $this->Customer_model->update($customer->id, array('status' => 'active'));
        }

        $this->Email_verification_model->mark_as_used($token);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'Unable to activate your account right now. Please try again later.'
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Your email has been confirmed. You can now log in to your account.'
        ]);
    }

    /**
     * Public site root (parent of /admin).
     */
    protected function frontend_site_root() {
        $this->load->helper('url');
        return rtrim(preg_replace('#/admin/?$#', '', rtrim(base_url(), '/')), '/');
    }

    /**
     * Build the public-site reset URL (outside /admin).
     */
    protected function build_frontend_reset_url($token) {
        return $this->frontend_site_root() . '/reset-password.php?token=' . rawurlencode($token);
    }

    /**
     * Build the public-site account activation URL.
     */
    protected function build_frontend_activate_url($token) {
        return $this->frontend_site_root() . '/activate-account.php?token=' . rawurlencode($token);
    }

    /**
     * HTML email for password reset (same pattern as the main website Forgot flow).
     */
    protected function build_reset_email($title, $name, $reset_url) {
        $safe_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safe_url = htmlspecialchars($reset_url, ENT_QUOTES, 'UTF-8');

        $logo_src = htmlspecialchars($this->frontend_site_root() . '/img/logo.png', ENT_QUOTES, 'UTF-8');
        $accent = '#b2945b';

        return '
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Reset Password</title></head>
<body style="margin:0;padding:0;background:#f5f6fa;font-family:Arial,Helvetica,sans-serif;color:#333;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f6fa;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:12px;overflow:hidden;max-width:560px;width:100%;">
          <tr>
            <td style="background:' . $accent . ';padding:20px 28px;color:#fff;">
              <div style="font-size:18px;font-weight:bold;">' . $safe_title . '</div>
              <div style="font-size:13px;opacity:.9;margin-top:4px;">Password reset request</div>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;">
              <img src="' . $logo_src . '" alt="' . $safe_title . '" style="max-height:48px;margin-bottom:16px;">
              <p style="margin:0 0 12px;font-size:15px;">Hi ' . $safe_name . ',</p>
              <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                We received a request to reset your password. Click the button below to create a new password.
                This link will expire in <strong>1 hour</strong>.
              </p>
              <p style="margin:24px 0;" align="center">
                <a href="' . $safe_url . '" style="display:inline-block;background:' . $accent . ';color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;font-weight:bold;font-size:15px;">
                  Reset Password
                </a>
              </p>
              <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#555;">
                If the button does not work, copy and paste this link into your browser:
              </p>
              <p style="margin:0 0 18px;font-size:12px;line-height:1.5;word-break:break-all;color:' . $accent . ';">
                ' . $safe_url . '
              </p>
              <p style="margin:0;font-size:13px;line-height:1.6;color:#777;">
                If you did not request a password reset, you can safely ignore this email. Your password will stay the same.
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding:16px 28px;background:#f8f9fb;font-size:12px;color:#888;">
              &copy; ' . date('Y') . ' ' . $safe_title . '
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';
    }

    /**
     * HTML email for account activation / email confirmation.
     */
    protected function build_activation_email($title, $name, $activate_url) {
        $safe_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safe_url = htmlspecialchars($activate_url, ENT_QUOTES, 'UTF-8');

        $logo_src = htmlspecialchars($this->frontend_site_root() . '/img/logo.png', ENT_QUOTES, 'UTF-8');
        $accent = '#b2945b';

        return '
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Confirm Your Email</title></head>
<body style="margin:0;padding:0;background:#f5f6fa;font-family:Arial,Helvetica,sans-serif;color:#333;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f6fa;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:12px;overflow:hidden;max-width:560px;width:100%;">
          <tr>
            <td style="background:' . $accent . ';padding:20px 28px;color:#fff;">
              <div style="font-size:18px;font-weight:bold;">' . $safe_title . '</div>
              <div style="font-size:13px;opacity:.9;margin-top:4px;">Confirm your email address</div>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;">
              <img src="' . $logo_src . '" alt="' . $safe_title . '" style="max-height:48px;margin-bottom:16px;">
              <p style="margin:0 0 12px;font-size:15px;">Hi ' . $safe_name . ',</p>
              <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                Thanks for registering with ' . $safe_title . '. Please confirm your email address to activate your account.
                This link will expire in <strong>24 hours</strong>.
              </p>
              <p style="margin:24px 0;" align="center">
                <a href="' . $safe_url . '" style="display:inline-block;background:' . $accent . ';color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;font-weight:bold;font-size:15px;">
                  Activate Account
                </a>
              </p>
              <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#555;">
                If the button does not work, copy and paste this link into your browser:
              </p>
              <p style="margin:0 0 18px;font-size:12px;line-height:1.5;word-break:break-all;color:' . $accent . ';">
                ' . $safe_url . '
              </p>
              <p style="margin:0;font-size:13px;line-height:1.6;color:#777;">
                If you did not create an account, you can safely ignore this email.
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding:16px 28px;background:#f8f9fb;font-size:12px;color:#888;">
              &copy; ' . date('Y') . ' ' . $safe_title . '
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';
    }
}

