<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Inquiry tool helpers (HTML sanitize, attachments, formatting).
 */

if (!function_exists('getCreateDate')) {
    function getCreateDate($orderby, $table) {
        $CI =& get_instance();
        $CI->db->select('created_at, cdate');
        $CI->db->order_by($orderby, 'DESC');
        $CI->db->limit(1);
        $row = $CI->db->get($table)->row();
        if (!$row) {
            return '—';
        }
        if (!empty($row->created_at)) {
            return date('M j, Y g:i A', strtotime($row->created_at));
        }
        return !empty($row->cdate) ? $row->cdate : '—';
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
