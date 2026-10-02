<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH . 'core/Admin_Controller.php');
}

/**
 * Admin tool: website identity, SEO, logo, address, contact info and social media.
 * Values are stored in `site_settings` and read by the public site via includes/site-config.php.
 */
class Site_settings extends Admin_Controller {

    /** Upload folder relative to the admin front controller (FCPATH = admin/). */
    const UPLOAD_DIR = 'uploads/site/';

    private $text_fields = array(
        'site_name' => 150,
        'site_short_name' => 50,
        'legal_name' => 200,
        'tagline' => 255,
        'about_text' => 1000,
        'meta_title' => 120,
        'meta_description' => 320,
        'meta_keywords' => 1000,
        'meta_tags' => 1000,
        'street_address' => 255,
        'address_locality' => 100,
        'address_region' => 100,
        'postal_code' => 20,
        'address_country' => 2,
        'contact_phone' => 50,
        'contact_phone_e164' => 20,
        'contact_phone_alt' => 50,
        'business_hours' => 255
    );

    private $url_fields = array(
        'map_url' => 'Google Maps link',
        'facebook_url' => 'Facebook',
        'messenger_url' => 'Messenger',
        'instagram_url' => 'Instagram',
        'tiktok_url' => 'TikTok',
        'youtube_url' => 'YouTube',
        'x_url' => 'X (Twitter)',
        'linkedin_url' => 'LinkedIn'
    );

    private $image_fields = array(
        'logo_path' => array('input' => 'logo', 'label' => 'Logo', 'max_kb' => 2048),
        'og_image_path' => array('input' => 'og_image', 'label' => 'Social share image', 'max_kb' => 5120)
    );

    public function __construct() {
        parent::__construct();
        $this->load->model('Site_settings_model');
    }

    /**
     * Super Admin always passes, even before the permission SQL is applied.
     */
    private function can($permission_slug) {
        return $this->is_super_admin() || $this->has_permission($permission_slug);
    }

    private function require_can($permission_slug, $redirect_url = 'dashboard') {
        if (!$this->can($permission_slug)) {
            $this->session->set_flashdata('error', 'You do not have permission to access this page.');
            redirect($redirect_url);
        }
    }

    public function index() {
        if (!$this->can('manage_site_settings')) {
            $this->require_can('view_site_settings');
        }

        $data['title'] = 'Site Settings';
        $data['can_manage'] = $this->can('manage_site_settings');
        $data['table_ready'] = $this->Site_settings_model->table_exists();
        $data['settings'] = $this->Site_settings_model->get_all_settings();
        $data['public_url'] = $this->public_base_url();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/site_settings/index', $data);
        $this->load->view('admin/layout/footer');
    }

    public function update() {
        $this->require_can('manage_site_settings', 'site_settings');

        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            redirect('site_settings');
            return;
        }

        if (!$this->Site_settings_model->table_exists()) {
            $this->session->set_flashdata('error', 'The site_settings table is missing. Run admin/sql/create_site_settings_table.sql first.');
            redirect('site_settings');
            return;
        }

        $errors = array();
        $settings_data = array();

        foreach ($this->text_fields as $field => $max) {
            $value = trim(strip_tags((string) $this->input->post($field)));
            $value = preg_replace('/[ \t]+/', ' ', $value);
            if (function_exists('mb_substr')) {
                $value = mb_substr($value, 0, $max);
            } else {
                $value = substr($value, 0, $max);
            }
            $settings_data[$field] = $value;
        }

        if ($settings_data['site_name'] === '') {
            $errors[] = 'Website name is required.';
        }
        $settings_data['address_country'] = strtoupper($settings_data['address_country']);
        if ($settings_data['address_country'] !== '' && !preg_match('/^[A-Z]{2}$/', $settings_data['address_country'])) {
            $errors[] = 'Country must be a 2-letter code (for example PH).';
        }

        $settings_data['meta_keywords'] = $this->normalize_list($settings_data['meta_keywords']);
        $settings_data['meta_tags'] = $this->normalize_list($settings_data['meta_tags']);

