<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwLandingPage extends Model
{
    public function getAllPages(): array
    {
        $languageId = (int) $this->config->get('config_language_id');
        $storeId = (int) $this->config->get('config_store_id');

        $query = $this->db->query("
        SELECT l.landing_page_id, ld.name
        FROM `" . DB_PREFIX . "aw_landing_page` l
        LEFT JOIN `" . DB_PREFIX . "aw_landing_page_description` ld
            ON l.landing_page_id = ld.landing_page_id
        LEFT JOIN `" . DB_PREFIX . "aw_landing_page_to_store` ls
            ON l.landing_page_id = ls.landing_page_id
        WHERE l.status = 1
          AND ld.language_id = '" . $languageId . "'
          AND ls.store_id = '" . $storeId . "'
        ORDER BY ld.name ASC
    ");

        return $query->rows;
    }

    /**
     * @param $landingPageId
     * @return mixed
     */
    public function getPage($landingPageId)
    {
        $query = $this->db->query(
            "SELECT l.landing_page_id,
                    l.status,
                    ld.name,
                    ld.short_description,
                    ld.description,
                    ld.meta_title,
                    ld.meta_description,
                    ld.meta_h1
             FROM `" . DB_PREFIX . "aw_landing_page` l
             LEFT JOIN `" . DB_PREFIX . "aw_landing_page_description` ld
                ON (l.landing_page_id = ld.landing_page_id)
            LEFT JOIN `" . DB_PREFIX . "aw_landing_page_to_store` l2s
                ON (l.landing_page_id = l2s.landing_page_id)
             WHERE l.landing_page_id = '" . (int)$landingPageId . "'
               AND ld.language_id = '" . (int)$this->config->get('config_language_id') . "'"
        );

        return $query->row;
    }

    /**
     * @param  int  $landingPageId
     * @return int
     */
    public function getTotalPageProducts(int $landingPageId): int
    {
        $languageId = (int)$this->config->get('config_language_id');
        $storeId = (int)$this->config->get('config_store_id');

        $query = $this->db->query("
        SELECT COUNT(DISTINCT p.product_id) AS total
        FROM `" . DB_PREFIX . "aw_landing_page_to_product` l2p
        LEFT JOIN `" . DB_PREFIX . "product` p ON l2p.product_id = p.product_id
        LEFT JOIN `" . DB_PREFIX . "product_description` pd ON p.product_id = pd.product_id
        LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON p.product_id = p2s.product_id
        WHERE l2p.landing_page_id = '" . (int)$landingPageId . "'
          AND p.status = 1
          AND p.date_available <= NOW()
          AND pd.language_id = '" . $languageId . "'
          AND p2s.store_id = '" . $storeId . "'
    ");

        return (int)($query->row['total'] ?? 0);
    }

    /**
     * @param  int  $landingPageId
     * @param  array  $data
     * @return array
     */
    public function getPageProducts(int $landingPageId, array $data = []): array
    {
        $customerGroupId = (int)$this->config->get('config_customer_group_id');
        $languageId = (int)$this->config->get('config_language_id');
        $storeId = (int)$this->config->get('config_store_id');

        $sql = "
        SELECT p.product_id,
               (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = 1 GROUP BY r1.product_id) AS rating,
               (SELECT price FROM " . DB_PREFIX . "product_discount pd2
                WHERE pd2.product_id = p.product_id
                  AND pd2.customer_group_id = '" . $customerGroupId . "'
                  AND pd2.quantity = 1
                  AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW())
                       AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW()))
                ORDER BY pd2.priority ASC, pd2.price ASC
                LIMIT 1) AS discount,
               (SELECT price FROM " . DB_PREFIX . "product_special ps
                WHERE ps.product_id = p.product_id
                  AND ps.customer_group_id = '" . $customerGroupId . "'
                  AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW())
                       AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))
                ORDER BY ps.priority ASC, ps.price ASC
                LIMIT 1) AS special
        FROM `" . DB_PREFIX . "aw_landing_page_to_product` l2p
        LEFT JOIN `" . DB_PREFIX . "product` p ON l2p.product_id = p.product_id
        LEFT JOIN `" . DB_PREFIX . "product_description` pd ON p.product_id = pd.product_id
        LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON p.product_id = p2s.product_id
        WHERE l2p.landing_page_id = '" . (int)$landingPageId . "'
          AND pd.language_id = '" . $languageId . "'
          AND p.status = 1
          AND p.date_available <= NOW()
          AND p2s.store_id = '" . $storeId . "'
        GROUP BY p.product_id
    ";

        $sortFields = [
            'pd.name',
            'p.model',
            'p.quantity',
            'p.price',
            'rating',
            'p.sort_order',
            'p.date_added'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sortFields, true)) {
            if (in_array($data['sort'], ['pd.name', 'p.model'], true)) {
                $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
            } elseif ($data['sort'] === 'p.price') {
                $sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
            } else {
                $sql .= " ORDER BY " . $data['sort'];
            }
        } else {
            $sql .= " ORDER BY p.sort_order";
        }

        $sql .= (isset($data['order']) && strtoupper($data['order']) === 'DESC')
            ? " DESC, LCASE(pd.name) DESC"
            : " ASC, LCASE(pd.name) ASC";

        if (isset($data['start']) || isset($data['limit'])) {
            $start = max(0, (int)($data['start'] ?? 0));
            $limit = max(1, (int)($data['limit'] ?? 20));
            $sql .= " LIMIT " . $start . ", " . $limit;
        }

        $this->load->model('catalog/product');

        $query = $this->db->query($sql);

        $products = [];

        foreach ($query->rows as $row) {
            $product = $this->model_catalog_product->getProduct($row['product_id']);

            if ($product) {
                $products[$product['product_id']] = $product;
            }
        }

        return $products;
    }
}
