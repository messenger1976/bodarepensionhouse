<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Payments extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Payment_model');
        $this->load->model('Invoice_model');
        $this->load->model('Booking_model');
        $this->load->library('form_validation');
    }

    public function index() {
        $this->require_permission('view_payments');

        $filters = array();
        $status = $this->input->get('status');
        if ($status) {
            $filters['status'] = $status;
        }

        $data['title'] = 'Payment Records';
        $data['payments'] = $this->Payment_model->get_all($filters);
        $data['filter_status'] = $status;
        $data['can_add'] = $this->has_permission('add_payments');
        $data['can_edit'] = $this->has_permission('edit_payments');
        $data['can_delete'] = $this->has_permission('delete_payments');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/payments/index', $data);
        $this->load->view('admin/layout/footer');
    }

    public function view($id) {
        $this->require_permission('view_payments');

        $payment = $this->Payment_model->get($id);
        if (!$payment) {
            show_404();
            return;
        }

        $data['title'] = 'Payment Details';
        $data['payment'] = $payment;
        $data['can_edit'] = $this->has_permission('edit_payments');
        $data['can_delete'] = $this->has_permission('delete_payments');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/payments/view', $data);
        $this->load->view('admin/layout/footer');
    }

    public function add() {
        $this->require_permission('add_payments');

        $data['title'] = 'Record Payment';
        $data['invoices'] = $this->Invoice_model->get_all();
        $data['bookings'] = $this->Booking_model->get_all_bookings();

        $invoice_id = $this->input->get('invoice_id');
        $booking_id = $this->input->get('booking_id');
        $data['prefill_invoice_id'] = $invoice_id;
        $data['prefill_booking_id'] = $booking_id;

        if ($invoice_id) {
            $inv = $this->Invoice_model->get($invoice_id);
            if ($inv) {
                $data['prefill_amount'] = $inv->balance_due;
                $data['prefill_booking_id'] = $inv->booking_id;
            }
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('amount', 'Amount', 'required|numeric|greater_than[0]');
            $this->form_validation->set_rules('payment_method', 'Payment Method', 'required');
            $this->form_validation->set_rules('payment_status', 'Payment Status', 'required');

            if ($this->form_validation->run() === TRUE) {
                $invoice_id_post = $this->input->post('invoice_id') ? (int) $this->input->post('invoice_id') : null;
                $booking_id_post = $this->input->post('booking_id') ? (int) $this->input->post('booking_id') : null;

                if ($invoice_id_post && !$booking_id_post) {
                    $inv = $this->Invoice_model->get($invoice_id_post);
                    if ($inv && $inv->booking_id) {
                        $booking_id_post = (int) $inv->booking_id;
                    }
                }

                $payment_data = array(
                    'invoice_id' => $invoice_id_post,
                    'booking_id' => $booking_id_post,
                    'amount' => (float) $this->input->post('amount'),
                    'payment_method' => $this->input->post('payment_method'),
                    'payment_status' => $this->input->post('payment_status'),
                    'transaction_id' => $this->input->post('transaction_id'),
                    'reference_number' => $this->input->post('reference_number'),
                    'payment_date' => $this->input->post('payment_date') ? $this->input->post('payment_date') : date('Y-m-d H:i:s'),
                    'notes' => $this->input->post('notes'),
                    'admin_id' => $this->admin_id
                );

                if (!$payment_data['booking_id'] && !$payment_data['invoice_id']) {
                    $this->session->set_flashdata('error', 'Please link the payment to an invoice or booking.');
                } else {
                    $payment_id = $this->Payment_model->create($payment_data);
                    if ($payment_id) {
                        if ($invoice_id_post) {
                            $this->Invoice_model->recalculate_totals($invoice_id_post);
                        }
                        $this->session->set_flashdata('success', 'Payment recorded successfully.');
                        redirect($invoice_id_post ? 'invoices/view/' . $invoice_id_post : 'payments/view/' . $payment_id);
                    }
                    $this->session->set_flashdata('error', 'Failed to record payment.');
                }
            }
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/payments/add', $data);
        $this->load->view('admin/layout/footer');
    }

    public function edit($id) {
        $this->require_permission('edit_payments');

        $payment = $this->Payment_model->get($id);
        if (!$payment) {
            show_404();
            return;
        }

        $data['title'] = 'Edit Payment';
        $data['payment'] = $payment;
        $data['invoices'] = $this->Invoice_model->get_all();
        $data['bookings'] = $this->Booking_model->get_all_bookings();

        if ($this->input->post()) {
            $this->form_validation->set_rules('amount', 'Amount', 'required|numeric|greater_than[0]');
            $this->form_validation->set_rules('payment_method', 'Payment Method', 'required');
            $this->form_validation->set_rules('payment_status', 'Payment Status', 'required');

            if ($this->form_validation->run() === TRUE) {
                $old_invoice_id = $payment->invoice_id;
                $update = array(
                    'invoice_id' => $this->input->post('invoice_id') ? (int) $this->input->post('invoice_id') : null,
                    'booking_id' => $this->input->post('booking_id') ? (int) $this->input->post('booking_id') : null,
                    'amount' => (float) $this->input->post('amount'),
                    'payment_method' => $this->input->post('payment_method'),
                    'payment_status' => $this->input->post('payment_status'),
                    'transaction_id' => $this->input->post('transaction_id'),
                    'reference_number' => $this->input->post('reference_number'),
                    'payment_date' => $this->input->post('payment_date'),
                    'notes' => $this->input->post('notes')
                );

                if ($this->Payment_model->update($id, $update)) {
                    if ($old_invoice_id) {
                        $this->Invoice_model->recalculate_totals($old_invoice_id);
                    }
                    if (!empty($update['invoice_id'])) {
                        $this->Invoice_model->recalculate_totals($update['invoice_id']);
                    }
                    $this->session->set_flashdata('success', 'Payment updated.');
                    redirect('payments/view/' . $id);
                }
                $this->session->set_flashdata('error', 'Failed to update payment.');
            }
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/payments/edit', $data);
        $this->load->view('admin/layout/footer');
    }

    public function delete($id) {
        $this->require_permission('delete_payments');

        $payment = $this->Payment_model->get($id);
        if (!$payment) {
            show_404();
            return;
        }

        $invoice_id = $payment->invoice_id;
        if ($this->Payment_model->delete($id)) {
            if ($invoice_id) {
                $this->Invoice_model->recalculate_totals($invoice_id);
            }
            $this->session->set_flashdata('success', 'Payment deleted.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete payment.');
        }
        redirect('payments');
    }
}