        $email = trim((string) $this->input->post('contact_email'));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Contact email is not a valid email address.';
        }
        $settings_data['contact_email'] = $email;

        if ($settings_data['contact_phone_e164'] === '') {
            $settings_data['contact_phone_e164'] = $this->derive_e164($settings_data['contact_phone']);
        } elseif (!preg_match('/^\+[1-9][0-9]{6,14}$/', str_replace(' ', '', $settings_data['contact_phone_e164']))) {
            $errors[] = 'International phone format must look like +639505337480.';
        } else {
            $settings_data['contact_phone_e164'] = str_replace(' ', '', $settings_data['contact_phone_e164']);
        }

        foreach (array('geo_latitude' => 90, 'geo_longitude' => 180) as $field => $limit) {
            $value = trim((string) $this->input->post($field));
            if ($value !== '' && (!is_numeric($value) || abs((float) $value) > $limit)) {
                $errors[] = ($field === 'geo_latitude' ? 'Latitude' : 'Longitude') . ' must be a number between -' . $limit . ' and ' . $limit . '.';
            }
            $settings_data[$field] = $value;
        }

        foreach ($this->url_fields as $field => $label) {
            $value = trim((string) $this->input->post($field));
            if ($value !== '' && !preg_match('#^https?://#i', $value)) {
                $value = 'https://' . $value;
            }
            if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                $errors[] = $label . ' link is not a valid URL.';
            }
            $settings_data[$field] = $value;
        }

        $settings_data['seo_noindex'] = $this->input->post('seo_noindex') ? '1' : '0';
        $settings_data['google_site_verification'] = $this->verification_code($this->input->post('google_site_verification'));
        $settings_data['bing_site_verification'] = $this->verification_code($this->input->post('bing_site_verification'));

        $ga = strtoupper(trim((string) $this->input->post('google_analytics_id')));
        if ($ga !== '' && !preg_match('/^(G|GT|AW|UA)-[A-Z0-9-]{4,20}$/', $ga)) {
            $errors[] = 'Google Analytics ID must look like G-XXXXXXXXXX.';
        }
        $settings_data['google_analytics_id'] = $ga;

        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode('<br>', array_map('html_escape', $errors)));
            redirect('site_settings');
            return;
        }

        $current = $this->Site_settings_model->get_all_settings();
        $replaced_files = array();
        $new_files = array();
        foreach ($this->image_fields as $field => $meta) {
            $old = isset($current[$field]) ? (string) $current[$field] : '';
            if ($this->input->post('remove_' . $meta['input'])) {
                $settings_data[$field] = '';
                if ($old !== '') {
                    $replaced_files[] = $old;
                }
            }
            if (!empty($_FILES[$meta['input']]['name'])) {
                $upload = $this->upload_image($meta['input'], $meta['max_kb']);
                if ($upload['error'] !== '') {
                    foreach ($new_files as $new_file) {
                        $this->delete_upload($new_file);
                    }
                    $this->session->set_flashdata('error', html_escape($meta['label'] . ' upload failed: ' . $upload['error']));
                    redirect('site_settings');
                    return;
                }
                $settings_data[$field] = $upload['path'];
                $new_files[] = $upload['path'];
                if ($old !== '') {
                    $replaced_files[] = $old;
                }
            }
        }

        if ($this->Site_settings_model->update_settings($settings_data)) {
            foreach (array_unique($replaced_files) as $old_file) {
                $this->delete_upload($old_file);
            }
            $this->session->set_flashdata('success', 'Site settings updated successfully');
            if (isset($this->activity_log)) {
                $changed = array();
                foreach ($settings_data as $key => $value) {
                    $before = isset($current[$key]) ? (string) $current[$key] : '';
                    if ($before !== (string) $value) {
                        $changed[$key] = $value;
                    }
                }
                $this->activity_log->crud('site_settings', 'update', 'settings', null, 'Site settings updated', null, $changed);
            }
        } else {
            foreach ($new_files as $new_file) {
                $this->delete_upload($new_file);
            }
            $this->session->set_flashdata('error', 'Failed to update site settings');
        }
        redirect('site_settings');
    }

    private function upload_image($input, $max_kb) {
        $full_path = FCPATH . self::UPLOAD_DIR;
        if (!is_dir($full_path) && !@mkdir($full_path, 0755, true)) {
            return array('path' => '', 'error' => 'Upload folder could not be created.');
        }

        $this->load->library('upload');
        $this->upload->initialize(array(
            'upload_path' => $full_path,
            'allowed_types' => 'gif|jpg|jpeg|png|webp',
            'max_size' => $max_kb,
            'encrypt_name' => true
        ));

        if (!$this->upload->do_upload($input)) {
            return array('path' => '', 'error' => $this->upload->display_errors('', ''));
        }

        $file = $this->upload->data();
        if (@getimagesize($file['full_path']) === false) {
            @unlink($file['full_path']);
            return array('path' => '', 'error' => 'The file is not a valid image.');
        }

        return array('path' => self::UPLOAD_DIR . $file['file_name'], 'error' => '');
    }

    /**
     * Only deletes files inside the site upload folder, never paths outside it.
     */
    private function delete_upload($relative) {
        $relative = ltrim(str_replace('\\', '/', (string) $relative), '/');
        if (strpos($relative, self::UPLOAD_DIR) !== 0 || strpos($relative, '..') !== false) {
            return;
        }
        $full = FCPATH . $relative;
        if (is_file($full)) {
            @unlink($full);
        }
    }

    private function normalize_list($value) {
        $items = preg_split('/[,\r\n]+/', (string) $value);
        $clean = array();
        foreach ($items as $item) {
            $item = trim($item);
            if ($item !== '' && !in_array(strtolower($item), array_map('strtolower', $clean), true)) {
                $clean[] = $item;
            }
        }
        return implode(', ', $clean);
    }

    /**
     * Accepts either the bare code or the full <meta ... content="..."> tag pasted from the console.
     */
    private function verification_code($value) {
        $value = trim((string) $value);
        if (preg_match('/content\s*=\s*["\']([^"\']+)["\']/i', $value, $m)) {
            $value = $m[1];
        }
        return preg_replace('/[^A-Za-z0-9_\-]/', '', $value);
    }

    private function derive_e164($phone) {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return '';
        }
        if (strlen($digits) === 11 && $digits[0] === '0') {
            return '+63' . substr($digits, 1);
        }
        if (strlen($digits) === 12 && strpos($digits, '63') === 0) {
            return '+' . $digits;
        }
        if (strlen($digits) === 10 && $digits[0] === '9') {
            return '+63' . $digits;
        }
        return '';
    }

    private function public_base_url() {
        $base = rtrim(base_url(), '/');
        if (substr($base, -6) === '/admin') {
            $base = substr($base, 0, -6);
        }
        return $base;
    }
}
