<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| Align MySQL session timezone with Asia/Manila for all admin/API requests.
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/
$hook['post_controller_constructor'][] = array(
	'class'    => 'Timezone',
	'function' => 'apply_mysql',
	'filename' => 'Timezone.php',
	'filepath' => 'hooks',
	'params'   => array()
);
