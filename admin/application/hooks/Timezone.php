<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Align MySQL session timezone with Asia/Manila after the controller boots.
 */
class Timezone
{
    public function apply_mysql()
    {
        $CI =& get_instance();
        if (!isset($CI->db) || !is_object($CI->db) || empty($CI->db->conn_id)) {
            return;
        }

        static $applied = false;
        if ($applied) {
            return;
        }

        $offset = defined('BODARE_TIMEZONE_OFFSET') ? BODARE_TIMEZONE_OFFSET : '+08:00';
        $CI->db->query("SET time_zone = " . $CI->db->escape($offset));
        $applied = true;
    }
}
