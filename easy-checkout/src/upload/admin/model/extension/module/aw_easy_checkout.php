<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwEasyCheckout extends Model
{
    public function getCountries($data = [])
    {
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'country';

        if (! empty($data['filter_name'])) {
            $sql .= " WHERE name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sql .= ' ORDER BY name ASC';
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

    public function getPaymentMethods(): array
    {
        $query = $this->db->query('SELECT code FROM  `' . DB_PREFIX . "extension` WHERE type = 'payment'");


        $payments = [];

        if ($query->num_rows) {
            foreach ($query->rows as $payment) {
                if ($this->awCore->isLegacy()) {
                    $this->load->language('extension/payment/' . $payment['code']);
                    $heading_title = $this->language->get('heading_title');
                } else {
                    $this->load->language('extension/payment/' . $payment['code'], 'extension');
                    $heading_title = $this->language->get('extension')->get('heading_title');
                }

                $payments[] = [
                    'name' => $heading_title,
                    'code' => $payment['code'],
                ];
            }
        }

        return $payments;
    }

    public function deleteSetting($code, $store_id = 0)
    {
        $this->db->query('DELETE FROM ' . DB_PREFIX . "setting WHERE store_id = '" . (int) $store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
    }

    public function install()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . 'aw_easy_abandoned (`abandoned_id` int(11) NOT NULL AUTO_INCREMENT,`store_id` int(11),`customer_id` int(11),`email` varchar(765),`email_sent_at` datetime,`sms_sent_at` datetime,`telephone` varchar(765),`firstname` varchar(96),`lastname` varchar(96),`products` text,`language_id` int(11),`viewed` int(1),`created_at` datetime,PRIMARY KEY (`abandoned_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'aw_ec_custom_field` (`custom_field_id` INT(11) NOT NULL AUTO_INCREMENT,`type` VARCHAR(32) NOT NULL,`value` TEXT NOT NULL,`validation` VARCHAR(255) NOT NULL,`location` VARCHAR(15) NOT NULL,`status` TINYINT(1) NOT NULL,`required` TINYINT(1) NOT NULL DEFAULT 0,`save_to_order` TINYINT(1) NOT NULL,`sort_order` INT(3) NOT NULL,PRIMARY KEY (`custom_field_id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'aw_ec_custom_field_customer_group` (`custom_field_id` INT(11) NOT NULL,`customer_group_id` INT(11) NOT NULL,PRIMARY KEY (`custom_field_id`,`customer_group_id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'aw_ec_custom_field_description` (`custom_field_id` INT(11) NOT NULL,`language_id` INT(11) NOT NULL,`name` VARCHAR(128) NOT NULL,`text_error` TEXT NOT NULL,PRIMARY KEY (`custom_field_id`,`language_id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'aw_ec_custom_field_value` (`custom_field_value_id` INT(11) NOT NULL AUTO_INCREMENT,`custom_field_id` INT(11) NOT NULL,`sort_order` INT(3) NOT NULL,PRIMARY KEY (`custom_field_value_id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'aw_ec_custom_field_value_description` (`custom_field_value_id` INT(11) NOT NULL,`language_id` INT(11) NOT NULL,`custom_field_id` INT(11) NOT NULL,`name` VARCHAR(128) NOT NULL,PRIMARY KEY (`custom_field_value_id`,`language_id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
    }

    public function addCustomField($data)
    {
        $this->db->query('INSERT INTO `' . DB_PREFIX . "aw_ec_custom_field` SET
            type = '" . $this->db->escape($data['type']) . "',
            value = '" . $this->db->escape($data['value']) . "',
            validation = '" . $this->db->escape($data['validation']) . "',
            location = '" . $this->db->escape($data['location']) . "',
            status = '" . (int) $data['status'] . "',
            required = '" . (int) ($data['required'] ?? 0) . "',
            save_to_order = '" . (int) $data['save_to_order'] . "'");

        $customFieldId = $this->db->getLastId();

        if (isset($data['custom_field_description']['name'])) {
            foreach ($data['custom_field_description']['name'] as $languageId => $name) {
                $textError = $data['custom_field_description']['text_error'][$languageId] ?? '';
                $this->db->query('INSERT INTO ' . DB_PREFIX . "aw_ec_custom_field_description SET
                    custom_field_id = '" . (int) $customFieldId . "',
                    language_id = '" . (int) $languageId . "',
                    name = '" . $this->db->escape($name) . "',
                    text_error = '" . $this->db->escape($textError) . "'");
            }
        }

        if (isset($data['custom_field_customer_group'])) {
            foreach ($data['custom_field_customer_group'] as $custom_field_customer_group) {
                if (isset($custom_field_customer_group['customer_group_id'])) {
                    $this->db->query('INSERT INTO ' . DB_PREFIX . "aw_ec_custom_field_customer_group SET
                        custom_field_id = '" . (int) $customFieldId . "',
                        customer_group_id = '" . (int) $custom_field_customer_group['customer_group_id'] . "'");
                }
            }
        }

        if (isset($data['custom_field_value'])) {
            foreach ($data['custom_field_value'] as $custom_field_value) {
                $this->db->query('INSERT INTO ' . DB_PREFIX . "aw_ec_custom_field_value SET custom_field_id = '" . (int) $customFieldId . "', sort_order = '" . (int) $custom_field_value['sort_order'] . "'");
                $custom_field_value_id = $this->db->getLastId();

                foreach (
                    $custom_field_value['custom_field_value_description'] as $languageId => $custom_field_value_description
                ) {
                    $this->db->query('INSERT INTO ' . DB_PREFIX . "aw_ec_custom_field_value_description SET 
                        custom_field_value_id = '" . (int) $custom_field_value_id . "', 
                        language_id = '" . (int) $languageId . "', 
                        custom_field_id = '" . (int) $customFieldId . "', 
                        name = '" . $this->db->escape($custom_field_value_description['name']) . "'");
                }
            }
        }

        return $customFieldId;
    }

    public function editCustomField($customFieldId, $data)
    {
        $this->db->query('UPDATE `' . DB_PREFIX . "aw_ec_custom_field` SET
        type = '" . $this->db->escape($data['type']) . "',
        value = '" . $this->db->escape($data['value']) . "',
        validation = '" . $this->db->escape($data['validation']) . "',
        location = '" . $this->db->escape($data['location']) . "',
        status = '" . (int) $data['status'] . "',
        required = '" . (int) ($data['required'] ?? 0) . "',
        save_to_order = '" . (int) $data['save_to_order'] . "'
        WHERE custom_field_id = '" . (int) $customFieldId . "'");

        $this->db->query('DELETE FROM ' . DB_PREFIX . "aw_ec_custom_field_description WHERE custom_field_id = '" . (int) $customFieldId . "'");

        if (isset($data['custom_field_description']['name'])) {
            foreach ($data['custom_field_description']['name'] as $languageId => $name) {
                $textError = $data['custom_field_description']['text_error'][$languageId] ?? '';
                $this->db->query('INSERT INTO ' . DB_PREFIX . "aw_ec_custom_field_description SET
                custom_field_id = '" . (int) $customFieldId . "',
                language_id = '" . (int) $languageId . "',
                name = '" . $this->db->escape($name) . "',
                text_error = '" . $this->db->escape($textError) . "'");
            }
        }

        $this->db->query('DELETE FROM ' . DB_PREFIX . "aw_ec_custom_field_customer_group WHERE custom_field_id = '" . (int) $customFieldId . "'");

        if (isset($data['custom_field_customer_group'])) {
            foreach ($data['custom_field_customer_group'] as $custom_field_customer_group) {
                if (isset($custom_field_customer_group['customer_group_id'])) {
                    $this->db->query('INSERT INTO ' . DB_PREFIX . "aw_ec_custom_field_customer_group SET
                    custom_field_id = '" . (int) $customFieldId . "',
                    customer_group_id = '" . (int) $custom_field_customer_group['customer_group_id'] . "'");
                }
            }
        }

        $this->db->query('DELETE FROM ' . DB_PREFIX . "aw_ec_custom_field_value WHERE custom_field_id = '" . (int) $customFieldId . "'");
        $this->db->query('DELETE FROM ' . DB_PREFIX . "aw_ec_custom_field_value_description WHERE custom_field_id = '" . (int) $customFieldId . "'");

        if (isset($data['custom_field_value'])) {
            foreach ($data['custom_field_value'] as $custom_field_value) {
                if ($custom_field_value['custom_field_value_id']) {
                    $this->db->query('INSERT INTO ' . DB_PREFIX . "aw_ec_custom_field_value SET 
                    custom_field_value_id = '" . (int) $custom_field_value['custom_field_value_id'] . "', 
                    custom_field_id = '" . (int) $customFieldId . "', 
                    sort_order = '" . (int) $custom_field_value['sort_order'] . "'");
                } else {
                    $this->db->query('INSERT INTO ' . DB_PREFIX . "aw_ec_custom_field_value SET 
                    custom_field_id = '" . (int) $customFieldId . "', 
                    sort_order = '" . (int) $custom_field_value['sort_order'] . "'");
                }

                $customFieldValueId = $this->db->getLastId();

                foreach (
                    $custom_field_value['custom_field_value_description'] as $languageId => $custom_field_value_description
                ) {
                    $this->db->query('INSERT INTO ' . DB_PREFIX . "aw_ec_custom_field_value_description SET 
                    custom_field_value_id = '" . (int) $customFieldValueId . "', 
                    language_id = '" . (int) $languageId . "', 
                    custom_field_id = '" . (int) $customFieldId . "', 
                    name = '" . $this->db->escape($custom_field_value_description['name']) . "'");
                }
            }
        }
    }

    public function deleteCustomField($customFieldId)
    {
        $this->db->query('DELETE FROM `' . DB_PREFIX . "aw_ec_custom_field` WHERE custom_field_id = '" . (int) $customFieldId . "'");
        $this->db->query('DELETE FROM `' . DB_PREFIX . "aw_ec_custom_field_description` WHERE custom_field_id = '" . (int) $customFieldId . "'");
        $this->db->query('DELETE FROM `' . DB_PREFIX . "aw_ec_custom_field_customer_group` WHERE custom_field_id = '" . (int) $customFieldId . "'");
        $this->db->query('DELETE FROM `' . DB_PREFIX . "aw_ec_custom_field_value` WHERE custom_field_id = '" . (int) $customFieldId . "'");
        $this->db->query('DELETE FROM `' . DB_PREFIX . "aw_ec_custom_field_value_description` WHERE custom_field_id = '" . (int) $customFieldId . "'");
    }

    public function getCustomField($customFieldId)
    {
        $query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'aw_ec_custom_field` custom_field
        LEFT JOIN ' . DB_PREFIX . "aw_ec_custom_field_description custom_field_description ON (custom_field.custom_field_id = custom_field_description.custom_field_id)
        WHERE custom_field.custom_field_id = '" . (int) $customFieldId . "'
        AND custom_field_description.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getCustomFieldName($customFieldId)
    {
        $query = $this->db->query('SELECT `name` FROM ' . DB_PREFIX . "aw_ec_custom_field_description WHERE custom_field_id = '" . (int) $customFieldId . "' AND language_id = '" . (int) $this->config->get('config_language_id') . "'");

        return $query->row['name'] ?? '';
    }

    public function getCustomFields()
    {
        $sql = 'SELECT * FROM `' . DB_PREFIX . 'aw_ec_custom_field` custom_field
        LEFT JOIN ' . DB_PREFIX . "aw_ec_custom_field_description custom_field_description ON (custom_field.custom_field_id = custom_field_description.custom_field_id)
        WHERE custom_field_description.language_id = '" . (int) $this->config->get('config_language_id') . "' ORDER BY custom_field.location DESC";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getCustomFieldDescriptions($customFieldId): array
    {
        $result = [
            'name' => [],
            'text_error' => [],
        ];
        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . "aw_ec_custom_field_description WHERE custom_field_id = '" . (int) $customFieldId . "'");

        foreach ($query->rows as $query) {
            $result['name'][$query['language_id']] = $query['name'];
            $result['text_error'][$query['language_id']] = $query['text_error'];
        }

        return $result;
    }

    public function getCustomFieldCustomerGroups($customFieldId)
    {
        $query = $this->db->query('SELECT * FROM `' . DB_PREFIX . "aw_ec_custom_field_customer_group` WHERE custom_field_id = '" . (int) $customFieldId . "'");

        return $query->rows;
    }

    public function getCustomFieldValueDescriptions($customFieldId): array
    {
        $result = [];

        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . "aw_ec_custom_field_value WHERE custom_field_id = '" . (int) $customFieldId . "'");

        foreach ($query->rows as $custom_field_value) {
            $customFieldValueDescriptions = [];

            $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . "aw_ec_custom_field_value_description WHERE custom_field_value_id = '" . (int) $custom_field_value['custom_field_value_id'] . "'");

            foreach ($query->rows as $custom_field_value_description) {
                $customFieldValueDescriptions[$custom_field_value_description['language_id']] = ['name' => $custom_field_value_description['name']];
            }

            $result[] = [
                'custom_field_value_id' => $custom_field_value['custom_field_value_id'],
                'custom_field_value_description' => $customFieldValueDescriptions,
                'sort_order' => $custom_field_value['sort_order'],
            ];
        }

        return $result;
    }
}
