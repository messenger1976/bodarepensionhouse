<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inquiry extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->library('form_security');
        $this->load->database();
        $this->load->helper('url');
        header('Content-Type: application/json');
    }
    
    /**
     * Issue CSRF + public security bootstrap for the contact form.
     */
    public function csrf() {
        ob_start();
        $this->form_security->apply_cors_headers();

        if ($this->input->method() === 'options') {
            ob_end_clean();
            exit;
        }

        if ($this->input->method() !== 'get') {
            $this->output->set_status_header(405);
            echo json_encode(array('success' => FALSE, 'message' => 'Method not allowed'));
            return;
        }

        $bootstrap = $this->form_security->public_bootstrap();
        echo json_encode(array(
            'success' => TRUE,
            'csrf_token' => $bootstrap['csrf_token'],
            'honeypot_field' => $bootstrap['honeypot_field'],
            'recaptcha_enabled' => $bootstrap['recaptcha_enabled'],
            'recaptcha_site_key' => $bootstrap['recaptcha_site_key'],
            'recaptcha_action' => $bootstrap['recaptcha_action'],
        ));
        ob_end_flush();
    }

    /**
     * Submit an inquiry (public Contact Us + logged-in customer).
     * Stores into inquiry table first, then sends best-effort emails.
     */
    public function submit() {
        ob_start();
        
        $this->form_security->apply_cors_headers();
        
        if ($this->input->method() === 'options') {
            ob_end_clean();
            exit;
        }
        
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(array('success' => FALSE, 'message' => 'Method not allowed'));
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), TRUE);
        if (!$data) {
            $data = $this->input->post();
        }
        if (!is_array($data)) {
            $data = array();
        }

        // 1) Honeypot — bots that fill hidden fields are rejected silently.
        if (!$this->form_security->check_honeypot($data)) {
            $this->form_security->log_event('inquiry_rejected_honeypot', array(
                'ip' => $this->input->ip_address(),
            ));
            echo json_encode(array(
                'success' => TRUE,
                'message' => 'Your inquiry has been sent successfully! We will get back to you soon.',
            ));
            return;
        }

        // 2) Rate limiting / throttling by IP.
        $rate = $this->form_security->check_rate_limit();
        if (empty($rate['ok'])) {
            $retry = isset($rate['retry_after']) ? (int) $rate['retry_after'] : 900;
            header('Retry-After: ' . $retry);
            $this->output->set_status_header(429);
            echo json_encode(array(
                'success' => FALSE,
                'message' => isset($rate['message']) ? $rate['message'] : 'Too many submissions. Please try again later.',
                'retry_after' => $retry,
            ));
            return;
        }

        // 3) CSRF token (body or header).
        $csrfToken = '';
        if (!empty($data['csrf_token']) && is_string($data['csrf_token'])) {
            $csrfToken = $data['csrf_token'];
        } elseif (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $csrfToken = (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        $csrf = $this->form_security->verify_csrf($csrfToken);
        if (empty($csrf['ok'])) {
            $this->output->set_status_header(403);
            echo json_encode(array(
                'success' => FALSE,
                'message' => isset($csrf['message']) ? $csrf['message'] : 'Invalid security token.',
            ));
            return;
        }

        // 4) reCAPTCHA v3 (when configured).
        $recaptchaToken = isset($data['recaptcha_token']) ? $data['recaptcha_token'] : '';
        $captcha = $this->form_security->verify_recaptcha($recaptchaToken);
        if (empty($captcha['ok'])) {
            $this->output->set_status_header(403);
            echo json_encode(array(
                'success' => FALSE,
                'message' => isset($captcha['message']) ? $captcha['message'] : 'CAPTCHA verification failed.',
            ));
            return;
        }
        
        $logged_in = (bool) $this->session->userdata('user_logged_in');
        $user_id = $this->session->userdata('user_id');
        $user = NULL;
        $name = '';
        $email = '';

        if ($logged_in && $user_id) {
            $this->load->model('User_model');
            $user = $this->User_model->get_user($user_id);
            if (!$user) {
                $this->output->set_status_header(401);
                echo json_encode(array('success' => FALSE, 'message' => 'User not found. Please log in again.'));
                return;
            }
            $name = trim($user->first_name . ' ' . $user->last_name);
            $email = $user->email;
            $data['name'] = $name;
            $data['email'] = $email;
        }

        if (!isset($data['message']) && isset($data['body'])) {
            $data['message'] = $data['body'];
        }

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[255]');
        $this->form_validation->set_rules('subject', 'Subject', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('message', 'Message', 'required|trim|max_length[5000]');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|max_length[50]');

        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            echo json_encode(array(
                'success' => FALSE,
                'message' => 'Please check the form fields and ensure all information is entered correctly.',
                'errors' => $this->form_validation->error_array()
            ));
            return;
        }

        // 5) Extra sanitization / format checks (injection & spam hardening).
        $clean = $this->form_security->sanitize_inquiry_fields($data);
        if (empty($clean['ok'])) {
            $this->output->set_status_header(400);
            echo json_encode(array(
                'success' => FALSE,
                'message' => isset($clean['message']) ? $clean['message'] : 'Invalid form data.',
            ));
            return;
        }

        $name = $clean['data']['name'];
        $email = $clean['data']['email'];
        $subject = $clean['data']['subject'];
        $body = $clean['data']['message'];
        $now = date('Y-m-d H:i:s');
        
        $inquiry = array(
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $body,
            'status' => 'new',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
            'cdate' => date('j F Y'),
            'created_at' => $now,
            'updated_at' => $now,
        );
        
        try {
            ob_clean();
            
            if (!$this->db->table_exists('inquiry')) {
                $this->output->set_status_header(500);
                echo json_encode(array('success' => FALSE, 'message' => 'Inquiry storage is not available.'));
                return;
            }
            
            // Query Builder uses escaped bindings (parameterized) for inserts.
            $inserted = $this->db->insert('inquiry', $inquiry);
            if (!$inserted) {
                $this->output->set_status_header(500);
                echo json_encode(array('success' => FALSE, 'message' => 'Sorry, we could not save your message. Please try again.'));
                return;
            }
            
            $inquiryid = (int) $this->db->insert_id();

            $this->form_security->log_event('inquiry_accepted', array(
                'inquiry_id' => $inquiryid,
                'ip' => $this->input->ip_address(),
                'email' => $email,
                'recaptcha_score' => isset($captcha['score']) ? $captcha['score'] : NULL,
            ));

            $this->sendInquiryEmails($inquiryid, $name, $email, $subject, $body);
            
            echo json_encode(array(
                'success' => TRUE,
                'message' => 'Your inquiry has been sent successfully! We will get back to you soon.',
                'inquiry_id' => $inquiryid
            ));
        } catch (Exception $e) {
            ob_clean();
            log_message('error', 'Inquiry creation error: ' . $e->getMessage());
            $this->output->set_status_header(500);
            echo json_encode(array(
                'success' => FALSE,
                'message' => 'We encountered an issue sending your inquiry. Please try again.'
            ));
        } finally {
            ob_end_flush();
        }
    }
    
    protected function sendInquiryEmails($inquiryid, $name, $email, $subject, $body) {
        $siteName = 'BODARE Pension House';
        $toEmail = '';
        
        $this->load->library('coop_mail');
        $this->load->library('coop_imap');
        $this->coop_mail->set_profile('contact');
        $contactSettings = $this->coop_mail->get_settings('contact');
        $contactMailbox = ($contactSettings && !empty($contactSettings->from_email)) ? $contactSettings->from_email : '';
        $toEmail = $contactMailbox;
        
        if ($this->db->table_exists('websitebasic')) {
            $info = $this->db->get('websitebasic')->row();
            if ($info) {
                if (!empty($info->email)) {
                    $toEmail = $info->email;
                }
                if (!empty($info->title)) {
                    $siteName = $info->title;
                }
            }
        }
        
        $mailHeaders = array('X-BODARE-Inquiry-ID' => (string) $inquiryid);
        $mailTimeout = 8;
        
        if ($toEmail) {
            $notifyHtml = '
                <div style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:640px;margin:0 auto;">
                    <h2 style="color:#02245b;margin-bottom:8px;">New Contact Inquiry</h2>
                    <p>A guest submitted a message through the Contact Us form.</p>
                    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
                        <tr><td style="padding:6px 0;font-weight:bold;width:110px;">Name</td><td>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="padding:6px 0;font-weight:bold;">Email</td><td>' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="padding:6px 0;font-weight:bold;">Subject</td><td>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="padding:6px 0;font-weight:bold;vertical-align:top;">Message</td><td>' . nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')) . '</td></tr>
                        <tr><td style="padding:6px 0;font-weight:bold;">Inquiry #</td><td>' . (int) $inquiryid . '</td></tr>
                    </table>
                    <p style="color:#666;font-size:13px;">Reply from the admin panel: Dashboard &rarr; Inquiries.</p>
                </div>
            ';
            @$this->coop_mail->send(
                $toEmail,
                Coop_imap::tagged_subject($inquiryid, 'New Inquiry: ' . $subject),
                $notifyHtml,
                NULL,
                NULL,
                $email,
                $name,
                $mailTimeout,
                $mailHeaders
            );
        }
        
        $ackHtml = '
            <div style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:640px;margin:0 auto;">
                <h2 style="color:#02245b;margin-bottom:8px;">We received your message</h2>
                <p>Hi ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>
                <p>Thank you for contacting <strong>' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '</strong>. We have received your inquiry and will get back to you soon.</p>
                <p><strong>Subject:</strong> ' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</p>
                <p style="color:#666;font-size:13px;">You can reply to this email if you need to add more details. Please keep the subject line so we can match your message.</p>
            </div>
        ';
        @$this->coop_mail->send(
            $email,
            Coop_imap::tagged_subject($inquiryid, 'We received your message: ' . $subject),
            $ackHtml,
            NULL,
            NULL,
            $contactMailbox,
            $siteName,
            $mailTimeout,
            $mailHeaders
        );
    }
}
