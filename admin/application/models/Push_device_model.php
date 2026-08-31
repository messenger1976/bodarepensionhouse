<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Push_device_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function upsert_token($fcm_token, $platform, $user_id = null, $device_label = null)
    {
        $fcm_token = trim((string) $fcm_token);
        if ($fcm_token === '') {
            return false;
        }

        $platform = strtolower(trim((string) $platform));
        if (!in_array($platform, ['android', 'ios', 'web'], true)) {
            $platform = 'android';
        }

        $now = date('Y-m-d H:i:s');
        $existing = $this->db
            ->where('fcm_token', $fcm_token)
            ->get('push_device_tokens')
            ->row();

        $payload = [
            'platform' => $platform,
            'device_label' => $device_label !== null && $device_label !== '' ? substr((string) $device_label, 0, 255) : null,
            'is_active' => 1,
            'last_seen_at' => $now,
            'updated_at' => $now,
        ];

        if ($user_id !== null) {
            $payload['user_id'] = (int) $user_id;
        }

        if ($existing) {
            $this->db->where('id', (int) $existing->id)->update('push_device_tokens', $payload);
            return (int) $existing->id;
        }

        $payload['fcm_token'] = $fcm_token;
        $payload['user_id'] = $user_id !== null ? (int) $user_id : null;
        $payload['created_at'] = $now;
        $this->db->insert('push_device_tokens', $payload);
        return (int) $this->db->insert_id();
    }

    public function deactivate_token($fcm_token)
    {
        $fcm_token = trim((string) $fcm_token);
        if ($fcm_token === '') {
            return false;
        }

        $this->db->where('fcm_token', $fcm_token)->update('push_device_tokens', [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function get_active_tokens_for_user($user_id)
    {
        return $this->db
            ->where('user_id', (int) $user_id)
            ->where('is_active', 1)
            ->get('push_device_tokens')
            ->result();
    }
}
