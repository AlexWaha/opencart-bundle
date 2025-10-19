<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwEasyAbandoned extends Model
{
    public function getOrders($data = [])
    {
        $sql = "SELECT a.abandoned_id, a.email, a.email_sent_at, a.sms_sent_at, a.telephone, CONCAT(a.firstname, ' ', a.lastname) AS customer, a.created_at FROM `" . DB_PREFIX . 'aw_easy_abandoned` a';
        $sql .= " WHERE a.abandoned_id > '0'";
        $implode = [];

        if (! empty($data['filter_customer'])) {
            $implode[] = "CONCAT(a.firstname, ' ', a.lastname, ' ', a.email, ' ', a.telephone) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
        }
        if ($implode) {
            $sql .= ' AND ' . implode(' AND ', $implode);
        }

        if (! empty($data['filter_created_at'])) {
            $sql .= " AND DATE(a.created_at) = DATE('" . $this->db->escape($data['filter_created_at']) . "')";
        }

        $sort_data = [
            'o.order_id',
            'customer',
            'o.created_at',
        ];
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= ' ORDER BY ' . $data['sort'];
        } else {
            $sql .= ' ORDER BY a.abandoned_id';
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= ' DESC';
        } else {
            $sql .= ' ASC';
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= ' LIMIT ' . (int) $data['start'] . ',' . (int) $data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalOrders($data = [])
    {
        $sql = 'SELECT COUNT(*) AS total FROM `' . DB_PREFIX . 'aw_easy_abandoned`';
        $sql .= ' WHERE abandoned_id > 0';

        if (! empty($data['filter_abandoned_id'])) {
            $sql .= " AND filter_abandoned_id = '" . (int) $data['filter_abandoned_id'] . "'";
        }

        $implode = [];

        if (! empty($data['filter_customer'])) {
            $implode[] = "CONCAT(firstname, ' ', lastname, ' ', email, ' ', telephone) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
        }
        if ($implode) {
            $sql .= ' AND ' . implode(' AND ', $implode);
        }

        if (! empty($data['filter_created_at'])) {
            $sql .= " AND DATE(created_at) = DATE('" . $this->db->escape($data['filter_created_at']) . "')";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getTotalCountOrder()
    {
        $query = $this->db->query('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . 'aw_easy_abandoned WHERE email_sent_at IS NULL AND sms_sent_at IS NULL AND viewed IS NULL');

        return $query->row['total'] ?: 0;
    }

    public function deleteOrder($abandoned_id)
    {
        $this->db->query('DELETE FROM `' . DB_PREFIX . "aw_easy_abandoned` WHERE abandoned_id = '" . (int) $abandoned_id . "'");
    }

    public function getOrderInfo($abandoned_id)
    {
        $query = $this->db->query('SELECT * FROM `' . DB_PREFIX . "aw_easy_abandoned` WHERE abandoned_id = '" . (int) $abandoned_id . "'");
        $this->db->query('UPDATE ' . DB_PREFIX . "aw_easy_abandoned SET viewed = '1' WHERE abandoned_id = '" . (int) $abandoned_id . "'");

        return $query->row;
    }

    public function addStatusSendEmail($abandoned_id)
    {
        $this->db->query('UPDATE ' . DB_PREFIX . "aw_easy_abandoned SET email_sent_at = now() WHERE abandoned_id = '" . (int) $abandoned_id . "'");
        $query = $this->db->query('SELECT email_sent_at FROM `' . DB_PREFIX . "aw_easy_abandoned` WHERE abandoned_id = '" . (int) $abandoned_id . "'");

        return $query->row['email_sent_at'] ?: '';
    }

    public function addStatusSendSms($abandoned_id)
    {
        $this->db->query('UPDATE ' . DB_PREFIX . "aw_easy_abandoned SET sms_sent_at = now() WHERE abandoned_id = '" . (int) $abandoned_id . "'");
        $query = $this->db->query('SELECT sms_sent_at FROM `' . DB_PREFIX . "aw_easy_abandoned` WHERE abandoned_id = '" . (int) $abandoned_id . "'");

        return $query->row['sms_sent_at'] ?: '';
    }

    public function getOrdersByCustomerData($data)
    {
        $sql = "SELECT o.order_id, CONCAT(TRIM(o.firstname), ' ', TRIM(o.lastname)) AS customer, o.email, o.telephone, o.total, o.order_status_id, o.date_added, o.currency_code, o.currency_value";
        $sql .= ', (SELECT os.name FROM ' . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int) $this->config->get('config_language_id') . "') AS order_status";
        $sql .= ' FROM `' . DB_PREFIX . 'order` o';
        $sql .= " WHERE o.order_id > '0'";
        $implode = [];

        if (! empty($data['email'])) {
            $implode[] = "o.email LIKE '%" . $this->db->escape($data['email']) . "%'";
        }

        if (! empty($data['telephone'])) {
            $implode[] = "o.telephone LIKE '%" . $this->db->escape($data['telephone']) . "%'";
        }
        if ($implode) {
            $sql .= ' AND (' . implode(' OR ', $implode) . ')';
        }

        $sort_data = ['o.order_id'];
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= ' ORDER BY ' . $data['sort'];
        } else {
            $sql .= ' ORDER BY o.order_id';
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= ' DESC';
        } else {
            $sql .= ' ASC';
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getCustomers($data = [])
    {
        $sql = "SELECT *, CONCAT(TRIM(a.firstname), ' ', TRIM(a.lastname)) AS name FROM `" . DB_PREFIX . 'aw_easy_abandoned` a';
        $sql .= " WHERE a.abandoned_id > '0'";
        $implode = [];

        if (! empty($data['filter_name'])) {
            $implode[] = "CONCAT(a.firstname, ' ', a.lastname, ' ', a.email, ' ', a.telephone) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }
        if ($implode) {
            $sql .= ' AND ' . implode(' AND ', $implode);
        }

        $sort_data = ['abandoned_id'];
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= ' ORDER BY ' . $data['sort'];
        } else {
            $sql .= ' ORDER BY name';
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= ' DESC';
        } else {
            $sql .= ' ASC';
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if (isset($data['start']) && ($data['start'] < 0)) {
                $data['start'] = 0;
            }

            if (isset($data['limit']) && ($data['limit'] < 1)) {
                $data['limit'] = 20;
            }

            $sql .= ' LIMIT ' . (int) $data['start'] . ',' . (int) $data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function isTableExists()
    {
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "aw_easy_abandoned'");
        return $query->num_rows > 0;
    }
}
