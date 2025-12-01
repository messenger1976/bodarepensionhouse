# CodeIgniter Module Generator - Complete Guide

## Overview

This guide explains how to use the Module Generator to quickly create CRUD (Create, Read, Update, Delete) modules in your CodeIgniter application without hardcoding. The generator automatically creates:

- **Controller** with index, add, edit, delete, and view methods
- **Model** with standard CRUD operations
- **View files** (index.php, add.php, edit.php, view.php)
- **Form validation rules**
- **Permission checks**
- **Database table structure** (SQL)

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Accessing the Module Generator](#accessing-the-module-generator)
3. [Creating a New Module](#creating-a-new-module)
4. [Understanding Generated Files](#understanding-generated-files)
5. [Manual Module Creation (Alternative)](#manual-module-creation-alternative)
6. [Best Practices](#best-practices)
7. [Troubleshooting](#troubleshooting)

---

## Quick Start

### Step 1: Access the Generator

1. Log into your admin panel
2. Navigate to: `http://your-domain/admin/module_generator`
3. Ensure you have proper admin permissions

### Step 2: Fill in Module Details

Fill in the form with:
- **Module Name** (singular, e.g., "Product", "Category", "Invoice")
- **Table Name** (database table name, e.g., "products", "categories", "invoices")
- **Fields Configuration** (define all database fields)

### Step 3: Generate Files

Click "Generate Module" and the system will:
- Create controller file
- Create model file
- Create view files (index, add, edit, view)
- Generate SQL table structure
- Set up proper permissions

---

## Accessing the Module Generator

### URL Path
```
http://your-domain/admin/module_generator
```

### Required Permissions
The module generator should have restricted access. You can protect it by requiring a specific permission like `manage_modules` or `super_admin` role.

---

## Creating a New Module

### Module Information

#### 1. Module Name (Singular)
- Use singular form: `Product`, `Category`, `Invoice`, `Customer`
- This will be used in class names and titles
- Example: `Product` → generates `Product` controller, `Product_model`, etc.

#### 2. Module Name (Plural - Optional)
- Automatically generated from singular (adds 's' or 'es')
- Can be customized if needed
- Example: `Product` → `Products`, `Category` → `Categories`

#### 3. Table Name
- Database table name (lowercase, underscores)
- Example: `products`, `product_categories`, `invoice_items`
- Will be used in all database queries

#### 4. Primary Key
- Default: `id`
- Can be changed if your table uses a different primary key
- Example: `product_id`, `user_id`

### Fields Configuration

For each field, you need to specify:

#### Field Name
- Database column name (e.g., `name`, `price`, `created_at`)

#### Field Label
- Display label in forms (e.g., "Product Name", "Price", "Date Created")

#### Field Type
- **text** - Text input
- **textarea** - Textarea
- **number** - Number input
- **email** - Email input
- **date** - Date input
- **datetime** - DateTime input
- **select** - Dropdown select
- **checkbox** - Checkbox
- **radio** - Radio buttons
- **file** - File upload
- **password** - Password field
- **hidden** - Hidden field

#### Database Type
- **VARCHAR(length)** - String (default 255)
- **TEXT** - Long text
- **INT** - Integer
- **DECIMAL(10,2)** - Decimal number
- **DATE** - Date only
- **DATETIME** - Date and time
- **TIMESTAMP** - Auto timestamp
- **ENUM('val1','val2')** - Enumeration
- **BOOLEAN/TINYINT(1)** - Boolean

#### Required
- Check if field is required
- Adds validation rules and database `NOT NULL` constraint

#### Show in List
- Whether to display this field in the index/list view table

#### Editable
- Whether field can be edited in add/edit forms

#### Searchable
- Whether field can be used for searching/filtering

#### Validation Rules
- Additional validation rules (comma-separated)
- Examples: `trim`, `is_unique[table.column]`, `min_length[3]`, `max_length[100]`

### Example: Creating a "Product" Module

**Module Information:**
- Module Name (Singular): `Product`
- Table Name: `products`
- Primary Key: `id`

**Fields:**
1. **name** - Text - VARCHAR(255) - Required - Show in List - Editable
2. **description** - Textarea - TEXT - Optional - Not in List - Editable
3. **price** - Number - DECIMAL(10,2) - Required - Show in List - Editable
4. **stock** - Number - INT - Required - Show in List - Editable
5. **status** - Select - ENUM('active','inactive') - Required - Show in List - Editable
6. **created_at** - DateTime - TIMESTAMP - Auto - Show in List - Not Editable

---

## Understanding Generated Files

### Controller (`application/controllers/admin/{Module}.php`)

The generated controller includes:

#### Standard Methods:
- `index()` - List all records
- `add()` - Display add form and process submission
- `edit($id)` - Display edit form and process submission
- `delete($id)` - Delete a record
- `view($id)` - View single record details

#### Features:
- Permission checks for each action
- Form validation
- Flash messages (success/error)
- Proper redirects
- Data sanitization

### Model (`application/models/{Module}_model.php`)

The generated model includes:

#### Standard Methods:
- `get_all_{table}()` - Get all records
- `get_{table}($id)` - Get single record by ID
- `create_{table}($data)` - Create new record
- `update_{table}($id, $data)` - Update existing record
- `delete_{table}($id)` - Delete record
- Additional helper methods as configured

### Views

#### `index.php` - List View
- Table displaying all records
- Add button (if permission allows)
- Edit/Delete buttons (if permissions allow)
- Search/filter functionality (if configured)
- Pagination ready

#### `add.php` - Add Form
- Form with all editable fields
- Validation error display
- Proper form structure with Bootstrap styling

#### `edit.php` - Edit Form
- Pre-filled form with existing data
- Same structure as add form
- Updates existing record

#### `view.php` - Detail View
- Display all fields in read-only mode
- Edit/Delete buttons (if permissions allow)

### SQL Table Structure

The generator creates SQL statements to:
- Create the database table
- Add all configured columns
- Set primary key
- Add indexes if needed
- Add timestamps (`created_at`, `updated_at`) if enabled

---

## Manual Module Creation (Alternative)

If you prefer to create modules manually, follow this structure:

### 1. Create Database Table

```sql
CREATE TABLE `your_table` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### 2. Create Model

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Your_module_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    public function get_all() {
        $this->db->order_by('id', 'DESC');
        return $this->db->get('your_table')->result();
    }
    
    public function get($id) {
        $this->db->where('id', $id);
        return $this->db->get('your_table')->row();
    }
    
    public function create($data) {
        $this->db->insert('your_table', $data);
        return $this->db->insert_id();
    }
    
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('your_table', $data);
    }
    
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('your_table');
    }
}
```

### 3. Create Controller

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Your_module extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Your_module_model');
        $this->load->library('form_validation');
    }
    
    public function index() {
        $this->require_permission('view_your_module');
        
        $data['title'] = 'Manage Your Module';
        $data['items'] = $this->Your_module_model->get_all();
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/your_module/index', $data);
        $this->load->view('admin/layout/footer');
    }
    
    // Add other methods: add(), edit(), delete(), view()
}
```

### 4. Create Views

Create directory: `application/views/admin/your_module/`

Create files:
- `index.php` - List view
- `add.php` - Add form
- `edit.php` - Edit form
- `view.php` - Detail view

---

## Best Practices

### 1. Naming Conventions
- **Modules**: Use singular, PascalCase (e.g., `Product`, `Invoice`)
- **Tables**: Use plural, lowercase with underscores (e.g., `products`, `invoice_items`)
- **Primary Keys**: Use `id` when possible for consistency

### 2. Permissions
Always set up permissions for new modules:
- `view_{module}` - View list
- `add_{module}` - Add new
- `edit_{module}` - Edit existing
- `delete_{module}` - Delete
- `view_{module}_detail` - View single record

### 3. Validation
- Always validate required fields
- Use appropriate validation rules
- Sanitize user input
- Use CodeIgniter's form_validation library

### 4. Security
- Always check permissions in controllers
- Use prepared statements (CodeIgniter Query Builder does this automatically)
- Escape output in views using `htmlspecialchars()` or `esc()`
- Validate and sanitize all input

### 5. Code Organization
- Keep controllers thin (business logic in models)
- Use models for all database operations
- Separate concerns properly
- Follow MVC pattern

### 6. Database Design
- Always include `created_at` and `updated_at` timestamps
- Use proper data types
- Add indexes for frequently queried columns
- Use foreign keys where appropriate

---

## Troubleshooting

### Generated files don't appear
- Check file permissions on `application/controllers/admin/` and `application/models/`
- Ensure PHP has write permissions
- Check error logs

### Module doesn't load
- Check if controller class name matches file name (case-sensitive)
- Verify model is loaded in controller constructor
- Check routes configuration

### Permission errors
- Verify permissions are set up in database
- Check Admin_Controller is properly loaded
- Ensure user has required permissions

### Form validation not working
- Check validation rules are properly formatted
- Verify form_validation library is loaded
- Check form submits to correct action

### Database errors
- Verify table exists and matches field configuration
- Check database connection settings
- Verify field names match database columns

---

## Advanced Usage

### Customizing Generated Code

After generating, you can customize:
- Add custom methods to model
- Add additional validation rules
- Customize view layouts
- Add relationships to other models
- Add custom permissions checks

### Adding Relationships

If your module relates to others, add relationship methods:

```php
// In Product_model.php
public function get_with_category($id) {
    $this->db->select('products.*, categories.name as category_name');
    $this->db->join('categories', 'categories.id = products.category_id');
    $this->db->where('products.id', $id);
    return $this->db->get('products')->row();
}
```

### Adding Search Functionality

Add search to index method:

```php
public function index() {
    $search = $this->input->get('search');
    
    if ($search) {
        $this->db->like('name', $search);
        $this->db->or_like('description', $search);
    }
    
    $data['items'] = $this->Your_module_model->get_all();
    // ...
}
```

---

## Summary

The Module Generator provides a fast, consistent way to create CRUD modules in your CodeIgniter application. By following this guide and using the generator, you can:

✅ Create complete modules in minutes instead of hours
✅ Maintain consistent code structure
✅ Reduce coding errors
✅ Follow best practices automatically
✅ Focus on custom business logic instead of boilerplate code

For questions or issues, refer to the generated code comments or contact your development team.

