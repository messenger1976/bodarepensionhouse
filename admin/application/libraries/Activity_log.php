<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Activity_log
 *
 * Central, best-effort writer for the System Activity Logs / audit trail.
 * Designed to be called from admin controllers, API controllers and the
 * customer dashboard. Writes are wrapped in try/catch so a logging failure
 * can never break the request that triggered it.
 *
 * Convenience wrappers:
 *   ->log($type, $module, $action, $description, $opts)
 *   ->page_view($module, $description, $opts)
 *   ->crud($module, $action, $entity_type, $entity_id, $description, $old, $new, $opts)
 *   ->auth_event($action, $description, $opts)
 *
 * Reads / queries live in Activity_log_model.
 */
class Activity_log {

    /** @var CI_Controller */
    protected $CI;

    /** @var array */
    protected $settings = array();

    /** @var bool */
    protected $settings_loaded = FALSE;

    /** @var bool */
    protected $table_checked = FALSE;

    /** @var bool */
    protected $table_ok = TRUE;

    /** @var bool */
    protected $table_checked_failed = FALSE;

    /** @var bool */
    protected $insert_failed = FALSE;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('activity_log', TRUE);
    }

    /* ----------------------------------------------------------------- */
    /* Config                                                            */
    /* ----------------------------------------------------------------- */

    protected function settings() {
        if (!$this->settings_loaded) {
            $this->settings = (array) $this->CI->config->item('activity_log');
            $this->settings_loaded = TRUE;
        }
        return $this->settings;
    }

    /**
     * Master switch.
     * @return bool
     */
    public function enabled() {
        $s = $this->settings();
        return !isset($s['enabled']) || (bool) $s['enabled'];
    }

    protected function setting($key, $default = NULL) {
        $s = $this->settings();
        return array_key_exists($key, $s) ? $s[$key] : $default;
    }

    /**
     * Whether auto page views are enabled for the admin panel.
     * @return bool
     */
    public function admin_page_views_enabled() {
        return (bool) $this->setting('log_admin_page_views', TRUE);
    }

    /**
     * Whether auto page views are enabled for the public website.
     * @return bool
     */
    public function public_page_views_enabled() {
        return (bool) $this->setting('log_public_page_views', TRUE);
    }

    public function retention_days() {
        $days = (int) $this->setting('retention_days', 90);
        return $days > 0 ? $days : NULL;
    }

    /**
     * True when a request path matches one of the ignore fragments.
     * @param string $path
     * @return bool
     */
    protected function is_ignored_path($path = NULL) {
        $ignore = (string) $this->setting('ignore_paths', '');
        if ($ignore === '') {
            return FALSE;
        }
        $path = $path !== NULL ? $path : $this->request_path();
        $path = (string) $path;
        foreach (explode('|', $ignore) as $fragment) {
            $fragment = trim($fragment);
            if ($fragment !== '' && stripos($path, $fragment) !== FALSE) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Decide whether a page view should be recorded (path + enabled flag).
     * @param string $path
     * @return bool
     */
    public function should_log_view($path = NULL) {
        if (!$this->enabled()) {
            return FALSE;
        }
        return !$this->is_ignored_path($path);
    }

    /* ----------------------------------------------------------------- */
    /* Request context                                                   */
    /* ----------------------------------------------------------------- */

    protected function request_path() {
        return isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : $this->CI->uri->uri_string();
    }

    protected function clamp($value, $max) {
        if ($max <= 0) {
            return $value;
        }
        $value = (string) $value;
        return strlen($value) > $max ? substr($value, 0, $max) : $value;
    }

    protected function request_metadata() {
        $s = $this->settings();
        $max_url = (int) $this->setting('max_url_length', 500);
        $max_ua = (int) $this->setting('max_user_agent_length', 255);

        $url = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : ($this->CI->input->server('REQUEST_URI') ?: $this->CI->uri->uri_string());
        $referrer = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';

        return array(
            'ip_address'    => $this->CI->input->ip_address(),
            'user_agent'    => $this->clamp($this->CI->input->user_agent(), $max_ua),
            'request_method'=> $this->CI->input->method(),
            'request_url'   => $this->clamp($url, $max_url),
            'referrer'      => $this->clamp($referrer, $max_url),
        );
    }

    /**
     * Determine the acting user from the current session (admin or customer).
     * @return array actor_type, actor_id, actor_name
     */
    protected function current_actor() {
        $session = $this->CI->session;

        if ($session->userdata('admin_id')) {
            return array(
                'actor_type' => 'admin',
                'actor_id'   => (int) $session->userdata('admin_id'),
                'actor_name' => $session->userdata('admin_name') ?: $session->userdata('admin_username'),
            );
        }

        if ($session->userdata('user_id')) {
            return array(
                'actor_type' => 'customer',
                'actor_id'   => (int) $session->userdata('user_id'),
                'actor_name' => $session->userdata('user_name') ?: $session->userdata('user_email'),
            );
        }

        return array(
            'actor_type' => 'guest',
            'actor_id'   => NULL,
            'actor_name' => NULL,
        );
    }

    /* ----------------------------------------------------------------- */
    /* Insert                                                            */
    /* ----------------------------------------------------------------- */

    /**
     * Write one activity row. Returns TRUE on success, FALSE on failure or
     * when disabled. Never throws.
     *
     * @param string $type        page_view|auth|crud|api|system|security
     * @param string $module
     * @param string $action
     * @param string $description
     * @param array  $opts        log_type|severity|status|entity_type|actor_type|old|new|metadata overrides
     * @return bool
     */
    public function log($type, $module, $action, $description = '', $opts = array()) {
        if (!$this->enabled() || $this->insert_failed) {
            return FALSE;
        }

        // Check once per request whether the table exists; skip silently if absent.
        if (!$this->table_checked) {
            $this->table_checked = TRUE;
            if ($this->CI->db->table_exists('activity_logs') === FALSE) {
                $this->table_ok = FALSE;
                log_message('error', 'Activity_log: activity_logs table does not exist; logging disabled for this request.');
            }
        }
        if (!$this->table_ok || $this->table_checked_failed) {
            return FALSE;
        }

        $actor = $this->current_actor();
        $meta = $this->request_metadata();

        $max_desc = (int) $this->setting('max_description_length', 1000);

        $row = array(
            'log_type'     => $this->clamp(isset($opts['log_type']) ? $opts['log_type'] : $type, 30),
            'module'       => $this->clamp((string) $module, 60),
            'action'       => $this->clamp((string) $action, 60),
            'description'  => $this->clamp((string) $description, $max_desc),
            'entity_type'  => isset($opts['entity_type']) ? $this->clamp($opts['entity_type'], 60) : NULL,
            'entity_id'    => isset($opts['entity_id']) ? (int) $opts['entity_id'] : NULL,
            'actor_type'   => isset($opts['actor_type']) ? $this->clamp($opts['actor_type'], 20) : $actor['actor_type'],
            'actor_id'     => array_key_exists('actor_id', $opts) ? $opts['actor_id'] : $actor['actor_id'],
            'actor_name'   => isset($opts['actor_name']) ? $this->clamp($opts['actor_name'], 150) : $actor['actor_name'],
            'ip_address'   => isset($opts['ip_address']) ? $opts['ip_address'] : $meta['ip_address'],
            'user_agent'   => $meta['user_agent'],
            'request_method'=> $meta['request_method'],
            'request_url'  => $meta['request_url'],
            'referrer'     => $meta['referrer'],
            'old_values'   => $this->encode_values(isset($opts['old']) ? $opts['old'] : NULL),
            'new_values'   => $this->encode_values(isset($opts['new']) ? $opts['new'] : NULL),
            'status'       => isset($opts['status']) ? $this->clamp($opts['status'], 20) : 'success',
            'severity'     => isset($opts['severity']) ? $this->clamp($opts['severity'], 20) : 'info',
            'metadata'     => isset($opts['metadata']) && !empty($opts['metadata']) ? $this->encode_values($opts['metadata']) : NULL,
            'created_at'   => date('Y-m-d H:i:s'),
        );

        try {
            if ($this->CI->db->insert('activity_logs', $row)) {
                return TRUE;
            }
            // Insert failed -> stop hammering the DB for the rest of the request.
            $this->insert_failed = TRUE;
            log_message('error', 'Activity_log: insert failed: ' . $this->CI->db->_error_message());
            return FALSE;
        } catch (Exception $e) {
            $this->insert_failed = TRUE;
            log_message('error', 'Activity_log: exception writing log: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Record a page view (admin or public). Respects ignore path + flags.
     *
     * @param string $module      e.g. dashboard, bookings, website
     * @param string $description optional
     * @param array  $opts
     * @return bool
     */
    public function page_view($module = '', $description = '', $opts = array()) {
        if (!$this->should_log_view()) {
            return FALSE;
        }

        $uri = $this->CI->uri->uri_string();
        if ($uri === '' || $uri === 'login' || $uri === 'logout') {
            // Login/logout handled by explicit auth events; skip duplicate page views.
            if ($uri !== '') {
                return FALSE;
            }
        }

        if ($module === '') {
            // Derive module from the first URI segment (controller name).
            $module = $this->CI->uri->segment(1) ?: 'index';
        }

        if ($description === '') {
            $description = 'Page view: ' . str_replace('/', ' / ', $uri);
        }

        return $this->log(
            'page_view',
            $module,
            'view',
            $description,
            array_merge($opts, array(
                'log_type'  => 'page_view',
                'severity'  => 'info',
                'status'    => 'success',
            ))
        );
    }

    /**
     * Record a CRUD (create/update/delete) action with optional before/after
     * snapshots (stored as JSON).
     *
     * @param string $module        e.g. bookings, users
     * @param string $action        create|update|delete|restore
     * @param string $entity_type   e.g. booking, user
     * @param int    $entity_id
     * @param string $description
     * @param mixed  $old           array/object/null -> JSON snapshot
     * @param mixed  $new           array/object/null -> JSON snapshot
     * @param array  $opts
     * @return bool
     */
    public function crud($module, $action, $entity_type, $entity_id, $description = '', $old = NULL, $new = NULL, $opts = array()) {
        // Severity hint: anything that removes or permanently alters data is a warning.
        $severity = 'info';
        $verb = strtolower((string) $action);
        if (in_array($verb, array('delete', 'remove', 'void', 'purge', 'archive', 'restore'), TRUE)) {
            $severity = 'warning';
        }

        return $this->log(
            'crud',
            $module,
            $action,
            $description,
            array_merge($opts, array(
                'log_type'    => 'crud',
                'entity_type' => $entity_type,
                'entity_id'   => $entity_id,
                'old'         => $old,
                'new'         => $new,
                'severity'    => isset($opts['severity']) ? $opts['severity'] : $severity,
            ))
        );
    }

    /**
     * Record an authentication event (login, logout, reset, ...).
     *
     * @param string $action       login|logout|failed_login|register|reset_password|activate|forgot_password
     * @param string $description
     * @param array  $opts         e.g. ['status'=>'failed','actor_type'=>'guest']
     * @return bool
     */
    public function auth_event($action, $description = '', $opts = array()) {
        $status = isset($opts['status']) ? $opts['status'] : (stripos($action, 'failed') !== FALSE ? 'failed' : 'success');
        $actor_type = isset($opts['actor_type']) ? $opts['actor_type'] : 'guest';

        return $this->log(
            'auth',
            'auth',
            $action,
            $description,
            array_merge($opts, array(
                'log_type'    => 'auth',
                'module'      => 'auth',
                'action'      => $action,
                'status'      => $status,
                'actor_type'  => $actor_type,
            ))
        );
    }

    /* ----------------------------------------------------------------- */
    /* Maintenance                                                       */
    /* ----------------------------------------------------------------- */

    /**
     * Purge log rows older than the configured retention window.
     * @return int rows deleted (or -1 when disabled / no window)
     */
    public function purge_old() {
        if (!$this->enabled()) {
            return -1;
        }
        $days = $this->retention_days();
        if ($days === NULL) {
            return -1;
        }

        $cutoff = date('Y-m-d H:i:s', strtotime('-' . (int) $days . ' days'));
        $this->CI->db->where('created_at <', $cutoff);
        $this->CI->db->delete('activity_logs');
        return (int) $this->CI->db->affected_rows();
    }

    /**
     * Delete rows before a cutoff date (admin-triggered clear).
     * @param string|null $before ISO date or NULL for "everything".
     * @return int rows deleted
     */
    public function clear($before = NULL) {
        if (!$this->enabled()) {
            return 0;
        }
        if ($before !== NULL && $before !== '') {
            $this->CI->db->where('created_at <', $before);
        }
        $this->CI->db->delete('activity_logs');
        return (int) $this->CI->db->affected_rows();
    }

    /* ----------------------------------------------------------------- */
    /* Internals                                                         */
    /* ----------------------------------------------------------------- */

    protected function encode_values($value) {
        if ($value === NULL) {
            return NULL;
        }
        if (is_object($value)) {
            $value = (array) $value;
        }
        if (!is_array($value)) {
            $value = array('value' => $value);
        }
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === FALSE) {
            $json = json_encode(array('value' => (string) $value));
        }
        return $json;
    }
}
