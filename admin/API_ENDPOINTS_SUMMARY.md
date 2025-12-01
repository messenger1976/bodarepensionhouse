# API Endpoints Summary

This document lists all available API endpoints for the BODARE Pension House system.

## Base URL
- **Local Development**: `http://localhost/bodarepensionhouse/admin/index.php/api`
- **Production**: `https://pensionhouse.bodarempc.com/admin/index.php/api`

## Authentication Endpoints

| Endpoint | Method | Route | Controller Method | Description | Auth Required |
|----------|--------|-------|-------------------|-------------|---------------|
| `/api/auth/register` | POST | `api/auth/register` | `Auth::register()` | Register new user | No |
| `/api/auth/login` | POST | `api/auth/login` | `Auth::login()` | Login user | No |
| `/api/auth/logout` | POST | `api/auth/logout` | `Auth::logout()` | Logout user | No |
| `/api/auth/check` | GET | `api/auth/check` | `Auth::check()` | Check if user is logged in | No |
| `/api/auth/forgot-password` | POST | `api/auth/forgot-password` | `Auth::forgot_password()` | Request password reset | No |
| `/api/auth/verify-reset-token` | POST | `api/auth/verify-reset-token` | `Auth::verify_reset_token()` | Verify reset token | No |
| `/api/auth/reset-password` | POST | `api/auth/reset-password` | `Auth::reset_password()` | Reset password with token | No |

## Booking Endpoints

| Endpoint | Method | Route | Controller Method | Description | Auth Required |
|----------|--------|-------|-------------------|-------------|---------------|
| `/api/booking/get_rooms` | GET | `api/booking/get_rooms` | `Booking::get_rooms()` | Get all active rooms | No |
| `/api/booking/rooms` | GET | `api/booking/rooms` | `Booking::get_rooms()` | Get all active rooms (alias) | No |
| `/api/booking/availability` | GET | `api/booking/availability` | `Booking::check_availability()` | Check room availability | No |
| `/api/booking/get_availability` | GET | `api/booking/get_availability` | `Booking::get_availability()` | Get availability for specific date | No |
| `/api/booking/room/{id}` | GET | `api/booking/room/(:num)` | `Booking::get_room($id)` | Get room by ID | No |
| `/api/booking/room-code/{code}` | GET | `api/booking/room-code/(:any)` | `Booking::get_room_by_code($code)` | Get room by code | No |
| `/api/booking/calculate` | GET | `api/booking/calculate` | `Booking::calculate_total()` | Calculate booking total | No |
| `/api/booking/create` | POST | `api/booking/create` | `Booking::create()` | Create new booking | No (but can use user_id if logged in) |
| `/api/booking/my-bookings` | GET | `api/booking/my-bookings` | `Booking::my_bookings()` | Get user's bookings | Yes |
| `/api/booking/number/{number}` | GET | `api/booking/number/(:any)` | `Booking::get_by_number($number)` | Get booking by booking number | No (but checks ownership) |

## User Profile Endpoints

| Endpoint | Method | Route | Controller Method | Description | Auth Required |
|----------|--------|-------|-------------------|-------------|---------------|
| `/api/user/profile` | GET | `api/user/profile` | `User::profile()` | Get user profile | Yes |
| `/api/user/update` | POST | `api/user/update` | `User::update()` | Update user profile | Yes |

## Inquiry Endpoints

| Endpoint | Method | Route | Controller Method | Description | Auth Required |
|----------|--------|-------|-------------------|-------------|---------------|
| `/api/inquiry/submit` | POST | `api/inquiry/submit` | `Inquiry::submit()` | Submit an inquiry | Yes |

## Testing

To test all endpoints, access:
- **Test Script**: `admin/test_api_endpoints.php`

The test script will verify:
- All endpoints are accessible (not returning 404)
- Endpoints return proper HTTP status codes
- Responses are in JSON format
- Endpoints handle errors correctly

## CORS Configuration

All API endpoints support CORS with:
- `Access-Control-Allow-Origin: *` (or specific origin)
- `Access-Control-Allow-Methods: POST, GET, OPTIONS`
- `Access-Control-Allow-Headers: Content-Type`
- `Access-Control-Allow-Credentials: true` (for session-based auth)

## Response Format

All endpoints return JSON responses in the following format:

**Success:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... } // Optional validation errors
}
```

## Status Codes

- `200` - Success
- `400` - Bad Request (validation errors, missing parameters)
- `401` - Unauthorized (authentication required)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

## Notes

1. All endpoints are located in `admin/application/controllers/api/`
2. Routes are defined in `admin/application/config/routes.php`
3. The API directory was renamed from `Api` to `api` (lowercase) to fix case-sensitivity issues on Linux servers
4. Session-based authentication is used for protected endpoints
5. All endpoints set `Content-Type: application/json` header

