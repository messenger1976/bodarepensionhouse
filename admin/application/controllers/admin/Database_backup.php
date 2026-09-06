<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH . 'core/Admin_Controller.php');
}

/**
 * Admin tool: create / upload / download / delete / restore .sql database backups.
 */
class Database_backup extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('backup_service');
        $this->load->library('activity_log');
        $this->load->helper(array('url', 'download'));
    }

    public function index() {
        $this->require_permission('view_database_backup');

        $data['title'] = 'Backup Database';
        $data['backups'] = $this->backup_service->list_backups();
        $data['vault_hint'] = 'application/backups/';
        $data['can_create'] = $this->has_permission('create_database_backup');
        $data['can_upload'] = $this->has_permission('upload_database_backup');
        $data['can_delete'] = $this->has_permission('delete_database_backup');
        $data['can_restore'] = $this->has_permission('restore_database_backup');
        $data['max_upload_label'] = '50 MB';

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/database_backup/index', $data);
        $this->load->view('admin/layout/footer');
    }

    public function create() {
        $this->require_permission('create_database_backup');

        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            redirect('database_backup');
            return;
        }

        $result = $this->backup_service->create_backup();
        if (!empty($result['success'])) {
            $this->activity_log->log('system', 'database_backup', 'create', 'Created backup ' . $result['filename'], array(
                'severity' => 'info',
                'metadata' => array('filename' => $result['filename']),
            ));
            $this->session->set_flashdata('success', 'Backup created: ' . $result['filename']);
        } else {
            $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Backup creation failed.');
        }
        redirect('database_backup');
    }

    public function upload() {
        $this->require_permission('upload_database_backup');

        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            redirect('database_backup');
            return;
        }

        $file = isset($_FILES['backup_file']) ? $_FILES['backup_file'] : NULL;
        $result = $this->backup_service->store_upload($file);
        if (!empty($result['success'])) {
            $this->activity_log->log('system', 'database_backup', 'upload', 'Uploaded backup ' . $result['filename'], array(
                'severity' => 'info',
                'metadata' => array('filename' => $result['filename']),
            ));
            $this->session->set_flashdata('success', 'Backup uploaded: ' . $result['filename']);
        } else {
            $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Upload failed.');
        }
        redirect('database_backup');
    }

    public function download($filename = '') {
        $this->require_permission('view_database_backup');

        $filename = rawurldecode((string) $filename);
        $path = $this->backup_service->get_download_path($filename);
        if ($path === FALSE) {
            $this->session->set_flashdata('error', 'Backup file not found.');
            redirect('database_backup');
            return;
        }

        $this->activity_log->log('system', 'database_backup', 'download', 'Downloaded backup ' . basename($path), array(
            'severity' => 'info',
            'metadata' => array('filename' => basename($path)),
        ));

        force_download(basename($path), file_get_contents($path));
    }

    public function delete($filename = '') {
        $this->require_permission('delete_database_backup');

        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            redirect('database_backup');
            return;
        }

        $filename = rawurldecode((string) $filename);
        $confirm = trim((string) $this->input->post('confirm_delete'));
        if (strtoupper($confirm) !== 'DELETE') {
            $this->session->set_flashdata('error', 'Type DELETE to confirm removing this backup file.');
            redirect('database_backup');
            return;
        }

        $result = $this->backup_service->delete_backup($filename);
        if (!empty($result['success'])) {
            $this->activity_log->log('system', 'database_backup', 'delete', 'Deleted backup ' . $filename, array(
                'severity' => 'warning',
                'metadata' => array('filename' => $filename),
            ));
            $this->session->set_flashdata('success', 'Backup deleted: ' . $filename);
        } else {
            $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Delete failed.');
        }
        redirect('database_backup');
    }

    public function restore($filename = '') {
        $this->require_permission('restore_database_backup');

        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            redirect('database_backup');
            return;
        }

        $filename = rawurldecode((string) $filename);
        $confirm = trim((string) $this->input->post('confirm_restore'));
        if (strtoupper($confirm) !== 'RESTORE') {
            $this->session->set_flashdata('error', 'Type RESTORE to confirm overwriting the live database.');
            redirect('database_backup');
            return;
        }

        $result = $this->backup_service->restore_backup($filename);
        if (!empty($result['success'])) {
            $this->activity_log->log('system', 'database_backup', 'restore', 'Restored database from ' . $filename, array(
                'severity' => 'critical',
                'metadata' => array(
                    'filename' => $filename,
                    'statements' => isset($result['statements']) ? $result['statements'] : NULL,
                ),
            ));
            $this->session->set_flashdata('success', isset($result['message']) ? $result['message'] : 'Database restored.');
        } else {
            $this->activity_log->log('system', 'database_backup', 'restore_failed', 'Restore failed for ' . $filename, array(
                'severity' => 'critical',
                'status' => 'failed',
                'metadata' => array(
                    'filename' => $filename,
                    'error' => isset($result['message']) ? $result['message'] : NULL,
                ),
            ));
            $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Restore failed.');
        }
        redirect('database_backup');
    }
}
