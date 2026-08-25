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
        $data['can_sync_paymongo'] = $this->has_permission('add_payments') || $this->has_permission('edit_payments');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/payments/view', $data);
        $this->load->view('admin/layout/footer');
    }

    /**
     * Pull latest PayMongo status for a pending QRPH/card payment (useful on localhost without webhooks).
     */
    public function sync_paymongo($id) {
        $this->require_permission('edit_payments');
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $payment = $this->Payment_model->get($id);
        if (!$payment) {
            show_404();
            return;
        }

        if ($payment->payment_status === 'paid') {
            $this->session->set_flashdata('success', 'Payment is already marked paid.');
            redirect('payments/view/' . (int) $id);
            return;
        }

        $this->load->library('paymongo');
        $this->load->library('paymongo_service');
        if (!$this->paymongo->is_configured()) {
            $this->session->set_flashdata('error', 'PayMongo is not configured.');
            redirect('payments/view/' . (int) $id);
            return;
        }

        $meta = array();
        if (!empty($payment->notes)) {
            $decoded = json_decode($payment->notes, true);
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }

        $intent_id = !empty($payment->paymongo_intent_id) ? $payment->paymongo_intent_id : null;
        if (!$intent_id && !empty($meta['payment_intent_id'])) {
            $intent_id = $meta['payment_intent_id'];
        }
        $session_id = !empty($payment->transaction_id) && strpos($payment->transaction_id, 'cs_') === 0
            ? $payment->transaction_id
            : (!empty($meta['checkout_session_id']) ? $meta['checkout_session_id'] : null);
        if (!$intent_id && !empty($payment->transaction_id) && strpos($payment->transaction_id, 'pi_') === 0) {
            $intent_id = $payment->transaction_id;
        }

        $booking = !empty($payment->booking_id) ? $this->Booking_model->get_booking((int) $payment->booking_id) : null;
        $invoice = !empty($payment->invoice_id) ? $this->Invoice_model->get((int) $payment->invoice_id) : null;
        $fulfilled = false;

        if ($session_id && strpos($session_id, 'cs_') === 0) {
            $session = $this->paymongo->retrieve_checkout_session($session_id);
            if ($session && $this->paymongo->session_is_paid($session)) {
                if ($booking) {
                    $fulfilled = $this->paymongo_service->fulfill_paid_session($booking, $session, $session_id);
                } else {
                    $fulfilled = $this->paymongo_service->fulfill_paid_checkout_by_session($session, $session_id);
                }
            }
        }

        if (!$fulfilled && $intent_id && strpos($intent_id, 'pi_') === 0) {
            $intent = $this->paymongo->retrieve_payment_intent($intent_id);
            if ($intent && $this->paymongo->intent_is_paid($intent)) {
                if ($booking) {
                    $fulfilled = $this->paymongo_service->fulfill_paid_intent($booking, $intent);
                } elseif ($invoice) {
                    $fulfilled = $this->paymongo_service->fulfill_paid_intent_for_invoice($invoice, $intent);
                }
            } elseif ($intent) {
                $status = isset($intent['attributes']['status']) ? $intent['attributes']['status'] : 'unknown';
                $this->session->set_flashdata('error', 'PayMongo intent is not paid yet (status: ' . $status . '). If you just Authorized, wait a few seconds and try again.');
                redirect('payments/view/' . (int) $id);
                return;
            }
        }

        if ($fulfilled) {
            $this->session->set_flashdata('success', 'PayMongo payment confirmed and marked paid.');
        } else {
            $this->session->set_flashdata('error', $this->paymongo->get_last_error() ?: 'Could not confirm a paid payment with PayMongo yet.');
        }
        redirect('payments/view/' . (int) $id);
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

            if ($payment_method === 'qrph' || $payment_method === 'card') {
                // Status forced to pending for PayMongo online methods
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
                } elseif ($payment_method === 'card') {
                    $this->handle_card_checkout_and_email($booking_id_post, $invoice_id_post, $amount);
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
        if ($payment_method === 'gcash') {
            $this->form_validation->set_rules('reference_number', 'GCash Ref #', 'required|trim');
            $this->form_validation->set_rules('transaction_id', 'Transaction ID', 'required|trim');
        } elseif ($payment_method === 'bank_transfer') {
            $this->form_validation->set_rules('bank_name', 'Bank name', 'required|trim');
            $this->form_validation->set_rules('bank_account_name', 'Account name', 'required|trim');
            $this->form_validation->set_rules('bank_account_number', 'Account number', 'required|trim');
            $this->form_validation->set_rules('bank_transfer_date', 'Transfer date', 'required');
            $this->form_validation->set_rules('bank_reference', 'Bank reference #', 'required|trim');
        }
        // Card uses PayMongo Hosted Checkout — no last4/expiry collected here
    }

    /**
     * Extra server-side checks beyond form_validation.
     */
    private function validate_method_fields($payment_method) {
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
            // PayMongo card checkout — do not collect PAN/CVV; optional offline last4 if posted
            $last4 = preg_replace('/\D+/', '', (string) $this->input->post('card_last4'));
            $payload['card_last4'] = (strlen($last4) >= 4) ? substr($last4, -4) : null;
            $exp = trim((string) $this->input->post('card_exp'));
            $payload['card_exp'] = $exp !== '' ? $exp : null;
            // Keep posted txn/ref (checkout session id) when editing
            $payload['reference_number'] = $this->input->post('reference_number') ?: null;
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
     * Generate PayMongo QRPH for booking or invoice-only charge and email the guest.
     */
    private function handle_qrph_generate_and_email($booking_id, $invoice_id, $amount) {
        if (!$booking_id && !$invoice_id) {
            $this->session->set_flashdata('error', 'QRPH requires a linked booking or invoice.');
            return;
        }

        $this->load->library('paymongo_service');
        if (!$this->paymongo_service->is_ready()) {
            $this->session->set_flashdata('error', $this->paymongo_service->get_last_error() ?: 'PayMongo is not enabled or configured.');
            return;
        }

        $booking = null;
        $invoice = null;
        $guest_email = '';

        if ($booking_id) {
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
        } else {
            $invoice = $this->Invoice_model->get($invoice_id);
            if (!$invoice) {
                $this->session->set_flashdata('error', 'Invoice not found.');
                return;
            }
            $guest_email = isset($invoice->guest_email) ? trim($invoice->guest_email) : '';
            if ($guest_email === '' || !filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
                $this->session->set_flashdata('error', 'Guest has no valid email on the invoice. Update the invoice email before sending QRPH.');
                return;
            }
        }

        $invoice_error = $this->validate_payment_against_invoice($invoice_id, $booking_id, $amount, 'pending');
        if ($invoice_error) {
            $this->session->set_flashdata('error', $invoice_error);
            return;
        }

        if ($booking) {
            $payload = $this->paymongo_service->start_qrph_for_booking($booking, $amount, true);
        } else {
            $payload = $this->paymongo_service->start_qrph_for_invoice($invoice, $amount, true);
        }
        if (!$payload) {
            $this->session->set_flashdata('error', $this->paymongo_service->get_last_error() ?: 'Failed to create QRPH payment.');
            return;
        }

        $payment_id = !empty($payload['payment_id']) ? (int) $payload['payment_id'] : null;
        if ($payment_id && $this->admin_id) {
            $this->Payment_model->update($payment_id, array('admin_id' => $this->admin_id));
        }

        $resolved_invoice_id = !empty($payload['invoice_id']) ? (int) $payload['invoice_id'] : $invoice_id;
        if ($resolved_invoice_id && !$invoice) {
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

        $msg = 'QRPH generated and emailed to ' . $guest_email . '. Payment is pending until paid.';
        if (!empty($payload['test_url'])) {
            $msg .= ' Open this payment and click “Simulate QR Ph payment” to Authorize in test mode.';
        }
        $this->session->set_flashdata('success', $msg);
        redirect($payment_id ? 'payments/view/' . $payment_id : ($resolved_invoice_id ? 'invoices/view/' . $resolved_invoice_id : 'payments'));
    }

    /**
     * Create PayMongo Hosted Checkout (card) and email the guest a secure pay link.
     */
    private function handle_card_checkout_and_email($booking_id, $invoice_id, $amount) {
        if (!$booking_id && !$invoice_id) {
            $this->session->set_flashdata('error', 'Card payment requires a linked booking or invoice.');
            return;
        }

        $this->load->library('paymongo_service');
        if (!$this->paymongo_service->is_ready()) {
            $this->session->set_flashdata('error', $this->paymongo_service->get_last_error() ?: 'PayMongo is not enabled or configured.');
            return;
        }

        $booking = null;
        $invoice = null;
        $guest_email = '';

        if ($booking_id) {
            $booking = $this->Booking_model->get_booking($booking_id);
            if (!$booking) {
                $this->session->set_flashdata('error', 'Booking not found.');
                return;
            }
            $guest_email = isset($booking->guest_email) ? trim($booking->guest_email) : '';
            if ($guest_email === '' || !filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
                $this->session->set_flashdata('error', 'Guest has no valid email on the booking. Update the booking email before sending a card payment link.');
                return;
            }
        } else {
            $invoice = $this->Invoice_model->get($invoice_id);
            if (!$invoice) {
                $this->session->set_flashdata('error', 'Invoice not found.');
                return;
            }
            $guest_email = isset($invoice->guest_email) ? trim($invoice->guest_email) : '';
            if ($guest_email === '' || !filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
                $this->session->set_flashdata('error', 'Guest has no valid email on the invoice. Update the invoice email before sending a card payment link.');
                return;
            }
        }

        $invoice_error = $this->validate_payment_against_invoice($invoice_id, $booking_id, $amount, 'pending');
        if ($invoice_error) {
            $this->session->set_flashdata('error', $invoice_error);
            return;
        }

        if ($booking) {
            $payload = $this->paymongo_service->start_card_checkout_for_booking($booking, $amount, true);
        } else {
            $payload = $this->paymongo_service->start_card_checkout_for_invoice($invoice, $amount, true);
        }
        if (!$payload || empty($payload['checkout_url'])) {
            $this->session->set_flashdata('error', $this->paymongo_service->get_last_error() ?: 'Failed to create PayMongo card checkout.');
            return;
        }

        $payment_id = !empty($payload['payment_id']) ? (int) $payload['payment_id'] : null;
        if ($payment_id && $this->admin_id) {
            $this->Payment_model->update($payment_id, array('admin_id' => $this->admin_id));
        }

        $resolved_invoice_id = !empty($payload['invoice_id']) ? (int) $payload['invoice_id'] : $invoice_id;
        if ($resolved_invoice_id && !$invoice) {
            $invoice = $this->Invoice_model->get($resolved_invoice_id);
        }

        $this->load->library('billing_mail');
        $emailed = $this->billing_mail->send_card_checkout($booking, $payload, $invoice, $guest_email);
        if (!$emailed) {
            $mail_err = $this->billing_mail->get_last_error();
            $this->session->set_flashdata('error', 'Card checkout was created but email failed'
                . ($mail_err ? ': ' . $mail_err : '.')
                . ' Payment #' . ($payment_id ?: '—') . ' is pending. Checkout URL is on the payment record.');
            redirect($payment_id ? 'payments/view/' . $payment_id : 'payments');
            return;
        }

        $this->session->set_flashdata('success', 'PayMongo card checkout link emailed to ' . $guest_email . '. Payment is pending until the guest pays.');
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
                        if ($payment_method === 'qrph' || $payment_method === 'card') {
                            // Preserve PayMongo meta; only update core fields + status/notes
                            $update = array(
                                'invoice_id' => $invoice_id_post,
                                'booking_id' => $booking_id_post,
                                'amount' => $amount,
                                'payment_method' => $payment_method,
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
