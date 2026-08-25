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
            $payment_method = $this->input->post('payment_method');

            $this->form_validation->set_rules('amount', 'Amount', 'required|numeric|greater_than[0]');
            $this->form_validation->set_rules('payment_method', 'Payment Method', 'required');

            if ($payment_method === 'qrph') {
                // Status is forced to pending; skip client-disabled field requirement
            } else {
                $this->form_validation->set_rules('payment_status', 'Payment Status', 'required');
            }

            $this->apply_method_validation_rules($payment_method);

            if ($this->form_validation->run() === TRUE) {
                $invoice_id_post = $this->input->post('invoice_id') ? (int) $this->input->post('invoice_id') : null;
                $booking_id_post = $this->input->post('booking_id') ? (int) $this->input->post('booking_id') : null;
                $amount = (float) $this->input->post('amount');

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

                if (!$booking_id_post && !$invoice_id_post) {
                    $this->session->set_flashdata('error', 'Please link the payment to an invoice (preferred) or booking.');
                } elseif ($payment_method === 'qrph') {
                    $this->handle_qrph_generate_and_email($booking_id_post, $invoice_id_post, $amount);
                } else {
                    $payment_status = $this->input->post('payment_status');
                    $method_error = $this->validate_method_fields($payment_method);
                    if ($method_error) {
                        $this->session->set_flashdata('error', $method_error);
                    } else {
                        $error = $this->validate_payment_against_invoice($invoice_id_post, $booking_id_post, $amount, $payment_status);
                        if ($error) {
                            $this->session->set_flashdata('error', $error);
                        } else {
                            $payment_data = $this->build_payment_payload($payment_method, array(
                                'invoice_id' => $invoice_id_post,
                                'booking_id' => $booking_id_post,
                                'amount' => $amount,
                                'payment_status' => $payment_status,
                                'payment_date' => $this->input->post('payment_date') ? $this->input->post('payment_date') : date('Y-m-d H:i:s'),
                                'notes' => $this->input->post('notes'),
                                'admin_id' => $this->admin_id
                            ));

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

    private function apply_method_validation_rules($payment_method) {
        if ($payment_method === 'card') {
            $this->form_validation->set_rules('card_last4', 'Card last 4 digits', 'required|exact_length[4]|numeric');
            $this->form_validation->set_rules('card_exp', 'Card expiry', 'required');
        } elseif ($payment_method === 'gcash') {
            $this->form_validation->set_rules('reference_number', 'GCash Ref #', 'required|trim');
            $this->form_validation->set_rules('transaction_id', 'Transaction ID', 'required|trim');
        } elseif ($payment_method === 'bank_transfer') {
            $this->form_validation->set_rules('bank_name', 'Bank name', 'required|trim');
            $this->form_validation->set_rules('bank_account_name', 'Account name', 'required|trim');
            $this->form_validation->set_rules('bank_account_number', 'Account number', 'required|trim');
            $this->form_validation->set_rules('bank_transfer_date', 'Transfer date', 'required');
            $this->form_validation->set_rules('bank_reference', 'Bank reference #', 'required|trim');
        }
    }

    /**
     * Extra server-side checks beyond form_validation (PCI card last4, etc.).
     */
    private function validate_method_fields($payment_method) {
        if ($payment_method === 'card') {
            $last4 = preg_replace('/\D+/', '', (string) $this->input->post('card_last4'));
            $raw_last4 = trim((string) $this->input->post('card_last4'));
            // Reject anything that looks like a full PAN
            if (strlen(preg_replace('/\D+/', '', $raw_last4)) > 4) {
                return 'Enter only the last 4 digits of the card. Full card numbers cannot be stored.';
            }
            if (strlen($last4) !== 4) {
                return 'Card last 4 digits must be exactly 4 numbers.';
            }
            $exp = trim((string) $this->input->post('card_exp'));
            if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $exp)) {
                return 'Card expiry must be in MM/YY format.';
            }
        }
        return null;
    }

    /**
     * Build payments row fields including method-specific columns.
     */
    private function build_payment_payload($payment_method, $base) {
        $payload = $base;
        $payload['payment_method'] = $payment_method;
        $payload['transaction_id'] = $this->input->post('transaction_id');
        $payload['reference_number'] = $this->input->post('reference_number');

        // Clear method-specific fields first when method changes
        if ($this->db->field_exists('card_last4', 'payments')) {
            $payload['card_last4'] = null;
        }
        if ($this->db->field_exists('card_exp', 'payments')) {
            $payload['card_exp'] = null;
        }
        if ($this->db->field_exists('bank_name', 'payments')) {
            $payload['bank_name'] = null;
            $payload['bank_account_name'] = null;
            $payload['bank_account_number'] = null;
            $payload['bank_transfer_date'] = null;
        }

        if ($payment_method === 'card') {
            $payload['card_last4'] = substr(preg_replace('/\D+/', '', (string) $this->input->post('card_last4')), -4);
            $payload['card_exp'] = trim((string) $this->input->post('card_exp'));
            $payload['reference_number'] = null;
            $payload['transaction_id'] = $this->input->post('transaction_id') ?: null;
        } elseif ($payment_method === 'gcash') {
            $payload['reference_number'] = trim((string) $this->input->post('reference_number'));
            $payload['transaction_id'] = trim((string) $this->input->post('transaction_id'));
        } elseif ($payment_method === 'bank_transfer') {
            $payload['bank_name'] = trim((string) $this->input->post('bank_name'));
            $payload['bank_account_name'] = trim((string) $this->input->post('bank_account_name'));
            $payload['bank_account_number'] = trim((string) $this->input->post('bank_account_number'));
            $payload['bank_transfer_date'] = $this->input->post('bank_transfer_date') ?: null;
            $payload['reference_number'] = trim((string) $this->input->post('bank_reference'));
            $payload['transaction_id'] = $this->input->post('transaction_id') ?: null;
        } elseif ($payment_method === 'cash') {
            // keep generic ref/txn if provided
        }

        // Drop columns that do not exist yet (migration not run)
        foreach (array('card_last4', 'card_exp', 'bank_name', 'bank_account_name', 'bank_account_number', 'bank_transfer_date') as $col) {
            if (array_key_exists($col, $payload) && !$this->db->field_exists($col, 'payments')) {
                unset($payload[$col]);
            }
        }

        return $payload;
    }

    /**
     * Generate PayMongo QRPH for booking and email the guest.
     */
    private function handle_qrph_generate_and_email($booking_id, $invoice_id, $amount) {
        if (!$booking_id) {
            $this->session->set_flashdata('error', 'QRPH requires a linked booking (or an invoice that has a booking).');
            return;
        }

        $booking = $this->Booking_model->get_booking($booking_id);
        if (!$booking) {
            $this->session->set_flashdata('error', 'Booking not found.');
            return;
        }

        $guest_email = isset($booking->guest_email) ? trim($booking->guest_email) : '';
        if ($guest_email === '' || !filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('error', 'Guest has no valid email on the booking. Update the booking email before sending QRPH.');
            return;
        }

        $invoice_error = $this->validate_payment_against_invoice($invoice_id, $booking_id, $amount, 'pending');
        if ($invoice_error) {
            $this->session->set_flashdata('error', $invoice_error);
            return;
        }

        $this->load->library('paymongo_service');
        if (!$this->paymongo_service->is_ready()) {
            $this->session->set_flashdata('error', $this->paymongo_service->get_last_error() ?: 'PayMongo is not enabled or configured.');
            return;
        }

        $payload = $this->paymongo_service->start_qrph_for_booking($booking, $amount, true);
        if (!$payload) {
            $this->session->set_flashdata('error', $this->paymongo_service->get_last_error() ?: 'Failed to create QRPH payment.');
            return;
        }

        $payment_id = !empty($payload['payment_id']) ? (int) $payload['payment_id'] : null;
        if ($payment_id && $this->admin_id) {
            $this->Payment_model->update($payment_id, array('admin_id' => $this->admin_id));
        }

        $invoice = null;
        $resolved_invoice_id = !empty($payload['invoice_id']) ? (int) $payload['invoice_id'] : $invoice_id;
        if ($resolved_invoice_id) {
            $invoice = $this->Invoice_model->get($resolved_invoice_id);
        }

        $this->load->library('billing_mail');
        $emailed = $this->billing_mail->send_qrph($booking, $payload, $invoice, $guest_email);
        if (!$emailed) {
            $mail_err = $this->billing_mail->get_last_error();
            $this->session->set_flashdata('error', 'QRPH was created but email failed'
                . ($mail_err ? ': ' . $mail_err : '.')
                . ' Payment #' . ($payment_id ?: '—') . ' is pending.');
            redirect($payment_id ? 'payments/view/' . $payment_id : 'payments');
            return;
        }

        $this->session->set_flashdata('success', 'QRPH generated and emailed to ' . $guest_email . '. Payment is pending until the guest pays.');
        redirect($payment_id ? 'payments/view/' . $payment_id : ($resolved_invoice_id ? 'invoices/view/' . $resolved_invoice_id : 'payments'));
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
            $payment_method = $this->input->post('payment_method');

            $this->form_validation->set_rules('amount', 'Amount', 'required|numeric|greater_than[0]');
            $this->form_validation->set_rules('payment_method', 'Payment Method', 'required');
            $this->form_validation->set_rules('payment_status', 'Payment Status', 'required');
            $this->apply_method_validation_rules($payment_method);

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

                $method_error = null;
                if ($payment_method !== 'qrph') {
                    $method_error = $this->validate_method_fields($payment_method);
                }

                if ($method_error) {
                    $this->session->set_flashdata('error', $method_error);
                } else {
                    $error = $this->validate_payment_against_invoice($invoice_id_post, $booking_id_post, $amount, $payment_status, $id);
                    if ($error) {
                        $this->session->set_flashdata('error', $error);
                    } else {
                        if ($payment_method === 'qrph') {
                            // Preserve existing QRPH meta; only update core fields + status/notes
                            $update = array(
                                'invoice_id' => $invoice_id_post,
                                'booking_id' => $booking_id_post,
                                'amount' => $amount,
                                'payment_method' => 'qrph',
                                'payment_status' => $payment_status,
                                'payment_date' => $this->input->post('payment_date') ?: null,
                                'notes' => $this->input->post('notes'),
                                'transaction_id' => $payment->transaction_id,
                                'reference_number' => $payment->reference_number
                            );
                        } else {
                            $update = $this->build_payment_payload($payment_method, array(
                                'invoice_id' => $invoice_id_post,
                                'booking_id' => $booking_id_post,
                                'amount' => $amount,
                                'payment_status' => $payment_status,
                                'payment_date' => $this->input->post('payment_date'),
                                'notes' => $this->input->post('notes')
                            ));
                        }

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
