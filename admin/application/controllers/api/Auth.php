<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    /** OTP validity window (seconds). */
    const OTP_EXPIRY_SECONDS = 900; // 15 minutes

    /** Minimum wait before an OTP can be resent (seconds). */
    const OTP_RESEND_COOLDOWN_SECONDS = 60;

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('User_model');
        $this->load->model('Customer_model');
        $this->load->model('Password_reset_model');
        $this->load->model('Email_verification_model');
        $this->load->library('form_validation');
        $this->load->library('api_auth');
        $this->load->library('activity_log');
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
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
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

        // Look up existing records for this address before creating anything.
        $existing_user = $this->User_model->get_user_by_email($email);
        $existing_customer = $this->Customer_model->get_customer_by_email($email);

        // Only an account that is actually usable (active, verified, or
        // suspended by staff) blocks re-registration. Inactive accounts that
        // were never email-verified are incomplete registrations, not real
        // accounts - they must not produce a false "already registered".
        if ($existing_user && ($existing_user->status !== 'inactive' || (int)$existing_user->email_verified === 1)) {
            $blocked_message = ($existing_user->status === 'suspended')
                ? 'This account is currently suspended. Please contact us for assistance.'
                : 'This email address is already registered. Please use a different email or try logging in instead.';
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => $blocked_message
            ]);
            return;
        }

        // If we get here the email is brand new, or it belongs only to an
        // incomplete earlier attempt (inactive + never verified) and/or an
        // offline customer profile. Decide how to proceed:
        //   - $resuming:       an inactive/unverified user exists - reuse it
        //                      and simply issue a fresh verification code.
        //   - $reuse_customer: no user yet, but the email is already attached
        //                      to an offline/walk-in customer profile - create
        //                      the online account without duplicating the row.
        $resuming = ($existing_user !== null);
        $reuse_customer = (!$existing_user && $existing_customer !== null);

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

        // Branch creation based on whether we are resuming an incomplete
        // registration. Track rows created in THIS request so they can be
        // removed again if the verification email cannot be delivered.
        $created_this_request = array(
            'users' => null,
            'customers' => null
        );

        if ($resuming) {
            // Incomplete account from an earlier attempt: reuse the existing
            // (inactive, never-verified) user and refresh it with the details
            // just submitted - including the password the visitor typed now.
            $user_id = (int)$existing_user->id;
            $this->User_model->update_user($user_id, $user_data);

            // Mirror the customer record too when one does not exist yet.
            // (An existing customer profile - e.g. from a walk-in booking - is
            // left untouched; the account links to it through the shared email.)
            if (!$existing_customer) {
                $new_customer_id = $this->Customer_model->create($customer_data);
                if ($new_customer_id) {
                    $created_this_request['customers'] = (int)$new_customer_id;
                }
            }
        } else {
            // Fresh online account.
            $user_id = $this->User_model->register($user_data);

            if (!$user_id) {
                $this->output->set_status_header(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'We encountered an issue creating your account. Our team has been notified. Please try again in a few moments.'
                ]);
                return;
            }
            $created_this_request['users'] = (int)$user_id;

            if ($reuse_customer) {
                // The email already belongs to an offline/walk-in customer
                // profile added by staff - do not duplicate the profile. The
                // new online account links to it through the shared email.
            } else {
                // Create customer record (for detailed customer management)
                $customer_id = $this->Customer_model->create($customer_data);

                if (!$customer_id) {
                    // Remove the user row that was just created.
                    $this->db->where('id', (int)$user_id);
                    $this->db->delete('users');
                    $created_this_request['users'] = null;
                    $this->output->set_status_header(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'We encountered an issue creating your account. Our team has been notified. Please try again in a few moments.'
                    ]);
                    return;
                }
                $created_this_request['customers'] = (int)$customer_id;
            }
        }

        // Cleanup helper: remove exactly the rows THIS request created, so a
        // failed delivery never leaves an unusable ghost account behind.
        $cleanup_created_rows = function () use (&$created_this_request) {
            if (!empty($created_this_request['customers'])) {
                $this->db->where('id', $created_this_request['customers']);
                $this->db->delete('customers');
                $created_this_request['customers'] = null;
            }
            if (!empty($created_this_request['users'])) {
                $this->db->where('id', $created_this_request['users']);
                $this->db->delete('users');
                $created_this_request['users'] = null;
            }
        };

        $user = $this->User_model->get_user($user_id);
        $name = trim($user->first_name . ' ' . $user->last_name);
        if ($name === '') {
            $name = $email;
        }

        // Issue a 6-digit OTP instead of a clickable link. The code is stored
        // hashed and expires in 15 minutes (standard email-verification flow).
        try {
            $code = (string) random_int(100000, 999999);
        } catch (Exception $e) {
            log_message('error', 'OTP generation failed: ' . $e->getMessage());
            $cleanup_created_rows();
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We encountered an issue creating your account. Please try again in a few moments.'
            ]);
            return;
        }

        $expires_at = date('Y-m-d H:i:s', time() + self::OTP_EXPIRY_SECONDS);
        $code_hash = password_hash($code, PASSWORD_DEFAULT);

        if (!$this->Email_verification_model->create_otp($email, $code_hash, $expires_at)) {
            log_message('error', 'OTP row could not be created for: ' . $email);
            $cleanup_created_rows();
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We could not complete your registration right now. Please try again in a few moments.'
            ]);
            return;
        }

        $title = 'BODARE Pension House';
        $subject = 'Your verification code - ' . $title;
        $message = $this->build_otp_email($title, $name, $code, self::OTP_EXPIRY_SECONDS / 60);

        $this->load->library('coop_mail');
        $this->coop_mail->set_profile('account');

        if (!$this->coop_mail->send($email, $subject, $message)) {
            $smtp_error = $this->coop_mail->get_last_error();
            log_message('error', 'Registration OTP email failed: ' . $smtp_error);
            // Remove the pending code so the user can safely request a new one.
            $this->Email_verification_model->delete_by_email($email);
            // Remove any rows created by THIS request so a failed delivery
            // never leaves an unusable ghost account behind.
            $rows_were_created = !empty($created_this_request['users']) || !empty($created_this_request['customers']);
            $cleanup_created_rows();

            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => $rows_were_created
                    ? 'We could not email the verification code, so no account was created. Please try again in a few minutes or contact support.'
                    : 'We could not email a new verification code right now. Please try again in a few minutes or request a new code from the verification page.'
            ]);
            return;
        }

        $success_message = $resuming
            ? 'An account for this email was already started but not yet activated. We emailed a NEW 6-digit verification code to ' . $this->mask_email($email) . ' - enter it to activate your account.'
            : 'Your account has been created. We emailed a 6-digit verification code to ' . $this->mask_email($email) . ' - enter it to activate your account.';

        if (isset($this->activity_log)) {
            $snap = $user_data;
            unset($snap['password']);
            $this->activity_log->auth_event('register', 'Customer account registered: ' . trim($data['first_name'] . ' ' . $data['last_name']) . ' (' . $email . ')' . ($resuming ? ' [resumed]' : ''), array(
                'status'     => 'success',
                'actor_type' => 'guest',
                'actor_name' => $email,
                'metadata'   => array('user_id' => $user_id, 'resumed' => $resuming),
            ));
        }

        echo json_encode([
            'success' => true,
            'requires_verification' => true,
            'message' => $success_message,
            'email_masked' => $this->mask_email($email),
            'expires_in' => self::OTP_EXPIRY_SECONDS,
            'resend_after' => self::OTP_RESEND_COOLDOWN_SECONDS,
            'verify_url' => $this->frontend_site_root() . '/verify-account.php'
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
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
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
            if (isset($this->activity_log)) {
                $this->activity_log->auth_event('failed_login', 'Customer login failed: no account for ' . $email, array('status' => 'failed', 'severity' => 'warning', 'actor_type' => 'guest', 'actor_name' => $email));
            }
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
            if (isset($this->activity_log)) {
                $this->activity_log->auth_event('failed_login', 'Customer login failed: account not active for ' . $email . ($pending_verification ? ' (pending verification)' : ''), array('status' => 'failed', 'severity' => 'warning', 'actor_type' => 'guest', 'actor_name' => $email));
            }
            $this->output->set_status_header(401);
            echo json_encode([
                'success' => false,
                'requires_activation' => true,
                'message' => $pending_verification
                    ? 'Your account isn\'t activated yet. We emailed you a 6-digit verification code - enter it to activate your account, or request a new code.'
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

            // Issue a bearer token kept in the client's localStorage so the
            // mobile app / WebView stays signed in even if the session cookie
            // is dropped or expired by the OS.
            $remember = isset($data['remember']) ? (bool) $data['remember'] : true;
            $lifetime = $remember
                ? Api_auth::TOKEN_LIFETIME_REMEMBER // 30 days
                : Api_auth::TOKEN_LIFETIME_SHORT;   // 12 hours
            $token = $this->api_auth->issue_token($user->id, $lifetime);

            if ($token === null) {
                log_message('error', 'Login succeeded but auth token could not be created for user ' . $user->id);
                $this->output->set_status_header(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'We could not create a secure sign-in session. Please contact support and mention "auth token table".'
                ]);
                return;
            }

            $actor_name = trim($user->first_name . ' ' . $user->last_name);
            if ($actor_name === '') {
                $actor_name = $user->email;
            }
            if (isset($this->activity_log)) {
                $this->activity_log->auth_event('login', 'Customer logged in: ' . $actor_name . ' (' . $user->email . ')', array(
                    'status'     => 'success',
                    'actor_type' => 'customer',
                    'actor_id'   => $user->id,
                    'actor_name' => $actor_name,
                ));
            }

            echo json_encode([
                'success' => true,
                'message' => 'Welcome back! You have successfully logged in.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email
                ],
                'token' => $token,
                'remember' => $remember,
                'expires_in' => $lifetime
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
                    'requires_activation' => true,
                    'message' => $pending_verification
                        ? 'Your account isn\'t activated yet. We emailed you a 6-digit verification code - enter it to activate your account, or request a new code.'
                        : 'Your account is not active. Please contact support for assistance.'
                ]);
            } else {
                // User exists and is active, but password is wrong
                if (isset($this->activity_log)) {
                    $this->activity_log->auth_event('failed_login', 'Customer login failed: wrong password for ' . $email, array('status' => 'failed', 'severity' => 'warning', 'actor_type' => 'guest', 'actor_name' => $email));
                }
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

        // Capture actor before clearing the session so the audit trail still
        // records who logged out.
        $logout_actor_id = (int) $this->session->userdata('user_id');
        $logout_actor_name = $this->session->userdata('user_name');

        // Revoke the presented bearer token (idempotent).
        $this->api_auth->revoke_current_token();

        $this->session->unset_userdata(['user_logged_in', 'user_id', 'user_email', 'user_name']);
        $this->session->sess_destroy();

        if (isset($this->activity_log)) {
            $this->activity_log->auth_event('logout', 'Customer logged out' . ($logout_actor_name ? ': ' . $logout_actor_name : ''), array('status' => 'success', 'actor_type' => 'customer', 'actor_id' => $logout_actor_id, 'actor_name' => $logout_actor_name));
        }

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

        // Bearer token first, cookie session as fallback (see Api_auth).
        $auth = $this->api_auth->customer();

        if ($auth) {
            $user = $auth['user'];
            echo json_encode([
                'success' => true,
                'logged_in' => true,
                'auth_source' => $auth['auth_source'],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email
                ]
            ]);
            return;
        }

        // No valid token or session (or account is gone/inactive).
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
    }

    /**
     * Forgot password - send reset link
     */
    public function forgot_password() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
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
                if (isset($this->activity_log)) {
                    $this->activity_log->auth_event('forgot_password', 'Customer password reset requested: ' . $email . ' (no active account)', array('status' => 'success', 'actor_type' => 'guest', 'actor_name' => $email, 'metadata' => array('email' => $email, 'sent' => false)));
                }
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

            if (isset($this->activity_log)) {
                $this->activity_log->auth_event('forgot_password', 'Customer password reset requested: ' . $email . ' (reset link sent)', array('status' => 'success', 'actor_type' => 'guest', 'actor_name' => $email, 'metadata' => array('email' => $email, 'sent' => true)));
            }

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
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
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
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
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

            if (isset($this->activity_log)) {
                $this->activity_log->auth_event('reset_password', 'Customer password reset completed for: ' . $reset_token->email, array('status' => 'success', 'actor_type' => 'guest', 'actor_name' => $reset_token->email, 'metadata' => array('email' => $reset_token->email)));
            }

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
     * Resend the account-activation OTP (with a 60s cooldown).
     * POST api/auth/send-activation-otp  { email }
     */
    public function send_activation_otp() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
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

        $email = isset($data['email']) ? trim(strtolower((string) $data['email'])) : '';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please enter a valid email address.'
            ]);
            return;
        }

        $user = $this->User_model->get_user_by_email($email);
        $is_verified = $user && isset($user->email_verified) && (int) $user->email_verified === 1;

        if (!$user || ($user->status === 'active' && $is_verified) || $user->status === 'suspended') {
            // Do not leak whether the address exists for active/suspended accounts.
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'This email is not registered or the account is already active. Please log in instead.'
            ]);
            return;
        }

        // Resend cooldown (one code per 60 seconds).
        $latest = $this->Email_verification_model->get_latest_by_email($email);
        if ($latest && isset($latest->created_at) && $latest->created_at !== '') {
            $elapsed = time() - strtotime($latest->created_at);
            if ($elapsed < self::OTP_RESEND_COOLDOWN_SECONDS) {
                $wait = self::OTP_RESEND_COOLDOWN_SECONDS - max(0, $elapsed);
                $this->output->set_status_header(429);
                echo json_encode([
                    'success' => false,
                    'message' => 'Please wait ' . $wait . ' second' . ($wait === 1 ? '' : 's') . ' before requesting another code.',
                    'resend_after' => $wait
                ]);
                return;
            }
        }

        $name = trim($user->first_name . ' ' . $user->last_name);
        if ($name === '') {
            $name = $email;
        }

        $result = $this->create_and_send_otp($email, $name);
        if (!$result['ok']) {
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => $result['message']
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'A new 6-digit verification code has been sent to ' . $this->mask_email($email) . '.',
            'email_masked' => $this->mask_email($email),
            'expires_in' => self::OTP_EXPIRY_SECONDS,
            'resend_after' => self::OTP_RESEND_COOLDOWN_SECONDS
        ]);
    }

    /**
     * Verify the emailed OTP and activate (and sign in) the account.
     * POST api/auth/verify-otp  { email, code }
     */
    public function verify_otp() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
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

        $email = isset($data['email']) ? trim(strtolower((string) $data['email'])) : '';
        $code = isset($data['code']) ? trim((string) $data['code']) : '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please enter the email address you registered with.'
            ]);
            return;
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please enter the 6-digit code from the email.'
            ]);
            return;
        }

        $user = $this->User_model->get_user_by_email($email);
        if (!$user) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'No account was found for this email address.'
            ]);
            return;
        }

        $is_verified = isset($user->email_verified) && (int) $user->email_verified === 1;
        if ($user->status === 'active' && $is_verified) {
            echo json_encode([
                'success' => true,
                'already_active' => true,
                'message' => 'Your account is already active. Please log in with your email and password.'
            ]);
            return;
        }

        if ($user->status === 'suspended') {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'This account is not active. Please contact support for assistance.'
            ]);
            return;
        }

        $verification = $this->Email_verification_model->get_active_by_email($email);
        if (!$verification) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'That code is invalid or has expired. Please request a new code.'
            ]);
            return;
        }

        if (!password_verify($code, $verification->token)) {
            $attempts = $this->Email_verification_model->record_failed_attempt($email);

            if ($attempts >= Email_verification_model::MAX_ATTEMPTS) {
                $this->Email_verification_model->lock_by_email($email);
                $this->output->set_status_header(400);
                echo json_encode([
                    'success' => false,
                    'code_locked' => true,
                    'message' => 'Too many incorrect attempts. This code is no longer valid - please request a new code.'
                ]);
                return;
            }

            $remaining = Email_verification_model::MAX_ATTEMPTS - $attempts;
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'That code is incorrect. ' . $remaining . ' attempt' . ($remaining === 1 ? '' : 's') . ' remaining.',
                'attempts_remaining' => $remaining
            ]);
            return;
        }

        // Code correct -> activate the account.
        $this->db->trans_start();

        $this->User_model->update_user($user->id, array(
            'email_verified' => 1,
            'status' => 'active'
        ));

        $customer = $this->Customer_model->get_customer_by_email($email);
        if ($customer) {
            $this->Customer_model->update($customer->id, array('status' => 'active'));
        }

        $this->Email_verification_model->mark_as_used_by_email($email);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We could not activate your account right now. Please try again later.'
            ]);
            return;
        }

        // Auto sign-in after activation (smooth real-world onboarding).
        $fresh = $this->User_model->get_user($user->id);
        $this->session->set_userdata([
            'user_logged_in' => true,
            'user_id' => $fresh->id,
            'user_email' => $fresh->email,
            'user_name' => $fresh->first_name . ' ' . $fresh->last_name
        ]);

        $token = $this->api_auth->issue_token($fresh->id, Api_auth::TOKEN_LIFETIME_REMEMBER);
        if ($token === null) {
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'Your account was activated but we could not start a sign-in session. Please log in manually.'
            ]);
            return;
        }

        if (isset($this->activity_log)) {
            $this->activity_log->auth_event('activate', 'Customer account activated via OTP: ' . $fresh->email, array('status' => 'success', 'actor_type' => 'customer', 'actor_id' => $fresh->id, 'actor_name' => trim($fresh->first_name . ' ' . $fresh->last_name)));
        }

        echo json_encode([
            'success' => true,
            'message' => 'Your account has been activated. Welcome!',
            'user' => [
                'id' => $fresh->id,
                'name' => $fresh->first_name . ' ' . $fresh->last_name,
                'email' => $fresh->email
            ],
            'token' => $token,
            'remember' => true,
            'expires_in' => Api_auth::TOKEN_LIFETIME_REMEMBER
        ]);
    }

    /**
     * Activate account from email confirmation link
     */
    public function activate_account() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
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

    /**
     * Generate a fresh 6-digit OTP, store it (hashed) and email it.
     * Existing pending codes for the address are replaced.
     *
     * @param string $email
     * @param string $name
     * @return array ['ok' => bool, 'message' => string]
     */
    protected function create_and_send_otp($email, $name) {
        try {
            $code = (string) random_int(100000, 999999);
        } catch (Exception $e) {
            log_message('error', 'OTP generation failed: ' . $e->getMessage());
            return array('ok' => false, 'message' => 'We could not generate a verification code. Please try again.');
        } catch (Error $e) {
            log_message('error', 'OTP generation failed: ' . $e->getMessage());
            return array('ok' => false, 'message' => 'We could not generate a verification code. Please try again.');
        }

        $expires_at = date('Y-m-d H:i:s', time() + self::OTP_EXPIRY_SECONDS);
        $code_hash = password_hash($code, PASSWORD_DEFAULT);

        if (!$this->Email_verification_model->create_otp($email, $code_hash, $expires_at)) {
            return array('ok' => false, 'message' => 'We could not store the verification code. Please try again.');
        }

        $title = 'BODARE Pension House';
        $subject = 'Your verification code - ' . $title;
        $message = $this->build_otp_email($title, $name, $code, self::OTP_EXPIRY_SECONDS / 60);

        $this->load->library('coop_mail');
        $this->coop_mail->set_profile('account');

        if (!$this->coop_mail->send($email, $subject, $message)) {
            $smtp_error = $this->coop_mail->get_last_error();
            log_message('error', 'OTP email failed for ' . $email . ': ' . $smtp_error);
            $this->Email_verification_model->delete_by_email($email);
            return array('ok' => false, 'message' => 'We could not send the verification code right now. Please try again later.');
        }

        return array('ok' => true, 'message' => '');
    }

    /**
     * Mask an email address for display, e.g. juan@example.com -> ju**@example.com.
     *
     * @param string $email
     * @return string
     */
    protected function mask_email($email) {
        $email = trim((string) $email);
        $at = strpos($email, '@');
        if ($at === false) {
            return '***';
        }
        $local = substr($email, 0, $at);
        $domain = substr($email, $at + 1);

        if ($local === '') {
            return '***@' . $domain;
        }
        if (strlen($local) === 1) {
            $masked = $local . '*';
        } elseif (strlen($local) === 2) {
            $masked = $local . '**';
        } else {
            $masked = substr($local, 0, 2) . str_repeat('*', strlen($local) - 2);
        }
        return $masked . '@' . $domain;
    }

    /**
     * HTML email carrying the 6-digit verification code.
     */
    protected function build_otp_email($title, $name, $code, $minutes) {
        $safe_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safe_code = htmlspecialchars((string) $code, ENT_QUOTES, 'UTF-8');
        $minutes = max(1, (int) $minutes);

        $logo_src = htmlspecialchars($this->frontend_site_root() . '/img/logo.png', ENT_QUOTES, 'UTF-8');
        $accent = '#b2945b';

        return '
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Your Verification Code</title></head>
<body style="margin:0;padding:0;background:#f5f6fa;font-family:Arial,Helvetica,sans-serif;color:#333;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f6fa;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:12px;overflow:hidden;max-width:560px;width:100%;">
          <tr>
            <td style="background:' . $accent . ';padding:20px 28px;color:#fff;">
              <div style="font-size:18px;font-weight:bold;">' . $safe_title . '</div>
              <div style="font-size:13px;opacity:.9;margin-top:4px;">Account verification</div>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;">
              <img src="' . $logo_src . '" alt="' . $safe_title . '" style="max-height:48px;margin-bottom:16px;">
              <p style="margin:0 0 12px;font-size:15px;">Hi ' . $safe_name . ',</p>
              <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                Enter the 6-digit code below to activate your BODARE Pension House account.
                This code will expire in <strong>' . $minutes . ' minutes</strong>.
              </p>
              <p style="margin:20px 0;" align="center">
                <span style="display:inline-block;background:#f5f6fa;border:1px dashed ' . $accent . ';color:#1a2238;letter-spacing:8px;font-size:28px;font-weight:bold;padding:14px 22px;border-radius:8px;">' . $safe_code . '</span>
              </p>
              <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#555;">
                If you did not create an account with ' . $safe_title . ', you can safely ignore this email.
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

