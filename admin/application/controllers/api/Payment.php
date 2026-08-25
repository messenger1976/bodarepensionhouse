<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public API for PayMongo online payments (GCash Hosted Checkout).
 */
class Payment extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Booking_model');
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
     * Start a GCash PayMongo checkout for an existing booking.
     * POST { booking_number } or { booking_id }
     */
    public function create_gcash_checkout() {
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
                'message' => 'Online GCash payment is not available yet. Please choose Pay at the Hotel or contact us.'
            ));
            return;
        }

        $data = $this->json_input();
        $booking = $this->resolve_booking($data);
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

        $existing_paid = $this->paymongo_service->find_paid_payment((int) $booking->id);
        if ($existing_paid) {
            echo json_encode(array(
                'success' => true,
                'already_paid' => true,
                'booking_number' => $booking->booking_number,
                'message' => 'This booking is already paid.'
            ));
            return;
        }

        $amount = (float) $booking->total_amount;
        if ($amount <= 0) {
            $this->output->set_status_header(400);
            echo json_encode(array('success' => false, 'message' => 'Booking total is invalid for online payment.'));
            return;
        }

        $result = $this->paymongo_service->start_gcash_checkout_for_booking($booking, $amount);
        if (!$result) {
            $this->output->set_status_header(502);
            echo json_encode(array(
                'success' => false,
                'message' => $this->paymongo_service->get_last_error() ?: 'Unable to start GCash payment. Please try again.'
            ));
            return;
        }

        echo json_encode(array(
            'success' => true,
            'checkout_url' => $result['checkout_url'],
            'checkout_session_id' => $result['checkout_session_id'],
            'payment_id' => $result['payment_id'],
            'booking_number' => $booking->booking_number,
            'amount' => $amount
        ));
    }

    /**
     * Verify payment after PayMongo redirects back (success page).
     */
    public function verify() {
        $this->cors('GET, POST, OPTIONS');

        $booking_number = $this->input->get_post('booking') ?: $this->input->get_post('booking_number');
        $session_id = $this->input->get_post('session_id') ?: $this->input->get_post('checkout_session_id');

        if (!$booking_number && !$session_id) {
            $data = $this->json_input();
            $booking_number = isset($data['booking_number']) ? $data['booking_number'] : (isset($data['booking']) ? $data['booking'] : null);
            $session_id = isset($data['session_id']) ? $data['session_id'] : (isset($data['checkout_session_id']) ? $data['checkout_session_id'] : null);
        }

        $booking = null;
        if ($booking_number) {
            $booking = $this->Booking_model->get_booking_by_number($booking_number);
        }

        $payment_row = null;
        if ($session_id && $this->db->table_exists('payments')) {
            $this->db->where('transaction_id', $session_id);
            $this->db->order_by('id', 'DESC');
            $payment_row = $this->db->get('payments')->row();
            if ($payment_row && !$booking && !empty($payment_row->booking_id)) {
                $booking = $this->Booking_model->get_booking((int) $payment_row->booking_id);
            }
        }

        if (!$booking) {
            $this->output->set_status_header(404);
            echo json_encode(array('success' => false, 'message' => 'Booking not found.'));
            return;
        }

        if (!$session_id && $payment_row) {
            $session_id = $payment_row->transaction_id;
        }
        if (!$session_id) {
            $payment_row = $this->paymongo_service->find_latest_gcash_payment((int) $booking->id);
            if ($payment_row) {
                $session_id = $payment_row->transaction_id;
            }
        }

        if (!$session_id || !$this->paymongo->is_configured()) {
            $paid = (bool) $this->paymongo_service->find_paid_payment((int) $booking->id);
            echo json_encode(array(
                'success' => true,
                'paid' => $paid,
                'booking_number' => $booking->booking_number,
                'status' => $booking->status
            ));
            return;
        }

        $session = $this->paymongo->retrieve_checkout_session($session_id);
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
            $this->paymongo_service->fulfill_paid_session($booking, $session, $session_id);
            $booking = $this->Booking_model->get_booking((int) $booking->id);
        }

        echo json_encode(array(
            'success' => true,
            'paid' => $is_paid,
            'booking_number' => $booking->booking_number,
            'status' => $booking->status,
            'checkout_session_id' => $session_id
        ));
    }

    /**
     * PayMongo webhook endpoint.
     * Dashboard → Webhooks → subscribe to checkout_session.payment.paid
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
        if (in_array($event_type, array('checkout_session.payment.paid', 'payment.paid'), true) && $resource) {
            $session_id = isset($resource['id']) ? $resource['id'] : null;
            $attrs = isset($resource['attributes']) ? $resource['attributes'] : array();
            $reference = isset($attrs['reference_number']) ? $attrs['reference_number'] : null;

            $booking = null;
            if ($reference) {
                $booking = $this->Booking_model->get_booking_by_number($reference);
            }
            if (!$booking && $session_id && $this->db->table_exists('payments')) {
                $this->db->where('transaction_id', $session_id);
                $row = $this->db->get('payments')->row();
                if ($row) {
                    $booking = $this->Booking_model->get_booking((int) $row->booking_id);
                }
            }
            if (!$booking && !empty($attrs['metadata']['booking_number'])) {
                $booking = $this->Booking_model->get_booking_by_number($attrs['metadata']['booking_number']);
            }

            if ($booking) {
                if ($session_id && strpos($session_id, 'cs_') === 0) {
                    $session = $this->paymongo->retrieve_checkout_session($session_id);
                    if ($session && $this->paymongo->session_is_paid($session)) {
                        $this->paymongo_service->fulfill_paid_session($booking, $session, $session_id);
                        $handled = true;
                    } elseif (!$session) {
                        $this->paymongo_service->fulfill_paid_session($booking, $resource, $session_id);
                        $handled = true;
                    }
                } else {
                    $this->paymongo_service->fulfill_paid_session($booking, $resource, $session_id ?: ('wh_' . time()));
                    $handled = true;
                }
            }
        }

        echo json_encode(array('success' => true, 'handled' => $handled));
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
