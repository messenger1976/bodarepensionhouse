<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Form_security
 *
 * CSRF tokens, honeypot, rate limiting, reCAPTCHA v3, and submission logging
 * for public inquiry / contact forms.
 */
class Form_security {

	protected $CI;
	protected $settings = array();
	protected $cache_dir;

	public function __construct() {
		$this->CI =& get_instance();
		$this->CI->load->library('session');
		$this->CI->config->load('form_security', TRUE);
		$this->settings = $this->CI->config->item('form_security', 'form_security');
		if (!is_array($this->settings)) {
			$this->settings = array();
		}
		$this->settings = array_merge(array(
			'recaptcha_enabled' => TRUE,
			'recaptcha_site_key' => '',
			'recaptcha_secret_key' => '',
			'recaptcha_min_score' => 0.5,
			'recaptcha_expected_action' => 'contact_submit',
			'csrf_ttl' => 7200,
			'min_submit_seconds' => 2,
			'rate_limit_max' => 5,
			'rate_limit_window' => 900,
			'rate_limit_block_seconds' => 1800,
			'honeypot_field' => 'company_url',
			'allowed_origins' => array(),
		), $this->settings);

		$this->cache_dir = APPPATH . 'cache/form_security';
		if (!is_dir($this->cache_dir)) {
			@mkdir($this->cache_dir, 0755, TRUE);
		}
	}

	public function get_settings() {
		return $this->settings;
	}

	public function honeypot_field() {
		return $this->settings['honeypot_field'];
	}

	public function is_recaptcha_configured() {
		return !empty($this->settings['recaptcha_enabled'])
			&& $this->settings['recaptcha_site_key'] !== ''
			&& $this->settings['recaptcha_secret_key'] !== '';
	}

	public function public_bootstrap() {
		return array(
			'csrf_token' => $this->issue_csrf_token(),
			'honeypot_field' => $this->honeypot_field(),
			'recaptcha_enabled' => $this->is_recaptcha_configured(),
			'recaptcha_site_key' => $this->is_recaptcha_configured() ? $this->settings['recaptcha_site_key'] : '',
			'recaptcha_action' => $this->settings['recaptcha_expected_action'],
		);
	}

	/**
	 * Issue a new CSRF token bound to the CI session.
	 */
	public function issue_csrf_token() {
		$token = bin2hex(random_bytes(32));
		$this->CI->session->set_userdata('inquiry_csrf', array(
			'token' => $token,
			'issued_at' => time(),
		));
		return $token;
	}

	/**
	 * Verify CSRF token from request body. Consumes token on success.
	 *
	 * @return array{ok:bool,message?:string}
	 */
	public function verify_csrf($token) {
		$session = $this->CI->session->userdata('inquiry_csrf');
		if (!is_array($session) || empty($session['token']) || empty($session['issued_at'])) {
			return array('ok' => FALSE, 'message' => 'Security token missing or expired. Please refresh the page and try again.');
		}

		$ttl = (int) $this->settings['csrf_ttl'];
		if ((time() - (int) $session['issued_at']) > $ttl) {
			$this->CI->session->unset_userdata('inquiry_csrf');
			return array('ok' => FALSE, 'message' => 'Security token expired. Please refresh the page and try again.');
		}

		$minSeconds = (int) $this->settings['min_submit_seconds'];
		if ((time() - (int) $session['issued_at']) < $minSeconds) {
			$this->log_event('csrf_too_fast', array('ip' => $this->CI->input->ip_address()));
			return array('ok' => FALSE, 'message' => 'Please wait a moment and try again.');
		}

		if (!is_string($token) || !hash_equals($session['token'], $token)) {
			$this->log_event('csrf_mismatch', array('ip' => $this->CI->input->ip_address()));
			return array('ok' => FALSE, 'message' => 'Invalid security token. Please refresh the page and try again.');
		}

		// One-time use
		$this->CI->session->unset_userdata('inquiry_csrf');
		return array('ok' => TRUE);
	}

