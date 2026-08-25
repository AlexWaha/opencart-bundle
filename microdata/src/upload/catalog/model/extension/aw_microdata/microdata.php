<?php

class ModelExtensionAwMicrodataMicrodata extends Model
{
    public function tableExists(string $table): bool
    {
        $query = $this->db->query(
            "SHOW TABLES LIKE '" . DB_PREFIX . $this->db->escape($table) . "'"
        );

        return $query->num_rows > 0;
    }

    public function getStoreReviews(int $limit = 10): array
    {
        if (!$this->tableExists('aw_review')) {
            return [];
        }

        $query = $this->db->query(
            "SELECT author, text, rating, date_added
             FROM `" . DB_PREFIX . "aw_review`
             WHERE status = '1'
             ORDER BY date_added DESC
             LIMIT " . (int)$limit
        );

        return $query->rows;
    }

    public function getStoreAggregateRating(): array
    {
        if (!$this->tableExists('aw_review')) {
            return ['avg' => 0, 'count' => 0];
        }

        $query = $this->db->query(
            "SELECT AVG(rating) AS avg, COUNT(*) AS count
             FROM `" . DB_PREFIX . "aw_review`
             WHERE status = '1'"
        );

        if ($query->num_rows && $query->row['count'] > 0) {
            return [
                'avg'   => round((float)$query->row['avg'], 1),
                'count' => (int)$query->row['count'],
            ];
        }

        return ['avg' => 0, 'count' => 0];
    }

    public function getProductReviews(int $productId, int $limit = 10): array
    {
        $query = $this->db->query(
            "SELECT author, text, rating, date_added
             FROM `" . DB_PREFIX . "review`
             WHERE product_id = '" . (int)$productId . "'
             AND status = '1'
             ORDER BY date_added DESC
             LIMIT " . (int)$limit
        );

        return $query->rows;
    }

    public function getProductAggregateRating(int $productId): array
    {
        $query = $this->db->query(
            "SELECT AVG(rating) AS avg, COUNT(*) AS count
             FROM `" . DB_PREFIX . "review`
             WHERE product_id = '" . (int)$productId . "'
             AND status = '1'"
        );

        if ($query->num_rows && $query->row['count'] > 0) {
            return [
                'avg'   => round((float)$query->row['avg'], 1),
                'count' => (int)$query->row['count'],
            ];
        }

        return ['avg' => 0, 'count' => 0];
    }

