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

    /**
     * Index the columns the listing price-source sub-selects key on. Stock OpenCart
     * ships neither, and `special` is the default price source, so every store pays.
     * Idempotent and non-fatal: an existing index or a missing ALTER grant is ignored.
     */
    public function createPriceSourceIndexes(): void
    {
        // table => [index name, column] - one entry per clause the shipped SQL filters on
        $indexes = [
            // special-price sub-select
            'product_special'      => ['idx_aw_microdata_product_special_product', 'product_id'],
            // outer required-options filter
            'product_option'       => ['idx_aw_microdata_product_option_product', 'product_id'],
            // inner per-option MIN - runs once per required option per product, the hot path
            'product_option_value' => ['idx_aw_microdata_product_option_value_option', 'product_option_id'],
        ];

        foreach ($indexes as $table => [$indexName, $column]) {
            try {
                $query = $this->db->query(
                    "SELECT COUNT(*) AS total
                     FROM `information_schema`.`STATISTICS`
                     WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = '" . DB_PREFIX . $this->db->escape($table) . "'
                     AND INDEX_NAME = '" . $this->db->escape($indexName) . "'"
                );

                if ((int)$query->row['total'] > 0) {
                    continue;
                }

                $this->db->query(
                    "ALTER TABLE `" . DB_PREFIX . $table . "`
                     ADD INDEX `" . $indexName . "` (`" . $column . "`)"
                );
            } catch (\Throwable $e) {
                // No ALTER rights or a concurrent creation - the queries still work, just slower.
                // Throwable, not Exception: an adapter-level Error here would abort install()
                // after the config row is written, leaving the module half-installed.
            }
        }
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
