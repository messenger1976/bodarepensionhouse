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
        $data['can_view_bookings'] = $this->can_view_room_stays();
        $data['can_view_events'] = $this->can_view_hotel_events();
        $data['can_add_bookings'] = $this->has_permission('add_bookings');
        $data['can_add_events'] = $this->has_permission('add_events');
        $data['today_summary'] = $this->build_today_summary();
        $data['initial_calendar_events'] = $this->load_calendar_events(
            date('Y-m-01'),
            date('Y-m-d', strtotime(date('Y-m-01') . ' +2 months')),
            'all',
            '',
            null,
            false
        );

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/calendar/index', $data);
        $this->load->view('admin/layout/footer');
    }

    /**
     * JSON feed for FullCalendar (room bookings + hotel events)
     */
    public function feed() {
        $this->require_calendar_access();

        $start_raw = $this->input->get('start');
        $end_raw = $this->input->get('end');
        $start = $this->parse_calendar_date($start_raw, date('Y-m-01'));
        $end = $this->parse_calendar_date(
            $end_raw,
            date('Y-m-d', strtotime($start . ' +1 month'))
        );
        $type = $this->input->get('type') ? strtolower(trim($this->input->get('type'))) : 'all';
        $status = $this->input->get('status') ? strtolower(trim($this->input->get('status'))) : '';
        $room_id = $this->input->get('room_id') ? (int) $this->input->get('room_id') : null;
        $include_cancelled = $this->input->get('include_cancelled') === '1' || $status === 'cancelled';

        if (!in_array($type, array('all', 'room', 'event'), true)) {
            $type = 'all';
        }

        $events = $this->load_calendar_events($start, $end, $type, $status, $room_id, $include_cancelled);

        $json_flags = JSON_UNESCAPED_UNICODE;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $json_flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(array_values($events), $json_flags));
    }

    /**
     * Compact day summary for the calendar header cards
     */
    public function summary() {
        $this->require_calendar_access();
        header('Content-Type: application/json');

        $date = $this->input->get('date') ? $this->parse_calendar_date($this->input->get('date'), date('Y-m-d')) : date('Y-m-d');
        echo json_encode(array(
            'success' => true,
            'date' => $date,
            'summary' => $this->build_today_summary($date)
        ));
    }

    private function require_calendar_access() {
        if ($this->is_super_admin()) {
            return;
        }

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

        $this->session->set_flashdata('error', 'You do not have permission to access this page.');
        redirect('dashboard');
    }

    private function can_view_room_stays() {
        return $this->is_super_admin()
            || $this->has_permission('view_bookings')
            || $this->has_permission('view_calendar');
    }

    private function can_view_hotel_events() {
        return $this->is_super_admin()
            || $this->has_permission('view_events')
            || $this->has_permission('view_calendar');
    }

    private function load_calendar_events($start, $end, $type, $status, $room_id, $include_cancelled) {
        $events = array();

        if ($type === 'all' || $type === 'room') {
            if ($this->can_view_room_stays()) {
                $events = array_merge(
                    $events,
                    $this->Booking_model->get_calendar_feed($start, $end, $room_id, $status, $include_cancelled)
                );
            }
        }

        if (($type === 'all' || $type === 'event') && !$room_id) {
            if ($this->db->table_exists('events') && $this->can_view_hotel_events()) {
                $events = array_merge(
                    $events,
                    $this->Event_model->get_calendar_feed($start, $end, $status, $include_cancelled)
                );
            }
        }

        return $events;
    }

    /**
     * Parse FullCalendar ISO dates without shifting days due to server timezone.
     */
    private function parse_calendar_date($raw, $fallback) {
        if (!$raw) {
            return $fallback;
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', trim($raw), $matches)) {
            return $matches[1];
        }

        $timestamp = strtotime($raw);
        return $timestamp ? date('Y-m-d', $timestamp) : $fallback;
    }

    private function build_today_summary($date = null) {
        $date = $date ? $this->parse_calendar_date($date, date('Y-m-d')) : date('Y-m-d');
        $summary = array(
            'date' => $date,
            'check_ins' => 0,
            'check_outs' => 0,
            'in_house' => 0,
            'events_today' => 0,
            'pending_bookings' => 0
        );

        if ($this->db->table_exists('booking_items')) {
            $this->db->from('booking_items');
            $this->db->join('bookings', 'bookings.id = booking_items.booking_id', 'inner');
            $this->db->where('bookings.status !=', 'cancelled');
            $this->db->where('booking_items.status !=', 'cancelled');
            $this->db->where('DATE(booking_items.check_in)', $date);
            $summary['check_ins'] = (int) $this->db->count_all_results();

            $this->db->from('booking_items');
            $this->db->join('bookings', 'bookings.id = booking_items.booking_id', 'inner');
            $this->db->where('bookings.status !=', 'cancelled');
            $this->db->where('booking_items.status !=', 'cancelled');
            $this->db->where('DATE(booking_items.check_out)', $date);
            $summary['check_outs'] = (int) $this->db->count_all_results();

            $this->db->from('booking_items');
            $this->db->join('bookings', 'bookings.id = booking_items.booking_id', 'inner');
            $this->db->where('bookings.status !=', 'cancelled');
            $this->db->where('booking_items.status !=', 'cancelled');
            $this->db->where('DATE(booking_items.check_in) <=', $date);
            $this->db->where('DATE(booking_items.check_out) >', $date);
            $summary['in_house'] = (int) $this->db->count_all_results();

            $this->db->from('booking_items');
            $this->db->join('bookings', 'bookings.id = booking_items.booking_id', 'inner');
            $this->db->where('bookings.status', 'pending');
            $this->db->where('booking_items.status !=', 'cancelled');
            $this->db->where('DATE(booking_items.check_in) <=', $date);
            $this->db->where('DATE(booking_items.check_out) >=', $date);
            $summary['pending_bookings'] = (int) $this->db->count_all_results();
        } elseif ($this->db->table_exists('bookings')) {
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
