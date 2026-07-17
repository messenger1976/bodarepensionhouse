<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Forgot extends MX_Controller {

    public function __construct() {
        parent::__construct();

        $logged_in = $this->session->userdata('logged_in');
        if ($logged_in) {
            redirect('dashboard/dashboard', 'refresh');
        }
    }

    public function index() {
        $this->render('forgot');
    }

    public function send() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('forgot_error', strip_tags(validation_errors(' ', ' ')));
            redirect('access/forgot', 'refresh');
            return;
        }

        $email = trim($this->input->post('email', TRUE));
        $user = $this->db->get_where('users', array('email' => $email), 1)->row();

        // Always show the same message to avoid account enumeration.
        $neutral = 'If that email is registered, we have sent a password reset link. Please check your inbox and spam folder.';

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $this->db->where('userid', $user->userid);
            $this->db->update('users', array(
                'reset_token' => hash('sha256', $token),
                'reset_expiry' => date('Y-m-d H:i:s', time() + 3600),
            ));

            $siteinfo = $this->getBasicInfo();
            $title = !empty($siteinfo[0]->title) ? $siteinfo[0]->title : 'BODARE & COMMUNITY MPC';
            $logo = !empty($siteinfo[0]->logo) ? $siteinfo[0]->logo : '';
            $reset_url = base_url('access/forgot/reset/' . $token);
            $name = trim((!empty($user->fname) ? $user->fname : '') . ' ' . (!empty($user->lname) ? $user->lname : ''));
            if ($name === '') {
                $name = !empty($user->username) ? $user->username : 'there';
            }

            $subject = 'Reset your password - ' . $title;
            $message = $this->buildResetEmail($title, $logo, $name, $reset_url);

            $this->load->library('coop_mail');
            $this->coop_mail->set_profile('account');
            if (!$this->coop_mail->send($email, $subject, $message)) {
                // Clear token if email failed so the user can retry.
                $this->db->where('userid', $user->userid);
                $this->db->update('users', array(
                    'reset_token' => NULL,
                    'reset_expiry' => NULL,
                ));
                $this->session->set_flashdata('forgot_error', 'We could not send the reset email right now. ' . $this->coop_mail->get_last_error());
                redirect('access/forgot', 'refresh');
                return;
            }
        }

        $this->session->set_flashdata('forgot_success', $neutral);
        redirect('access/forgot', 'refresh');
    }

    public function reset($token = '') {
        $user = $this->findUserByToken($token);
        if (!$user) {
            $this->session->set_flashdata('forgot_error', 'This password reset link is invalid or has expired. Please request a new one.');
            redirect('access/forgot', 'refresh');
            return;
        }

        $data = array(
            'siteinfo' => $this->getBasicInfo(),
            'slider' => $this->getSlider(),
            'token' => $token,
        );
        $this->load->view('header', $data);
        $this->load->view('reset', $data);
        $this->load->view('footer', $data);
    }

    public function update() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('token', 'Token', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|max_length[50]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');

        $token = (string) $this->input->post('token', TRUE);

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('reset_error', strip_tags(validation_errors(' ', ' ')));
            redirect('access/forgot/reset/' . rawurlencode($token), 'refresh');
            return;
        }

        $user = $this->findUserByToken($token);
        if (!$user) {
            $this->session->set_flashdata('forgot_error', 'This password reset link is invalid or has expired. Please request a new one.');
            redirect('access/forgot', 'refresh');
            return;
        }

        $password = (string) $this->input->post('password', FALSE);
        $this->db->where('userid', $user->userid);
        $updated = $this->db->update('users', array(
            'password' => md5($password),
            'reset_token' => NULL,
            'reset_expiry' => NULL,
        ));

        if ($updated) {
            $this->session->set_flashdata('logout_msg', 'Your password has been updated. You can sign in with your new password.');
            redirect('access/login', 'refresh');
            return;
        }

        $this->session->set_flashdata('reset_error', 'Unable to update your password. Please try again.');
        redirect('access/forgot/reset/' . rawurlencode($token), 'refresh');
    }

    private function findUserByToken($token) {
        $token = trim((string) $token);
        if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
            return NULL;
        }

        $this->db->where('reset_token', hash('sha256', $token));
        $this->db->where('reset_expiry >=', date('Y-m-d H:i:s'));
        $query = $this->db->get('users', 1);
        return $query->row();
    }

    private function buildResetEmail($title, $logo, $name, $reset_url) {
        $safe_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safe_url = htmlspecialchars($reset_url, ENT_QUOTES, 'UTF-8');
        $logo_html = '';
        if (!empty($logo)) {
            $logo_src = htmlspecialchars(base_url('images/website/' . $logo), ENT_QUOTES, 'UTF-8');
            $logo_html = '<img src="' . $logo_src . '" alt="' . $safe_title . '" style="max-height:48px;margin-bottom:16px;">';
        }

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
            <td style="background:#36661f;padding:20px 28px;color:#fff;">
              <div style="font-size:18px;font-weight:bold;">' . $safe_title . '</div>
              <div style="font-size:13px;opacity:.9;margin-top:4px;">Password reset request</div>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;">
              ' . $logo_html . '
              <p style="margin:0 0 12px;font-size:15px;">Hi ' . $safe_name . ',</p>
              <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                We received a request to reset your password. Click the button below to create a new password.
                This link will expire in <strong>1 hour</strong>.
              </p>
              <p style="margin:24px 0;" align="center">
                <a href="' . $safe_url . '" style="display:inline-block;background:#36661f;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;font-weight:bold;font-size:15px;">
                  Reset Password
                </a>
              </p>
              <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#555;">
                If the button does not work, copy and paste this link into your browser:
              </p>
              <p style="margin:0 0 18px;font-size:12px;line-height:1.5;word-break:break-all;color:#36661f;">
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

    private function render($view) {
        $data['siteinfo'] = $this->getBasicInfo();
        $data['slider'] = $this->getSlider();
        $this->load->view('header', $data);
        $this->load->view($view, $data);
        $this->load->view('footer', $data);
    }

    private function getBasicInfo() {
        return $this->db->get('websitebasic')->result();
    }

    private function getSlider() {
        $this->db->order_by('serialid', 'asc');
        $this->db->limit(5);
        return $this->db->get('slider')->result();
    }
}
