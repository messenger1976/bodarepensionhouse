# Module Generator - Super Admin Access Setup

## Overview

The Module Generator has been configured to be accessible **only by Super Administrators**. This ensures that only trusted users can generate new modules for the system.

## What Was Configured

### 1. Super Admin Detection
- Added `is_super_admin()` method to `Admin_model`
- Checks if user has 'super_admin' role OR is admin_id = 1 (first admin)
- Added helper method in `Admin_Controller` for easy access

### 2. Sidebar Menu Item
- Module Generator menu item appears in sidebar **only for super admins**
- Visual distinction:
  - Badge showing "Super Admin"
  - Special styling with border highlight
  - Separator line above it

### 3. Access Control
- Controller-level protection in `Module_generator.php`
- Non-super admins are redirected to dashboard with error message
- Menu item is hidden from non-super admins

## How to Verify Super Admin Status

### Check via Database

```sql
-- Check if user has super_admin role
SELECT a.id, a.username, r.name as role_name, r.slug as role_slug
FROM admins a
JOIN admin_user_groups aug ON aug.admin_id = a.id
JOIN group_roles gr ON gr.group_id = aug.group_id
JOIN roles r ON r.id = gr.role_id
WHERE a.username = 'admin' AND r.slug = 'super_admin';
```

### Check via Code

In any controller that extends `Admin_Controller`:
```php
if ($this->is_super_admin()) {
    // User is super admin
}
```

## Making a User Super Admin

### Method 1: Assign Super Admin Role

1. Ensure the user is assigned to a group (e.g., "Administrators")
2. Ensure that group has the "Super Admin" role assigned
3. The role slug should be 'super_admin'

```sql
-- Assign user to Administrators group (group_id = 1)
INSERT INTO admin_user_groups (admin_id, group_id)
VALUES (USER_ID, 1)
ON DUPLICATE KEY UPDATE admin_id = admin_id;

-- Ensure Administrators group has Super Admin role
INSERT INTO group_roles (group_id, role_id)
SELECT 1, id FROM roles WHERE slug = 'super_admin'
ON DUPLICATE KEY UPDATE group_id = group_id;
```

### Method 2: First Admin (Fallback)

The user with `admin_id = 1` is automatically considered super admin, even without role assignment. This is a fallback mechanism.

## Menu Item Appearance

The Module Generator menu item will appear in the sidebar with:
- **Icon**: Magic wand (bi-magic)
- **Label**: Module Generator
- **Badge**: "Super Admin" badge
- **Styling**: Highlighted with border accent
- **Position**: After Roles, before Logout, with separator above

## Access Behavior

### For Super Admin Users:
✅ Menu item visible in sidebar
✅ Can access `/admin/module_generator`
✅ Can generate modules

### For Regular Admin Users:
❌ Menu item NOT visible in sidebar
❌ Cannot access `/admin/module_generator` (redirected to dashboard)
❌ Error message shown: "Access denied. Module Generator is only accessible to Super Administrators."

## Security Notes

1. **Controller Protection**: Even if someone knows the URL, they cannot access the module generator without super admin status
2. **Menu Hiding**: The menu item is completely hidden from non-super admins for better UX
3. **Role-Based**: Uses the role system for flexible permission management
4. **Fallback**: Admin ID = 1 is super admin as backup

## Troubleshooting

### Menu Item Not Showing
- Verify user has super_admin role assigned
- Check if user is admin_id = 1
- Clear browser cache and refresh
- Check database for role assignment

### Access Denied Error
- User is not super admin
- Check role assignments in database
- Verify Admin_model->is_super_admin() returns true

### Want to Allow Other Users?
- Either assign super_admin role to them
- Or modify the access check in `Module_generator.php` controller

## Files Modified

1. `admin/application/models/Admin_model.php` - Added `is_super_admin()` method
2. `admin/application/core/Admin_Controller.php` - Added helper method
3. `admin/application/views/admin/layout/header.php` - Added conditional menu item
4. `admin/application/controllers/admin/Module_generator.php` - Added access restriction

---

**Note**: Super admin status is checked on every page load for the sidebar, so changes to user roles will be reflected immediately after page refresh.

