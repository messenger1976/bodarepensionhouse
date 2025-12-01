<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'index';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'admin/auth/login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Admin Panel Routes
$route[''] = 'admin/auth/login';
$route['login'] = 'admin/auth/login';
$route['logout'] = 'admin/auth/logout';
$route['dashboard'] = 'admin/dashboard/index';
$route['profile'] = 'admin/profile/index';
$route['profile/update'] = 'admin/profile/update';
$route['bookings'] = 'admin/bookings/index';
$route['bookings/add'] = 'admin/bookings/add';
$route['bookings/(:num)'] = 'admin/bookings/view/$1';
$route['bookings/edit/(:num)'] = 'admin/bookings/edit/$1';
$route['bookings/delete/(:num)'] = 'admin/bookings/delete/$1';
$route['booking_settings'] = 'admin/booking_settings/index';
$route['rooms'] = 'admin/rooms/index';
$route['rooms/add'] = 'admin/rooms/add';
$route['rooms/edit/(:num)'] = 'admin/rooms/edit/$1';
$route['rooms/delete/(:num)'] = 'admin/rooms/delete/$1';
$route['rooms/calendar'] = 'admin/rooms/calendar';
$route['rooms/get_availability_data'] = 'admin/rooms/get_availability_data';
$route['rooms/upload_image/(:num)'] = 'admin/rooms/upload_image/$1';
$route['rooms/delete_image/(:num)'] = 'admin/rooms/delete_image/$1';
$route['rooms/set_primary_image/(:num)/(:num)'] = 'admin/rooms/set_primary_image/$1/$2';
$route['room_settings'] = 'admin/room_settings/index';

// Inquiry Management routes
$route['inquiries'] = 'admin/inquiries/index';
$route['inquiries/(:num)'] = 'admin/inquiries/view/$1';
$route['inquiries/update_status/(:num)'] = 'admin/inquiries/update_status/$1';
$route['inquiries/delete/(:num)'] = 'admin/inquiries/delete/$1';

// Customer/Guest Management routes
$route['customers'] = 'admin/customers/index';
$route['customers/add'] = 'admin/customers/add';
$route['customers/edit/(:num)'] = 'admin/customers/edit/$1';
$route['customers/delete/(:num)'] = 'admin/customers/delete/$1';
$route['customers/view/(:num)'] = 'admin/customers/view/$1';

// User Management routes
$route['users'] = 'admin/users/index';
$route['users/add'] = 'admin/users/add';
$route['users/edit/(:num)'] = 'admin/users/edit/$1';
$route['users/delete/(:num)'] = 'admin/users/delete/$1';
$route['users/view/(:num)'] = 'admin/users/view/$1';

// Group Management routes
$route['groups'] = 'admin/groups/index';
$route['groups/add'] = 'admin/groups/add';
$route['groups/edit/(:num)'] = 'admin/groups/edit/$1';
$route['groups/delete/(:num)'] = 'admin/groups/delete/$1';

// Role Management routes
$route['roles'] = 'admin/roles/index';
$route['roles/add'] = 'admin/roles/add';
$route['roles/edit/(:num)'] = 'admin/roles/edit/$1';
$route['roles/delete/(:num)'] = 'admin/roles/delete/$1';

// Module Generator routes
$route['module_generator'] = 'admin/module_generator/index';
$route['module_generator/generate'] = 'admin/module_generator/generate';

// Reports routes
$route['reports'] = 'admin/reports/daily_sales';
$route['reports/daily_sales'] = 'admin/reports/daily_sales';
$route['reports/export_excel'] = 'admin/reports/export_excel';

// API Routes for Frontend
$route['api/auth/register'] = 'api/auth/register';
$route['api/auth/login'] = 'api/auth/login';
$route['api/auth/logout'] = 'api/auth/logout';
$route['api/auth/check'] = 'api/auth/check';
$route['api/auth/forgot-password'] = 'api/auth/forgot_password';
$route['api/auth/verify-reset-token'] = 'api/auth/verify_reset_token';
$route['api/auth/reset-password'] = 'api/auth/reset_password';

$route['api/booking/availability'] = 'api/booking/check_availability';
$route['api/booking/get_availability'] = 'api/booking/get_availability';
$route['api/booking/create'] = 'api/booking/create';
$route['api/booking/my-bookings'] = 'api/booking/my_bookings';
$route['api/booking/calculate'] = 'api/booking/calculate_total';
$route['api/booking/room/(:num)'] = 'api/booking/get_room/$1';
$route['api/booking/room-code/(:any)'] = 'api/booking/get_room_by_code/$1';
$route['api/booking/number/(:any)'] = 'api/booking/get_by_number/$1';
$route['api/booking/rooms'] = 'api/booking/get_rooms';
$route['api/booking/get_rooms'] = 'api/booking/get_rooms';

$route['api/user/profile'] = 'api/user/profile';
$route['api/user/update'] = 'api/user/update';

$route['api/inquiry/submit'] = 'api/inquiry/submit';

// Customer Dashboard routes
$route['customer/dashboard'] = 'customer/dashboard/index';
$route['customer/booking/(:any)'] = 'customer/dashboard/booking/$1';
