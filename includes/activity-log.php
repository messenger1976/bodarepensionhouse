<?php
/**
 * System Activity Logs — public website helper.
 *
 * Provides bodare_activity_log() (write one activity row) and
 * bodare_log_page_view() (auto page-view capture) for the plain-PHP public
 * pages. It shares the same `activity_logs` table used by the admin panel
 * and the API, and it reuses the site's existing mysqli connection
 * (bodare_db()). Writes are best-effort: a failure never breaks the page.
 *
 * Wire the page view into a page by calling bodare_log_page_view() after
 * the site header/footer render, or rely on the site-head.php hook.
 */

if (!function_exists('bodare_activity_log_enabled')) {
    /**
     * Whether activity logging is on for the public site.
     * Defaults to TRUE; override with the constant BODARE_ACTIVITY_LOG_ENABLED.
     */
    function bodare_activity_log_enabled()
    {
        static $enabled = null;
        if ($enabled !== null) {
            return $enabled;
        }
        $enabled = defined('BODARE_ACTIVITY_LOG_ENABLED') ? (bool) BODARE_ACTIVITY_LOG_ENABLED : true;
        return $enabled;
    }
}

if (!function_exists('bodare_activity_log_table_exists')) {
    /**
     * Cheap cached check so we stop writing once the table is missing.
     */
    function bodare_activity_log_table_exists()
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }

        $db = bodare_db();
        if (!$db) {
            $exists = false;
            return $exists;
        }

        $res = @$db->query("SHOW TABLES LIKE 'activity_logs'");
        $exists = $res && $res->num_rows > 0;
        if ($res) {
            $res->free();
        }
        return $exists;
    }
}

if (!function_exists('bodare_activity_log_is_crawler')) {
    /**
     * Crude bot/crawler detection so we don't inflate page views with SEO
     * bots. Kept intentionally simple.
     */
    function bodare_activity_log_is_crawler()
    {
        static $is_crawler = null;
        if ($is_crawler !== null) {
            return $is_crawler;
        }

        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
        $is_crawler = $ua === '' || (bool) preg_match(
            '/bot|crawl|spider|slurp|bing|duckduck|yandex|baidu|facebookexternalhit|preview|headless|curl|wget/i',
            $ua
        );
        return $is_crawler;
    }
}

if (!function_exists('bodare_activity_log_is_http')) {
    /**
     * True only for real HTTP requests (skips CLI cron/inspect runs).
     */
    function bodare_activity_log_is_http()
    {
        return PHP_SAPI !== 'cli' && !empty($_SERVER['REQUEST_METHOD']);
    }
}

if (!function_exists('bodare_activity_log_prepare_row')) {
    /**
     * Build the row array and capture request context + actor.
     */
    function bodare_activity_log_prepare_row(array $opts)
    {
        $type  = isset($opts['type']) ? (string) $opts['type'] : 'system';
        $module = isset($opts['module']) ? (string) $opts['module'] : '';
        $action = isset($opts['action']) ? (string) $opts['action'] : '';
        $description = isset($opts['description']) ? (string) $opts['description'] : '';

        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
        $method = isset($_SERVER['REQUEST_METHOD']) ? (string) $_SERVER['REQUEST_METHOD'] : 'GET';
        $url = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        $referrer = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';

        // Actor: default guest, but honour an explicit actor_* in opts.
        if (isset($opts['actor_type']) && isset($opts['actor_id'])) {
            $actor_type = (string) $opts['actor_type'];
            $actor_id = $opts['actor_id'];
            $actor_name = isset($opts['actor_name']) ? (string) $opts['actor_name'] : null;
        } else {
            $actor_type = 'guest';
            $actor_id = null;
            $actor_name = null;
        }

        $now = date('Y-m-d H:i:s');
        $cut = static function ($v, $max) {
            $v = (string) $v;
            return $max > 0 && strlen($v) > $max ? substr($v, 0, $max) : $v;
        };

        return array(
            'id' => null,
            'log_type'     => $cut($type, 30),
            'module'       => $cut($module, 60),
            'action'       => $cut($action, 60),
            'description'  => $cut($description, 1000),
            'entity_type'  => isset($opts['entity_type']) ? $cut($opts['entity_type'], 60) : null,
            'entity_id'    => isset($opts['entity_id']) ? (int) $opts['entity_id'] : null,
            'actor_type'   => $cut($actor_type, 20),
            'actor_id'     => $actor_id !== null ? (int) $actor_id : null,
            'actor_name'   => $actor_name !== null ? $cut($actor_name, 150) : null,
            'ip_address'   => $cut($ip, 45),
            'user_agent'   => $cut($ua, 255),
            'request_method' => $cut($method, 10),
            'request_url'  => $cut($url, 500),
            'referrer'     => $cut($referrer, 500),
            'old_values'   => isset($opts['old']) ? bodare_activity_log_encode($opts['old']) : null,
            'new_values'   => isset($opts['new']) ? bodare_activity_log_encode($opts['new']) : null,
            'status'       => isset($opts['status']) ? $cut($opts['status'], 20) : 'success',
            'severity'     => isset($opts['severity']) ? $cut($opts['severity'], 20) : 'info',
            'metadata'     => !empty($opts['metadata']) ? bodare_activity_log_encode($opts['metadata']) : null,
            'created_at'   => $now,
        );
    }
}

