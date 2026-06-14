<?php

/**
 * Viewed Products - catalog model
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwViewed extends Model
{
    public function isProductViewable(int $productId): bool
    {
        $query = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product` p
            JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)
            WHERE p.product_id = '" . (int) $productId . "'
            AND p.status = '1'
            AND p.date_available <= NOW()
            AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'
            LIMIT 1");

        return (bool) $query->num_rows;
    }

    public function addViewedProduct(string $sessionToken, int $productId, int $productLimit): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_viewed`
            WHERE session_token = '" . $this->db->escape($sessionToken) . "' AND product_id = '" . (int) $productId . "'");

        $count = $this->getTotalViewedProduct($sessionToken);

        if (($count + 1) > $productLimit) {
            $deleteLimit = ($count - $productLimit) + 1;
            $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_viewed`
                WHERE session_token = '" . $this->db->escape($sessionToken) . "'
                ORDER BY date_added ASC LIMIT " . (int) $deleteLimit);
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_viewed` SET
            session_token = '" . $this->db->escape($sessionToken) . "',
            customer_id = '" . (int) $this->customer->getId() . "',
            product_id = '" . (int) $productId . "',
            store_id = '" . (int) $this->config->get('config_store_id') . "',
            date_added = NOW()");
    }

    public function mergeCustomer(string $sessionToken): void
    {
        $customerId = (int) $this->customer->getId();

        if (!$customerId) {
            return;
        }

        $this->db->query("UPDATE `" . DB_PREFIX . "aw_viewed`
            SET customer_id = '" . $customerId . "'
            WHERE session_token = '" . $this->db->escape($sessionToken) . "'");
    }

    public function deleteOldViewedProduct(int $days): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_viewed`
            WHERE date_added < DATE_SUB(NOW(), INTERVAL " . (int) $days . " DAY)");
    }

    public function deleteViewedProduct(int $productId): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_viewed`
            WHERE customer_id = '" . (int) $this->customer->getId() . "' AND product_id = '" . (int) $productId . "'");
    }

    public function getViewedProductIds(int $start = 0, int $limit = 0, int $excludeProductId = 0, string $sessionToken = ''): array
    {
        $sql = "SELECT DISTINCT product_id FROM `" . DB_PREFIX . "aw_viewed`";

        if ($this->customer->isLogged()) {
            $sql .= " WHERE customer_id = '" . (int) $this->customer->getId() . "'";
        } else {
            $sql .= " WHERE session_token = '" . $this->db->escape($sessionToken) . "'";
        }

        $sql .= " AND store_id = '" . (int) $this->config->get('config_store_id') . "'";

        if ($excludeProductId) {
            $sql .= " AND product_id <> '" . (int) $excludeProductId . "'";
        }

        $sql .= " GROUP BY product_id ORDER BY MAX(viewed_id) DESC";

        if ($limit > 0) {
            if ($start < 0) {
                $start = 0;
            }
            $sql .= " LIMIT " . (int) $start . "," . (int) $limit;
        }

        $query = $this->db->query($sql);

        return array_map('intval', array_column($query->rows, 'product_id'));
    }

    public function getTotalViewedProduct(string $sessionToken = ''): int
    {
        if ($this->customer->isLogged()) {
            $query = $this->db->query("SELECT COUNT(DISTINCT product_id) AS total FROM `" . DB_PREFIX . "aw_viewed`
                WHERE customer_id = '" . (int) $this->customer->getId() . "'
                AND store_id = '" . (int) $this->config->get('config_store_id') . "'");
        } else {
            $query = $this->db->query("SELECT COUNT(DISTINCT product_id) AS total FROM `" . DB_PREFIX . "aw_viewed`
                WHERE session_token = '" . $this->db->escape($sessionToken) . "'
                AND store_id = '" . (int) $this->config->get('config_store_id') . "'");
        }

        return (int) $query->row['total'];
    }
}
