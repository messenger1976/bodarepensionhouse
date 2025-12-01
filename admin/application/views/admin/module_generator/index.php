<div class="content-card">
    <div class="mb-4">
        <h5 class="mb-0"><i class="bi bi-magic"></i> Module Generator</h5>
        <p class="text-muted mt-2">Generate complete CRUD modules (Controller, Model, Views) for your CodeIgniter application</p>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php echo form_open('module_generator/generate', array('id' => 'module-generator-form')); ?>
        
        <!-- Basic Module Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Module Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="module_name_singular" class="form-label">Module Name (Singular) *</label>
                        <input type="text" class="form-control" id="module_name_singular" name="module_name_singular" 
                               placeholder="e.g., Product, Category, Invoice" required>
                        <small class="form-text text-muted">Use PascalCase (e.g., Product, Category)</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="module_name_plural" class="form-label">Module Name (Plural)</label>
                        <input type="text" class="form-control" id="module_name_plural" name="module_name_plural" 
                               placeholder="Auto-generated if left empty">
                        <small class="form-text text-muted">Will auto-generate by adding 's' or 'es'</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="table_name" class="form-label">Database Table Name *</label>
                        <input type="text" class="form-control" id="table_name" name="table_name" 
                               placeholder="e.g., products, categories, invoices" required>
                        <small class="form-text text-muted">Lowercase with underscores</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="primary_key" class="form-label">Primary Key Column</label>
                        <input type="text" class="form-control" id="primary_key" name="primary_key" value="id">
                        <small class="form-text text-muted">Default: id</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fields Configuration -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Database Fields</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addField()">
                    <i class="bi bi-plus-circle"></i> Add Field
                </button>
            </div>
            <div class="card-body">
                <div id="fields-container">
                    <!-- Fields will be added here dynamically -->
                </div>
                <input type="hidden" name="fields_json" id="fields_json">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-gear"></i> Generate Module
            </button>
        </div>
    <?php echo form_close(); ?>
</div>

<script>
// Field counter
let fieldCounter = 0;
let fields = [];

// Field type options
const fieldTypes = [
    {value: 'text', label: 'Text'},
    {value: 'textarea', label: 'Textarea'},
    {value: 'number', label: 'Number'},
    {value: 'email', label: 'Email'},
    {value: 'date', label: 'Date'},
    {value: 'datetime', label: 'DateTime'},
    {value: 'select', label: 'Select/Dropdown'},
    {value: 'checkbox', label: 'Checkbox'},
    {value: 'radio', label: 'Radio'},
    {value: 'file', label: 'File Upload'},
    {value: 'password', label: 'Password'},
    {value: 'hidden', label: 'Hidden'}
];

// Database type options
const dbTypes = [
    {value: 'VARCHAR(255)', label: 'VARCHAR(255)'},
    {value: 'TEXT', label: 'TEXT'},
    {value: 'INT', label: 'INT'},
    {value: 'DECIMAL(10,2)', label: 'DECIMAL(10,2)'},
    {value: 'DATE', label: 'DATE'},
    {value: 'DATETIME', label: 'DATETIME'},
    {value: 'TIMESTAMP', label: 'TIMESTAMP'},
    {value: 'BOOLEAN', label: 'BOOLEAN/TINYINT(1)'}
];

