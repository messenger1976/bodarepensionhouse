<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Business logic for PayMongo GCash checkout tied to bookings/payments.
 */
class Paymongo_service {

    protected $CI;
    protected $last_error = '';

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

    public function start_gcash_checkout_for_booking($booking, $amount = null) {
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
        if ($amount <= 0) {
            $this->last_error = 'Booking total is invalid for online payment.';
            return false;
        }

        $site = rtrim($this->CI->paymongo->get_public_site_base_url(), '/');
        $booking_number = $booking->booking_number;
        $payment_id = $this->ensure_pending_gcash_payment($booking, $amount);

        $success_url = $site . '/booking-confirmation.php?booking=' . rawurlencode($booking_number)
            . '&payment=success';
        $cancel_url = $site . '/checkout.php?payment=cancelled&booking=' . rawurlencode($booking_number);

        $session = $this->CI->paymongo->create_checkout_session(array(
            'name' => 'BODARE Reservation ' . $booking_number,
            'description' => 'Room reservation payment for booking ' . $booking_number,
            'amount_php' => $amount,
            'reference_number' => $booking_number,
            'payment_method_types' => array('gcash'),
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
            'send_email_receipt' => true,
            'metadata' => array(
                'booking_number' => $booking_number,
                'booking_id' => (string) $booking->id,
                'payment_id' => (string) $payment_id
            ),
            'billing' => array(
                'name' => $booking->guest_name,
                'email' => $booking->guest_email,
                'phone' => $booking->guest_phone
            )
        ));

        if (!$session || empty($session['checkout_url'])) {
            $this->last_error = $this->CI->paymongo->get_last_error() ?: 'PayMongo did not return a checkout URL.';
            return false;
        }

        $update = array(
            'transaction_id' => $session['id'],
            'payment_status' => 'pending',
            'payment_method' => 'gcash',
            'notes' => 'PayMongo GCash checkout initiated. Session: ' . $session['id']
        );
        if ($this->CI->db->field_exists('reference_number', 'payments')) {
            $update['reference_number'] = $booking_number;
        }
        $this->CI->Payment_model->update($payment_id, $update);

        return array(
            'checkout_url' => $session['checkout_url'],
            'checkout_session_id' => $session['id'],
            'payment_id' => $payment_id
        );
    }

    public function fulfill_paid_session($booking, $session, $session_id) {
        if (!$booking || !$this->CI->db->table_exists('payments')) {
            return false;
        }

        if ($this->find_paid_payment((int) $booking->id)) {
            return true;
        }

        $paymongo_payment_id = $this->CI->paymongo->get_session_payment_id($session);
        $amount = (float) $booking->total_amount;
        $attrs = isset($session['attributes']) ? $session['attributes'] : array();
        if (!empty($attrs['payments'][0]['attributes']['amount'])) {
            $amount = ((float) $attrs['payments'][0]['attributes']['amount']) / 100;
        } elseif (!empty($attrs['line_items'][0]['amount'])) {
            $amount = ((float) $attrs['line_items'][0]['amount']) / 100;
        }

        $payment_row = null;
        if ($session_id) {
            $this->CI->db->where('transaction_id', $session_id);
            $this->CI->db->where('booking_id', (int) $booking->id);
            $payment_row = $this->CI->db->get('payments')->row();
        }
        if (!$payment_row) {
            $payment_row = $this->find_latest_gcash_payment((int) $booking->id);
        }

        $invoice_id = $payment_row && !empty($payment_row->invoice_id) ? (int) $payment_row->invoice_id : null;
        if (!$invoice_id && $this->CI->db->table_exists('invoices')) {
            $primary = $this->CI->Invoice_model->get_primary_for_booking((int) $booking->id);
            if ($primary) {
                $invoice_id = (int) $primary->id;
            }
        }

        $update = array(
            'amount' => $amount,
            'payment_method' => 'gcash',
            'payment_status' => 'paid',
            'payment_date' => date('Y-m-d H:i:s'),
            'transaction_id' => $session_id ?: ($paymongo_payment_id ?: null),
            'notes' => 'Paid via PayMongo GCash'
                . ($paymongo_payment_id ? (' | Payment: ' . $paymongo_payment_id) : '')
        );
        if ($invoice_id) {
            $update['invoice_id'] = $invoice_id;
        }
        if ($this->CI->db->field_exists('reference_number', 'payments')) {
            $update['reference_number'] = $booking->booking_number;
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
            $this->CI->Booking_model->update_booking((int) $booking->id, array('status' => 'confirmed'));
            $this->CI->billing_service->maybe_create_invoice_for_booking((int) $booking->id, null);

            if (!$invoice_id && $this->CI->db->table_exists('invoices')) {
                $primary = $this->CI->Invoice_model->get_primary_for_booking((int) $booking->id);
                if ($primary) {
                    $payment_after = $this->find_paid_payment((int) $booking->id);
                    if ($payment_after) {
                        $this->CI->Payment_model->update((int) $payment_after->id, array('invoice_id' => (int) $primary->id));
                        $this->CI->Invoice_model->recalculate_totals((int) $primary->id);
                    }
                }
            }
        }

        return true;
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

    public function find_latest_gcash_payment($booking_id) {
        if (!$this->CI->db->table_exists('payments')) {
            return null;
        }
        $this->CI->db->where('booking_id', (int) $booking_id);
        $this->CI->db->where('payment_method', 'gcash');
        $this->CI->db->order_by('id', 'DESC');
        return $this->CI->db->get('payments')->row();
    }

    private function ensure_pending_gcash_payment($booking, $amount) {
        $existing = $this->find_latest_gcash_payment((int) $booking->id);
        if ($existing && $existing->payment_status === 'pending') {
            $this->CI->Payment_model->update($existing->id, array('amount' => $amount));
            return (int) $existing->id;
        }

        $invoice_id = null;
        if ($this->CI->db->table_exists('invoices')) {
            $primary = $this->CI->Invoice_model->get_primary_for_booking((int) $booking->id);
            if ($primary) {
                $invoice_id = (int) $primary->id;
            }
        }

        $payload = array(
            'booking_id' => (int) $booking->id,
            'invoice_id' => $invoice_id,
            'amount' => $amount,
            'payment_method' => 'gcash',
            'payment_status' => 'pending',
            'payment_date' => null,
            'notes' => 'Awaiting PayMongo GCash payment'
        );
        if ($this->CI->db->field_exists('reference_number', 'payments')) {
            $payload['reference_number'] = $booking->booking_number;
        }

        return (int) $this->CI->Payment_model->create($payload);
    }
}
