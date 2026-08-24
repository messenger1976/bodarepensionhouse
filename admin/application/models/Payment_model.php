<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($filters = array()) {
        $this->db->select('payments.*, bookings.booking_number, bookings.guest_name, invoices.invoice_number');
        $this->db->from('payments');
        $this->db->join('bookings', 'bookings.id = payments.booking_id', 'left');
        if ($this->db->table_exists('invoices')) {
            $this->db->join('invoices', 'invoices.id = payments.invoice_id', 'left');
        }
        $this->db->order_by('payments.created_at', 'DESC');

        if (!empty($filters['status'])) {
            $this->db->where('payments.payment_status', $filters['status']);
        }
        if (!empty($filters['invoice_id'])) {
            $this->db->where('payments.invoice_id', (int) $filters['invoice_id']);
        }
        if (!empty($filters['booking_id'])) {
            $this->db->where('payments.booking_id', (int) $filters['booking_id']);
        }

        return $this->db->get()->result();
    }

    public function get($id) {
        $this->db->select('payments.*, bookings.booking_number, bookings.guest_name, invoices.invoice_number');
        $this->db->from('payments');
        $this->db->join('bookings', 'bookings.id = payments.booking_id', 'left');
        if ($this->db->table_exists('invoices')) {
            $this->db->join('invoices', 'invoices.id = payments.invoice_id', 'left');
        }
        $this->db->where('payments.id', (int) $id);
        return $this->db->get()->row();
    }

    public function get_by_invoice($invoice_id) {
        $this->db->where('invoice_id', (int) $invoice_id);
        $this->db->order_by('payment_date', 'DESC');
        return $this->db->get('payments')->result();
    }

    public function get_by_booking($booking_id) {
        $this->db->where('booking_id', (int) $booking_id);
        $this->db->order_by('payment_date', 'DESC');
        return $this->db->get('payments')->result();
    }

    public function get_total_paid_for_invoice($invoice_id) {
        $this->db->select_sum('amount');
        $this->db->where('invoice_id', (int) $invoice_id);
        $this->db->where('payment_status', 'paid');
        $row = $this->db->get('payments')->row();
        return $row && $row->amount ? (float) $row->amount : 0.0;
    }

    public function create($data) {
        if (empty($data['payment_date'])) {
            $data['payment_date'] = date('Y-m-d H:i:s');
        }
        $this->db->insert('payments', $data);
        if ($this->db->error()['code'] != 0) {
            return false;
        }
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        return $this->db->update('payments', $data);
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('payments');
    }

    /**
     * Payments for a date range (by payment_date, fallback created_at).
     */
    public function get_for_date_range($from_date, $to_date, $status = null) {
        $from_date = $this->db->escape($from_date);
        $to_date = $this->db->escape($to_date);

        $this->db->select('payments.*, bookings.booking_number, bookings.guest_name, invoices.invoice_number');
        $this->db->from('payments');
        $this->db->join('bookings', 'bookings.id = payments.booking_id', 'left');
        if ($this->db->table_exists('invoices')) {
            $this->db->join('invoices', 'invoices.id = payments.invoice_id', 'left');
        }
        $this->db->where("DATE(COALESCE(payments.payment_date, payments.created_at)) >= {$from_date}", NULL, FALSE);
        $this->db->where("DATE(COALESCE(payments.payment_date, payments.created_at)) <= {$to_date}", NULL, FALSE);
        if ($status) {
            $this->db->where('payments.payment_status', $status);
        }
        $this->db->order_by('payments.payment_date', 'DESC');
        $this->db->order_by('payments.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_total_collected($from_date, $to_date) {
        $from_date = $this->db->escape($from_date);
        $to_date = $this->db->escape($to_date);

        $this->db->select_sum('amount');
        $this->db->where('payment_status', 'paid');
        $this->db->where("DATE(COALESCE(payment_date, created_at)) >= {$from_date}", NULL, FALSE);
        $this->db->where("DATE(COALESCE(payment_date, created_at)) <= {$to_date}", NULL, FALSE);
        $row = $this->db->get('payments')->row();
        return $row && $row->amount ? (float) $row->amount : 0.0;
    }

    public function get_totals_by_method($from_date, $to_date) {
        $from_date = $this->db->escape($from_date);
        $to_date = $this->db->escape($to_date);

        $this->db->select('payment_method, COUNT(id) as payment_count, SUM(amount) as total_amount');
        $this->db->from('payments');
        $this->db->where('payment_status', 'paid');
        $this->db->where("DATE(COALESCE(payment_date, created_at)) >= {$from_date}", NULL, FALSE);
        $this->db->where("DATE(COALESCE(payment_date, created_at)) <= {$to_date}", NULL, FALSE);
        $this->db->group_by('payment_method');
        $this->db->order_by('total_amount', 'DESC');
        return $this->db->get()->result();
    }

    public function get_daily_collected($date) {
        return $this->get_total_collected($date, $date);
    }
}
