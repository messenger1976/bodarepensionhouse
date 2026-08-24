<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Handles automatic invoice creation when bookings are confirmed.
 */
class Billing_service {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->model('Booking_settings_model');
        $this->CI->load->model('Booking_model');
        $this->CI->load->model('Booking_item_model');
        $this->CI->load->model('Invoice_model');
        $this->CI->load->model('Invoice_item_model');
        $this->CI->load->model('Payment_model');
    }

    public function is_enabled() {
        return $this->CI->Booking_settings_model->get_setting('auto_create_invoice', '0') === '1'
            && $this->CI->db->table_exists('invoices');
    }

    public function should_issue_automatically() {
        return $this->CI->Booking_settings_model->get_setting('auto_issue_invoice', '1') === '1';
    }

    public function should_email_automatically() {
        return $this->CI->Booking_settings_model->get_setting('auto_email_invoice', '0') === '1';
    }

    /**
     * Create an invoice for a confirmed booking when auto-invoice is enabled.
     * Skips if a non-void invoice already exists for the booking.
     *
     * @return array|null ['created'=>bool, 'invoice_id'=>int, 'invoice_number'=>string, 'emailed'=>bool]
     */
    public function maybe_create_invoice_for_booking($booking_id, $admin_id = null) {
        $booking_id = (int) $booking_id;
        if ($booking_id <= 0 || !$this->is_enabled()) {
            return null;
        }

        $booking = $this->CI->Booking_model->get_booking($booking_id);
        if (!$booking || $booking->status !== 'confirmed') {
            return null;
        }

        $existing = $this->CI->Invoice_model->get_primary_for_booking($booking_id);
        if ($existing) {
            return array(
                'created' => false,
                'invoice_id' => (int) $existing->id,
                'invoice_number' => $existing->invoice_number,
                'emailed' => false,
                'skipped' => 'already_exists'
            );
        }

        $booking_items = $this->CI->db->table_exists('booking_items')
            ? $this->CI->Booking_item_model->get_booking_items($booking_id)
            : array();

        $rates = $this->CI->Invoice_model->get_default_rates();
        $items = $this->CI->Invoice_model->build_items_from_booking($booking, $booking_items);

        if (empty($items)) {
            log_message('warning', 'Auto-invoice skipped for booking #' . $booking_id . ': no line items.');
            return null;
        }

        $invoice_id = $this->CI->Invoice_model->create(array(
            'booking_id' => $booking_id,
            'guest_name' => $booking->guest_name,
            'guest_email' => $booking->guest_email,
            'guest_phone' => $booking->guest_phone,
            'tax_rate' => $rates['tax_rate'],
            'service_charge_rate' => $rates['service_charge_rate'],
            'discount_amount' => 0,
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'notes' => 'Auto-generated from booking ' . ($booking->booking_number ?: ('#' . $booking_id)),
            'status' => 'draft',
            'admin_id' => $admin_id
        ), $items);

        if (!$invoice_id) {
            log_message('error', 'Auto-invoice failed for booking #' . $booking_id);
            return null;
        }

        $invoice = $this->CI->Invoice_model->get($invoice_id);

        if ($this->should_issue_automatically()) {
            $this->CI->Invoice_model->issue($invoice_id);
            $invoice = $this->CI->Invoice_model->get($invoice_id);
        }

        $emailed = false;
        if ($this->should_email_automatically() && !empty($invoice->guest_email)) {
            $this->CI->load->library('billing_mail');
            $items_rows = $this->CI->Invoice_item_model->get_by_invoice($invoice_id);
            $payments = $this->CI->Payment_model->get_by_invoice($invoice_id);
            if ($this->CI->billing_mail->send_invoice($invoice, $items_rows, $payments)) {
                $this->CI->Invoice_model->mark_emailed($invoice_id);
                $emailed = true;
            } else {
                log_message('error', 'Auto-invoice email failed for invoice #' . $invoice_id . ': ' . $this->CI->billing_mail->get_last_error());
            }
        }

        return array(
            'created' => true,
            'invoice_id' => (int) $invoice_id,
            'invoice_number' => $invoice->invoice_number,
            'emailed' => $emailed
        );
    }

    /**
     * Resolve initial booking status from booking settings (API/public bookings).
     */
    public function resolve_initial_booking_status() {
        if ($this->CI->Booking_settings_model->get_setting('auto_confirm_bookings', '0') === '1') {
            return 'confirmed';
        }

        $default = $this->CI->Booking_settings_model->get_setting('default_status', 'pending');
        $allowed = array('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'completed');
        return in_array($default, $allowed, true) ? $default : 'pending';
    }
}
