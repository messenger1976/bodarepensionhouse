<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Activity_log_model
 *
 * Read-side access to the System Activity Logs table. Writing is handled by
 * the Activity_log library. This model powers the admin panel screens and the
 * dashboard "Recent Activity" widget.
 */
class Activity_log_model extends CI_Model {

    protected $table = 'activity_logs';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Query the log with filters, pagination and keyword search.
     *
     * @param array $filters log_type, module, action, actor_type, actor_id,
     *                       entity_type, entity_id, status, severity,
     *                       date_from, date_to, search
     * @param int   $page    (1-based)
     * @param int   $per_page
     * @return array ['rows'=>array,'total'=>int]
     */
    public function list_logs(array $filters, $page = 1, $per_page = 25) {
        $this->apply_filters($filters);

        // Count matching rows.
        $count = $this->db->count_all_results($this->table);

        // Re-apply filters for the row query.
        $this->apply_filters($filters);
        $this->db->order_by('created_at', 'DESC');
        $this->db->order_by('id', 'DESC');

        $page = max(1, (int) $page);
        $per_page = max(1, min(200, (int) $per_page));
        $this->db->limit($per_page, ($page - 1) * $per_page);

        $rows = $this->db->get($this->table)->result();

        return array(
            'rows'  => $rows,
            'total' => (int) $count,
        );
    }

    /**
     * Fetch a single log row.
     * @param int $id
     * @return object|null
     */
    public function get_log($id) {
        return $this->db->where('id', (int) $id)->get($this->table)->row();
    }

    /**
     * Latest activity, optionally scoped by log_type.
     * @param int    $limit
     * @param string|null $log_type
     * @return array
     */
    public function recent($limit = 10, $log_type = NULL) {
        if ($log_type !== NULL && $log_type !== '') {
            $this->db->where('log_type', $log_type);
        }
        $this->db->order_by('created_at', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(max(1, min(100, (int) $limit)));
        return $this->db->get($this->table)->result();
    }

    /**
     * Count total rows and per log_type (for dashboard / header badges).
     * @return array
     */
    public function summary_counts($days = NULL) {
        $this->db->select('log_type, COUNT(*) AS total');
        $this->db->from($this->table);
        if ($days !== NULL && $days > 0) {
            $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-' . (int) $days . ' days')));
        }
        $this->db->group_by('log_type');

        $result = array('total' => 0, 'by_type' => array());
        $rows = $this->db->get()->result();
        foreach ($rows as $row) {
            $result['by_type'][$row->log_type] = (int) $row->total;
            $result['total'] += (int) $row->total;
        }
        return $result;
    }

    /**
     * Distinct values for a column (filter dropdowns), limited to recent N days.
     * @param string $column
     * @param int|null $days
     * @return array
     */
    public function distinct_column($column, $days = 90) {
        $allowed = array('log_type', 'module', 'action', 'actor_type', 'status', 'severity');
        if (!in_array($column, $allowed, TRUE)) {
            return array();
        }

        $this->db->select($column);
        $this->db->distinct();
        $this->db->from($this->table);
        if ($days !== NULL && $days > 0) {
            $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-' . (int) $days . ' days')));
        }
        $this->db->where($column . ' !=', '');
        $this->db->where($column . ' IS NOT NULL');
        $this->db->order_by($column, 'ASC');

        $rows = $this->db->get()->result();
        $out = array();
        foreach ($rows as $row) {
            $out[] = $row->$column;
        }
        return $out;
    }

    /**
     * Fetch all rows matching filters for CSV export.
     * @param array $filters
     * @param int   $limit
     * @return array
     */
    public function export_logs(array $filters, $limit = 10000) {
        $this->apply_filters($filters);
        $this->db->order_by('created_at', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(max(1, min(100000, (int) $limit)));
        return $this->db->get($this->table)->result();
    }

    /**
     * A small set of "story" summaries grouped by module for the widget.
     * @param int $limit
     * @return array
     */
    public function recent_by_module($limit = 10) {
        $this->db->select('module, log_type, COUNT(*) AS total');
        $this->db->from($this->table);
        $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')));
        $this->db->group_by(array('module', 'log_type'));
        $this->db->order_by('total', 'DESC');
        $this->db->limit(max(1, min(50, (int) $limit)));
        return $this->db->get()->result();
    }

    /* ----------------------------------------------------------------- */
    /* Filters                                                           */
    /* ----------------------------------------------------------------- */

    protected function apply_filters(array $filters) {
        // Allowed scalar filters.
        $scalar = array('log_type', 'module', 'action', 'actor_type', 'actor_id', 'entity_type', 'entity_id', 'status', 'severity');
        foreach ($scalar as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '' && $filters[$key] !== NULL) {
                $this->db->where($key, $filters[$key]);
            }
        }

        // Date range (created_at).
        if (!empty($filters['date_from'])) {
            $this->db->where('created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        // Keyword search across description / module / action / actor_name / request_url.
        if (!empty($filters['search'])) {
            $term = (string) $filters['search'];
            $this->db->group_start();
            $this->db->like('description', $term);
            $this->db->or_like('module', $term);
            $this->db->or_like('action', $term);
            $this->db->or_like('actor_name', $term);
            $this->db->or_like('entity_type', $term);
            $this->db->or_like('request_url', $term);
            $this->db->or_like('ip_address', $term);
            $this->db->group_end();
        }
    }
}
