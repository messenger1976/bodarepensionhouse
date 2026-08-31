<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Firebase Cloud Messaging (HTTP v1) using a service account JSON.
 */
class Fcm_service {

    protected $CI;
    protected $project_id = '';
    protected $service_account = null;
    protected $last_error = '';
    protected $access_token = null;
    protected $access_token_expires = 0;

    public function __construct()
    {
        $this->CI =& get_instance();

        $config_path = dirname(dirname(dirname(__DIR__))) . '/includes/firebase.php';
        if (is_file($config_path)) {
            require_once $config_path;
        }

        $config = function_exists('bodare_firebase_config') ? bodare_firebase_config() : [];
        $this->project_id = trim((string) ($config['project_id'] ?? ''));

        $json_path = trim((string) ($config['service_account_json'] ?? ''));
        if ($json_path !== '' && is_file($json_path)) {
            $decoded = json_decode((string) file_get_contents($json_path), true);
            if (is_array($decoded)) {
                $this->service_account = $decoded;
            }
        }
    }

    public function get_last_error()
    {
        return $this->last_error;
    }

    public function is_configured()
    {
        return $this->project_id !== ''
            && is_array($this->service_account)
            && !empty($this->service_account['private_key'])
            && !empty($this->service_account['client_email']);
    }

    public function send_to_token($token, $title, $body, array $data = [])
    {
        $token = trim((string) $token);
        if ($token === '') {
            $this->last_error = 'Missing FCM token.';
            return false;
        }

        return $this->send_message([
            'token' => $token,
            'notification' => [
                'title' => (string) $title,
                'body' => (string) $body,
            ],
            'data' => $this->normalize_data($data),
            'android' => [
                'priority' => 'HIGH',
            ],
        ]);
    }

    public function send_to_user($user_id, $title, $body, array $data = [])
    {
        $this->CI->load->model('Push_device_model');
        $tokens = $this->CI->Push_device_model->get_active_tokens_for_user((int) $user_id);
        if (!$tokens) {
            $this->last_error = 'No active device tokens for this user.';
            return false;
        }

        $sent = 0;
        foreach ($tokens as $row) {
            if ($this->send_to_token($row->fcm_token, $title, $body, $data)) {
                $sent++;
            }
        }

        if ($sent === 0) {
            return false;
        }

        return $sent;
    }

    protected function send_message(array $message)
    {
        if (!$this->is_configured()) {
            $this->last_error = 'Firebase service account is not configured.';
            return false;
        }

        $access_token = $this->get_access_token();
        if ($access_token === '') {
            return false;
        }

        $url = 'https://fcm.googleapis.com/v1/projects/' . rawurlencode($this->project_id) . '/messages:send';
        $payload = json_encode(['message' => $message]);

        $response = $this->http_post_json($url, $payload, [
            'Authorization: Bearer ' . $access_token,
        ]);

        if ($response === false) {
            return false;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            $this->last_error = 'Invalid FCM response.';
            return false;
        }

        if (!empty($decoded['error']['message'])) {
            $this->last_error = (string) $decoded['error']['message'];
            return false;
        }

        return true;
    }

    protected function normalize_data(array $data)
    {
        $normalized = [];
        foreach ($data as $key => $value) {
            $normalized[(string) $key] = is_scalar($value) ? (string) $value : json_encode($value);
        }
        return $normalized;
    }

    protected function get_access_token()
    {
        if ($this->access_token !== null && time() < ($this->access_token_expires - 60)) {
            return $this->access_token;
        }

        $jwt = $this->build_jwt();
        if ($jwt === '') {
            return '';
        }

        $body = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        $response = $this->http_post_raw(
            'https://oauth2.googleapis.com/token',
            $body,
            ['Content-Type: application/x-www-form-urlencoded']
        );

        if ($response === false) {
            return '';
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || empty($decoded['access_token'])) {
            $this->last_error = 'Could not obtain Firebase access token.';
            return '';
        }

        $this->access_token = (string) $decoded['access_token'];
        $this->access_token_expires = time() + (int) ($decoded['expires_in'] ?? 3600);
        return $this->access_token;
    }

    protected function build_jwt()
    {
        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => (string) $this->service_account['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $segments = [
            $this->base64url_encode(json_encode($header)),
            $this->base64url_encode(json_encode($claims)),
        ];
        $signing_input = implode('.', $segments);

        $private_key = openssl_pkey_get_private((string) $this->service_account['private_key']);
        if ($private_key === false) {
            $this->last_error = 'Invalid Firebase service account private key.';
            return '';
        }

        $signature = '';
        if (!openssl_sign($signing_input, $signature, $private_key, OPENSSL_ALGO_SHA256)) {
            $this->last_error = 'Could not sign Firebase JWT.';
            return '';
        }

        $segments[] = $this->base64url_encode($signature);
        return implode('.', $segments);
    }

    protected function base64url_encode($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    protected function http_post_json($url, $payload, array $headers = [])
    {
        $headers[] = 'Content-Type: application/json';
        return $this->http_post_raw($url, $payload, $headers);
    }

    protected function http_post_raw($url, $payload, array $headers = [])
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
            ]);
            $response = curl_exec($ch);
            if ($response === false) {
                $this->last_error = 'FCM request failed: ' . curl_error($ch);
                curl_close($ch);
                return false;
            }
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($status >= 400) {
                $this->last_error = 'FCM HTTP ' . $status . ': ' . $response;
                return $response;
            }
            return $response;
        }

        $header_lines = implode("\r\n", $headers);
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $header_lines,
                'content' => $payload,
                'timeout' => 30,
            ],
        ]);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            $this->last_error = 'FCM request failed (streams).';
            return false;
        }
        return $response;
    }
}
