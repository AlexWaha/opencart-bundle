<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwBuyerHistory extends Model
{
    public function getOrdersMeta(array $orderIds): array
    {
        $orderIds = array_filter(array_map('intval', $orderIds));

        if (! $orderIds) {
            return [];
        }

        $sql = "SELECT order_id, customer_id, email, telephone, order_status_id, total, currency_code, currency_value, date_added
                FROM `" . DB_PREFIX . "order`
                WHERE order_id IN (" . implode(',', $orderIds) . ")";

        $rows = [];

        foreach ($this->db->query($sql)->rows as $row) {
            $rows[(int) $row['order_id']] = $row;
        }

        return $rows;
    }

    public function getCustomerStats(array $cids, array $emails, array $phones): array
    {
        $cids = array_unique(array_filter(array_map('intval', $cids)));
        $emails = array_values(array_unique(array_filter(array_map('strtolower', $emails))));
        $phones = array_values(array_unique(array_filter($phones)));

        $where = [];

        if ($cids) {
            $where[] = "o.customer_id IN (" . implode(',', $cids) . ")";
        }

        if ($emails && $phones) {
            $emailsEsc = array_map(function ($e) {
                return "'" . $this->db->escape($e) . "'";
            }, $emails);
            // Column collation is case-insensitive, so plain IN keeps the email index usable.
            $where[] = "o.email IN (" . implode(',', $emailsEsc) . ")";
        }

        if (! $where) {
            return [];
        }

        $sql = "SELECT
                    o.customer_id,
                    LOWER(o.email) AS email_norm,
                    o.telephone AS phone_raw,
                    o.order_status_id,
                    os.name AS status_name,
                    o.currency_code,
                    o.currency_value,
                    COUNT(*) AS cnt,
                    SUM(o.total) AS sum_total
                FROM `" . DB_PREFIX . "order` o
                LEFT JOIN `" . DB_PREFIX . "order_status` os
                    ON os.order_status_id = o.order_status_id
                   AND os.language_id = '" . (int) $this->config->get('config_language_id') . "'
                WHERE (" . implode(' OR ', $where) . ")
                GROUP BY o.customer_id, email_norm, phone_raw, o.order_status_id, o.currency_code";

        return $this->db->query($sql)->rows;
    }

    public function getDuplicates(array $cids, array $emails, int $startTs, int $endTs, array $excludeIds): array
    {
        $cids = array_unique(array_filter(array_map('intval', $cids)));
        $emails = array_values(array_unique(array_filter(array_map('strtolower', $emails))));

        $where = [];

        if ($cids) {
            $where[] = "o.customer_id IN (" . implode(',', $cids) . ")";
        }

        if ($emails) {
            $emailsEsc = array_map(function ($e) {
                return "'" . $this->db->escape($e) . "'";
            }, $emails);
            // Column collation is case-insensitive, so plain IN keeps the email index usable.
            $where[] = "o.email IN (" . implode(',', $emailsEsc) . ")";
        }

        if (! $where) {
            return [];
        }

        $excludeIds = array_filter(array_map('intval', $excludeIds));
        $excludeSql = $excludeIds ? "AND o.order_id NOT IN (" . implode(',', $excludeIds) . ")" : '';

        $sql = "SELECT o.order_id, o.customer_id, LOWER(o.email) AS email_norm, o.telephone AS phone_raw, o.date_added
                FROM `" . DB_PREFIX . "order` o
                WHERE (" . implode(' OR ', $where) . ")
                  AND o.date_added BETWEEN '" . $this->db->escape(date('Y-m-d H:i:s', $startTs)) . "'
                                       AND '" . $this->db->escape(date('Y-m-d H:i:s', $endTs)) . "'
                  $excludeSql
                ORDER BY o.date_added";

        return $this->db->query($sql)->rows;
    }

    public function getCustomersList(array $filters, int $start, int $limit, string $sort, string $order): array
    {
        $sql = $this->buildCustomersBaseSql($filters);
        $sortCol = in_array($sort, ['total_orders', 'total_amount', 'avg_amount', 'first_order_date', 'last_order_date'], true) ? $sort : 'last_order_date';
        $orderDir = $order === 'ASC' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY $sortCol $orderDir LIMIT " . (int) $start . ', ' . (int) $limit;

        $rows = $this->db->query($sql)->rows;

        $windowSec = max(60, (int) ($filters['duplicate_window_seconds'] ?? 86400));
        $minCount = max(2, (int) ($filters['duplicate_min_count'] ?? 2));

        foreach ($rows as &$row) {
            $row['has_duplicates'] = $this->customerHasDuplicates($row, $windowSec, $minCount, ! empty($filters['match_guests']));
        }
        unset($row);

        if (! empty($filters['has_duplicates'])) {
            $rows = array_values(array_filter($rows, static fn ($r) => $r['has_duplicates']));
        }

        return $rows;
    }

    public function getCustomersTotal(array $filters): int
    {
        $sql = $this->buildCustomersBaseSql($filters);

        $countSql = "SELECT COUNT(*) AS cnt FROM ($sql) AS sub";

        $row = $this->db->query($countSql)->row;

        return (int) ($row['cnt'] ?? 0);
    }

    public function getCustomerOrders(string $matchKey): array
    {
        if (! preg_match('/^(cid|gst):(.+)$/', $matchKey, $m)) {
            return [];
        }

        $where = '';

        if ($m[1] === 'cid') {
            $where = "o.customer_id = '" . (int) $m[2] . "'";
        } else {
            $parts = explode('|', $m[2], 2);
            $email = strtolower($parts[0] ?? '');
            $phone = $parts[1] ?? '';
            $where = "o.customer_id = 0 AND LOWER(o.email) = '" . $this->db->escape($email) . "'";

            if ($phone !== '') {
                $where .= " AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(o.telephone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', ''), '.', '') = '" . $this->db->escape($phone) . "'";
            }
        }

        $sql = "SELECT o.order_id, o.order_status_id, o.firstname, o.lastname, o.email, o.telephone,
                       o.total, o.currency_code, o.currency_value, o.date_added,
                       (SELECT name FROM `" . DB_PREFIX . "order_status` WHERE order_status_id = o.order_status_id AND language_id = '" . (int) $this->config->get('config_language_id') . "') AS status_name
                FROM `" . DB_PREFIX . "order` o
                WHERE $where
                ORDER BY o.date_added DESC";

        $orders = $this->db->query($sql)->rows;

        if (! $orders) {
            return [];
        }

        $orderIds = array_map(static fn ($o) => (int) $o['order_id'], $orders);
        $idsSql = implode(',', $orderIds);

        $products = $this->db->query("SELECT op.order_product_id, op.order_id, op.product_id, op.name, op.model, op.quantity, op.price, op.total
                                      FROM `" . DB_PREFIX . "order_product` op
                                      WHERE op.order_id IN ($idsSql)
                                      ORDER BY op.order_id, op.order_product_id")->rows;

        $options = $this->db->query("SELECT order_product_id, name, value
                                     FROM `" . DB_PREFIX . "order_option`
                                     WHERE order_id IN ($idsSql)
                                     ORDER BY order_product_id, order_option_id")->rows;

        $optionsByLine = [];

        foreach ($options as $opt) {
            $optionsByLine[(int) $opt['order_product_id']][] = $opt['name'] . ': ' . $opt['value'];
        }

        $productsByOrder = [];

        foreach ($products as $p) {
            $oid = (int) $p['order_id'];
            $opl = (int) $p['order_product_id'];
            $p['options'] = $optionsByLine[$opl] ?? [];
            $productsByOrder[$oid][] = $p;
        }

        foreach ($orders as &$o) {
            $oid = (int) $o['order_id'];
            $o['products'] = $productsByOrder[$oid] ?? [];
            $o['products_count'] = count($o['products']);
        }
        unset($o);

        return $orders;
    }

    private function buildCustomersBaseSql(array $filters): string
    {
        $tracked = array_filter(array_map('intval', (array) ($filters['tracked_status_ids'] ?? [])));
        $statusFilter = $tracked ? "o.order_status_id IN (" . implode(',', $tracked) . ")" : "1";

        $matchGuests = ! empty($filters['match_guests']);

        $matchKeyExpr = $matchGuests
            ? "CASE WHEN o.customer_id > 0 THEN CONCAT('cid:', o.customer_id) ELSE CONCAT('gst:', LOWER(TRIM(o.email)), '|', REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(o.telephone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', ''), '.', '')) END"
            : "CASE WHEN o.customer_id > 0 THEN CONCAT('cid:', o.customer_id) ELSE CONCAT('oid:', o.order_id) END";

        $where = $statusFilter;

        if (! empty($filters['search'])) {
            $term = $this->db->escape((string) $filters['search']);
            $where .= " AND (LOWER(o.email) LIKE '%" . strtolower($term) . "%' OR o.telephone LIKE '%" . $term . "%' OR CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $term . "%')";
        }

        $having = '';
        $tier = $filters['tier'] ?? '';
        $thresholdMid = max(1, (int) ($filters['threshold_mid'] ?? 3));
        $thresholdHigh = max(1, (int) ($filters['threshold_high'] ?? 10));

        if ($tier === 'high') {
            $having = " HAVING total_orders >= " . $thresholdHigh;
        } elseif ($tier === 'mid') {
            $having = " HAVING total_orders >= " . $thresholdMid . " AND total_orders < " . $thresholdHigh;
        } elseif ($tier === 'low') {
            $having = " HAVING total_orders < " . $thresholdMid;
        }

        return "SELECT
                    $matchKeyExpr AS match_key,
                    MAX(o.customer_id) AS customer_id,
                    SUBSTRING_INDEX(GROUP_CONCAT(CONCAT(o.firstname, ' ', o.lastname) ORDER BY o.date_added DESC SEPARATOR '|||'), '|||', 1) AS name,
                    SUBSTRING_INDEX(GROUP_CONCAT(o.email ORDER BY o.date_added DESC SEPARATOR '|||'), '|||', 1) AS email,
                    SUBSTRING_INDEX(GROUP_CONCAT(o.telephone ORDER BY o.date_added DESC SEPARATOR '|||'), '|||', 1) AS telephone,
                    COUNT(*) AS total_orders,
                    SUM(o.total) AS total_amount,
                    AVG(o.total) AS avg_amount,
                    MIN(o.date_added) AS first_order_date,
                    MAX(o.date_added) AS last_order_date
                FROM `" . DB_PREFIX . "order` o
                WHERE $where
                GROUP BY match_key
                $having";
    }

    public function getOrderFingerprints(array $orderIds): array
    {
        $orderIds = array_filter(array_map('intval', $orderIds));

        if (! $orderIds) {
            return [];
        }

        $idsSql = implode(',', $orderIds);

        $totals = [];

        foreach ($this->db->query("SELECT order_id, total FROM `" . DB_PREFIX . "order` WHERE order_id IN ($idsSql)")->rows as $r) {
            $totals[(int) $r['order_id']] = number_format((float) $r['total'], 4, '.', '');
        }

        $items = [];

        $rows = $this->db->query("SELECT order_id, product_id, quantity, price
                                  FROM `" . DB_PREFIX . "order_product`
                                  WHERE order_id IN ($idsSql)
                                  ORDER BY order_id, product_id, quantity, price")->rows;

        foreach ($rows as $r) {
            $oid = (int) $r['order_id'];
            $items[$oid][] = (int) $r['product_id'] . ':' . (int) $r['quantity'] . ':' . number_format((float) $r['price'], 4, '.', '');
        }

        $fingerprints = [];

        foreach ($orderIds as $oid) {
            $part = isset($items[$oid]) ? implode('|', $items[$oid]) : '';
            $fingerprints[$oid] = $part . '#' . ($totals[$oid] ?? '0.0000');
        }

        return $fingerprints;
    }

    private function customerHasDuplicates(array $row, int $windowSec, int $minCount, bool $matchGuests): bool
    {
        $orders = $this->getCustomerOrders((string) $row['match_key']);

        if (count($orders) < $minCount) {
            return false;
        }

        $timestamps = array_map(static fn ($o) => strtotime($o['date_added']), $orders);
        sort($timestamps);

        $i = 0;

        for ($j = 0; $j < count($timestamps); $j++) {
            while ($timestamps[$j] - $timestamps[$i] > $windowSec) {
                $i++;
            }

            if ($j - $i + 1 >= $minCount) {
                return true;
            }
        }

        return false;
    }

    public function createIndexes(): void
    {
        $table = DB_PREFIX . 'order';

        $this->addIndexIfMissing($table, 'aw_bh_email', 'email');
        $this->addIndexIfMissing($table, 'aw_bh_date_added', 'date_added');
    }

    public function dropIndexes(): void
    {
        $table = DB_PREFIX . 'order';

        $this->dropIndexIfExists($table, 'aw_bh_email');
        $this->dropIndexIfExists($table, 'aw_bh_date_added');
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $sql = "SHOW INDEX FROM `" . $table . "` WHERE Key_name = '" . $this->db->escape($indexName) . "'";

        return (bool) $this->db->query($sql)->num_rows;
    }

    private function addIndexIfMissing(string $table, string $indexName, string $column): void
    {
        if (! $this->hasIndex($table, $indexName)) {
            $this->db->query("ALTER TABLE `" . $table . "` ADD INDEX `" . $indexName . "` (`" . $column . "`)");
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if ($this->hasIndex($table, $indexName)) {
            $this->db->query("ALTER TABLE `" . $table . "` DROP INDEX `" . $indexName . "`");
        }
    }
}
