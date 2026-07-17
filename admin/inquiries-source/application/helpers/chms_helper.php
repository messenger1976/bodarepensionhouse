<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');



if (!function_exists('getBasic')) {

    /*     * ************************** */
    /*     * **** Get Basic ***** */
    /*     * ************************** */

    function getBasic() {
        $ci = & get_instance(); //get main CodeIgniter object		
        $ci->load->database(); //load databse library
        $query = $ci->db->get('websitebasic');
        if ($query->num_rows() > 0) {
            $result = $query->result()[0];
            return $result;
        } else {
            //Default Currency USD Because No Row Founds In Table
            return false;
        }
    }

}


if (!function_exists('globalCurrency')) {

    /*     * ************************** */
    /*     * **** Global Currency ***** */
    /*     * ************************** */

    function globalCurrency() {
        $ci = & get_instance(); //get main CodeIgniter object		
        $ci->load->database(); //load databse library
        $query = $ci->db->get('websitebasic');
        if ($query->num_rows() > 0) {
            $result = $query->result();
            foreach ($result as $row) {
                $currency = $row->currency . " ";
            }
            return $currency;
        } else {
            //Default Currency USD Because No Row Founds In Table
            return false;
        }
    }

}


if (!function_exists('getCreateDate')) {

    /*     * ************************** */
    /*     * **** Get Table Create Date ***** */
    /*     * ************************** */

    function getCreateDate($orderby, $table) {
        $ci = & get_instance(); //get main CodeIgniter object		
        $ci->load->database(); //load databse library
        $ci->db->order_by($orderby, "desc");
        $ci->db->limit(1);
        $query = $ci->db->get($table);

//                $result = $query;
//                return $result;

        if ($query->num_rows() > 0) {
            $result = $query->result();
            foreach ($result as $row) {
                $cdate = $row->cdate;
            }
            return $cdate;
        } else {
            //Default Currency USD Because No Row Founds In Table
            return false;
        }
    }

}



if (!function_exists('validPurchase')) {

    /*     * ************************** */
    /*     * **** Item Purchase Validation ***** */
    /*     * ************************** */

    function validPurchase($purchase_key) {
        $username = 'princejohn25';
        $api_key = '9qzsnpfp5lqjy8k5qx84nheghq2pz24j';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://marketplace.envato.com/api/edge/" . $username . "/" . $api_key . "/verify-purchase:" . $purchase_key . ".json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        $purchase_data = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $purchase_data;
    }

}


