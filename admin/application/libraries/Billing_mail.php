<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Billing_mail {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('coop_mail');
    }

    public function get_last_error() {
        return $this->CI->coop_mail->get_last_error();
    }

    /**
     * Send invoice to guest email.
     */
    public function send_invoice($invoice, $items, $payments = array(), $to_email = null) {
        if (!$invoice) {
            return false;
        }

        $recipient = $to_email ? trim($to_email) : trim($invoice->guest_email);
        if ($recipient === '' || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $subject = 'Your Invoice ' . $invoice->invoice_number . ' - BODARE Pension House';
        $message = $this->build_invoice_html($invoice, $items, $payments);

        $this->CI->coop_mail->set_profile('account');
        return $this->CI->coop_mail->send($recipient, $subject, $message);
    }

    public function build_invoice_html($invoice, $items, $payments = array()) {
        $rows = '';
        foreach ((array) $items as $item) {
            $rows .= '<tr>'
                . '<td style="padding:8px;border-bottom:1px solid #eee;">' . htmlspecialchars($item->description, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td style="padding:8px;border-bottom:1px solid #eee;text-align:right;">' . number_format((float) $item->quantity, 2) . '</td>'
                . '<td style="padding:8px;border-bottom:1px solid #eee;text-align:right;">₱' . number_format((float) $item->unit_price, 2) . '</td>'
                . '<td style="padding:8px;border-bottom:1px solid #eee;text-align:right;">₱' . number_format((float) $item->total_price, 2) . '</td>'
                . '</tr>';
        }

        $payment_rows = '';
        if (!empty($payments)) {
            foreach ($payments as $p) {
                if ($p->payment_status !== 'paid') {
                    continue;
                }
                $payment_rows .= '<tr>'
                    . '<td style="padding:6px 0;color:#666;">' . ($p->payment_date ? date('M d, Y', strtotime($p->payment_date)) : '—') . '</td>'
                    . '<td style="padding:6px 0;color:#666;">' . ucfirst($p->payment_method) . '</td>'
                    . '<td style="padding:6px 0;text-align:right;color:#059669;">₱' . number_format((float) $p->amount, 2) . '</td>'
                    . '</tr>';
            }
        }

        $due_label = $invoice->due_date ? date('F d, Y', strtotime($invoice->due_date)) : 'Upon receipt';
        $issued_label = $invoice->issued_at ? date('F d, Y', strtotime($invoice->issued_at)) : date('F d, Y');
        $portal_url = $this->build_customer_portal_url($invoice->id);

        $html = '
        <div style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:640px;margin:0 auto;">
            <div style="border-bottom:3px solid #6576ff;padding-bottom:16px;margin-bottom:24px;">
                <h1 style="margin:0;color:#6576ff;font-size:22px;">BODARE Pension House</h1>
                <p style="margin:4px 0 0;color:#666;">Room Billing Invoice</p>
            </div>
            <p>Dear <strong>' . htmlspecialchars($invoice->guest_name, ENT_QUOTES, 'UTF-8') . '</strong>,</p>
            <p>Please find your invoice details below. You can also view this invoice online after logging in to your account.</p>
            <p style="margin:16px 0;"><a href="' . htmlspecialchars($portal_url, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#6576ff;color:#fff;padding:10px 18px;text-decoration:none;border-radius:6px;font-weight:bold;">View Invoice Online</a></p>
            <table style="width:100%;margin:16px 0;border-collapse:collapse;">
                <tr>
                    <td style="padding:4px 0;"><strong>Invoice #:</strong> ' . htmlspecialchars($invoice->invoice_number, ENT_QUOTES, 'UTF-8') . '</td>
                    <td style="padding:4px 0;text-align:right;"><strong>Status:</strong> ' . ucfirst($invoice->status) . '</td>
                </tr>
                <tr>
                    <td style="padding:4px 0;"><strong>Issued:</strong> ' . $issued_label . '</td>
                    <td style="padding:4px 0;text-align:right;"><strong>Due:</strong> ' . $due_label . '</td>
                </tr>
            </table>
            <table style="width:100%;border-collapse:collapse;margin:20px 0;">
                <thead>
                    <tr style="background:#f8f9fa;">
                        <th style="padding:10px;text-align:left;">Description</th>
                        <th style="padding:10px;text-align:right;">Qty</th>
                        <th style="padding:10px;text-align:right;">Unit Price</th>
                        <th style="padding:10px;text-align:right;">Amount</th>
                    </tr>
                </thead>
                <tbody>' . $rows . '</tbody>
            </table>
            <table style="width:100%;max-width:320px;margin-left:auto;border-collapse:collapse;">
                <tr><td style="padding:4px 0;text-align:right;">Subtotal</td><td style="padding:4px 0;text-align:right;width:120px;">₱' . number_format((float) $invoice->subtotal, 2) . '</td></tr>';

        if ((float) $invoice->discount_amount > 0) {
            $html .= '<tr><td style="padding:4px 0;text-align:right;color:#c00;">Discount</td><td style="padding:4px 0;text-align:right;">-₱' . number_format((float) $invoice->discount_amount, 2) . '</td></tr>';
        }
        if ((float) $invoice->tax_amount > 0) {
            $html .= '<tr><td style="padding:4px 0;text-align:right;">Tax</td><td style="padding:4px 0;text-align:right;">₱' . number_format((float) $invoice->tax_amount, 2) . '</td></tr>';
        }
        if ((float) $invoice->service_charge_amount > 0) {
            $html .= '<tr><td style="padding:4px 0;text-align:right;">Service Charge</td><td style="padding:4px 0;text-align:right;">₱' . number_format((float) $invoice->service_charge_amount, 2) . '</td></tr>';
        }

        $html .= '
                <tr><td style="padding:8px 0;text-align:right;font-weight:bold;">Total</td><td style="padding:8px 0;text-align:right;font-weight:bold;">₱' . number_format((float) $invoice->total_amount, 2) . '</td></tr>
                <tr><td style="padding:4px 0;text-align:right;color:#059669;">Amount Paid</td><td style="padding:4px 0;text-align:right;color:#059669;">₱' . number_format((float) $invoice->amount_paid, 2) . '</td></tr>
                <tr><td style="padding:8px 0;text-align:right;font-weight:bold;color:#c00;">Balance Due</td><td style="padding:8px 0;text-align:right;font-weight:bold;color:#c00;">₱' . number_format((float) $invoice->balance_due, 2) . '</td></tr>
            </table>';

        if ($payment_rows !== '') {
            $html .= '
            <h3 style="font-size:15px;color:#666;margin-top:24px;">Payment History</h3>
            <table style="width:100%;border-collapse:collapse;">' . $payment_rows . '</table>';
        }

        if (!empty($invoice->notes)) {
            $html .= '<p style="margin-top:20px;color:#666;"><strong>Notes:</strong> ' . nl2br(htmlspecialchars($invoice->notes, ENT_QUOTES, 'UTF-8')) . '</p>';
        }

        $html .= '
            <p style="margin-top:32px;font-size:13px;color:#888;">Thank you for choosing BODARE Pension House.</p>
        </div>';

        return $html;
    }

    /**
     * Email guest a PayMongo QRPH code / payment link for a booking or invoice-only charge.
     *
     * @param object|null $booking
     * @param array  $qrph_payload from Paymongo_service
     * @param object|null $invoice
     * @param string|null $to_email
     */
    public function send_qrph($booking, $qrph_payload, $invoice = null, $to_email = null) {
        if (empty($qrph_payload) || (!$booking && !$invoice)) {
            return false;
        }

        $recipient = $to_email ? trim($to_email) : '';
        if ($recipient === '' && $invoice && !empty($invoice->guest_email)) {
            $recipient = trim($invoice->guest_email);
        }
        if ($recipient === '' && $booking && !empty($booking->guest_email)) {
            $recipient = trim($booking->guest_email);
        }
        if ($recipient === '' || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($invoice && !empty($invoice->invoice_number)) {
            $label = $invoice->invoice_number;
        } elseif (!empty($qrph_payload['invoice_number'])) {
            $label = $qrph_payload['invoice_number'];
        } elseif ($booking && !empty($booking->booking_number)) {
            $label = $booking->booking_number;
        } elseif ($booking) {
            $label = 'BK' . $booking->id;
        } else {
            $label = 'payment';
        }

        $subject = 'Pay via QR Ph — ' . $label . ' - BODARE Pension House';
        $message = $this->build_qrph_html($booking, $qrph_payload, $invoice);

        $this->CI->coop_mail->set_profile('account');
        return $this->CI->coop_mail->send($recipient, $subject, $message);
    }

    public function build_qrph_html($booking, $qrph_payload, $invoice = null) {
        $guest_name = 'Guest';
        if ($invoice && !empty($invoice->guest_name)) {
            $guest_name = $invoice->guest_name;
        } elseif ($booking && !empty($booking->guest_name)) {
            $guest_name = $booking->guest_name;
        }

        $booking_label = null;
        if ($booking) {
            $booking_label = !empty($booking->booking_number) ? $booking->booking_number : ('BK' . $booking->id);
        } elseif (!empty($qrph_payload['booking_number'])) {
            $booking_label = $qrph_payload['booking_number'];
        }

        $amount = isset($qrph_payload['amount']) ? (float) $qrph_payload['amount'] : 0;
        if ($amount <= 0 && $invoice && isset($invoice->balance_due)) {
            $amount = (float) $invoice->balance_due;
        } elseif ($amount <= 0 && $booking && isset($booking->total_amount)) {
            $amount = (float) $booking->total_amount;
        }

        $expires = !empty($qrph_payload['expires_at']) ? date('F d, Y h:i A', strtotime($qrph_payload['expires_at'])) : null;
        $qr_url = !empty($qrph_payload['qr_image_url']) ? $qrph_payload['qr_image_url'] : null;
        $invoice_number = null;
        if ($invoice && !empty($invoice->invoice_number)) {
            $invoice_number = $invoice->invoice_number;
        } elseif (!empty($qrph_payload['invoice_number'])) {
            $invoice_number = $qrph_payload['invoice_number'];
        }

        $portal_url = null;
        $invoice_id = null;
        if ($invoice && !empty($invoice->id)) {
            $invoice_id = (int) $invoice->id;
        } elseif (!empty($qrph_payload['invoice_id'])) {
            $invoice_id = (int) $qrph_payload['invoice_id'];
        }
        if ($invoice_id) {
            $portal_url = $this->build_customer_portal_url($invoice_id);
        }

        $confirm_url = $booking_label ? $this->build_booking_confirmation_url($booking_label) : null;

        $html = '
        <div style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:640px;margin:0 auto;">
            <div style="border-bottom:3px solid #6576ff;padding-bottom:16px;margin-bottom:24px;">
                <h1 style="margin:0;color:#6576ff;font-size:22px;">BODARE Pension House</h1>
                <p style="margin:4px 0 0;color:#666;">QR Ph Payment</p>
            </div>
            <p>Dear <strong>' . htmlspecialchars($guest_name, ENT_QUOTES, 'UTF-8') . '</strong>,</p>
            <p>Please scan the QR Ph code below with your banking or e-wallet app to complete your payment.</p>
            <table style="width:100%;margin:16px 0;border-collapse:collapse;">';

        if ($booking_label) {
            $html .= '
                <tr>
                    <td style="padding:4px 0;"><strong>Booking #:</strong> ' . htmlspecialchars($booking_label, ENT_QUOTES, 'UTF-8') . '</td>
                    <td style="padding:4px 0;text-align:right;"><strong>Amount:</strong> ₱' . number_format($amount, 2) . '</td>
                </tr>';
        } else {
            $html .= '
                <tr>
                    <td style="padding:4px 0;" colspan="2"><strong>Amount:</strong> ₱' . number_format($amount, 2) . '</td>
                </tr>';
        }

        if ($invoice_number) {
            $html .= '
                <tr>
                    <td style="padding:4px 0;" colspan="2"><strong>Invoice #:</strong> ' . htmlspecialchars($invoice_number, ENT_QUOTES, 'UTF-8') . '</td>
                </tr>';
        }
        if ($expires) {
            $html .= '
                <tr>
                    <td style="padding:4px 0;" colspan="2"><strong>QR expires:</strong> ' . htmlspecialchars($expires, ENT_QUOTES, 'UTF-8') . '</td>
                </tr>';
        }

        $html .= '
            </table>';

        if ($qr_url) {
            $html .= '
            <div style="text-align:center;margin:24px 0;">
                <img src="' . htmlspecialchars($qr_url, ENT_QUOTES, 'UTF-8') . '" alt="QR Ph code" style="max-width:280px;width:100%;height:auto;border:1px solid #eee;border-radius:8px;">
            </div>';
        } else {
            $html .= '<p style="color:#856404;">QR image is not available in this email. Please use the link below to view and pay online.</p>';
        }

        if ($confirm_url) {
            $html .= '<p style="margin:16px 0;"><a href="' . htmlspecialchars($confirm_url, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#6576ff;color:#fff;padding:10px 18px;text-decoration:none;border-radius:6px;font-weight:bold;">Open payment page</a></p>';
        } elseif ($portal_url) {
            $html .= '<p style="margin:16px 0;"><a href="' . htmlspecialchars($portal_url, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#6576ff;color:#fff;padding:10px 18px;text-decoration:none;border-radius:6px;font-weight:bold;">View invoice &amp; pay online</a></p>';
        }
        if ($portal_url && $confirm_url) {
            $html .= '<p style="margin:8px 0;"><a href="' . htmlspecialchars($portal_url, ENT_QUOTES, 'UTF-8') . '" style="color:#6576ff;">View invoice online</a></p>';
        }

        $html .= '
            <p style="margin-top:24px;font-size:13px;color:#888;">If the QR code expires, open the payment page to generate a new one. Thank you for choosing BODARE Pension House.</p>
        </div>';

        return $html;
    }

    /**
     * Email guest a PayMongo Hosted Checkout link for card payment.
     *
     * @param object|null $booking
     * @param array $card_payload from Paymongo_service::start_card_checkout_*
     * @param object|null $invoice
     * @param string|null $to_email
     */
    public function send_card_checkout($booking, $card_payload, $invoice = null, $to_email = null) {
        if (empty($card_payload) || empty($card_payload['checkout_url']) || (!$booking && !$invoice)) {
            return false;
        }

        $recipient = $to_email ? trim($to_email) : '';
        if ($recipient === '' && $invoice && !empty($invoice->guest_email)) {
            $recipient = trim($invoice->guest_email);
        }
        if ($recipient === '' && $booking && !empty($booking->guest_email)) {
            $recipient = trim($booking->guest_email);
        }
        if ($recipient === '' || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($invoice && !empty($invoice->invoice_number)) {
            $label = $invoice->invoice_number;
        } elseif (!empty($card_payload['invoice_number'])) {
            $label = $card_payload['invoice_number'];
        } elseif ($booking && !empty($booking->booking_number)) {
            $label = $booking->booking_number;
        } elseif ($booking) {
            $label = 'BK' . $booking->id;
        } else {
            $label = 'payment';
        }

        $subject = 'Pay by Card — ' . $label . ' - BODARE Pension House';
        $message = $this->build_card_checkout_html($booking, $card_payload, $invoice);

        $this->CI->coop_mail->set_profile('account');
        return $this->CI->coop_mail->send($recipient, $subject, $message);
    }

    public function build_card_checkout_html($booking, $card_payload, $invoice = null) {
        $guest_name = 'Guest';
        if ($invoice && !empty($invoice->guest_name)) {
            $guest_name = $invoice->guest_name;
        } elseif ($booking && !empty($booking->guest_name)) {
            $guest_name = $booking->guest_name;
        }

        $amount = isset($card_payload['amount']) ? (float) $card_payload['amount'] : 0;
        $checkout_url = !empty($card_payload['checkout_url']) ? $card_payload['checkout_url'] : null;

        $invoice_number = null;
        if ($invoice && !empty($invoice->invoice_number)) {
            $invoice_number = $invoice->invoice_number;
        } elseif (!empty($card_payload['invoice_number'])) {
            $invoice_number = $card_payload['invoice_number'];
        }

        $booking_label = null;
        if ($booking && !empty($booking->booking_number)) {
            $booking_label = $booking->booking_number;
        } elseif (!empty($card_payload['booking_number'])) {
            $booking_label = $card_payload['booking_number'];
        }

        $html = '
        <div style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:640px;margin:0 auto;">
            <div style="border-bottom:3px solid #6576ff;padding-bottom:16px;margin-bottom:24px;">
                <h1 style="margin:0;color:#6576ff;font-size:22px;">BODARE Pension House</h1>
                <p style="margin:4px 0 0;color:#666;">Secure Card Payment</p>
            </div>
            <p>Dear <strong>' . htmlspecialchars($guest_name, ENT_QUOTES, 'UTF-8') . '</strong>,</p>
            <p>Please use the secure PayMongo checkout link below to pay by credit or debit card. Your card details are entered only on PayMongo&rsquo;s page — we never store your full card number.</p>
            <table style="width:100%;margin:16px 0;border-collapse:collapse;">
                <tr>
                    <td style="padding:4px 0;"><strong>Amount:</strong> ₱' . number_format($amount, 2) . '</td>
                </tr>';

        if ($invoice_number) {
            $html .= '
                <tr>
                    <td style="padding:4px 0;"><strong>Invoice #:</strong> ' . htmlspecialchars($invoice_number, ENT_QUOTES, 'UTF-8') . '</td>
                </tr>';
        }
        if ($booking_label) {
            $html .= '
                <tr>
                    <td style="padding:4px 0;"><strong>Booking #:</strong> ' . htmlspecialchars($booking_label, ENT_QUOTES, 'UTF-8') . '</td>
                </tr>';
        }

        $html .= '
            </table>';

        if ($checkout_url) {
            $html .= '<p style="margin:24px 0;text-align:center;"><a href="' . htmlspecialchars($checkout_url, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#6576ff;color:#fff;padding:12px 22px;text-decoration:none;border-radius:6px;font-weight:bold;">Pay securely by card</a></p>';
            $html .= '<p style="font-size:13px;color:#666;word-break:break-all;">Or copy this link:<br>' . htmlspecialchars($checkout_url, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        $html .= '
            <p style="margin-top:24px;font-size:13px;color:#888;">If the link expires, ask the hotel to send a new card payment link. Thank you for choosing BODARE Pension House.</p>
        </div>';

        return $html;
    }

    protected function build_customer_portal_url($invoice_id) {
        $this->CI->load->helper('url');
        $admin_base = rtrim(base_url(), '/');
        $site_root = preg_replace('#/admin/?$#', '', $admin_base);
        return $site_root . '/customer-invoices.php?id=' . (int) $invoice_id;
    }

    protected function build_booking_confirmation_url($booking_number) {
        $this->CI->load->helper('url');
        $admin_base = rtrim(base_url(), '/');
        $site_root = preg_replace('#/admin/?$#', '', $admin_base);
        return $site_root . '/booking-confirmation.php?booking=' . rawurlencode($booking_number) . '&payment=qrph';
    }
}