    /**
     * SQL expression for a product's effective price under the given source.
     *
     * base       - the raw product price
     * special    - the active special price when one exists, otherwise the product price
     * option_min - the effective price plus the cheapest buyable configuration: the sum,
     *              across required options only, of each option's own minimum modifier
     *
     * The expression always refers to the product table under the alias `p`.
     */
    private function effectivePriceExpression(string $priceSource): string
    {
        if ($priceSource !== 'special' && $priceSource !== 'option_min') {
            return 'p.price';
        }

        $customerGroupId = $this->customer && $this->customer->isLogged()
            ? (int)$this->customer->getGroupId()
            : (int)$this->config->get('config_customer_group_id');

        // Zero dates are handled the same way core catalog/model/catalog/product.php does it
        $special = "COALESCE((SELECT ps.price
                              FROM `" . DB_PREFIX . "product_special` ps
                              WHERE ps.product_id = p.product_id
                              AND ps.customer_group_id = '" . $customerGroupId . "'
                              AND (ps.date_start = '0000-00-00' OR ps.date_start <= NOW())
                              AND (ps.date_end = '0000-00-00' OR ps.date_end >= NOW())
                              ORDER BY ps.priority ASC, ps.price ASC
                              LIMIT 1), p.price)";

        if ($priceSource === 'special') {
            return $special;
        }

        // Optional options contribute nothing - the customer can decline them. Every required
        // option contributes its own cheapest value, so two required options both count.
        $requiredOptionsMin = "COALESCE((SELECT SUM((SELECT MIN(CASE WHEN pov.price_prefix = '-' THEN -pov.price ELSE pov.price END)
                                                     FROM `" . DB_PREFIX . "product_option_value` pov
                                                     WHERE pov.product_option_id = po.product_option_id))
                                         FROM `" . DB_PREFIX . "product_option` po
                                         WHERE po.product_id = p.product_id
                                         AND po.required = '1'), 0)";

        return "(" . $special . " + " . $requiredOptionsMin . ")";
    }

    /**
     * Aggregate a per-product effective price sub-select into the range contract.
     * Values at or below zero are skipped.
     */
    private function aggregateRange(string $innerSql): array
    {
        $query = $this->db->query(
            "SELECT MIN(t.effective) AS low, MAX(t.effective) AS high, COUNT(*) AS count
             FROM (" . $innerSql . ") t
             WHERE t.effective > 0"
        );

        if ($query->num_rows && $query->row['count'] > 0) {
            return [
                'low'   => round((float)$query->row['low'], 2),
                'high'  => round((float)$query->row['high'], 2),
                'count' => (int)$query->row['count'],
            ];
        }

        return ['low' => 0, 'high' => 0, 'count' => 0];
    }

    public function getCategoryPriceRange(int $categoryId, string $priceSource = 'base'): array
    {
        return $this->aggregateRange(
            "SELECT " . $this->effectivePriceExpression($priceSource) . " AS effective
             FROM `" . DB_PREFIX . "product` p
             LEFT JOIN `" . DB_PREFIX . "product_to_category` p2c ON (p.product_id = p2c.product_id)
             LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)
             WHERE p2c.category_id = '" . (int)$categoryId . "'
             AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
             AND p.status = '1'
             AND p.price > 0
             AND p.date_available <= NOW()"
        );
    }

    public function getCategoryProductCount(int $categoryId): int
    {
        $query = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM `" . DB_PREFIX . "product` p
             LEFT JOIN `" . DB_PREFIX . "product_to_category` p2c ON (p.product_id = p2c.product_id)
             LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)
             WHERE p2c.category_id = '" . (int)$categoryId . "'
             AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
             AND p.status = '1'
             AND p.date_available <= NOW()"
        );

        return (int)$query->row['total'];
    }

    public function getLandingPriceRange(int $landingPageId, string $priceSource = 'base'): array
    {
        if (!$this->tableExists('aw_landing_page_to_product')) {
            return ['low' => 0, 'high' => 0, 'count' => 0];
        }

        return $this->aggregateRange(
            "SELECT " . $this->effectivePriceExpression($priceSource) . " AS effective
             FROM `" . DB_PREFIX . "aw_landing_page_to_product` lp2p
             LEFT JOIN `" . DB_PREFIX . "product` p ON (lp2p.product_id = p.product_id)
             WHERE lp2p.landing_page_id = '" . (int)$landingPageId . "'
             AND p.status = '1'
             AND p.price > 0"
        );
    }

    public function getManufacturerPriceRange(int $manufacturerId, string $priceSource = 'base'): array
    {
        return $this->aggregateRange(
            "SELECT " . $this->effectivePriceExpression($priceSource) . " AS effective
             FROM `" . DB_PREFIX . "product` p
             LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)
             WHERE p.manufacturer_id = '" . (int)$manufacturerId . "'
             AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
             AND p.status = '1'
             AND p.price > 0
             AND p.date_available <= NOW()"
        );
    }

    public function getSpecialPriceRange(): array
    {
        $query = $this->db->query(
            "SELECT MIN(ps.price) AS low, MAX(ps.price) AS high, COUNT(DISTINCT ps.product_id) AS count
             FROM `" . DB_PREFIX . "product_special` ps
             LEFT JOIN `" . DB_PREFIX . "product` p ON (ps.product_id = p.product_id)
             LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)
             WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
             AND p.status = '1'
             AND ps.price > 0
             AND p.date_available <= NOW()
             AND (ps.date_start = '0000-00-00' OR ps.date_start <= NOW())
             AND (ps.date_end = '0000-00-00' OR ps.date_end >= NOW())"
        );

        if ($query->num_rows && $query->row['count'] > 0) {
            return [
                'low'   => round((float)$query->row['low'], 2),
                'high'  => round((float)$query->row['high'], 2),
                'count' => (int)$query->row['count'],
            ];
        }

        return ['low' => 0, 'high' => 0, 'count' => 0];
    }

    public function getSearchPriceRange(string $search, int $categoryId = 0, bool $subCategory = false, bool $description = false): array
    {
        $search = trim($search);

        if (!$search) {
            return ['low' => 0, 'high' => 0, 'count' => 0];
        }

        $storeId = (int)$this->config->get('config_store_id');
        $langId = (int)$this->config->get('config_language_id');

        $sql = "SELECT MIN(p.price) AS low, MAX(p.price) AS high, COUNT(DISTINCT p.product_id) AS count
                FROM `" . DB_PREFIX . "product` p
                LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)
                LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)";

        if ($categoryId) {
            $sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` p2c ON (p.product_id = p2c.product_id)";

            if ($subCategory) {
                $sql .= " LEFT JOIN `" . DB_PREFIX . "category_path` cp ON (p2c.category_id = cp.category_id)";
            }
        }

        $sql .= " WHERE p2s.store_id = '" . $storeId . "'
                  AND pd.language_id = '" . $langId . "'
                  AND p.status = '1'
                  AND p.price > 0
                  AND p.date_available <= NOW()";

        $implode = [];
        $words = explode(' ', $search);

        foreach ($words as $word) {
            $word = trim($word);
            if ($word) {
                $escaped = $this->db->escape($word);
                $implode[] = "pd.name LIKE '%" . $escaped . "%'";
            }
        }

        if ($implode) {
            $sql .= " AND (" . implode(" AND ", $implode);

            if ($description) {
                foreach ($words as $word) {
                    $word = trim($word);
                    if ($word) {
                        $sql .= " OR pd.description LIKE '%" . $this->db->escape($word) . "%'";
                    }
                }
            }

            $sql .= ")";
        }

        if ($categoryId) {
            if ($subCategory) {
                $sql .= " AND cp.path_id = '" . (int)$categoryId . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$categoryId . "'";
            }
        }

        $query = $this->db->query($sql);

        if ($query->num_rows && $query->row['count'] > 0) {
            return [
                'low'   => round((float)$query->row['low'], 2),
                'high'  => round((float)$query->row['high'], 2),
                'count' => (int)$query->row['count'],
            ];
        }

        return ['low' => 0, 'high' => 0, 'count' => 0];
    }

    public function getHomepagePriceRange(): array
    {
        $query = $this->db->query(
            "SELECT MIN(p.price) AS low, MAX(p.price) AS high, COUNT(*) AS count
             FROM `" . DB_PREFIX . "product` p
             LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)
             WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
             AND p.status = '1'
             AND p.price > 0
             AND p.date_available <= NOW()"
        );

        if ($query->num_rows && $query->row['count'] > 0) {
            return [
                'low'   => round((float)$query->row['low'], 2),
                'high'  => round((float)$query->row['high'], 2),
                'count' => (int)$query->row['count'],
            ];
        }

        return ['low' => 0, 'high' => 0, 'count' => 0];
    }

    public function getCompositeRating(): array
    {
        $hasStoreReviews = $this->tableExists('aw_review');

        if ($hasStoreReviews) {
            $query = $this->db->query(
                "SELECT SUM(total) / SUM(cnt) AS avg, SUM(cnt) AS count FROM (
                    SELECT SUM(rating) AS total, COUNT(*) AS cnt
                    FROM `" . DB_PREFIX . "review`
                    WHERE status = '1'
                    UNION ALL
                    SELECT SUM(rating) AS total, COUNT(*) AS cnt
                    FROM `" . DB_PREFIX . "aw_review`
                    WHERE status = '1'
                ) AS combined"
            );
        } else {
            $query = $this->db->query(
                "SELECT AVG(rating) AS avg, COUNT(*) AS count
                 FROM `" . DB_PREFIX . "review`
                 WHERE status = '1'"
            );
        }

        if ($query->num_rows && $query->row['count'] > 0) {
            return [
                'avg'   => round((float)$query->row['avg'], 1),
                'count' => (int)$query->row['count'],
            ];
        }

        return ['avg' => 0, 'count' => 0];
    }

    public function getFaqItems(): array
    {
        if (!$this->tableExists('aw_faq') || !$this->tableExists('aw_faq_description')) {
            return [];
        }

        $languageId = (int)$this->config->get('config_language_id');

        $query = $this->db->query(
            "SELECT fd.question, fd.answer
             FROM `" . DB_PREFIX . "aw_faq` f
             LEFT JOIN `" . DB_PREFIX . "aw_faq_description` fd ON (f.faq_id = fd.faq_id)
             WHERE f.status = '1'
             AND fd.language_id = '" . (int)$languageId . "'
             ORDER BY f.sort_order ASC"
        );

        return $query->rows;
    }
}
