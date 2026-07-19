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
                    // IMPORTANT: Use the exact admin ID from the database query result
                    $admin_id = (int)$admin->id;
                    $admin_username = $admin->username;
                    $admin_name = $admin->name ? $admin->name : $admin->username;
                    
                    log_message('debug', 'Login attempt for admin_id: ' . $admin_id . ' (username: ' . $admin_username . ')');
                    
                    // Clear any existing admin session data first
                    $this->session->unset_userdata(array('admin_id', 'admin_username', 'admin_name', 'admin_logged_in'));
                    
                    // Regenerate session ID to prevent session fixation and ensure clean session
                    // sess_regenerate with TRUE parameter destroys old session data
                    $this->session->sess_regenerate(TRUE);
                    
                    // Set new session data with the logged-in admin's information
                    $session_data = array(
                        'admin_id' => $admin_id,
                        'admin_username' => $admin_username,
                        'admin_name' => $admin_name,
                        'admin_logged_in' => TRUE
                    );
                    
                    // Set session data
                    $this->session->set_userdata($session_data);
                    
                    // Force session write by accessing it
                    $test_id = $this->session->userdata('admin_id');
                    
                    // Verify session was set correctly
                    if ($test_id != $admin_id) {
                        log_message('error', 'CRITICAL: Session mismatch after login! Expected admin_id ' . $admin_id . ' but got ' . var_export($test_id, true));
                        $this->session->set_flashdata('error', 'Session error occurred. Please try logging in again.');
                        redirect('login');
                        return;
                    }
                    
                    log_message('debug', 'Login successful - Session verified for admin_id: ' . $admin_id . ' (username: ' . $admin_username . ', name: ' . $admin_name . ')');
                    
                    redirect('dashboard');
                } else {
                    $this->session->set_flashdata('error', 'Invalid username or password');
                }
            }
        }
        
        $data['title'] = 'Admin Login';
        $this->load->view('admin/auth/login', $data);
    }
    
    /**
     * Forgot password - request a reset link by email
     */
    public function forgot_password() {
        if ($this->session->userdata('admin_logged_in')) {
            redirect('dashboard');
        }
        
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
            
            if ($this->form_validation->run() == TRUE) {
                $email = trim(strtolower($this->input->post('email')));
                
                // Neutral response either way to avoid email enumeration
                $neutral = 'If an account exists with this email, a password reset link has been sent. Please check your inbox and spam folder.';
                
                $admin = $this->Admin_model->get_by_email($email);
                
                if ($admin && $admin->status === 'active') {
                    $this->load->model('Admin_token_model');
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    $token = $this->Admin_token_model->create_token($admin->id, Admin_token_model::TYPE_PASSWORD_RESET, $expires_at);
                    
                    if ($token) {
                        $reset_url = site_url('reset-password/' . $token);
                        $name = $admin->name ? $admin->name : $admin->username;
                        $subject = 'Reset your admin password - BODARE Pension House';
                        $message = $this->build_token_email(
                            'Password reset request',
                            $name,
                            'We received a request to reset your admin panel password. Click the button below to create a new password. This link will expire in <strong>1 hour</strong>.',
                            'Reset Password',
                            $reset_url,
                            'If you did not request a password reset, you can safely ignore this email. Your password will stay the same.'
                        );
                        
                        if (!$this->send_email($admin->email, $subject, $message)) {
                            $this->Admin_token_model->delete_for_admin($admin->id, Admin_token_model::TYPE_PASSWORD_RESET);
                            $this->session->set_flashdata('error', 'We could not send the reset email right now. Please try again later.');
                            redirect('forgot-password');
                        }
                    }
                }
                
                $this->session->set_flashdata('success', $neutral);
                redirect('forgot-password');
            }
        }
        
        $data['title'] = 'Forgot Password';
        $this->load->view('admin/auth/forgot_password', $data);
    }
    
    /**
     * Reset password - landing page from the emailed link
     */
    public function reset_password($token = '') {
        if ($this->session->userdata('admin_logged_in')) {
            redirect('dashboard');
        }
        
        $token = trim($token);
        $this->load->model('Admin_token_model');
        
        $token_row = $token !== '' ? $this->Admin_token_model->get_valid_token($token, Admin_token_model::TYPE_PASSWORD_RESET) : null;
        
        if (!$token_row) {
            $this->session->set_flashdata('error', 'This reset link is invalid or has expired. Please request a new one.');
            redirect('forgot-password');
        }
        
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('password', 'New Password', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
            
            if ($this->form_validation->run() == TRUE) {
                $admin = $this->Admin_model->get_admin($token_row->admin_id);
                
                if ($admin && $this->Admin_model->update_password($admin->id, $this->input->post('password'))) {
                    $this->Admin_token_model->mark_as_used($token);
                    $this->session->set_flashdata('success', 'Your password has been reset successfully. You can now login with your new password.');
                    redirect('login');
                } else {
                    $this->session->set_flashdata('error', 'Unable to reset password. Please try again.');
                    redirect('reset-password/' . $token);
                }
            }
        }
        
        $data['title'] = 'Reset Password';
        $data['token'] = $token;
        $this->load->view('admin/auth/reset_password', $data);
    }
    
    /**
     * Create account - self-registration for admin panel (activated as Staff)
     */
    public function register() {
        if ($this->session->userdata('admin_logged_in')) {
            redirect('dashboard');
        }
        
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('name', 'Full Name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('username', 'Username', 'required|trim|min_length[3]|max_length[50]|alpha_dash');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|max_length[100]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
            
            if ($this->form_validation->run() == TRUE) {
                $username = trim($this->input->post('username'));
                $email = trim(strtolower($this->input->post('email')));
                
                if ($this->Admin_model->username_exists($username)) {
                    $this->session->set_flashdata('error', 'This username is already taken. Please choose another one.');
                } elseif ($this->Admin_model->email_exists($email)) {
                    $this->session->set_flashdata('error', 'This email address is already registered.');
                } else {
                    // Create the account as inactive until the email is confirmed
                    $admin_id = $this->Admin_model->create(array(
                        'username' => $username,
                        'password' => $this->input->post('password'),
                        'name' => trim($this->input->post('name')),
                        'email' => $email,
                        'status' => 'inactive'
                    ));
                    
                    if ($admin_id) {
                        $this->load->model('Admin_token_model');
                        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
                        $token = $this->Admin_token_model->create_token($admin_id, Admin_token_model::TYPE_ACTIVATION, $expires_at);
                        
                        $activate_url = site_url('activate-account/' . $token);
                        $subject = 'Activate your admin account - BODARE Pension House';
                        $message = $this->build_token_email(
                            'Activate your account',
                            trim($this->input->post('name')),
                            'Thanks for creating an admin panel account. Please confirm your email address to activate your account. Your account will be activated with the <strong>Staff</strong> role. This link will expire in <strong>24 hours</strong>.',
                            'Activate Account',
                            $activate_url,
                            'If you did not create this account, you can safely ignore this email.'
                        );
                        
                        if ($token && $this->send_email($email, $subject, $message)) {
                            $this->session->set_flashdata('success', 'Your account has been created. Please check your email and click the activation link to activate your account before logging in.');
                            redirect('login');
                        } else {
                            // Roll back the account so the user can retry registration
                            $this->Admin_model->delete($admin_id);
                            $this->session->set_flashdata('error', 'We could not send the activation email right now. Please try again later.');
                        }
                    } else {
                        $this->session->set_flashdata('error', 'Unable to create your account. Please try again.');
                    }
                }
            }
        }
        
        $data['title'] = 'Create Account';
        $this->load->view('admin/auth/register', $data);
    }
    
    /**
     * Activate account - landing page from the emailed activation link.
     * Activates the account and assigns the Staff role (via the Staff group).
     */
    public function activate($token = '') {
        $token = trim($token);
        $this->load->model('Admin_token_model');
        
        $data['title'] = 'Account Activation';
        $data['success'] = false;
        
        $token_row = $token !== '' ? $this->Admin_token_model->get_valid_token($token, Admin_token_model::TYPE_ACTIVATION) : null;
        
        if (!$token_row) {
            $data['message'] = 'This activation link is invalid or has expired. Please register again or contact an administrator.';
            $this->load->view('admin/auth/activation_result', $data);
            return;
        }
        
        $admin = $this->Admin_model->get_admin($token_row->admin_id);
        
        if (!$admin) {
            $data['message'] = 'The account for this activation link no longer exists.';
            $this->load->view('admin/auth/activation_result', $data);
            return;
        }
        
        $this->db->trans_start();
        
        $this->Admin_model->update($admin->id, array('status' => 'active'));
        
        // Assign the Staff role via the Staff group
        $staff_group_id = $this->Admin_model->get_staff_group_id();
        if ($staff_group_id) {
            $this->Admin_model->add_to_group($admin->id, $staff_group_id);
        } else {
            log_message('error', 'Admin activation: no Staff group found for admin_id ' . $admin->id);
        }
        
        $this->Admin_token_model->mark_as_used($token);
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            $data['message'] = 'Unable to activate your account right now. Please try again later.';
        } else {
            $data['success'] = true;
            $data['message'] = 'Your account has been activated with the Staff role. You can now log in.';
        }
        
        $this->load->view('admin/auth/activation_result', $data);
    }
    
    /**
     * Send an email using the account mailer profile.
     */
    protected function send_email($to, $subject, $message) {
        $this->load->library('coop_mail');
        $this->coop_mail->set_profile('account');
        
        if (!$this->coop_mail->send($to, $subject, $message)) {
            log_message('error', 'Admin auth email failed: ' . $this->coop_mail->get_last_error());
            return false;
        }
        return true;
    }
    
    /**
     * Shared HTML template for token emails (reset / activation).
     */
    protected function build_token_email($heading, $name, $intro_html, $button_label, $url, $footer_note) {
        $title = 'BODARE Pension House';
        $safe_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safe_heading = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
        $safe_name = htmlspecialchars($name !== '' ? $name : 'there', ENT_QUOTES, 'UTF-8');
        $safe_label = htmlspecialchars($button_label, ENT_QUOTES, 'UTF-8');
        $safe_url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $safe_footer = htmlspecialchars($footer_note, ENT_QUOTES, 'UTF-8');
        $accent = '#b2945b';
        
        return '
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>' . $safe_heading . '</title></head>
<body style="margin:0;padding:0;background:#f5f6fa;font-family:Arial,Helvetica,sans-serif;color:#333;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f6fa;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:12px;overflow:hidden;max-width:560px;width:100%;">
          <tr>
            <td style="background:' . $accent . ';padding:20px 28px;color:#fff;">
              <div style="font-size:18px;font-weight:bold;">' . $safe_title . ' - Admin Panel</div>
              <div style="font-size:13px;opacity:.9;margin-top:4px;">' . $safe_heading . '</div>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;">
              <p style="margin:0 0 12px;font-size:15px;">Hi ' . $safe_name . ',</p>
              <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">' . $intro_html . '</p>
              <p style="margin:24px 0;" align="center">
                <a href="' . $safe_url . '" style="display:inline-block;background:' . $accent . ';color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;font-weight:bold;font-size:15px;">
                  ' . $safe_label . '
                </a>
              </p>
              <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#555;">
                If the button does not work, copy and paste this link into your browser:
              </p>
              <p style="margin:0 0 18px;font-size:12px;line-height:1.5;word-break:break-all;color:' . $accent . ';">
                ' . $safe_url . '
              </p>
              <p style="margin:0;font-size:13px;line-height:1.6;color:#777;">' . $safe_footer . '</p>
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
    
    public function logout() {
        // Get cookie settings from config
        $cookie_name = $this->config->item('sess_cookie_name') ?: 'bodare_admin_session';
        $cookie_path = $this->config->item('cookie_path') ?: '/';
        $cookie_domain = $this->config->item('cookie_domain') ?: '';
        // Get cookie_secure from config (auto-detects HTTPS)
        $cookie_secure = $this->config->item('cookie_secure');
        if ($cookie_secure === null || $cookie_secure === false) {
            // Auto-detect HTTPS if not set
            $cookie_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || 
                            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        }
        $cookie_httponly = $this->config->item('cookie_httponly') !== false ? true : false;
        
        // Unset all session data first
        $this->session->unset_userdata(array(
            'admin_id',
            'admin_username',
            'admin_name',
            'admin_logged_in'
        ));
        
        // Delete the session cookie explicitly by setting it to expire in the past
        // Try multiple variations to ensure it's deleted in all scenarios
        if ($cookie_domain) {
            setcookie($cookie_name, '', time() - 3600, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);
        } else {
            // Try without domain
            setcookie($cookie_name, '', time() - 3600, $cookie_path, '', $cookie_secure, $cookie_httponly);
            // Also try with empty domain string explicitly
            setcookie($cookie_name, '', time() - 3600, $cookie_path, false, $cookie_secure, $cookie_httponly);
        }
        
        // Also unset from $_COOKIE superglobal
        if (isset($_COOKIE[$cookie_name])) {
            unset($_COOKIE[$cookie_name]);
        }
        
        // Destroy the session (this also clears all session data)
        $this->session->sess_destroy();
        
        redirect('login');
    }
}

