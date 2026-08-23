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
}
