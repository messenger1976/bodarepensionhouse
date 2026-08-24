<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Reports extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Booking_model');
        $this->load->model('Room_model');
        if ($this->db->table_exists('invoices')) {
            $this->load->model('Invoice_model');
        }
        if ($this->db->table_exists('payments')) {
            $this->load->model('Payment_model');
        }
        if ($this->db->table_exists('events')) {
            $this->load->model('Event_model');
        }
    }

    private function require_reports_access() {
        if ($this->db->table_exists('permissions')) {
            $this->require_permission('view_reports');
        } elseif (!$this->is_super_admin()) {
            $this->session->set_flashdata('error', 'You do not have permission to access this page.');
            redirect('dashboard');
        }
    }

    private function parse_date_range() {
        $from = $this->input->get('from_date');
        $to = $this->input->get('to_date');
        $date = $this->input->get('date');

        if (!$from && !$to && $date) {
            $from = $date;
            $to = $date;
        }
        if (!$from) {
            $from = date('Y-m-d');
        }
        if (!$to) {
            $to = $from;
        }
        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }
        return array($from, $to);
    }

    public function index() {
        redirect('reports/daily_sales');
    }

    /**
     * Daily Sales Report — bookings + billing snapshot for the day
     */
    public function daily_sales() {
        $this->require_reports_access();

        $date = $this->input->get('date') ? $this->input->get('date') : date('Y-m-d');
        $data['title'] = 'Daily Sales Report';
        $data['selected_date'] = $date;

        $data['daily_sales'] = $this->Booking_model->get_daily_sales($date);
        $data['total_revenue'] = $this->Booking_model->get_daily_total_revenue($date);
        $data['total_bookings'] = $this->Booking_model->get_daily_bookings_count($date);
        $data['confirmed_bookings'] = $this->Booking_model->get_daily_confirmed_bookings_count($date);
        $data['pending_bookings'] = $this->Booking_model->get_daily_pending_bookings_count($date);
        $data['sales_by_room'] = $this->Booking_model->get_daily_sales_by_room($date);

        $data['billing_enabled'] = $this->db->table_exists('invoices') && $this->db->table_exists('payments');
        $data['payments_collected'] = 0;
        $data['payments_by_method'] = array();
        $data['invoice_summary'] = array(
            'invoice_count' => 0,
            'total_billed' => 0,
            'total_paid' => 0,
            'total_balance' => 0
        );
        $data['day_payments'] = array();
        $data['day_invoices'] = array();

        if ($data['billing_enabled']) {
            $data['payments_collected'] = $this->Payment_model->get_daily_collected($date);
            $data['payments_by_method'] = $this->Payment_model->get_totals_by_method($date, $date);
            $data['invoice_summary'] = $this->Invoice_model->get_summary_for_range($date, $date);
            $data['day_payments'] = $this->Payment_model->get_for_date_range($date, $date, 'paid');
            $data['day_invoices'] = $this->Invoice_model->get_for_date_range($date, $date);
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/daily_sales', $data);
        $this->load->view('admin/layout/footer');
    }

    /**
     * Billing & Collections Report (date range)
     */
    public function billing() {
        $this->require_reports_access();

        if (!$this->db->table_exists('invoices')) {
            $this->session->set_flashdata('error', 'Billing module is not installed. Run create_billing_module.sql first.');
            redirect('reports/daily_sales');
            return;
        }

        list($from, $to) = $this->parse_date_range();
        $data['title'] = 'Billing & Collections Report';
        $data['from_date'] = $from;
        $data['to_date'] = $to;

        $data['invoice_summary'] = $this->Invoice_model->get_summary_for_range($from, $to);
        $data['invoices'] = $this->Invoice_model->get_for_date_range($from, $to);
        $data['item_totals'] = $this->Invoice_model->get_totals_by_item_type($from, $to);
        $data['outstanding'] = $this->Invoice_model->get_outstanding(25);

        $data['payments_collected'] = 0;
        $data['payments_by_method'] = array();
        $data['payments'] = array();
        if ($this->db->table_exists('payments')) {
            $data['payments_collected'] = $this->Payment_model->get_total_collected($from, $to);
            $data['payments_by_method'] = $this->Payment_model->get_totals_by_method($from, $to);
            $data['payments'] = $this->Payment_model->get_for_date_range($from, $to, 'paid');
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/billing', $data);
        $this->load->view('admin/layout/footer');
    }

    /**
     * Payments Report (date range)
     */
    public function payments() {
        $this->require_reports_access();

        if (!$this->db->table_exists('payments')) {
            $this->session->set_flashdata('error', 'Payments table is not available.');
            redirect('reports/daily_sales');
            return;
        }

        list($from, $to) = $this->parse_date_range();
        $status = $this->input->get('status');

        $data['title'] = 'Payments Report';
        $data['from_date'] = $from;
        $data['to_date'] = $to;
        $data['filter_status'] = $status;
        $data['payments'] = $this->Payment_model->get_for_date_range($from, $to, $status ?: null);
        $data['payments_collected'] = $this->Payment_model->get_total_collected($from, $to);
        $data['payments_by_method'] = $this->Payment_model->get_totals_by_method($from, $to);
        $data['payment_count'] = count($data['payments']);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/payments', $data);
        $this->load->view('admin/layout/footer');
    }

    /**
     * Events Revenue Report
     */
    public function events() {
        $this->require_reports_access();

        if (!$this->db->table_exists('events')) {
            $this->session->set_flashdata('error', 'Events module is not installed.');
            redirect('reports/daily_sales');
            return;
        }

        list($from, $to) = $this->parse_date_range();
        if (!$this->input->get('from_date') && !$this->input->get('to_date') && !$this->input->get('date')) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
        }

        $data['title'] = 'Events Revenue Report';
        $data['from_date'] = $from;
        $data['to_date'] = $to;
        $data['summary'] = $this->Event_model->get_summary_for_range($from, $to);
        $data['events'] = $this->Event_model->get_for_date_range($from, $to);
        $data['by_type'] = $this->Event_model->get_totals_by_type($from, $to);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/events', $data);
        $this->load->view('admin/layout/footer');
    }

    /**
     * Export Daily Sales Report to Excel (.xls)
     */
    public function export_excel() {
        $this->require_reports_access();

        $date = $this->input->get('date') ? $this->input->get('date') : date('Y-m-d');

        $daily_sales = $this->Booking_model->get_daily_sales($date);
        $total_revenue = $this->Booking_model->get_daily_total_revenue($date);
        $total_bookings = $this->Booking_model->get_daily_bookings_count($date);
        $confirmed_bookings = $this->Booking_model->get_daily_confirmed_bookings_count($date);
        $pending_bookings = $this->Booking_model->get_daily_pending_bookings_count($date);
        $sales_by_room = $this->Booking_model->get_daily_sales_by_room($date);

        $payments_collected = 0;
        $invoice_summary = null;
        $payments_by_method = array();
        if ($this->db->table_exists('payments') && $this->db->table_exists('invoices')) {
            $payments_collected = $this->Payment_model->get_daily_collected($date);
            $invoice_summary = $this->Invoice_model->get_summary_for_range($date, $date);
            $payments_by_method = $this->Payment_model->get_totals_by_method($date, $date);
        }

        $filename = 'Daily_Sales_Report_' . date('Y-m-d', strtotime($date)) . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $e = function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };

        echo "\xEF\xBB\xBF";
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>';
        echo '<table border="1">';

        echo '<tr><td colspan="12"><b>BODARE PENSION HOUSE</b></td></tr>';
        echo '<tr><td colspan="12">Daily Sales Report - ' . $e(date('F d, Y', strtotime($date))) . '</td></tr>';
        echo '<tr><td colspan="12">Generated on: ' . $e(date('F d, Y h:i A')) . '</td></tr>';
        echo '<tr><td colspan="12"></td></tr>';

        echo '<tr><td colspan="12"><b>BOOKING SUMMARY</b></td></tr>';
        echo '<tr><td><b>Total Revenue</b></td><td><b>Total Bookings</b></td><td><b>Confirmed</b></td><td><b>Pending</b></td></tr>';
        echo '<tr>';
        echo '<td>PHP ' . $e(number_format($total_revenue, 2)) . '</td>';
        echo '<td>' . $e($total_bookings) . '</td>';
        echo '<td>' . $e($confirmed_bookings) . '</td>';
        echo '<td>' . $e($pending_bookings) . '</td>';
        echo '</tr>';
        echo '<tr><td colspan="12"></td></tr>';

        if ($invoice_summary) {
            echo '<tr><td colspan="12"><b>BILLING SUMMARY</b></td></tr>';
            echo '<tr><td><b>Payments Collected</b></td><td><b>Invoices Issued</b></td><td><b>Amount Billed</b></td><td><b>Invoice Balance Due</b></td></tr>';
            echo '<tr>';
            echo '<td>PHP ' . $e(number_format($payments_collected, 2)) . '</td>';
            echo '<td>' . $e($invoice_summary['invoice_count']) . '</td>';
            echo '<td>PHP ' . $e(number_format($invoice_summary['total_billed'], 2)) . '</td>';
            echo '<td>PHP ' . $e(number_format($invoice_summary['total_balance'], 2)) . '</td>';
            echo '</tr>';
            echo '<tr><td colspan="12"></td></tr>';

            if (!empty($payments_by_method)) {
                echo '<tr><td colspan="12"><b>PAYMENTS BY METHOD</b></td></tr>';
                echo '<tr><td><b>Method</b></td><td><b>Count</b></td><td><b>Amount</b></td></tr>';
                foreach ($payments_by_method as $row) {
                    echo '<tr>';
                    echo '<td>' . $e(ucfirst(str_replace('_', ' ', $row->payment_method))) . '</td>';
                    echo '<td>' . $e($row->payment_count) . '</td>';
                    echo '<td>PHP ' . $e(number_format($row->total_amount, 2)) . '</td>';
                    echo '</tr>';
                }
                echo '<tr><td colspan="12"></td></tr>';
            }
        }

        if (!empty($sales_by_room)) {
            echo '<tr><td colspan="12"><b>SALES BY ROOM TYPE</b></td></tr>';
            echo '<tr><td><b>Room Type</b></td><td><b>Room Name</b></td><td><b>Number of Bookings</b></td><td><b>Total Revenue</b></td></tr>';
            foreach ($sales_by_room as $room_sale) {
                echo '<tr>';
                echo '<td>' . $e($room_sale->room_type) . '</td>';
                echo '<td>' . $e($room_sale->room_name) . '</td>';
                echo '<td>' . $e($room_sale->booking_count) . '</td>';
                echo '<td>PHP ' . $e(number_format($room_sale->total_revenue, 2)) . '</td>';
                echo '</tr>';
            }
            echo '<tr><td colspan="12"></td></tr>';
        }

        echo '<tr><td colspan="12"><b>DETAILED BOOKINGS</b></td></tr>';
        echo '<tr>';
        foreach (array(
            'Booking Number', 'Guest Name', 'Email', 'Phone', 'Room', 'Room Code',
            'Check-In', 'Check-Out', 'Guests', 'Status', 'Amount', 'Created At'
        ) as $header) {
            echo '<td><b>' . $e($header) . '</b></td>';
        }
        echo '</tr>';

        if (!empty($daily_sales)) {
            foreach ($daily_sales as $booking) {
                $booking_number = isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT);
                echo '<tr>';
                echo '<td>' . $e($booking_number) . '</td>';
                echo '<td>' . $e($booking->guest_name) . '</td>';
                echo '<td>' . $e($booking->guest_email) . '</td>';
                echo '<td>' . $e($booking->guest_phone) . '</td>';
                echo '<td>' . $e($booking->room_name) . '</td>';
                echo '<td>' . $e(isset($booking->room_code) ? $booking->room_code : '') . '</td>';
                echo '<td>' . $e(date('M d, Y', strtotime($booking->check_in))) . '</td>';
                echo '<td>' . $e(date('M d, Y', strtotime($booking->check_out))) . '</td>';
                echo '<td>' . $e($booking->guests) . '</td>';
                echo '<td>' . $e(ucfirst($booking->status)) . '</td>';
                echo '<td>PHP ' . $e(number_format($booking->total_amount, 2)) . '</td>';
                echo '<td>' . $e(date('M d, Y h:i A', strtotime($booking->created_at))) . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="12">No bookings found for this date</td></tr>';
        }

        echo '<tr><td colspan="12"></td></tr>';
        echo '<tr><td colspan="10"><b>TOTAL BOOKING REVENUE</b></td><td colspan="2"><b>PHP ' . $e(number_format($total_revenue, 2)) . '</b></td></tr>';
        if ($invoice_summary) {
            echo '<tr><td colspan="10"><b>TOTAL PAYMENTS COLLECTED</b></td><td colspan="2"><b>PHP ' . $e(number_format($payments_collected, 2)) . '</b></td></tr>';
        }

        echo '</table></body></html>';
        exit;
    }

    /**
     * Export Billing Report CSV
     */
    public function export_billing() {
        $this->require_reports_access();
        if (!$this->db->table_exists('invoices')) {
            redirect('reports/daily_sales');
            return;
        }

        list($from, $to) = $this->parse_date_range();
        $summary = $this->Invoice_model->get_summary_for_range($from, $to);
        $invoices = $this->Invoice_model->get_for_date_range($from, $to);
        $payments_collected = $this->db->table_exists('payments')
            ? $this->Payment_model->get_total_collected($from, $to)
            : 0;
        $by_method = $this->db->table_exists('payments')
            ? $this->Payment_model->get_totals_by_method($from, $to)
            : array();
        $item_totals = $this->Invoice_model->get_totals_by_item_type($from, $to);

        $filename = 'Billing_Report_' . $from . '_to_' . $to . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, array('BODARE PENSION HOUSE'));
        fputcsv($output, array('Billing & Collections Report'));
        fputcsv($output, array('Period: ' . date('F d, Y', strtotime($from)) . ' - ' . date('F d, Y', strtotime($to))));
        fputcsv($output, array('Generated on: ' . date('F d, Y h:i A')));
        fputcsv($output, array(''));

        fputcsv($output, array('SUMMARY'));
        fputcsv($output, array('Invoices', 'Amount Billed', 'Amount Paid on Invoices', 'Balance Due', 'Cash Collected (Payments)'));
        fputcsv($output, array(
            $summary['invoice_count'],
            'PHP ' . number_format($summary['total_billed'], 2),
            'PHP ' . number_format($summary['total_paid'], 2),
            'PHP ' . number_format($summary['total_balance'], 2),
            'PHP ' . number_format($payments_collected, 2)
        ));
        fputcsv($output, array(''));

        if (!empty($by_method)) {
            fputcsv($output, array('COLLECTIONS BY PAYMENT METHOD'));
            fputcsv($output, array('Method', 'Count', 'Amount'));
            foreach ($by_method as $row) {
                fputcsv($output, array(
                    ucfirst(str_replace('_', ' ', $row->payment_method)),
                    $row->payment_count,
                    'PHP ' . number_format($row->total_amount, 2)
                ));
            }
            fputcsv($output, array(''));
        }

        if (!empty($item_totals)) {
            fputcsv($output, array('BILLED BY CHARGE TYPE'));
            fputcsv($output, array('Item Type', 'Line Items', 'Amount'));
            foreach ($item_totals as $row) {
                fputcsv($output, array(
                    ucfirst(str_replace('_', ' ', $row->item_type)),
                    $row->item_count,
                    'PHP ' . number_format($row->total_amount, 2)
                ));
            }
            fputcsv($output, array(''));
        }

        fputcsv($output, array('INVOICES'));
        fputcsv($output, array('Invoice #', 'Guest', 'Booking', 'Event', 'Status', 'Total', 'Paid', 'Balance', 'Due Date', 'Issued'));
        foreach ($invoices as $inv) {
            fputcsv($output, array(
                $inv->invoice_number,
                $inv->guest_name,
                $inv->booking_number ?: '',
                !empty($inv->event_name) ? $inv->event_name : '',
                ucfirst($inv->status),
                'PHP ' . number_format($inv->total_amount, 2),
                'PHP ' . number_format($inv->amount_paid, 2),
                'PHP ' . number_format($inv->balance_due, 2),
                $inv->due_date ? date('M d, Y', strtotime($inv->due_date)) : '',
                $inv->issued_at ? date('M d, Y', strtotime($inv->issued_at)) : ''
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * Export Payments Report CSV
     */
    public function export_payments() {
        $this->require_reports_access();
        if (!$this->db->table_exists('payments')) {
            redirect('reports/daily_sales');
            return;
        }

        list($from, $to) = $this->parse_date_range();
        $status = $this->input->get('status');
        $payments = $this->Payment_model->get_for_date_range($from, $to, $status ?: null);
        $collected = $this->Payment_model->get_total_collected($from, $to);

        $filename = 'Payments_Report_' . $from . '_to_' . $to . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, array('BODARE PENSION HOUSE'));
        fputcsv($output, array('Payments Report'));
        fputcsv($output, array('Period: ' . $from . ' to ' . $to));
        fputcsv($output, array('Generated on: ' . date('F d, Y h:i A')));
        fputcsv($output, array(''));
        fputcsv($output, array('Total Collected (Paid)', 'PHP ' . number_format($collected, 2)));
        fputcsv($output, array(''));
        fputcsv($output, array('Date', 'Guest', 'Booking', 'Invoice', 'Method', 'Status', 'Reference', 'Amount'));

        foreach ($payments as $p) {
            fputcsv($output, array(
                $p->payment_date ? date('Y-m-d H:i', strtotime($p->payment_date)) : date('Y-m-d H:i', strtotime($p->created_at)),
                $p->guest_name ?: '',
                $p->booking_number ?: '',
                $p->invoice_number ?: '',
                ucfirst(str_replace('_', ' ', $p->payment_method)),
                ucfirst($p->payment_status),
                $p->reference_number ?: ($p->transaction_id ?: ''),
                'PHP ' . number_format($p->amount, 2)
            ));
        }

        fclose($output);
        exit;
    }
}
