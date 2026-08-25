<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public API for PayMongo QRPH (and legacy checkout) online payments.
 */
class Payment extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Booking_model');
        $this->load->model('Invoice_model');
        $this->load->library('paymongo');
        $this->load->library('paymongo_service');
        header('Content-Type: application/json');
    }

    private function cors($methods = 'POST, GET, OPTIONS') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: ' . $methods);
        header('Access-Control-Allow-Headers: Content-Type, Paymongo-Signature');
        if ($this->input->method() === 'options') {
            exit;
        }
    }

    private function json_input() {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : $this->input->post();
    }

    /**
     * Start or regenerate QRPH for a booking / invoice.
     * POST { booking_number } | { booking_id } | { invoice_id }, optional regenerate=true
     */
    public function create_qrph() {
        $this->cors('POST, OPTIONS');

        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
            return;
        }

        if (!$this->paymongo_service->is_ready()) {
            $this->output->set_status_header(503);
            echo json_encode(array(
                'success' => false,
                'message' => 'Online QR Ph payment is not configured yet. Please choose Pay at the Hotel or contact us.'
            ));
            return;
        }

        $data = $this->json_input();
        $booking = $this->resolve_booking($data);
        $invoice = null;

        if (!$booking && !empty($data['invoice_id']) && $this->db->table_exists('invoices')) {
            $invoice = $this->Invoice_model->get((int) $data['invoice_id']);
            if ($invoice && !empty($invoice->booking_id)) {
                $booking = $this->Booking_model->get_booking((int) $invoice->booking_id);
            }
        }

        $force = !empty($data['regenerate']) || !empty($data['force_new']);

        // Invoice-only QRPH (no linked booking)
        if (!$booking && $invoice) {
            if (isset($invoice->status) && $invoice->status === 'void') {
                $this->output->set_status_header(400);
                echo json_encode(array('success' => false, 'message' => 'This invoice was voided and cannot be paid.'));
                return;
            }
            if (isset($invoice->status) && $invoice->status === 'paid') {
                echo json_encode(array(
                    'success' => true,
                    'already_paid' => true,
                    'paid' => true,
                    'invoice_id' => (int) $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'message' => 'This invoice is already paid.'
                ));
                return;
            }

            $result = $this->paymongo_service->start_qrph_for_invoice($invoice, null, $force);
            if (!$result || empty($result['qr_image_url'])) {
                $this->output->set_status_header(502);
                echo json_encode(array(
                    'success' => false,
                    'invoice_id' => (int) $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'can_retry_payment' => true,
                    'message' => $this->paymongo_service->get_last_error() ?: 'Unable to start QR Ph payment. Please try again.'
                ));
                return;
            }

            echo json_encode(array(
                'success' => true,
                'payment' => $result,
                'invoice_id' => $result['invoice_id'],
                'invoice_number' => $result['invoice_number'],
                'qr_image_url' => $result['qr_image_url'],
                'expires_at' => $result['expires_at'],
                'payment_intent_id' => $result['payment_intent_id'],
                'amount' => $result['amount']
            ));
            return;
        }

        if (!$booking) {
            $this->output->set_status_header(404);
            echo json_encode(array('success' => false, 'message' => 'Booking not found.'));
            return;
        }

        if (strtolower((string) $booking->status) === 'cancelled') {
            $this->output->set_status_header(400);
            echo json_encode(array('success' => false, 'message' => 'This booking was cancelled and cannot be paid.'));
            return;
        }

        if ($this->paymongo_service->find_paid_payment((int) $booking->id)) {
            echo json_encode(array(
                'success' => true,
                'already_paid' => true,
                'paid' => true,
                'booking_number' => $booking->booking_number,
                'message' => 'This booking is already paid.'
            ));
            return;
        }

        $result = $force
            ? $this->paymongo_service->regenerate_qrph_for_booking($booking)
            : $this->paymongo_service->start_qrph_for_booking($booking);

        if (!$result || empty($result['qr_image_url'])) {
            $this->output->set_status_header(502);
            echo json_encode(array(
                'success' => false,
                'booking_number' => $booking->booking_number,
                'can_retry_payment' => true,
                'message' => $this->paymongo_service->get_last_error() ?: 'Unable to start QR Ph payment. Please try again.'
            ));
            return;
        }

        echo json_encode(array(
            'success' => true,
            'payment' => $result,
            'booking_number' => $booking->booking_number,
            'qr_image_url' => $result['qr_image_url'],
            'expires_at' => $result['expires_at'],
            'payment_intent_id' => $result['payment_intent_id'],
            'invoice_id' => $result['invoice_id'],
            'invoice_number' => $result['invoice_number'],
            'amount' => $result['amount']
        ));
    }

    /**
     * Legacy alias — now starts QRPH instead of hosted GCash redirect.
     */
    public function create_gcash_checkout() {
        $this->create_qrph();
    }

    /**
     * Verify Payment Intent status (polling from confirmation / invoices).
     */
    public function verify() {
        $this->cors('GET, POST, OPTIONS');

        $booking_number = $this->input->get_post('booking') ?: $this->input->get_post('booking_number');
        $intent_id = $this->input->get_post('payment_intent_id')
            ?: $this->input->get_post('session_id')
            ?: $this->input->get_post('checkout_session_id');

        if (!$booking_number && !$intent_id) {
            $data = $this->json_input();
            $booking_number = isset($data['booking_number']) ? $data['booking_number'] : (isset($data['booking']) ? $data['booking'] : null);
            $intent_id = isset($data['payment_intent_id']) ? $data['payment_intent_id']
                : (isset($data['session_id']) ? $data['session_id'] : (isset($data['checkout_session_id']) ? $data['checkout_session_id'] : null));
        }

        $booking = null;
        if ($booking_number) {
            $booking = $this->Booking_model->get_booking_by_number($booking_number);
        }
        if (!$booking && $intent_id) {
            $booking = $this->paymongo_service->find_booking_by_intent_id($intent_id);
        }

        if (!$booking) {
            $this->output->set_status_header(404);
            echo json_encode(array('success' => false, 'message' => 'Booking not found.'));
            return;
        }

        if ($this->paymongo_service->find_paid_payment((int) $booking->id)) {
            $booking = $this->Booking_model->get_booking((int) $booking->id);
            $online = $this->paymongo_service->get_online_payment_for_booking((int) $booking->id);
            echo json_encode(array(
                'success' => true,
                'paid' => true,
                'booking_number' => $booking->booking_number,
                'status' => $booking->status,
                'payment_intent_id' => $intent_id,
                'online_payment' => $online
            ));
            return;
        }

        if (!$intent_id) {
            $row = $this->paymongo_service->find_latest_online_payment((int) $booking->id);
            if ($row) {
                $intent_id = $row->transaction_id;
            }
        }

        if (!$intent_id || !$this->paymongo->is_configured()) {
            echo json_encode(array(
                'success' => true,
                'paid' => false,
                'booking_number' => $booking->booking_number,
                'status' => $booking->status,
                'online_payment' => $this->paymongo_service->get_online_payment_for_booking((int) $booking->id)
            ));
            return;
        }

        // Payment Intent path (pi_...)
        if (strpos($intent_id, 'pi_') === 0) {
            $intent = $this->paymongo->retrieve_payment_intent($intent_id);
            if (!$intent) {
                $this->output->set_status_header(502);
                echo json_encode(array(
                    'success' => false,
                    'message' => $this->paymongo->get_last_error() ?: 'Unable to verify payment with PayMongo.'
                ));
                return;
            }

            $is_paid = $this->paymongo->intent_is_paid($intent);
            if ($is_paid) {
                $this->paymongo_service->fulfill_paid_intent($booking, $intent);
                $booking = $this->Booking_model->get_booking((int) $booking->id);
            }

            echo json_encode(array(
                'success' => true,
                'paid' => $is_paid,
                'booking_number' => $booking->booking_number,
                'status' => $booking->status,
                'payment_intent_id' => $intent_id,
                'intent_status' => isset($intent['attributes']['status']) ? $intent['attributes']['status'] : null,
                'online_payment' => $this->paymongo_service->get_online_payment_for_booking((int) $booking->id)
            ));
            return;
        }

        // Legacy checkout session
        $session = $this->paymongo->retrieve_checkout_session($intent_id);
        if (!$session) {
            $this->output->set_status_header(502);
            echo json_encode(array(
                'success' => false,
                'message' => $this->paymongo->get_last_error() ?: 'Unable to verify payment with PayMongo.'
            ));
            return;
        }

        $is_paid = $this->paymongo->session_is_paid($session);
        if ($is_paid) {
            $this->paymongo_service->fulfill_paid_session($booking, $session, $intent_id);
            $booking = $this->Booking_model->get_booking((int) $booking->id);
        }

        echo json_encode(array(
            'success' => true,
            'paid' => $is_paid,
            'booking_number' => $booking->booking_number,
            'status' => $booking->status,
            'checkout_session_id' => $intent_id
        ));
    }

    /**
     * PayMongo webhook — subscribe to payment.paid (and optionally qrph.expired).
     */
    public function webhook() {
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(array('success' => false));
            return;
        }

        $raw = file_get_contents('php://input');
        $signature = isset($_SERVER['HTTP_PAYMONGO_SIGNATURE']) ? $_SERVER['HTTP_PAYMONGO_SIGNATURE'] : '';

        if (!$this->paymongo->verify_webhook_signature($raw, $signature)) {
            $this->output->set_status_header(400);
            echo json_encode(array('success' => false, 'message' => 'Invalid signature'));
            return;
        }

        $payload = json_decode($raw, true);
        $event_type = '';
        if (isset($payload['data']['attributes']['type'])) {
            $event_type = $payload['data']['attributes']['type'];
        } elseif (isset($payload['data']['type'])) {
            $event_type = $payload['data']['type'];
        }

        $resource = null;
        if (isset($payload['data']['attributes']['data'])) {
            $resource = $payload['data']['attributes']['data'];
        } elseif (isset($payload['data']) && isset($payload['data']['id'])) {
            $resource = $payload['data'];
        }

        $handled = false;

        if ($event_type === 'payment.paid' && $resource) {
            $handled = $this->handle_payment_paid_resource($resource) || $handled;
        }

        if ($event_type === 'checkout_session.payment.paid' && $resource) {
            $session_id = isset($resource['id']) ? $resource['id'] : null;
            if ($session_id && strpos($session_id, 'cs_') === 0) {
                $session = $this->paymongo->retrieve_checkout_session($session_id);
                if ($session && $this->paymongo->session_is_paid($session)) {
                    $handled = $this->paymongo_service->fulfill_paid_checkout_by_session($session, $session_id) || $handled;
                } elseif ($this->paymongo->session_is_paid($resource)) {
                    $handled = $this->paymongo_service->fulfill_paid_checkout_by_session($resource, $session_id) || $handled;
                }
            }
        }

        // qrph.expired — no DB mutation required; client regenerates on demand
        if ($event_type === 'qrph.expired') {
            $handled = true;
        }

        echo json_encode(array('success' => true, 'handled' => $handled, 'event' => $event_type));
    }

    private function handle_payment_paid_resource($resource) {
        $attrs = isset($resource['attributes']) ? $resource['attributes'] : array();
        $booking = null;
        $invoice = null;

        if (!empty($attrs['metadata']['booking_number'])) {
            $booking = $this->Booking_model->get_booking_by_number($attrs['metadata']['booking_number']);
        }

        // Payment resources often include payment_intent_id
        $intent_id = null;
        if (!empty($attrs['payment_intent_id'])) {
            $intent_id = $attrs['payment_intent_id'];
        } elseif (!empty($resource['id']) && strpos($resource['id'], 'pi_') === 0) {
            $intent_id = $resource['id'];
        }

        if (!$booking && $intent_id) {
            $booking = $this->paymongo_service->find_booking_by_intent_id($intent_id);
        }

        if (!$booking && !empty($attrs['description']) && preg_match('/\b(BK\d+)\b/i', $attrs['description'], $m)) {
            $booking = $this->Booking_model->get_booking_by_number($m[1]);
        }

        // Invoice-only QRPH (additional charges with no booking)
        if (!$booking) {
            if (!empty($attrs['metadata']['invoice_id'])) {
                $invoice = $this->Invoice_model->get((int) $attrs['metadata']['invoice_id']);
            }
            if (!$invoice && !empty($attrs['metadata']['invoice_number'])) {
                $this->db->where('invoice_number', $attrs['metadata']['invoice_number']);
                $invoice = $this->db->get('invoices')->row();
            }
            if (!$invoice && $intent_id) {
                $payment_row = $this->paymongo_service->find_payment_by_intent_id($intent_id);
                if ($payment_row && !empty($payment_row->invoice_id)) {
                    $invoice = $this->Invoice_model->get((int) $payment_row->invoice_id);
                }
            }
            if ($invoice && !empty($invoice->booking_id)) {
                $booking = $this->Booking_model->get_booking((int) $invoice->booking_id);
            }
        }

        if ($booking) {
            if ($intent_id && strpos($intent_id, 'pi_') === 0) {
                $intent = $this->paymongo->retrieve_payment_intent($intent_id);
                if ($intent && $this->paymongo->intent_is_paid($intent)) {
                    return $this->paymongo_service->fulfill_paid_intent($booking, $intent);
                }
            }

            $fake = array(
                'id' => $intent_id ?: (isset($resource['id']) ? $resource['id'] : null),
                'attributes' => $attrs
            );
            return $this->paymongo_service->fulfill_paid_intent($booking, $fake);
        }

        if ($invoice) {
            if ($intent_id && strpos($intent_id, 'pi_') === 0) {
                $intent = $this->paymongo->retrieve_payment_intent($intent_id);
                if ($intent && $this->paymongo->intent_is_paid($intent)) {
                    return $this->paymongo_service->fulfill_paid_intent_for_invoice($invoice, $intent);
                }
            }

            $fake = array(
                'id' => $intent_id ?: (isset($resource['id']) ? $resource['id'] : null),
                'attributes' => $attrs
            );
            return $this->paymongo_service->fulfill_paid_intent_for_invoice($invoice, $fake);
        }

        return false;
    }

    private function resolve_booking($data) {
        if (!empty($data['booking_id'])) {
            return $this->Booking_model->get_booking((int) $data['booking_id']);
        }
        $number = !empty($data['booking_number']) ? $data['booking_number'] : (!empty($data['booking']) ? $data['booking'] : null);
        if ($number) {
            return $this->Booking_model->get_booking_by_number($number);
        }
        return null;
    }
}
