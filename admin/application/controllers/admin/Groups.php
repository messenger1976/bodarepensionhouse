<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Groups extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('User_group_model');
        $this->load->model('Role_model');
        $this->load->library('form_validation');
    }
    
    /**
     * List all user groups
     * Requires: manage_groups permission
     */
    public function index() {
        $this->require_permission('manage_groups');
        
        $data['title'] = 'Manage Groups';
        $data['groups'] = $this->User_group_model->get_all();
        
        // Get roles for each group
        foreach ($data['groups'] as $group) {
            $group->roles = $this->User_group_model->get_group_roles($group->id);
        }
        
        $data['can_manage'] = $this->has_permission('manage_groups');
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/groups/index', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Add new user group
     * Requires: manage_groups permission
     */
    public function add() {
        $this->require_permission('manage_groups');
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Group Name', 'required|is_unique[user_groups.name]');
            $this->form_validation->set_rules('roles[]', 'Roles', 'required');
            
            if ($this->form_validation->run() == TRUE) {
                $group_data = array(
                    'name' => $this->input->post('name'),
                    'description' => $this->input->post('description'),
                    'status' => $this->input->post('status') ? 'active' : 'inactive'
                );
                
                $group_id = $this->User_group_model->create($group_data);
                
                // Assign roles
                if ($group_id && $this->input->post('roles')) {
                    $this->User_group_model->assign_roles_to_group($group_id, $this->input->post('roles'));
                    $this->session->set_flashdata('success', 'Group created successfully');
                    redirect('groups');
                }
            }
        }
        
        $data['title'] = 'Add New Group';
        $data['roles'] = $this->Role_model->get_all('active');
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/groups/add', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Edit user group
     * Requires: manage_groups permission
     */
    public function edit($id) {
        $this->require_permission('manage_groups');
        
        $group = $this->User_group_model->get($id);
        if (!$group) {
            show_404();
            return;
        }
        
        if ($this->input->post()) {
            // Check name uniqueness if changed
            if ($this->input->post('name') != $group->name) {
                $this->form_validation->set_rules('name', 'Group Name', 'required|is_unique[user_groups.name]');
            } else {
                $this->form_validation->set_rules('name', 'Group Name', 'required');
            }
            $this->form_validation->set_rules('roles[]', 'Roles', 'required');
            
            if ($this->form_validation->run() == TRUE) {
                $group_data = array(
                    'name' => $this->input->post('name'),
                    'description' => $this->input->post('description'),
                    'status' => $this->input->post('status') ? 'active' : 'inactive'
                );
                
                if ($this->User_group_model->update($id, $group_data)) {
                    // Update roles
                    if ($this->input->post('roles')) {
                        $this->User_group_model->assign_roles_to_group($id, $this->input->post('roles'));
                    }
                    
                    $this->session->set_flashdata('success', 'Group updated successfully');
                    redirect('groups');
                }
            }
        }
        
        $data['title'] = 'Edit Group';
        $data['group'] = $group;
        $data['roles'] = $this->Role_model->get_all('active');
        $data['group_roles'] = $this->User_group_model->get_group_roles($id);
        
        // Get IDs for selected roles
        $data['selected_role_ids'] = array();
        foreach ($data['group_roles'] as $role) {
            $data['selected_role_ids'][] = $role->id;
        }
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/groups/edit', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Delete user group
     * Requires: manage_groups permission
     */
    public function delete($id) {
        $this->require_permission('manage_groups');
        
        if ($this->User_group_model->delete($id)) {
            $this->session->set_flashdata('success', 'Group deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete group');
        }
        
        redirect('groups');
    }
}

