<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all customers
     */
    public function get_all($status = null) {
        if ($status) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('customers')->result();
    }
    
    /**
     * Get all customers with user information (for dropdowns)
     * Customers table has email directly, so we just need to format the name
     */
    public function get_all_with_user_info() {
        $this->db->select('customers.*');
        $this->db->from('customers');
        // Check if status column exists before filtering
        $fields = $this->db->list_fields('customers');
        if (in_array('status', $fields)) {
            $this->db->where('customers.status', 'active');
        }
        $this->db->order_by('customers.first_name', 'ASC');
        $this->db->order_by('customers.last_name', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Get customer with user information by ID
     */
    public function get_customer_with_user_info($id) {
        $this->db->where('id', $id);
        return $this->db->get('customers')->row();
    }
    
    /**
     * Get customer by ID
     */
    public function get_customer($id) {
        $this->db->where('id', $id);
        return $this->db->get('customers')->row();
    }
    
    /**
     * Get customer by email
     */
    public function get_customer_by_email($email) {
        $this->db->where('LOWER(email)', strtolower(trim($email)));
        return $this->db->get('customers')->row();
    }
    
    /**
     * Create new customer
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('customers', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Update customer
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('customers', $data);
    }
    
    /**
     * Delete customer
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('customers');
    }
    
    /**
     * Check if email exists
     */
    public function email_exists($email, $exclude_id = null) {
        $this->db->where('LOWER(email)', strtolower(trim($email)));
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get('customers')->num_rows() > 0;
    }
    
    /**
     * Get customer bookings
     */
    public function get_customer_bookings($customer_id) {
        $customer = $this->get_customer($customer_id);
        if (!$customer) {
            return array();
        }
        
        $this->db->select('bookings.*, rooms.room_name, rooms.room_type, rooms.price');
        $this->db->from('bookings');
        $this->db->join('rooms', 'rooms.id = bookings.room_id', 'left');
        $this->db->where('bookings.guest_email', $customer->email);
        $this->db->order_by('bookings.created_at', 'DESC');
        return $this->db->get()->result();
    }
    
    /**
     * Search customers
     */
    public function search($search_term) {
        $this->db->group_start();
        $this->db->like('first_name', $search_term);
        $this->db->or_like('last_name', $search_term);
        $this->db->or_like('email', $search_term);
        $this->db->or_like('phone', $search_term);
        $this->db->group_end();
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('customers')->result();
    }
    
    /**
     * Get total customers count
     */
    public function get_total_count($status = null) {
        if ($status) {
            $this->db->where('status', $status);
        }
        return $this->db->count_all_results('customers');
    }
}

