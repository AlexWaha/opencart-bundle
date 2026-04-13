<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwMicrodata extends Model
{
    public function getRegisteredEvents(string $codePrefix): array
    {
        $query = $this->db->query(
            "SELECT `trigger`, `action`, `status` FROM `" . DB_PREFIX . "event` WHERE `code` LIKE '" . $this->db->escape($codePrefix) . "%' ORDER BY `event_id`"
        );

        return $query->rows;
    }

    public function getFirstProductId(): int
    {
        $query = $this->db->query(
            "SELECT product_id FROM `" . DB_PREFIX . "product` WHERE status = '1' ORDER BY product_id ASC LIMIT 1"
        );

        return $query->num_rows ? (int) $query->row['product_id'] : 0;
    }

    public function getFirstCategoryId(): int
    {
        $query = $this->db->query(
            "SELECT category_id FROM `" . DB_PREFIX . "category` WHERE status = '1' ORDER BY category_id ASC LIMIT 1"
        );

        return $query->num_rows ? (int) $query->row['category_id'] : 0;
    }
}
