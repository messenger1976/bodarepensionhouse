<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Room date blocks (room_blocks table).
 *
 * start_date / end_date are the first and last blocked NIGHT (inclusive).
 * rooms_blocked NULL = every unit of the room type.
 */
class Room_block_model extends CI_Model {

    private $table = 'room_blocks';
    private $table_ready = null;

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Availability must keep working when code ships before the migration runs.
     */
    public function is_ready() {
        if ($this->table_ready === null) {
            $this->table_ready = $this->db->table_exists($this->table);
        }
        return $this->table_ready;
    }

    /**
     * @param string $scope 'upcoming' (ends today or later), 'past', or 'all'
     */
    public function get_blocks($scope = 'upcoming', $room_id = null) {
        if (!$this->is_ready()) {
            return array();
        }

        $today = date('Y-m-d');
        $this->db->select('room_blocks.*, rooms.room_name, rooms.room_type, rooms.room_code, rooms.available_rooms');
        $this->db->from($this->table);
        $this->db->join('rooms', 'rooms.id = room_blocks.room_id', 'left');
        if ($scope === 'upcoming') {
            $this->db->where('room_blocks.end_date >=', $today);
        } elseif ($scope === 'past') {
            $this->db->where('room_blocks.end_date <', $today);
        }
        if ($room_id) {
            $this->db->where('room_blocks.room_id', (int)$room_id);
        }
        $this->db->order_by('room_blocks.start_date', $scope === 'past' ? 'DESC' : 'ASC');
        $this->db->order_by('rooms.room_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_block($id) {
        if (!$this->is_ready()) {
            return null;
        }
        $this->db->select('room_blocks.*, rooms.room_name, rooms.room_type, rooms.available_rooms');
        $this->db->from($this->table);
        $this->db->join('rooms', 'rooms.id = room_blocks.room_id', 'left');
        $this->db->where('room_blocks.id', (int)$id);
        return $this->db->get()->row();
    }

    public function create_block($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update_block($id, $data) {
        $this->db->where('id', (int)$id);
        return $this->db->update($this->table, $data);
    }

    public function delete_block($id) {
        $this->db->where('id', (int)$id);
        return $this->db->delete($this->table);
    }

    /**
     * Blocks touching any night in [$first_night, $last_night].
     */
    public function get_overlapping_blocks($first_night, $last_night, $room_id = null) {
        if (!$this->is_ready()) {
            return array();
        }
        $this->db->where('start_date <=', $last_night);
        $this->db->where('end_date >=', $first_night);
        if ($room_id) {
            $this->db->where('room_id', (int)$room_id);
        }
        return $this->db->get($this->table)->result();
    }

    /**
     * Units blocked on each night of a stay, keyed by room id then date.
     *
     * @return array [room_id][Y-m-d] => array('units' => int, 'reasons' => string[])
     */
    public function get_blocked_map($first_night, $last_night, $room_id = null) {
        $map = array();
        $blocks = $this->get_overlapping_blocks($first_night, $last_night, $room_id);
        if (empty($blocks)) {
            return $map;
        }

        $this->load->model('Room_model');
        $totals = array();

        foreach ($blocks as $block) {
            $rid = (int)$block->room_id;
            if (!isset($totals[$rid])) {
                $room = $this->Room_model->get_room($rid);
                $totals[$rid] = $room ? max(1, (int)$room->available_rooms) : 1;
            }
            $units = $block->rooms_blocked === null ? $totals[$rid] : (int)$block->rooms_blocked;

            $cursor = new DateTime(max($block->start_date, $first_night));
            $end = new DateTime(min($block->end_date, $last_night));
            while ($cursor <= $end) {
                $d = $cursor->format('Y-m-d');
                if (!isset($map[$rid][$d])) {
                    $map[$rid][$d] = array('units' => 0, 'reasons' => array());
                }
                $map[$rid][$d]['units'] = min($totals[$rid], $map[$rid][$d]['units'] + $units);
                if (!empty($block->reason)) {
                    $map[$rid][$d]['reasons'][] = $block->reason;
                }
                $cursor->modify('+1 day');
            }
        }

        return $map;
    }

    /**
     * FullCalendar entries for the Hotel Calendar; [$start, $end) is FullCalendar's half-open range.
     */
    public function get_calendar_feed($start, $end, $room_id = null, $edit_links = false) {
        $this->load->helper('url');
        $last_night = date('Y-m-d', strtotime($end . ' -1 day'));
        if (!$this->is_ready() || $last_night < $start) {
            return array();
        }

        $this->db->select('room_blocks.*, rooms.room_name, rooms.room_code, rooms.room_type, rooms.available_rooms');
        $this->db->from($this->table);
        $this->db->join('rooms', 'rooms.id = room_blocks.room_id', 'left');
        $this->db->where('room_blocks.start_date <=', $last_night);
        $this->db->where('room_blocks.end_date >=', $start);
        if ($room_id) {
            $this->db->where('room_blocks.room_id', (int)$room_id);
        }
        $this->db->order_by('room_blocks.start_date', 'ASC');
        $rows = $this->db->get()->result();

        $entries = array();
        foreach ($rows as $row) {
            $total = max(1, (int)$row->available_rooms);
            $full = $row->rooms_blocked === null || (int)$row->rooms_blocked >= $total;
            $units_label = $full ? 'All ' . $total : (int)$row->rooms_blocked . ' of ' . $total;
            $room_name = $row->room_name ? $row->room_name : 'Room';
            $nights = (new DateTime($row->start_date))->diff(new DateTime($row->end_date))->days + 1;

            $entries[] = array(
                'id' => 'block-' . $row->id,
                'title' => 'Blocked · ' . $room_name . ($full ? '' : ' (' . $units_label . ')') . ($row->reason ? ' — ' . $row->reason : ''),
                'start' => $row->start_date,
                'end' => date('Y-m-d', strtotime($row->end_date . ' +1 day')),
                'allDay' => true,
                'classNames' => array('cal-block', $full ? 'cal-block-full' : 'cal-block-partial'),
                'backgroundColor' => $full ? '#1e293b' : '#475569',
                'borderColor' => '#0f172a',
                'textColor' => '#ffffff',
                'extendedProps' => array(
                    'source' => 'block',
                    'blockId' => (int)$row->id,
                    'roomName' => $room_name,
                    'roomCode' => $row->room_code ? $row->room_code : '-',
                    'roomType' => $row->room_type ? $row->room_type : '-',
                    'unitsLabel' => $units_label,
                    'fullyBlocked' => $full,
                    'reason' => $row->reason ? $row->reason : '',
                    'startDate' => $row->start_date,
                    'endDate' => $row->end_date,
                    'reopenDate' => date('Y-m-d', strtotime($row->end_date . ' +1 day')),
                    'nights' => $nights,
                    'url' => $edit_links ? site_url('room_blocks/edit/' . $row->id) : site_url('room_blocks?scope=all&room_id=' . (int)$row->room_id)
                )
            );
        }
        return $entries;
    }

    /**
     * Units blocked on a single night.
     */
    public function blocked_units_for_date($room_id, $date) {
        $date = date('Y-m-d', strtotime($date));
        $map = $this->get_blocked_map($date, $date, $room_id);
        return isset($map[(int)$room_id][$date]) ? $map[(int)$room_id][$date]['units'] : 0;
    }

    /**
     * Highest number of units blocked on any night of a stay
     * (check_in inclusive, check_out exclusive).
     */
    public function max_blocked_units_for_stay($room_id, $check_in, $check_out) {
        $check_in = date('Y-m-d', strtotime($check_in));
        $check_out = date('Y-m-d', strtotime($check_out));
        $last_night = date('Y-m-d', strtotime($check_out . ' -1 day'));
        if ($last_night < $check_in) {
            $last_night = $check_in;
        }

        $map = $this->get_blocked_map($check_in, $last_night, $room_id);
        if (empty($map[(int)$room_id])) {
            return 0;
        }

        $max = 0;
        foreach ($map[(int)$room_id] as $night) {
            $max = max($max, $night['units']);
        }
        return $max;
    }
}
