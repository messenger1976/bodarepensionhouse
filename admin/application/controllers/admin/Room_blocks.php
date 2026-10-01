<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

/**
 * Blocked dates: take some or all units of a room type off sale for a date range.
 */
class Room_blocks extends Admin_Controller {

    const MAX_BLOCK_NIGHTS = 730;

    public function __construct() {
        parent::__construct();
        $this->load->model('Room_block_model');
        $this->load->model('Room_model');
        $this->load->model('Booking_model');
    }

    public function index() {
        $this->require_permission('view_rooms');

        $scope = $this->input->get('scope');
        if (!in_array($scope, array('upcoming', 'past', 'all'), true)) {
            $scope = 'upcoming';
        }
        $room_id = (int)$this->input->get('room_id');

        $data['title'] = 'Blocked Dates';
        $data['scope'] = $scope;
        $data['room_id'] = $room_id;
        $data['rooms'] = $this->Room_model->get_all_rooms();
        $data['blocks'] = $this->Room_block_model->get_blocks($scope, $room_id ?: null);
        $data['table_ready'] = $this->Room_block_model->is_ready();
        $data['can_manage'] = $this->has_permission('edit_rooms');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/room_blocks/index', $data);
        $this->load->view('admin/layout/footer');
    }

    public function add() {
        $this->require_permission('edit_rooms');
        $this->require_table();

        $prefill_start = $this->valid_date($this->input->get('start')) ? $this->input->get('start') : date('Y-m-d');
        $block = (object) array(
            'id' => null,
            'room_id' => (int)$this->input->get('room_id'),
            'start_date' => $prefill_start,
            'end_date' => $prefill_start,
            'rooms_blocked' => null,
            'reason' => ''
        );

        if ($this->input->post()) {
            $input = $this->read_form(true);
            if (empty($input['errors'])) {
                $created = array();
                foreach ($input['rooms'] as $room) {
                    $row = array(
                        'room_id' => (int)$room->id,
                        'start_date' => $input['start_date'],
                        'end_date' => $input['end_date'],
                        'rooms_blocked' => $this->units_for_room($input['rooms_blocked'], $room),
                        'reason' => $input['reason'],
                        'created_by' => (int)$this->session->userdata('admin_id') ?: null
                    );
                    $id = $this->Room_block_model->create_block($row);
                    if ($id) {
                        $created[] = $id;
                        if (isset($this->activity_log)) {
                            $this->activity_log->crud('room_blocks', 'create', 'room_block', $id, 'Dates blocked: ' . $room->room_name . ' ' . $row['start_date'] . ' to ' . $row['end_date'], null, $row);
                        }
                    }
                }

                if ($created) {
                    $this->session->set_flashdata('success', count($created) === 1 ? 'Dates blocked successfully' : count($created) . ' room types blocked successfully');
                    $this->flash_booking_overlap($input['rooms'], $input['start_date'], $input['end_date']);
                    redirect('room_blocks');
                    return;
                }
                $input['errors'][] = 'Failed to save the block. Please try again.';
            }
            $data['errors'] = $input['errors'];
            $block = $input['block'];
        }

        $data['title'] = 'Block Dates';
        $data['block'] = $block;
        $data['rooms'] = $this->Room_model->get_all_rooms();
        $data['is_edit'] = false;

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/room_blocks/form', $data);
        $this->load->view('admin/layout/footer');
    }

    public function edit($id) {
        $this->require_permission('edit_rooms');
        $this->require_table();

        $existing = $this->Room_block_model->get_block($id);
        if (!$existing) {
            show_404();
            return;
        }
        $block = $existing;

        if ($this->input->post()) {
            $input = $this->read_form(false);
            if (empty($input['errors'])) {
                $room = $input['rooms'][0];
                $row = array(
                    'room_id' => (int)$room->id,
                    'start_date' => $input['start_date'],
                    'end_date' => $input['end_date'],
                    'rooms_blocked' => $this->units_for_room($input['rooms_blocked'], $room),
                    'reason' => $input['reason']
                );
                if ($this->Room_block_model->update_block($id, $row)) {
                    if (isset($this->activity_log)) {
                        $old = array('room_id' => $existing->room_id, 'start_date' => $existing->start_date, 'end_date' => $existing->end_date, 'rooms_blocked' => $existing->rooms_blocked, 'reason' => $existing->reason);
                        $this->activity_log->crud('room_blocks', 'update', 'room_block', $id, 'Blocked dates updated: ' . $room->room_name . ' ' . $row['start_date'] . ' to ' . $row['end_date'], $old, $row);
                    }
                    $this->session->set_flashdata('success', 'Blocked dates updated successfully');
                    $this->flash_booking_overlap($input['rooms'], $input['start_date'], $input['end_date']);
                    redirect('room_blocks');
                    return;
                }
                $input['errors'][] = 'Failed to update the block. Please try again.';
            }
            $data['errors'] = $input['errors'];
            $block = $input['block'];
            $block->id = $existing->id;
        }

        $data['title'] = 'Edit Blocked Dates';
        $data['block'] = $block;
        $data['rooms'] = $this->Room_model->get_all_rooms();
        $data['is_edit'] = true;

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/room_blocks/form', $data);
        $this->load->view('admin/layout/footer');
    }

