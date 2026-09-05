<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Users extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Admin_model');
        $this->load->model('User_group_model');
        $this->load->library('form_validation');
        $this->load->library('upload');
    }
    
    /**
     * List all admin users
     */
    public function index() {
        // Require permission to view users (using manage_users as fallback)
        if (!$this->has_permission('view_users') && !$this->has_permission('manage_users')) {
            $this->require_permission('manage_users');
        }
        
        $data['title'] = 'Manage Admin Users';
        $data['users'] = $this->Admin_model->get_all();
        
        // Get groups for each user
        foreach ($data['users'] as $user) {
            $user->groups = $this->Admin_model->get_admin_groups($user->id);
        }
        
        $data['can_add'] = $this->has_permission('add_users');
        $data['can_edit'] = $this->has_permission('edit_users');
        $data['can_delete'] = $this->has_permission('delete_users');
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/users/index', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Add new admin user
     */
    public function add() {
        // Require permission to add users
        $this->require_permission('add_users');
        
        $data['title'] = 'Add New Admin User';
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[admins.username]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|is_unique[admins.email]');
            $this->form_validation->set_rules('groups[]', 'Groups', 'required');
            
            if ($this->form_validation->run() == TRUE) {
                $user_data = array(
                    'username' => $this->input->post('username'),
                    'password' => $this->input->post('password'),
                    'name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'status' => $this->input->post('status') ? 'active' : 'inactive'
                );
                
                // Handle avatar upload
                if (!empty($_FILES['avatar']['name'])) {
                    $upload_path = 'admin/img/avatars/';
                    $full_path = FCPATH . $upload_path;
                    
                    // Ensure upload directory exists
                    if (!is_dir($full_path)) {
                        mkdir($full_path, 0755, true);
                    }
                    
                    // Configure upload
                    $config['upload_path'] = $full_path;
                    $config['allowed_types'] = 'gif|jpg|jpeg|png|webp';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = true;
                    
                    $this->upload->initialize($config);
                    
                    if ($this->upload->do_upload('avatar')) {
                        $upload_data = $this->upload->data();
                        $user_data['avatar'] = $upload_path . $upload_data['file_name'];
                    } else {
                        $this->session->set_flashdata('error', 'Avatar upload failed: ' . $this->upload->display_errors('', ''));
                        redirect('users/add');
                        return;
                    }
                }
                
                $user_id = $this->Admin_model->create($user_data);
                
                // Assign groups
                if ($user_id && $this->input->post('groups')) {
                    $this->User_group_model->assign_groups_to_admin($user_id, $this->input->post('groups'));
                    $this->session->set_flashdata('success', 'Admin user created successfully');
                    // Audit: log creation (strip credentials before snapshotting).
                    $log_snapshot = $user_data;
                    unset($log_snapshot['password']);
                    if (isset($this->activity_log)) {
                        $this->activity_log->crud('users', 'create', 'admin_user', $user_id, 'Admin user created: ' . $user_data['name'] . ' (' . $user_data['username'] . ')', null, $log_snapshot);
                    }
                    redirect('users');
                } else {
                    $this->session->set_flashdata('error', 'Failed to create user');
                }
            }
        }
        
        $data['groups'] = $this->User_group_model->get_all('active');
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/users/add', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Edit admin user
     */
    public function edit($id) {
        // Require permission to edit users
        $this->require_permission('edit_users');
        
        $user = $this->Admin_model->get_admin($id);
        if (!$user) {
            show_404();
            return;
        }
        
        $data['title'] = 'Edit Admin User';
        $data['user'] = $user;
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('username', 'Username', 'required|trim');
            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
            $this->form_validation->set_rules('groups[]', 'Groups', 'required');
            
            // Check username uniqueness if changed
            if ($this->input->post('username') != $user->username) {
                $this->form_validation->set_rules('username', 'Username', 'is_unique[admins.username]');
            }
            
            // Check email uniqueness if changed
            if ($this->input->post('email') != $user->email) {
                $this->form_validation->set_rules('email', 'Email', 'is_unique[admins.email]');
            }
            
            // Password is optional on edit
            if ($this->input->post('password')) {
                $this->form_validation->set_rules('password', 'Password', 'min_length[6]');
            }
            
            if ($this->form_validation->run() == TRUE) {
                $user_data = array(
                    'username' => $this->input->post('username'),
                    'name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'status' => $this->input->post('status') ? 'active' : 'inactive'
                );
                
                // Only update password if provided
                if ($this->input->post('password')) {
                    $user_data['password'] = $this->input->post('password');
                }
                
                // Handle avatar removal
                if ($this->input->post('remove_avatar')) {
                    // Delete old avatar if exists
                    if (!empty($user->avatar) && file_exists(FCPATH . $user->avatar)) {
                        @unlink(FCPATH . $user->avatar);
                    }
                    $user_data['avatar'] = null;
                }
                // Handle avatar upload (only if not removing)
                else if (!empty($_FILES['avatar']['name'])) {
                    $upload_path = 'admin/img/avatars/';
                    $full_path = FCPATH . $upload_path;
                    
                    // Ensure upload directory exists
                    if (!is_dir($full_path)) {
                        mkdir($full_path, 0755, true);
                    }
                    
                    // Configure upload
                    $config['upload_path'] = $full_path;
                    $config['allowed_types'] = 'gif|jpg|jpeg|png|webp';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = true;
                    
                    $this->upload->initialize($config);
                    
                    if ($this->upload->do_upload('avatar')) {
                        $upload_data = $this->upload->data();
                        $avatar_path = $upload_path . $upload_data['file_name'];
                        
                        // Delete old avatar if exists
                        if (!empty($user->avatar) && file_exists(FCPATH . $user->avatar)) {
                            @unlink(FCPATH . $user->avatar);
                        }
                        
                        $user_data['avatar'] = $avatar_path;
                    } else {
                        $this->session->set_flashdata('error', 'Avatar upload failed: ' . $this->upload->display_errors('', ''));
                        redirect('users/edit/' . $id);
                        return;
                    }
                }
                
                if ($this->Admin_model->update($id, $user_data)) {
                    // Update groups
                    if ($this->input->post('groups')) {
                        $this->User_group_model->assign_groups_to_admin($id, $this->input->post('groups'));
                    }
                    
                    // Audit: log update with before/after snapshots (credentials stripped).
                    $log_old = array('name' => $user->name, 'username' => $user->username, 'email' => $user->email, 'status' => $user->status);
                    $log_new = $user_data;
                    unset($log_new['password']);
                    if (isset($this->activity_log)) {
                        $this->activity_log->crud('users', 'update', 'admin_user', $id, 'Admin user updated: ' . $user_data['name'] . ' (' . $user_data['username'] . ')', $log_old, $log_new);
                    }
                    
                    $this->session->set_flashdata('success', 'User updated successfully');
                    redirect('users');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update user');
                }
            }
        }
        
        $data['groups'] = $this->User_group_model->get_all('active');
        $data['user_groups'] = $this->Admin_model->get_admin_groups($id);
        
        // Get IDs for selected groups
        $data['selected_group_ids'] = array();
        foreach ($data['user_groups'] as $group) {
            $data['selected_group_ids'][] = $group->id;
        }
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/users/edit', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Delete admin user
     */
    public function delete($id) {
        // Require permission to delete users
        $this->require_permission('delete_users');
        
        // Prevent deleting yourself
        if ($id == $this->admin_id) {
            $this->session->set_flashdata('error', 'You cannot delete your own account');
            redirect('users');
            return;
        }
        
        if ($this->Admin_model->delete($id)) {
            $this->session->set_flashdata('success', 'User deleted successfully');
            if (isset($this->activity_log)) {
                $this->activity_log->crud('users', 'delete', 'admin_user', $id, 'Admin user deleted (ID: ' . $id . ')', array('id' => $id), null);
            }
        } else {
            $this->session->set_flashdata('error', 'Failed to delete user');
        }
        
        redirect('users');
    }

    /**
     * Delete multiple admin users
     */
    public function batch_delete() {
        $this->require_permission('delete_users');

        if ($this->input->method() !== 'post') {
            redirect('users');
            return;
        }

        $user_ids = $this->input->post('user_ids');
        if (empty($user_ids) || !is_array($user_ids)) {
            $this->session->set_flashdata('error', 'No users selected for deletion.');
            redirect('users');
            return;
        }

        $deleted = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($user_ids as $id) {
            $id = (int) $id;
            if ($id <= 0) {
                $skipped++;
                continue;
            }

            if ($id == $this->admin_id) {
                $skipped++;
                continue;
            }

            if ($this->Admin_model->delete($id)) {
                $deleted++;
            } else {
                $failed++;
            }
        }

        if ($deleted > 0) {
            $message = $deleted . ' user(s) deleted successfully.';
            if ($skipped > 0) {
                $message .= ' ' . $skipped . ' user(s) were skipped.';
            }
            if ($failed > 0) {
                $message .= ' ' . $failed . ' user(s) could not be deleted.';
            }
            $this->session->set_flashdata('success', $message);
            if (isset($this->activity_log)) {
                $this->activity_log->crud('users', 'batch_delete', 'admin_user', null, 'Batch deleted ' . $deleted . ' admin user(s)' . ($skipped ? ', skipped ' . $skipped : '') . ($failed ? ', failed ' . $failed : ''), array('ids' => $user_ids), null);
            }
        } else {
            $this->session->set_flashdata('error', 'No users were deleted. You cannot delete your own account.');
        }

        redirect('users');
    }
    
    /**
     * View user details
     */
    public function view($id) {
        // Require permission to view users
        if (!$this->has_permission('view_users') && !$this->has_permission('manage_users')) {
            $this->require_permission('manage_users');
        }
        
        $user = $this->Admin_model->get_admin($id);
        if (!$user) {
            show_404();
            return;
        }
        
        $data['title'] = 'User Details';
        $data['user'] = $user;
        $data['groups'] = $this->Admin_model->get_admin_groups($id);
        $data['roles'] = $this->Admin_model->get_admin_roles($id);
        $data['permissions'] = $this->Admin_model->get_admin_permissions($id);
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/users/view', $data);
        $this->load->view('admin/layout/footer');
    }
}

