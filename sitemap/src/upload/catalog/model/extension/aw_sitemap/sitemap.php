<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */
class ModelExtensionAwSitemapSitemap extends Model
{
    public function getTotalProducts(int $languageId): int
    {
        $query = $this->db->query("
            SELECT COUNT(DISTINCT p.product_id) AS total
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)
            LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)
            WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
              AND pd.language_id = '" . (int)$languageId . "'
              AND p.status = '1'
              AND p.date_available <= NOW()
        ");

        return (int)($query->row['total'] ?? 0);
    }

    public function getProducts(int $languageId, int $start, int $limit): array
    {
        $query = $this->db->query("
            SELECT p.product_id, p.image, p.date_added, p.date_modified, pd.name
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)
            LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)
            WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
              AND pd.language_id = '" . (int)$languageId . "'
              AND p.status = '1'
              AND p.date_available <= NOW()
            GROUP BY p.product_id
            ORDER BY p.product_id
            LIMIT " . (int)$start . ", " . (int)$limit . "
        ");

        return $query->rows;
    }

    public function getTotalCategories(int $languageId): int
    {
        $query = $this->db->query("
            SELECT COUNT(DISTINCT c.category_id) AS total
            FROM `" . DB_PREFIX . "category` c
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id)
            LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.category_id = c2s.category_id)
            WHERE cd.language_id = '" . (int)$languageId . "'
              AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
              AND c.status = '1'
        ");

        return (int)($query->row['total'] ?? 0);
    }

    public function getCategories(int $languageId, int $start, int $limit): array
    {
        $query = $this->db->query("
            SELECT c.category_id, c.date_added, c.date_modified,
                   (SELECT GROUP_CONCAT(cp.path_id ORDER BY cp.level SEPARATOR '_')
                    FROM `" . DB_PREFIX . "category_path` cp
                    WHERE cp.category_id = c.category_id) AS path
            FROM `" . DB_PREFIX . "category` c
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id)
            LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.category_id = c2s.category_id)
            WHERE cd.language_id = '" . (int)$languageId . "'
              AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
              AND c.status = '1'
            ORDER BY c.category_id
            LIMIT " . (int)$start . ", " . (int)$limit . "
        ");

        return $query->rows;
    }

    public function getTotalManufacturers(): int
    {
        $query = $this->db->query("
            SELECT COUNT(DISTINCT m.manufacturer_id) AS total
            FROM `" . DB_PREFIX . "manufacturer` m
            LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON (m.manufacturer_id = m2s.manufacturer_id)
            WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
        ");

        return (int)($query->row['total'] ?? 0);
    }

    public function getManufacturers(int $start, int $limit): array
    {
        $query = $this->db->query("
            SELECT m.manufacturer_id
            FROM `" . DB_PREFIX . "manufacturer` m
            LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON (m.manufacturer_id = m2s.manufacturer_id)
            WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
            ORDER BY m.manufacturer_id
            LIMIT " . (int)$start . ", " . (int)$limit . "
        ");

        return $query->rows;
    }

    public function getTotalInformations(int $languageId): int
    {
        $query = $this->db->query("
            SELECT COUNT(DISTINCT i.information_id) AS total
            FROM `" . DB_PREFIX . "information` i
            LEFT JOIN `" . DB_PREFIX . "information_description` id ON (i.information_id = id.information_id)
            LEFT JOIN `" . DB_PREFIX . "information_to_store` i2s ON (i.information_id = i2s.information_id)
            WHERE id.language_id = '" . (int)$languageId . "'
              AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
              AND i.status = '1'
        ");

        return (int)($query->row['total'] ?? 0);
    }

    public function getInformations(int $languageId, int $start, int $limit): array
    {
        $query = $this->db->query("
            SELECT i.information_id
            FROM `" . DB_PREFIX . "information` i
            LEFT JOIN `" . DB_PREFIX . "information_description` id ON (i.information_id = id.information_id)
            LEFT JOIN `" . DB_PREFIX . "information_to_store` i2s ON (i.information_id = i2s.information_id)
            WHERE id.language_id = '" . (int)$languageId . "'
              AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
              AND i.status = '1'
            ORDER BY i.sort_order, i.information_id
            LIMIT " . (int)$start . ", " . (int)$limit . "
        ");

        return $query->rows;
    }
}