if (!function_exists('bodare_activity_log_encode')) {
    function bodare_activity_log_encode($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_object($value)) {
            $value = (array) $value;
        }
        if (!is_array($value)) {
            $value = array('value' => $value);
        }
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $json = json_encode(array('value' => (string) $value));
        }
        return $json;
    }
}

if (!function_exists('bodare_activity_log')) {
    /**
     * Write one activity row from the public website. Best-effort.
     *
     * @param string $type        page_view|auth|crud|api|system|security
     * @param string $module
     * @param string $action
     * @param string $description
     * @param array  $opts        entity_*, actor_*, old, new, status, severity, metadata
     * @return bool
     */
    function bodare_activity_log($type, $module, $action, $description = '', array $opts = array())
    {
        if (!bodare_activity_log_enabled() || !bodare_activity_log_is_http()) {
            return false;
        }
        if (!bodare_activity_log_table_exists()) {
            return false;
        }

        $db = bodare_db();
        if (!$db) {
            return false;
        }

        $opts['type'] = $type;
        $opts['module'] = $module;
        $opts['action'] = $action;
        $opts['description'] = $description;
        $row = bodare_activity_log_prepare_row($opts);

        $sql = 'INSERT INTO activity_logs
                (log_type, module, action, description, entity_type, entity_id,
                 actor_type, actor_id, actor_name, ip_address, user_agent,
                 request_method, request_url, referrer, old_values, new_values,
                 status, severity, metadata, created_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $vals = array(
            $row['log_type'], $row['module'], $row['action'], $row['description'],
            $row['entity_type'], $row['entity_id'], $row['actor_type'], $row['actor_id'],
            $row['actor_name'], $row['ip_address'], $row['user_agent'], $row['request_method'],
            $row['request_url'], $row['referrer'], $row['old_values'], $row['new_values'],
            $row['status'], $row['severity'], $row['metadata'], $row['created_at'],
        );

        // Build dynamic types so NULL integer columns stay SQL NULL (not '').
        $types = '';
        $params = array();
        $int_indexes = array(5, 7); // entity_id, actor_id
        foreach ($vals as $i => $v) {
            $types .= in_array($i, $int_indexes, true) ? 'i' : 's';
            $params[] = &$vals[$i];
        }
        array_unshift($params, $types);

        try {
            call_user_func_array(array($stmt, 'bind_param'), $params);
            $ok = $stmt->execute();
            $stmt->close();
            return (bool) $ok;
        } catch (Throwable $e) {
            @$stmt->close();
            return false;
        }
    }
}

if (!function_exists('bodare_log_page_view')) {
    /**
     * Record a public page view (guests/customers). Skips crawlers and asset
     * URLs. Module defaults to 'website', action to 'view'.
     *
     * @param string $module
     * @param string $description
     * @param array  $opts
     * @return bool
     */
    function bodare_log_page_view($module = 'website', $description = '', array $opts = array())
    {
        if (!bodare_activity_log_enabled() || !bodare_activity_log_is_http()) {
            return false;
        }
        if (bodare_activity_log_is_crawler()) {
            return false;
        }

        // Skip asset and probe URLs so we don't pollute the log.
        $url = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|mp4|map|json|xml)$/i', $url)) {
            return false;
        }
        if (strpos($url, 'manifest.json') !== false || strpos($url, 'sw.js') !== false || strpos($url, 'test-api') !== false) {
            return false;
        }

        // Use the page's script name as a human-readable description.
        if ($description === '') {
            $script = isset($_SERVER['SCRIPT_NAME']) ? basename(str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME'])) : 'index.php';
            $description = 'Page view: ' . $script . ($url !== '' ? ' (' . $url . ')' : '');
        }

        return bodare_activity_log('page_view', $module, 'view', $description, array_merge($opts, array(
            'status'   => 'success',
            'severity' => 'info',
        )));
    }
}
