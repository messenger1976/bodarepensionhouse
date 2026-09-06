<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Database backup vault helpers for the admin Backup Database tool.
 * Stores .sql files under APPPATH/backups/ (not web-served).
 */
class Backup_service {

    const MAX_UPLOAD_BYTES = 52428800; // 50 MB
    const FILENAME_PATTERN = '/^[a-zA-Z0-9._-]+\.sql$/';

    /** @var CI_Controller */
    protected $CI;

    /** @var string */
    protected $vault_path;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->vault_path = rtrim(APPPATH, '/\\') . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR;
        $this->ensure_vault();
    }

    public function vault_path() {
        return $this->vault_path;
    }

    protected function ensure_vault() {
        if (!is_dir($this->vault_path)) {
            @mkdir($this->vault_path, 0750, TRUE);
        }
    }

    /**
     * Validate and normalize a backup basename (no path traversal).
     * @return string|false
     */
    public function sanitize_filename($name) {
        $name = basename((string) $name);
        if ($name === '' || !preg_match(self::FILENAME_PATTERN, $name)) {
            return FALSE;
        }
        return $name;
    }

    /**
     * Absolute path for an allowlisted backup file, or FALSE.
     * @return string|false
     */
    public function resolve_path($filename) {
        $safe = $this->sanitize_filename($filename);
        if ($safe === FALSE) {
            return FALSE;
        }
        $path = $this->vault_path . $safe;
        $real_vault = realpath($this->vault_path);
        $real_file = realpath($path);
        if ($real_vault === FALSE || $real_file === FALSE) {
            // File may not exist yet for create; still return composed path if within vault name rules
            if (!is_file($path)) {
                return $path;
            }
            return FALSE;
        }
        if (strpos($real_file, $real_vault) !== 0) {
            return FALSE;
        }
        return $real_file;
    }

    /**
     * List backup files newest first.
     * @return array
     */
    public function list_backups() {
        $this->ensure_vault();
        $files = array();
        $entries = @scandir($this->vault_path);
        if ($entries === FALSE) {
            return $files;
        }
        foreach ($entries as $entry) {
            if ($this->sanitize_filename($entry) === FALSE) {
                continue;
            }
            $path = $this->vault_path . $entry;
            if (!is_file($path)) {
                continue;
            }
            $files[] = array(
                'name' => $entry,
                'size' => (int) filesize($path),
                'size_label' => $this->format_bytes((int) filesize($path)),
                'mtime' => (int) filemtime($path),
                'mtime_label' => date('Y-m-d H:i:s', filemtime($path)),
            );
        }
        usort($files, function ($a, $b) {
            return $b['mtime'] - $a['mtime'];
        });
        return $files;
    }

    /**
     * Create a new .sql backup via CI dbutil.
     * @return array{success:bool,filename?:string,message?:string}
     */
    public function create_backup() {
        $this->ensure_vault();
        $this->CI->load->dbutil();

        $filename = 'bodare_' . date('Ymd_His') . '.sql';
        if ($this->sanitize_filename($filename) === FALSE) {
            return array('success' => FALSE, 'message' => 'Unable to generate a valid backup filename.');
        }

        $prefs = array(
            'format' => 'txt',
            'filename' => $filename,
            'add_drop' => TRUE,
            'add_insert' => TRUE,
            'newline' => "\n",
        );

        $backup = $this->CI->dbutil->backup($prefs);
        if ($backup === FALSE || $backup === '') {
            return array('success' => FALSE, 'message' => 'Database backup generation failed.');
        }

        $path = $this->vault_path . $filename;
        if (@file_put_contents($path, $backup) === FALSE) {
            return array('success' => FALSE, 'message' => 'Unable to write backup file to the vault.');
        }

        return array('success' => TRUE, 'filename' => $filename);
    }

    /**
     * Store an uploaded .sql file in the vault.
     * @param array $file $_FILES entry
     * @return array{success:bool,filename?:string,message?:string}
     */
    public function store_upload($file) {
        $this->ensure_vault();

        if (!is_array($file) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return array('success' => FALSE, 'message' => 'No valid upload was received.');
        }

        if (!empty($file['error']) && (int) $file['error'] !== UPLOAD_ERR_OK) {
            return array('success' => FALSE, 'message' => 'Upload failed (error code ' . (int) $file['error'] . ').');
        }

        $original = isset($file['name']) ? basename($file['name']) : '';
        $original = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original);
        if (!preg_match('/\.sql$/i', $original)) {
            return array('success' => FALSE, 'message' => 'Only .sql backup files are allowed.');
        }

        $size = isset($file['size']) ? (int) $file['size'] : (int) filesize($file['tmp_name']);
        if ($size <= 0) {
            return array('success' => FALSE, 'message' => 'Uploaded file is empty.');
        }
        if ($size > self::MAX_UPLOAD_BYTES) {
            return array('success' => FALSE, 'message' => 'Upload exceeds the 50 MB size limit.');
        }

        $base = pathinfo($original, PATHINFO_FILENAME);
        $base = preg_replace('/[^a-zA-Z0-9._-]/', '_', (string) $base);
        if ($base === '') {
            $base = 'upload';
        }
        $filename = 'upload_' . date('Ymd_His') . '_' . $base . '.sql';
        if ($this->sanitize_filename($filename) === FALSE) {
            return array('success' => FALSE, 'message' => 'Unable to build a safe upload filename.');
        }

        $dest = $this->vault_path . $filename;
        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            return array('success' => FALSE, 'message' => 'Unable to move the uploaded file into the vault.');
        }

        return array('success' => TRUE, 'filename' => $filename);
    }

    /**
     * Delete a backup file from the vault.
     * @return array{success:bool,message?:string}
     */
    public function delete_backup($filename) {
        $path = $this->resolve_path($filename);
        if ($path === FALSE || !is_file($path)) {
            return array('success' => FALSE, 'message' => 'Backup file not found.');
        }
        if (!@unlink($path)) {
            return array('success' => FALSE, 'message' => 'Unable to delete the backup file.');
        }
        return array('success' => TRUE);
    }

    /**
     * Absolute path for download, or FALSE.
     * @return string|false
     */
    public function get_download_path($filename) {
        $path = $this->resolve_path($filename);
        if ($path === FALSE || !is_file($path)) {
            return FALSE;
        }
        return $path;
    }

    /**
     * Restore the live database from a vault .sql file.
     * @return array{success:bool,message?:string,statements?:int}
     */
    public function restore_backup($filename) {
        $path = $this->resolve_path($filename);
        if ($path === FALSE || !is_file($path)) {
            return array('success' => FALSE, 'message' => 'Backup file not found.');
        }

        $sql = @file_get_contents($path);
        if ($sql === FALSE || trim($sql) === '') {
            return array('success' => FALSE, 'message' => 'Backup file is empty or unreadable.');
        }

        // Strip UTF-8 BOM if present
        if (substr($sql, 0, 3) === "\xEF\xBB\xBF") {
            $sql = substr($sql, 3);
        }

        $statements = $this->split_sql_statements($sql);
        if (empty($statements)) {
            return array('success' => FALSE, 'message' => 'No SQL statements found in the backup file.');
        }

        $conn = $this->CI->db->conn_id;
        if (!$conn) {
            return array('success' => FALSE, 'message' => 'Database connection is not available.');
        }

        @set_time_limit(0);
        $this->CI->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->CI->db->query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');

        $executed = 0;
        $error = NULL;

        try {
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if ($statement === '' || $this->is_sql_comment_only($statement)) {
                    continue;
                }
                if ($this->CI->db->query($statement) === FALSE) {
                    $error = $this->CI->db->error();
                    $msg = is_array($error) && !empty($error['message'])
                        ? $error['message']
                        : 'A SQL statement failed during restore.';
                    throw new RuntimeException($msg);
                }
                $executed++;
            }
        } catch (Exception $e) {
            $this->CI->db->query('SET FOREIGN_KEY_CHECKS=1');
            return array(
                'success' => FALSE,
                'message' => 'Restore failed after ' . $executed . ' statement(s): ' . $e->getMessage(),
                'statements' => $executed,
            );
        }

        $this->CI->db->query('SET FOREIGN_KEY_CHECKS=1');

        return array(
            'success' => TRUE,
            'message' => 'Database restored successfully (' . $executed . ' statements).',
            'statements' => $executed,
        );
    }

    protected function is_sql_comment_only($statement) {
        $lines = preg_split('/\r\n|\r|\n/', $statement);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '--') === 0 || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '/*') === 0) {
                continue;
            }
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Split SQL dump into executable statements (handles strings / comments lightly).
     * @return string[]
     */
    protected function split_sql_statements($sql) {
        $statements = array();
        $buffer = '';
        $in_single = FALSE;
        $in_double = FALSE;
        $in_backtick = FALSE;
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            $next = ($i + 1 < $len) ? $sql[$i + 1] : '';

            // Line comments
            if (!$in_single && !$in_double && !$in_backtick && $ch === '-' && $next === '-') {
                while ($i < $len && $sql[$i] !== "\n") {
                    $buffer .= $sql[$i];
                    $i++;
                }
                if ($i < $len) {
                    $buffer .= $sql[$i];
                }
                continue;
            }
            if (!$in_single && !$in_double && !$in_backtick && $ch === '#') {
                while ($i < $len && $sql[$i] !== "\n") {
                    $buffer .= $sql[$i];
                    $i++;
                }
                if ($i < $len) {
                    $buffer .= $sql[$i];
                }
                continue;
            }

            // Block comments
            if (!$in_single && !$in_double && !$in_backtick && $ch === '/' && $next === '*') {
                $buffer .= $ch . $next;
                $i += 2;
                while ($i < $len - 1 && !($sql[$i] === '*' && $sql[$i + 1] === '/')) {
                    $buffer .= $sql[$i];
                    $i++;
                }
                if ($i < $len - 1) {
                    $buffer .= '*/';
                    $i++;
                }
                continue;
            }

            if ($ch === "'" && !$in_double && !$in_backtick) {
                // Escaped quote '' or \'
                if ($in_single && $next === "'") {
                    $buffer .= "''";
                    $i++;
                    continue;
                }
                $in_single = !$in_single;
                $buffer .= $ch;
                continue;
            }
            if ($ch === '"' && !$in_single && !$in_backtick) {
                $in_double = !$in_double;
                $buffer .= $ch;
                continue;
            }
            if ($ch === '`' && !$in_single && !$in_double) {
                $in_backtick = !$in_backtick;
                $buffer .= $ch;
                continue;
            }

            if ($ch === ';' && !$in_single && !$in_double && !$in_backtick) {
                $statements[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $ch;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }

    public function format_bytes($bytes) {
        $bytes = (float) $bytes;
        if ($bytes < 1024) {
            return round($bytes) . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1048576, 2) . ' MB';
    }
}
