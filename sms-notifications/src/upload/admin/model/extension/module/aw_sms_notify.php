<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 *
 * @link    https://alexwaha.com
 *
 * @email   support@alexwaha.com
 *
 * @license GPLv3
 */
class ModelExtensionModuleAwSmsNotify extends Model
{
    public function getOrderStatuses()
    {
        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . "order_status WHERE language_id = '" . (int) $this->config->get('config_language_id') . "' AND order_status_id <> '" . (int) $this->config->get('config_order_status_id') . "' ORDER BY name ASC");

        return $query->rows;
    }

    public function getPaymentList()
    {
        $query = $this->db->query('SELECT code FROM  `' . DB_PREFIX . "extension` WHERE type = 'payment'");

        $payments = [];

        if ($query->num_rows) {
            foreach ($query->rows as $payment) {
                if ($this->awCore->getVersion() > 23) {
                    $this->load->language('extension/payment/' . $payment['code'], 'extension');
                    $heading_title = $this->language->get('extension')->get('heading_title');
                }

                if ($this->awCore->getVersion() <= 23) {
                    $this->load->language('extension/payment/' . $payment['code']);
                    $heading_title = $this->language->get('heading_title');
                }

                $payments[] = [
                    'name' => $heading_title,
                    'code' => $payment['code'],
                ];
            }
        }

        return $payments;
    }

    public function getOrdersPhone($data = [])
    {
        $result = [];

        if (isset($data['selected'])) {
            foreach ($data['selected'] as $order_id) {
                $sql = 'SELECT telephone FROM `' . DB_PREFIX . "order` WHERE order_id = '" . (int) $order_id . "'";

                if (isset($data['sms_order_status']) && $data['sms_order_status']) {
                    $sql .= " AND order_status_id = '" . (int) $data['sms_order_status'] . "'";
                }

                $query = $this->db->query($sql);

                if ($query->num_rows && $query->row['telephone']) {
                    $result[] = preg_replace('/[^0-9,]/', '', $query->row['telephone']);
                }
            }
        } else {
            if (isset($data['sms_order_status']) && $data['sms_order_status']) {
                $sql = 'SELECT DISTINCT telephone FROM `' . DB_PREFIX . "order` WHERE order_status_id = '" . (int) $data['sms_order_status'] . "'";

                $query = $this->db->query($sql);

                if ($query->num_rows) {
                    foreach ($query->rows as $row) {
                        $result[] = preg_replace('/[^0-9,]/', '', $row['telephone']);
                    }
                }
            }
        }

        return $result;
    }

    public function addOrderHistory($order_id, $order_status_id, $message)
    {
        $this->db->query('INSERT INTO ' . DB_PREFIX . "order_history SET order_id = '" . (int) $order_id . "', order_status_id = '" . (int) $order_status_id . "', notify = '1', comment = '" . $this->db->escape($message) . "', date_added = NOW()");
    }
}
