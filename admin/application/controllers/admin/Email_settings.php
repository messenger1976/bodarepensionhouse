<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('Admin_Controller', FALSE)) {
	require_once(APPPATH . 'core/Admin_Controller.php');
}

class Email_settings extends Admin_Controller {

	public function __construct() {
		parent::__construct();
		$this->require_permission('manage_email_settings');
		$this->load->database();
		$this->load->library('form_validation');
		$this->load->helper('url');
	}

	protected function emailProfile($profile) {
		return in_array($profile, array('contact', 'account'), TRUE) ? $profile : 'contact';
	}

	public function index() {
		$profile = $this->emailProfile($this->input->get('profile', TRUE));
		$data['email_settings'] = NULL;
		if ($this->db->table_exists('email_smtp_settings')) {
			if ($this->db->field_exists('profile', 'email_smtp_settings')) {
				$data['email_settings'] = $this->db->get_where('email_smtp_settings', array('profile' => $profile), 1)->row();
			} else {
				$data['email_settings'] = $this->db->get_where('email_smtp_settings', array('id' => 1), 1)->row();
			}
		}
		$data['email_profile'] = $profile;
		$data['title'] = 'Email/SMTP Settings';

		$this->load->view('admin/layout/header', $data);
		$this->load->view('admin/email_settings/index', $data);
		$this->load->view('admin/layout/footer');
	}

	public function update() {
		$profile = $this->emailProfile($this->input->post('profile', TRUE));
		$settings_url = 'email_settings?profile=' . $profile;

		if (!$this->db->table_exists('email_smtp_settings')) {
			$this->session->set_flashdata('error', 'email_smtp_settings table is missing. Run the SQL migrations first.');
			redirect($settings_url);
			return;
		}

		$this->form_validation->set_rules('smtp_host', 'SMTP Host', 'required|trim');
		$this->form_validation->set_rules('smtp_port', 'SMTP Port', 'required|integer|greater_than[0]|less_than_equal_to[65535]');
		$this->form_validation->set_rules('smtp_user', 'SMTP Username', 'required|trim');
		$this->form_validation->set_rules('from_email', 'From Email', 'required|valid_email|trim');
		$this->form_validation->set_rules('from_name', 'From Name', 'required|trim');

		if (!$this->form_validation->run()) {
			$this->session->set_flashdata('error', strip_tags(validation_errors(' ', ' ')));
			redirect($settings_url);
			return;
		}

		$crypto = $this->input->post('smtp_crypto', TRUE);
		$mailtype = $this->input->post('mailtype', TRUE);
		if (!in_array($crypto, array('', 'tls', 'ssl'), TRUE)) {
			$crypto = 'tls';
		}
		if (!in_array($mailtype, array('html', 'text'), TRUE)) {
			$mailtype = 'html';
		}

		$data = array(
			'protocol' => 'smtp',
			'smtp_host' => trim($this->input->post('smtp_host', TRUE)),
			'smtp_port' => (int) $this->input->post('smtp_port'),
			'smtp_user' => trim($this->input->post('smtp_user', TRUE)),
			'smtp_crypto' => $crypto,
			'smtp_timeout' => max(1, (int) $this->input->post('smtp_timeout')),
			'from_email' => trim($this->input->post('from_email', TRUE)),
			'from_name' => trim($this->input->post('from_name', TRUE)),
			'mailtype' => $mailtype,
			'charset' => 'utf-8',
			'newline' => "\r\n",
			'crlf' => "\r\n",
			'is_active' => $this->input->post('is_active') ? 1 : 0,
			'updated_at' => date('Y-m-d H:i:s'),
		);

		if ($profile === 'contact' && $this->db->field_exists('imap_enabled', 'email_smtp_settings')) {
			$imapHost = trim($this->input->post('imap_host', TRUE));
			$imapCrypto = $this->input->post('imap_crypto', TRUE);
			$data['imap_host'] = $imapHost !== '' ? $imapHost : $data['smtp_host'];
			$data['imap_port'] = max(1, (int) $this->input->post('imap_port'));
			$data['imap_crypto'] = in_array($imapCrypto, array('', 'tls', 'ssl'), TRUE) ? ($imapCrypto !== '' ? $imapCrypto : 'ssl') : 'ssl';
			$data['imap_enabled'] = $this->input->post('imap_enabled') ? 1 : 0;
		}

		$password = (string) $this->input->post('smtp_pass', FALSE);
		$hasProfile = $this->db->field_exists('profile', 'email_smtp_settings');
		if ($hasProfile) {
			$exists = $this->db->where('profile', $profile)->count_all_results('email_smtp_settings') > 0;
		} else {
			$exists = $this->db->where('id', 1)->count_all_results('email_smtp_settings') > 0;
		}

		if (!$exists && $password === '') {
			$this->session->set_flashdata('error', 'SMTP Password is required when creating the settings.');
			redirect($settings_url);
			return;
		}
		if ($password !== '') {
			$this->load->library('coop_mail');
			$data['smtp_pass'] = $this->coop_mail->encrypt_password($password);
		}

		if ($exists) {
			if ($hasProfile) {
				$this->db->where('profile', $profile);
			} else {
				$this->db->where('id', 1);
			}
			$saved = $this->db->update('email_smtp_settings', $data);
		} else {
			$data['id'] = ($profile === 'account') ? 2 : 1;
			if ($hasProfile) {
				$data['profile'] = $profile;
			}
			$data['created_at'] = date('Y-m-d H:i:s');
			if (!isset($data['smtp_pass'])) {
				$data['smtp_pass'] = '';
			}
			$saved = $this->db->insert('email_smtp_settings', $data);
		}

		$this->session->set_flashdata(
			$saved ? 'success' : 'error',
			$saved ? 'Email/SMTP settings saved successfully.' : 'Unable to save Email/SMTP settings.'
		);
		redirect($settings_url);
	}

	public function test() {
		$profile = $this->emailProfile($this->input->post('profile', TRUE));
		$settings_url = 'email_settings?profile=' . $profile;

		$this->form_validation->set_rules('test_email', 'Test Email', 'required|valid_email|trim');
		if (!$this->form_validation->run()) {
			$this->session->set_flashdata('error', strip_tags(validation_errors(' ', ' ')));
			redirect($settings_url);
			return;
		}

		$to = trim($this->input->post('test_email', TRUE));
		$this->load->library('coop_mail');
		$this->coop_mail->set_profile($profile);

		if ($this->coop_mail->send_test($to)) {
			$this->session->set_flashdata('success', 'Test email sent successfully to ' . $to . '. Check the inbox (and spam folder).');
		} else {
			$error = $this->coop_mail->get_last_error();
			$this->session->set_flashdata('error', 'Test email failed. ' . $error);
		}

		redirect($settings_url);
	}
}
