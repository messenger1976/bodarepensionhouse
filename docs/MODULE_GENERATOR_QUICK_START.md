# Module Generator - Quick Start Guide

## What is the Module Generator?

The Module Generator is a web-based tool that automatically creates complete CRUD (Create, Read, Update, Delete) modules for your CodeIgniter application. Instead of manually writing controllers, models, and views, you can generate them in minutes!

## How to Access

1. Log into your admin panel
2. Navigate to: `http://your-domain/admin/module_generator`
3. Fill in the form and generate your module

## Quick Example: Creating a "Product" Module

### Step 1: Fill Basic Information
- **Module Name (Singular)**: `Product`
- **Table Name**: `products` (auto-generated)
- **Primary Key**: `id` (default)

### Step 2: Add Fields
Click "Add Field" and configure:

**Field 1: Name**
- Field Name: `name`
- Label: `Product Name`
- Input Type: `Text`
- DB Type: `VARCHAR(255)`
- ✅ Required
- ✅ Show in List
- ✅ Editable

**Field 2: Price**
- Field Name: `price`
- Label: `Price`
- Input Type: `Number`
- DB Type: `DECIMAL(10,2)`
- ✅ Required
- ✅ Show in List
- ✅ Editable

**Field 3: Description**
- Field Name: `description`
- Label: `Description`
- Input Type: `Textarea`
- DB Type: `TEXT`
- ❌ Required (optional)
- ❌ Show in List
- ✅ Editable

**Field 4: Status**
- Field Name: `status`
- Label: `Status`
- Input Type: `Select`
- DB Type: `ENUM('active','inactive')`
- ✅ Required
- ✅ Show in List
- ✅ Editable

### Step 3: Generate
Click "Generate Module" button

### Step 4: Execute SQL
Copy the generated SQL and run it in your database:
```sql
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('active','inactive') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 5: Set Permissions
Add these permissions to your permissions table:
- `view_products`
- `add_products`
- `edit_products`
- `delete_products`

### Step 6: Test
Navigate to: `http://your-domain/admin/products`

## What Gets Generated?

✅ **Controller** (`application/controllers/admin/Product.php`)
   - index() - List all products
   - add() - Add new product
   - edit($id) - Edit product
   - delete($id) - Delete product
   - view($id) - View product details

✅ **Model** (`application/models/Product_model.php`)
   - get_all() - Get all records
   - get($id) - Get single record
   - create($data) - Create new record
   - update($id, $data) - Update record
   - delete($id) - Delete record
   - count_all() - Get total count

✅ **Views** (`application/views/admin/product/`)
   - index.php - List view with table
   - add.php - Add form
   - edit.php - Edit form
   - view.php - Detail view

✅ **SQL** - Complete table structure

## Features

- ✅ Automatic permission checks
- ✅ Form validation
- ✅ Flash messages
- ✅ Bootstrap-styled forms
- ✅ Responsive design
- ✅ Security features (XSS protection, CSRF ready)

## Tips

1. **Field Names**: Use lowercase with underscores (e.g., `product_name`, `created_at`)
2. **Required Fields**: Always mark required fields to add validation
3. **Show in List**: Only mark important fields to show in the table
4. **Database Types**: Match your DB type with input type (e.g., DECIMAL for numbers)
5. **Customization**: Generated code is customizable - modify as needed after generation

## Need Help?

See the complete guide: `MODULE_GENERATOR_GUIDE.md`

## Troubleshooting

**Files not generated?**
- Check file permissions on `application/controllers/admin/` and `application/models/`
- Ensure PHP has write permissions

**Module not loading?**
- Check controller class name matches file name
- Verify model is loaded correctly
- Check routes configuration

**Permission errors?**
- Verify permissions are added to database
- Assign permissions to your user/role

