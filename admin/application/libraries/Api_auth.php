<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Api_auth
 *
 * Customer authentication for the public API that works over both:
 *   1. Bearer tokens (raw token kept in the client's localStorage, sent as
 *      `Authorization: Bearer <token>`); and
 *   2. The legacy CodeIgniter cookie session (set for desktop browsers).
 *
 * Bearer tokens are checked first so the mobile app / WebView no longer
 * depends on a session cookie that can be dropped or expired by the OS. The
 * cookie session remains as a fallback so existing desktop visitors stay
 * signed in while sessions are still alive.
 *
 * The admin panel session (admin_logged_in) is intentionally NOT touched.
 */
class Api_auth {

    /** Lifetime (seconds) for a "remember me" token. */
    const TOKEN_LIFETIME_REMEMBER = 2592000; // 30 days
    /** Lifetime (seconds) for a short-lived session. */
    const TOKEN_LIFETIME_SHORT = 43200;      // 12 hours

    /** @var CI_Controller */
    protected $ci;

    public function __construct() {
        $this->ci =& get_instance();
    }

    /**
     * Extract the raw bearer token from the request headers.
     *
     * @return string|null
     */
    public function bearer_token() {
        $header = $this->ci->input->get_request_header('Authorization', true);
        if ($header === null || $header === '') {
            // Apache + mod_php sometimes hides Authorization from
            // getallheaders(); PHP exposes it in $_SERVER in most setups.
            if (function_exists('getallheaders')) {
                foreach (getallheaders() as $name => $value) {
                    if (strcasecmp($name, 'Authorization') === 0) {
                        $header = $value;
                        break;
                    }
                }
            }
            if (($header === null || $header === '') && isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $header = $_SERVER['HTTP_AUTHORIZATION'];
            }
            if (($header === null || $header === '') && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }

        if (is_string($header) && preg_match('/Bearer\s+(\S+)/i', trim($header), $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function load_deps() {
        static $loaded = false;
        if (!$loaded) {
            $this->ci->load->model('User_token_model');
            $this->ci->load->model('User_model');
            $loaded = true;
        }
    }

    /**
     * Returns the currently authenticated customer user, or null.
     *
     * Bearer token first, cookie session second. The returned user is always
     * an active `users` row.
     *
     * @return array|null ['user' => stdClass, 'auth_source' => 'token'|'session', 'token' => string|null]
     */
    public function customer() {
        $this->load_deps();

        $raw = $this->bearer_token();
        if ($raw) {
            $user_id = $this->ci->User_token_model->resolve_user_id($raw);
            if ($user_id) {
                $user = $this->ci->User_model->get_user($user_id);
                if ($user && isset($user->status) && $user->status === 'active') {
                    return array(
                        'user'        => $user,
                        'auth_source' => 'token',
                        'token'       => $raw
                    );
                }
                // Account was deleted/suspended - drop the dead token.
                $this->ci->User_token_model->revoke_token($raw);
                return null;
            }
            // Invalid or expired token. Fall through to the cookie session so
            // legacy desktop sessions keep working.
        }

        if ($this->ci->session->userdata('user_logged_in')) {
            $uid = (int) $this->ci->session->userdata('user_id');
            if ($uid > 0) {
                $user = $this->ci->User_model->get_user($uid);
                if ($user && isset($user->status) && $user->status === 'active') {
                    return array(
                        'user'        => $user,
                        'auth_source' => 'session',
                        'token'       => null
                    );
                }
                $this->ci->session->unset_userdata(array('user_logged_in', 'user_id', 'user_email', 'user_name'));
            }
        }

        return null;
    }

    /**
     * Convenience: id of the authenticated customer, or null.
     *
     * @return int|null
     */
    public function customer_user_id() {
        $auth = $this->customer();
        return $auth ? (int) $auth['user']->id : null;
    }

    /**
     * Convenience: active customer row, or null.
     *
     * @return object|null
     */
    public function customer_user() {
        $auth = $this->customer();
        return $auth ? $auth['user'] : null;
    }

    /**
     * Issue a new bearer token for a customer.
     *
     * @param int  $user_id
     * @param int  $lifetime_seconds
     * @return string|null raw token (client must store it) or null on failure
     */
    public function issue_token($user_id, $lifetime_seconds = self::TOKEN_LIFETIME_REMEMBER) {
        $this->load_deps();

        try {
            $raw = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            log_message('error', 'Could not generate auth token: ' . $e->getMessage());
            return null;
        } catch (Error $e) {
            log_message('error', 'Could not generate auth token: ' . $e->getMessage());
            return null;
        }

        $hash = hash('sha256', $raw);
        $expires_at = date('Y-m-d H:i:s', time() + max(60, (int) $lifetime_seconds));

        if (!$this->ci->User_token_model->create_token($user_id, $hash, $expires_at)) {
            log_message('error', 'Could not store auth token - run admin/sql/create_user_auth_tokens.sql');
            return null;
        }

        return $raw;
    }

    /**
     * Revoke the presented token (used by logout).
     */
    public function revoke_current_token() {
        $this->load_deps();
        $raw = $this->bearer_token();
        if ($raw) {
            $this->ci->User_token_model->revoke_token($raw);
        }
    }

    /**
     * Revoke all tokens for a user, optionally keeping the current one.
     *
     * @param int         $user_id
     * @param string|null $keep_raw_token
     */
    public function revoke_user_tokens($user_id, $keep_raw_token = null) {
        $this->load_deps();
        if ($keep_raw_token) {
            $this->ci->User_token_model->revoke_all_except($user_id, $keep_raw_token);
        } else {
            $this->ci->User_token_model->revoke_all_for_user($user_id);
        }
    }
}
