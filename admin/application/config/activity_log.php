<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| System Activity Logs configuration
| -------------------------------------------------------------------
|
| Controls the behaviour of the Activity_log library (admin panel) and
| the bodare_activity_log() helper (public website). Everything here
| is optional; the defaults are sensible for a live site.
|
*/

$config['activity_log'] = array(

    /*
    | Master switch. When FALSE no activity is written from either side.
    */
    'enabled' => TRUE,

    /*
    | Whether to auto-record authenticated admin page views. Turn this off
    | if you find the log growing too fast; view traffic (public site and
    | admin) can be disabled independently.
    */
    'log_admin_page_views' => TRUE,

    /*
    | Whether to auto-record public website page views (guest/anon traffic).
    */
    'log_public_page_views' => TRUE,

    /*
    | Comma-separated regex fragments (matched against the request URI path)
    | that are never logged as page views. Useful to skip assets, health
    | checks, search-engine bots, and noisy endpoints.
    */
    'ignore_paths' => 'assets/|css/|js/|img/|fonts/|favicon|robots\.txt|\.ico|\.png|\.jpg|\.css|\.js|check_production|test_api|manifest\.json|sw\.js|analytics_data|inquiries/poll|calendar/feed|calendar/summary|rooms/get_availability|rooms/calendar|get_availability_data|poll',

    /*
    | How many days of activity to keep before the automatic retention
    | purger (run by the cron job) deletes older rows. Set NULL/0 to keep
    | everything (manual cleanup only).
    */
    'retention_days' => 90,

    /*
    | Maximum characters to store for description, request_url and
    | user_agent columns. Keeps rows from silently growing unbounded.
    */
    'max_description_length' => 1000,
    'max_url_length' => 500,
    'max_user_agent_length' => 255,
);
