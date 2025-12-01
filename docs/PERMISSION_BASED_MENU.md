# Permission-Based Sidebar Menu

## Overview

The sidebar menu now displays menu items based on user permissions. Only menu items that the logged-in admin has permission to access will be visible in the sidebar.

## Menu Items and Required Permissions

### Dashboard
- **Permission Required**: None
- **Visibility**: All logged-in admins can see this
- **Description**: Main dashboard accessible to everyone

### Bookings
- **Permission Required**: `view_bookings`
- **Visibility**: Only admins with view_bookings permission
- **Description**: Access to bookings management module

### Rooms
- **Permission Required**: `view_rooms`
- **Visibility**: Only admins with view_rooms permission
- **Description**: Access to rooms management module

### Users
- **Permission Required**: `view_users` OR `manage_users`
- **Visibility**: Admins with either permission can see this
- **Description**: Access to admin users management module

### Groups
- **Permission Required**: `manage_groups`
- **Visibility**: Only admins with manage_groups permission
- **Description**: Access to user groups management

### Roles
- **Permission Required**: `manage_roles`
- **Visibility**: Only admins with manage_roles permission
- **Description**: Access to roles management

### Reports
- **Permission Required**: `view_reports`
- **Visibility**: Only admins with view_reports permission
- **Description**: Access to reports module
- **Submenu Items**:
  - **Daily Sales Report** - View daily sales data and booking statistics for a specific date
    - Shows total revenue, booking counts, sales by room type, and detailed booking information
    - Includes print functionality with formatted table layout

### Module Generator
- **Permission Required**: Super Admin status
- **Visibility**: Only super admins
- **Description**: Access to module generator tool

## How It Works

1. **Permission Check**: On each page load, the sidebar checks permissions for each menu item
2. **Dynamic Display**: Menu items are only shown if the user has the required permission
3. **Security**: Even if a user knows the URL, they cannot access pages without proper permissions (controller-level checks)

## Permission Hierarchy

```
Admin User
  └── Assigned to User Groups
      └── Groups have Roles
          └── Roles have Permissions
              └── Permissions grant access to menu items
```

## Setting Up Permissions

### Step 1: Ensure Permissions Exist

Run the granular permissions SQL script:
```sql
-- Execute: admin/add_granular_permissions.sql
```

This creates permissions like:
- `view_bookings`, `add_bookings`, `edit_bookings`, `delete_bookings`
- `view_rooms`, `add_rooms`, `edit_rooms`, `delete_rooms`
- `view_users`, `add_users`, `edit_users`, `delete_users`
- `manage_users`, `manage_groups`, `manage_roles`
- `view_reports` - Access to reports module (Daily Sales Report)

### Step 2: Assign Permissions to Roles

```sql
-- Assign view_bookings permission to a role
INSERT INTO role_permissions (role_id, permission_id)
SELECT ROLE_ID, id FROM permissions WHERE slug = 'view_bookings';
```

### Step 3: Assign Roles to Groups

```sql
-- Assign role to group
INSERT INTO group_roles (group_id, role_id)
VALUES (GROUP_ID, ROLE_ID);
```

### Step 4: Assign Admin to Group

```sql
-- Assign admin user to group
INSERT INTO admin_user_groups (admin_id, group_id)
VALUES (ADMIN_ID, GROUP_ID);
```

## Testing Permissions

### Check What Menu Items a User Sees

1. **Login as the user**
2. **Check sidebar** - only items with required permissions will appear
3. **Verify in database**:
   ```sql
   SELECT p.slug, p.name
   FROM permissions p
   JOIN role_permissions rp ON rp.permission_id = p.id
   JOIN roles r ON r.id = rp.role_id
   JOIN group_roles gr ON gr.role_id = r.id
   JOIN admin_user_groups aug ON aug.group_id = gr.group_id
   WHERE aug.admin_id = YOUR_ADMIN_ID;
   ```

## Common Permission Sets

### Reception Staff
- `view_bookings` - See bookings
- `view_rooms` - See rooms
- No access to Users, Groups, Roles, Reports

### Manager
- `view_bookings`, `add_bookings`, `edit_bookings` - Manage bookings
- `view_rooms`, `add_rooms`, `edit_rooms` - Manage rooms
- `view_reports` - View daily sales reports
- No access to Users, Groups, Roles

### Admin
- All view permissions
- All add/edit permissions
- `view_reports` - Access to all reports
- `manage_groups`, `manage_roles` - Manage system settings

### Super Admin
- All permissions
- Access to Module Generator
- Full system access including all reports

## Customization

### Adding New Menu Items

To add a new menu item based on permission:

1. **Add permission check in header.php**:
   ```php
   <?php if ($admin_id && $this->Admin_model->has_permission($admin_id, 'your_permission')): ?>
   <a href="<?php echo base_url('your_module'); ?>" class="nav-link">
       <i class="bi bi-icon"></i> Your Module
   </a>
   <?php endif; ?>
   ```

2. **Create the permission in database**:
   ```sql
   INSERT INTO permissions (name, slug, description, module)
   VALUES ('Your Permission', 'your_permission', 'Description', 'your_module');
   ```

3. **Assign permission to roles as needed**

### Multiple Permission Options

If a menu item can be accessed with multiple permissions:

```php
<?php if ($admin_id && (
    $this->Admin_model->has_permission($admin_id, 'permission1') ||
    $this->Admin_model->has_permission($admin_id, 'permission2')
)): ?>
    <!-- Menu item -->
<?php endif; ?>
```

## Fallback Behavior

### No Permission System
If permission tables don't exist:
- Super admin (admin_id = 1) can see all menu items
- Other users may not see menu items

### Missing Permissions
If a permission doesn't exist:
- Menu item will not appear
- User cannot access the module (even if they know the URL)

## Benefits

1. **Security**: Users only see what they can access
2. **Clean UI**: No unnecessary menu items cluttering the sidebar
3. **User Experience**: Clear indication of available features
4. **Maintainability**: Easy to add/remove menu items based on permissions
5. **Scalability**: Easy to add new modules with permission checks

## Troubleshooting

### Menu Item Not Showing

1. **Check permission exists**:
   ```sql
   SELECT * FROM permissions WHERE slug = 'your_permission';
   ```

2. **Check user has permission**:
   ```sql
   SELECT p.slug FROM permissions p
   JOIN role_permissions rp ON rp.permission_id = p.id
   JOIN roles r ON r.id = rp.role_id
   JOIN group_roles gr ON gr.role_id = r.id
   JOIN admin_user_groups aug ON aug.group_id = gr.group_id
   WHERE aug.admin_id = YOUR_ADMIN_ID AND p.slug = 'your_permission';
   ```

3. **Check role is active**:
   ```sql
   SELECT * FROM roles WHERE id = ROLE_ID AND status = 'active';
   ```

### All Menu Items Missing

- Check if admin is logged in
- Check if Admin_model is loading correctly
- Check database connection

### Permission Check Not Working

- Verify permission tables exist
- Check Admin_model->has_permission() method
- Ensure admin_id is set in session

---

## Summary

The sidebar menu is now fully permission-based. Each menu item checks the user's permissions before displaying. This provides better security, cleaner UI, and a better user experience.

Users will only see menu items for modules they have access to, making the admin panel more intuitive and secure.

