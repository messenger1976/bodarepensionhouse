# Granular Permissions System

This document explains the granular role-based access control (RBAC) system implemented for the BODARE Pension House admin panel.

## Overview

The system now supports granular permissions that allow you to control who can add, edit, or delete records in each module (bookings, users, and rooms).

## Permission Structure

### Bookings Module
- `view_bookings` - View bookings list and details
- `add_bookings` - Create new bookings
- `edit_bookings` - Edit existing bookings
- `delete_bookings` - Delete bookings

### Users Module
- `view_users` or `manage_users` - View users list (fallback to manage_users)
- `add_users` - Create new admin users
- `edit_users` - Edit existing admin users
- `delete_users` - Delete admin users

### Rooms Module
- `view_rooms` - View rooms list
- `add_rooms` - Create new rooms
- `edit_rooms` - Edit existing rooms
- `delete_rooms` - Delete rooms

## Installation

1. **Run the SQL script** to add the new permissions:
   ```sql
   -- Execute the file: admin/add_granular_permissions.sql
   ```

2. **Assign permissions to roles** as needed. The script automatically assigns all granular permissions to the Super Admin role (role_id = 1).

## How It Works

### Controller Implementation

Each controller method now checks for specific permissions:

```php
// Example: Bookings Controller
public function add() {
    $this->require_permission('add_bookings');
    // ... rest of the method
}

public function edit($id) {
    $this->require_permission('edit_bookings');
    // ... rest of the method
}

public function delete($id) {
    $this->require_permission('delete_bookings');
    // ... rest of the method
}
```

### View Implementation

In your views, you can check permissions to show/hide buttons:

```php
<?php if ($this->has_permission('add_bookings')): ?>
    <a href="<?php echo base_url('bookings/add'); ?>" class="btn btn-primary">Add Booking</a>
<?php endif; ?>

<?php if ($this->has_permission('edit_bookings')): ?>
    <a href="<?php echo base_url('bookings/edit/' . $booking->id); ?>" class="btn btn-warning">Edit</a>
<?php endif; ?>

<?php if ($this->has_permission('delete_bookings')): ?>
    <a href="<?php echo base_url('bookings/delete/' . $booking->id); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
<?php endif; ?>
```

## Assigning Permissions to Roles

### Example: Create a "Booking Manager" Role

This role can add and edit bookings but cannot delete them:

```sql
-- 1. Create the role
INSERT INTO `roles` (`name`, `slug`, `description`, `status`) VALUES
('Booking Manager', 'booking_manager', 'Can add and edit bookings but not delete', 'active');

-- Get the role_id (let's assume it's 6)

-- 2. Assign permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 6, `id` FROM `permissions` 
WHERE `slug` IN ('view_bookings', 'add_bookings', 'edit_bookings');

-- 3. Assign role to a group
INSERT INTO `group_roles` (`group_id`, `role_id`) VALUES
(2, 6); -- Assuming group_id 2 is "Managers"
```

### Example: Create a "View-Only Staff" Role

This role can only view bookings and rooms:

```sql
-- 1. Create the role
INSERT INTO `roles` (`name`, `slug`, `description`, `status`) VALUES
('View-Only Staff', 'view_only_staff', 'Can only view bookings and rooms', 'active');

-- Get the role_id (let's assume it's 7)

-- 2. Assign permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 7, `id` FROM `permissions` 
WHERE `slug` IN ('view_dashboard', 'view_bookings', 'view_rooms');
```

## Permission Hierarchy

The system follows this hierarchy:
```
Admin Users → User Groups → Roles → Permissions
```

1. **Admin Users** are assigned to **User Groups**
2. **User Groups** are assigned **Roles**
3. **Roles** have specific **Permissions**

When checking permissions, the system:
1. Gets all groups for the admin user
2. Gets all roles for those groups
3. Gets all permissions for those roles
4. Checks if the required permission exists

## Available Methods

### In Controllers (extends Admin_Controller)

```php
// Check if user has permission
if ($this->has_permission('add_bookings')) {
    // User can add bookings
}

// Require permission (redirects if not authorized)
$this->require_permission('edit_bookings');

// Get all permissions for current admin
$permissions = $this->get_admin_permissions();

// Get all roles for current admin
$roles = $this->get_admin_roles();

// Get all groups for current admin
$groups = $this->get_admin_groups();
```

### In Views

```php
<?php if ($this->has_permission('delete_bookings')): ?>
    <!-- Show delete button -->
<?php endif; ?>
```

## Default Permissions

After running the SQL script, the Super Admin role (role_id = 1) will have all granular permissions automatically assigned.

For other roles, you'll need to manually assign the specific permissions you want them to have.

## Troubleshooting

### Permission not working?

1. **Check if the permission exists in the database:**
   ```sql
   SELECT * FROM `permissions` WHERE `slug` = 'add_bookings';
   ```

2. **Check if the role has the permission:**
   ```sql
   SELECT rp.*, p.slug, r.name as role_name
   FROM `role_permissions` rp
   JOIN `permissions` p ON p.id = rp.permission_id
   JOIN `roles` r ON r.id = rp.role_id
   WHERE p.slug = 'add_bookings';
   ```

3. **Check if the admin user is in a group with the role:**
   ```sql
   SELECT ag.*, g.name as group_name, r.name as role_name
   FROM `admin_user_groups` ag
   JOIN `user_groups` g ON g.id = ag.group_id
   JOIN `group_roles` gr ON gr.group_id = g.id
   JOIN `roles` r ON r.id = gr.role_id
   WHERE ag.admin_id = 1; -- Replace with your admin_id
   ```

## Best Practices

1. **Always use granular permissions** in controllers to ensure proper access control
2. **Check permissions in views** to hide/show UI elements based on user capabilities
3. **Test permissions** after creating new roles or assigning permissions
4. **Document custom roles** and their permissions for future reference
5. **Use descriptive permission slugs** that clearly indicate what they allow

## Support

For questions or issues with the permission system, refer to:
- `admin/USER_AND_PERMISSION_SETUP.md` (if exists)
- Database schema: `admin1/database_schema.sql`
- Permission models: `admin/application/models/Permission_model.php` and `User_group_model.php`

