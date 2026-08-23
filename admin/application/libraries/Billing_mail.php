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

    protected function build_customer_portal_url($invoice_id) {
        $this->CI->load->helper('url');
        $admin_base = rtrim(base_url(), '/');
        $site_root = preg_replace('#/admin/?$#', '', $admin_base);
        return $site_root . '/customer-invoices.php?id=' . (int) $invoice_id;
    }
}
