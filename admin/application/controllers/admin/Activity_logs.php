<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Activity_logs extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Activity_log_model');
        $this->load->library('activity_log');
    }

    /**
     * Authorize a log action, allowing super admin regardless of permission mapping.
     */
    protected function authorize_log_action($permission_slug) {
        if ($this->is_super_admin() || $this->has_permission($permission_slug)) {
            return;
        }
        $this->session->set_flashdata('error', 'You do not have permission to access this page.');
        redirect('dashboard');
    }

    /**
     * List / filter the system activity logs.
     */
    public function index() {
        $this->authorize_log_action('view_activity_logs');

        $filters = $this->collect_filters();
        $page = max(1, (int) $this->input->get('page', TRUE));
        $per_page = 25;

        $result = $this->Activity_log_model->list_logs($filters, $page, $per_page);

        $data['title'] = 'System Activity Logs';
        $data['logs'] = $result['rows'];
        $data['total'] = $result['total'];
        $data['page'] = $page;
        $data['per_page'] = $per_page;
        $data['total_pages'] = max(1, (int) ceil($result['total'] / $per_page));
        $data['filters'] = $filters;

        // Filter dropdown options (distinct values from the last 90 days).
        $data['log_types'] = $this->Activity_log_model->distinct_column('log_type');
        $data['modules'] = $this->Activity_log_model->distinct_column('module');
        $data['actions'] = $this->Activity_log_model->distinct_column('action');
        $data['actor_types'] = $this->Activity_log_model->distinct_column('actor_type');
        $data['statuses'] = $this->Activity_log_model->distinct_column('status');
        $data['severities'] = $this->Activity_log_model->distinct_column('severity');

        $data['can_export'] = $this->is_super_admin() || $this->has_permission('export_activity_logs');
        $data['can_delete'] = $this->is_super_admin() || $this->has_permission('delete_activity_logs');

        // Build a base query string to preserve filters across pagination.
        $data['qs'] = $this->build_query_string($filters);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/activity_logs/index', $data);
        $this->load->view('admin/layout/footer');
    }

    /**
     * Show a single log entry with before/after values.
     */
    public function view($id) {
        $this->authorize_log_action('view_activity_logs');

        $log = $this->Activity_log_model->get_log($id);
        if (!$log) {
            show_404();
            return;
        }

        $data['title'] = 'Activity Log Detail';
        $data['log'] = $log;
        $data['old_values'] = $this->decode_json($log->old_values);
        $data['new_values'] = $this->decode_json($log->new_values);
        $data['metadata'] = $this->decode_json($log->metadata);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/activity_logs/view', $data);
        $this->load->view('admin/layout/footer');
    }

    /**
     * Export the filtered log list to CSV.
     */
    public function export() {
        $this->authorize_log_action('export_activity_logs');

        $filters = $this->collect_filters();
        $rows = $this->Activity_log_model->export_logs($filters);

        $filename = 'activity-logs-' . date('Ymd-His') . '.csv';

        $this->output->set_content_type('text/csv');
        $this->output->set_header('Content-Disposition: attachment; filename="' . $filename . '"');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');

        $out = fopen('php://output', 'w');

        fputcsv($out, array(
            'ID', 'Date', 'Type', 'Module', 'Action', 'Description',
            'Entity', 'Entity ID', 'Actor', 'Actor ID', 'Actor Name',
            'IP', 'Method', 'URL', 'Referrer', 'Status', 'Severity',
        ));

        foreach ($rows as $r) {
            $desc = is_null($r->description) ? '' : preg_replace('/\s+/', ' ', trim((string) $r->description));
            fputcsv($out, array(
                $r->id,
                $r->created_at,
                $r->log_type,
                $r->module,
                $r->action,
                $desc,
                $r->entity_type,
                $r->entity_id,
                $r->actor_type,
                $r->actor_id,
                $r->actor_name,
                $r->ip_address,
                $r->request_method,
                $r->request_url,
                $r->referrer,
                $r->status,
                $r->severity,
            ));
        }

        fclose($out);
    }

    /**
     * Delete log entries (super admin / delete permission only).
     */
    public function clear() {
        $this->authorize_log_action('delete_activity_logs');

        if ($this->input->method() !== 'post') {
            redirect('activity_logs');
            return;
        }

        $before = $this->input->post('before');
        $deleted = $this->activity_log->clear($before ?: NULL);

        $this->session->set_flashdata('success', $deleted . ' activity log entr' . ($deleted === 1 ? 'y' : 'ies') . ' deleted.');
        redirect('activity_logs');
    }

    /* ----------------------------------------------------------------- */
    /* Helpers                                                           */
    /* ----------------------------------------------------------------- */

    protected function collect_filters() {
        return array(
            'log_type'   => $this->input->get('log_type', TRUE),
            'module'     => $this->input->get('module', TRUE),
            'action'     => $this->input->get('action', TRUE),
            'actor_type' => $this->input->get('actor_type', TRUE),
            'actor_id'   => $this->input->get('actor_id', TRUE),
            'entity_type'=> $this->input->get('entity_type', TRUE),
            'entity_id'  => $this->input->get('entity_id', TRUE),
            'status'     => $this->input->get('status', TRUE),
            'severity'   => $this->input->get('severity', TRUE),
            'date_from'  => $this->input->get('date_from', TRUE),
            'date_to'    => $this->input->get('date_to', TRUE),
            'search'     => $this->input->get('search', TRUE),
        );
    }

    protected function build_query_string(array $filters) {
        $parts = array();
        foreach ($filters as $k => $v) {
            if ($v !== NULL && $v !== '') {
                $parts[] = $k . '=' . rawurlencode((string) $v);
            }
        }
        return implode('&', $parts);
    }

    protected function decode_json($json) {
        if ($json === NULL || $json === '') {
            return NULL;
        }
        $decoded = json_decode((string) $json, TRUE);
        return is_array($decoded) ? $decoded : NULL;
    }
}
