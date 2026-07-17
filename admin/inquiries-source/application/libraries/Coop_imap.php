<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Fetches guest email replies from the contact mailbox (IMAP) into inquiry_reply.
 * Requires the PHP IMAP extension (php_imap) on the server.
 */
class Coop_imap {

    protected $CI;
    protected $last_error = '';

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->library('coop_mail');
    }

    public function get_last_error() {
        return $this->last_error;
    }

    /**
     * Import new guest replies from the contact mailbox.
     *
     * @return array{imported:int,skipped:int,matched:int,errors:array}
     */
    public function import_inbound_replies() {
        $result = array(
            'imported' => 0,
            'skipped' => 0,
            'matched' => 0,
            'errors' => array(),
            'inquiry_ids' => array(),
        );

        if (!function_exists('imap_open')) {
            $this->last_error = 'PHP IMAP extension is not enabled on this server. Enable php_imap in php.ini.';
            $result['errors'][] = $this->last_error;
            return $result;
        }

        $this->CI->coop_mail->set_profile('contact');
        $settings = $this->CI->coop_mail->get_settings('contact');
        if (!$settings) {
            $this->last_error = 'Contact mail settings are not configured.';
            $result['errors'][] = $this->last_error;
            return $result;
        }

        if (!$this->imap_is_enabled($settings)) {
            $this->last_error = 'Inbound email fetching is disabled. Enable it under Email/SMTP Settings (Contact Us Mailer).';
            $result['errors'][] = $this->last_error;
            return $result;
        }

        $password = $this->CI->coop_mail->decrypt_password($settings->smtp_pass);
        if ($password === FALSE || $password === '') {
            $this->last_error = 'Unable to decrypt the contact mailbox password.';
            $result['errors'][] = $this->last_error;
            return $result;
        }

        $mailbox = $this->build_mailbox_string($settings);
        $connection = @imap_open($mailbox, $settings->smtp_user, $password, 0, 1);
        if ($connection === FALSE) {
            $this->last_error = 'IMAP connection failed: ' . imap_last_error();
            $result['errors'][] = $this->last_error;
            return $result;
        }

        $uids = @imap_search($connection, 'UNSEEN', SE_UID);
        if ($uids === FALSE || empty($uids)) {
            $uids = @imap_search($connection, 'SINCE "' . date('d-M-Y', strtotime('-14 days')) . '"', SE_UID);
        }
        if ($uids === FALSE || empty($uids)) {
            imap_close($connection);
            return $result;
        }

        rsort($uids);
        $uids = array_slice($uids, 0, 50);

        foreach ($uids as $uid) {
            $imported = $this->import_message($connection, (int) $uid, $settings);
            if (is_array($imported) && !empty($imported['success'])) {
                $result['imported']++;
                $result['matched']++;
                $result['inquiry_ids'][] = (int) $imported['inquiryid'];
            } elseif ($imported === FALSE) {
                $result['skipped']++;
            } else {
                $result['errors'][] = (string) $imported;
            }
        }

        $result['inquiry_ids'] = array_values(array_unique($result['inquiry_ids']));

        imap_close($connection);
        return $result;
    }

    public static function inquiry_subject_tag($inquiryid) {
        return '[Inquiry #' . (int) $inquiryid . ']';
    }

    public static function tagged_subject($inquiryid, $subject) {
        $tag = self::inquiry_subject_tag($inquiryid);
        if (stripos($subject, $tag) !== FALSE) {
            return $subject;
        }
        return $tag . ' ' . ltrim($subject);
    }

    public static function parse_inquiry_id_from_subject($subject) {
        if (preg_match('/\[Inquiry\s*#(\d+)\]/i', (string) $subject, $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }

    protected function imap_is_enabled($settings) {
        if (!$this->CI->db->field_exists('imap_enabled', 'email_smtp_settings')) {
            return TRUE;
        }
        return !empty($settings->imap_enabled);
    }

    protected function build_mailbox_string($settings) {
        $host = !empty($settings->imap_host) ? $settings->imap_host : $settings->smtp_host;
        $port = !empty($settings->imap_port) ? (int) $settings->imap_port : 993;
        $crypto = !empty($settings->imap_crypto) ? strtolower($settings->imap_crypto) : 'ssl';

        $flags = '/imap';
        if ($crypto === 'ssl') {
            $flags .= '/ssl/novalidate-cert';
        } elseif ($crypto === 'tls') {
            $flags .= '/tls/novalidate-cert';
        }

        return '{' . $host . ':' . $port . $flags . '}INBOX';
    }

    protected function import_message($connection, $uid, $settings) {
        $uidKey = 'contact:' . $uid;
        if ($this->CI->db->where('imap_uid', $uidKey)->count_all_results('inquiry_reply') > 0) {
            return FALSE;
        }

        $overview = imap_fetch_overview($connection, (string) $uid, FT_UID);
        if (empty($overview[0])) {
            return 'Could not read email overview for UID ' . $uid;
        }
        $meta = $overview[0];

        $rawHeaders = imap_fetchheader($connection, $uid, FT_UID);
        $inquiryid = $this->parse_inquiry_id_from_headers($rawHeaders);
        if (!$inquiryid) {
            $inquiryid = self::parse_inquiry_id_from_subject(isset($meta->subject) ? $this->decode_mime_header($meta->subject) : '');
        }

        $from = isset($meta->from) ? $this->decode_mime_header($meta->from) : '';
        $sender = $this->parse_email_address($from);
        if (!$inquiryid) {
            $inquiryid = $this->match_inquiry_by_sender_and_subject($sender['email'], isset($meta->subject) ? $this->decode_mime_header($meta->subject) : '');
        }

        if (!$inquiryid) {
            return FALSE;
        }

        $inquiry = $this->CI->db->get_where('inquiry', array('inquiryid' => (int) $inquiryid), 1)->row();
        if (!$inquiry) {
            return FALSE;
        }

        if (!empty($sender['email']) && strcasecmp($sender['email'], $inquiry->email) !== 0) {
            return FALSE;
        }

        $subject = isset($meta->subject) ? $this->decode_mime_header($meta->subject) : '(No subject)';
        $body = $this->extract_plain_body($connection, $uid);
        if ($body === '') {
            $body = '(Empty message body)';
        }
        $body = $this->strip_quoted_reply($body);

        $messageId = '';
        if (preg_match('/^Message-ID:\s*(.+)$/im', $rawHeaders, $matches)) {
            $messageId = trim($matches[1]);
        }
        if ($messageId !== '' && $this->CI->db->where('message_id', $messageId)->count_all_results('inquiry_reply') > 0) {
            return FALSE;
        }

        $now = date('Y-m-d H:i:s');
        $this->CI->db->insert('inquiry_reply', array(
            'inquiryid' => (int) $inquiryid,
            'userid' => NULL,
            'direction' => 'inbound',
            'sender_email' => $sender['email'],
            'sender_name' => $sender['name'],
            'reply_subject' => $subject,
            'reply_message' => $body,
            'email_sent' => 0,
            'imap_uid' => 'contact:' . $uid,
            'message_id' => $messageId !== '' ? $messageId : NULL,
            'cdate' => date('j F Y'),
            'created_at' => !empty($meta->date) ? date('Y-m-d H:i:s', strtotime($meta->date)) : $now,
        ));
        $replyid = (int) $this->CI->db->insert_id();
        $this->import_message_attachments($connection, $uid, $replyid, (int) $inquiryid);

        $newStatus = 'guest_replied';
        $this->CI->db->where('inquiryid', (int) $inquiryid);
        $this->CI->db->update('inquiry', array(
            'status' => $newStatus,
            'updated_at' => $now,
        ));

        @imap_setflag_full($connection, (string) $uid, '\\Seen', ST_UID);
        return array(
            'success' => TRUE,
            'inquiryid' => (int) $inquiryid,
        );
    }

    protected function parse_inquiry_id_from_headers($rawHeaders) {
        if (preg_match('/^X-BODARE-Inquiry-ID:\s*(\d+)\s*$/im', $rawHeaders, $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }

    protected function match_inquiry_by_sender_and_subject($email, $subject) {
        $email = strtolower(trim((string) $email));
        if ($email === '') {
            return 0;
        }

        $normalizedSubject = $this->normalize_reply_subject($subject);
        $this->CI->db->where('email', $email);
        $this->CI->db->order_by('inquiryid', 'DESC');
        $inquiries = $this->CI->db->get('inquiry')->result();

        foreach ($inquiries as $row) {
            if ($this->normalize_reply_subject($row->subject) === $normalizedSubject) {
                return (int) $row->inquiryid;
            }
        }

        return !empty($inquiries[0]) ? (int) $inquiries[0]->inquiryid : 0;
    }

    protected function normalize_reply_subject($subject) {
        $subject = strtolower(trim((string) $subject));
        $subject = preg_replace('/\[inquiry\s*#\d+\]\s*/i', '', $subject);
        $subject = preg_replace('/^(re|fw|fwd):\s*/i', '', $subject);
        while (preg_match('/^(re|fw|fwd):\s*/i', $subject)) {
            $subject = preg_replace('/^(re|fw|fwd):\s*/i', '', $subject);
        }
        $subject = preg_replace('/^we received your message:\s*/i', '', $subject);
        return trim($subject);
    }

    protected function parse_email_address($from) {
        $from = trim((string) $from);
        $email = $from;
        $name = '';

        if (preg_match('/^(.*)<([^>]+)>$/', $from, $matches)) {
            $name = trim(trim($matches[1]), '"\'');
            $email = trim($matches[2]);
        }

        return array(
            'name' => $name,
            'email' => strtolower($email),
        );
    }

    protected function decode_mime_header($value) {
        $decoded = @imap_utf8((string) $value);
        return $decoded !== FALSE ? $decoded : (string) $value;
    }

    protected function extract_plain_body($connection, $uid) {
        $structure = @imap_fetchstructure($connection, $uid, FT_UID);
        if (!$structure) {
            $body = imap_body($connection, $uid, FT_UID);
            return $this->cleanup_body($body);
        }

        $body = $this->fetch_part_body($connection, $uid, $structure);
        return $this->cleanup_body($body);
    }

    protected function fetch_part_body($connection, $uid, $structure, $partNumber = '') {
        if (!empty($structure->parts)) {
            foreach ($structure->parts as $index => $subpart) {
                $partId = $partNumber === '' ? (string) ($index + 1) : $partNumber . '.' . ($index + 1);
                $type = isset($subpart->type) ? (int) $subpart->type : 0;
                $subtype = isset($subpart->subtype) ? strtolower($subpart->subtype) : '';

                if ($type === 0 && $subtype === 'plain') {
                    $body = imap_fetchbody($connection, $uid, $partId, FT_UID);
                    return $this->decode_part_body($body, $subpart);
                }
            }

            foreach ($structure->parts as $index => $subpart) {
                $partId = $partNumber === '' ? (string) ($index + 1) : $partNumber . '.' . ($index + 1);
                $body = $this->fetch_part_body($connection, $uid, $subpart, $partId);
                if ($body !== '') {
                    return $body;
                }
            }
            return '';
        }

        $body = imap_fetchbody($connection, $uid, $partNumber === '' ? '1' : $partNumber, FT_UID);
        return $this->decode_part_body($body, $structure);
    }

    protected function decode_part_body($body, $structure) {
        if (!isset($structure->encoding)) {
            return (string) $body;
        }

        switch ((int) $structure->encoding) {
            case 3:
                return (string) base64_decode($body);
            case 4:
                return (string) quoted_printable_decode($body);
            default:
                return (string) $body;
        }
    }

    protected function cleanup_body($body) {
        $body = (string) $body;
        $body = preg_replace("/\r\n?/", "\n", $body);
        $body = preg_replace("/\n{3,}/", "\n\n", $body);
        $body = trim($body);

        if (stripos($body, '<html') !== FALSE || stripos($body, '<body') !== FALSE) {
            $body = html_entity_decode(strip_tags($body), ENT_QUOTES, 'UTF-8');
            $body = preg_replace("/\n{3,}/", "\n\n", trim($body));
        }

        $body = @iconv('UTF-8', 'UTF-8//IGNORE', $body);
        return $body;
    }

    protected function strip_quoted_reply($body) {
        $body = trim((string) $body);
        if ($body === '') {
            return $body;
        }

        $patterns = array(
            "/\n+On .+wrote:\s*\n.*/is",
            "/\n+-----\s*Original Message\s*-----[\s\S]*/i",
            "/\n+From:\s*BODARE[\s\S]*/i",
            "/\n+_{5,}[\s\S]*/",
        );

        foreach ($patterns as $pattern) {
            $cleaned = preg_replace($pattern, '', $body);
            if (is_string($cleaned) && trim($cleaned) !== '') {
                $body = trim($cleaned);
            }
        }

        return trim($body);
    }

    protected function import_message_attachments($connection, $uid, $replyid, $inquiryid) {
        if (!$this->CI->db->table_exists('inquiry_reply_attachment')) {
            return;
        }

        $structure = @imap_fetchstructure($connection, $uid, FT_UID);
        if (!$structure) {
            return;
        }

        $parts = $this->collect_attachment_parts($structure);
        if (empty($parts)) {
            return;
        }

        $policy = inquiry_attachment_policy();
        $savedCount = 0;
        $totalSize = 0;

        foreach ($parts as $part) {
            if ($savedCount >= $policy['max_count']) {
                break;
            }

            $filename = $this->get_part_filename($part['structure']);
            if ($filename === '') {
                continue;
            }

            $rawBody = imap_fetchbody($connection, $uid, $part['part'], FT_UID);
            $decoded = $this->decode_part_body($rawBody, $part['structure']);
            $size = strlen($decoded);
            if ($size <= 0) {
                continue;
            }
            if ($size > $policy['max_file_bytes'] || ($totalSize + $size) > $policy['max_total_bytes']) {
                continue;
            }

            $ext = inquiry_attachment_extension($filename);
            if ($ext === '' || !in_array($ext, $policy['allowed_ext'], TRUE)) {
                continue;
            }

            $storagePath = inquiry_attachment_storage_path();
            if ($storagePath === FALSE) {
                break;
            }

            $storedName = inquiry_attachment_make_stored_name($filename);
            $destination = $storagePath . DIRECTORY_SEPARATOR . $storedName;
            if (@file_put_contents($destination, $decoded) === FALSE) {
                continue;
            }

            $mime = inquiry_attachment_detect_mime($destination, $this->get_part_mime_type($part['structure']));
            if (!inquiry_attachment_mime_allowed($mime, $filename)) {
                @unlink($destination);
                continue;
            }

            $this->CI->db->insert('inquiry_reply_attachment', array(
                'replyid' => (int) $replyid,
                'inquiryid' => (int) $inquiryid,
                'direction' => 'inbound',
                'original_filename' => $filename,
                'stored_filename' => $storedName,
                'mime_type' => $mime,
                'file_size' => $size,
                'created_at' => date('Y-m-d H:i:s'),
            ));

            $savedCount++;
            $totalSize += $size;
        }
    }

    protected function collect_attachment_parts($structure, $partNumber = '') {
        $parts = array();

        if (!empty($structure->parts)) {
            foreach ($structure->parts as $index => $subpart) {
                $partId = $partNumber === '' ? (string) ($index + 1) : $partNumber . '.' . ($index + 1);
                $parts = array_merge($parts, $this->collect_attachment_parts($subpart, $partId));
            }
            return $parts;
        }

        if ($this->is_attachment_part($structure)) {
            $parts[] = array(
                'part' => $partNumber === '' ? '1' : $partNumber,
                'structure' => $structure,
            );
        }

        return $parts;
    }

    protected function is_attachment_part($structure) {
        $type = isset($structure->type) ? (int) $structure->type : 0;
        $subtype = isset($structure->subtype) ? strtolower($structure->subtype) : '';
        $disposition = !empty($structure->disposition) ? strtolower($structure->disposition) : '';
        $filename = $this->get_part_filename($structure);

        if ($disposition === 'inline') {
            return FALSE;
        }

        if ($disposition === 'attachment') {
            return $filename !== '';
        }

        if ($filename !== '' && !($type === 0 && in_array($subtype, array('plain', 'html'), TRUE))) {
            return TRUE;
        }

        return FALSE;
    }

    protected function get_part_filename($structure) {
        $filename = '';

        if (!empty($structure->ifdparameters)) {
            foreach ($structure->dparameters as $param) {
                if (strtolower($param->attribute) === 'filename') {
                    $filename = $this->decode_mime_header($param->value);
                    break;
                }
            }
        }

        if ($filename === '' && !empty($structure->ifparameters)) {
            foreach ($structure->parameters as $param) {
                if (strtolower($param->attribute) === 'name') {
                    $filename = $this->decode_mime_header($param->value);
                    break;
                }
            }
        }

        return trim((string) $filename);
    }

    protected function get_part_mime_type($structure) {
        $typeMap = array(
            0 => 'text',
            1 => 'multipart',
            2 => 'message',
            3 => 'application',
            4 => 'audio',
            5 => 'image',
            6 => 'video',
            7 => 'other',
        );

        $type = isset($structure->type) ? (int) $structure->type : 3;
        $subtype = isset($structure->subtype) ? strtolower($structure->subtype) : 'octet-stream';
        $primary = isset($typeMap[$type]) ? $typeMap[$type] : 'application';

        return $primary . '/' . $subtype;
    }
}