if (!function_exists('shortCode')) {

    /*     * ************************** */
    /*     * **** ShortCode Pastor ***** */
    /*     * ************************** */

    function shortCode($type, $table, $sort, $sortby, $quantity) {
        $table = str_replace(' ', '', $table);

        $ci = & get_instance(); //get main CodeIgniter object		
        $ci->load->database(); //load databse library
       
        //$ci->db->where('status', 1);
        
        if ($sort && $sortby) {
            $ci->db->order_by($sortby, $sort);
        }
        if ($quantity) {
            $ci->db->limit($quantity);
        }

        $query = $ci->db->get($table);

        if ($query->num_rows() > 0) {
            $result = $query->result();

            $resultHtml = "";

            if ($type == "speech") {
                $resultHtml = "<div class='owl-carousel'>";
            } else if ($type == "event" && $table == "seminar") {
                $resultHtml = "<div class='row'>";
            }

            $i = 0;
            foreach ($result as $row) {
                $i++;

                if ($table == "pastor") {
                    $peopleid = $row->pastorid;
                } elseif ($table == "committee") {
                    $peopleid = $row->committeeid;
                } elseif ($table == "cooperative_officers") {
                    $peopleid = $row->cooperative_officersid;
                }elseif ($table == "member") {
                    $peopleid = $row->memberid;
                } elseif ($table == "chorus") {
                    $peopleid = $row->chorusid;
                } elseif ($table == "clan") {
                    $peopleid = $row->clanid;
                } elseif ($table == "student") {
                    $peopleid = $row->studentid;
                } elseif ($table == "staff") {
                    $peopleid = $row->staffid;
                } elseif ($table == "sundayschool") {
                    $peopleid = $row->sschoolid;
                } elseif ($table == "speech") {
                    $peopleid = $row->speechid;
                } else {
                    $peopleid = "";
                }

                if ($type == "bodaregroup") {
                    $resultHtml .= "<div class='col-lg-4 col-md-6 wow fadeInUp' data-wow-delay='0.1s'>
                    <div class='team-item'>
                        <img class='img-fluid' src='". base_url() . "images/$table/profile/$row->profileimage' alt='$row->fname' width='550px' height='600px'> 
                        <div class='d-flex'>
                            <div class='flex-shrink-0 btn-square bg-primary' style='width: 90px; height: 90px;'>
                                <i class='fa fa-2x fa-share text-white'></i>
                            </div>
                            <div class='position-relative overflow-hidden bg-light d-flex flex-column justify-content-center w-100 ps-4'
                                style='height: 90px;'>
                                <h5><a target='_blank' href='" . base_url() . "home/$table/view/$peopleid'>$row->fname $row->lname</a></h5>
                                <span class='text-primary'>$row->position</span>
                                <div class='team-social'>
                                    <a class='btn btn-square btn-dark rounded-circle mx-1' href=''><i
                                            class='fab fa-facebook-f'></i></a>
                                    <a class='btn btn-square btn-dark rounded-circle mx-1' href=''><i
                                            class='fab fa-twitter'></i></a>
                                    <a class='btn btn-square btn-dark rounded-circle mx-1' href=''><i
                                            class='fab fa-instagram'></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
                    //$resultHtml .= "<div class='col-lg-3 col-md-3 col-sm-6 col-xs-12'><div class='pastors'><img src='" . base_url() . "images/$table/profile/$row->profileimage' alt='$row->fname'></img> <h5>$row->position</h5>       <h4><a target='_blank' href='" . base_url() . "home/$table/view/$peopleid'>$row->fname $row->lname</a></h4>                                </div>                            </div>";
                }else if ($type == "group") {
                    $resultHtml .= "<div class='col-lg-3 col-md-3 col-sm-6 col-xs-12'>
                                <div class='pastors'>
                                    <img src='" . base_url() . "images/$table/profile/$row->profileimage' alt='$row->fname'></img>
                                    <h5>$row->position</h5>
                                    <h4><a target='_blank' href='" . base_url() . "home/$table/view/$peopleid'>$row->fname $row->lname</a></h4>
                                </div>
                            </div>";
                } else if ($type == "speech") {
                    $resultHtml .= "<div class='col-md-offset-2 col-lg-8 col-md-8 col-sm-12 col-xs-12'>
                                    <div class='pastors'>
                                            <img src='" . base_url() . "images/" . $table . "/profile/$row->profileimage' alt='$row->fname'></img>
                                            <h4>$row->fname $row->lname</h4>
                                            <h5>$row->position</h5>
                                            <p>" . word_limiter(strip_tags($row->speech), 100) . "</p><a class='read_more' href='" . base_url() . "home/speech/view/" . $row->speechid . "' data-toggle='modal' data-target='" . base_url() . "home/speech/view/" . $row->speechid . "'>Read More...</a>
                                    </div>
                            </div>";
                } else if ($type == "event" && $table == "seminar") {

                    if ($i % 4 == 0 && $i != 0) {
                        $resultHtml .= '</div><div class="row">';
                    }

                    $resultHtml .= "<div class='col-lg-4 col-md-4 col-sm-12 col-xs-12'>
                                <div class='seminar'>
                                    <img src=' " . base_url() . "images/$table/banner/$row->seminarbanner' alt='$table'></img>
                                    <h4><a target='_blank' href=' " . base_url() . "home/$table/view/$row->seminarid'>$row->seminartitle</a></h4>
                                </div>
                            </div>";
                } else if ($type == "event" && $table == "sermon") {

                    $resultHtml .= "<div class='sermon'>
                                        <div class='left'>
                                            <img src='" . base_url() . "images/sermon/feature/" . $row->sermonbanner .  "' alt='" . $row->sermontitle . "'>
                                        </div>
                                        <div class='center'>
                                            <h4 class='title'><a href='" . base_url() . "home/sermon/view/" . $row->sermonid . "'>" . $row->sermontitle . "</a></h4>
                                            <span class='elements'>Time - " . $row->sermontime . " | Date - " . $row->sermondate . " | Pastor/Writer/Author - " . $row->sermonauthor . "</span>
                                        </div>    
                                        <div class='right'>
                                            <button class='btn '><a href='" . base_url() . "home/sermon/view/" . $row->sermonid . "'><i class='fa fa-music fa-fw'></i> Audio</a></button> 
                                            <button class='btn '><a href='" . base_url() . "home/sermon/view/" . $row->sermonid . "'><i class='fa fa-youtube fa-fw'></i> Video</a></button> 
                                        </div>
                                    </div>";
                }
            }

            if ($type == "event" && $table == "seminar") {                
                $resultHtml .= '</div>';
            } else if ($type == "speech") {
                $resultHtml .= "</div>";
            }

            return $resultHtml;
        } else {
            //Default Currency USD Because No Row Founds In Table
            return false;
        }
    }

}

