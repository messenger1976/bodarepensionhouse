# Quick Setup Guide - Booking System

## Prerequisites
- XAMPP installed (or any PHP/MySQL server)
- CodeIgniter 3.x system folder (should already be in place)

## Step-by-Step Setup

### 1. Start XAMPP Services
- Start Apache
- Start MySQL

### 2. Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database: `bodarepensionhouse`
3. Select the database

### 3. Import Database Schema

#### Option A: Via phpMyAdmin
1. Click "Import" tab
2. Choose file: `admin/database_schema.sql`
3. Click "Go"
4. Repeat for `admin/database_schema_extended.sql`

#### Option B: Via Command Line
```bash
cd C:\xampp3\htdocs\bodarepensionhouse
mysql -u root -p bodarepensionhouse < admin/database_schema.sql
mysql -u root -p bodarepensionhouse < admin/database_schema_extended.sql
```

### 4. Configure Database Connection

Edit: `admin/application/config/database.php`

```php
'hostname' => 'localhost',
'username' => 'root',
'password' => '',  // Your MySQL password (empty for default XAMPP)
'database' => 'bodarepensionhouse',
```

### 5. Update API Base URL (if needed)

If your installation path is different, edit: `api-config.js`

```javascript
const API_BASE_URL = window.location.origin + '/your-path/admin/api';
```

### 6. Test the System

#### Test Admin Panel
1. Go to: `http://localhost/bodarepensionhouse/admin/login`
2. Login with:
   - Username: `admin`
   - Password: `admin23`

#### Test Frontend
1. Go to: `http://localhost/bodarepensionhouse/`
2. Browse rooms
3. Try to make a booking

#### Test API
1. Go to: `http://localhost/bodarepensionhouse/admin/api/booking/rooms`
2. Should see JSON response with room data

### 7. Test User Registration

1. Go to: `http://localhost/bodarepensionhouse/registration.php`
2. Create a test account
3. Try logging in: `http://localhost/bodarepensionhouse/login.php`

---

## Common Issues

### Issue: 404 Errors on API Calls

**Solution:**
1. Check if mod_rewrite is enabled in Apache
2. Ensure `.htaccess` file exists in `admin/` folder
3. Try accessing with `index.php`: `/admin/index.php/api/booking/rooms`

### Issue: Database Connection Failed

**Solution:**
1. Verify MySQL is running
2. Check database credentials in `admin/application/config/database.php`
3. Ensure database `bodarepensionhouse` exists

### Issue: Permission Denied Errors

**Solution:**
1. Admin login: Check if you're logged in as admin
2. Permission checks: Verify user has required permissions through groups and roles

---

## Default Login Credentials

### Admin
- URL: `/admin/login`
- Username: `admin`
- Password: `admin23`

⚠️ **Change this immediately after setup!**

### Customer
- No default customer accounts
- Register at: `/registration.php`

---

## Next Steps

1. ✅ Change admin password
2. ✅ Create additional admin users if needed
3. ✅ Set up user groups and roles
4. ✅ Customize room details
5. ✅ Test booking flow end-to-end

---

## File Checklist

Make sure these files exist:
- ✅ `admin/database_schema.sql`
- ✅ `admin/database_schema_extended.sql`
- ✅ `admin/application/config/database.php`
- ✅ `api-config.js`
- ✅ `booking-api.js`
- ✅ All frontend pages (registration.php, login.php, etc.)

---

## Support

Refer to `BOOKING_SYSTEM_README.md` for detailed documentation.

