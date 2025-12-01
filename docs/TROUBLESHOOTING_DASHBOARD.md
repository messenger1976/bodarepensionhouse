# Customer Dashboard Troubleshooting Guide

## Common Issues and Solutions

### 1. **"Please log in to view your profile" Error**

**Problem:** API endpoints can't access the session because cookies aren't being sent with fetch requests.

**Solution:** The API needs to accept session cookies. Check:

1. **Browser Console Errors:**
   - Open browser DevTools (F12)
   - Check Console tab for errors
   - Check Network tab to see if API calls are failing

2. **Session Cookie Issues:**
   - Make sure `credentials: 'include'` is set in fetch requests
   - Check if cookies are being sent in the Network tab

3. **CORS Configuration:**
   - The API needs to allow credentials in CORS headers

### 2. **Dashboard Not Loading Profile Data**

**Possible Causes:**
- User not logged in (check localStorage for 'user')
- API endpoint not accessible
- Database table missing
- Session not persisting

**Check:**
1. Open browser console and check for JavaScript errors
2. Check Network tab to see API response
3. Verify user is in localStorage: `localStorage.getItem('user')`

### 3. **"Failed to load bookings" Error**

**Check:**
- User must be logged in
- Bookings table must exist
- User must have bookings in database

### 4. **Inquiry Form Not Working**

**Check:**
- `inquiries` table must be created in database
- Run: `admin/create_inquiries_table.sql`
- Check browser console for errors

### 5. **Profile Update Not Working**

**Check:**
- All fields must be filled
- Email must be valid format
- Password must be 6+ characters if provided
- Check browser console for validation errors

## Quick Fixes

### Fix 1: Update API Config to Include Credentials

The `api-config.js` needs to send cookies with requests. Update the `request` method to include credentials.

### Fix 2: Check Database Tables

Make sure these tables exist:
- `users` table
- `bookings` table  
- `inquiries` table (run the SQL file)

### Fix 3: Check Session Configuration

Verify session is configured in `admin/application/config/config.php`

## Testing Steps

1. **Test Login:**
   - Login at registration.php or checkout.php
   - Check if redirected to customer-dashboard.php
   - Check localStorage for 'user' data

2. **Test Dashboard:**
   - Open customer-dashboard.php
   - Check browser console for errors
   - Try switching between tabs

3. **Test API Endpoints:**
   - Open browser DevTools > Network tab
   - Try loading profile or bookings
   - Check response status and data

