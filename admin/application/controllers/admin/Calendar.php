<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH . 'core/Admin_Controller.php');
}

/**
 * Unified Hotel Calendar — room stays + hotel events
 */
class Calendar extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Booking_model');
        $this->load->model('Event_model');
        $this->load->model('Room_model');
    }

    public function index() {
        $this->require_calendar_access();

        $data['title'] = 'Calendar';
        $data['rooms'] = $this->Room_model->get_all_rooms();
        $data['can_view_bookings'] = $this->has_permission('view_bookings');
        $data['can_view_events'] = $this->has_permission('view_events');
        $data['can_add_bookings'] = $this->has_permission('add_bookings');
        $data['can_add_events'] = $this->has_permission('add_events');
        $data['today_summary'] = $this->build_today_summary();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/calendar/index', $data);
        $this->load->view('admin/layout/footer');
    }

    /**
     * JSON feed for FullCalendar (room bookings + hotel events)
     */
    public function feed() {
        $this->require_calendar_access();
        header('Content-Type: application/json');

        $start = $this->input->get('start') ? date('Y-m-d', strtotime($this->input->get('start'))) : date('Y-m-01');
        $end = $this->input->get('end') ? date('Y-m-d', strtotime($this->input->get('end'))) : date('Y-m-t');
        $type = $this->input->get('type') ? strtolower(trim($this->input->get('type'))) : 'all';
        $status = $this->input->get('status') ? strtolower(trim($this->input->get('status'))) : '';
        $room_id = $this->input->get('room_id') ? (int) $this->input->get('room_id') : null;
        $include_cancelled = $this->input->get('include_cancelled') === '1' || $status === 'cancelled';

        if (!in_array($type, array('all', 'room', 'event'), true)) {
            $type = 'all';
        }

        $events = array();

        if ($type === 'all' || $type === 'room') {
            if ($this->has_permission('view_bookings') || $this->has_permission('view_calendar')) {
                $events = array_merge(
                    $events,
                    $this->Booking_model->get_calendar_feed($start, $end, $room_id, $status, $include_cancelled)
                );
            }
        }

        if (($type === 'all' || $type === 'event') && !$room_id) {
            if ($this->db->table_exists('events') && ($this->has_permission('view_events') || $this->has_permission('view_calendar'))) {
                $events = array_merge(
                    $events,
                    $this->Event_model->get_calendar_feed($start, $end, $status, $include_cancelled)
                );
            }
        }

        echo json_encode($events);
    }

    /**
     * Compact day summary for the calendar header cards
     */
    public function summary() {
        $this->require_calendar_access();
        header('Content-Type: application/json');

        $date = $this->input->get('date') ? date('Y-m-d', strtotime($this->input->get('date'))) : date('Y-m-d');
        echo json_encode(array(
            'success' => true,
            'date' => $date,
            'summary' => $this->build_today_summary($date)
        ));
    }

    private function require_calendar_access() {
        if ($this->db->table_exists('permissions')) {
            if ($this->has_permission('view_calendar')) {
                return;
            }
            // Soft fallback if permission row not yet seeded
            if ($this->has_permission('view_bookings') || $this->has_permission('view_events')) {
                return;
            }
            $this->require_permission('view_calendar');
            return;
        }

        if (!$this->is_super_admin()) {
            $this->session->set_flashdata('error', 'You do not have permission to access this page.');
            redirect('dashboard');
        }
    }

    private function build_today_summary($date = null) {
        $date = $date ? date('Y-m-d', strtotime($date)) : date('Y-m-d');
        $summary = array(
            'date' => $date,
            'check_ins' => 0,
            'check_outs' => 0,
            'in_house' => 0,
            'events_today' => 0,
            'pending_bookings' => 0
        );

        if ($this->db->table_exists('bookings')) {
            $this->db->from('bookings');
            $this->db->where('status !=', 'cancelled');
            $this->db->where('DATE(check_in)', $date);
            $summary['check_ins'] = (int) $this->db->count_all_results();

            $this->db->from('bookings');
            $this->db->where('status !=', 'cancelled');
            $this->db->where('DATE(check_out)', $date);
            $summary['check_outs'] = (int) $this->db->count_all_results();

            $this->db->from('bookings');
            $this->db->where('status !=', 'cancelled');
            $this->db->where('DATE(check_in) <=', $date);
            $this->db->where('DATE(check_out) >', $date);
            $summary['in_house'] = (int) $this->db->count_all_results();

            $this->db->from('bookings');
            $this->db->where('status', 'pending');
            $this->db->where('DATE(check_in) <=', $date);
            $this->db->where('DATE(check_out) >=', $date);
            $summary['pending_bookings'] = (int) $this->db->count_all_results();
        }

        if ($this->db->table_exists('events')) {
            $this->db->from('events');
            $this->db->where('status !=', 'cancelled');
            $this->db->where('event_date', $date);
            $summary['events_today'] = (int) $this->db->count_all_results();
        }

        return $summary;
    }
}
