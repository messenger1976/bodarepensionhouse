<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Email_verification_model
 *
 * Handles both activation flavours:
 *  - legacy clickable-link tokens (64-hex, stored raw in `token`); and
 *  - 6-digit OTP codes (stored as a bcrypt hash in `token`), with wrong-attempt
 *    tracking via the optional `attempts` column.
 *
 * The `attempts` column ships in a migration
 * (admin/sql/add_otp_attempts_to_email_verifications.sql). When the column is
 * missing the code degrades gracefully (no attempt lock-out) instead of
 * failing, so existing installs keep working until the SQL is run.
 */
class Email_verification_model extends CI_Model {

    /** Maximum number of wrong OTP attempts before the code is locked. */
    const MAX_ATTEMPTS = 5;

    /** @var bool|null Memoized presence of the `attempts` column. */
    private static $has_attempts = null;

    /** @var bool Prevent repeated log spam while the column is missing. */
    private static $attempts_warned = false;

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * True when the email_verifications table has the `attempts` column.
     *
     * @return bool
     */
    protected function attempts_column_exists() {
        if (self::$has_attempts !== null) {
            return self::$has_attempts;
        }
        self::$has_attempts = false;
        try {
            if ($this->db->table_exists('email_verifications')) {
                $result = $this->db->query("SHOW COLUMNS FROM `email_verifications` LIKE 'attempts'");
                self::$has_attempts = $result && $result->num_rows() > 0;
            }
        } catch (Exception $e) {
            log_message('error', 'attempts column check failed: ' . $e->getMessage());
        } catch (Error $e) {
            log_message('error', 'attempts column check failed: ' . $e->getMessage());
        }
        if (!self::$has_attempts && !self::$attempts_warned) {
            self::$attempts_warned = true;
            log_message('error', 'email_verifications.attempts column missing - run admin/sql/add_otp_attempts_to_email_verifications.sql');
        }
        return self::$has_attempts;
    }

    /**
     * Legacy: create a clickable-link activation token.
     */
    public function create_token($email, $token, $expires_at) {
        $this->db->where('email', $email);
        $this->db->delete('email_verifications');

        $data = array(
            'email'      => $email,
            'token'      => $token,
            'expires_at' => $expires_at,
            'used'       => 0
        );
        if ($this->attempts_column_exists()) {
            $data['attempts'] = 0;
        }

        return $this->db->insert('email_verifications', $data);
    }

    /**
     * Store a new OTP code (bcrypt hash) for an email, replacing any previous
     * pending verification for that address (invalidates old codes/links).
     *
     * @param string $email
     * @param string $code_hash password_hash() of the 6-digit code
     * @param string $expires_at Y-m-d H:i:s
     * @return bool
     */
    public function create_otp($email, $code_hash, $expires_at) {
        return $this->create_token($email, $code_hash, $expires_at);
    }

    /**
     * Get a token by its raw value (legacy link activation).
     */
    public function get_token($token) {
        $this->db->where('token', $token);
        $this->db->where('used', 0);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        return $this->db->get('email_verifications')->row();
    }

    /**
     * Latest usable (unused, unexpired) verification row for an email.
     *
     * @param string $email
     * @return object|null
     */
    public function get_active_by_email($email) {
        $this->db->where('email', $email);
        $this->db->where('used', 0);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        $this->db->order_by('id', 'DESC');
        return $this->db->get('email_verifications')->row();
    }

    /**
     * Latest verification row for an email regardless of state (used for
     * resend cooldown/messaging).
     *
     * @param string $email
     * @return object|null
     */
    public function get_latest_by_email($email) {
        $this->db->where('email', $email);
        $this->db->order_by('id', 'DESC');
        return $this->db->get('email_verifications')->row();
    }

    /**
     * Increment the wrong-attempt counter for an email and return the new
     * count (or 0 when the column is unavailable).
     *
     * @param string $email
     * @return int new attempt count
     */
    public function record_failed_attempt($email) {
        if (!$this->attempts_column_exists()) {
            return 0;
        }
        $this->db->where('email', $email);
        $this->db->set('attempts', 'attempts + 1', false);
        $this->db->update('email_verifications');

        $this->db->select('attempts');
        $this->db->where('email', $email);
        $this->db->order_by('id', 'DESC');
        $row = $this->db->get('email_verifications')->row();
        return $row ? (int) $row->attempts : 0;
    }

    /**
     * Lock a code after too many failed attempts (marks it used so it can no
     * longer be submitted, forcing the user to request a fresh code).
     *
     * @param string $email
     */
    public function lock_by_email($email) {
        $this->db->where('email', $email);
        $this->db->update('email_verifications', array('used' => 1));
    }

    /**
     * Mark the newest verification row for an email as used (successful verify).
     *
     * @param string $email
     */
    public function mark_as_used_by_email($email) {
        $this->db->where('email', $email);
        $this->db->update('email_verifications', array('used' => 1));
    }

    /**
     * Legacy: mark a specific raw token used.
     */
    public function mark_as_used($token) {
        $this->db->where('token', $token);
        return $this->db->update('email_verifications', array('used' => 1));
    }

    /**
     * Delete all verification rows for an email.
     */
    public function delete_by_email($email) {
        $this->db->where('email', $email);
        return $this->db->delete('email_verifications');
    }
}
