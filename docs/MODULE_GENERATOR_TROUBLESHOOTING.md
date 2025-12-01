# Module Generator - Troubleshooting Guide

## 404 Error - Page Not Found

### Issue: Getting 404 error when accessing module_generator

### Solutions:

#### 1. **Check Routes File**
The routes have been added to `admin/application/config/routes.php`. Make sure these lines exist:

```php
// Module Generator routes
$route['module_generator'] = 'admin/module_generator/index';
$route['module_generator/generate'] = 'admin/module_generator/generate';
```

#### 2. **Access URL**
Try accessing the module generator using these URLs:

- `http://localhost/bodarepensionhouse/admin/module_generator`
- `http://your-domain/admin/module_generator`

**Important:** Make sure you're logged into the admin panel first!

#### 3. **Check Controller File**
Verify the controller file exists at:
- `admin/application/controllers/admin/Module_generator.php`

The file should contain the class:
```php
class Module_generator extends Admin_Controller {
```

#### 4. **Check Case Sensitivity**
On Linux servers, file names are case-sensitive. Make sure:
- File name: `Module_generator.php` (capital M)
- Class name: `Module_generator` (capital M)
- URL: `module_generator` (lowercase is fine)

#### 5. **Clear Cache**
If you have CodeIgniter cache enabled, try clearing it:
- Delete files in `admin/application/cache/` directory

#### 6. **Check .htaccess**
If you have `.htaccess` file, make sure it's not blocking the route.

#### 7. **Check Permissions**
Make sure the controller file has proper read permissions:
- File should be readable by the web server

#### 8. **Direct Controller Access Test**
Try accessing directly:
- `http://your-domain/admin/admin/module_generator`

If this works, there's a routing issue.

### Alternative: Manual Route Testing

You can test if routes are working by temporarily adding this to your controller's index method:

```php
public function index() {
    die('Module Generator is accessible!');
    // ... rest of code
}
```

If you see the message, the route is working and the issue is elsewhere.

### Still Not Working?

1. **Check Error Logs**
   - Check `admin/application/logs/` for error messages
   - Check Apache/Nginx error logs

2. **Enable Debug Mode**
   In `admin/application/config/config.php`, temporarily set:
   ```php
   $config['log_threshold'] = 4; // Enable all logging
   ```

3. **Verify Base URL**
   Check `admin/application/config/config.php` has correct base_url setting

4. **Check .htaccess File**
   Make sure you have proper rewrite rules for CodeIgniter

### Common Issues

#### Issue: "Class Module_generator not found"
**Solution:** Check file name matches class name exactly

#### Issue: "404 after login"
**Solution:** Make sure you're accessing the correct URL path

#### Issue: "Permission denied"
**Solution:** You need to be logged in as admin first

### Test Routes

To verify routes are loaded, you can add this temporary test at the end of `routes.php`:

```php
// Test route (remove after testing)
$route['test_module_generator'] = 'admin/module_generator/index';
```

Then access: `http://your-domain/admin/test_module_generator`

If this works, the controller is fine and it's a routing configuration issue.

