<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Best-effort guest push notifications for booking / invoice events.
 * Never throws; failures are logged only.
 */
class Push_notify {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    protected function push_enabled()
    {
        $firebase_path = realpath(FCPATH . '../includes/firebase.php');
        if (!$firebase_path) {
            $firebase_path = dirname(dirname(dirname(__DIR__))) . '/includes/firebase.php';
        }
        if (is_file($firebase_path)) {
            require_once $firebase_path;
        }
        return function_exists('bodare_push_enabled') && bodare_push_enabled();
    }

    protected function load_fcm()
    {
        if (!$this->push_enabled()) {
            return null;
        }
        $this->CI->load->library('fcm_service');
        if (!$this->CI->fcm_service->is_configured()) {
            log_message('debug', 'Push_notify: FCM service account not configured; skip send.');
            return null;
        }
        return $this->CI->fcm_service;
    }

    /**
     * Resolve app user id from booking (user_id column or guest email).
     */
    public function resolve_user_id_from_booking($booking)
    {
        if (!$booking) {
            return null;
        }
        if (!empty($booking->user_id)) {
            return (int) $booking->user_id;
        }
        $email = isset($booking->guest_email) ? trim((string) $booking->guest_email) : '';
        if ($email === '') {
            return null;
        }
        $this->CI->load->model('User_model');
        $user = $this->CI->User_model->get_user_by_email($email);
        return ($user && !empty($user->id)) ? (int) $user->id : null;
    }

    public function resolve_user_id_from_invoice($invoice)
    {
        if (!$invoice) {
            return null;
        }
        if (!empty($invoice->booking_id)) {
            $this->CI->load->model('Booking_model');
            $booking = $this->CI->Booking_model->get_booking((int) $invoice->booking_id);
            $uid = $this->resolve_user_id_from_booking($booking);
            if ($uid) {
                return $uid;
            }
        }
        $email = isset($invoice->guest_email) ? trim((string) $invoice->guest_email) : '';
        if ($email === '') {
            return null;
        }
        $this->CI->load->model('User_model');
        $user = $this->CI->User_model->get_user_by_email($email);
        return ($user && !empty($user->id)) ? (int) $user->id : null;
    }

    protected function send_to_user_id($user_id, $title, $body, array $data = [])
    {
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            return false;
        }
        $fcm = $this->load_fcm();
        if (!$fcm) {
            return false;
        }
        $sent = $fcm->send_to_user($user_id, $title, $body, $data);
        if (!$sent) {
            log_message('debug', 'Push_notify failed: ' . $fcm->get_last_error());
        }
        return (bool) $sent;
    }

    protected function booking_number($booking)
    {
        if (!$booking) {
            return '';
        }
        if (!empty($booking->booking_number)) {
            return (string) $booking->booking_number;
        }
        return '#' . str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT);
    }

    protected function format_date($ymd)
    {
        $ts = strtotime((string) $ymd);
        return $ts ? date('M j, Y', $ts) : (string) $ymd;
    }

    /**
     * Called when booking status changes (pending→confirmed, cancel, check-in, etc.).
     */
    public function booking_status_changed($booking, $old_status, $new_status)
    {
        try {
            if (!$booking || $old_status === $new_status) {
                return false;
            }
            $user_id = $this->resolve_user_id_from_booking($booking);
            if (!$user_id) {
                return false;
            }

            $number = $this->booking_number($booking);
            $data = [
                'booking' => '1',
                'booking_id' => (string) $booking->id,
                'booking_number' => $number,
                'status' => (string) $new_status,
            ];

            switch ($new_status) {
                case 'confirmed':
                    $check_in = !empty($booking->check_in) ? $this->format_date($booking->check_in) : '';
                    $body = $check_in !== ''
                        ? "Booking {$number} is confirmed. Check-in {$check_in}."
                        : "Booking {$number} is confirmed. See details in My Bookings.";
                    return $this->send_to_user_id($user_id, 'Booking confirmed', $body, $data);

                case 'cancelled':
                    return $this->send_to_user_id(
                        $user_id,
                        'Booking cancelled',
                        "Booking {$number} was cancelled. Contact us if this was a mistake.",
                        $data
                    );

                case 'checked_in':
                    return $this->send_to_user_id(
                        $user_id,
                        'Welcome to BODARE',
                        "You are checked in for booking {$number}. Enjoy your stay.",
                        $data
                    );

                case 'checked_out':
                    return $this->send_to_user_id(
                        $user_id,
                        'Thank you for staying',
                        "You are checked out for booking {$number}. We hope to see you again.",
                        $data
                    );

                default:
                    return false;
            }
        } catch (Exception $e) {
            log_message('error', 'Push_notify::booking_status_changed ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Invoice issued / emailed to guest.
     */
    public function invoice_ready($invoice)
    {
        try {
            if (!$invoice || empty($invoice->id)) {
                return false;
            }
            $status = isset($invoice->status) ? strtolower((string) $invoice->status) : '';
            if (in_array($status, ['void', 'draft'], true)) {
                return false;
            }

            $user_id = $this->resolve_user_id_from_invoice($invoice);
            if (!$user_id) {
                return false;
            }

            $inv_no = !empty($invoice->invoice_number)
                ? (string) $invoice->invoice_number
                : ('#' . $invoice->id);
            $balance = isset($invoice->balance_due) ? (float) $invoice->balance_due : 0;
            $body = $balance > 0
                ? "Invoice {$inv_no} is ready. Balance due: ₱" . number_format($balance, 2) . "."
                : "Invoice {$inv_no} is ready. Open My Bookings to view it.";

            return $this->send_to_user_id($user_id, 'Invoice ready', $body, [
                'booking' => '1',
                'invoice_id' => (string) $invoice->id,
                'invoice_number' => $inv_no,
            ]);
        } catch (Exception $e) {
            log_message('error', 'Push_notify::invoice_ready ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send check-in reminders for confirmed bookings checking in on $date (Y-m-d).
     * Returns counts: scanned, sent, skipped.
     */
    public function send_check_in_reminders($date = null)
    {
        $stats = ['scanned' => 0, 'sent' => 0, 'skipped' => 0];
        try {
            if (!$this->load_fcm()) {
                return $stats;
            }
            $date = $date ?: date('Y-m-d', strtotime('+1 day'));
            $this->CI->load->model('Booking_model');
            $this->CI->db->where('status', 'confirmed');
            $this->CI->db->where('check_in', $date);
            $bookings = $this->CI->db->get('bookings')->result();
            $stats['scanned'] = count($bookings);

            foreach ($bookings as $booking) {
                $user_id = $this->resolve_user_id_from_booking($booking);
                if (!$user_id) {
                    $stats['skipped']++;
                    continue;
                }
                $number = $this->booking_number($booking);
                $when = $this->format_date($booking->check_in);
                $time = !empty($booking->check_in_time)
                    ? date('g:i A', strtotime($booking->check_in_time))
                    : '2:00 PM';
                $ok = $this->send_to_user_id(
                    $user_id,
                    'Check-in tomorrow',
                    "Booking {$number}: check-in from {$time} on {$when} at BODARE Pension House.",
                    [
                        'booking' => '1',
                        'booking_id' => (string) $booking->id,
                        'booking_number' => $number,
                        'type' => 'check_in_reminder',
                    ]
                );
                if ($ok) {
                    $stats['sent']++;
                } else {
                    $stats['skipped']++;
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Push_notify::send_check_in_reminders ' . $e->getMessage());
        }
        return $stats;
    }
}
