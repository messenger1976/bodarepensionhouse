<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Push extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Push_device_model');
        header('Content-Type: application/json');
    }

    protected function apply_cors_headers()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Credentials: true');
    }

    protected function read_json_body()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            $data = $this->input->post();
        }
        return is_array($data) ? $data : [];
    }

    protected function push_enabled()
    {
        $firebase_path = dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/firebase.php';
        if (is_file($firebase_path)) {
            require_once $firebase_path;
        }
        return function_exists('bodare_push_enabled') && bodare_push_enabled();
    }

    protected function current_user_id()
    {
        if (!$this->session->userdata('user_logged_in')) {
            return null;
        }
        $user_id = (int) $this->session->userdata('user_id');
        return $user_id > 0 ? $user_id : null;
    }

    /**
     * Register or refresh an FCM device token from the Capacitor app.
     */
    public function register()
    {
        $this->apply_cors_headers();

        if ($this->input->method() === 'options') {
            exit;
        }

        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        if (!$this->push_enabled()) {
            $this->output->set_status_header(503);
            echo json_encode([
                'success' => false,
                'message' => 'Push notifications are not enabled on the server yet.',
            ]);
            return;
        }

        $data = $this->read_json_body();
        $token = trim((string) ($data['token'] ?? ''));
        if ($token === '') {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Missing device token.']);
            return;
        }

        $platform = strtolower(trim((string) ($data['platform'] ?? 'android')));
        $device_label = isset($data['device_label']) ? trim((string) $data['device_label']) : null;
        $user_id = $this->current_user_id();

        if (!$this->db->table_exists('push_device_tokens')) {
            $this->output->set_status_header(503);
            echo json_encode([
                'success' => false,
                'message' => 'Push device table is missing. Run admin/sql/create_push_device_tokens_table.sql.',
            ]);
            return;
        }

        $id = $this->Push_device_model->upsert_token($token, $platform, $user_id, $device_label);
        if (!$id) {
            $this->output->set_status_header(500);
            echo json_encode(['success' => false, 'message' => 'Could not save device token.']);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Device token registered.',
            'linked_user' => $user_id !== null,
        ]);
    }

    /**
     * Deactivate a device token (e.g. on logout).
     */
    public function unregister()
    {
        $this->apply_cors_headers();

        if ($this->input->method() === 'options') {
            exit;
        }

        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $data = $this->read_json_body();
        $token = trim((string) ($data['token'] ?? ''));
        if ($token === '') {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Missing device token.']);
            return;
        }

        if (!$this->db->table_exists('push_device_tokens')) {
            echo json_encode(['success' => true, 'message' => 'Nothing to unregister.']);
            return;
        }

        $this->Push_device_model->deactivate_token($token);
        echo json_encode(['success' => true, 'message' => 'Device token removed.']);
    }
}
