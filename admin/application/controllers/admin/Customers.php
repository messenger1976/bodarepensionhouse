<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Customers extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Customer_model');
        $this->load->library('form_validation');
    }
    
    /**
     * List all customers
     */
    public function index() {
        // Require permission to view bookings (using same permission for now)
        if (!$this->has_permission('view_bookings')) {
            $this->require_permission('view_bookings');
        }
        
        $data['title'] = 'Manage Customers/Guests';
        
        // Handle search
        $search = $this->input->get('search');
        if ($search) {
            $data['customers'] = $this->Customer_model->search($search);
            $data['search_term'] = $search;
        } else {
            $data['customers'] = $this->Customer_model->get_all();
            $data['search_term'] = '';
        }
        
        $data['can_add'] = $this->has_permission('add_bookings');
        $data['can_edit'] = $this->has_permission('edit_bookings');
        $data['can_delete'] = $this->has_permission('delete_bookings');
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/customers/index', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Add new customer
     */
    public function add() {
        // Require permission to add bookings
        if (!$this->has_permission('add_bookings')) {
            $this->require_permission('add_bookings');
        }
        
        $data['title'] = 'Add New Customer/Guest';
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|is_unique[customers.email]');
            $this->form_validation->set_rules('phone', 'Phone', 'trim');
            $this->form_validation->set_rules('date_of_birth', 'Date of Birth', 'trim');
            $this->form_validation->set_rules('gender', 'Gender', 'trim');
            
            if ($this->form_validation->run() == TRUE) {
                $customer_data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'email' => $this->input->post('email'),
                    'phone' => $this->input->post('phone'),
                    'address' => $this->input->post('address'),
                    'city' => $this->input->post('city'),
                    'province' => $this->input->post('province'),
                    'postal_code' => $this->input->post('postal_code'),
                    'country' => $this->input->post('country') ? $this->input->post('country') : 'Philippines',
                    'date_of_birth' => $this->input->post('date_of_birth') ? $this->input->post('date_of_birth') : null,
                    'gender' => $this->input->post('gender') ? $this->input->post('gender') : null,
                    'nationality' => $this->input->post('nationality'),
                    'id_type' => $this->input->post('id_type'),
                    'id_number' => $this->input->post('id_number'),
                    'notes' => $this->input->post('notes'),
                    'status' => $this->input->post('status') ? 'active' : 'inactive',
                    'created_by' => $this->admin_id
                );
                
                $customer_id = $this->Customer_model->create($customer_data);
                
                if ($customer_id) {
                    $this->session->set_flashdata('success', 'Customer/guest registered successfully');
                    redirect('customers');
                } else {
                    $this->session->set_flashdata('error', 'Failed to register customer/guest');
                }
            }
        }
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/customers/add', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Edit customer
     */
    public function edit($id) {
        // Require permission to edit bookings
        if (!$this->has_permission('edit_bookings')) {
            $this->require_permission('edit_bookings');
        }
        
        $customer = $this->Customer_model->get_customer($id);
        if (!$customer) {
            show_404();
            return;
        }
        
        $data['title'] = 'Edit Customer/Guest';
        $data['customer'] = $customer;
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
            $this->form_validation->set_rules('phone', 'Phone', 'trim');
            $this->form_validation->set_rules('date_of_birth', 'Date of Birth', 'trim');
            $this->form_validation->set_rules('gender', 'Gender', 'trim');
            
            // Check email uniqueness if changed
            if ($this->input->post('email') != $customer->email) {
                $this->form_validation->set_rules('email', 'Email', 'is_unique[customers.email]');
            }
            
            if ($this->form_validation->run() == TRUE) {
                $customer_data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'email' => $this->input->post('email'),
                    'phone' => $this->input->post('phone'),
                    'address' => $this->input->post('address'),
                    'city' => $this->input->post('city'),
                    'province' => $this->input->post('province'),
                    'postal_code' => $this->input->post('postal_code'),
                    'country' => $this->input->post('country') ? $this->input->post('country') : 'Philippines',
                    'date_of_birth' => $this->input->post('date_of_birth') ? $this->input->post('date_of_birth') : null,
                    'gender' => $this->input->post('gender') ? $this->input->post('gender') : null,
                    'nationality' => $this->input->post('nationality'),
                    'id_type' => $this->input->post('id_type'),
                    'id_number' => $this->input->post('id_number'),
                    'notes' => $this->input->post('notes'),
                    'status' => $this->input->post('status') ? 'active' : 'inactive'
                );
                
                if ($this->Customer_model->update($id, $customer_data)) {
                    $this->session->set_flashdata('success', 'Customer/guest updated successfully');
                    redirect('customers');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update customer/guest');
                }
            }
        }
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/customers/edit', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Delete customer
     */
    public function delete($id) {
        // Require permission to delete bookings
        if (!$this->has_permission('delete_bookings')) {
            $this->require_permission('delete_bookings');
        }
        
        if ($this->Customer_model->delete($id)) {
            $this->session->set_flashdata('success', 'Customer/guest deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete customer/guest');
        }
        
        redirect('customers');
    }
    
    /**
     * View customer details
     */
    public function view($id) {
        // Require permission to view bookings
        if (!$this->has_permission('view_bookings')) {
            $this->require_permission('view_bookings');
        }
        
        $customer = $this->Customer_model->get_customer($id);
        if (!$customer) {
            show_404();
            return;
        }
        
        $data['title'] = 'Customer/Guest Details';
        $data['customer'] = $customer;
        $data['bookings'] = $this->Customer_model->get_customer_bookings($id);
        $data['can_edit'] = $this->has_permission('edit_bookings');
        $data['can_delete'] = $this->has_permission('delete_bookings');
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/customers/view', $data);
        $this->load->view('admin/layout/footer');
    }
}

