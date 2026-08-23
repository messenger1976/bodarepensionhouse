<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice_item_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_invoice($invoice_id) {
        $this->db->where('invoice_id', (int) $invoice_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('invoice_items')->result();
    }

    public function get($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('invoice_items')->row();
    }

    public function create($data) {
        if (!isset($data['total_price'])) {
            $qty = isset($data['quantity']) ? (float) $data['quantity'] : 1;
            $price = isset($data['unit_price']) ? (float) $data['unit_price'] : 0;
            $data['total_price'] = round($qty * $price, 2);
        }
        $this->db->insert('invoice_items', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        if (isset($data['quantity']) || isset($data['unit_price'])) {
            $item = $this->get($id);
            if ($item) {
                $qty = isset($data['quantity']) ? (float) $data['quantity'] : (float) $item->quantity;
                $price = isset($data['unit_price']) ? (float) $data['unit_price'] : (float) $item->unit_price;
                $data['total_price'] = round($qty * $price, 2);
            }
        }
        $this->db->where('id', (int) $id);
        return $this->db->update('invoice_items', $data);
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('invoice_items');
    }

    public function delete_by_invoice($invoice_id) {
        $this->db->where('invoice_id', (int) $invoice_id);
        return $this->db->delete('invoice_items');
    }

    public function get_subtotal($invoice_id) {
        $this->db->select_sum('total_price');
        $this->db->where('invoice_id', (int) $invoice_id);
        $row = $this->db->get('invoice_items')->row();
        return $row && $row->total_price ? (float) $row->total_price : 0.0;
    }
}
