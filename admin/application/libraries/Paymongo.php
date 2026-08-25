<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PayMongo Hosted Checkout client (GCash and other online methods).
 * Docs: https://docs.paymongo.com/docs/payment-channels-hosted-checkout
 */
class Paymongo {

    protected $CI;
    protected $secret_key = '';
    protected $public_key = '';
    protected $webhook_secret = '';
    protected $enabled = false;
    protected $last_error = '';
    protected $api_base = 'https://api.paymongo.com';

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Booking_settings_model');
        $this->reload_settings();
    }

    public function reload_settings() {
        $this->enabled = $this->CI->Booking_settings_model->get_setting('paymongo_enabled', '0') === '1';
        $this->secret_key = trim((string) $this->CI->Booking_settings_model->get_setting('paymongo_secret_key', ''));
        $this->public_key = trim((string) $this->CI->Booking_settings_model->get_setting('paymongo_public_key', ''));
        $this->webhook_secret = trim((string) $this->CI->Booking_settings_model->get_setting('paymongo_webhook_secret', ''));
    }

    public function is_configured() {
        return $this->enabled && $this->secret_key !== '' && strpos($this->secret_key, 'sk_') === 0;
    }

    public function get_last_error() {
        return $this->last_error;
    }

    public function get_public_site_base_url() {
        $this->CI->load->helper('url');
        $admin_base = rtrim(base_url(), '/');
        // Strip trailing /index.php if present (some hosts keep it in base_url)
        $admin_base = preg_replace('#/index\.php$#', '', $admin_base);
        if (substr($admin_base, -6) === '/admin') {
            return substr($admin_base, 0, -6);
        }
        return preg_replace('#/admin$#', '', $admin_base);
    }

    /**
     * Create a Hosted Checkout Session (v1 creates Payment Intent with the session).
     *
     * @param array $opts name, amount_php, description, reference_number, success_url, cancel_url,
     *                    payment_method_types (default gcash), metadata, billing
     * @return array|false
     */
    public function create_checkout_session(array $opts) {
        $this->last_error = '';

        if (!$this->is_configured()) {
            $this->last_error = 'PayMongo is not enabled or secret key is missing.';
            return false;
        }

        $amount_php = isset($opts['amount_php']) ? (float) $opts['amount_php'] : 0;
        $amount_centavos = (int) round($amount_php * 100);
        if ($amount_centavos < 2000) {
            // PayMongo minimum is typically ₱20.00
            $this->last_error = 'Payment amount must be at least ₱20.00.';
            return false;
        }

        $methods = !empty($opts['payment_method_types']) && is_array($opts['payment_method_types'])
            ? $opts['payment_method_types']
            : array('gcash');

        $line_name = !empty($opts['name']) ? substr((string) $opts['name'], 0, 100) : 'Room Reservation';
        $attributes = array(
            'send_email_receipt' => !empty($opts['send_email_receipt']),
            'show_description' => true,
            'show_line_items' => true,
            'description' => !empty($opts['description'])
                ? substr((string) $opts['description'], 0, 255)
                : $line_name,
            'line_items' => array(
                array(
                    'currency' => 'PHP',
                    'amount' => $amount_centavos,
                    'name' => $line_name,
                    'quantity' => 1
                )
            ),
            'payment_method_types' => array_values($methods),
            'success_url' => $opts['success_url'],
            'cancel_url' => $opts['cancel_url']
        );

        if (!empty($opts['reference_number'])) {
            $attributes['reference_number'] = substr((string) $opts['reference_number'], 0, 100);
        }
        if (!empty($opts['metadata']) && is_array($opts['metadata'])) {
            $attributes['metadata'] = $opts['metadata'];
        }
        if (!empty($opts['billing']) && is_array($opts['billing'])) {
            $attributes['billing'] = $opts['billing'];
        }

        $response = $this->request('POST', '/v1/checkout_sessions', array(
            'data' => array('attributes' => $attributes)
        ));

        if ($response === false) {
            return false;
        }

        $data = isset($response['data']) ? $response['data'] : null;
        if (!$data || empty($data['id'])) {
            $this->last_error = 'Unexpected PayMongo response when creating checkout session.';
            return false;
        }

        $attrs = isset($data['attributes']) ? $data['attributes'] : array();
        return array(
            'id' => $data['id'],
            'checkout_url' => isset($attrs['checkout_url']) ? $attrs['checkout_url'] : null,
            'status' => isset($attrs['status']) ? $attrs['status'] : null,
            'payment_intent' => isset($attrs['payment_intent']) ? $attrs['payment_intent'] : null,
            'raw' => $data
        );
    }

    public function retrieve_checkout_session($session_id) {
        $this->last_error = '';
        if (!$session_id) {
            $this->last_error = 'Checkout session ID is required.';
            return false;
        }

        $response = $this->request('GET', '/v1/checkout_sessions/' . rawurlencode($session_id));
        if ($response === false) {
            return false;
        }

        return isset($response['data']) ? $response['data'] : false;
    }

    /**
     * Determine if a checkout session has a successful payment.
     */
    public function session_is_paid($session) {
        if (!$session || !is_array($session)) {
            return false;
        }
        $attrs = isset($session['attributes']) ? $session['attributes'] : array();

        if (!empty($attrs['payments']) && is_array($attrs['payments'])) {
            foreach ($attrs['payments'] as $payment) {
                if (is_string($payment) && strpos($payment, 'pay_') === 0) {
                    // ID-only reference — treat as paid attempt present; confirm via intent below
                    continue;
                }
                $p_attrs = isset($payment['attributes']) ? $payment['attributes'] : $payment;
                $status = isset($p_attrs['status']) ? strtolower((string) $p_attrs['status']) : '';
                if ($status === 'paid') {
                    return true;
                }
            }
        }

        $pi = isset($attrs['payment_intent']) ? $attrs['payment_intent'] : null;
        if (is_array($pi)) {
            $pi_attrs = isset($pi['attributes']) ? $pi['attributes'] : $pi;
            $pi_status = isset($pi_attrs['status']) ? strtolower((string) $pi_attrs['status']) : '';
            if (in_array($pi_status, array('succeeded', 'paid'), true)) {
                return true;
            }
        } elseif (is_string($pi) && strpos($pi, 'pi_') === 0) {
            $intent = $this->retrieve_payment_intent($pi);
            if ($intent && $this->intent_is_paid($intent)) {
                return true;
            }
        }

        $status = isset($attrs['status']) ? strtolower((string) $attrs['status']) : '';
        // Hosted Checkout often stays "active" after a successful payment when payments[] is populated
        if ($status === 'paid') {
            return true;
        }
        if ($status === 'active' && !empty($attrs['payments']) && is_array($attrs['payments']) && count($attrs['payments']) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Extract a PayMongo payment id from a paid session (for transaction_id).
     */
    public function get_session_payment_id($session) {
        if (!$session || !is_array($session)) {
            return null;
        }
        $attrs = isset($session['attributes']) ? $session['attributes'] : array();
        if (!empty($attrs['payments'][0]['id'])) {
            return $attrs['payments'][0]['id'];
        }
        if (!empty($attrs['payments'][0]) && is_string($attrs['payments'][0])) {
            return $attrs['payments'][0];
        }
        if (!empty($attrs['payment_intent']['id'])) {
            return $attrs['payment_intent']['id'];
        }
        return isset($session['id']) ? $session['id'] : null;
    }

    /**
     * Create a Payment Intent (amount in PHP pesos).
     *
     * @param float $amount_php
     * @param string $description
     * @param array $metadata
     * @param array|null $payment_method_allowed default qrph; pass ['card'] for cards
     * @return array|false { id, client_key, status, raw }
     */
    public function create_payment_intent($amount_php, $description = '', $metadata = array(), $payment_method_allowed = null) {
        $this->last_error = '';
        if (!$this->is_configured()) {
            $this->last_error = 'PayMongo is not enabled or secret key is missing.';
            return false;
        }

        $amount_centavos = (int) round(((float) $amount_php) * 100);
        if ($amount_centavos < 2000) {
            $this->last_error = 'Payment amount must be at least ₱20.00.';
            return false;
        }

        $methods = (!empty($payment_method_allowed) && is_array($payment_method_allowed))
            ? array_values($payment_method_allowed)
            : array('qrph');

        $attributes = array(
            'amount' => $amount_centavos,
            'currency' => 'PHP',
            'payment_method_allowed' => $methods,
            'description' => $description !== '' ? substr((string) $description, 0, 255) : 'Room reservation',
            'statement_descriptor' => 'BODARE'
        );
        if (!empty($metadata) && is_array($metadata)) {
            $attributes['metadata'] = $metadata;
        }

        $response = $this->request('POST', '/v1/payment_intents', array(
            'data' => array('attributes' => $attributes)
        ));
        if ($response === false) {
            return false;
        }

        $data = isset($response['data']) ? $response['data'] : null;
        if (!$data || empty($data['id'])) {
            $this->last_error = 'Unexpected PayMongo response when creating payment intent.';
            return false;
        }

        $attrs = isset($data['attributes']) ? $data['attributes'] : array();
        return array(
            'id' => $data['id'],
            'client_key' => isset($attrs['client_key']) ? $attrs['client_key'] : null,
            'status' => isset($attrs['status']) ? $attrs['status'] : null,
            'raw' => $data
        );
    }

    /**
     * Create a QRPH Payment Method (secret key).
     *
     * @return array|false { id, raw }
     */
    public function create_qrph_payment_method($expiry_seconds = 1800) {
        $this->last_error = '';
        if (!$this->is_configured()) {
            $this->last_error = 'PayMongo is not enabled or secret key is missing.';
            return false;
        }

        $expiry_seconds = (int) $expiry_seconds;
        if ($expiry_seconds < 60) {
            $expiry_seconds = 60;
        }
        if ($expiry_seconds > 9000) {
            $expiry_seconds = 9000;
        }

        $response = $this->request('POST', '/v1/payment_methods', array(
            'data' => array(
                'attributes' => array(
                    'type' => 'qrph',
                    'expiry_seconds' => $expiry_seconds
                )
            )
        ));
        if ($response === false) {
            return false;
        }

        $data = isset($response['data']) ? $response['data'] : null;
        if (!$data || empty($data['id'])) {
            $this->last_error = 'Unexpected PayMongo response when creating QRPH payment method.';
            return false;
        }

        return array(
            'id' => $data['id'],
            'raw' => $data
        );
    }

    /**
     * Attach a payment method to a payment intent.
     *
     * @return array|false Intent resource data
     */
    public function attach_payment_method($intent_id, $method_id, $client_key = null) {
        $this->last_error = '';
        if (!$this->is_configured()) {
            $this->last_error = 'PayMongo is not enabled or secret key is missing.';
            return false;
        }
        if (!$intent_id || !$method_id) {
            $this->last_error = 'Payment intent and payment method are required.';
            return false;
        }

        $attributes = array(
            'payment_method' => $method_id
        );
        if ($client_key) {
            $attributes['client_key'] = $client_key;
        }

        $response = $this->request(
            'POST',
            '/v1/payment_intents/' . rawurlencode($intent_id) . '/attach',
            array('data' => array('attributes' => $attributes))
        );
        if ($response === false) {
            return false;
        }

        return isset($response['data']) ? $response['data'] : false;
    }

    public function retrieve_payment_intent($intent_id) {
        $this->last_error = '';
        if (!$intent_id) {
            $this->last_error = 'Payment intent ID is required.';
            return false;
        }

        $response = $this->request('GET', '/v1/payment_intents/' . rawurlencode($intent_id));
        if ($response === false) {
            return false;
        }

        return isset($response['data']) ? $response['data'] : false;
    }

    public function intent_is_paid($intent) {
        if (!$intent || !is_array($intent)) {
            return false;
        }
        $attrs = isset($intent['attributes']) ? $intent['attributes'] : array();
        $status = isset($attrs['status']) ? strtolower((string) $attrs['status']) : '';
        return in_array($status, array('succeeded', 'paid'), true);
    }

    /**
     * Extract QR image URL + related fields from an attached Payment Intent.
     *
     * @return array|null
     */
    public function extract_qrph_from_intent($intent) {
        if (!$intent || !is_array($intent)) {
            return null;
        }
        $attrs = isset($intent['attributes']) ? $intent['attributes'] : array();
        $next = isset($attrs['next_action']) ? $attrs['next_action'] : null;
        if (!$next || !is_array($next)) {
            return null;
        }

        $image = null;
        if (!empty($next['code']['image_url'])) {
            $image = $next['code']['image_url'];
        } elseif (!empty($next['redirect']['url'])) {
            // Fallback unused for QRPH, but keep shape safe
            $image = null;
        }

        if (!$image) {
            return null;
        }

        // Ensure usable as <img src>
        if (strpos($image, 'data:') !== 0 && preg_match('/^[A-Za-z0-9+\/=]+$/', $image)) {
            $image = 'data:image/png;base64,' . $image;
        }

        return array(
            'qr_image_url' => $image,
            'status' => isset($attrs['status']) ? $attrs['status'] : null,
            'client_key' => isset($attrs['client_key']) ? $attrs['client_key'] : null
        );
    }

    public function get_intent_payment_id($intent) {
        if (!$intent || !is_array($intent)) {
            return null;
        }
        $attrs = isset($intent['attributes']) ? $intent['attributes'] : array();
        if (!empty($attrs['payments'][0]['id'])) {
            return $attrs['payments'][0]['id'];
        }
        if (!empty($attrs['payments'][0]) && is_string($attrs['payments'][0])) {
            return $attrs['payments'][0];
        }
        return isset($intent['id']) ? $intent['id'] : null;
    }

    /**
     * Optional webhook signature check (Paymongo-Signature header).
     * If no webhook secret is configured, returns true (rely on session retrieve).
     */
    public function verify_webhook_signature($raw_body, $signature_header) {
        if ($this->webhook_secret === '') {
            return true;
        }
        if (!$signature_header || !$raw_body) {
            return false;
        }

        // Format: t=timestamp,te=test_signature,li=live_signature
        $parts = array();
        foreach (explode(',', $signature_header) as $piece) {
            $kv = explode('=', trim($piece), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }
        if (empty($parts['t'])) {
            return false;
        }

        $signed_payload = $parts['t'] . '.' . $raw_body;
        $expected = hash_hmac('sha256', $signed_payload, $this->webhook_secret);
        $candidate = !empty($parts['li']) ? $parts['li'] : (!empty($parts['te']) ? $parts['te'] : '');
        if ($candidate === '') {
            return false;
        }
        return hash_equals($expected, $candidate);
    }

    protected function request($method, $path, $body = null) {
        $url = $this->api_base . $path;
        $method = strtoupper($method);
        $payload = ($body !== null) ? json_encode($body) : null;
        $auth = 'Basic ' . base64_encode($this->secret_key . ':');

        if (function_exists('curl_init')) {
            $result = $this->request_with_curl($url, $method, $payload, $auth);
            if ($result !== false) {
                return $result;
            }
            $curl_error = $this->last_error;
            // Fall through to streams if cURL could not connect
            if (strpos($curl_error, 'Could not reach PayMongo') === 0) {
                $fallback = $this->request_with_streams($url, $method, $payload, $auth);
                if ($fallback !== false) {
                    return $fallback;
                }
                // Keep the more specific transport error
                if ($curl_error !== '') {
                    $this->last_error = $curl_error
                        . ' Hosting must allow outbound HTTPS to api.paymongo.com (port 443).';
                }
            }
            return false;
        }

        return $this->request_with_streams($url, $method, $payload, $auth);
    }

    protected function request_with_curl($url, $method, $payload, $auth) {
        $ch = curl_init($url);
        if ($ch === false) {
            $this->last_error = 'Could not reach PayMongo: cURL failed to initialize.';
            return false;
        }

        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . $auth
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        // Prefer IPv4 + HTTP/1.1 — common fixes on shared hosting
        if (defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
        if (defined('CURL_HTTP_VERSION_1_1')) {
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $errno) {
            $detail = $error;
            if ($detail === '' && function_exists('curl_strerror')) {
                $detail = curl_strerror($errno);
            }
            if ($detail === '') {
                $detail = 'cURL error #' . $errno;
            }
            $this->last_error = 'Could not reach PayMongo: ' . $detail;
            log_message('error', 'PayMongo cURL error #' . $errno . ': ' . $detail . ' URL=' . $url);
            return false;
        }

        return $this->decode_paymongo_response($raw, $http);
    }

    protected function request_with_streams($url, $method, $payload, $auth) {
        $header_lines = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . $auth,
            'Connection: close'
        );

        $opts = array(
            'http' => array(
                'method' => $method,
                'header' => implode("\r\n", $header_lines),
                'timeout' => 60,
                'ignore_errors' => true,
                'protocol_version' => 1.1
            ),
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true
            )
        );

        if ($payload !== null) {
            $opts['http']['content'] = $payload;
            $header_lines[] = 'Content-Length: ' . strlen($payload);
            $opts['http']['header'] = implode("\r\n", $header_lines);
        }

        $context = stream_context_create($opts);
        $raw = @file_get_contents($url, false, $context);
        $http = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $http = (int) $m[1];
        }

        if ($raw === false) {
            $this->last_error = 'Could not reach PayMongo: outbound HTTPS request failed (streams). Hosting must allow connections to api.paymongo.com.';
            log_message('error', 'PayMongo streams request failed for URL=' . $url);
            return false;
        }

        return $this->decode_paymongo_response($raw, $http);
    }

    protected function decode_paymongo_response($raw, $http) {
        $decoded = json_decode($raw, true);
        if ($http < 200 || $http >= 300) {
            $msg = 'PayMongo request failed (HTTP ' . $http . ').';
            if (isset($decoded['errors'][0]['detail'])) {
                $msg = $decoded['errors'][0]['detail'];
            } elseif (isset($decoded['errors'][0]['title'])) {
                $msg = $decoded['errors'][0]['title'];
            } elseif (!is_array($decoded) && is_string($raw) && $raw !== '') {
                $msg .= ' ' . substr(strip_tags($raw), 0, 180);
            }
            $this->last_error = $msg;
            log_message('error', 'PayMongo HTTP ' . $http . ': ' . $raw);
            return false;
        }

        if (!is_array($decoded)) {
            $this->last_error = 'PayMongo returned an invalid response.';
            return false;
        }

        return $decoded;
    }
}
