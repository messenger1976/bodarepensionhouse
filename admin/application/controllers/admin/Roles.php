<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Roles extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Role_model');
        $this->load->model('Permission_model');
        $this->load->library('form_validation');
    }
    
    /**
     * List all roles
     * Requires: manage_roles permission
     */
    public function index() {
        $this->require_permission('manage_roles');
        
        $data['title'] = 'Manage Roles';
        
        // Explicitly get all roles without status filter
        $roles_result = $this->Role_model->get_all(null);
        
        // Ensure roles is always an array
        $data['roles'] = is_array($roles_result) ? $roles_result : array();
        
        // Debug: Log the count (remove in production if not needed)
        if (empty($data['roles'])) {
            log_message('debug', 'Roles index: No roles found in database');
        } else {
            log_message('debug', 'Roles index: Found ' . count($data['roles']) . ' roles');
        }
        
        // Get permissions for each role
        if (!empty($data['roles']) && is_array($data['roles'])) {
            foreach ($data['roles'] as $role) {
                if (isset($role->id)) {
                    $role->permissions = $this->Role_model->get_role_permissions($role->id);
                }
            }
        }
        
        $data['can_manage'] = $this->has_permission('manage_roles');
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/roles/index', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Add new role
     * Requires: manage_roles permission
     */
    public function add() {
        $this->require_permission('manage_roles');
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Role Name', 'required|is_unique[roles.name]');
            $this->form_validation->set_rules('slug', 'Role Slug', 'required|is_unique[roles.slug]|alpha_dash');
            $this->form_validation->set_rules('permissions[]', 'Permissions', 'required');
            
            if ($this->form_validation->run() == TRUE) {
                $role_data = array(
                    'name' => $this->input->post('name'),
                    'slug' => strtolower($this->input->post('slug')),
                    'description' => $this->input->post('description'),
                    'status' => $this->input->post('status') ? 'active' : 'inactive'
                );
                
                $role_id = $this->Role_model->create($role_data);
                
                if ($role_id) {
                    // Assign permissions if provided
                    if ($this->input->post('permissions')) {
                        $this->Role_model->assign_permissions_to_role($role_id, $this->input->post('permissions'));
                    }
                    $this->session->set_flashdata('success', 'Role created successfully');
                    redirect('roles');
                } else {
                    $this->session->set_flashdata('error', 'Failed to create role. Please try again.');
                }
            }
        }
        
        $data['title'] = 'Add New Role';
        $data['permissions'] = $this->Permission_model->get_by_module();
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/roles/add', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Edit role
     * Requires: manage_roles permission
     */
    public function edit($id) {
        $this->require_permission('manage_roles');
        
        $role = $this->Role_model->get($id);
        if (!$role) {
            show_404();
            return;
        }
        
        if ($this->input->post()) {
            // Check name uniqueness if changed
            if ($this->input->post('name') != $role->name) {
                $this->form_validation->set_rules('name', 'Role Name', 'required|is_unique[roles.name]');
            } else {
                $this->form_validation->set_rules('name', 'Role Name', 'required');
            }
            
            // Check slug uniqueness if changed
            if ($this->input->post('slug') != $role->slug) {
                $this->form_validation->set_rules('slug', 'Role Slug', 'required|is_unique[roles.slug]|alpha_dash');
            } else {
                $this->form_validation->set_rules('slug', 'Role Slug', 'required|alpha_dash');
            }
            
            $this->form_validation->set_rules('permissions[]', 'Permissions', 'required');
            
            if ($this->form_validation->run() == TRUE) {
                $role_data = array(
                    'name' => $this->input->post('name'),
                    'slug' => strtolower($this->input->post('slug')),
                    'description' => $this->input->post('description'),
                    'status' => $this->input->post('status') ? 'active' : 'inactive'
                );
                
                if ($this->Role_model->update($id, $role_data)) {
                    // Update permissions
                    if ($this->input->post('permissions')) {
                        $this->Role_model->assign_permissions_to_role($id, $this->input->post('permissions'));
                    }
                    
                    $this->session->set_flashdata('success', 'Role updated successfully');
                    redirect('roles');
                }
            }
        }
        
        $data['title'] = 'Edit Role';
        $data['role'] = $role;
        $data['permissions'] = $this->Permission_model->get_by_module();
        $data['role_permissions'] = $this->Role_model->get_role_permissions($id);
        
        // Get IDs for selected permissions
        $data['selected_permission_ids'] = array();
        foreach ($data['role_permissions'] as $permission) {
            $data['selected_permission_ids'][] = $permission->id;
        }
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/roles/edit', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Delete role
     * Requires: manage_roles permission
     */
    public function delete($id) {
        $this->require_permission('manage_roles');
        
        if ($this->Role_model->delete($id)) {
            $this->session->set_flashdata('success', 'Role deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete role');
        }
        
        redirect('roles');
    }
}

