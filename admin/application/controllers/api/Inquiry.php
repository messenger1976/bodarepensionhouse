<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inquiry extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->database();
        $this->load->helper('url');
        header('Content-Type: application/json');
    }
    
    /**
     * Submit an inquiry (public Contact Us + logged-in customer).
     * Stores into inquiry table first, then sends best-effort emails.
     */
    public function submit() {
        ob_start();
        
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
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $this->input->post();
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
                echo json_encode(['success' => false, 'message' => 'User not found. Please log in again.']);
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

        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please check the form fields and ensure all information is entered correctly.',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $name = $this->security->xss_clean($data['name']);
        $email = $this->security->xss_clean($data['email']);
        $subject = $this->security->xss_clean($data['subject']);
        $body = trim(strip_tags($data['message']));
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
                echo json_encode(['success' => false, 'message' => 'Inquiry storage is not available.']);
                return;
            }
            
            $inserted = $this->db->insert('inquiry', $inquiry);
            if (!$inserted) {
                $this->output->set_status_header(500);
                echo json_encode(['success' => false, 'message' => 'Sorry, we could not save your message. Please try again.']);
                return;
            }
            
            $inquiryid = (int) $this->db->insert_id();
            $this->sendInquiryEmails($inquiryid, $name, $email, $subject, $body);
            
            echo json_encode([
                'success' => true,
                'message' => 'Your inquiry has been sent successfully! We will get back to you soon.',
                'inquiry_id' => $inquiryid
            ]);
        } catch (Exception $e) {
            ob_clean();
            log_message('error', 'Inquiry creation error: ' . $e->getMessage());
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We encountered an issue sending your inquiry. Please try again.'
            ]);
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
