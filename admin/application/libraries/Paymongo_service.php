<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Business logic for PayMongo QRPH payments tied to bookings/invoices.
 */
class Paymongo_service {

    protected $CI;
    protected $last_error = '';
    protected $default_qr_expiry = 1800;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->model('Booking_model');
        $this->CI->load->model('Booking_settings_model');
        $this->CI->load->model('Payment_model');
        $this->CI->load->model('Invoice_model');
        $this->CI->load->library('paymongo');
        $this->CI->load->library('billing_service');
    }

    public function is_ready() {
        return $this->CI->paymongo->is_configured();
    }

    public function get_last_error() {
        return $this->last_error ?: $this->CI->paymongo->get_last_error();
    }

    /**
     * Preferred online payment method label stored on payments rows.
     */
    public function online_payment_method() {
        if ($this->CI->db->table_exists('payments') && $this->payment_method_allows('qrph')) {
            return 'qrph';
        }
        return 'gcash';
    }

    private function payment_method_allows($method) {
        $row = $this->CI->db->query("SHOW COLUMNS FROM `payments` LIKE 'payment_method'")->row();
        if (!$row || empty($row->Type)) {
            return false;
        }
        return stripos($row->Type, "'" . $method . "'") !== false;
    }

    /**
     * Start or reuse QRPH for a booking. Creates unpaid issued invoice when needed.
     *
     * @return array|false
     */
    public function start_qrph_for_booking($booking, $amount = null, $force_new_qr = false) {
        $this->last_error = '';
        if (!$this->is_ready()) {
            $this->last_error = 'PayMongo is not enabled or secret key is missing.';
            return false;
        }
        if (!$this->CI->db->table_exists('payments')) {
            $this->last_error = 'Payments table is missing. Run the billing module SQL first.';
            return false;
        }

        $amount = $amount !== null ? (float) $amount : (float) $booking->total_amount;
        if ($amount < 20) {
            $this->last_error = 'QR Ph payment requires a minimum total of ₱20.00.';
            return false;
        }

        if ($this->find_paid_payment((int) $booking->id)) {
            $this->last_error = 'This booking is already paid.';
            return false;
        }

        $invoice_info = $this->CI->billing_service->ensure_issued_invoice_for_booking((int) $booking->id);
        $invoice_id = $invoice_info && !empty($invoice_info['invoice_id']) ? (int) $invoice_info['invoice_id'] : null;
        $invoice_number = $invoice_info && !empty($invoice_info['invoice_number']) ? $invoice_info['invoice_number'] : null;

        $payment_id = $this->ensure_pending_online_payment($booking, $amount, $invoice_id);
        $existing = $this->CI->Payment_model->get($payment_id);
        $meta = $this->parse_payment_meta($existing);

        // Reuse active QR if still valid
        if (!$force_new_qr && !empty($meta['qr_image_url']) && !empty($meta['expires_at'])) {
            $expires_ts = strtotime($meta['expires_at']);
            if ($expires_ts && $expires_ts > (time() + 60) && !empty($meta['payment_intent_id'])) {
                return $this->format_qrph_payload($booking, $payment_id, $meta, $invoice_id, $invoice_number, $amount);
            }
        }

        $intent_id = !empty($meta['payment_intent_id']) ? $meta['payment_intent_id'] : null;
        $client_key = !empty($meta['client_key']) ? $meta['client_key'] : null;

        // Create new intent if missing, or if previous QR expired and intent is no longer attachable
        $need_new_intent = !$intent_id;
        if ($intent_id && $force_new_qr) {
            $existing_intent = $this->CI->paymongo->retrieve_payment_intent($intent_id);
            if ($existing_intent && $this->CI->paymongo->intent_is_paid($existing_intent)) {
                $this->fulfill_paid_intent($booking, $existing_intent);
                $this->last_error = 'This booking is already paid.';
                return false;
            }
            $status = '';
            if ($existing_intent && isset($existing_intent['attributes']['status'])) {
                $status = strtolower((string) $existing_intent['attributes']['status']);
            }
            // Reuse intent when awaiting_payment_method; otherwise create fresh
            if (!in_array($status, array('awaiting_payment_method', 'awaiting_next_action'), true)) {
                $need_new_intent = true;
                $intent_id = null;
                $client_key = null;
            } elseif ($status === 'awaiting_next_action' && !$force_new_qr) {
                $qr = $this->CI->paymongo->extract_qrph_from_intent($existing_intent);
                if ($qr) {
                    $meta = $this->store_qrph_meta($payment_id, $booking, $amount, $invoice_id, array(
                        'payment_intent_id' => $intent_id,
                        'client_key' => $client_key ?: (isset($qr['client_key']) ? $qr['client_key'] : null),
                        'qr_image_url' => $qr['qr_image_url'],
                        'payment_method_id' => isset($meta['payment_method_id']) ? $meta['payment_method_id'] : null,
                        'expires_at' => date('Y-m-d H:i:s', time() + $this->default_qr_expiry)
                    ));
                    return $this->format_qrph_payload($booking, $payment_id, $meta, $invoice_id, $invoice_number, $amount);
                }
            }
        }

        if ($need_new_intent) {
            $intent = $this->CI->paymongo->create_payment_intent(
                $amount,
                'BODARE Reservation ' . $booking->booking_number,
                array(
                    'booking_number' => $booking->booking_number,
                    'booking_id' => (string) $booking->id,
                    'payment_id' => (string) $payment_id,
                    'invoice_id' => $invoice_id ? (string) $invoice_id : ''
                )
            );
            if (!$intent || empty($intent['id'])) {
                $this->last_error = $this->CI->paymongo->get_last_error() ?: 'Unable to create PayMongo payment intent.';
                return false;
            }
            $intent_id = $intent['id'];
            $client_key = $intent['client_key'];
        }

        $method = $this->CI->paymongo->create_qrph_payment_method($this->default_qr_expiry);
        if (!$method || empty($method['id'])) {
            $this->last_error = $this->CI->paymongo->get_last_error() ?: 'Unable to create QRPH payment method.';
            return false;
        }

        $attached = $this->CI->paymongo->attach_payment_method($intent_id, $method['id'], $client_key);
        if (!$attached) {
            $this->last_error = $this->CI->paymongo->get_last_error() ?: 'Unable to attach QRPH payment method.';
            return false;
        }

        $qr = $this->CI->paymongo->extract_qrph_from_intent($attached);
        if (!$qr || empty($qr['qr_image_url'])) {
            $this->last_error = 'PayMongo did not return a QR Ph image.';
            return false;
        }

        if (!empty($qr['client_key'])) {
            $client_key = $qr['client_key'];
        }

        $meta = $this->store_qrph_meta($payment_id, $booking, $amount, $invoice_id, array(
            'payment_intent_id' => $intent_id,
            'client_key' => $client_key,
            'qr_image_url' => $qr['qr_image_url'],
            'payment_method_id' => $method['id'],
            'expires_at' => date('Y-m-d H:i:s', time() + $this->default_qr_expiry)
        ));

        return $this->format_qrph_payload($booking, $payment_id, $meta, $invoice_id, $invoice_number, $amount);
    }

    public function regenerate_qrph_for_booking($booking) {
        return $this->start_qrph_for_booking($booking, null, true);
    }

    /**
     * Start or reuse QRPH for an invoice (including invoices with no booking).
     *
     * @return array|false
     */
    public function start_qrph_for_invoice($invoice, $amount = null, $force_new_qr = false) {
        $this->last_error = '';
        if (!$this->is_ready()) {
            $this->last_error = 'PayMongo is not enabled or secret key is missing.';
            return false;
        }
        if (!$invoice || empty($invoice->id)) {
            $this->last_error = 'Invoice is required.';
            return false;
        }
        if (!$this->CI->db->table_exists('payments')) {
            $this->last_error = 'Payments table is missing. Run the billing module SQL first.';
            return false;
        }

        // Prefer booking-linked flow when invoice has a booking
        if (!empty($invoice->booking_id)) {
            $booking = $this->CI->Booking_model->get_booking((int) $invoice->booking_id);
            if ($booking) {
                return $this->start_qrph_for_booking($booking, $amount, $force_new_qr);
            }
        }

        $invoice_id = (int) $invoice->id;
        $invoice_number = !empty($invoice->invoice_number) ? $invoice->invoice_number : ('INV' . $invoice_id);
        $amount = $amount !== null ? (float) $amount : (float) $invoice->balance_due;
        if ($amount <= 0 && isset($invoice->total_amount)) {
            $amount = (float) $invoice->total_amount;
        }
        if ($amount < 20) {
            $this->last_error = 'QR Ph payment requires a minimum total of ₱20.00.';
            return false;
        }

        if (isset($invoice->status) && $invoice->status === 'void') {
            $this->last_error = 'Cannot create QRPH for a voided invoice.';
            return false;
        }
        if (isset($invoice->status) && $invoice->status === 'paid') {
            $this->last_error = 'This invoice is already paid.';
            return false;
        }
        if ($this->find_paid_payment_for_invoice($invoice_id)) {
            $balance = isset($invoice->balance_due) ? (float) $invoice->balance_due : 0;
            if ($balance <= 0) {
                $this->last_error = 'This invoice is already paid.';
                return false;
            }
        }

        $payment_id = $this->ensure_pending_online_payment_for_invoice($invoice, $amount);
        $existing = $this->CI->Payment_model->get($payment_id);
        $meta = $this->parse_payment_meta($existing);

        if (!$force_new_qr && !empty($meta['qr_image_url']) && !empty($meta['expires_at'])) {
            $expires_ts = strtotime($meta['expires_at']);
            if ($expires_ts && $expires_ts > (time() + 60) && !empty($meta['payment_intent_id'])) {
                return $this->format_qrph_payload(null, $payment_id, $meta, $invoice_id, $invoice_number, $amount);
            }
        }

        $intent_id = !empty($meta['payment_intent_id']) ? $meta['payment_intent_id'] : null;
        $client_key = !empty($meta['client_key']) ? $meta['client_key'] : null;

        $need_new_intent = !$intent_id;
        if ($intent_id && $force_new_qr) {
            $existing_intent = $this->CI->paymongo->retrieve_payment_intent($intent_id);
            if ($existing_intent && $this->CI->paymongo->intent_is_paid($existing_intent)) {
                $this->fulfill_paid_intent_for_invoice($invoice, $existing_intent);
                $this->last_error = 'This invoice is already paid.';
                return false;
            }
            $status = '';
            if ($existing_intent && isset($existing_intent['attributes']['status'])) {
                $status = strtolower((string) $existing_intent['attributes']['status']);
            }
            if (!in_array($status, array('awaiting_payment_method', 'awaiting_next_action'), true)) {
                $need_new_intent = true;
                $intent_id = null;
                $client_key = null;
            } elseif ($status === 'awaiting_next_action' && !$force_new_qr) {
                $qr = $this->CI->paymongo->extract_qrph_from_intent($existing_intent);
                if ($qr) {
                    $meta = $this->store_qrph_meta($payment_id, null, $amount, $invoice_id, array(
                        'payment_intent_id' => $intent_id,
                        'client_key' => $client_key ?: (isset($qr['client_key']) ? $qr['client_key'] : null),
                        'qr_image_url' => $qr['qr_image_url'],
                        'payment_method_id' => isset($meta['payment_method_id']) ? $meta['payment_method_id'] : null,
                        'expires_at' => date('Y-m-d H:i:s', time() + $this->default_qr_expiry)
                    ), $invoice_number);
                    return $this->format_qrph_payload(null, $payment_id, $meta, $invoice_id, $invoice_number, $amount);
                }
            }
        }

        if ($need_new_intent) {
            $intent = $this->CI->paymongo->create_payment_intent(
                $amount,
                'BODARE Invoice ' . $invoice_number,
                array(
                    'invoice_number' => $invoice_number,
                    'invoice_id' => (string) $invoice_id,
                    'payment_id' => (string) $payment_id,
                    'booking_id' => '',
                    'booking_number' => ''
                )
            );
            if (!$intent || empty($intent['id'])) {
                $this->last_error = $this->CI->paymongo->get_last_error() ?: 'Unable to create PayMongo payment intent.';
                return false;
            }
            $intent_id = $intent['id'];
            $client_key = $intent['client_key'];
        }

        $method = $this->CI->paymongo->create_qrph_payment_method($this->default_qr_expiry);
        if (!$method || empty($method['id'])) {
            $this->last_error = $this->CI->paymongo->get_last_error() ?: 'Unable to create QRPH payment method.';
            return false;
        }

        $attached = $this->CI->paymongo->attach_payment_method($intent_id, $method['id'], $client_key);
        if (!$attached) {
            $this->last_error = $this->CI->paymongo->get_last_error() ?: 'Unable to attach QRPH payment method.';
            return false;
        }

        $qr = $this->CI->paymongo->extract_qrph_from_intent($attached);
        if (!$qr || empty($qr['qr_image_url'])) {
            $this->last_error = 'PayMongo did not return a QR Ph image.';
            return false;
        }

        if (!empty($qr['client_key'])) {
            $client_key = $qr['client_key'];
        }

        $meta = $this->store_qrph_meta($payment_id, null, $amount, $invoice_id, array(
            'payment_intent_id' => $intent_id,
            'client_key' => $client_key,
            'qr_image_url' => $qr['qr_image_url'],
            'payment_method_id' => $method['id'],
            'expires_at' => date('Y-m-d H:i:s', time() + $this->default_qr_expiry)
        ), $invoice_number);

        return $this->format_qrph_payload(null, $payment_id, $meta, $invoice_id, $invoice_number, $amount);
    }

    /**
     * Start PayMongo Hosted Checkout for card (booking-linked).
     * Guest pays on PayMongo's PCI-compliant card page.
     *
     * @return array|false
     */
    public function start_card_checkout_for_booking($booking, $amount = null, $force_new = true) {
        $this->last_error = '';
        if (!$booking || empty($booking->id)) {
            $this->last_error = 'Booking is required.';
            return false;
        }
        if (!$this->is_ready()) {
            $this->last_error = 'PayMongo is not enabled or secret key is missing.';
            return false;
        }

        $amount = $amount !== null ? (float) $amount : (float) $booking->total_amount;
        if ($amount < 20) {
            $this->last_error = 'Card payment requires a minimum of ₱20.00.';
            return false;
        }

        $invoice_info = $this->CI->billing_service->ensure_issued_invoice_for_booking((int) $booking->id);
        $invoice_id = $invoice_info && !empty($invoice_info['invoice_id']) ? (int) $invoice_info['invoice_id'] : null;
        $invoice_number = $invoice_info && !empty($invoice_info['invoice_number']) ? $invoice_info['invoice_number'] : null;

        return $this->create_card_checkout_session(array(
            'booking' => $booking,
            'invoice_id' => $invoice_id,
            'invoice_number' => $invoice_number,
            'amount' => $amount,
            'force_new' => $force_new,
            'guest_name' => isset($booking->guest_name) ? $booking->guest_name : null,
            'guest_email' => isset($booking->guest_email) ? $booking->guest_email : null
        ));
    }

    /**
     * Start PayMongo Hosted Checkout for card on an invoice (incl. no booking).
     *
     * @return array|false
     */
    public function start_card_checkout_for_invoice($invoice, $amount = null, $force_new = true) {
        $this->last_error = '';
        if (!$invoice || empty($invoice->id)) {
            $this->last_error = 'Invoice is required.';
            return false;
        }
        if (!$this->is_ready()) {
            $this->last_error = 'PayMongo is not enabled or secret key is missing.';
            return false;
        }

        if (!empty($invoice->booking_id)) {
            $booking = $this->CI->Booking_model->get_booking((int) $invoice->booking_id);
            if ($booking) {
                return $this->start_card_checkout_for_booking($booking, $amount, $force_new);
            }
        }

        if (isset($invoice->status) && $invoice->status === 'void') {
            $this->last_error = 'Cannot create card checkout for a voided invoice.';
            return false;
        }
        if (isset($invoice->status) && $invoice->status === 'paid') {
            $this->last_error = 'This invoice is already paid.';
            return false;
        }

        $invoice_id = (int) $invoice->id;
        $invoice_number = !empty($invoice->invoice_number) ? $invoice->invoice_number : ('INV' . $invoice_id);
        $amount = $amount !== null ? (float) $amount : (float) $invoice->balance_due;
        if ($amount <= 0 && isset($invoice->total_amount)) {
            $amount = (float) $invoice->total_amount;
        }
        if ($amount < 20) {
            $this->last_error = 'Card payment requires a minimum of ₱20.00.';
            return false;
        }

        return $this->create_card_checkout_session(array(
            'booking' => null,
            'invoice_id' => $invoice_id,
            'invoice_number' => $invoice_number,
            'amount' => $amount,
            'force_new' => $force_new,
            'guest_name' => isset($invoice->guest_name) ? $invoice->guest_name : null,
            'guest_email' => isset($invoice->guest_email) ? $invoice->guest_email : null
        ));
    }

    /**
     * Fulfill a paid Hosted Checkout session (card or legacy).
     * Resolves booking or invoice from metadata / payment row.
     */
    public function fulfill_paid_checkout_by_session($session, $session_id = null) {
        if (!$session || !is_array($session)) {
            return false;
        }
        $sid = $session_id ?: (isset($session['id']) ? $session['id'] : null);
        $attrs = isset($session['attributes']) ? $session['attributes'] : array();
        $meta = isset($attrs['metadata']) && is_array($attrs['metadata']) ? $attrs['metadata'] : array();

        $booking = null;
        $invoice = null;

        if (!empty($meta['booking_id'])) {
            $booking = $this->CI->Booking_model->get_booking((int) $meta['booking_id']);
        }
        if (!$booking && !empty($meta['booking_number'])) {
            $booking = $this->CI->Booking_model->get_booking_by_number($meta['booking_number']);
        }
        if (!$booking && !empty($attrs['reference_number'])) {
            $booking = $this->CI->Booking_model->get_booking_by_number($attrs['reference_number']);
        }

        if (!empty($meta['invoice_id'])) {
            $invoice = $this->CI->Invoice_model->get((int) $meta['invoice_id']);
        }
        if (!$invoice && $sid) {
            $payment = $this->find_payment_by_checkout_session_id($sid);
            if ($payment && !empty($payment->invoice_id)) {
                $invoice = $this->CI->Invoice_model->get((int) $payment->invoice_id);
            }
            if ($payment && !empty($payment->booking_id) && !$booking) {
                $booking = $this->CI->Booking_model->get_booking((int) $payment->booking_id);
            }
        }

        if ($booking) {
            return $this->fulfill_paid_session($booking, $session, $sid);
        }
        if ($invoice) {
            // Build intent-like payload from session for amount / ids
            $pi = isset($attrs['payment_intent']) ? $attrs['payment_intent'] : null;
            $intent = is_array($pi) ? $pi : array(
                'id' => is_string($pi) ? $pi : $sid,
                'attributes' => $attrs
            );
            if (empty($intent['id']) && !empty($attrs['payments'][0]['attributes']['payment_intent_id'])) {
                $intent['id'] = $attrs['payments'][0]['attributes']['payment_intent_id'];
            }
            return $this->fulfill_paid_intent_for_invoice($invoice, $intent);
        }

        return false;
    }

    public function find_payment_by_checkout_session_id($session_id) {
        if (!$session_id || !$this->CI->db->table_exists('payments')) {
            return null;
        }
        $this->CI->db->where('transaction_id', $session_id);
        $row = $this->CI->db->get('payments')->row();
        if ($row) {
            return $row;
        }
        // Fallback: notes JSON may store checkout_session_id
        $this->CI->db->like('notes', $session_id);
        $this->CI->db->order_by('id', 'DESC');
        return $this->CI->db->get('payments')->row();
    }

    /**
     * Build online_payment payload for invoice/booking APIs from stored payment row.
     */
    public function get_online_payment_for_booking($booking_id) {
        $payment = $this->find_latest_online_payment((int) $booking_id);
        if (!$payment) {
            return null;
        }
        if ($payment->payment_status === 'paid') {
            return array(
                'provider' => 'paymongo',
                'method' => 'qrph',
                'status' => 'paid',
                'qr_image_url' => null,
                'expires_at' => null,
                'can_regenerate' => false,
                'payment_intent_id' => $payment->transaction_id
            );
        }

        $meta = $this->parse_payment_meta($payment);
        $expired = true;
        if (!empty($meta['expires_at'])) {
            $ts = strtotime($meta['expires_at']);
            $expired = !($ts && $ts > time());
        }

        $booking = $this->CI->Booking_model->get_booking((int) $booking_id);
        return array(
            'provider' => 'paymongo',
            'method' => 'qrph',
            'status' => $expired ? 'expired' : 'awaiting_payment',
            'qr_image_url' => (!$expired && !empty($meta['qr_image_url'])) ? $meta['qr_image_url'] : null,
            'expires_at' => isset($meta['expires_at']) ? $meta['expires_at'] : null,
            'can_regenerate' => true,
            'payment_intent_id' => isset($meta['payment_intent_id']) ? $meta['payment_intent_id'] : $payment->transaction_id,
            'booking_number' => $booking ? $booking->booking_number : null,
            'amount' => (float) $payment->amount
        );
    }

    public function fulfill_paid_intent($booking, $intent) {
        if (!$booking || !$this->CI->db->table_exists('payments')) {
            return false;
        }

        if ($this->find_paid_payment((int) $booking->id)) {
            return true;
        }

        $intent_id = isset($intent['id']) ? $intent['id'] : null;
        $paymongo_payment_id = $this->CI->paymongo->get_intent_payment_id($intent);
        $amount = (float) $booking->total_amount;
        $attrs = isset($intent['attributes']) ? $intent['attributes'] : array();
        if (!empty($attrs['amount'])) {
            $amount = ((float) $attrs['amount']) / 100;
        }

        $payment_row = null;
        if ($intent_id) {
            $this->CI->db->where('transaction_id', $intent_id);
            $this->CI->db->where('booking_id', (int) $booking->id);
            $payment_row = $this->CI->db->get('payments')->row();
        }
        if (!$payment_row) {
            // Includes pending card Hosted Checkout (transaction_id = cs_…)
            $payment_row = $this->find_latest_paymongo_payment((int) $booking->id);
        }
        if ($payment_row && $payment_row->payment_status === 'paid') {
            return true;
        }

        $invoice_id = $payment_row && !empty($payment_row->invoice_id) ? (int) $payment_row->invoice_id : null;
        if (!$invoice_id) {
            $ensured = $this->CI->billing_service->ensure_issued_invoice_for_booking((int) $booking->id);
            if ($ensured && !empty($ensured['invoice_id'])) {
                $invoice_id = (int) $ensured['invoice_id'];
            }
        }

        $method = ($payment_row && !empty($payment_row->payment_method))
            ? $payment_row->payment_method
            : $this->online_payment_method();
        $method_label = ($method === 'card') ? 'card' : 'qrph';
        $update = array(
            'amount' => $amount,
            'payment_method' => $method,
            'payment_status' => 'paid',
            'payment_date' => date('Y-m-d H:i:s'),
            'transaction_id' => $intent_id ?: ($paymongo_payment_id ?: null),
            'notes' => json_encode(array(
                'provider' => 'paymongo',
                'method' => $method_label,
                'status' => 'paid',
                'payment_intent_id' => $intent_id,
                'paymongo_payment_id' => $paymongo_payment_id
            ))
        );
        if ($invoice_id) {
            $update['invoice_id'] = $invoice_id;
        }
        if ($this->CI->db->field_exists('reference_number', 'payments')) {
            $update['reference_number'] = $booking->booking_number;
        }
        if ($this->CI->db->field_exists('paymongo_intent_id', 'payments') && $intent_id) {
            $update['paymongo_intent_id'] = $intent_id;
        }
        if ($this->CI->db->field_exists('qrph_expires_at', 'payments')) {
            $update['qrph_expires_at'] = null;
        }

        if ($payment_row) {
            $this->CI->Payment_model->update((int) $payment_row->id, $update);
        } else {
            $update['booking_id'] = (int) $booking->id;
            $this->CI->Payment_model->create($update);
        }

        if ($invoice_id) {
            $this->CI->Invoice_model->recalculate_totals($invoice_id);
        }

        $confirm_on_paid = $this->CI->Booking_settings_model->get_setting('paymongo_confirm_on_paid', '1') === '1';
        if ($confirm_on_paid && strtolower((string) $booking->status) === 'pending') {
            $this->CI->Booking_model->set_booking_status((int) $booking->id, 'confirmed');
        }

        return true;
    }

    /**
     * Mark invoice-only (no booking) QRPH payment as paid from a PayMongo intent.
     */
    public function fulfill_paid_intent_for_invoice($invoice, $intent) {
        if (!$invoice || empty($invoice->id) || !$this->CI->db->table_exists('payments')) {
            return false;
        }

        $invoice_id = (int) $invoice->id;
        $intent_id = isset($intent['id']) ? $intent['id'] : null;
        $paymongo_payment_id = $this->CI->paymongo->get_intent_payment_id($intent);
        $amount = isset($invoice->balance_due) ? (float) $invoice->balance_due : (float) $invoice->total_amount;
        $attrs = isset($intent['attributes']) ? $intent['attributes'] : array();
        if (!empty($attrs['amount'])) {
            $amount = ((float) $attrs['amount']) / 100;
        }

        $payment_row = null;
        if ($intent_id) {
            if ($this->CI->db->field_exists('paymongo_intent_id', 'payments')) {
                $this->CI->db->where('paymongo_intent_id', $intent_id);
                $this->CI->db->where('invoice_id', $invoice_id);
                $payment_row = $this->CI->db->get('payments')->row();
            }
            if (!$payment_row) {
                $this->CI->db->where('transaction_id', $intent_id);
                $this->CI->db->where('invoice_id', $invoice_id);
                $payment_row = $this->CI->db->get('payments')->row();
            }
        }
        if (!$payment_row) {
            $payment_row = $this->find_latest_online_payment_for_invoice($invoice_id);
        }
        if (!$payment_row) {
            $this->CI->db->where('invoice_id', $invoice_id);
            $this->CI->db->where('payment_method', 'card');
            $this->CI->db->where('payment_status', 'pending');
            $this->CI->db->order_by('id', 'DESC');
            $payment_row = $this->CI->db->get('payments')->row();
        }

        // Already paid for this intent
        if ($payment_row && $payment_row->payment_status === 'paid') {
            $this->CI->Invoice_model->recalculate_totals($invoice_id);
            return true;
        }

        $method = ($payment_row && !empty($payment_row->payment_method))
            ? $payment_row->payment_method
            : $this->online_payment_method();
        $method_label = ($method === 'card') ? 'card' : 'qrph';
        $invoice_number = !empty($invoice->invoice_number) ? $invoice->invoice_number : ('INV' . $invoice_id);
        $update = array(
            'amount' => $amount,
            'payment_method' => $method,
            'payment_status' => 'paid',
            'payment_date' => date('Y-m-d H:i:s'),
            'transaction_id' => $intent_id ?: ($paymongo_payment_id ?: null),
            'invoice_id' => $invoice_id,
            'notes' => json_encode(array(
                'provider' => 'paymongo',
                'method' => $method_label,
                'status' => 'paid',
                'payment_intent_id' => $intent_id,
                'paymongo_payment_id' => $paymongo_payment_id
            ))
        );
        if ($this->CI->db->field_exists('reference_number', 'payments')) {
            $update['reference_number'] = $invoice_number;
        }
        if ($this->CI->db->field_exists('paymongo_intent_id', 'payments') && $intent_id) {
            $update['paymongo_intent_id'] = $intent_id;
        }
        if ($this->CI->db->field_exists('qrph_expires_at', 'payments')) {
            $update['qrph_expires_at'] = null;
        }

        if ($payment_row) {
            $this->CI->Payment_model->update((int) $payment_row->id, $update);
        } else {
            $update['booking_id'] = null;
            $this->CI->Payment_model->create($update);
        }

        $this->CI->Invoice_model->recalculate_totals($invoice_id);
        return true;
    }

    /** @deprecated kept for older checkout-session webhook path */
    public function fulfill_paid_session($booking, $session, $session_id) {
        if (!$booking) {
            return false;
        }
        if ($session && isset($session['id']) && strpos($session['id'], 'pi_') === 0) {
            return $this->fulfill_paid_intent($booking, $session);
        }

        $attrs = isset($session['attributes']) ? $session['attributes'] : array();
        $intent = null;
        $pi = isset($attrs['payment_intent']) ? $attrs['payment_intent'] : null;
        if (is_array($pi) && !empty($pi['id'])) {
            $intent = $pi;
        } elseif (is_string($pi) && strpos($pi, 'pi_') === 0) {
            $intent = $this->CI->paymongo->retrieve_payment_intent($pi);
        }

        // Prefer real Payment Intent when available (correct amount / ids)
        if ($intent && $this->CI->paymongo->intent_is_paid($intent)) {
            return $this->fulfill_paid_intent($booking, $intent);
        }

        // Fallback: fulfill using checkout session id (matches payments.transaction_id = cs_…)
        $amount_centavos = null;
        if (!empty($attrs['payments'][0]['attributes']['amount'])) {
            $amount_centavos = (float) $attrs['payments'][0]['attributes']['amount'];
        } elseif (!empty($attrs['line_items'][0]['amount'])) {
            $amount_centavos = (float) $attrs['line_items'][0]['amount'];
        }

        $fake_intent = array(
            'id' => $session_id ?: (isset($session['id']) ? $session['id'] : null),
            'attributes' => array(
                'amount' => $amount_centavos,
                'status' => 'succeeded',
                'payments' => isset($attrs['payments']) ? $attrs['payments'] : array()
            )
        );
        return $this->fulfill_paid_intent($booking, $fake_intent);
    }

    /**
     * Latest PayMongo-backed payment for a booking (QRPH, GCash, or Card checkout).
     */
    public function find_latest_paymongo_payment($booking_id) {
        if (!$this->CI->db->table_exists('payments') || !$booking_id) {
            return null;
        }
        $this->CI->db->where('booking_id', (int) $booking_id);
        $this->CI->db->group_start();
        $this->CI->db->where('payment_method', 'qrph');
        $this->CI->db->or_where('payment_method', 'gcash');
        $this->CI->db->or_where('payment_method', 'card');
        $this->CI->db->group_end();
        $this->CI->db->order_by('id', 'DESC');
        return $this->CI->db->get('payments')->row();
    }

    /** @deprecated use start_qrph_for_booking */
    public function start_gcash_checkout_for_booking($booking, $amount = null) {
        return $this->start_qrph_for_booking($booking, $amount, false);
    }

    public function find_paid_payment($booking_id) {
        if (!$this->CI->db->table_exists('payments')) {
            return null;
        }
        $this->CI->db->where('booking_id', (int) $booking_id);
        $this->CI->db->where('payment_status', 'paid');
        $this->CI->db->order_by('id', 'DESC');
        return $this->CI->db->get('payments')->row();
    }

    public function find_paid_payment_for_invoice($invoice_id) {
        if (!$this->CI->db->table_exists('payments') || !$invoice_id) {
            return null;
        }
        $this->CI->db->where('invoice_id', (int) $invoice_id);
        $this->CI->db->where('payment_status', 'paid');
        $this->CI->db->order_by('id', 'DESC');
        return $this->CI->db->get('payments')->row();
    }

    public function find_latest_gcash_payment($booking_id) {
        return $this->find_latest_online_payment($booking_id);
    }

    public function find_latest_online_payment($booking_id) {
        if (!$this->CI->db->table_exists('payments')) {
            return null;
        }
        $this->CI->db->where('booking_id', (int) $booking_id);
        $this->CI->db->group_start();
        $this->CI->db->where('payment_method', 'qrph');
        $this->CI->db->or_where('payment_method', 'gcash');
        $this->CI->db->group_end();
        $this->CI->db->order_by('id', 'DESC');
        return $this->CI->db->get('payments')->row();
    }

    public function find_latest_online_payment_for_invoice($invoice_id) {
        if (!$this->CI->db->table_exists('payments') || !$invoice_id) {
            return null;
        }
        $this->CI->db->where('invoice_id', (int) $invoice_id);
        $this->CI->db->group_start();
        $this->CI->db->where('payment_method', 'qrph');
        $this->CI->db->or_where('payment_method', 'gcash');
        $this->CI->db->group_end();
        $this->CI->db->order_by('id', 'DESC');
        return $this->CI->db->get('payments')->row();
    }

    public function find_booking_by_intent_id($intent_id) {
        $row = $this->find_payment_by_intent_id($intent_id);
        if ($row && !empty($row->booking_id)) {
            return $this->CI->Booking_model->get_booking((int) $row->booking_id);
        }
        return null;
    }

    /**
     * Find payment row by PayMongo payment intent id.
     */
    public function find_payment_by_intent_id($intent_id) {
        if (!$intent_id || !$this->CI->db->table_exists('payments')) {
            return null;
        }
        if ($this->CI->db->field_exists('paymongo_intent_id', 'payments')) {
            $this->CI->db->where('paymongo_intent_id', $intent_id);
            $row = $this->CI->db->get('payments')->row();
            if ($row) {
                return $row;
            }
        }
        $this->CI->db->where('transaction_id', $intent_id);
        return $this->CI->db->get('payments')->row();
    }

    private function ensure_pending_online_payment($booking, $amount, $invoice_id = null) {
        $existing = $this->find_latest_online_payment((int) $booking->id);
        if ($existing && $existing->payment_status === 'pending') {
            $update = array('amount' => $amount);
            if ($invoice_id) {
                $update['invoice_id'] = $invoice_id;
            }
            $this->CI->Payment_model->update($existing->id, $update);
            return (int) $existing->id;
        }

        if (!$invoice_id && $this->CI->db->table_exists('invoices')) {
            $primary = $this->CI->Invoice_model->get_primary_for_booking((int) $booking->id);
            if ($primary) {
                $invoice_id = (int) $primary->id;
            }
        }

        $payload = array(
            'booking_id' => (int) $booking->id,
            'invoice_id' => $invoice_id,
            'amount' => $amount,
            'payment_method' => $this->online_payment_method(),
            'payment_status' => 'pending',
            'payment_date' => null,
            'notes' => json_encode(array('provider' => 'paymongo', 'method' => 'qrph', 'status' => 'pending'))
        );
        if ($this->CI->db->field_exists('reference_number', 'payments')) {
            $payload['reference_number'] = $booking->booking_number;
        }

        return (int) $this->CI->Payment_model->create($payload);
    }

    private function ensure_pending_online_payment_for_invoice($invoice, $amount) {
        $invoice_id = (int) $invoice->id;
        $invoice_number = !empty($invoice->invoice_number) ? $invoice->invoice_number : ('INV' . $invoice_id);
        $existing = $this->find_latest_online_payment_for_invoice($invoice_id);
        if ($existing && $existing->payment_status === 'pending') {
            $this->CI->Payment_model->update($existing->id, array(
                'amount' => $amount,
                'invoice_id' => $invoice_id
            ));
            return (int) $existing->id;
        }

        $payload = array(
            'booking_id' => null,
            'invoice_id' => $invoice_id,
            'amount' => $amount,
            'payment_method' => $this->online_payment_method(),
            'payment_status' => 'pending',
            'payment_date' => null,
            'notes' => json_encode(array('provider' => 'paymongo', 'method' => 'qrph', 'status' => 'pending'))
        );
        if ($this->CI->db->field_exists('reference_number', 'payments')) {
            $payload['reference_number'] = $invoice_number;
        }

        return (int) $this->CI->Payment_model->create($payload);
    }

    /**
     * Create (or reuse) a pending card payment + PayMongo Hosted Checkout session.
     *
     * @param array $ctx booking?, invoice_id, invoice_number, amount, force_new, guest_name?, guest_email?
     * @return array|false
     */
    private function create_card_checkout_session(array $ctx) {
        $booking = !empty($ctx['booking']) ? $ctx['booking'] : null;
        $invoice_id = !empty($ctx['invoice_id']) ? (int) $ctx['invoice_id'] : null;
        $invoice_number = !empty($ctx['invoice_number']) ? $ctx['invoice_number'] : null;
        $amount = (float) $ctx['amount'];
        $force_new = !empty($ctx['force_new']);

        $payment_id = $this->ensure_pending_card_payment($booking, $invoice_id, $amount, $invoice_number);
        $existing = $this->CI->Payment_model->get($payment_id);
        $meta = $this->parse_payment_meta($existing);

        if (!$force_new && !empty($meta['checkout_url']) && !empty($meta['checkout_session_id'])) {
            return $this->format_card_checkout_payload($booking, $payment_id, $meta, $invoice_id, $invoice_number, $amount);
        }

        $site = $this->CI->paymongo->get_public_site_base_url();
        $success_url = $site . '/';
        $cancel_url = $site . '/';
        if ($booking && !empty($booking->booking_number)) {
            $success_url = $site . '/booking-confirmation.php?booking=' . rawurlencode($booking->booking_number) . '&payment=card&status=success';
            $cancel_url = $site . '/checkout.php?booking=' . rawurlencode($booking->booking_number) . '&payment=cancelled&method=card';
        } elseif ($invoice_id) {
            $success_url = $site . '/customer-invoices.php?id=' . (int) $invoice_id . '&payment=card&status=success';
            $cancel_url = $site . '/customer-invoices.php?id=' . (int) $invoice_id . '&payment=card&status=cancelled';
        }

        $line_name = $invoice_number
            ? ('Invoice ' . $invoice_number)
            : ($booking && !empty($booking->booking_number) ? ('Reservation ' . $booking->booking_number) : 'BODARE Payment');
        $description = 'BODARE Card Payment — ' . $line_name;
        $reference = $booking && !empty($booking->booking_number)
            ? $booking->booking_number
            : ($invoice_number ?: ('PAY' . $payment_id));

        $metadata = array(
            'payment_id' => (string) $payment_id,
            'invoice_id' => $invoice_id ? (string) $invoice_id : '',
            'invoice_number' => $invoice_number ? (string) $invoice_number : '',
            'booking_id' => $booking ? (string) $booking->id : '',
            'booking_number' => ($booking && !empty($booking->booking_number)) ? (string) $booking->booking_number : '',
            'method' => 'card'
        );

        $billing = array();
        if (!empty($ctx['guest_name'])) {
            $billing['name'] = substr((string) $ctx['guest_name'], 0, 100);
        }
        if (!empty($ctx['guest_email'])) {
            $billing['email'] = substr((string) $ctx['guest_email'], 0, 100);
        }

        $session = $this->CI->paymongo->create_checkout_session(array(
            'name' => $line_name,
            'amount_php' => $amount,
            'description' => $description,
            'reference_number' => $reference,
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
            'payment_method_types' => array('card'),
            'send_email_receipt' => true,
            'metadata' => $metadata,
            'billing' => !empty($billing) ? $billing : null
        ));

        if (!$session || empty($session['checkout_url'])) {
            $this->last_error = $this->CI->paymongo->get_last_error() ?: 'Unable to create PayMongo card checkout session.';
            return false;
        }

        $intent_id = null;
        if (!empty($session['payment_intent'])) {
            if (is_array($session['payment_intent']) && !empty($session['payment_intent']['id'])) {
                $intent_id = $session['payment_intent']['id'];
            } elseif (is_string($session['payment_intent'])) {
                $intent_id = $session['payment_intent'];
            }
        }

        $meta = array(
            'provider' => 'paymongo',
            'method' => 'card',
            'status' => 'awaiting_payment',
            'checkout_session_id' => $session['id'],
            'checkout_url' => $session['checkout_url'],
            'payment_intent_id' => $intent_id
        );

        $update = array(
            'amount' => $amount,
            'payment_method' => 'card',
            'payment_status' => 'pending',
            'transaction_id' => $session['id'],
            'notes' => json_encode($meta)
        );
        if ($invoice_id) {
            $update['invoice_id'] = $invoice_id;
        }
        if ($this->CI->db->field_exists('reference_number', 'payments')) {
            $update['reference_number'] = $reference;
        }
        if ($intent_id && $this->CI->db->field_exists('paymongo_intent_id', 'payments')) {
            $update['paymongo_intent_id'] = $intent_id;
        }

        $this->CI->Payment_model->update((int) $payment_id, $update);

        return $this->format_card_checkout_payload($booking, $payment_id, $meta, $invoice_id, $invoice_number, $amount);
    }

    private function ensure_pending_card_payment($booking, $invoice_id, $amount, $invoice_number = null) {
        $existing = null;
        if ($invoice_id) {
            $this->CI->db->where('invoice_id', (int) $invoice_id);
            $this->CI->db->where('payment_method', 'card');
            $this->CI->db->where('payment_status', 'pending');
            $this->CI->db->order_by('id', 'DESC');
            $existing = $this->CI->db->get('payments')->row();
        }
        if (!$existing && $booking) {
            $this->CI->db->where('booking_id', (int) $booking->id);
            $this->CI->db->where('payment_method', 'card');
            $this->CI->db->where('payment_status', 'pending');
            $this->CI->db->order_by('id', 'DESC');
            $existing = $this->CI->db->get('payments')->row();
        }

        if ($existing) {
            $update = array('amount' => $amount);
            if ($invoice_id) {
                $update['invoice_id'] = $invoice_id;
            }
            $this->CI->Payment_model->update($existing->id, $update);
            return (int) $existing->id;
        }

        $payload = array(
            'booking_id' => $booking ? (int) $booking->id : null,
            'invoice_id' => $invoice_id,
            'amount' => $amount,
            'payment_method' => 'card',
            'payment_status' => 'pending',
            'payment_date' => null,
            'notes' => json_encode(array('provider' => 'paymongo', 'method' => 'card', 'status' => 'pending'))
        );
        if ($this->CI->db->field_exists('reference_number', 'payments')) {
            $payload['reference_number'] = $booking && !empty($booking->booking_number)
                ? $booking->booking_number
                : ($invoice_number ?: null);
        }

        return (int) $this->CI->Payment_model->create($payload);
    }

    private function format_card_checkout_payload($booking, $payment_id, $meta, $invoice_id, $invoice_number, $amount) {
        return array(
            'method' => 'card',
            'provider' => 'paymongo',
            'status' => 'awaiting_payment',
            'checkout_session_id' => isset($meta['checkout_session_id']) ? $meta['checkout_session_id'] : null,
            'checkout_url' => isset($meta['checkout_url']) ? $meta['checkout_url'] : null,
            'payment_intent_id' => isset($meta['payment_intent_id']) ? $meta['payment_intent_id'] : null,
            'payment_id' => (int) $payment_id,
            'invoice_id' => $invoice_id,
            'invoice_number' => $invoice_number,
            'booking_number' => ($booking && !empty($booking->booking_number)) ? $booking->booking_number : null,
            'amount' => (float) $amount
        );
    }

    private function store_qrph_meta($payment_id, $booking, $amount, $invoice_id, $meta, $reference = null) {
        $update = array(
            'amount' => $amount,
            'payment_method' => $this->online_payment_method(),
            'payment_status' => 'pending',
            'transaction_id' => $meta['payment_intent_id'],
            'notes' => json_encode(array(
                'provider' => 'paymongo',
                'method' => 'qrph',
                'status' => 'awaiting_payment',
                'payment_intent_id' => $meta['payment_intent_id'],
                'client_key' => isset($meta['client_key']) ? $meta['client_key'] : null,
                'qr_image_url' => $meta['qr_image_url'],
                'payment_method_id' => isset($meta['payment_method_id']) ? $meta['payment_method_id'] : null,
                'expires_at' => $meta['expires_at']
            ))
        );
        if ($invoice_id) {
            $update['invoice_id'] = $invoice_id;
        }
        if ($this->CI->db->field_exists('reference_number', 'payments')) {
            if ($reference) {
                $update['reference_number'] = $reference;
            } elseif ($booking && !empty($booking->booking_number)) {
                $update['reference_number'] = $booking->booking_number;
            }
        }
        if ($this->CI->db->field_exists('paymongo_intent_id', 'payments')) {
            $update['paymongo_intent_id'] = $meta['payment_intent_id'];
        }
        if ($this->CI->db->field_exists('paymongo_client_key', 'payments')) {
            $update['paymongo_client_key'] = isset($meta['client_key']) ? $meta['client_key'] : null;
        }
        if ($this->CI->db->field_exists('qrph_expires_at', 'payments')) {
            $update['qrph_expires_at'] = $meta['expires_at'];
        }

        $this->CI->Payment_model->update((int) $payment_id, $update);
        return $meta;
    }

    private function parse_payment_meta($payment) {
        $meta = array();
        if (!$payment) {
            return $meta;
        }
        if ($this->CI->db->field_exists('paymongo_intent_id', 'payments') && !empty($payment->paymongo_intent_id)) {
            $meta['payment_intent_id'] = $payment->paymongo_intent_id;
        } elseif (!empty($payment->transaction_id) && strpos($payment->transaction_id, 'pi_') === 0) {
            $meta['payment_intent_id'] = $payment->transaction_id;
        }
        if ($this->CI->db->field_exists('paymongo_client_key', 'payments') && !empty($payment->paymongo_client_key)) {
            $meta['client_key'] = $payment->paymongo_client_key;
        }
        if ($this->CI->db->field_exists('qrph_expires_at', 'payments') && !empty($payment->qrph_expires_at)) {
            $meta['expires_at'] = $payment->qrph_expires_at;
        }

        if (!empty($payment->notes)) {
            $decoded = json_decode($payment->notes, true);
            if (is_array($decoded)) {
                $meta = array_merge($meta, $decoded);
            }
        }
        return $meta;
    }

    private function format_qrph_payload($booking, $payment_id, $meta, $invoice_id, $invoice_number, $amount) {
        $expires_at = isset($meta['expires_at']) ? $meta['expires_at'] : null;
        $can_regenerate = true;
        if ($expires_at) {
            $ts = strtotime($expires_at);
            $can_regenerate = !($ts && $ts > time());
        }

        return array(
            'method' => 'qrph',
            'provider' => 'paymongo',
            'status' => 'awaiting_payment',
            'payment_intent_id' => isset($meta['payment_intent_id']) ? $meta['payment_intent_id'] : null,
            'qr_image_url' => isset($meta['qr_image_url']) ? $meta['qr_image_url'] : null,
            'expires_at' => $expires_at,
            'payment_id' => (int) $payment_id,
            'invoice_id' => $invoice_id,
            'invoice_number' => $invoice_number,
            'booking_number' => ($booking && !empty($booking->booking_number)) ? $booking->booking_number : null,
            'amount' => (float) $amount,
            'can_regenerate' => $can_regenerate
        );
    }
}
