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
        $data['booking_invoice_map'] = $this->build_booking_invoice_map($data['invoices']);

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
        } elseif ($booking_id) {
            $primary = $this->Invoice_model->get_primary_for_booking($booking_id);
            if ($primary) {
                $data['prefill_invoice_id'] = $primary->id;
                $data['prefill_amount'] = $primary->balance_due;
            }
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('amount', 'Amount', 'required|numeric|greater_than[0]');
            $this->form_validation->set_rules('payment_method', 'Payment Method', 'required');
            $this->form_validation->set_rules('payment_status', 'Payment Status', 'required');

            if ($this->form_validation->run() === TRUE) {
                $invoice_id_post = $this->input->post('invoice_id') ? (int) $this->input->post('invoice_id') : null;
                $booking_id_post = $this->input->post('booking_id') ? (int) $this->input->post('booking_id') : null;
                $amount = (float) $this->input->post('amount');
                $payment_status = $this->input->post('payment_status');

                // Resolve booking <-> invoice tagging before validation
                if ($invoice_id_post && !$booking_id_post) {
                    $inv = $this->Invoice_model->get($invoice_id_post);
                    if ($inv && $inv->booking_id) {
                        $booking_id_post = (int) $inv->booking_id;
                    }
                }
                if ($booking_id_post && !$invoice_id_post) {
                    $primary = $this->Invoice_model->get_primary_for_booking($booking_id_post);
                    if ($primary) {
                        $invoice_id_post = (int) $primary->id;
                    }
                }

                $error = $this->validate_payment_against_invoice($invoice_id_post, $booking_id_post, $amount, $payment_status);
                if ($error) {
                    $this->session->set_flashdata('error', $error);
                } else {
                    $payment_data = array(
                        'invoice_id' => $invoice_id_post,
                        'booking_id' => $booking_id_post,
                        'amount' => $amount,
                        'payment_method' => $this->input->post('payment_method'),
                        'payment_status' => $payment_status,
                        'transaction_id' => $this->input->post('transaction_id'),
                        'reference_number' => $this->input->post('reference_number'),
                        'payment_date' => $this->input->post('payment_date') ? $this->input->post('payment_date') : date('Y-m-d H:i:s'),
                        'notes' => $this->input->post('notes'),
                        'admin_id' => $this->admin_id
                    );

                    if (!$payment_data['booking_id'] && !$payment_data['invoice_id']) {
                        $this->session->set_flashdata('error', 'Please link the payment to an invoice (preferred) or booking.');
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
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/payments/add', $data);
        $this->load->view('admin/layout/footer');
    }

    /**
     * Map booking_id => primary payable invoice info for the payment form UI.
     */
    private function build_booking_invoice_map($invoices) {
        $map = array();
        foreach ($invoices as $inv) {
            if (empty($inv->booking_id) || $inv->status === 'void') {
                continue;
            }
            $bid = (int) $inv->booking_id;
            if (!isset($map[$bid]) || (float) $inv->balance_due > (float) $map[$bid]['balance_due']) {
                $map[$bid] = array(
                    'invoice_id' => (int) $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'balance_due' => (float) $inv->balance_due,
                    'status' => $inv->status,
                    'amount_paid' => (float) $inv->amount_paid
                );
            }
        }
        return $map;
    }

    /**
     * Prevent double payment: reject when invoice is paid or amount exceeds balance.
     */
    private function validate_payment_against_invoice($invoice_id, $booking_id, $amount, $payment_status, $exclude_payment_id = null) {
        if (!$invoice_id) {
            return null;
        }

        $inv = $this->Invoice_model->get($invoice_id);
        if (!$inv) {
            return 'Selected invoice was not found.';
        }
        if ($inv->status === 'void') {
            return 'Cannot record payment on a voided invoice.';
        }
        if ($inv->status === 'paid' || ((float) $inv->balance_due <= 0 && (float) $inv->amount_paid > 0)) {
            return 'Invoice ' . $inv->invoice_number . ' is already fully paid. No additional payment needed.';
        }

        if ($payment_status === 'paid') {
            $already_paid = $this->Payment_model->get_total_paid_for_invoice($invoice_id);
            if ($exclude_payment_id) {
                $existing = $this->Payment_model->get($exclude_payment_id);
                if ($existing && (int) $existing->invoice_id === (int) $invoice_id && $existing->payment_status === 'paid') {
                    $already_paid = max(0, $already_paid - (float) $existing->amount);
                }
            }
            $balance = round(max(0, (float) $inv->total_amount - $already_paid), 2);
            if ($amount > $balance + 0.009) {
                return 'Payment amount (₱' . number_format($amount, 2) . ') exceeds the remaining balance of ₱'
                    . number_format($balance, 2) . ' on invoice ' . $inv->invoice_number . '.';
            }
        }

        return null;
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
        $data['booking_invoice_map'] = $this->build_booking_invoice_map($data['invoices']);

        if ($this->input->post()) {
            $this->form_validation->set_rules('amount', 'Amount', 'required|numeric|greater_than[0]');
            $this->form_validation->set_rules('payment_method', 'Payment Method', 'required');
            $this->form_validation->set_rules('payment_status', 'Payment Status', 'required');

            if ($this->form_validation->run() === TRUE) {
                $old_invoice_id = $payment->invoice_id;
                $invoice_id_post = $this->input->post('invoice_id') ? (int) $this->input->post('invoice_id') : null;
                $booking_id_post = $this->input->post('booking_id') ? (int) $this->input->post('booking_id') : null;
                $amount = (float) $this->input->post('amount');
                $payment_status = $this->input->post('payment_status');

                if ($invoice_id_post && !$booking_id_post) {
                    $inv = $this->Invoice_model->get($invoice_id_post);
                    if ($inv && $inv->booking_id) {
                        $booking_id_post = (int) $inv->booking_id;
                    }
                }

                $error = $this->validate_payment_against_invoice($invoice_id_post, $booking_id_post, $amount, $payment_status, $id);
                if ($error) {
                    $this->session->set_flashdata('error', $error);
                } else {
                    $update = array(
                        'invoice_id' => $invoice_id_post,
                        'booking_id' => $booking_id_post,
                        'amount' => $amount,
                        'payment_method' => $this->input->post('payment_method'),
                        'payment_status' => $payment_status,
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
