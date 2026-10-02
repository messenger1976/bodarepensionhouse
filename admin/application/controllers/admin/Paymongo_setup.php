<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH . 'core/Admin_Controller.php');
}

/**
 * Admin tool: PayMongo gateway settings (QR Ph / Card).
 * Values are stored in `booking_settings` so the Paymongo libraries read them unchanged.
 */
class Paymongo_setup extends Admin_Controller {

    private $secret_fields = array('paymongo_secret_key', 'paymongo_webhook_secret');

    public function __construct() {
        parent::__construct();
        $this->load->model('Booking_settings_model');
    }

    /**
     * Super Admin always passes, even before the permission SQL is applied.
     */
    private function can($permission_slug) {
        return $this->is_super_admin() || $this->has_permission($permission_slug);
    }

    private function require_can($permission_slug, $redirect_url = 'dashboard') {
        if (!$this->can($permission_slug)) {
            $this->session->set_flashdata('error', 'You do not have permission to access this page.');
            redirect($redirect_url);
        }
    }

    public function index() {
        if (!$this->can('manage_paymongo_setup')) {
            $this->require_can('view_paymongo_setup');
        }

        $all = $this->Booking_settings_model->get_all_settings();
        $secret_key = isset($all['paymongo_secret_key']) ? trim((string) $all['paymongo_secret_key']) : '';
        $webhook_secret = isset($all['paymongo_webhook_secret']) ? trim((string) $all['paymongo_webhook_secret']) : '';

        $mode = '';
        if (strpos($secret_key, 'sk_live_') === 0) {
            $mode = 'live';
        } elseif (strpos($secret_key, 'sk_test_') === 0) {
            $mode = 'test';
        }

        $data['title'] = 'PayMongo Setup';
        $data['can_manage'] = $this->can('manage_paymongo_setup');
        $data['paymongo_enabled'] = isset($all['paymongo_enabled']) && $all['paymongo_enabled'] === '1';
        $data['paymongo_confirm_on_paid'] = !isset($all['paymongo_confirm_on_paid']) || $all['paymongo_confirm_on_paid'] === '1';
        $data['paymongo_public_key'] = isset($all['paymongo_public_key']) ? $all['paymongo_public_key'] : '';
        $data['has_secret_key'] = $secret_key !== '';
        $data['has_webhook_secret'] = $webhook_secret !== '';
        $data['key_mode'] = $mode;

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/paymongo_setup/index', $data);
        $this->load->view('admin/layout/footer');
    }

    public function update() {
        $this->require_can('manage_paymongo_setup', 'paymongo_setup');

        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            redirect('paymongo_setup');
            return;
        }

        $settings_data = array(
            'paymongo_enabled' => $this->input->post('paymongo_enabled') ? '1' : '0',
            'paymongo_public_key' => trim((string) $this->input->post('paymongo_public_key')),
            'paymongo_confirm_on_paid' => $this->input->post('paymongo_confirm_on_paid') ? '1' : '0'
        );

        $changed_secrets = array();
        foreach ($this->secret_fields as $field) {
            $new_value = trim((string) $this->input->post($field));
            if ($this->input->post('clear_' . $field)) {
                $settings_data[$field] = '';
                $changed_secrets[$field] = 'cleared';
            } elseif ($new_value !== '') {
                $settings_data[$field] = $new_value;
                $changed_secrets[$field] = 'replaced';
            }
        }

        if ($this->Booking_settings_model->update_settings($settings_data)) {
            $this->session->set_flashdata('success', 'PayMongo settings updated successfully');
            if (isset($this->activity_log)) {
                $snap = $settings_data;
                unset($snap['paymongo_secret_key'], $snap['paymongo_public_key'], $snap['paymongo_webhook_secret']);
                $snap['secrets'] = $changed_secrets;
                $this->activity_log->crud('paymongo_setup', 'update', 'settings', null, 'PayMongo settings updated', null, $snap);
            }
        } else {
            $this->session->set_flashdata('error', 'Failed to update PayMongo settings');
        }
        redirect('paymongo_setup');
    }
}
