<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        // Ensure URL helper is loaded
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('html');
    }
}

