<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Invoice_item_model');
        $this->load->model('Payment_model');
    }

    public function get_all($filters = array()) {
        $this->db->select('invoices.*, bookings.booking_number, events.event_name, events.event_number');
        $this->db->from('invoices');
        $this->db->join('bookings', 'bookings.id = invoices.booking_id', 'left');
        $this->db->join('events', 'events.id = invoices.event_id', 'left');
        $this->db->order_by('invoices.created_at', 'DESC');

        if (!empty($filters['status'])) {
            $this->db->where('invoices.status', $filters['status']);
        }
        if (!empty($filters['booking_id'])) {
            $this->db->where('invoices.booking_id', (int) $filters['booking_id']);
        }
        if (!empty($filters['event_id'])) {
            $this->db->where('invoices.event_id', (int) $filters['event_id']);
        }

        return $this->db->get()->result();
    }

    public function get($id) {
        $this->db->select('invoices.*, bookings.booking_number, bookings.guest_name as booking_guest, bookings.check_in, bookings.check_out, events.event_name, events.event_number, events.event_date');
        $this->db->from('invoices');
        $this->db->join('bookings', 'bookings.id = invoices.booking_id', 'left');
        $this->db->join('events', 'events.id = invoices.event_id', 'left');
        $this->db->where('invoices.id', (int) $id);
        return $this->db->get()->row();
    }

    public function create($data, $items = array()) {
        $this->db->trans_start();

        $this->db->insert('invoices', $data);
        $invoice_id = $this->db->insert_id();

        if (!$invoice_id) {
            $this->db->trans_complete();
            return false;
        }

        $this->update_invoice_number($invoice_id);

        foreach ($items as $item) {
            $item['invoice_id'] = $invoice_id;
            $this->Invoice_item_model->create($item);
        }

        $this->recalculate_totals($invoice_id);

        $this->db->trans_complete();
        return $this->db->trans_status() ? $invoice_id : false;
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        return $this->db->update('invoices', $data);
    }

    public function delete($id) {
        $this->db->trans_start();
        $this->Invoice_item_model->delete_by_invoice($id);
        $this->db->where('id', (int) $id);
        $this->db->delete('invoices');
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function update_invoice_number($id) {
        $number = 'INV' . str_pad($id, 8, '0', STR_PAD_LEFT);
        $this->db->where('id', (int) $id);
        $this->db->update('invoices', array('invoice_number' => $number));
        return $number;
    }

    public function recalculate_totals($invoice_id) {
        $invoice = $this->get($invoice_id);
        if (!$invoice) {
            return false;
        }

        $subtotal = $this->Invoice_item_model->get_subtotal($invoice_id);
        $tax_rate = (float) $invoice->tax_rate;
        $service_rate = (float) $invoice->service_charge_rate;
        $discount = (float) $invoice->discount_amount;

        $taxable = max(0, $subtotal - $discount);
        $tax_amount = round($taxable * ($tax_rate / 100), 2);
        $service_amount = round($taxable * ($service_rate / 100), 2);
        $total = round($taxable + $tax_amount + $service_amount, 2);

        $amount_paid = $this->Payment_model->get_total_paid_for_invoice($invoice_id);
        $balance = round(max(0, $total - $amount_paid), 2);

        $status = $invoice->status;
        if ($status !== 'void' && $status !== 'draft') {
            if ($balance <= 0 && $total > 0) {
                $status = 'paid';
            } elseif ($amount_paid > 0 && $balance > 0) {
                $status = 'partial';
            } elseif ($status === 'paid' && $balance > 0) {
                $status = 'partial';
            }
        }

        $update = array(
            'subtotal' => $subtotal,
            'tax_amount' => $tax_amount,
            'service_charge_amount' => $service_amount,
            'total_amount' => $total,
            'amount_paid' => $amount_paid,
            'balance_due' => $balance,
            'status' => $status
        );

        $this->db->where('id', (int) $invoice_id);
        $this->db->update('invoices', $update);
        return true;
    }

    public function issue($invoice_id) {
        $data = array(
            'status' => 'issued',
            'issued_at' => date('Y-m-d H:i:s')
        );
        if (empty($this->get($invoice_id)->due_date)) {
            $data['due_date'] = date('Y-m-d', strtotime('+7 days'));
        }
        return $this->update($invoice_id, $data);
    }

    public function void_invoice($invoice_id) {
        return $this->update($invoice_id, array('status' => 'void', 'balance_due' => 0));
    }

    /**
     * Primary (non-void) invoice linked to a booking, if any.
     */
    public function get_primary_for_booking($booking_id) {
        $this->db->where('booking_id', (int) $booking_id);
        $this->db->where('status !=', 'void');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('invoices')->row();
    }

    public function get_invoices_for_booking($booking_id) {
        $this->db->where('booking_id', (int) $booking_id);
        $this->db->where('status !=', 'void');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('invoices')->result();
    }

    /**
     * Summarize payment status for a booking from its non-void invoices.
     * Returns: label (paid|partial|unpaid|no_invoice), badge, totals, primary_invoice_id.
     */
    public function get_booking_payment_status($booking_id) {
        $invoices = $this->get_invoices_for_booking($booking_id);
        return $this->summarize_booking_payment_status($invoices);
    }

    /**
     * Batch payment status keyed by booking_id (for bookings list).
     */
    public function get_payment_status_map() {
        if (!$this->db->table_exists('invoices')) {
            return array();
        }

        $this->db->select('booking_id, id, invoice_number, status, amount_paid, balance_due, total_amount');
        $this->db->from('invoices');
        $this->db->where('status !=', 'void');
        $this->db->where('booking_id IS NOT NULL', null, false);
        $this->db->order_by('id', 'ASC');
        $rows = $this->db->get()->result();

        $grouped = array();
        foreach ($rows as $row) {
            $bid = (int) $row->booking_id;
            if (!isset($grouped[$bid])) {
                $grouped[$bid] = array();
            }
            $grouped[$bid][] = $row;
        }

        $map = array();
        foreach ($grouped as $bid => $invoices) {
            $map[$bid] = $this->summarize_booking_payment_status($invoices);
        }
        return $map;
    }

    private function summarize_booking_payment_status($invoices) {
        if (empty($invoices)) {
            return array(
                'label' => 'no_invoice',
                'display' => 'No Invoice',
                'badge' => 'secondary',
                'total_amount' => 0.0,
                'amount_paid' => 0.0,
                'balance' => 0.0,
                'primary_invoice_id' => null,
                'primary_invoice_number' => null,
                'invoice_count' => 0
            );
        }

        $total_amount = 0.0;
        $amount_paid = 0.0;
        $balance = 0.0;
        $primary = $invoices[0];
        foreach ($invoices as $inv) {
            $total_amount += (float) $inv->total_amount;
            $amount_paid += (float) $inv->amount_paid;
            $balance += (float) $inv->balance_due;
            if ((float) $inv->balance_due > (float) $primary->balance_due) {
                $primary = $inv;
            }
        }

        $balance = round(max(0, $balance), 2);
        $amount_paid = round($amount_paid, 2);

        if ($balance <= 0 && ($amount_paid > 0 || $total_amount <= 0)) {
            $label = 'paid';
            $display = 'Paid';
            $badge = 'success';
        } elseif ($amount_paid > 0 && $balance > 0) {
            $label = 'partial';
            $display = 'Partial';
            $badge = 'warning';
        } else {
            $label = 'unpaid';
            $display = 'Unpaid';
            $badge = 'danger';
        }

        return array(
            'label' => $label,
            'display' => $display,
            'badge' => $badge,
            'total_amount' => round($total_amount, 2),
            'amount_paid' => $amount_paid,
            'balance' => $balance,
            'primary_invoice_id' => isset($primary->id) ? (int) $primary->id : null,
            'primary_invoice_number' => isset($primary->invoice_number) ? $primary->invoice_number : null,
            'invoice_count' => count($invoices)
        );
    }

    /**
     * Build invoice line items from a booking (rooms + extra services).
     */
    public function build_items_from_booking($booking, $booking_items = array()) {
        $items = array();

        if (!empty($booking_items)) {
            foreach ($booking_items as $bi) {
                $nights = 1;
                if (!empty($bi->check_in) && !empty($bi->check_out)) {
                    $ci = new DateTime($bi->check_in);
                    $co = new DateTime($bi->check_out);
                    $nights = max(1, (int) $ci->diff($co)->days);
                }
                $room_label = isset($bi->room_name) ? $bi->room_name : 'Room';
                $items[] = array(
                    'item_type' => 'room',
                    'description' => $room_label . ' (' . $nights . ' night' . ($nights > 1 ? 's' : '') . ')',
                    'quantity' => $nights,
                    'unit_price' => isset($bi->price_per_night) ? (float) $bi->price_per_night : (isset($bi->room_price) ? (float) $bi->room_price : 0),
                    'booking_item_id' => isset($bi->id) ? $bi->id : null,
                    'room_id' => isset($bi->room_id) ? $bi->room_id : null
                );
            }
        } elseif ($booking) {
            $nights = 1;
            if (!empty($booking->check_in) && !empty($booking->check_out)) {
                $ci = new DateTime($booking->check_in);
                $co = new DateTime($booking->check_out);
                $nights = max(1, (int) $ci->diff($co)->days);
            }
            $room_label = isset($booking->room_name) ? $booking->room_name : 'Room Booking';
            $unit = isset($booking->price) ? (float) $booking->price : 0;
            if ($unit <= 0 && isset($booking->total_amount)) {
                $unit = round((float) $booking->total_amount / $nights, 2);
            }
            $items[] = array(
                'item_type' => 'room',
                'description' => $room_label . ' (' . $nights . ' night' . ($nights > 1 ? 's' : '') . ')',
                'quantity' => $nights,
                'unit_price' => $unit,
                'room_id' => isset($booking->room_id) ? $booking->room_id : null
            );
        }

        if ($booking && !empty($booking->extra_services)) {
            $services = is_array($booking->extra_services)
                ? $booking->extra_services
                : json_decode($booking->extra_services, true);
            if (is_array($services)) {
                foreach ($services as $svc) {
                    if (!is_array($svc) || empty($svc['name'])) {
                        continue;
                    }
                    $items[] = array(
                        'item_type' => 'extra_service',
                        'description' => $svc['name'],
                        'quantity' => 1,
                        'unit_price' => isset($svc['cost']) ? (float) $svc['cost'] : 0
                    );
                }
            }
        }

        return $items;
    }

    public function get_default_rates() {
        $tax = 0;
        $service = 0;
        if ($this->db->table_exists('booking_settings')) {
            $row = $this->db->get_where('booking_settings', array('setting_key' => 'tax_rate'))->row();
            if ($row) {
                $tax = (float) $row->setting_value;
            }
            $row = $this->db->get_where('booking_settings', array('setting_key' => 'service_charge'))->row();
            if ($row) {
                $service = (float) $row->setting_value;
            }
        }
        return array('tax_rate' => $tax, 'service_charge_rate' => $service);
    }

    public function get_by_number($invoice_number) {
        $this->db->where('invoice_number', $invoice_number);
        return $this->db->get('invoices')->row();
    }

    /**
     * Invoices visible to a logged-in customer.
     */
    public function get_invoices_for_user($user_id, $user_email) {
        $this->db->select('invoices.*, bookings.booking_number, events.event_name');
        $this->db->from('invoices');
        $this->db->join('bookings', 'bookings.id = invoices.booking_id', 'left');
        $this->db->join('events', 'events.id = invoices.event_id', 'left');
        $this->db->where_not_in('invoices.status', array('draft', 'void'));
        $this->db->group_start();
        $this->db->where('bookings.user_id', (int) $user_id);
        if ($user_email) {
            $this->db->or_where('LOWER(invoices.guest_email)', strtolower(trim($user_email)));
        }
        $this->db->group_end();
        $this->db->order_by('invoices.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function user_can_access($invoice, $user_id, $user_email) {
        if (!$invoice) {
            return false;
        }

        if ($invoice->booking_id) {
            $booking = $this->db->get_where('bookings', array('id' => (int) $invoice->booking_id))->row();
            if ($booking && (int) $booking->user_id === (int) $user_id) {
                return true;
            }
        }

        if ($user_email && $invoice->guest_email
            && strtolower(trim($invoice->guest_email)) === strtolower(trim($user_email))) {
            return true;
        }

        return false;
    }

    public function format_for_api($invoice, $detailed = false) {
        $data = array(
            'id' => (int) $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'guest_name' => $invoice->guest_name,
            'status' => $invoice->status,
            'subtotal' => (float) $invoice->subtotal,
            'tax_amount' => (float) $invoice->tax_amount,
            'service_charge_amount' => (float) $invoice->service_charge_amount,
            'discount_amount' => (float) $invoice->discount_amount,
            'total_amount' => (float) $invoice->total_amount,
            'amount_paid' => (float) $invoice->amount_paid,
            'balance_due' => (float) $invoice->balance_due,
            'due_date' => $invoice->due_date,
            'issued_at' => $invoice->issued_at,
            'booking_number' => isset($invoice->booking_number) ? $invoice->booking_number : null,
            'event_name' => isset($invoice->event_name) ? $invoice->event_name : null,
            'created_at' => $invoice->created_at
        );

        if ($detailed) {
            $data['guest_email'] = $invoice->guest_email;
            $data['guest_phone'] = $invoice->guest_phone;
            $data['tax_rate'] = (float) $invoice->tax_rate;
            $data['service_charge_rate'] = (float) $invoice->service_charge_rate;
            $data['notes'] = $invoice->notes;
        }

        return $data;
    }

    public function format_items_for_api($items) {
        $result = array();
        foreach ((array) $items as $item) {
            $result[] = array(
                'id' => (int) $item->id,
                'item_type' => $item->item_type,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price
            );
        }
        return $result;
    }

    public function format_payments_for_api($payments) {
        $result = array();
        foreach ((array) $payments as $p) {
            $result[] = array(
                'id' => (int) $p->id,
                'amount' => (float) $p->amount,
                'payment_method' => $p->payment_method,
                'payment_status' => $p->payment_status,
                'payment_date' => $p->payment_date,
                'reference_number' => isset($p->reference_number) ? $p->reference_number : null
            );
        }
        return $result;
    }

    public function mark_emailed($invoice_id) {
        if (!$this->db->field_exists('emailed_at', 'invoices')) {
            return true;
        }
        $this->db->where('id', (int) $invoice_id);
        return $this->db->update('invoices', array('emailed_at' => date('Y-m-d H:i:s')));
    }

    /**
     * Invoices for reporting within a date range (by issued_at / created_at).
     */
    public function get_for_date_range($from_date, $to_date, $status = null) {
        $from_date = $this->db->escape($from_date);
        $to_date = $this->db->escape($to_date);

        $this->db->select('invoices.*, bookings.booking_number, events.event_name, events.event_number');
        $this->db->from('invoices');
        $this->db->join('bookings', 'bookings.id = invoices.booking_id', 'left');
        $this->db->join('events', 'events.id = invoices.event_id', 'left');
        $this->db->where("DATE(COALESCE(invoices.issued_at, invoices.created_at)) >= {$from_date}", NULL, FALSE);
        $this->db->where("DATE(COALESCE(invoices.issued_at, invoices.created_at)) <= {$to_date}", NULL, FALSE);
        $this->db->where('invoices.status !=', 'void');
        if ($status) {
            $this->db->where('invoices.status', $status);
        }
        $this->db->order_by('invoices.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_summary_for_range($from_date, $to_date) {
        $from_date = $this->db->escape($from_date);
        $to_date = $this->db->escape($to_date);

        $this->db->select("
            COUNT(id) as invoice_count,
            COALESCE(SUM(total_amount), 0) as total_billed,
            COALESCE(SUM(amount_paid), 0) as total_paid,
            COALESCE(SUM(balance_due), 0) as total_balance,
            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END) as partial_count,
            SUM(CASE WHEN status IN ('issued','overdue') THEN 1 ELSE 0 END) as unpaid_count,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count
        ", FALSE);
        $this->db->from('invoices');
        $this->db->where("DATE(COALESCE(issued_at, created_at)) >= {$from_date}", NULL, FALSE);
        $this->db->where("DATE(COALESCE(issued_at, created_at)) <= {$to_date}", NULL, FALSE);
        $this->db->where('status !=', 'void');
        $row = $this->db->get()->row();
        return array(
            'invoice_count' => $row ? (int) $row->invoice_count : 0,
            'total_billed' => $row ? (float) $row->total_billed : 0.0,
            'total_paid' => $row ? (float) $row->total_paid : 0.0,
            'total_balance' => $row ? (float) $row->total_balance : 0.0,
            'paid_count' => $row ? (int) $row->paid_count : 0,
            'partial_count' => $row ? (int) $row->partial_count : 0,
            'unpaid_count' => $row ? (int) $row->unpaid_count : 0,
            'draft_count' => $row ? (int) $row->draft_count : 0
        );
    }

    public function get_outstanding($limit = 50) {
        $this->db->select('invoices.*, bookings.booking_number, events.event_name');
        $this->db->from('invoices');
        $this->db->join('bookings', 'bookings.id = invoices.booking_id', 'left');
        $this->db->join('events', 'events.id = invoices.event_id', 'left');
        $this->db->where('invoices.balance_due >', 0);
        $this->db->where_not_in('invoices.status', array('void', 'draft', 'paid'));
        $this->db->order_by('invoices.due_date', 'ASC');
        $this->db->order_by('invoices.balance_due', 'DESC');
        if ($limit) {
            $this->db->limit((int) $limit);
        }
        return $this->db->get()->result();
    }

    public function get_totals_by_item_type($from_date, $to_date) {
        if (!$this->db->table_exists('invoice_items')) {
            return array();
        }
        $from_date = $this->db->escape($from_date);
        $to_date = $this->db->escape($to_date);

        $this->db->select('invoice_items.item_type, COUNT(invoice_items.id) as item_count, SUM(invoice_items.total_price) as total_amount');
        $this->db->from('invoice_items');
        $this->db->join('invoices', 'invoices.id = invoice_items.invoice_id', 'inner');
        $this->db->where("DATE(COALESCE(invoices.issued_at, invoices.created_at)) >= {$from_date}", NULL, FALSE);
        $this->db->where("DATE(COALESCE(invoices.issued_at, invoices.created_at)) <= {$to_date}", NULL, FALSE);
        $this->db->where('invoices.status !=', 'void');
        $this->db->group_by('invoice_items.item_type');
        $this->db->order_by('total_amount', 'DESC');
        return $this->db->get()->result();
    }
}