	/**
	 * Reject if honeypot field is filled (bots).
	 */
	public function check_honeypot($data) {
		$field = $this->honeypot_field();
		$value = isset($data[$field]) ? trim((string) $data[$field]) : '';
		if ($value !== '') {
			$this->log_event('honeypot_triggered', array(
				'ip' => $this->CI->input->ip_address(),
				'field' => $field,
			));
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Rate limit by IP. Returns ok/false and optional retry_after seconds.
	 *
	 * @return array{ok:bool,retry_after?:int,message?:string}
	 */
	public function check_rate_limit($ip = NULL) {
		$ip = $ip !== NULL ? $ip : $this->CI->input->ip_address();
		$ip = preg_replace('/[^a-zA-Z0-9\.:_-]/', '_', (string) $ip);
		$file = $this->cache_dir . '/rl_' . md5($ip) . '.json';
		$now = time();
		$window = (int) $this->settings['rate_limit_window'];
		$max = (int) $this->settings['rate_limit_max'];
		$blockSeconds = (int) $this->settings['rate_limit_block_seconds'];

		$state = array('attempts' => array(), 'blocked_until' => 0);
		if (is_file($file)) {
			$raw = @file_get_contents($file);
			$decoded = json_decode((string) $raw, TRUE);
			if (is_array($decoded)) {
				$state = array_merge($state, $decoded);
			}
		}

		if (!empty($state['blocked_until']) && (int) $state['blocked_until'] > $now) {
			$retry = (int) $state['blocked_until'] - $now;
			$this->log_event('rate_limit_blocked', array('ip' => $ip, 'retry_after' => $retry));
			return array(
				'ok' => FALSE,
				'retry_after' => $retry,
				'message' => 'Too many submissions. Please try again later.',
			);
		}

		$attempts = array();
		if (!empty($state['attempts']) && is_array($state['attempts'])) {
			foreach ($state['attempts'] as $ts) {
				$ts = (int) $ts;
				if ($ts >= ($now - $window)) {
					$attempts[] = $ts;
				}
			}
		}

		if (count($attempts) >= $max) {
			$blockedUntil = $now + $blockSeconds;
			$state['attempts'] = $attempts;
			$state['blocked_until'] = $blockedUntil;
			@file_put_contents($file, json_encode($state), LOCK_EX);
			$this->log_event('rate_limit_exceeded', array('ip' => $ip, 'count' => count($attempts)));
			return array(
				'ok' => FALSE,
				'retry_after' => $blockSeconds,
				'message' => 'Too many submissions. Please try again later.',
			);
		}

		$attempts[] = $now;
		$state['attempts'] = $attempts;
		$state['blocked_until'] = 0;
		@file_put_contents($file, json_encode($state), LOCK_EX);
		return array('ok' => TRUE);
	}

	/**
	 * Verify Google reCAPTCHA v3 token. Skips when not configured.
	 *
	 * @return array{ok:bool,message?:string,score?:float}
	 */
	public function verify_recaptcha($token, $remoteIp = NULL) {
		if (!$this->is_recaptcha_configured()) {
			return array('ok' => TRUE, 'skipped' => TRUE);
		}

		if (!is_string($token) || $token === '') {
			return array('ok' => FALSE, 'message' => 'CAPTCHA verification failed. Please refresh and try again.');
		}

		$remoteIp = $remoteIp !== NULL ? $remoteIp : $this->CI->input->ip_address();
		$payload = http_build_query(array(
			'secret' => $this->settings['recaptcha_secret_key'],
			'response' => $token,
			'remoteip' => $remoteIp,
		));

		$response = $this->http_post('https://www.google.com/recaptcha/api/siteverify', $payload);
		if ($response === FALSE) {
			$this->log_event('recaptcha_unreachable', array('ip' => $remoteIp));
			return array('ok' => FALSE, 'message' => 'CAPTCHA verification is temporarily unavailable. Please try again.');
		}

		$result = json_decode($response, TRUE);
		if (!is_array($result) || empty($result['success'])) {
			$this->log_event('recaptcha_failed', array('ip' => $remoteIp, 'raw' => $result));
			return array('ok' => FALSE, 'message' => 'CAPTCHA verification failed. Please try again.');
		}

		$score = isset($result['score']) ? (float) $result['score'] : 0.0;
		$action = isset($result['action']) ? (string) $result['action'] : '';
		$minScore = (float) $this->settings['recaptcha_min_score'];
		$expectedAction = (string) $this->settings['recaptcha_expected_action'];

		if ($expectedAction !== '' && $action !== $expectedAction) {
			$this->log_event('recaptcha_bad_action', array('ip' => $remoteIp, 'action' => $action, 'score' => $score));
			return array('ok' => FALSE, 'message' => 'CAPTCHA verification failed. Please try again.');
		}

		if ($score < $minScore) {
			$this->log_event('recaptcha_low_score', array('ip' => $remoteIp, 'score' => $score));
			return array('ok' => FALSE, 'message' => 'Unable to verify you are human. Please try again later.');
		}

		return array('ok' => TRUE, 'score' => $score);
	}

	/**
	 * Multibyte-safe length with strlen fallback.
	 */
	protected function str_len($value) {
		$value = (string) $value;
		return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
	}

	/**
	 * Sanitize and validate common inquiry fields beyond CI form_validation.
	 *
	 * @return array{ok:bool,message?:string,data?:array}
	 */
	public function sanitize_inquiry_fields($data) {
		$name = isset($data['name']) ? trim(strip_tags((string) $data['name'])) : '';
		$email = isset($data['email']) ? trim(strtolower(strip_tags((string) $data['email']))) : '';
		$subject = isset($data['subject']) ? trim(strip_tags((string) $data['subject'])) : '';
		$phone = isset($data['phone']) ? trim(strip_tags((string) $data['phone'])) : '';
		$message = isset($data['message']) ? trim(strip_tags((string) $data['message'])) : '';

		if ($name === '' || $this->str_len($name) > 150) {
			return array('ok' => FALSE, 'message' => 'Please enter a valid name.');
		}
		if (!preg_match("/^[\\p{L}\\p{M}'\\-\\.\\s]+$/u", $name)) {
			return array('ok' => FALSE, 'message' => 'Name contains invalid characters.');
		}
		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
			return array('ok' => FALSE, 'message' => 'Please enter a valid email address.');
		}
		if ($subject === '' || $this->str_len($subject) > 255) {
			return array('ok' => FALSE, 'message' => 'Please enter a valid subject.');
		}
		if ($message === '' || $this->str_len($message) > 5000) {
			return array('ok' => FALSE, 'message' => 'Please enter a valid message.');
		}
		if ($phone !== '' && ($this->str_len($phone) > 50 || !preg_match('/^[0-9+()\\s.\\-]{7,50}$/', $phone))) {
			return array('ok' => FALSE, 'message' => 'Please enter a valid phone number.');
		}

		// Basic spam heuristics
		$urlCount = preg_match_all('/https?:\\/\\/|www\\./i', $message . ' ' . $subject);
		if ($urlCount !== FALSE && $urlCount > 3) {
			$this->log_event('spam_url_density', array('ip' => $this->CI->input->ip_address(), 'urls' => $urlCount));
			return array('ok' => FALSE, 'message' => 'Your message looks like spam. Please reduce links and try again.');
		}

		return array(
			'ok' => TRUE,
			'data' => array(
				'name' => $this->CI->security->xss_clean($name),
				'email' => $this->CI->security->xss_clean($email),
				'subject' => $this->CI->security->xss_clean($subject),
				'phone' => $this->CI->security->xss_clean($phone),
				'message' => $message,
			),
		);
	}

	/**
	 * Apply CORS headers restricted to same host / configured origins.
	 */
	public function apply_cors_headers() {
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? (string) $_SERVER['HTTP_ORIGIN'] : '';
		$allowed = $this->settings['allowed_origins'];
		if (!is_array($allowed)) {
			$allowed = array();
		}

		$host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
		$scheme = $this->request_is_https() ? 'https' : 'http';
		$sameOriginCandidates = array(
			$scheme . '://' . $host,
			'https://' . $host,
			'http://' . $host,
		);

		$allowOrigin = '';
		if ($origin !== '') {
			if (in_array($origin, $allowed, TRUE) || in_array($origin, $sameOriginCandidates, TRUE)) {
				$allowOrigin = $origin;
			}
		}

		if ($allowOrigin !== '') {
			header('Access-Control-Allow-Origin: ' . $allowOrigin);
			header('Vary: Origin');
			header('Access-Control-Allow-Credentials: true');
		}
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
		header('Content-Type: application/json');
	}

	public function log_event($event, $context = array()) {
		$line = array(
			'time' => date('c'),
			'event' => $event,
			'context' => $context,
			'ua' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255) : '',
		);
		log_message('info', 'Form_security: ' . $event . ' ' . json_encode($context));

		$logFile = $this->cache_dir . '/submissions.log';
		@file_put_contents($logFile, json_encode($line) . PHP_EOL, FILE_APPEND | LOCK_EX);
	}

	protected function request_is_https() {
		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
			|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
	}

	protected function http_post($url, $body) {
		if (function_exists('curl_init')) {
			$ch = curl_init($url);
			curl_setopt_array($ch, array(
				CURLOPT_POST => TRUE,
				CURLOPT_POSTFIELDS => $body,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_TIMEOUT => 8,
				CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
			));
			$result = curl_exec($ch);
			$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if ($result === FALSE || $code < 200 || $code >= 300) {
				return FALSE;
			}
			return $result;
		}

		$context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
				'content' => $body,
				'timeout' => 8,
				'ignore_errors' => TRUE,
			),
		));
		$result = @file_get_contents($url, FALSE, $context);
		return ($result === FALSE) ? FALSE : $result;
	}
}
