# Production Server Troubleshooting Guide

## Common Issues When Moving from Localhost to Production

### 1. Admin Name Not Displaying Correctly

**Symptoms:**
- Admin panel shows "admin" or empty name even after login
- Different admin users all show the same name

**Possible Causes:**
- Session not persisting correctly
- Database `name` field is empty for admin users
- Session configuration issues on production server

**Solutions:**
1. Check if session is working:
   ```php
   // Add this temporarily to a controller to debug
   var_dump($this->session->userdata('admin_name'));
   var_dump($this->session->userdata('admin_username'));
   ```

2. Check database:
   ```sql
   SELECT id, username, name, email FROM admins;
   ```
   If `name` is NULL or empty, update it:
   ```sql
   UPDATE admins SET name = 'Your Name' WHERE id = X;
   ```

3. Check session configuration in `config.php`:
   - Ensure `sess_save_path` is writable
   - Check `sess_driver` (file/database)
   - Verify `sess_cookie_name` and `sess_expiration`

### 2. Case Sensitivity Issues

**Symptoms:**
- 404 errors on API endpoints
- Controllers not found

**Solution:**
- Ensure directory names are lowercase: `api` not `Api`
- Ensure controller class names match file names exactly
- Check file permissions (755 for directories, 644 for files)

### 3. Database Connection Issues

**Symptoms:**
- "Database connection not available" errors
- Blank pages

**Check:**
1. Verify `admin/application/config/database.php` has correct production credentials
2. Check database host allows connections from production server IP
3. Verify database user has proper permissions

### 4. File Permission Issues

**Symptoms:**
- Cannot write to logs
- Session not saving
- Uploads not working

**Solution:**
```bash
# Set proper permissions
chmod 755 admin/application/cache
chmod 755 admin/application/logs
chmod 644 admin/application/config/database.php
chmod 644 admin/application/config/config.php
```

### 5. Base URL Issues

**Symptoms:**
- CSS/JS not loading
- Redirects going to wrong URLs
- API endpoints returning 404

**Solution:**
Check `admin/application/config/config.php`:
- Base URL should auto-detect, but you can hardcode for production:
```php
$config['base_url'] = 'https://pensionhouse.bodarempc.com/admin/';
```

### 6. .htaccess Issues

**Symptoms:**
- URLs not rewriting correctly
- 404 errors on clean URLs

**Check:**
1. Ensure mod_rewrite is enabled on Apache
2. Verify `.htaccess` file exists in `admin/` directory
3. Check Apache `AllowOverride` is set to `All` for the directory

### 7. PHP Version Differences

**Symptoms:**
- Syntax errors
- Deprecated function warnings

**Solution:**
- Ensure production PHP version is 7.4 or higher
- Check `php.ini` settings match requirements

### 8. Session Issues

**Symptoms:**
- Logged out after page refresh
- Session data not persisting

**Check:**
1. Session save path is writable
2. Session cookies are being set (check browser dev tools)
3. If using database sessions, ensure table exists:
```sql
CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
  `data` blob NOT NULL,
  KEY `ci_sessions_timestamp` (`timestamp`)
);
```

### 9. Error Reporting

**Symptoms:**
- Blank pages with no error messages
- Can't see what's wrong

**Solution:**
Check `admin/index.php`:
```php
define('ENVIRONMENT', 'production'); // Change to 'development' temporarily for debugging
```

### 10. CORS Issues (for API)

**Symptoms:**
- API calls failing from frontend
- CORS errors in browser console

**Solution:**
- Verify CORS headers are set in API controllers
- Check if production server has additional CORS restrictions

## Quick Diagnostic Script

Create a file `admin/check_production.php`:

```php
<?php
// Production Environment Check
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Production Environment Check</h1>";

// Check PHP version
echo "<p>PHP Version: " . phpversion() . "</p>";

// Check if CodeIgniter can load
define('BASEPATH', 'system/');
define('APPPATH', 'application/');

// Check database connection
require_once 'application/config/database.php';
$db = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
if ($db->connect_error) {
    echo "<p style='color:red;'>Database Connection: FAILED - " . $db->connect_error . "</p>";
} else {
    echo "<p style='color:green;'>Database Connection: OK</p>";
}

// Check file permissions
$writable_dirs = ['application/cache', 'application/logs'];
foreach ($writable_dirs as $dir) {
    if (is_writable($dir)) {
        echo "<p style='color:green;'>$dir: Writable</p>";
    } else {
        echo "<p style='color:red;'>$dir: NOT Writable</p>";
    }
}

// Check session
session_start();
$_SESSION['test'] = 'test';
if (isset($_SESSION['test'])) {
    echo "<p style='color:green;'>Session: Working</p>";
} else {
    echo "<p style='color:red;'>Session: NOT Working</p>";
}

// Check admin users
$result = $db->query("SELECT id, username, name, email FROM admins LIMIT 5");
echo "<h2>Admin Users:</h2>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['username'] . "</td>";
    echo "<td>" . ($row['name'] ? $row['name'] : '<em>empty</em>') . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "</tr>";
}
echo "</table>";

$db->close();
?>
```

## Next Steps

1. **Tell me the specific error** you're seeing on production
2. Check browser console for JavaScript errors
3. Check server error logs: `admin/application/logs/`
4. Run the diagnostic script above
5. Verify the issue is related to the admin name display or something else

