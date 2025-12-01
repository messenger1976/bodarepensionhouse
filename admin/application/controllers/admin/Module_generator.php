<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Module_generator extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper('file');
        $this->load->model('Permission_model');
        $this->load->model('Role_model');
        // Restrict access to super admin only
        if (!$this->is_super_admin()) {
            $this->session->set_flashdata('error', 'Access denied. Module Generator is only accessible to Super Administrators.');
            redirect('dashboard');
        }
    }
    
    /**
     * Display module generator form
     */
    public function index() {
        $data['title'] = 'Module Generator';
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/module_generator/index', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Process module generation
     */
    public function generate() {
        if (!$this->input->post()) {
            redirect('module_generator');
        }
        
        // Get form data
        $module_name_singular = ucfirst($this->input->post('module_name_singular'));
        $module_name_plural = $this->input->post('module_name_plural') ?: $module_name_singular . 's';
        $table_name = strtolower($this->input->post('table_name'));
        $primary_key = $this->input->post('primary_key') ?: 'id';
        $fields = json_decode($this->input->post('fields_json'), true);
        
        // Validate
        if (empty($module_name_singular) || empty($table_name) || empty($fields)) {
            $this->session->set_flashdata('error', 'Please fill in all required fields');
            redirect('module_generator');
        }
        
        // Generate files
        $generated = array();
        $errors = array();
        
        // Generate Controller
        try {
            $controller_content = $this->generate_controller($module_name_singular, $module_name_plural, $table_name, $primary_key, $fields);
            $controller_path = APPPATH . 'controllers/admin/' . $module_name_singular . '.php';
            if (write_file($controller_path, $controller_content)) {
                $generated[] = 'Controller: ' . $module_name_singular . '.php';
            } else {
                $errors[] = 'Failed to create controller file';
            }
        } catch (Exception $e) {
            $errors[] = 'Controller error: ' . $e->getMessage();
        }
        
        // Generate Model
        try {
            $model_content = $this->generate_model($module_name_singular, $table_name, $primary_key, $fields);
            $model_path = APPPATH . 'models/' . $module_name_singular . '_model.php';
            if (write_file($model_path, $model_content)) {
                $generated[] = 'Model: ' . $module_name_singular . '_model.php';
            } else {
                $errors[] = 'Failed to create model file';
            }
        } catch (Exception $e) {
            $errors[] = 'Model error: ' . $e->getMessage();
        }
        
        // Create views directory
        $views_dir = APPPATH . 'views/admin/' . strtolower($module_name_singular);
        if (!is_dir($views_dir)) {
            mkdir($views_dir, 0755, true);
        }
        
        // Generate Views
        try {
            $index_view = $this->generate_view_index($module_name_singular, $module_name_plural, $table_name, $primary_key, $fields);
            write_file($views_dir . '/index.php', $index_view);
            $generated[] = 'View: index.php';
            
            $add_view = $this->generate_view_add($module_name_singular, $module_name_plural, $table_name, $fields);
            write_file($views_dir . '/add.php', $add_view);
            $generated[] = 'View: add.php';
            
            $edit_view = $this->generate_view_edit($module_name_singular, $module_name_plural, $table_name, $primary_key, $fields);
            write_file($views_dir . '/edit.php', $edit_view);
            $generated[] = 'View: edit.php';
            
            $view_view = $this->generate_view_view($module_name_singular, $module_name_plural, $fields);
            write_file($views_dir . '/view.php', $view_view);
            $generated[] = 'View: view.php';
        } catch (Exception $e) {
            $errors[] = 'Views error: ' . $e->getMessage();
        }
        
        // Generate SQL
        $sql_content = $this->generate_sql($table_name, $primary_key, $fields);
        
        // Automatically create permissions and assign to Super Admin
        $permission_prefix = strtolower($module_name_plural);
        $permissions_created = array();
        $permissions_assigned = array();
        
        try {
            $permissions_created = $this->create_module_permissions($module_name_singular, $permission_prefix);
            $permissions_assigned = $this->assign_permissions_to_super_admin($permissions_created);
        } catch (Exception $e) {
            $errors[] = 'Permission creation error: ' . $e->getMessage();
        }
        
        // Pass results to view
        $data['title'] = 'Module Generated';
        $data['module_name'] = $module_name_singular;
        $data['module_name_plural'] = $module_name_plural;
        $data['generated'] = $generated;
        $data['errors'] = $errors;
        $data['sql'] = $sql_content;
        $data['permissions_created'] = $permissions_created;
        $data['permissions_assigned'] = $permissions_assigned;
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/module_generator/result', $data);
        $this->load->view('admin/layout/footer');
    }
    
    /**
     * Generate Controller code
     */
    private function generate_controller($module_name, $module_name_plural, $table_name, $primary_key, $fields) {
        $module_lower = strtolower($module_name);
        $permission_prefix = strtolower($module_name_plural);
        
        $code = "<?php\n";
        $code .= "defined('BASEPATH') OR exit('No direct script access allowed');\n\n";
        $code .= "if (!class_exists('Admin_Controller', FALSE)) {\n";
        $code .= "    require_once(APPPATH.'core/Admin_Controller.php');\n";
        $code .= "}\n\n";
        $code .= "class {$module_name} extends Admin_Controller {\n\n";
        $code .= "    public function __construct() {\n";
        $code .= "        parent::__construct();\n";
        $code .= "        \$this->load->model('{$module_name}_model');\n";
        $code .= "        \$this->load->library('form_validation');\n";
        $code .= "    }\n\n";
        
        // Index method
        $code .= "    public function index() {\n";
        $code .= "        \$this->require_permission('view_{$permission_prefix}');\n\n";
        $code .= "        \$data['title'] = 'Manage {$module_name_plural}';\n";
        $code .= "        \$data['items'] = \$this->{$module_name}_model->get_all();\n";
        $code .= "        \$data['can_add'] = \$this->has_permission('add_{$permission_prefix}');\n";
        $code .= "        \$data['can_edit'] = \$this->has_permission('edit_{$permission_prefix}');\n";
        $code .= "        \$data['can_delete'] = \$this->has_permission('delete_{$permission_prefix}');\n\n";
        $code .= "        \$this->load->view('admin/layout/header', \$data);\n";
        $code .= "        \$this->load->view('admin/{$module_lower}/index', \$data);\n";
        $code .= "        \$this->load->view('admin/layout/footer');\n";
        $code .= "    }\n\n";
        
        // Add method
        $code .= "    public function add() {\n";
        $code .= "        \$this->require_permission('add_{$permission_prefix}');\n\n";
        $code .= "        \$data['title'] = 'Add New {$module_name}';\n\n";
        $code .= "        if (\$this->input->post()) {\n";
        
        // Validation rules
        foreach ($fields as $field) {
            if (!empty($field['name']) && isset($field['required']) && $field['required']) {
                $label = !empty($field['label']) ? $field['label'] : ucfirst($field['name']);
                $rules = "required";
                if (!empty($field['validation'])) {
                    $rules .= "|" . $field['validation'];
                }
                $code .= "            \$this->form_validation->set_rules('{$field['name']}', '{$label}', '{$rules}');\n";
            }
        }
        
        $code .= "\n            if (\$this->form_validation->run() == TRUE) {\n";
        $code .= "                \$data_array = array(\n";
        
        foreach ($fields as $field) {
            if (!empty($field['name']) && isset($field['editable']) && $field['editable']) {
                if ($field['name'] != $primary_key) {
                    $code .= "                    '{$field['name']}' => \$this->input->post('{$field['name']}'),\n";
                }
            }
        }
        
        $code .= "                );\n\n";
        $code .= "                if (\$this->{$module_name}_model->create(\$data_array)) {\n";
        $code .= "                    \$this->session->set_flashdata('success', '{$module_name} added successfully');\n";
        $code .= "                    redirect('{$module_lower}');\n";
        $code .= "                } else {\n";
        $code .= "                    \$this->session->set_flashdata('error', 'Failed to add {$module_name}');\n";
        $code .= "                }\n";
        $code .= "            }\n";
        $code .= "        }\n\n";
        $code .= "        \$this->load->view('admin/layout/header', \$data);\n";
        $code .= "        \$this->load->view('admin/{$module_lower}/add', \$data);\n";
        $code .= "        \$this->load->view('admin/layout/footer');\n";
        $code .= "    }\n\n";
        
        // Edit method
        $code .= "    public function edit(\$id) {\n";
        $code .= "        \$this->require_permission('edit_{$permission_prefix}');\n\n";
        $code .= "        \$data['title'] = 'Edit {$module_name}';\n";
        $code .= "        \$data['item'] = \$this->{$module_name}_model->get(\$id);\n\n";
        $code .= "        if (!\$data['item']) {\n";
        $code .= "            show_404();\n";
        $code .= "            return;\n";
        $code .= "        }\n\n";
        $code .= "        if (\$this->input->post()) {\n";
        
        // Validation rules for edit
        foreach ($fields as $field) {
            if (!empty($field['name']) && isset($field['required']) && $field['required']) {
                $label = !empty($field['label']) ? $field['label'] : ucfirst($field['name']);
                $rules = "required";
                if (!empty($field['validation'])) {
                    $rules .= "|" . $field['validation'];
                }
                $code .= "            \$this->form_validation->set_rules('{$field['name']}', '{$label}', '{$rules}');\n";
            }
        }
        
        $code .= "\n            if (\$this->form_validation->run() == TRUE) {\n";
        $code .= "                \$data_array = array(\n";
        
        foreach ($fields as $field) {
            if (!empty($field['name']) && isset($field['editable']) && $field['editable']) {
                if ($field['name'] != $primary_key) {
                    $code .= "                    '{$field['name']}' => \$this->input->post('{$field['name']}'),\n";
                }
            }
        }
        
        $code .= "                );\n\n";
        $code .= "                if (\$this->{$module_name}_model->update(\$id, \$data_array)) {\n";
        $code .= "                    \$this->session->set_flashdata('success', '{$module_name} updated successfully');\n";
        $code .= "                    redirect('{$module_lower}');\n";
        $code .= "                } else {\n";
        $code .= "                    \$this->session->set_flashdata('error', 'Failed to update {$module_name}');\n";
        $code .= "                }\n";
        $code .= "            }\n";
        $code .= "        }\n\n";
        $code .= "        \$this->load->view('admin/layout/header', \$data);\n";
        $code .= "        \$this->load->view('admin/{$module_lower}/edit', \$data);\n";
        $code .= "        \$this->load->view('admin/layout/footer');\n";
        $code .= "    }\n\n";
        
        // Delete method
        $code .= "    public function delete(\$id) {\n";
        $code .= "        \$this->require_permission('delete_{$permission_prefix}');\n\n";
        $code .= "        if (\$this->{$module_name}_model->delete(\$id)) {\n";
        $code .= "            \$this->session->set_flashdata('success', '{$module_name} deleted successfully');\n";
        $code .= "        } else {\n";
        $code .= "            \$this->session->set_flashdata('error', 'Failed to delete {$module_name}');\n";
        $code .= "        }\n";
        $code .= "        redirect('{$module_lower}');\n";
        $code .= "    }\n\n";
        
        // View method
        $code .= "    public function view(\$id) {\n";
        $code .= "        \$this->require_permission('view_{$permission_prefix}');\n\n";
        $code .= "        \$item = \$this->{$module_name}_model->get(\$id);\n";
        $code .= "        if (!\$item) {\n";
        $code .= "            show_404();\n";
        $code .= "            return;\n";
        $code .= "        }\n\n";
        $code .= "        \$data['title'] = '{$module_name} Details';\n";
        $code .= "        \$data['item'] = \$item;\n";
        $code .= "        \$data['can_edit'] = \$this->has_permission('edit_{$permission_prefix}');\n";
        $code .= "        \$data['can_delete'] = \$this->has_permission('delete_{$permission_prefix}');\n\n";
        $code .= "        \$this->load->view('admin/layout/header', \$data);\n";
        $code .= "        \$this->load->view('admin/{$module_lower}/view', \$data);\n";
        $code .= "        \$this->load->view('admin/layout/footer');\n";
        $code .= "    }\n";
        $code .= "}\n";
        
        return $code;
    }
    
    /**
     * Generate Model code
     */
    private function generate_model($module_name, $table_name, $primary_key, $fields) {
        $code = "<?php\n";
        $code .= "defined('BASEPATH') OR exit('No direct script access allowed');\n\n";
        $code .= "class {$module_name}_model extends CI_Model {\n\n";
        $code .= "    public function __construct() {\n";
        $code .= "        parent::__construct();\n";
        $code .= "        \$this->load->database();\n";
        $code .= "    }\n\n";
        
        $code .= "    /**\n";
        $code .= "     * Get all records\n";
        $code .= "     */\n";
        $code .= "    public function get_all() {\n";
        $code .= "        \$this->db->order_by('{$primary_key}', 'DESC');\n";
        $code .= "        return \$this->db->get('{$table_name}')->result();\n";
        $code .= "    }\n\n";
        
        $code .= "    /**\n";
        $code .= "     * Get record by ID\n";
        $code .= "     */\n";
        $code .= "    public function get(\$id) {\n";
        $code .= "        \$this->db->where('{$primary_key}', \$id);\n";
        $code .= "        return \$this->db->get('{$table_name}')->row();\n";
        $code .= "    }\n\n";
        
        $code .= "    /**\n";
        $code .= "     * Create new record\n";
        $code .= "     */\n";
        $code .= "    public function create(\$data) {\n";
        $code .= "        \$this->db->insert('{$table_name}', \$data);\n";
        $code .= "        return \$this->db->insert_id();\n";
        $code .= "    }\n\n";
        
        $code .= "    /**\n";
        $code .= "     * Update record\n";
        $code .= "     */\n";
        $code .= "    public function update(\$id, \$data) {\n";
        $code .= "        \$this->db->where('{$primary_key}', \$id);\n";
        $code .= "        return \$this->db->update('{$table_name}', \$data);\n";
        $code .= "    }\n\n";
        
        $code .= "    /**\n";
        $code .= "     * Delete record\n";
        $code .= "     */\n";
        $code .= "    public function delete(\$id) {\n";
        $code .= "        \$this->db->where('{$primary_key}', \$id);\n";
        $code .= "        return \$this->db->delete('{$table_name}');\n";
        $code .= "    }\n\n";
        
        $code .= "    /**\n";
        $code .= "     * Get total count\n";
        $code .= "     */\n";
        $code .= "    public function count_all() {\n";
        $code .= "        return \$this->db->count_all('{$table_name}');\n";
        $code .= "    }\n";
        $code .= "}\n";
        
        return $code;
    }
    
    /**
     * Generate Index View
     */
    private function generate_view_index($module_name, $module_name_plural, $table_name, $primary_key, $fields) {
        $module_lower = strtolower($module_name);
        $list_fields = array_filter($fields, function($f) { 
            return !empty($f['name']) && isset($f['show_in_list']) && $f['show_in_list']; 
        });
        
        $code = "<div class=\"content-card\">\n";
        $code .= "    <div class=\"d-flex justify-content-between align-items-center mb-4\">\n";
        $code .= "        <h5 class=\"mb-0\"><i class=\"bi bi-list-ul\"></i> Manage {$module_name_plural}</h5>\n";
        $code .= "        <div>\n";
        $code .= "            <?php if (isset(\$can_add) && \$can_add): ?>\n";
        $code .= "            <a href=\"<?php echo base_url('{$module_lower}/add'); ?>\" class=\"btn btn-primary\">\n";
        $code .= "                <i class=\"bi bi-plus-circle\"></i> Add New {$module_name}\n";
        $code .= "            </a>\n";
        $code .= "            <?php endif; ?>\n";
        $code .= "        </div>\n";
        $code .= "    </div>\n\n";
        
        $code .= "    <?php if (\$this->session->flashdata('success')): ?>\n";
        $code .= "        <div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">\n";
        $code .= "            <?php echo \$this->session->flashdata('success'); ?>\n";
        $code .= "            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; ?>\n\n";
        
        $code .= "    <?php if (\$this->session->flashdata('error')): ?>\n";
        $code .= "        <div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">\n";
        $code .= "            <?php echo \$this->session->flashdata('error'); ?>\n";
        $code .= "            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; ?>\n\n";
        
        $code .= "    <div class=\"table-responsive\">\n";
        $code .= "        <table class=\"table table-hover\">\n";
        $code .= "            <thead>\n";
        $code .= "                <tr>\n";
        $code .= "                    <th>ID</th>\n";
        
        foreach ($list_fields as $field) {
            $label = !empty($field['label']) ? $field['label'] : ucfirst($field['name']);
            $code .= "                    <th>" . htmlspecialchars($label) . "</th>\n";
        }
        
        $code .= "                    <th>Actions</th>\n";
        $code .= "                </tr>\n";
        $code .= "            </thead>\n";
        $code .= "            <tbody>\n";
        $code .= "                <?php if (!empty(\$items)): ?>\n";
        $code .= "                    <?php foreach (\$items as \$item): ?>\n";
        $code .= "                        <tr>\n";
        $code .= "                            <td><?php echo \$item->{$primary_key}; ?></td>\n";
        
        foreach ($list_fields as $field) {
            $field_name = $field['name'];
            if ($field['type'] == 'checkbox' || (isset($field['db_type']) && strpos(strtolower($field['db_type']), 'boolean') !== false)) {
                $code .= "                            <td><?php echo \$item->{$field_name} ? 'Yes' : 'No'; ?></td>\n";
            } elseif ($field['type'] == 'date' || $field['type'] == 'datetime') {
                $code .= "                            <td><?php echo \$item->{$field_name} ? date('Y-m-d H:i', strtotime(\$item->{$field_name})) : '-'; ?></td>\n";
            } else {
                $code .= "                            <td><?php echo htmlspecialchars(\$item->{$field_name}); ?></td>\n";
            }
        }
        
        $code .= "                            <td>\n";
        $code .= "                                <?php if (isset(\$can_edit) && \$can_edit): ?>\n";
        $code .= "                                <a href=\"<?php echo base_url('{$module_lower}/edit/' . \$item->{$primary_key}); ?>\" class=\"btn btn-sm btn-warning\" title=\"Edit\">\n";
        $code .= "                                    <i class=\"bi bi-pencil\"></i>\n";
        $code .= "                                </a>\n";
        $code .= "                                <?php endif; ?>\n";
        $code .= "                                <?php if (isset(\$can_delete) && \$can_delete): ?>\n";
        $code .= "                                <a href=\"<?php echo base_url('{$module_lower}/delete/' . \$item->{$primary_key}); ?>\" class=\"btn btn-sm btn-danger\" onclick=\"return confirm('Are you sure you want to delete this {$module_name}?');\" title=\"Delete\">\n";
        $code .= "                                    <i class=\"bi bi-trash\"></i>\n";
        $code .= "                                </a>\n";
        $code .= "                                <?php endif; ?>\n";
        $code .= "                                <a href=\"<?php echo base_url('{$module_lower}/view/' . \$item->{$primary_key}); ?>\" class=\"btn btn-sm btn-info\" title=\"View\">\n";
        $code .= "                                    <i class=\"bi bi-eye\"></i>\n";
        $code .= "                                </a>\n";
        $code .= "                            </td>\n";
        $code .= "                        </tr>\n";
        $code .= "                    <?php endforeach; ?>\n";
        $code .= "                <?php else: ?>\n";
        $code .= "                    <tr>\n";
        $colspan = count($list_fields) + 2;
        $code .= "                        <td colspan=\"{$colspan}\" class=\"text-center text-muted\">No {$module_name_plural} found</td>\n";
        $code .= "                    </tr>\n";
        $code .= "                <?php endif; ?>\n";
        $code .= "            </tbody>\n";
        $code .= "        </table>\n";
        $code .= "    </div>\n";
        $code .= "</div>\n";
        
        return $code;
    }
    
    /**
     * Generate Add View
     */
    private function generate_view_add($module_name, $module_name_plural, $table_name, $fields) {
        $module_lower = strtolower($module_name);
        $editable_fields = array_filter($fields, function($f) { 
            return !empty($f['name']) && isset($f['editable']) && $f['editable'] && $f['name'] != 'id' && $f['type'] != 'hidden'; 
        });
        
        $code = "<div class=\"content-card\">\n";
        $code .= "    <div class=\"d-flex justify-content-between align-items-center mb-4\">\n";
        $code .= "        <h5 class=\"mb-0\"><i class=\"bi bi-plus-circle\"></i> Add New {$module_name}</h5>\n";
        $code .= "        <a href=\"<?php echo base_url('{$module_lower}'); ?>\" class=\"btn btn-secondary\">\n";
        $code .= "            <i class=\"bi bi-arrow-left\"></i> Back\n";
        $code .= "        </a>\n";
        $code .= "    </div>\n\n";
        
        $code .= "    <?php if (validation_errors()): ?>\n";
        $code .= "        <div class=\"alert alert-danger\">\n";
        $code .= "            <?php echo validation_errors(); ?>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; ?>\n\n";
        
        $code .= "    <?php if (\$this->session->flashdata('error')): ?>\n";
        $code .= "        <div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">\n";
        $code .= "            <?php echo \$this->session->flashdata('error'); ?>\n";
        $code .= "            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; ?>\n\n";
        
        $code .= "    <?php echo form_open('{$module_lower}/add', array('id' => '{$module_lower}-form')); ?>\n";
        $code .= "        <div class=\"row\">\n";
        
        $col_count = 0;
        foreach ($editable_fields as $field) {
            if ($col_count % 2 == 0 && $col_count > 0) {
                $code .= "        </div>\n        <div class=\"row\">\n";
            }
            
            $label = !empty($field['label']) ? $field['label'] : ucfirst($field['name']);
            $required = isset($field['required']) && $field['required'] ? ' *' : '';
            $required_attr = isset($field['required']) && $field['required'] ? ' required' : '';
            
            $code .= "            <div class=\"col-md-6 mb-3\">\n";
            $code .= "                <label for=\"{$field['name']}\" class=\"form-label\">{$label}{$required}</label>\n";
            
            // Generate input based on type
            if ($field['type'] == 'textarea') {
                $code .= "                <textarea class=\"form-control\" id=\"{$field['name']}\" name=\"{$field['name']}\" rows=\"4\"{$required_attr}><?php echo set_value('{$field['name']}'); ?></textarea>\n";
            } elseif ($field['type'] == 'select') {
                $code .= "                <select class=\"form-control\" id=\"{$field['name']}\" name=\"{$field['name']}\"{$required_attr}>\n";
                $code .= "                    <option value=\"\">Select {$label}</option>\n";
                // You can customize this to add options
                $code .= "                </select>\n";
            } elseif ($field['type'] == 'checkbox') {
                $code .= "                <div class=\"form-check\">\n";
                $code .= "                    <input type=\"checkbox\" class=\"form-check-input\" id=\"{$field['name']}\" name=\"{$field['name']}\" value=\"1\" <?php echo set_checkbox('{$field['name']}', '1'); ?>>\n";
                $code .= "                    <label class=\"form-check-label\" for=\"{$field['name']}\">{$label}</label>\n";
                $code .= "                </div>\n";
            } else {
                $input_type = in_array($field['type'], ['number', 'email', 'date', 'datetime', 'password']) ? $field['type'] : 'text';
                $code .= "                <input type=\"{$input_type}\" class=\"form-control\" id=\"{$field['name']}\" name=\"{$field['name']}\" value=\"<?php echo set_value('{$field['name']}'); ?>\"{$required_attr}>\n";
            }
            
            $code .= "            </div>\n";
            $col_count++;
        }
        
        $code .= "        </div>\n\n";
        $code .= "        <div class=\"d-grid gap-2 d-md-flex justify-content-md-end\">\n";
        $code .= "            <a href=\"<?php echo base_url('{$module_lower}'); ?>\" class=\"btn btn-secondary\">Cancel</a>\n";
        $code .= "            <button type=\"submit\" class=\"btn btn-primary\">Add {$module_name}</button>\n";
        $code .= "        </div>\n";
        $code .= "    <?php echo form_close(); ?>\n";
        $code .= "</div>\n";
        
        return $code;
    }
    
    /**
     * Generate Edit View (similar to Add but with pre-filled values)
     */
    private function generate_view_edit($module_name, $module_name_plural, $table_name, $primary_key, $fields) {
        $module_lower = strtolower($module_name);
        $editable_fields = array_filter($fields, function($f) { 
            return !empty($f['name']) && isset($f['editable']) && $f['editable'] && $f['name'] != 'id' && $f['type'] != 'hidden'; 
        });
        
        $code = "<div class=\"content-card\">\n";
        $code .= "    <div class=\"d-flex justify-content-between align-items-center mb-4\">\n";
        $code .= "        <h5 class=\"mb-0\"><i class=\"bi bi-pencil\"></i> Edit {$module_name}</h5>\n";
        $code .= "        <a href=\"<?php echo base_url('{$module_lower}'); ?>\" class=\"btn btn-secondary\">\n";
        $code .= "            <i class=\"bi bi-arrow-left\"></i> Back\n";
        $code .= "        </a>\n";
        $code .= "    </div>\n\n";
        
        $code .= "    <?php if (validation_errors()): ?>\n";
        $code .= "        <div class=\"alert alert-danger\">\n";
        $code .= "            <?php echo validation_errors(); ?>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; ?>\n\n";
        
        $code .= "    <?php if (\$this->session->flashdata('error')): ?>\n";
        $code .= "        <div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">\n";
        $code .= "            <?php echo \$this->session->flashdata('error'); ?>\n";
        $code .= "            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; ?>\n\n";
        
        $code .= "    <?php echo form_open('{$module_lower}/edit/' . \$item->{$primary_key}, array('id' => '{$module_lower}-form')); ?>\n";
        $code .= "        <div class=\"row\">\n";
        
        $col_count = 0;
        foreach ($editable_fields as $field) {
            if ($col_count % 2 == 0 && $col_count > 0) {
                $code .= "        </div>\n        <div class=\"row\">\n";
            }
            
            $label = !empty($field['label']) ? $field['label'] : ucfirst($field['name']);
            $required = isset($field['required']) && $field['required'] ? ' *' : '';
            $required_attr = isset($field['required']) && $field['required'] ? ' required' : '';
            $field_name = $field['name'];
            
            $code .= "            <div class=\"col-md-6 mb-3\">\n";
            $code .= "                <label for=\"{$field_name}\" class=\"form-label\">{$label}{$required}</label>\n";
            
            // Generate input based on type
            if ($field['type'] == 'textarea') {
                $code .= "                <textarea class=\"form-control\" id=\"{$field_name}\" name=\"{$field_name}\" rows=\"4\"{$required_attr}><?php echo set_value('{$field_name}', isset(\$item->{$field_name}) ? \$item->{$field_name} : ''); ?></textarea>\n";
            } elseif ($field['type'] == 'select') {
                $code .= "                <select class=\"form-control\" id=\"{$field_name}\" name=\"{$field_name}\"{$required_attr}>\n";
                $code .= "                    <option value=\"\">Select {$label}</option>\n";
                $code .= "                    <?php \$selected = set_value('{$field_name}', isset(\$item->{$field_name}) ? \$item->{$field_name} : ''); ?>\n";
                $code .= "                    <!-- Add your options here -->\n";
                $code .= "                </select>\n";
            } elseif ($field['type'] == 'checkbox') {
                $code .= "                <div class=\"form-check\">\n";
                $code .= "                    <?php \$checked = set_checkbox('{$field_name}', '1', isset(\$item->{$field_name}) && \$item->{$field_name}); ?>\n";
                $code .= "                    <input type=\"checkbox\" class=\"form-check-input\" id=\"{$field_name}\" name=\"{$field_name}\" value=\"1\" <?php echo \$checked ? 'checked' : ''; ?>>\n";
                $code .= "                    <label class=\"form-check-label\" for=\"{$field_name}\">{$label}</label>\n";
                $code .= "                </div>\n";
            } else {
                $input_type = in_array($field['type'], ['number', 'email', 'date', 'datetime', 'password']) ? $field['type'] : 'text';
                $code .= "                <input type=\"{$input_type}\" class=\"form-control\" id=\"{$field_name}\" name=\"{$field_name}\" value=\"<?php echo set_value('{$field_name}', isset(\$item->{$field_name}) ? \$item->{$field_name} : ''); ?>\"{$required_attr}>\n";
            }
            
            $code .= "            </div>\n";
            $col_count++;
        }
        
        $code .= "        </div>\n\n";
        $code .= "        <div class=\"d-grid gap-2 d-md-flex justify-content-md-end\">\n";
        $code .= "            <a href=\"<?php echo base_url('{$module_lower}'); ?>\" class=\"btn btn-secondary\">Cancel</a>\n";
        $code .= "            <button type=\"submit\" class=\"btn btn-primary\">Update {$module_name}</button>\n";
        $code .= "        </div>\n";
        $code .= "    <?php echo form_close(); ?>\n";
        $code .= "</div>\n";
        
        return $code;
    }
    
    /**
     * Generate View (Detail) View
     */
    private function generate_view_view($module_name, $module_name_plural, $fields) {
        $module_lower = strtolower($module_name);
        
        $code = "<div class=\"content-card\">\n";
        $code .= "    <div class=\"d-flex justify-content-between align-items-center mb-4\">\n";
        $code .= "        <h5 class=\"mb-0\"><i class=\"bi bi-eye\"></i> {$module_name} Details</h5>\n";
        $code .= "        <div>\n";
        $code .= "            <?php if (isset(\$can_edit) && \$can_edit): ?>\n";
        $code .= "            <a href=\"<?php echo base_url('{$module_lower}/edit/' . \$item->id); ?>\" class=\"btn btn-warning me-2\">\n";
        $code .= "                <i class=\"bi bi-pencil\"></i> Edit\n";
        $code .= "            </a>\n";
        $code .= "            <?php endif; ?>\n";
        $code .= "            <a href=\"<?php echo base_url('{$module_lower}'); ?>\" class=\"btn btn-secondary\">\n";
        $code .= "                <i class=\"bi bi-arrow-left\"></i> Back\n";
        $code .= "            </a>\n";
        $code .= "        </div>\n";
        $code .= "    </div>\n\n";
        
        $code .= "    <div class=\"row\">\n";
        
        foreach ($fields as $field) {
            if (empty($field['name']) || $field['name'] == 'id') continue;
            
            $label = !empty($field['label']) ? $field['label'] : ucfirst($field['name']);
            $field_name = $field['name'];
            
            $code .= "        <div class=\"col-md-6 mb-3\">\n";
            $code .= "            <label class=\"form-label fw-bold\">{$label}</label>\n";
            $code .= "            <div class=\"form-control-plaintext\">\n";
            
            if ($field['type'] == 'checkbox' || (isset($field['db_type']) && strpos(strtolower($field['db_type']), 'boolean') !== false)) {
                $code .= "                <?php echo isset(\$item->{$field_name}) && \$item->{$field_name} ? 'Yes' : 'No'; ?>\n";
            } elseif ($field['type'] == 'date') {
                $code .= "                <?php echo isset(\$item->{$field_name}) && \$item->{$field_name} ? date('Y-m-d', strtotime(\$item->{$field_name})) : '-'; ?>\n";
            } elseif ($field['type'] == 'datetime') {
                $code .= "                <?php echo isset(\$item->{$field_name}) && \$item->{$field_name} ? date('Y-m-d H:i:s', strtotime(\$item->{$field_name})) : '-'; ?>\n";
            } else {
                $code .= "                <?php echo isset(\$item->{$field_name}) ? htmlspecialchars(\$item->{$field_name}) : '-'; ?>\n";
            }
            
            $code .= "            </div>\n";
            $code .= "        </div>\n";
        }
        
        $code .= "    </div>\n";
        $code .= "</div>\n";
        
        return $code;
    }
    
    /**
     * Generate SQL CREATE TABLE statement
     */
    private function generate_sql($table_name, $primary_key, $fields) {
        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (\n";
        $sql .= "  `{$primary_key}` INT(11) NOT NULL AUTO_INCREMENT,\n";
        
        foreach ($fields as $field) {
            if (empty($field['name']) || $field['name'] == $primary_key) continue;
            
            $field_name = $field['name'];
            $db_type = !empty($field['db_type']) ? $field['db_type'] : 'VARCHAR(255)';
            $null = (isset($field['required']) && $field['required']) ? 'NOT NULL' : 'NULL';
            
            $sql .= "  `{$field_name}` {$db_type} {$null},\n";
        }
        
        $sql .= "  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        $sql .= "  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
        $sql .= "  PRIMARY KEY (`{$primary_key}`)\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n";
        
        return $sql;
    }
    
    /**
     * Create permissions for the module
     * Returns array of created permission IDs
     */
    private function create_module_permissions($module_name, $permission_prefix) {
        $permissions_created = array();
        $module_lower = strtolower($module_name);
        
        // Define the 4 standard permissions
        $permission_definitions = array(
            array(
                'name' => 'View ' . $module_name . 's',
                'slug' => 'view_' . $permission_prefix,
                'description' => 'Permission to view list of ' . strtolower($module_name) . 's'
            ),
            array(
                'name' => 'Add ' . $module_name . 's',
                'slug' => 'add_' . $permission_prefix,
                'description' => 'Permission to create new ' . strtolower($module_name) . 's'
            ),
            array(
                'name' => 'Edit ' . $module_name . 's',
                'slug' => 'edit_' . $permission_prefix,
                'description' => 'Permission to edit existing ' . strtolower($module_name) . 's'
            ),
            array(
                'name' => 'Delete ' . $module_name . 's',
                'slug' => 'delete_' . $permission_prefix,
                'description' => 'Permission to delete ' . strtolower($module_name) . 's'
            )
        );
        
        foreach ($permission_definitions as $perm_def) {
            // Check if permission already exists
            $existing = $this->Permission_model->get_by_slug($perm_def['slug']);
            
            if ($existing) {
                // Permission already exists, use existing ID
                $permissions_created[] = array(
                    'id' => $existing->id,
                    'name' => $existing->name,
                    'slug' => $existing->slug,
                    'status' => 'existing'
                );
            } else {
                // Create new permission
                $permission_data = array(
                    'name' => $perm_def['name'],
                    'slug' => $perm_def['slug'],
                    'description' => $perm_def['description'],
                    'module' => $module_lower
                );
                
                $permission_id = $this->Permission_model->create($permission_data);
                
                if ($permission_id) {
                    $permissions_created[] = array(
                        'id' => $permission_id,
                        'name' => $perm_def['name'],
                        'slug' => $perm_def['slug'],
                        'status' => 'created'
                    );
                }
            }
        }
        
        return $permissions_created;
    }
    
    /**
     * Assign permissions to Super Admin role
     * Returns array of assigned permissions
     */
    private function assign_permissions_to_super_admin($permissions) {
        $assigned = array();
        
        // Get Super Admin role ID
        $super_admin_role_id = $this->Role_model->get_super_admin_role_id();
        
        if (!$super_admin_role_id) {
            return $assigned; // No Super Admin role found
        }
        
        foreach ($permissions as $permission) {
            $permission_id = $permission['id'];
            
            // Check if already assigned
            if ($this->Role_model->has_permission($super_admin_role_id, $permission['slug'])) {
                $assigned[] = array(
                    'permission' => $permission['name'],
                    'status' => 'already_assigned'
                );
            } else {
                // Assign permission to Super Admin
                $result = $this->Role_model->add_permission_to_role($super_admin_role_id, $permission_id);
                
                if ($result !== false) {
                    $assigned[] = array(
                        'permission' => $permission['name'],
                        'status' => 'assigned'
                    );
                } else {
                    $assigned[] = array(
                        'permission' => $permission['name'],
                        'status' => 'failed'
                    );
                }
            }
        }
        
        return $assigned;
    }
}

