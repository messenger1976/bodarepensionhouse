<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Default entry for /admin/ (empty URI).
 * CI3 default_controller cannot target subdirectory controllers (e.g. admin/auth),
 * so this root controller applies the same session gate as Auth::login().
 */
class Home extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function index() {
        if ($this->session->userdata('admin_logged_in')) {
            redirect('dashboard');
        }

        redirect('login');
    }
}
