<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Events extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Event_model');
        $this->load->model('Booking_model');
        $this->load->model('Invoice_model');
        $this->load->library('form_validation');
    }

    public function index() {
        $this->require_permission('view_events');

        $filters = array();
        $status = $this->input->get('status');
        if ($status) {
            $filters['status'] = $status;
        }

        $data['title'] = 'Hotel Events';
        $data['events'] = $this->Event_model->get_all($filters);
        $data['filter_status'] = $status;
        $data['can_add'] = $this->has_permission('add_events');
        $data['can_edit'] = $this->has_permission('edit_events');
        $data['can_delete'] = $this->has_permission('delete_events');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/events/index', $data);
        $this->load->view('admin/layout/footer');
    }

    public function view($id) {
        $this->require_permission('view_events');

        $event = $this->Event_model->get($id);
        if (!$event) {
            show_404();
            return;
        }

        $data['title'] = 'Event Details';
        $data['event'] = $event;
        $data['invoices'] = $this->Invoice_model->get_all(array('event_id' => $id));
        $data['can_edit'] = $this->has_permission('edit_events');
        $data['can_delete'] = $this->has_permission('delete_events');
        $data['can_add_invoice'] = $this->has_permission('add_invoices');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/events/view', $data);
        $this->load->view('admin/layout/footer');
    }

    public function add() {
        $this->require_permission('add_events');

        $data['title'] = 'Add Event';
        $data['bookings'] = $this->Booking_model->get_all_bookings();

        if ($this->input->post()) {
            $this->form_validation->set_rules('event_name', 'Event Name', 'required|trim');
            $this->form_validation->set_rules('event_date', 'Event Date', 'required');
            $this->form_validation->set_rules('organizer_name', 'Organizer Name', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $event_data = array(
                    'event_name' => $this->input->post('event_name'),
                    'event_type' => $this->input->post('event_type'),
                    'venue' => $this->input->post('venue'),
                    'event_date' => $this->input->post('event_date'),
                    'start_time' => $this->input->post('start_time') ? $this->input->post('start_time') : null,
                    'end_time' => $this->input->post('end_time') ? $this->input->post('end_time') : null,
                    'organizer_name' => $this->input->post('organizer_name'),
                    'organizer_email' => $this->input->post('organizer_email'),
                    'organizer_phone' => $this->input->post('organizer_phone'),
                    'expected_guests' => (int) $this->input->post('expected_guests'),
                    'total_amount' => (float) $this->input->post('total_amount'),
                    'status' => $this->input->post('status'),
                    'booking_id' => $this->input->post('booking_id') ? (int) $this->input->post('booking_id') : null,
                    'notes' => $this->input->post('notes'),
                    'admin_id' => $this->admin_id
                );

                $event_id = $this->Event_model->create($event_data);
                if ($event_id) {
                    $this->session->set_flashdata('success', 'Event created successfully.');
                    redirect('events/view/' . $event_id);
                }
                $this->session->set_flashdata('error', 'Failed to create event.');
            }
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/events/add', $data);
        $this->load->view('admin/layout/footer');
    }

    public function edit($id) {
        $this->require_permission('edit_events');

        $event = $this->Event_model->get($id);
        if (!$event) {
            show_404();
            return;
        }

        $data['title'] = 'Edit Event';
        $data['event'] = $event;
        $data['bookings'] = $this->Booking_model->get_all_bookings();

        if ($this->input->post()) {
            $this->form_validation->set_rules('event_name', 'Event Name', 'required|trim');
            $this->form_validation->set_rules('event_date', 'Event Date', 'required');
            $this->form_validation->set_rules('organizer_name', 'Organizer Name', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $event_data = array(
                    'event_name' => $this->input->post('event_name'),
                    'event_type' => $this->input->post('event_type'),
                    'venue' => $this->input->post('venue'),
                    'event_date' => $this->input->post('event_date'),
                    'start_time' => $this->input->post('start_time') ? $this->input->post('start_time') : null,
                    'end_time' => $this->input->post('end_time') ? $this->input->post('end_time') : null,
                    'organizer_name' => $this->input->post('organizer_name'),
                    'organizer_email' => $this->input->post('organizer_email'),
                    'organizer_phone' => $this->input->post('organizer_phone'),
                    'expected_guests' => (int) $this->input->post('expected_guests'),
                    'total_amount' => (float) $this->input->post('total_amount'),
                    'status' => $this->input->post('status'),
                    'booking_id' => $this->input->post('booking_id') ? (int) $this->input->post('booking_id') : null,
                    'notes' => $this->input->post('notes')
                );

                if ($this->Event_model->update($id, $event_data)) {
                    $this->session->set_flashdata('success', 'Event updated successfully.');
                    redirect('events/view/' . $id);
                }
                $this->session->set_flashdata('error', 'Failed to update event.');
            }
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/events/edit', $data);
        $this->load->view('admin/layout/footer');
    }

    public function delete($id) {
        $this->require_permission('delete_events');

        if ($this->Event_model->delete($id)) {
            $this->session->set_flashdata('success', 'Event deleted.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete event.');
        }
        redirect('events');
    }

    public function create_invoice($id) {
        $this->require_permission('add_invoices');

        $event = $this->Event_model->get($id);
        if (!$event) {
            show_404();
            return;
        }

        $rates = $this->Invoice_model->get_default_rates();
        $items = array();
        if ((float) $event->total_amount > 0) {
            $items[] = array(
                'item_type' => 'event',
                'description' => $event->event_name . ' - Event Package',
                'quantity' => 1,
                'unit_price' => (float) $event->total_amount,
                'event_id' => $event->id
            );
        }

        $invoice_id = $this->Invoice_model->create(array(
            'event_id' => $event->id,
            'booking_id' => $event->booking_id,
            'guest_name' => $event->organizer_name,
            'guest_email' => $event->organizer_email,
            'guest_phone' => $event->organizer_phone,
            'tax_rate' => $rates['tax_rate'],
            'service_charge_rate' => $rates['service_charge_rate'],
            'status' => 'draft',
            'admin_id' => $this->admin_id,
            'notes' => 'Invoice for event: ' . $event->event_name
        ), $items);

        if ($invoice_id) {
            $this->Invoice_model->issue($invoice_id);
            $this->session->set_flashdata('success', 'Event invoice created.');
            redirect('invoices/view/' . $invoice_id);
        }

        $this->session->set_flashdata('error', 'Failed to create event invoice.');
        redirect('events/view/' . $id);
    }
}