if (!function_exists('sanitize_inquiry_html')) {

    function sanitize_inquiry_html($html) {
        $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><blockquote><span><div>';
        $clean = strip_tags((string) $html, $allowed);
        $clean = preg_replace('/\s*on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
        $clean = preg_replace('/href\s*=\s*("\s*javascript:[^"]*"|\'\s*javascript:[^\']*\')/i', 'href="#"', $clean);
        return trim($clean);
    }
}

if (!function_exists('format_inquiry_reply_body')) {

    function format_inquiry_reply_body($body, $isInbound = FALSE) {
        $body = trim((string) $body);
        if ($body === '') {
            return '';
        }

        if ($isInbound) {
            $body = preg_replace("/\n+On .+wrote:\s*\n.*/is", '', $body);
            $body = preg_replace("/\n+-----\s*Original Message\s*-----[\s\S]*/i", '', $body);
            $body = preg_replace("/\n+From:\s*BODARE[\s\S]*/i", '', $body);
            $body = trim($body);
        }

        if (strip_tags($body) !== $body) {
            return sanitize_inquiry_html($body);
        }

        return nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
    }
}

if (!function_exists('inquiry_attachment_policy')) {

    function inquiry_attachment_policy() {
        return array(
            'max_count' => 5,
            'max_file_bytes' => 10 * 1024 * 1024,
            'max_total_bytes' => 20 * 1024 * 1024,
            'allowed_ext' => array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png'),
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|txt|jpg|jpeg|png',
            'allowed_mimes' => array(
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain',
                'image/jpeg',
                'image/png',
            ),
        );
    }
}

if (!function_exists('inquiry_attachment_storage_path')) {

    function inquiry_attachment_storage_path() {
        $path = realpath(APPPATH . '../files/inquiry_attachments');
        if ($path === FALSE) {
            $target = APPPATH . '../files/inquiry_attachments';
            if (!is_dir($target)) {
                @mkdir($target, 0755, TRUE);
            }
            $path = realpath($target);
        }

        return $path ? $path : FALSE;
    }
}

if (!function_exists('normalize_uploaded_files')) {

    function normalize_uploaded_files($fileField) {
        $normalized = array();
        if (!isset($fileField['name'])) {
            return $normalized;
        }

        if (!is_array($fileField['name'])) {
            if ((int) $fileField['error'] === UPLOAD_ERR_NO_FILE) {
                return $normalized;
            }
            return array($fileField);
        }

        $count = count($fileField['name']);
        for ($i = 0; $i < $count; $i++) {
            if ((int) $fileField['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $normalized[] = array(
                'name' => $fileField['name'][$i],
                'type' => $fileField['type'][$i],
                'tmp_name' => $fileField['tmp_name'][$i],
                'error' => $fileField['error'][$i],
                'size' => $fileField['size'][$i],
            );
        }

        return $normalized;
    }
}

if (!function_exists('inquiry_attachment_extension')) {

    function inquiry_attachment_extension($filename) {
        $ext = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext);
        return $ext;
    }
}

if (!function_exists('inquiry_attachment_detect_mime')) {

    function inquiry_attachment_detect_mime($path, $fallback = 'application/octet-stream') {
        if (is_file($path) && function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $path);
                finfo_close($finfo);
                if (is_string($mime) && $mime !== '') {
                    return $mime;
                }
            }
        }

        return $fallback;
    }
}

