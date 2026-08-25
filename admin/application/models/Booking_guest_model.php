<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking_guest_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all named guests for a booking
     */
    public function get_booking_guests($booking_id) {
        if (!$this->db->table_exists('booking_guests')) {
            return array();
        }

        $this->db->where('booking_id', $booking_id);
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('booking_guests')->result();
    }

    /**
     * Create a single guest row
     */
    public function create_booking_guest($data) {
        if (!$this->db->table_exists('booking_guests')) {
            return false;
        }

        if (empty($data['booking_id']) || empty($data['full_name'])) {
            return false;
        }

        $row = array(
            'booking_id' => (int) $data['booking_id'],
            'full_name' => trim($data['full_name']),
            'age' => isset($data['age']) && $data['age'] !== '' && $data['age'] !== null
                ? (int) $data['age']
                : null,
            'gender' => !empty($data['gender']) ? $data['gender'] : null,
            'date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            'contact_no' => !empty($data['contact_no']) ? trim($data['contact_no']) : null,
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0
        );

        if ($row['gender'] !== null && !in_array($row['gender'], array('Male', 'Female', 'Other'), true)) {
            $row['gender'] = null;
        }

        return $this->db->insert('booking_guests', $row) ? $this->db->insert_id() : false;
    }

    /**
     * Replace all guests for a booking with the given list
     */
    public function replace_booking_guests($booking_id, $guests) {
        if (!$this->db->table_exists('booking_guests')) {
            return true;
        }

        $this->delete_booking_guests($booking_id);

        if (empty($guests) || !is_array($guests)) {
            return true;
        }

        $sort = 0;
        foreach ($guests as $guest) {
            if (empty($guest['full_name'])) {
                continue;
            }
            $guest['booking_id'] = $booking_id;
            $guest['sort_order'] = $sort++;
            $this->create_booking_guest($guest);
        }

        return true;
    }

    /**
     * Delete all guests for a booking
     */
    public function delete_booking_guests($booking_id) {
        if (!$this->db->table_exists('booking_guests')) {
            return true;
        }

        $this->db->where('booking_id', $booking_id);
        return $this->db->delete('booking_guests');
    }
}
