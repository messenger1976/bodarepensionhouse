<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User_token_model
 *
 * Persists hashed "remember me" bearer tokens issued to customer accounts.
 * The raw token (64 hex chars) is handed to the client once and stored in
 * localStorage; only its SHA-256 hash is kept here so a DB leak cannot be
 * replayed. Tokens can be revoked individually (logout) or wholesale
 * (password change) server-side.
 */
class User_token_model extends CI_Model {

    /** @var bool|null Memoized schema state for the current request. */
    private static $schema_ok = null;

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Make sure the user_auth_tokens table exists (CREATE IF NOT EXISTS).
     * Auto-provisioning keeps deployments working without running the SQL file
     * by hand; if the DB user lacks CREATE privilege, callers degrade to
     * cookie-session auth and the bundled SQL file should be run manually.
     *
     * @return bool
     */
    public function ensure_schema() {
        if (self::$schema_ok !== null) {
            return self::$schema_ok;
        }

        if ($this->db->table_exists('user_auth_tokens')) {
            self::$schema_ok = true;
            return true;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `user_auth_tokens` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `token_hash` char(64) NOT NULL,
            `expires_at` datetime NOT NULL,
            `last_used_at` datetime DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_token_hash` (`token_hash`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $this->db->query($sql);
            self::$schema_ok = $this->db->table_exists('user_auth_tokens');
        } catch (Exception $e) {
            log_message('error', 'user_auth_tokens auto-create failed: ' . $e->getMessage());
            self::$schema_ok = false;
        } catch (Error $e) {
            log_message('error', 'user_auth_tokens auto-create failed: ' . $e->getMessage());
            self::$schema_ok = false;
        }

        if (!self::$schema_ok) {
            log_message('error', 'user_auth_tokens table missing - run admin/sql/create_user_auth_tokens.sql');
        }

        return self::$schema_ok;
    }

    /**
     * Store a new token (hashed) for a user.
     *
     * @param int    $user_id
     * @param string $token_hash sha256 hex of the raw token
     * @param string $expires_at Y-m-d H:i:s
     * @return bool
     */
    public function create_token($user_id, $token_hash, $expires_at) {
        if (!$this->ensure_schema()) {
            return false;
        }
        return (bool) $this->db->insert('user_auth_tokens', array(
            'user_id'    => (int) $user_id,
            'token_hash' => $token_hash,
            'expires_at' => $expires_at
        ));
    }

    /**
     * Resolve a raw bearer token to a user id.
     *
     * @param string $raw_token
     * @return int|null user id when the token exists and is not expired
     */
    public function resolve_user_id($raw_token) {
        if (!$raw_token) {
            return null;
        }
        if (self::$schema_ok === null && !$this->ensure_schema()) {
            return null;
        }
        if (self::$schema_ok === false) {
            return null;
        }
        if (!preg_match('/^[a-f0-9]{64}$/i', $raw_token)) {
            return null;
        }

        $hash = hash('sha256', $raw_token);
        $this->db->where('token_hash', $hash);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        $row = $this->db->get('user_auth_tokens')->row();

        if (!$row) {
            return null;
        }

        // Lightweight last-used stamp (at most once per minute per token).
        $now = date('Y-m-d H:i:s');
        if ($row->last_used_at === null || strtotime($row->last_used_at) < time() - 60) {
            $this->db->where('id', $row->id);
            $this->db->update('user_auth_tokens', array('last_used_at' => $now));
        }

        return (int) $row->user_id;
    }

    /**
     * Delete a specific token (logout).
     *
     * @param string $raw_token
     */
    public function revoke_token($raw_token) {
        if (!$raw_token) {
            return;
        }
        $hash = hash('sha256', $raw_token);
        $this->db->where('token_hash', $hash);
        $this->db->delete('user_auth_tokens');
    }

    /**
     * Delete every token for a user (e.g. password changed).
     *
     * @param int $user_id
     */
    public function revoke_all_for_user($user_id) {
        $this->db->where('user_id', (int) $user_id);
        $this->db->delete('user_auth_tokens');
    }

    /**
     * Delete every token for a user except the current one.
     *
     * @param int    $user_id
     * @param string $keep_raw_token raw token that should survive
     */
    public function revoke_all_except($user_id, $keep_raw_token) {
        $keep_hash = $keep_raw_token ? hash('sha256', $keep_raw_token) : '';
        $this->db->where('user_id', (int) $user_id);
        if ($keep_hash !== '') {
            $this->db->where('token_hash !=', $keep_hash);
        }
        $this->db->delete('user_auth_tokens');
    }

    /**
     * Opportunistic cleanup of expired tokens.
     */
    public function cleanup_expired() {
        if (self::$schema_ok === false || (self::$schema_ok === null && !$this->ensure_schema())) {
            return;
        }
        $this->db->where('expires_at <', date('Y-m-d H:i:s'));
        $this->db->delete('user_auth_tokens');
    }
}
