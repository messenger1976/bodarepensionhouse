<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coop_mail {

    const PROFILE_CONTACT = 'contact';
    const PROFILE_ACCOUNT = 'account';

    protected $CI;
    protected $last_error = '';
    protected $profile = self::PROFILE_CONTACT;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->library('email');
        $this->CI->config->load('smtp_settings', TRUE);
    }

    public function get_last_error() {
        return $this->last_error;
    }

    /**
     * Select which sender profile to use for subsequent sends:
     * 'contact' (Contact Us / inquiries) or 'account' (registration, password reset).
     */
    public function set_profile($profile) {
        $this->profile = in_array($profile, array(self::PROFILE_CONTACT, self::PROFILE_ACCOUNT), TRUE)
            ? $profile
            : self::PROFILE_CONTACT;
        return $this;
    }

    public function get_profile() {
        return $this->profile;
    }

    public function get_settings($profile = NULL) {
        $profile = $profile !== NULL ? $profile : $this->profile;

        // Fallback for databases that predate the profile column (run database/email_smtp_profiles.sql).
        if (!$this->CI->db->field_exists('profile', 'email_smtp_settings')) {
            return $this->CI->db->get_where('email_smtp_settings', array('id' => 1), 1)->row();
        }

        $row = $this->CI->db->get_where('email_smtp_settings', array('profile' => $profile), 1)->row();
        if (!$row) {
            $row = $this->CI->db->get_where('email_smtp_settings', array('id' => 1), 1)->row();
        }

        return $row;
    }

    public function encrypt_password($password) {
        $key = $this->encryption_key();
        $iv = openssl_random_pseudo_bytes(16);
        $ciphertext = openssl_encrypt($password, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $mac = hash_hmac('sha256', $iv . $ciphertext, $key, TRUE);
        return base64_encode($iv . $mac . $ciphertext);
    }

    public function decrypt_password($payload) {
        $raw = base64_decode($payload, TRUE);
        if ($raw === FALSE || strlen($raw) < 49) {
            return FALSE;
        }

        $key = $this->encryption_key();
        $iv = substr($raw, 0, 16);
        $mac = substr($raw, 16, 32);
        $ciphertext = substr($raw, 48);
        $expected = hash_hmac('sha256', $iv . $ciphertext, $key, TRUE);

        if (!hash_equals($expected, $mac)) {
            return FALSE;
        }

        $plain = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return ($plain === FALSE) ? FALSE : $plain;
    }

    public function configure($timeout_override = NULL) {
        $this->last_error = '';
        $settings = $this->get_settings();

        if (!$settings) {
            $this->last_error = 'Email/SMTP settings are not configured yet.';
            return FALSE;
        }

        if (empty($settings->is_active)) {
            $this->last_error = 'SMTP email sending is currently disabled.';
            return FALSE;
        }

        $password = $this->decrypt_password($settings->smtp_pass);
        if ($password === FALSE || $password === '') {
            $this->last_error = 'Unable to decrypt the saved SMTP password. Save the password again.';
            return FALSE;
        }

        $timeout = ($timeout_override !== NULL) ? max(1, (int) $timeout_override) : max(1, (int) $settings->smtp_timeout);

        $config = array(
            'protocol' => !empty($settings->protocol) ? $settings->protocol : 'smtp',
            'smtp_host' => $settings->smtp_host,
            'smtp_port' => (int) $settings->smtp_port,
            'smtp_user' => $settings->smtp_user,
            'smtp_pass' => $password,
            'smtp_crypto' => (string) $settings->smtp_crypto,
            'smtp_timeout' => $timeout,
            'mailtype' => !empty($settings->mailtype) ? $settings->mailtype : 'html',
            'charset' => !empty($settings->charset) ? $settings->charset : 'utf-8',
            'newline' => "\r\n",
            'crlf' => "\r\n",
            'wordwrap' => TRUE,
            'validate' => TRUE,
        );

        $this->CI->email->initialize($config);
        $this->CI->email->set_newline("\r\n");
        $this->CI->email->clear(TRUE);

        return $settings;
    }

    public function send($to, $subject, $message, $from_email = NULL, $from_name = NULL, $reply_to = NULL, $reply_to_name = NULL, $timeout_override = NULL, $headers = array(), $attachments = array()) {
        $settings = $this->configure($timeout_override);
        if ($settings === FALSE) {
            return FALSE;
        }

        $from_email = $from_email ? $from_email : $settings->from_email;
        $from_name = $from_name ? $from_name : $settings->from_name;

        $this->CI->email->from($from_email, $from_name);
        if ($reply_to) {
            $this->CI->email->reply_to($reply_to, $reply_to_name ? $reply_to_name : '');
        }
        foreach ((array) $headers as $headerName => $headerValue) {
            if ($headerValue !== '' && $headerValue !== NULL) {
                $this->CI->email->set_header($headerName, $headerValue);
            }
        }
        foreach ((array) $attachments as $attachment) {
            if (empty($attachment['path']) || !is_file($attachment['path'])) {
                continue;
            }
            $this->CI->email->attach(
                $attachment['path'],
                'attachment',
                !empty($attachment['name']) ? $attachment['name'] : '',
                !empty($attachment['mime']) ? $attachment['mime'] : ''
            );
        }
        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
        $this->CI->email->message($message);

        if ($this->CI->email->send()) {
            return TRUE;
        }

        $debug = $this->CI->email->print_debugger(array('headers'));
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($debug)));

        if (stripos($plain, '535') !== FALSE || stripos($plain, 'Incorrect authentication data') !== FALSE) {
            $this->last_error = 'SMTP login failed (535). Host/port/SSL are reachable, but the username or password was rejected. Re-check the mailbox password in cPanel/webmail, then save it again here.';
        } elseif (stripos($plain, 'Failed to connect') !== FALSE || stripos($plain, 'Unable to connect') !== FALSE) {
            $this->last_error = 'Could not connect to the SMTP server. Check host, port, and SSL/TLS settings.';
        } elseif ($plain !== '') {
            // Keep a short readable snippet, not the full RFC822 dump.
            if (preg_match('/(Failed to authenticate password\.[^.]*\.|Unable to send email using PHP SMTP\.[^.]*\.)/i', $plain, $m)) {
                $this->last_error = trim($m[1]);
            } else {
                $this->last_error = substr($plain, 0, 280);
            }
        } else {
            $this->last_error = 'The email could not be sent. Check SMTP host, port, SSL/TLS, and credentials.';
        }
        return FALSE;
    }

    public function send_test($to) {
        $settings = $this->get_settings();
        $title = $settings && !empty($settings->from_name) ? $settings->from_name : 'BODARE & COMMUNITY MPC';
        $subject = 'SMTP Test Email - ' . $title;
        $sent_at = date('F j, Y g:i A');
        $message = '
            <div style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:560px;margin:0 auto;">
                <h2 style="color:#36661f;margin-bottom:8px;">SMTP test successful</h2>
                <p>This is a test message from <strong>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</strong>.</p>
                <p>If you received this email, your XAMPP/SMTP configuration is working.</p>
                <p style="color:#666;font-size:13px;">Sent at: ' . htmlspecialchars($sent_at, ENT_QUOTES, 'UTF-8') . '</p>
            </div>
        ';

        return $this->send($to, $subject, $message);
    }

    protected function encryption_key() {
        return hash('sha256', $this->CI->config->item('encryption_key', 'smtp_settings'), TRUE);
    }
}
