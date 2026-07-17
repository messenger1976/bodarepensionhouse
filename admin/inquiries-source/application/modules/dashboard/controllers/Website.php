<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Website extends MX_Controller {
    
    

	function __construct() {
		parent::__construct();
		
		$logged_in = $this->session->userdata('logged_in');		
		$user_position = $this->session->userdata('user_position');		
		if(!$logged_in){
			redirect('access/login', 'refresh');
		}elseif(!in_array($user_position, array('Admin', 'Super Admin')) ){
			redirect('dashboard/index', 'refresh');
		}
		
		$language = $this->session->userdata('lang');		
		$this->lang->load('dashboard', $language);
		$this->load->library('coop_access');
		$this->coop_access->requireAnyRole(array('Super Admin', 'Admin'));
	}
	
	/*****************************/
	/***** Website Index Page *****/
	/*****************************/
	public function index(){
		$this->load->view('Dashboard/header');
		$this->load->view('Website/basic');
		$this->load->view('Dashboard/footer');
	}
	
	
	/*****************************/
	/***** Website Header *****/
	/*****************************/
	public function header(){
		$data['website'] = $this->getBasicInfo();
		$this->load->view('Dashboard/header');
		$this->load->view('Website/basic', $data);
		$this->load->view('Dashboard/footer');
	}
	
	/*****************************/
	/***** Getting Website Basic Info *****/
	/*****************************/
	public function getBasicInfo(){
		$query = $this->db->get('websitebasic');
		return $query->result();
	}

	/*****************************/
	/***** Email/SMTP Settings ***/
	/*****************************/
	protected function emailProfile($value){
		return ($value === 'account') ? 'account' : 'contact';
	}

	public function emailsettings(){
		$profile = $this->emailProfile($this->input->get('profile', TRUE));
		$query = $this->db->get_where('email_smtp_settings', array('profile' => $profile), 1);
		$data['email_settings'] = $query->row();
		$data['email_profile'] = $profile;
		$this->load->view('Dashboard/header');
		$this->load->view('Website/emailsettings', $data);
		$this->load->view('Dashboard/footer');
	}

	public function updateemailsettings(){
		$profile = $this->emailProfile($this->input->post('profile', TRUE));
		$settings_url = 'dashboard/website/emailsettings?profile=' . $profile;

		$this->load->library('form_validation');
		$this->form_validation->set_rules('smtp_host', 'SMTP Host', 'required|trim');
		$this->form_validation->set_rules('smtp_port', 'SMTP Port', 'required|integer|greater_than[0]|less_than_equal_to[65535]');
		$this->form_validation->set_rules('smtp_user', 'SMTP Username', 'required|trim');
		$this->form_validation->set_rules('from_email', 'From Email', 'required|valid_email|trim');
		$this->form_validation->set_rules('from_name', 'From Name', 'required|trim');

		if (!$this->form_validation->run()) {
			$this->session->set_flashdata('notsuccess', strip_tags(validation_errors(' ', ' ')));
			redirect($settings_url, 'refresh');
			return;
		}

		$crypto = $this->input->post('smtp_crypto', TRUE);
		$mailtype = $this->input->post('mailtype', TRUE);
		if (!in_array($crypto, array('', 'tls', 'ssl'), TRUE)) {
			$crypto = 'tls';
		}
		if (!in_array($mailtype, array('html', 'text'), TRUE)) {
			$mailtype = 'html';
		}

		$data = array(
			'protocol' => 'smtp',
			'smtp_host' => trim($this->input->post('smtp_host', TRUE)),
			'smtp_port' => (int) $this->input->post('smtp_port'),
			'smtp_user' => trim($this->input->post('smtp_user', TRUE)),
			'smtp_crypto' => $crypto,
			'smtp_timeout' => max(1, (int) $this->input->post('smtp_timeout')),
			'from_email' => trim($this->input->post('from_email', TRUE)),
			'from_name' => trim($this->input->post('from_name', TRUE)),
			'mailtype' => $mailtype,
			'charset' => 'utf-8',
			'newline' => "\r\n",
			'crlf' => "\r\n",
			'is_active' => $this->input->post('is_active') ? 1 : 0,
			'updated_at' => date('Y-m-d H:i:s')
		);

		if ($profile === 'contact' && $this->db->field_exists('imap_enabled', 'email_smtp_settings')) {
			$imapHost = trim($this->input->post('imap_host', TRUE));
			$imapCrypto = $this->input->post('imap_crypto', TRUE);
			$data['imap_host'] = $imapHost !== '' ? $imapHost : $data['smtp_host'];
			$data['imap_port'] = max(1, (int) $this->input->post('imap_port'));
			$data['imap_crypto'] = in_array($imapCrypto, array('', 'tls', 'ssl'), TRUE) ? ($imapCrypto !== '' ? $imapCrypto : 'ssl') : 'ssl';
			$data['imap_enabled'] = $this->input->post('imap_enabled') ? 1 : 0;
		}

		$password = (string) $this->input->post('smtp_pass', FALSE);
		$exists = $this->db->where('profile', $profile)->count_all_results('email_smtp_settings') > 0;
		if (!$exists && $password === '') {
			$this->session->set_flashdata('notsuccess', 'SMTP Password is required when creating the settings.');
			redirect($settings_url, 'refresh');
			return;
		}
		if ($password !== '') {
			$this->load->library('coop_mail');
			$data['smtp_pass'] = $this->coop_mail->encrypt_password($password);
		}

		if ($exists) {
			$this->db->where('profile', $profile);
			$saved = $this->db->update('email_smtp_settings', $data);
		} else {
			$data['id'] = ($profile === 'account') ? 2 : 1;
			$data['profile'] = $profile;
			$data['created_at'] = date('Y-m-d H:i:s');
			$saved = $this->db->insert('email_smtp_settings', $data);
		}

		$this->session->set_flashdata(
			$saved ? 'success' : 'notsuccess',
			$saved ? 'Email/SMTP settings saved successfully.' : 'Unable to save Email/SMTP settings.'
		);
		redirect($settings_url, 'refresh');
	}

	public function testemail(){
		$profile = $this->emailProfile($this->input->post('profile', TRUE));
		$settings_url = 'dashboard/website/emailsettings?profile=' . $profile;

		$this->load->library('form_validation');
		$this->form_validation->set_rules('test_email', 'Test Email', 'required|valid_email|trim');

		if (!$this->form_validation->run()) {
			$this->session->set_flashdata('notsuccess', strip_tags(validation_errors(' ', ' ')));
			redirect($settings_url, 'refresh');
			return;
		}

		$to = trim($this->input->post('test_email', TRUE));
		$this->load->library('coop_mail');
		$this->coop_mail->set_profile($profile);

		if ($this->coop_mail->send_test($to)) {
			$this->session->set_flashdata('success', 'Test email sent successfully to ' . $to . '. Check the inbox (and spam folder).');
		} else {
			$error = $this->coop_mail->get_last_error();
			$this->session->set_flashdata('notsuccess', 'Test email failed. ' . $error);
		}

		redirect($settings_url, 'refresh');
	}
	
	/*****************************/
	/***** Website Slider *****/
	/*****************************/
	public function slider(){
        $data['slider'] = $this->getSlider();
		$this->load->view('Dashboard/header2');
		$this->load->view('Website/slider', $data);
		$this->load->view('Dashboard/footer2');
	}
	
	        
	/*****************************/
	/***** Website Gallery *****/
	/*****************************/
	public function gallery(){
                $data['gallery'] = $this->getGallery();
		$this->load->view('Dashboard/header');
		$this->load->view('Website/gallery', $data);
		$this->load->view('Dashboard/footer');
	}
        
	/*****************************/
	/***** Website Footer *****/
	/*****************************/
	public function footer(){
		$this->load->view('Dashboard/header');
		$this->load->view('Website/basic');
		$this->load->view('Dashboard/footer');
	}
	
	
	/*****************************/
	/***** Website Save Basic Info *****/
	/*****************************/
	public function updatebasic(){	
			
		$errors = array();			
		$success = array();			
		$data = array();
		
		$title = $this->input->post('title');
		$tag = $this->input->post('tag');
		$map = $this->input->post('map');
		$mapapi = $this->input->post('mapapi');
		$email = $this->input->post('email');
		$color = $this->input->post('color');
		$currency = $this->input->post('currency');
		$churchtime = $this->input->post('churchtime');
		$about = $this->input->post('about');
		$contact = $this->input->post('contact');
		$address = $this->input->post('address');
		$city = $this->input->post('city');
		$country = $this->input->post('country');
		$postal = $this->input->post('postal');
		$copyright = $this->input->post('copyright');

                $facebook = $this->input->post('facebook');
                $twitter = $this->input->post('twitter');
                $googleplus = $this->input->post('googleplus');
                $linkedin = $this->input->post('linkedin');
                $youtube = $this->input->post('youtube');
                $pinterest = $this->input->post('pinterest');
                $instagram = $this->input->post('instagram');
                $whatsapp = $this->input->post('whatsapp');
		
		if (!empty($title)){$data['title'] = $title;}			
		if (!empty($tag)){$data['tag'] = $tag;}			
		if (!empty($map)){$data['map'] = $map;}
		if (!empty($mapapi)){$data['mapapi'] = $mapapi;}
		if (!empty($color)){$data['color'] = $color;}
		if (!empty($email)){$data['email'] = $email;}
		if (!empty($currency)){$data['currency'] = $currency;}	
		if (!empty($churchtime)){$data['churchtime'] = $churchtime;}			
		if (!empty($about)){$data['about'] = $about;}			
		if (!empty($contact)){$data['contact'] = $contact;}			
		if (!empty($address)){$data['address'] = $address;}			
		if (!empty($city)){$data['city'] = $city;}			
		if (!empty($country)){$data['country'] = $country;}			
		if (!empty($postal)){$data['postal'] = $postal;}			
		if (!empty($copyright)){$data['copyright'] = $copyright;}
                
		if (!empty($facebook)){$data['facebook'] = $facebook;}
		if (!empty($twitter)){$data['twitter'] = $twitter;}
		if (!empty($googleplus)){$data['googleplus'] = $googleplus;}
		if (!empty($linkedin)){$data['linkedin'] = $linkedin;}
		if (!empty($youtube)){$data['youtube'] = $youtube;}
		if (!empty($pinterest)){$data['pinterest'] = $pinterest;}
		if (!empty($instagram)){$data['instagram'] = $instagram;}
		if (!empty($whatsapp)){$data['whatsapp'] = $whatsapp;}
		
		$imagePath = realpath(APPPATH . '../images/website');
		$favicon = $_FILES['favicon']['tmp_name'];
		$logo = $_FILES['logo']['tmp_name'];
		
		if($favicon !== ""){
			$config['upload_path'] = $imagePath; 
			$config['allowed_types'] = 'jpg|png|jpeg|gif';	
//			$config['max_size']     = '200';
//			$config['max_width'] = '500';
//			$config['max_height'] = '500';					
			$config['file_name'] = date('Ymd_his_').rand(10,99).rand(10,99).rand(10,99);
			$this->load->library('upload', $config);
			if ($this->upload->do_upload('favicon')){				
				$uploaded_data = $this->upload->data();				
				$data['favicon']= $uploaded_data['file_name'];									
			}else{				
				$data['favicon']= '';	
				$errors['favicon_error'] = strip_tags($this->upload->display_errors());
				echo json_encode($errors);
			}
		}
		
		if($logo !== ""){
			$config['upload_path'] = $imagePath; 								
			$config['allowed_types'] = 'jpg|png|jpeg|gif';
//			$config['max_size']     = '5000';
//			$config['max_width'] = '500';
//			$config['max_height'] = '500';	
			$config['file_name'] = date('Ymd_his_').rand(10,99).rand(10,99).rand(10,99);				
			$this->load->library('upload', $config);				
			if ($this->upload->do_upload('logo')){				
				$uploaded_data = $this->upload->data();				
				$data['logo']= $uploaded_data['file_name'];					
			} else {				
				$data['logo']= '';		
				$errors['logo_error'] = strip_tags($this->upload->display_errors());
				echo json_encode($errors);
			}
		}
		
		$this->db->where('basicid', 1); 
		$updated = $this->db->update('websitebasic', $data); 
		if($updated == TRUE){
			$succcess['success'] = "Successfully Updated";
			echo json_encode($succcess);
		}else{
			$errors['notsuccess'] = 'Opps! Something Wrong';					
			echo json_encode($errors);
		}
	}
	
	/*****************************/
	/***** Website Upload Gallery *****/
	/*****************************/
	public function uploadgallery(){ 
	
		$this->load->library('upload');		
		$data = array();
		$files = $_FILES;
		$count = count($_FILES['userfile']['name']);
		$title = $this->input->post('title');
		if (!empty($title)){$data['title'] = $title;}

		for($i=0; $i<$count; $i++){
		
			$_FILES['userfile']['name']= $files['userfile']['name'][$i];
			$_FILES['userfile']['type']= $files['userfile']['type'][$i];
			$_FILES['userfile']['tmp_name']= $files['userfile']['tmp_name'][$i];
			$_FILES['userfile']['error']= $files['userfile']['error'][$i];
			$_FILES['userfile']['size']= $files['userfile']['size'][$i];  
			
			$imagePath = realpath(APPPATH . '../images/website/gallery');			
			$config['upload_path'] = $imagePath . "/large";
			$config['allowed_types'] = 'gif|jpg|png|jpeg';
			$config['file_name'] = date('Ymd_his_').rand(10,99).rand(10,99).rand(10,99);
			$this->upload->initialize($config);
			
			if($this->upload->do_upload()){
				$fileData = $this->upload->data();
				$data['filename'] = $fileData['file_name'];
				$data['cdate'] = date("j F Y");
				$this->db->insert('gallery', $data);
				
				$config1['source_image'] = $fileData['full_path'];
				$config1['new_image'] = $imagePath . '/small';
				$config1['maintain_ratio'] = FALSE;
				$config1['width'] = 400;
				$config1['height'] = 500;

				$this->image_lib->clear();
				$this->image_lib->initialize($config1);
				$this->image_lib->resize();
                                
				redirect('dashboard/website/gallery', 'refresh');	
			}else{
				echo strip_tags($this->upload->display_errors());
			}
			
		}
	}
        
        /*****************************/
	/***** Website get Slider *****/
	/*****************************/
        public function getSlider($sliderid=''){
			if($sliderid!=''){
				//$sliderid = $this->uri->segment(4);
				$this->db->order_by('serialid', 'asc');
        		$query = $this->db->get_where('slider', array('sliderid' => $sliderid));
			}else{
				$this->db->order_by('serialid', 'asc');
            	$query = $this->db->get('slider');
			}
			
            
            return $query->result();
        }
        
        /*****************************/
	/***** Website get Slider *****/
	/*****************************/
        public function getGallery($galleryid=''){
			if($galleryid!=''){
				$this->db->order_by('serialid', 'asc');
				$query = $this->db->get_where('gallery', array('galleryid' => $galleryid));
			}else{
				$this->db->order_by('serialid', 'asc');
            	$query = $this->db->get('gallery');
			}
            
            return $query->result();
        }
        
        /*****************************/
	/***** Website Slider Delete *****/
	/*****************************/
        public function sliderdelete($sliderid){
            $this->db->where('sliderid', $sliderid);
            $this->db->delete('slider');
            redirect('dashboard/website/slider', 'refresh');
        }

		/*     * ************************** */
		/*     * *** Website Slider Edit **** */
		/*     * ************************** */
		public function slideredit() {
			$sliderid = $this->uri->segment(4);
			$data['slider'] = $this->getSlider($sliderid);
			$this->load->view('Dashboard/header');
			$this->load->view('Website/slideredit', $data);
			$this->load->view('Dashboard/footer');
		}

        
        /*     * ************************** */
        /*     * *** Sort Section      **** */
        /*     * ************************** */

        public function slidersort() {
            $sorted = $this->input->post('sort');
            $data = json_decode($sorted, TRUE);
            $counted = count($data[0]);
            for($x=0; $x < $counted; $x++){
                $sliderid = $data[0][$x]["id"];
                $arrdata = array();
                $arrdata['serialid'] = $x;
                $this->db->where('sliderid', $sliderid);
                $this->db->update('slider', $arrdata);
            }
        }
        
        /*     * ************************** */
		/*     * *** Website Slider Edit **** */
		/*     * ************************** */
		public function galleryedit() {
			$galleryid = $this->uri->segment(4);
			$data['gallery'] = $this->getGallery($galleryid);
			$this->load->view('Dashboard/header');
			$this->load->view('Website/galleryedit', $data);
			$this->load->view('Dashboard/footer');
		}

        /*****************************/
	/***** Website Gallery Delete *****/
	/*****************************/
        public function gallerydelete($galleryid){
            $this->db->where('galleryid', $galleryid);
            $this->db->delete('gallery');
            redirect('dashboard/website/gallery', 'refresh');
        }
        
        
        /*     * ************************** */
        /*     * *** Sort Section      **** */
        /*     * ************************** */

        public function sortgallery() {
            $sorted = $this->input->post('sort');
            $data = json_decode($sorted, TRUE);
            $counted = count($data[0]);
            for($x=0; $x < $counted; $x++){
                $galleryid = $data[0][$x]["id"];
                $arrdata = array();
                $arrdata['serialid'] = $x;
                $this->db->where('galleryid', $galleryid);
                $this->db->update('gallery', $arrdata);
            }
        }
	
	/*****************************/
	/***** Website Upload Slider *****/
	/*****************************/
	public function uploadslider(){ 
	
		$this->load->library('upload');		
		$data = array();
		$subtitle = $this->input->post('subtitle');
		$content = $this->input->post('content');
		$button_text = $this->input->post('button_text');
		$button_link = $this->input->post('button_link');
		if (!empty($subtitle)) { $data['subtitle'] = $subtitle; }
		if (!empty($content)) { $data['content'] = $content; }
		if (!empty($button_text)) { $data['button_text'] = $button_text; }
		if (!empty($button_link)) { $data['button_link'] = $button_link; }

		$files = $_FILES;
		$count = count($_FILES['userfile']['name']);
		
		for($i=0; $i<$count; $i++){
		
			$_FILES['userfile']['name']= $files['userfile']['name'][$i];
			$_FILES['userfile']['type']= $files['userfile']['type'][$i];
			$_FILES['userfile']['tmp_name']= $files['userfile']['tmp_name'][$i];
			$_FILES['userfile']['error']= $files['userfile']['error'][$i];
			$_FILES['userfile']['size']= $files['userfile']['size'][$i];  
			$imagePath = realpath(APPPATH . '../images/website/slider');			
			$config['upload_path'] = $imagePath;
			$config['allowed_types'] = 'gif|jpg|png|jpeg';
			$config['file_name'] = date('Ymd_his_').rand(10,99).rand(10,99).rand(10,99);
			$this->upload->initialize($config);
			
			if($this->upload->do_upload()){
				$fileData = $this->upload->data();
				
				$config['source_image'] = $fileData['full_path'];
				$config['new_image'] = $imagePath.'/resize';
				$config['maintain_ratio'] = FALSE;
				$config['width'] = 1920;
				$config['height'] = 1080;
				
				$this->image_lib->clear();
				$this->image_lib->initialize($config);
				$this->image_lib->resize();
				
				$data['filename'] = $fileData['file_name'];
				$data['cdate'] = date("j F Y");
				$this->db->insert('slider', $data);
				$this->session->set_flashdata('success', 'Successfully New Record Added');
				redirect('dashboard/website/slider', 'refresh');	
			}else{
				echo strip_tags($this->upload->display_errors());
			}
			
		}
	}

	/*****************************/
	/***** Website Edit Slider *****/
	/*****************************/
	public function editslider(){ 
	
		$errors = array();			
		$success = array();			
		$data = array();
		$sliderid = $this->input->post('sliderid');
		$filename = $this->input->post('filename');
		$subtitle = $this->input->post('subtitle');
		$content = $this->input->post('content');
		$button_text = $this->input->post('button_text');
		$button_link = $this->input->post('button_link');

		if (!empty($subtitle)){$data['subtitle'] = $subtitle;}
		if (!empty($content)){$data['content'] = $content;}
		if (!empty($button_text)){$data['button_text'] = $button_text;}
		if (!empty($button_link)){$data['button_link'] = $button_link;}


		$imagePath = realpath(APPPATH . '../images/website/slider');
		
		if(isset($_FILES['sliderimage']['tmp_name']) && $_FILES['sliderimage']['tmp_name'] !== ""){
			$config['upload_path'] = $imagePath; 
			$config['allowed_types'] = 'jpg|png|jpeg|gif';	
			
			if (!empty($filename)){
				$filename_array = explode('.',$filename);
				$config['file_name'] = $filename_array[0];
			}else{
				$config['file_name'] = date('Ymd_his_').rand(10,99).rand(10,99).rand(10,99);
			}
			$this->load->library('upload', $config);
			if ($this->upload->do_upload('sliderimage')){	

				$uploaded_data = $this->upload->data();		
				$config1['source_image'] = $uploaded_data['full_path'];
				$config1['new_image'] = $imagePath.'/resize';
				$config1['maintain_ratio'] = FALSE;
				$config1['width'] = 1920;
				$config1['height'] = 1080;
				
				$this->image_lib->clear();
				$this->image_lib->initialize($config1);
				if ( ! $this->image_lib->resize()){
					@unlink($uploaded_data['full_path']);
					$errors['slider_error'] = 'Failed to create resized image.';
					echo json_encode($errors);
					return;
				}
				if (!empty($filename) && $uploaded_data['file_name'] !== $filename){
					@unlink($imagePath.'/'.$filename);
					@unlink($imagePath.'/resize/'.$filename);
				}
				$data['filename']= $uploaded_data['file_name'];									
			}else{				
				$errors['slider_error'] = strip_tags($this->upload->display_errors());
				echo json_encode($errors);
				return;
			}
		}
		$this->db->where('sliderid', $sliderid); 
		$updated = $this->db->update('slider', $data); 
		if($updated == TRUE){
			$success['success'] = "Successfully Updated";
			echo json_encode($success);
		}else{
			$errors['notsuccess'] = 'Opps! Something Wrong';					
			echo json_encode($errors);
		}


	}
	
	/*****************************/
	/***** Website Edit Gallery *****/
	/*****************************/
	public function editgallery(){ 
	
		$errors = array();			
		$success = array();			
		$data = array();
		$galleryid = $this->input->post('galleryid');
		$filename = $this->input->post('filename');
		$title = $this->input->post('title');
		
		if (!empty($title)){$data['title'] = $title;}
		

		$imagePath = realpath(APPPATH . '../images/website/gallery');
		$galleryimage = $_FILES['galleryimage']['tmp_name'];
		
		
		if($galleryimage !== ""){
			$config['upload_path'] = $imagePath.'/large'; 
			$config['allowed_types'] = 'jpg|png|jpeg|gif';	
//			$config['max_size']     = '200';
//			$config['max_width'] = '500';
//			$config['max_height'] = '500';		
			//$config['maintain_ratio'] = FALSE;
			//$config['width'] = 1920;
			//$config['height'] = 1080;
				
			//$this->image_lib->clear();
			//$this->image_lib->initialize($config);
			//$this->image_lib->resize();			
			//$config['file_name'] = date('Ymd_his_').rand(10,99).rand(10,99).rand(10,99);
			
			if (!empty($filename)){
				@unlink($imagePath.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$filename);
				@unlink($imagePath.DIRECTORY_SEPARATOR.'small'.DIRECTORY_SEPARATOR.$filename);
				$filename_array = explode('.',$filename);
				$config['file_name'] =$filename_array[0];
			}else{
				$config['file_name'] = date('Ymd_his_').rand(10,99).rand(10,99).rand(10,99);
			}
			//$config['file_name'] = date('Ymd_his_').rand(10,99).rand(10,99).rand(10,99);
			$this->load->library('upload', $config);
			if ($this->upload->do_upload('galleryimage')){	

				$uploaded_data = $this->upload->data();		
				$config1['source_image'] = $uploaded_data['full_path'];
				$config1['new_image'] = $imagePath.'/small';
				$config1['maintain_ratio'] = FALSE;
				$config1['width'] = 400;
				$config1['height'] = 500;
				
				$this->image_lib->clear();
				$this->image_lib->initialize($config1);
				$this->image_lib->resize();		
				$data['filename']= $uploaded_data['file_name'];									
			}else{				
				$data['filename']= '';	
				$errors['slider_error'] = strip_tags($this->upload->display_errors());
				echo json_encode($errors);
			}
		}
		$this->db->where('galleryid', $galleryid); 
		$updated = $this->db->update('gallery', $data); 
		if($updated == TRUE){
			$success['success'] = "Successfully Updated";
			echo json_encode($success);
		}else{
			$errors['notsuccess'] = 'Opps! Something Wrong';					
			echo json_encode($errors);
		}


	}
}