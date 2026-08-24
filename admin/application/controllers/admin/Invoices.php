<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Invoices extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Invoice_model');
        $this->load->model('Invoice_item_model');
        $this->load->model('Payment_model');
        $this->load->model('Booking_model');
        $this->load->model('Booking_item_model');
        $this->load->model('Event_model');
        $this->load->model('Customer_model');
        $this->load->library('form_validation');
    }

    public function index() {
        $this->require_permission('view_invoices');

        $filters = array();
        $status = $this->input->get('status');
        if ($status) {
            $filters['status'] = $status;
        }

        $data['title'] = 'Room Billing Invoices';
        $data['invoices'] = $this->Invoice_model->get_all($filters);
        $data['filter_status'] = $status;
        $data['can_add'] = $this->has_permission('add_invoices');
        $data['can_edit'] = $this->has_permission('edit_invoices');
        $data['can_delete'] = $this->has_permission('delete_invoices');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/invoices/index', $data);
        $this->load->view('admin/layout/footer');
    }

    public function view($id) {
        $this->require_permission('view_invoices');

        $invoice = $this->Invoice_model->get($id);
        if (!$invoice) {
            show_404();
            return;
        }

        $data['title'] = 'Invoice ' . $invoice->invoice_number;
        $data['invoice'] = $invoice;
        $data['items'] = $this->Invoice_item_model->get_by_invoice($id);
        $data['payments'] = $this->Payment_model->get_by_invoice($id);
        $data['can_edit'] = $this->has_permission('edit_invoices');
        $data['can_delete'] = $this->has_permission('delete_invoices');
        $data['can_add_payment'] = $this->has_permission('add_payments');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/invoices/view', $data);
        $this->load->view('admin/layout/footer');
    }

    public function print_invoice($id) {
        $this->require_permission('view_invoices');

        $invoice = $this->Invoice_model->get($id);
        if (!$invoice) {
            show_404();
            return;
        }

        $data['invoice'] = $invoice;
        $data['items'] = $this->Invoice_item_model->get_by_invoice($id);
        $data['payments'] = $this->Payment_model->get_by_invoice($id);
        $this->load->view('admin/invoices/print', $data);
    }

    public function add() {
        $this->require_permission('add_invoices');

        $data['title'] = 'Create Invoice';
        $data['bookings'] = $this->Booking_model->get_all_bookings();
        $data['events'] = $this->Event_model->get_for_select();
        $data['customers'] = $this->Customer_model->get_all_with_user_info();
        $data['rates'] = $this->Invoice_model->get_default_rates();

        if ($this->input->post()) {
            $this->form_validation->set_rules('guest_name', 'Guest Name', 'required|trim');
            $this->form_validation->set_rules('guest_email', 'Guest Email', 'trim|valid_email');

            if ($this->form_validation->run() === TRUE) {
                $rates = $this->Invoice_model->get_default_rates();
                $booking_id_post = $this->input->post('booking_id') ? (int) $this->input->post('booking_id') : null;

                if ($booking_id_post && !$this->input->post('allow_duplicate_invoice')) {
                    $existing = $this->Invoice_model->get_primary_for_booking($booking_id_post);
                    if ($existing) {
                        $this->session->set_flashdata(
                            'error',
                            'Booking already has invoice ' . $existing->invoice_number
                            . ' (' . ucfirst($existing->status) . '). Open that invoice instead, or confirm duplicate below.'
                        );
                        $data['existing_invoice'] = $existing;
                        $data['require_duplicate_confirm'] = true;
                        $this->load->view('admin/layout/header', $data);
                        $this->load->view('admin/invoices/add', $data);
                        $this->load->view('admin/layout/footer');
                        return;
                    }
                }

                $invoice_data = array(
                    'booking_id' => $booking_id_post,
                    'event_id' => $this->input->post('event_id') ? (int) $this->input->post('event_id') : null,
                    'guest_name' => $this->input->post('guest_name'),
                    'guest_email' => $this->input->post('guest_email'),
                    'guest_phone' => $this->input->post('guest_phone'),
                    'tax_rate' => $this->input->post('tax_rate') !== '' ? (float) $this->input->post('tax_rate') : $rates['tax_rate'],
                    'service_charge_rate' => $this->input->post('service_charge_rate') !== '' ? (float) $this->input->post('service_charge_rate') : $rates['service_charge_rate'],
                    'discount_amount' => (float) $this->input->post('discount_amount'),
                    'due_date' => $this->input->post('due_date') ? $this->input->post('due_date') : null,
                    'notes' => $this->input->post('notes'),
                    'status' => 'draft',
                    'admin_id' => $this->admin_id
                );

                $items = $this->parse_line_items_from_post();
                if (empty($items) && $invoice_data['booking_id']) {
                    $booking = $this->Booking_model->get_booking($invoice_data['booking_id']);
                    $booking_items = $this->db->table_exists('booking_items')
                        ? $this->Booking_item_model->get_booking_items($invoice_data['booking_id'])
                        : array();
                    $items = $this->Invoice_model->build_items_from_booking($booking, $booking_items);
                }

                $invoice_id = $this->Invoice_model->create($invoice_data, $items);
                if ($invoice_id) {
                    if ($this->input->post('issue_now')) {
                        $this->Invoice_model->issue($invoice_id);
                    }
                    $this->session->set_flashdata('success', 'Invoice created successfully.');
                    redirect('invoices/view/' . $invoice_id);
                }
                $this->session->set_flashdata('error', 'Failed to create invoice.');
            }
        }

        $booking_id = $this->input->get('booking_id');
        if ($booking_id) {
            $booking = $this->Booking_model->get_booking($booking_id);
            if ($booking) {
                $data['prefill'] = array(
                    'booking_id' => $booking->id,
                    'guest_name' => $booking->guest_name,
                    'guest_email' => $booking->guest_email,
                    'guest_phone' => $booking->guest_phone
                );
                $data['prefill_items'] = $this->Invoice_model->build_items_from_booking(
                    $booking,
                    $this->db->table_exists('booking_items') ? $this->Booking_item_model->get_booking_items($booking_id) : array()
                );

                $existing = $this->Invoice_model->get_primary_for_booking($booking_id);
                if ($existing) {
                    $data['existing_invoice'] = $existing;
                    $data['require_duplicate_confirm'] = true;
                }
            }
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/invoices/add', $data);
        $this->load->view('admin/layout/footer');
    }

    public function edit($id) {
        $this->require_permission('edit_invoices');

        $invoice = $this->Invoice_model->get($id);
        if (!$invoice) {
            show_404();
            return;
        }

        $data['title'] = 'Edit Invoice';
        $data['invoice'] = $invoice;
        $data['items'] = $this->Invoice_item_model->get_by_invoice($id);
        $data['bookings'] = $this->Booking_model->get_all_bookings();
        $data['events'] = $this->Event_model->get_for_select();

        if ($this->input->post()) {
            $this->form_validation->set_rules('guest_name', 'Guest Name', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $update = array(
                    'booking_id' => $this->input->post('booking_id') ? (int) $this->input->post('booking_id') : null,
                    'event_id' => $this->input->post('event_id') ? (int) $this->input->post('event_id') : null,
                    'guest_name' => $this->input->post('guest_name'),
                    'guest_email' => $this->input->post('guest_email'),
                    'guest_phone' => $this->input->post('guest_phone'),
                    'tax_rate' => (float) $this->input->post('tax_rate'),
                    'service_charge_rate' => (float) $this->input->post('service_charge_rate'),
                    'discount_amount' => (float) $this->input->post('discount_amount'),
                    'due_date' => $this->input->post('due_date') ? $this->input->post('due_date') : null,
                    'notes' => $this->input->post('notes')
                );

                $this->Invoice_model->update($id, $update);

                $this->Invoice_item_model->delete_by_invoice($id);
                $items = $this->parse_line_items_from_post();
                foreach ($items as $item) {
                    $item['invoice_id'] = $id;
                    $this->Invoice_item_model->create($item);
                }
                $this->Invoice_model->recalculate_totals($id);

                $this->session->set_flashdata('success', 'Invoice updated successfully.');
                redirect('invoices/view/' . $id);
            }
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/invoices/edit', $data);
        $this->load->view('admin/layout/footer');
    }

    public function add_charge($id) {
        $this->require_permission('edit_invoices');

        $invoice = $this->Invoice_model->get($id);
        if (!$invoice) {
            show_404();
            return;
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('description', 'Description', 'required|trim');
            $this->form_validation->set_rules('unit_price', 'Amount', 'required|numeric');

            if ($this->form_validation->run() === TRUE) {
                $this->Invoice_item_model->create(array(
                    'invoice_id' => $id,
                    'item_type' => $this->input->post('item_type') ? $this->input->post('item_type') : 'other',
                    'description' => $this->input->post('description'),
                    'quantity' => (float) $this->input->post('quantity') ?: 1,
                    'unit_price' => (float) $this->input->post('unit_price')
                ));
                $this->Invoice_model->recalculate_totals($id);
                $this->session->set_flashdata('success', 'Additional charge added.');
            }
        }

        redirect('invoices/view/' . $id);
    }

    public function issue($id) {
        $this->require_permission('edit_invoices');
        $invoice = $this->Invoice_model->get($id);
        if (!$invoice) {
            show_404();
            return;
        }
        $this->Invoice_model->issue($id);
        $this->Invoice_model->recalculate_totals($id);
        $this->session->set_flashdata('success', 'Invoice issued.');
        redirect('invoices/view/' . $id);
    }

    public function void($id) {
        $this->require_permission('delete_invoices');
        $invoice = $this->Invoice_model->get($id);
        if (!$invoice) {
            show_404();
            return;
        }
        $this->Invoice_model->void_invoice($id);
        $this->session->set_flashdata('success', 'Invoice voided.');
        redirect('invoices');
    }

    public function delete($id) {
        $this->require_permission('delete_invoices');
        if ($this->Invoice_model->delete($id)) {
            $this->session->set_flashdata('success', 'Invoice deleted.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete invoice.');
        }
        redirect('invoices');
    }

    public function from_booking($booking_id) {
        $this->require_permission('add_invoices');

        $existing = $this->Invoice_model->get_primary_for_booking($booking_id);
        if ($existing && !$this->input->get('force')) {
            $this->session->set_flashdata(
                'error',
                'This booking already has invoice ' . $existing->invoice_number
                . ' (' . ucfirst($existing->status) . ', balance ₱' . number_format($existing->balance_due, 2)
                . '). Use the existing invoice to avoid double billing.'
            );
            redirect('invoices/view/' . $existing->id);
            return;
        }

        redirect('invoices/add?booking_id=' . (int) $booking_id . ($this->input->get('force') ? '&force=1' : ''));
    }

    public function send_email($id) {
        $this->require_permission('edit_invoices');

        $invoice = $this->Invoice_model->get($id);
        if (!$invoice) {
            show_404();
            return;
        }

        if ($invoice->status === 'void') {
            $this->session->set_flashdata('error', 'Cannot email a voided invoice.');
            redirect('invoices/view/' . $id);
            return;
        }

        if (empty($invoice->guest_email)) {
            $this->session->set_flashdata('error', 'Guest email is required to send the invoice.');
            redirect('invoices/view/' . $id);
            return;
        }

        $items = $this->Invoice_item_model->get_by_invoice($id);
        $payments = $this->Payment_model->get_by_invoice($id);

        $this->load->library('billing_mail');
        if ($this->billing_mail->send_invoice($invoice, $items, $payments)) {
            $this->Invoice_model->mark_emailed($id);
            if ($invoice->status === 'draft') {
                $this->Invoice_model->issue($id);
            }
            $this->session->set_flashdata('success', 'Invoice emailed to ' . $invoice->guest_email . '.');
        } else {
            $error = $this->billing_mail->get_last_error();
            $this->session->set_flashdata('error', 'Failed to send invoice email. ' . ($error ?: 'Check SMTP settings.'));
        }

        redirect('invoices/view/' . $id);
    }

    private function parse_line_items_from_post() {
        $items = array();
        $descriptions = $this->input->post('item_description');
        $types = $this->input->post('item_type');
        $quantities = $this->input->post('item_quantity');
        $prices = $this->input->post('item_unit_price');

        if (!is_array($descriptions)) {
            return $items;
        }

        foreach ($descriptions as $i => $desc) {
            $desc = trim($desc);
            if ($desc === '') {
                continue;
            }
            $items[] = array(
                'item_type' => isset($types[$i]) ? $types[$i] : 'other',
                'description' => $desc,
                'quantity' => isset($quantities[$i]) ? (float) $quantities[$i] : 1,
                'unit_price' => isset($prices[$i]) ? (float) $prices[$i] : 0
            );
        }

        return $items;
    }
}
