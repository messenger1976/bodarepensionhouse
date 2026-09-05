<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Lightweight cron endpoints (no admin login).
 * Protect with cron_secret in includes/firebase-config.php.
 */
class Cron extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    protected function require_cron_secret()
    {
        $firebase_path = realpath(FCPATH . '../includes/firebase.php');
        if ($firebase_path && is_file($firebase_path)) {
            require_once $firebase_path;
        }

        $config = function_exists('bodare_firebase_config') ? bodare_firebase_config() : [];
        $expected = isset($config['cron_secret']) ? trim((string) $config['cron_secret']) : '';
        $provided = trim((string) $this->input->get_post('key'));

        if ($expected === '' || !hash_equals($expected, $provided)) {
            $this->output->set_status_header(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit;
        }
    }

    /**
     * GET/POST admin/index.php/cron/check_in_reminders?key=YOUR_SECRET
     * Sends FCM to guests with confirmed bookings checking in tomorrow (default).
     * Optional: &date=YYYY-MM-DD
     */
    public function check_in_reminders()
    {
        $this->require_cron_secret();
        header('Content-Type: application/json');

        $date = $this->input->get_post('date');
        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Invalid date']);
            return;
        }

        $this->load->library('push_notify');
        $stats = $this->push_notify->send_check_in_reminders($date ?: null);
        echo json_encode([
            'success' => true,
            'date' => $date ?: date('Y-m-d', strtotime('+1 day')),
            'stats' => $stats,
        ]);
    }

    /**
     * GET/POST admin/index.php/cron/purge_activity_logs?key=YOUR_SECRET
     * Deletes activity_log rows older than the configured retention window.
     */
    public function purge_activity_logs()
    {
        $this->require_cron_secret();
        header('Content-Type: application/json');

        $this->load->library('activity_log');
        $deleted = $this->activity_log->purge_old();

        echo json_encode([
            'success' => true,
            'deleted' => $deleted,
            'retention_days' => $this->activity_log->retention_days(),
        ]);
    }
}