// Add new field row
function addField() {
    const container = document.getElementById('fields-container');
    const fieldId = 'field_' + fieldCounter;
    
    const fieldHtml = `
        <div class="field-row border rounded p-3 mb-3" id="${fieldId}">
            <div class="row">
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Field Name *</label>
                    <input type="text" class="form-control form-control-sm" name="field_name[]" 
                           placeholder="e.g., name, price" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Label *</label>
                    <input type="text" class="form-control form-control-sm" name="field_label[]" 
                           placeholder="e.g., Product Name" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Input Type</label>
                    <select class="form-select form-select-sm" name="field_type[]">
                        ${fieldTypes.map(t => `<option value="${t.value}">${t.label}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label small">DB Type</label>
                    <input type="text" class="form-control form-control-sm" name="field_db_type[]" 
                           value="VARCHAR(255)" placeholder="VARCHAR(255)">
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Options</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="field_required[]" value="${fieldCounter}">
                        <label class="form-check-label small">Required</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="field_show_in_list[]" value="${fieldCounter}" checked>
                        <label class="form-check-label small">Show in List</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="field_editable[]" value="${fieldCounter}" checked>
                        <label class="form-check-label small">Editable</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-2">
                    <label class="form-label small">Validation Rules</label>
                    <input type="text" class="form-control form-control-sm" name="field_validation[]" 
                           placeholder="e.g., trim|min_length[3]|max_length[100]">
                    <small class="form-text text-muted">Comma-separated validation rules</small>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeField('${fieldId}')">
                <i class="bi bi-trash"></i> Remove
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', fieldHtml);
    fieldCounter++;
}

// Remove field row
function removeField(fieldId) {
    document.getElementById(fieldId).remove();
}

// Reset form
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All data will be lost.')) {
        document.getElementById('module-generator-form').reset();
        document.getElementById('fields-container').innerHTML = '';
        fieldCounter = 0;
        fields = [];
    }
}

// Auto-generate plural name
document.getElementById('module_name_singular').addEventListener('blur', function() {
    const singular = this.value;
    const pluralField = document.getElementById('module_name_plural');
    
    if (singular && !pluralField.value) {
        // Simple pluralization
        if (singular.endsWith('y')) {
            pluralField.value = singular.slice(0, -1) + 'ies';
        } else if (singular.endsWith('s') || singular.endsWith('x') || singular.endsWith('ch') || singular.endsWith('sh')) {
            pluralField.value = singular + 'es';
        } else {
            pluralField.value = singular + 's';
        }
    }
});

// Auto-generate table name from module name
document.getElementById('module_name_singular').addEventListener('blur', function() {
    const moduleName = this.value;
    const tableField = document.getElementById('table_name');
    
    if (moduleName && !tableField.value) {
        // Convert PascalCase to snake_case and pluralize
        let tableName = moduleName.replace(/([A-Z])/g, '_$1').toLowerCase().replace(/^_/, '');
        if (!tableName.endsWith('s')) {
            if (tableName.endsWith('y')) {
                tableName = tableName.slice(0, -1) + 'ies';
            } else {
                tableName = tableName + 's';
            }
        }
        tableField.value = tableName;
    }
});

// Collect fields data before form submission
document.getElementById('module-generator-form').addEventListener('submit', function(e) {
    const fieldRows = document.querySelectorAll('.field-row');
    fields = [];
    
    fieldRows.forEach((row, index) => {
        const fieldName = row.querySelector('input[name="field_name[]"]').value;
        const fieldLabel = row.querySelector('input[name="field_label[]"]').value;
        
        if (!fieldName || !fieldLabel) {
            alert('Please fill in all required field information');
            e.preventDefault();
            return false;
        }
        
        const field = {
            name: fieldName,
            label: fieldLabel,
            type: row.querySelector('select[name="field_type[]"]').value,
            db_type: row.querySelector('input[name="field_db_type[]"]').value || 'VARCHAR(255)',
            required: row.querySelector('input[name="field_required[]"]').checked,
            show_in_list: row.querySelector('input[name="field_show_in_list[]"]').checked,
            editable: row.querySelector('input[name="field_editable[]"]').checked,
            validation: row.querySelector('input[name="field_validation[]"]').value
        };
        
        fields.push(field);
    });
    
    if (fields.length === 0) {
        alert('Please add at least one field');
        e.preventDefault();
        return false;
    }
    
    document.getElementById('fields_json').value = JSON.stringify(fields);
});

// Add initial field on page load
window.addEventListener('DOMContentLoaded', function() {
    addField();
});
</script>

<style>
.field-row {
    background-color: #f8f9fa;
}
.field-row:hover {
    background-color: #e9ecef;
}
</style>

