<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */
class ModelExtensionFeedAwSitemap extends Model
{
    public function getTotalProducts(): int
    {
        $query = $this->db->query("
            SELECT COUNT(DISTINCT p.product_id) AS total
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)
            WHERE p2s.store_id = '0'
              AND p.status = '1'
        ");

        return (int)($query->row['total'] ?? 0);
    }
}
