# Booking and Reservation System - Complete Guide

## Overview

This is a complete booking and reservation system for BODARE Pension House with:
- **Customer Authentication**: User registration and login
- **Booking System**: Room availability checking and booking creation
- **Role-Based Access Control (RBAC)**: Admin system with permissions and roles
- **Customer Dashboard**: View and manage bookings
- **Admin Dashboard**: Manage bookings, rooms, users, and permissions

---

## System Architecture

### Frontend
- Static HTML/PHP pages in root directory
- JavaScript API clients (`api-config.js`, `booking-api.js`)
- Bootstrap-based responsive design

### Backend
- CodeIgniter 3.x framework (in `admin/` folder)
- RESTful API endpoints
- MVC architecture

### Database
- MySQL database: `bodarepensionhouse`
- Tables for users, bookings, rooms, admins, permissions, roles, groups

---

## Installation Steps

### 1. Database Setup

Run the database schema files in order:

```sql
-- Step 1: Run main schema
mysql -u root -p bodarepensionhouse < admin/database_schema.sql

-- Step 2: Run extended schema (users table and enhancements)
mysql -u root -p bodarepensionhouse < admin/database_schema_extended.sql
```

Or import via phpMyAdmin:
1. Import `admin/database_schema.sql`
2. Import `admin/database_schema_extended.sql`

### 2. Configure Database Connection

Edit `admin/application/config/database.php`:
```php
'hostname' => 'localhost',
'username' => 'root',
'password' => '', // Your MySQL password
'database' => 'bodarepensionhouse',
```

### 3. Configure Base URL

The base URL is auto-detected, but you can manually set it in:
`admin/application/config/config.php`

---

## API Endpoints

### Authentication Endpoints

#### Register User
```
POST /admin/api/auth/register
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "09123456789",
    "address": "123 Main St",
    "password": "password123",
    "confirm_password": "password123"
}
```

#### Login
```
POST /admin/api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Logout
```
POST /admin/api/auth/logout
```

#### Check Login Status
```
GET /admin/api/auth/check
```

### Booking Endpoints

#### Check Room Availability
```
GET /admin/api/booking/availability?check_in=2025-01-15&check_out=2025-01-17&guests=2
```

#### Get All Rooms
```
GET /admin/api/booking/rooms
```

#### Get Room Details
```
GET /admin/api/booking/room/{room_id}
```

#### Create Booking
```
POST /admin/api/booking/create
Content-Type: application/json

{
    "room_id": 1,
    "guest_name": "John Doe",
    "guest_email": "john@example.com",
    "guest_phone": "09123456789",
    "check_in": "2025-01-15",
    "check_out": "2025-01-17",
    "guests": 2,
    "notes": "Late check-in requested"
}
```

#### Get My Bookings (requires login)
```
GET /admin/api/booking/my-bookings
```

#### Get Booking by Number
```
GET /admin/api/booking/number/{booking_number}
```

#### Calculate Total
```
GET /admin/api/booking/calculate?room_id=1&check_in=2025-01-15&check_out=2025-01-17&guests=2
```

---

## User Roles and Permissions

### Default Roles

1. **Super Admin** - Full access to everything
2. **Admin** - Administrative access with user management
3. **Manager** - Can manage bookings and rooms
4. **Staff** - Can view and update bookings
5. **Receptionist** - Can view bookings and handle check-in/out

### Default Permissions

- `view_dashboard` - Access dashboard
- `manage_bookings` - Create, edit, delete bookings
- `view_bookings` - View bookings only
- `manage_rooms` - Create, edit, delete rooms
- `view_rooms` - View rooms only
- `manage_users` - Manage admin users
- `manage_groups` - Manage user groups
- `manage_roles` - Manage roles
- `manage_permissions` - Manage permissions
- `view_reports` - View reports
  - Daily Sales Report - View daily sales data, booking statistics, revenue totals, and sales breakdown by room type
- `system_settings` - System configuration

### Using Permissions in Controllers

```php
// Require permission (redirects if not authorized)
$this->require_permission('manage_bookings');

// Check permission (returns true/false)
if ($this->has_permission('view_bookings')) {
    // Show view
}

// Check role
if ($this->has_role('super_admin')) {
    // Admin only feature
}
```

---

## Frontend Pages

### Customer Pages

1. **index.php** - Homepage with room listings
2. **rooms.php** - Room browsing and selection
3. **room-detail.php** - Individual room details with booking widget
4. **checkout.php** - Booking checkout and payment info
5. **registration.php** - User registration
6. **login.php** - User login
7. **customer-dashboard.php** - View all bookings
8. **booking-confirmation.php** - Booking confirmation page

### Admin Pages

Access via: `http://localhost/bodarepensionhouse/admin/`

