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
        $admin_base = rtrim(base_url(), '/');
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
        }

        $status = isset($attrs['status']) ? strtolower((string) $attrs['status']) : '';
        return ($status === 'paid') || ($status === 'active' && !empty($attrs['payments']));
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
        $ch = curl_init($url);
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($this->secret_key . ':')
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            $this->last_error = 'Could not reach PayMongo: ' . $error;
            log_message('error', 'PayMongo cURL error: ' . $error);
            return false;
        }

        $decoded = json_decode($raw, true);
        if ($http < 200 || $http >= 300) {
            $msg = 'PayMongo request failed.';
            if (isset($decoded['errors'][0]['detail'])) {
                $msg = $decoded['errors'][0]['detail'];
            } elseif (isset($decoded['errors'][0]['title'])) {
                $msg = $decoded['errors'][0]['title'];
            }
            $this->last_error = $msg;
            log_message('error', 'PayMongo HTTP ' . $http . ': ' . $raw);
            return false;
        }

        return is_array($decoded) ? $decoded : false;
    }
}
