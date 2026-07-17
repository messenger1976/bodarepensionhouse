<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MX_Controller {
    

    

    function __construct() {
        parent::__construct();
        $this->load->library('envatoapi');
    }
    
    /*****************************/
    /***** Website Index  ********/
    /*****************************/
    public function index(){        
        $data['basicinfo'] = $this->getBasicInfo();
        //$data['basicinfo'] = getBasic();
        $data['event'] = $this->getEventInfo();
        $data['events'] = $this->getEventsInfo();
        $data['speech'] = $this->getSpeeches();
        $data['section'] = $this->getSection();
        $data['pastors'] = $this->getPastors();
        $data['committee'] = $this->getCommittee();
        $data['staff'] = $this->getStaff();
        $data['prayer'] = $this->getPrayerInfo();
        $data['notice'] = $this->getNoticeInfo();
        $data['gallery'] = $this->getGalleryInfo();
        $data['slider'] = $this->getSlider();
        $data['cooperative_officers'] = $this->getCooperativeOfficers();
        $data['pagination'] = '';
        $data['purchase'] = $this->evnatoVerify();
        $this->load->view('header2', $data);
        $this->load->view('index', $data);
        $this->load->view('footer2', $data);
    }
    
    /*****************************/
    /***** Products Page  ********/
    /*****************************/
    public function products(){        
        $data['basicinfo'] = $this->getBasicInfo();
        $data['purchase'] = $this->evnatoVerify();
        $this->load->view('header2', $data);
        $this->load->view('products/products', $data);
        $this->load->view('footer2', $data);
    }
    
    /*****************************/
    /***** About Us Page  ********/
    /*****************************/
    public function about(){        
        $data['basicinfo'] = $this->getBasicInfo();
        $data['purchase'] = $this->evnatoVerify();
        $this->load->view('header2', $data);
        $this->load->view('about/about', $data);
        $this->load->view('footer2', $data);
    }
    
    /*****************************/
    /***** Contact Us Page ********/
    /*****************************/
    public function contact(){        
        $data['basicinfo'] = $this->getBasicInfo();
        $data['purchase'] = $this->evnatoVerify();
        $this->load->view('header2', $data);
        $this->load->view('contact/contact', $data);
        $this->load->view('footer2', $data);
    }
    
    /*****************************/
    /***** Get Basic Info ********/
    /*****************************/
    public function getBasicInfo(){
        $query = $this->db->get('websitebasic');
        return $query->result();
    }
    
    /*****************************/
    /***** Get Event Info ********/
    /*****************************/
    public function getEventInfo(){        
        $this->db->order_by('eventid', 'desc');
        $this->db->limit(1);
        $query = $this->db->get('event');
        return $query->result();
    }
    
    /**********************************/
    /***** Get All Events Info ********/
    /**********************************/
    public function getEventsInfo(){        
        $this->db->order_by('eventid', 'desc');
        $query = $this->db->get('event');
        return $query->result();
    }
    
    /*****************************/
    /***** Get Speech     ********/
    /*****************************/
    public function getSpeeches(){   
        $query =  $this->db->get('speech');
        return $query->result();
    }
    
    /*****************************/
    /***** Get Section    ********/
    /*****************************/
    public function getSection(){   
        $this->db->where('status = 1');
        $this->db->order_by('serialid', 'asc');
        $query =  $this->db->get('section');
        return $query->result();
    }
    
    /*****************************/
    /***** Get Pastor Info ********/
    /*****************************/
    public function getPastors(){ 
        $this->db->limit(4);
        $query = $this->db->get('pastor');
        return $query->result();
    }
    
    /*****************************/
    /***** Get Committee Info ********/
    /*****************************/
    public function getCommittee(){ 
        $this->db->limit(4);
        $query = $this->db->get('committee');
        return $query->result();
    }

    /*****************************/
    /***** Get Staff Info ********/
    /*****************************/
    public function getStaff(){ 
        $this->db->limit(4);
        $query = $this->db->get('staff');
        return $query->result();
    }
    
    /*****************************/
    /***** Get Payer Info ********/
    /*****************************/
    public function getPrayerInfo(){ 
        $query = $this->db->get('prayer');
        return $query->result();
    }
    
    /*****************************/
    /***** Get Notice Info ********/
    /*****************************/
    public function getNoticeInfo(){ 
        $query = $this->db->get('notice');
        return $query->result();
    }
    
    /*****************************/
    /***** Get Gallery Info ********/
    /*****************************/
    public function getGalleryInfo(){ 
        $this->db->limit(6);
        $query = $this->db->get('gallery');
        return $query->result();
    }
    
    /*****************************/
    /***** Get Gallery Info ********/
    /*****************************/
    public function getSlider(){ 
        $this->db->order_by('serialid', 'asc');
        $query = $this->db->get('slider');
        return $query->result();
    }
        /*****************************/
    /***** Get Cooperative Officers Info ********/
    /*****************************/
    public function getCooperativeOfficers(){ 
        $query = $this->db->get('cooperative_officers');
        $officers = $query->result();
        
        // Group officers by department
        $grouped = array();
        foreach($officers as $officer) {
            $dept = !empty($officer->department) ? $officer->department : 'Unassigned';
            if(!isset($grouped[$dept])) {
                $grouped[$dept] = array();
            }
            $grouped[$dept][] = $officer;
        }
        
        return $grouped;
    }
        
    /*****************************/
    /***** Contact Form Submit ***/
    /*****************************/
    public function contactWithUs(){
        $redirect_to = $this->input->post('redirect_to');
        if ($redirect_to !== 'home' && $redirect_to !== 'home/contact') {
            $redirect_to = 'home/contact';
        }

        $this->form_validation->set_rules('name', 'Name', 'trim|required|max_length[150]');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[255]');
        $this->form_validation->set_rules('subject', 'Subject', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('body', 'Message', 'trim|required|max_length[5000]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('contact_error', strip_tags(validation_errors()));
            $this->contactRedirect($redirect_to);
            return;
        }

        $name = $this->security->xss_clean($this->input->post('name'));
        $email = $this->security->xss_clean($this->input->post('email'));
        $subject = $this->security->xss_clean($this->input->post('subject'));
        $body = trim(strip_tags($this->input->post('body')));
        $now = date('Y-m-d H:i:s');

        $inquiry = array(
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $body,
            'status' => 'new',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
            'cdate' => date('j F Y'),
            'created_at' => $now,
            'updated_at' => $now,
        );

        $inserted = $this->db->insert('inquiry', $inquiry);
        if (!$inserted) {
            $this->session->set_flashdata('contact_error', 'Sorry, we could not save your message. Please try again.');
            $this->contactRedirect($redirect_to);
            return;
        }

        $inquiryid = $this->db->insert_id();
        $info = $this->getBasicInfo();
        $toEmail = !empty($info[0]->email) ? $info[0]->email : '';
        $siteName = !empty($info[0]->title) ? $info[0]->title : 'BODARE & COMMUNITY MPC';

        $this->load->library('coop_mail');
        $this->load->library('coop_imap');
        $this->coop_mail->set_profile('contact');
        $contactSettings = $this->coop_mail->get_settings('contact');
        $contactMailbox = ($contactSettings && !empty($contactSettings->from_email)) ? $contactSettings->from_email : '';
        $mailHeaders = array('X-BODARE-Inquiry-ID' => (string) $inquiryid);
        // Best-effort notifications; inquiry is already saved for admin monitoring.
        $mailTimeout = 8;
        if ($toEmail) {
            $notifyHtml = '
                <div style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:640px;margin:0 auto;">
                    <h2 style="color:#02245b;margin-bottom:8px;">New Contact Inquiry</h2>
                    <p>A guest submitted a message through the Contact Us form.</p>
                    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
                        <tr><td style="padding:6px 0;font-weight:bold;width:110px;">Name</td><td>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="padding:6px 0;font-weight:bold;">Email</td><td>' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="padding:6px 0;font-weight:bold;">Subject</td><td>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="padding:6px 0;font-weight:bold;vertical-align:top;">Message</td><td>' . nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')) . '</td></tr>
                        <tr><td style="padding:6px 0;font-weight:bold;">Inquiry #</td><td>' . (int) $inquiryid . '</td></tr>
                    </table>
                    <p style="color:#666;font-size:13px;">Reply from the admin panel: Dashboard &rarr; Inquiries.</p>
                </div>
            ';
            @$this->coop_mail->send(
                $toEmail,
                Coop_imap::tagged_subject($inquiryid, 'New Inquiry: ' . $subject),
                $notifyHtml,
                NULL,
                NULL,
                $email,
                $name,
                $mailTimeout,
                $mailHeaders
            );
        }

        $ackHtml = '
            <div style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:640px;margin:0 auto;">
                <h2 style="color:#02245b;margin-bottom:8px;">We received your message</h2>
                <p>Hi ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>
                <p>Thank you for contacting <strong>' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '</strong>. We have received your inquiry and will get back to you soon.</p>
                <p><strong>Subject:</strong> ' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</p>
                <p style="color:#666;font-size:13px;">You can reply to this email if you need to add more details. Please keep the subject line so we can match your message.</p>
            </div>
        ';
        @$this->coop_mail->send(
            $email,
            Coop_imap::tagged_subject($inquiryid, 'We received your message: ' . $subject),
            $ackHtml,
            NULL,
            NULL,
            $contactMailbox,
            $siteName,
            $mailTimeout,
            $mailHeaders
        );

        $this->session->set_flashdata('contact_success', 'Thank you! Your message has been sent successfully. We will get back to you soon.');
        $this->contactRedirect($redirect_to);
    }

    protected function contactRedirect($redirect_to) {
        if ($redirect_to === 'home') {
            $this->session->set_flashdata('contact_scroll', '1');
            redirect('', 'refresh');
            return;
        }

        redirect($redirect_to, 'refresh');
    }
    
    
    public function evnatoVerify(){
        $purchaseCode = $this->getBasicInfo()[0]->verify;
        $o = $this->envatoapi->verifyPurchase($purchaseCode);
        if ( is_object($o) ) {
            return true;
        }else {
            return false;
        }
    }
    
}