    public function delete($id) {
        $this->require_permission('edit_rooms');
        $this->require_table();

        $block = $this->Room_block_model->get_block($id);
        if (!$block) {
            $this->session->set_flashdata('error', 'Block not found');
            redirect('room_blocks');
            return;
        }

        if ($this->Room_block_model->delete_block($id)) {
            $this->session->set_flashdata('success', 'Block removed. Those dates are bookable again.');
            if (isset($this->activity_log)) {
                $old = array('room_id' => $block->room_id, 'start_date' => $block->start_date, 'end_date' => $block->end_date, 'rooms_blocked' => $block->rooms_blocked, 'reason' => $block->reason);
                $this->activity_log->crud('room_blocks', 'delete', 'room_block', $id, 'Blocked dates removed: ' . $block->room_name . ' ' . $block->start_date . ' to ' . $block->end_date, $old, null);
            }
        } else {
            $this->session->set_flashdata('error', 'Failed to remove the block');
        }
        redirect('room_blocks');
    }

    private function require_table() {
        if (!$this->Room_block_model->is_ready()) {
            $this->session->set_flashdata('error', 'The room_blocks table is missing. Run admin/sql/create_room_blocks_table.sql first.');
            redirect('room_blocks');
            exit;
        }
    }

    /**
     * @return array start_date, end_date, rooms (room objects), rooms_blocked (int|null), reason, errors, block (for re-display)
     */
    private function read_form($allow_all_rooms) {
        $errors = array();
        $room_input = trim((string)$this->input->post('room_id'));
        $start_date = trim((string)$this->input->post('start_date'));
        $end_date = trim((string)$this->input->post('end_date'));
        $mode = $this->input->post('block_mode') === 'some' ? 'some' : 'all';
        $units = (int)$this->input->post('rooms_blocked');
        $reason = trim((string)$this->input->post('reason'));
        if (function_exists('mb_substr')) {
            $reason = mb_substr($reason, 0, 255);
        } else {
            $reason = substr($reason, 0, 255);
        }

        $rooms = array();
        if ($room_input === 'all' && $allow_all_rooms) {
            $rooms = $this->Room_model->get_active_rooms();
            if (!$rooms) {
                $errors[] = 'There are no active rooms to block.';
            }
        } else {
            $room = $this->Room_model->get_room((int)$room_input);
            if ($room) {
                $rooms[] = $room;
            } else {
                $errors[] = 'Please choose a room.';
            }
        }

        if (!$this->valid_date($start_date)) {
            $errors[] = 'Please enter a valid "From" date.';
        }
        if (!$this->valid_date($end_date)) {
            $errors[] = 'Please enter a valid "To" date.';
        }
        if (empty($errors)) {
            if ($end_date < $start_date) {
                $errors[] = 'The "To" date cannot be before the "From" date.';
            } else {
                $nights = (new DateTime($start_date))->diff(new DateTime($end_date))->days + 1;
                if ($nights > self::MAX_BLOCK_NIGHTS) {
                    $errors[] = 'A single block can cover at most ' . self::MAX_BLOCK_NIGHTS . ' nights.';
                }
            }
        }

        $rooms_blocked = null;
        if ($mode === 'some') {
            if ($units < 1) {
                $errors[] = 'Enter how many rooms to block (at least 1).';
            } elseif (count($rooms) === 1 && $units > (int)$rooms[0]->available_rooms) {
                $errors[] = $rooms[0]->room_name . ' only has ' . (int)$rooms[0]->available_rooms . ' room(s); you cannot block ' . $units . '.';
            }
            $rooms_blocked = $units;
        }

        return array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'rooms' => $rooms,
            'rooms_blocked' => $rooms_blocked,
            'reason' => $reason !== '' ? $reason : null,
            'errors' => $errors,
            'block' => (object) array(
                'id' => null,
                'room_id' => $room_input === 'all' ? 'all' : (int)$room_input,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'rooms_blocked' => $rooms_blocked,
                'reason' => $reason
            )
        );
    }

    /**
     * NULL (= every unit) when the request covers the whole room type, so a later
     * increase in available_rooms keeps the room fully blocked.
     */
    private function units_for_room($requested, $room) {
        if ($requested === null || $requested >= (int)$room->available_rooms) {
            return null;
        }
        return (int)$requested;
    }

    private function flash_booking_overlap($rooms, $start_date, $end_date) {
        $check_out = date('Y-m-d', strtotime($end_date . ' +1 day'));
        $names = array();
        $count = 0;
        foreach ($rooms as $room) {
            $conflicts = $this->Booking_model->get_conflicting_bookings($room->id, $start_date, $check_out);
            if ($conflicts) {
                $count += count($conflicts);
                $names[] = $room->room_name;
            }
        }
        if ($count) {
            $this->session->set_flashdata('warning', $count . ' existing booking(s) overlap these dates (' . implode(', ', $names) . '). Blocking does not cancel them; review them under Bookings.');
        }
    }

    private function valid_date($value) {
        if (!is_string($value) || $value === '') {
            return false;
        }
        $d = DateTime::createFromFormat('Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    }
}
