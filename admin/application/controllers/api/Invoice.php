<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Invoice_model');
        $this->load->model('Invoice_item_model');
        $this->load->model('Payment_model');
        $this->load->model('User_model');
        $this->load->library('api_auth');
        header('Content-Type: application/json');
    }

    private function set_cors_headers() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
    }

    private function require_customer_session() {
        // Accepts either a bearer token (localStorage) or the cookie session.
        $auth = $this->api_auth->customer();
        if (!$auth) {
            $this->output->set_status_header(401);
            echo json_encode(array(
                'success' => false,
                'message' => 'Please log in to view your invoices'
            ));
            return null;
        }

        return $auth['user'];
    }

    /**
     * GET api/invoice/my-invoices
     */
    public function my_invoices() {
        $this->set_cors_headers();

        if ($this->input->method() === 'options') {
            exit;
        }

        if (!$this->db->table_exists('invoices')) {
            echo json_encode(array(
                'success' => true,
                'invoices' => array(),
                'message' => 'Billing module is not installed yet.'
            ));
            return;
        }

        $user = $this->require_customer_session();
        if (!$user) {
            return;
        }

        $invoices = $this->Invoice_model->get_invoices_for_user($user->id, $user->email);
        $result = array();

        foreach ($invoices as $inv) {
            $result[] = $this->Invoice_model->format_for_api($inv);
        }

        echo json_encode(array(
            'success' => true,
            'invoices' => $result
        ));
    }

    /**
     * GET api/invoice/view/{id}
     */
    public function view($id) {
        $this->set_cors_headers();

        if ($this->input->method() === 'options') {
            exit;
        }

        if (!$this->db->table_exists('invoices')) {
            $this->output->set_status_header(503);
            echo json_encode(array(
                'success' => false,
                'message' => 'Billing module is not installed yet.'
            ));
            return;
        }

        $user = $this->require_customer_session();
        if (!$user) {
            return;
        }

        $invoice = $this->Invoice_model->get((int) $id);
        if (!$invoice) {
            $this->output->set_status_header(404);
            echo json_encode(array(
                'success' => false,
                'message' => 'Invoice not found'
            ));
            return;
        }

        if (!$this->Invoice_model->user_can_access($invoice, $user->id, $user->email)) {
            $this->output->set_status_header(403);
            echo json_encode(array(
                'success' => false,
                'message' => 'You do not have permission to view this invoice'
            ));
            return;
        }

        if ($invoice->status === 'draft' || $invoice->status === 'void') {
            $this->output->set_status_header(403);
            echo json_encode(array(
                'success' => false,
                'message' => 'This invoice is not available for viewing'
            ));
            return;
        }

        $items = $this->Invoice_item_model->get_by_invoice($invoice->id);
        $payments = $this->Payment_model->get_by_invoice($invoice->id);

        $online_payment = null;
        $balance = isset($invoice->balance_due) ? (float) $invoice->balance_due : 0;
        if ($balance > 0 && !empty($invoice->booking_id)) {
            $this->load->library('paymongo_service');
            $online_payment = $this->paymongo_service->get_online_payment_for_booking((int) $invoice->booking_id);
            if ($online_payment) {
                $online_payment['invoice_id'] = (int) $invoice->id;
            }
        }

        echo json_encode(array(
            'success' => true,
            'invoice' => $this->Invoice_model->format_for_api($invoice, true),
            'items' => $this->Invoice_model->format_items_for_api($items),
            'payments' => $this->Invoice_model->format_payments_for_api($payments),
            'online_payment' => $online_payment
        ));
    }

    /**
     * GET api/invoice/number/{invoice_number}
     */
    public function by_number($invoice_number) {
        $this->set_cors_headers();

        if ($this->input->method() === 'options') {
            exit;
        }

        $invoice = $this->Invoice_model->get_by_number($invoice_number);
        if (!$invoice) {
            $this->output->set_status_header(404);
            echo json_encode(array(
                'success' => false,
                'message' => 'Invoice not found'
            ));
            return;
        }

        $this->view($invoice->id);
    }
}
