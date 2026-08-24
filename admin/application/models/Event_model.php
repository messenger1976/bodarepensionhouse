<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($filters = array()) {
        $this->db->select('events.*, bookings.booking_number');
        $this->db->from('events');
        $this->db->join('bookings', 'bookings.id = events.booking_id', 'left');
        $this->db->order_by('events.event_date', 'DESC');

        if (!empty($filters['status'])) {
            $this->db->where('events.status', $filters['status']);
        }
        if (!empty($filters['from_date'])) {
            $this->db->where('events.event_date >=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $this->db->where('events.event_date <=', $filters['to_date']);
        }

        return $this->db->get()->result();
    }

    public function get($id) {
        $this->db->select('events.*, bookings.booking_number, bookings.guest_name as booking_guest');
        $this->db->from('events');
        $this->db->join('bookings', 'bookings.id = events.booking_id', 'left');
        $this->db->where('events.id', (int) $id);
        return $this->db->get()->row();
    }

    public function create($data) {
        $this->db->insert('events', $data);
        if ($this->db->error()['code'] != 0) {
            return false;
        }
        $id = $this->db->insert_id();
        $this->update_event_number($id);
        return $id;
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        return $this->db->update('events', $data);
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('events');
    }

    public function update_event_number($id) {
        $number = 'EV' . str_pad($id, 8, '0', STR_PAD_LEFT);
        $this->db->where('id', (int) $id);
        $this->db->update('events', array('event_number' => $number));
        return $number;
    }

    public function get_for_select() {
        $this->db->select('id, event_number, event_name, event_date, status');
        $this->db->where('status !=', 'cancelled');
        $this->db->order_by('event_date', 'DESC');
        return $this->db->get('events')->result();
    }

    public function get_for_date_range($from_date, $to_date) {
        $this->db->select('events.*, bookings.booking_number');
        $this->db->from('events');
        $this->db->join('bookings', 'bookings.id = events.booking_id', 'left');
        $this->db->where('events.event_date >=', $from_date);
        $this->db->where('events.event_date <=', $to_date);
        $this->db->order_by('events.event_date', 'ASC');
        return $this->db->get()->result();
    }

    public function get_summary_for_range($from_date, $to_date) {
        $this->db->select("
            COUNT(id) as event_count,
            COALESCE(SUM(total_amount), 0) as total_amount,
            COALESCE(SUM(expected_guests), 0) as total_guests,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = 'inquiry' THEN 1 ELSE 0 END) as inquiry_count,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
        ", FALSE);
        $this->db->from('events');
        $this->db->where('event_date >=', $from_date);
        $this->db->where('event_date <=', $to_date);
        $row = $this->db->get()->row();
        return array(
            'event_count' => $row ? (int) $row->event_count : 0,
            'total_amount' => $row ? (float) $row->total_amount : 0.0,
            'total_guests' => $row ? (int) $row->total_guests : 0,
            'confirmed_count' => $row ? (int) $row->confirmed_count : 0,
            'completed_count' => $row ? (int) $row->completed_count : 0,
            'inquiry_count' => $row ? (int) $row->inquiry_count : 0,
            'cancelled_count' => $row ? (int) $row->cancelled_count : 0
        );
    }

    public function get_totals_by_type($from_date, $to_date) {
        $this->db->select('event_type, COUNT(id) as event_count, SUM(total_amount) as total_amount');
        $this->db->from('events');
        $this->db->where('event_date >=', $from_date);
        $this->db->where('event_date <=', $to_date);
        $this->db->where('status !=', 'cancelled');
        $this->db->group_by('event_type');
        $this->db->order_by('total_amount', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * FullCalendar feed entries for hotel events / functions
     */
    public function get_calendar_feed($start_date, $end_date, $status = null, $include_cancelled = false) {
        if (!$this->db->table_exists('events')) {
            return array();
        }

        $this->load->helper('url');
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));

        $this->db->select('events.*, bookings.booking_number');
        $this->db->from('events');
        $this->db->join('bookings', 'bookings.id = events.booking_id', 'left');
        $this->db->where('events.event_date >=', $start_date);
        $this->db->where('events.event_date <=', $end_date);

        if (!$include_cancelled) {
            $this->db->where('events.status !=', 'cancelled');
        }
        if ($status) {
            $this->db->where('events.status', $status);
        }

        $this->db->order_by('events.event_date', 'ASC');
        $this->db->order_by('events.start_time', 'ASC');
        $rows = $this->db->get()->result();

        $entries = array();
        foreach ($rows as $row) {
            $event_date = date('Y-m-d', strtotime($row->event_date));
            $has_time = !empty($row->start_time);
            $start = $has_time
                ? $event_date . 'T' . date('H:i:s', strtotime($row->start_time))
                : $event_date;
            $end = null;
            if ($has_time && !empty($row->end_time)) {
                $end = $event_date . 'T' . date('H:i:s', strtotime($row->end_time));
            }

            $event_number = !empty($row->event_number)
                ? $row->event_number
                : ('EV' . str_pad($row->id, 8, '0', STR_PAD_LEFT));
            $type_label = $row->event_type ? ucfirst($row->event_type) : 'Event';

            $entry = array(
                'id' => 'event-' . $row->id,
                'title' => $row->event_name . ' · ' . $type_label,
                'start' => $start,
                'allDay' => !$has_time,
                'classNames' => array('cal-event', 'cal-event-' . ($row->event_type ? $row->event_type : 'other'), 'cal-status-' . $row->status),
                'extendedProps' => array(
                    'source' => 'event',
                    'eventId' => (int) $row->id,
                    'eventNumber' => $event_number,
                    'eventName' => $row->event_name,
                    'eventType' => $row->event_type ? $row->event_type : 'other',
                    'venue' => $row->venue ? $row->venue : '-',
                    'organizerName' => $row->organizer_name,
                    'organizerEmail' => $row->organizer_email,
                    'status' => $row->status,
                    'amount' => number_format((float) $row->total_amount, 2),
                    'guests' => (int) $row->expected_guests,
                    'eventDate' => $event_date,
                    'startTime' => $row->start_time ? date('H:i', strtotime($row->start_time)) : '',
                    'endTime' => $row->end_time ? date('H:i', strtotime($row->end_time)) : '',
                    'linkedBooking' => !empty($row->booking_number) ? $row->booking_number : '',
                    'url' => site_url('events/view/' . $row->id)
                )
            );

            if ($end) {
                $entry['end'] = $end;
            }

            $entries[] = $entry;
        }

        return $entries;
    }
}