- `/login` - Admin login
- `/dashboard` - Admin dashboard
- `/bookings` - Manage bookings
- `/rooms` - Manage rooms
- `/users` - Manage admin users
- `/groups` - Manage user groups
- `/roles` - Manage roles

---

## Usage Examples

### Creating a Booking (JavaScript)

```javascript
// Check availability first
const availability = await API.booking.checkAvailability('2025-01-15', '2025-01-17', 2);

// Create booking
const booking = await API.booking.create({
    room_id: 1,
    guest_name: 'John Doe',
    guest_email: 'john@example.com',
    guest_phone: '09123456789',
    check_in: '2025-01-15',
    check_out: '2025-01-17',
    guests: 2
});
```

### User Registration (JavaScript)

```javascript
const response = await API.auth.register({
    first_name: 'John',
    last_name: 'Doe',
    email: 'john@example.com',
    phone: '09123456789',
    address: '123 Main St',
    password: 'password123',
    confirm_password: 'password123'
});
```

### Checking Permissions (PHP)

```php
// In a controller that extends Admin_Controller
public function edit_booking($id) {
    // Only users with manage_bookings permission can access
    $this->require_permission('manage_bookings');
    
    // Or check conditionally
    if ($this->has_permission('manage_bookings')) {
        // Allow editing
    } else if ($this->has_permission('view_bookings')) {
        // Show read-only view
    }
}
```

---

## Default Credentials

### Admin Login
- **URL**: `http://localhost/bodarepensionhouse/admin/login`
- **Username**: `admin`
- **Password**: `admin123`

⚠️ **IMPORTANT**: Change the default admin password immediately after first login!

---

## File Structure

```
bodarepensionhouse/
├── admin/                          # CodeIgniter application
│   ├── application/
│   │   ├── config/                  # Configuration files
│   │   ├── controllers/
│   │   │   ├── admin/               # Admin controllers
│   │   │   ├── api/                 # API controllers
│   │   │   │   ├── Auth.php         # Authentication API
│   │   │   │   └── Booking.php      # Booking API
│   │   │   └── customer/            # Customer controllers
│   │   ├── models/                  # Database models
│   │   │   ├── User_model.php       # User model
│   │   │   ├── Booking_model.php    # Booking model
│   │   │   └── Admin_model.php      # Admin model
│   │   ├── core/
│   │   │   ├── Admin_Controller.php # Admin base controller
│   │   │   └── Customer_Controller.php # Customer base controller
│   ├── database_schema.sql          # Main database schema
│   ├── database_schema_extended.sql # Extended schema
│   └── index.php                    # CodeIgniter entry point
├── api-config.js                    # API configuration
├── booking-api.js                   # Booking API functions
├── registration.php                 # Registration page
├── login.php                        # Login page
├── checkout.php                     # Checkout page
├── customer-dashboard.php           # Customer dashboard
└── booking-confirmation.php         # Booking confirmation
```

---

## Troubleshooting

### API Returns 404

1. Check that routes are configured in `admin/application/config/routes.php`
2. Ensure `.htaccess` is working (check if mod_rewrite is enabled)
3. Try accessing with `index.php` in URL: `/admin/index.php/api/booking/rooms`

### Permission Errors

1. Verify user is assigned to a group
2. Verify group has roles assigned
3. Verify roles have permissions assigned
4. Check that roles are active (`status = 'active'`)

### Database Connection Errors

1. Verify database credentials in `admin/application/config/database.php`
2. Ensure database `bodarepensionhouse` exists
3. Check that all tables were created successfully

### Session Issues

1. Check session configuration in `admin/application/config/config.php`
2. Ensure session save path is writable
3. Clear browser cookies if experiencing login issues

---

## Security Features

1. **Password Hashing**: All passwords are hashed using `password_hash()` with `PASSWORD_DEFAULT`
2. **Session Management**: Secure session handling for both admin and customer accounts
3. **Permission Checks**: All admin routes are protected by permission checks
4. **SQL Injection Protection**: CodeIgniter's Query Builder prevents SQL injection
5. **XSS Protection**: Input validation and output escaping
6. **CSRF Protection**: Available via CodeIgniter's CSRF tokens (can be enabled)

---

## Future Enhancements

- Email notifications for bookings
- Payment gateway integration
- Room calendar view
- Customer reviews and ratings
- Booking cancellation and modification
- Multi-currency support
- Advanced reporting and analytics
- Mobile app API

---

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review the documentation in `admin/` folder
3. Check CodeIgniter 3.x documentation: https://codeigniter.com/userguide3/

---

## License

This booking system is part of the BODARE Pension House project.