if (!function_exists('inquiry_attachment_mime_allowed')) {

    function inquiry_attachment_mime_allowed($mime, $extension = '') {
        $policy = inquiry_attachment_policy();
        $mime = strtolower(trim((string) $mime));
        $extension = inquiry_attachment_extension($extension);

        if ($extension !== '' && !in_array($extension, $policy['allowed_ext'], TRUE)) {
            return FALSE;
        }

        if ($mime === 'application/octet-stream' && $extension !== '') {
            $map = array(
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'txt' => 'text/plain',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
            );
            if (isset($map[$extension])) {
                $mime = $map[$extension];
            }
        }

        return in_array($mime, $policy['allowed_mimes'], TRUE);
    }
}

if (!function_exists('validate_inquiry_attachment_batch')) {

    function validate_inquiry_attachment_batch($fileField) {
        $policy = inquiry_attachment_policy();
        $files = normalize_uploaded_files($fileField);

        if (empty($files)) {
            return array(
                'valid' => TRUE,
                'error' => '',
                'files' => array(),
            );
        }

        if (count($files) > $policy['max_count']) {
            return array(
                'valid' => FALSE,
                'error' => 'You can attach up to ' . $policy['max_count'] . ' files per message.',
                'files' => array(),
            );
        }

        $totalSize = 0;
        foreach ($files as $file) {
            if ((int) $file['error'] !== UPLOAD_ERR_OK) {
                return array(
                    'valid' => FALSE,
                    'error' => 'One or more attachments failed to upload.',
                    'files' => array(),
                );
            }

            $size = (int) $file['size'];
            if ($size <= 0) {
                return array(
                    'valid' => FALSE,
                    'error' => 'Attachment "' . $file['name'] . '" is empty.',
                    'files' => array(),
                );
            }

            if ($size > $policy['max_file_bytes']) {
                return array(
                    'valid' => FALSE,
                    'error' => 'Attachment "' . $file['name'] . '" exceeds the 10 MB per-file limit.',
                    'files' => array(),
                );
            }

            $ext = inquiry_attachment_extension($file['name']);
            if ($ext === '' || !in_array($ext, $policy['allowed_ext'], TRUE)) {
                return array(
                    'valid' => FALSE,
                    'error' => 'Attachment "' . $file['name'] . '" uses a file type that is not allowed.',
                    'files' => array(),
                );
            }

            $totalSize += $size;
        }

        if ($totalSize > $policy['max_total_bytes']) {
            return array(
                'valid' => FALSE,
                'error' => 'Total attachment size exceeds the 20 MB limit per message.',
                'files' => array(),
            );
        }

        return array(
            'valid' => TRUE,
            'error' => '',
            'files' => $files,
        );
    }
}

if (!function_exists('inquiry_attachment_make_stored_name')) {

    function inquiry_attachment_make_stored_name($originalFilename) {
        $ext = inquiry_attachment_extension($originalFilename);
        if ($ext === '') {
            $ext = 'bin';
        }
        return date('Ymd_His_') . bin2hex(random_bytes(8)) . '.' . $ext;
    }
}

if (!function_exists('format_inquiry_file_size')) {

    function format_inquiry_file_size($bytes) {
        $bytes = (int) $bytes;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}

if (!function_exists('inquiry_attachment_resolve_path')) {

    function inquiry_attachment_resolve_path($storedFilename) {
        $storagePath = inquiry_attachment_storage_path();
        if ($storagePath === FALSE) {
            return FALSE;
        }

        $safeFile = basename((string) $storedFilename);
        $absolutePath = $storagePath . DIRECTORY_SEPARATOR . $safeFile;
        if (!is_file($absolutePath)) {
            return FALSE;
        }

        $realFile = realpath($absolutePath);
        $realBase = realpath($storagePath);
        if ($realFile === FALSE || $realBase === FALSE || strpos($realFile, $realBase) !== 0) {
            return FALSE;
        }

        return $realFile;
    }
}