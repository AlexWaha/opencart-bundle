<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionAwEasyCheckoutModel extends Model
{
    public function addAbandonedOrder($data)
    {
        $this->db->query('INSERT INTO ' . DB_PREFIX . "aw_easy_abandoned SET 
        store_id = '" . (int) $data['store_id'] . "', 
        customer_id = '" . (int) $data['customer_id'] . "', 
        language_id = '" . (int) $this->config->get('config_language_id') . "', 
        email = '" . $this->db->escape($data['email']) . "', 
        telephone = '" . $this->db->escape($data['telephone']) . "', 
        firstname = '" . $this->db->escape($data['firstname']) . "', 
        lastname = '" . $this->db->escape($data['lastname']) . "', 
        products = '" . $this->db->escape(json_encode($data['products'])) . "', 
        created_at = NOW()");

        return $this->db->getLastId();
    }

    public function editAbandonedOrder($abandoned_id, $data)
    {
        if ($abandoned_id && $this->abandonedOrderExists($abandoned_id)) {
            $this->db->query('UPDATE ' . DB_PREFIX . "aw_easy_abandoned SET 
            language_id = '" . (int) $this->config->get('config_language_id') . "', 
            email = '" . $this->db->escape($data['email']) . "', 
            firstname = '" . $this->db->escape($data['firstname']) . "', 
            lastname = '" . $this->db->escape($data['lastname']) . "', 
            telephone = '" . $this->db->escape($data['telephone']) . "', 
            products = '" . $this->db->escape(json_encode($data['products'])) . "' 
            WHERE abandoned_id = '" . (int) $abandoned_id . "'");
        } else {
            $this->db->query('INSERT INTO ' . DB_PREFIX . "aw_easy_abandoned SET 
            store_id = '" . (int) $data['store_id'] . "', 
            customer_id = '" . (int) $data['customer_id'] . "', 
            language_id = '" . (int) $this->config->get('config_language_id') . "', 
            email = '" . $this->db->escape($data['email']) . "', 
            telephone = '" . $this->db->escape($data['telephone']) . "', 
            firstname = '" . $this->db->escape($data['firstname']) . "', 
            lastname = '" . $this->db->escape($data['lastname']) . "', 
            products = '" . $this->db->escape(json_encode($data['products'])) . "', 
            created_at = NOW()");

            $abandoned_id = $this->db->getLastId();
        }

        return $abandoned_id;
    }

    public function abandonedOrderExists($abandoned_id)
    {
        $query = $this->db->query('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "aw_easy_abandoned WHERE abandoned_id = '" . (int) $abandoned_id . "'");

        return $query->row['total'] > 0;
    }

    public function removeAbandonedOrder($abandoned_id)
    {
        if ($abandoned_id && $this->abandonedOrderExists($abandoned_id)) {
            $this->db->query('DELETE FROM ' . DB_PREFIX . "aw_easy_abandoned WHERE abandoned_id = '" . (int) $abandoned_id . "'");
        }
    }

    public function getCustomField($custom_field_id)
    {
        $query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'aw_ec_custom_field` custom_field 
        LEFT JOIN `' . DB_PREFIX . "aw_ec_custom_field_description` custom_field_description 
        ON (custom_field.custom_field_id = custom_field_description.custom_field_id) 
        WHERE custom_field.status = '1' AND custom_field.custom_field_id = '" . (int) $custom_field_id . "' 
        AND custom_field_description.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getCustomFields($location, $customer_group_id = 0)
    {
        $custom_field_data = [];

        $custom_field_query = $this->db->query("SELECT custom_field.*, custom_field_description.* FROM `" . DB_PREFIX . "aw_ec_custom_field` custom_field
        LEFT JOIN `" . DB_PREFIX . "aw_ec_custom_field_description` custom_field_description
        ON (custom_field.custom_field_id = custom_field_description.custom_field_id)
        WHERE custom_field.status = '1'
        AND custom_field_description.language_id = '" . (int) $this->config->get('config_language_id') . "'
        AND custom_field.location = '" . $this->db->escape($location) . "'
        AND (
            NOT EXISTS (
                SELECT 1 FROM `" . DB_PREFIX . "aw_ec_custom_field_customer_group` cfcg
                WHERE cfcg.custom_field_id = custom_field.custom_field_id
            )
            OR EXISTS (
                SELECT 1 FROM `" . DB_PREFIX . "aw_ec_custom_field_customer_group` cfcg2
                WHERE cfcg2.custom_field_id = custom_field.custom_field_id
                AND cfcg2.customer_group_id = '" . (int) $customer_group_id . "'
            )
        )
        ORDER BY custom_field.sort_order ASC");

        foreach ($custom_field_query->rows as $custom_field) {
            $custom_field_value_data = [];

            if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio' || $custom_field['type'] == 'checkbox') {
                $custom_field_value_query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'aw_ec_custom_field_value custom_field_value 
                LEFT JOIN ' . DB_PREFIX . "aw_ec_custom_field_value_description custom_field_value_description 
                ON (custom_field_value.custom_field_value_id = custom_field_value_description.custom_field_value_id) 
                WHERE custom_field_value.custom_field_id = '" . (int) $custom_field['custom_field_id'] . "' 
                AND custom_field_value_description.language_id = '" . (int) $this->config->get('config_language_id') . "' ORDER BY custom_field_value.sort_order ASC");

                foreach ($custom_field_value_query->rows as $custom_field_value) {
                    $custom_field_value_data[] = [
                        'custom_field_value_id' => $custom_field_value['custom_field_value_id'],
                        'name' => $custom_field_value['name'],
                    ];
                }
            }

            $custom_field_data[$custom_field['custom_field_id']] = [
                'custom_field_id' => $custom_field['custom_field_id'],
                'custom_field_value' => $custom_field_value_data,
                'name' => $custom_field['name'],
                'text_error' => trim($custom_field['text_error']),
                'type' => $custom_field['type'],
                'value' => $custom_field['value'],
                'validation' => $custom_field['validation'],
                'location' => $custom_field['location'],
                'required' => ! (empty($custom_field['required']) || $custom_field['required'] == 0),
                'sort_order' => $custom_field['sort_order'],
            ];
        }

        return $custom_field_data;
    }

    public function getCustomFieldValue($custom_field_value_id)
    {
        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'aw_ec_custom_field_value custom_field_value 
        LEFT JOIN ' . DB_PREFIX . "aw_ec_custom_field_value_description custom_field_value_description 
        ON (custom_field_value.custom_field_value_id = custom_field_value_description.custom_field_value_id) 
        WHERE custom_field_value.custom_field_value_id = '" . (int) $custom_field_value_id . "' 
        AND custom_field_value_description.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getCustomFieldValues($custom_field_id)
    {
        $custom_field_value_data = [];

        $custom_field_value_query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'aw_ec_custom_field_value custom_field_value 
        LEFT JOIN ' . DB_PREFIX . "aw_ec_custom_field_value_description custom_field_value_description 
        ON (custom_field_value.custom_field_value_id = custom_field_value_description.custom_field_value_id) 
        WHERE custom_field_value.custom_field_id = '" . (int) $custom_field_id . "' 
        AND custom_field_value_description.language_id = '" . (int) $this->config->get('config_language_id') . "' ORDER BY custom_field_value.sort_order ASC");

        foreach ($custom_field_value_query->rows as $custom_field_value) {
            $custom_field_value_data[$custom_field_value['custom_field_value_id']] = [
                'custom_field_value_id' => $custom_field_value['custom_field_value_id'],
                'name' => $custom_field_value['name'],
            ];
        }

        return $custom_field_value_data;
    }
}
