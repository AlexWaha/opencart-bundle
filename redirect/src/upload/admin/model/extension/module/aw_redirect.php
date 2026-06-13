<?php

/**
 * Redirect Manager Module
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @email support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwRedirect extends Model
{
    public function createTables(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_redirect` (
            `redirect_id` INT(11) NOT NULL AUTO_INCREMENT,
            `source` VARCHAR(2048) NOT NULL,
            `source_hash` CHAR(32) NOT NULL DEFAULT '',
            `target` VARCHAR(2048) NOT NULL,
            `match_type` TINYINT(1) NOT NULL DEFAULT 0,
            `match_query` TINYINT(1) NOT NULL DEFAULT 0,
            `status_code` SMALLINT(3) NOT NULL DEFAULT 301,
            `store_id` INT(11) NOT NULL DEFAULT 0,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `hits` INT(11) NOT NULL DEFAULT 0,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`redirect_id`),
            KEY `aw_redirect_lookup` (`source_hash`, `store_id`, `status`),
            KEY `aw_redirect_type` (`match_type`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_redirect_404` (
            `log_id` INT(11) NOT NULL AUTO_INCREMENT,
            `url` VARCHAR(2048) NOT NULL,
            `url_hash` CHAR(32) NOT NULL,
            `hits` INT(11) NOT NULL DEFAULT 1,
            `referrer` VARCHAR(2048) NOT NULL DEFAULT '',
            `user_agent` VARCHAR(512) NOT NULL DEFAULT '',
            `store_id` INT(11) NOT NULL DEFAULT 0,
            `language_id` INT(11) NOT NULL DEFAULT 0,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`log_id`),
            UNIQUE KEY `aw_redirect_404_url` (`url_hash`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    public function dropTables(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_redirect_404`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_redirect`");
    }

    /**
     * Normalize a URL key for hashing/matching.
     * MUST stay identical to the catalog model counterpart.
     */
    public function normalizeUrl(string $url): string
    {
        $url = trim($url);
        $hash = '';

        if (strpos($url, '?') !== false) {
            [$url, $query] = explode('?', $url, 2);
            parse_str($query, $params);
            ksort($params);
            $hash = $params ? '?' . http_build_query($params) : '';
        }

        $url = '/' . ltrim($url, '/');

        if ($url !== '/') {
            $url = rtrim($url, '/');
        }

        return strtolower($url . $hash);
    }

    /**
     * Build the hash key for a rule (path-only, or path+query when match_query is on).
     */
    public function buildHash(string $source, int $matchQuery): string
    {
        if (!$matchQuery && strpos($source, '?') !== false) {
            $source = explode('?', $source, 2)[0];
        }

        return md5($this->normalizeUrl($source));
    }

    public function getRedirect(int $redirectId): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "aw_redirect`
            WHERE redirect_id = '" . (int) $redirectId . "'");

        return $query->row ?: [];
    }

    public function getRedirects(array $data = []): array
    {
        $sql = "SELECT * FROM `" . DB_PREFIX . "aw_redirect`" . $this->buildWhere($data);

        $sortFields = ['source', 'target', 'status_code', 'status', 'hits', 'date_added'];
        $sort = isset($data['sort']) && in_array($data['sort'], $sortFields, true) ? $data['sort'] : 'date_added';
        $order = isset($data['order']) && strtoupper($data['order']) === 'ASC' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY `" . $sort . "` " . $order;

        if (isset($data['start']) || isset($data['limit'])) {
            $start = max(0, (int) ($data['start'] ?? 0));
            $limit = max(1, (int) ($data['limit'] ?? 20));
            $sql .= " LIMIT " . $start . ", " . $limit;
        }

        return $this->db->query($sql)->rows;
    }

    public function getTotalRedirects(array $data = []): int
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "aw_redirect`" . $this->buildWhere($data));

        return (int) ($query->row['total'] ?? 0);
    }

    private function buildWhere(array $data): string
    {
        $where = " WHERE 1";

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $where .= " AND status = '" . (int) $data['filter_status'] . "'";
        }

        if (isset($data['filter_match_type']) && $data['filter_match_type'] !== '') {
            $where .= " AND match_type = '" . (int) $data['filter_match_type'] . "'";
        }

        if (!empty($data['filter_source'])) {
            $term = $this->db->escape($data['filter_source']);
            $where .= " AND (source LIKE '%" . $term . "%' OR target LIKE '%" . $term . "%')";
        }

        return $where;
    }

    public function addRedirect(array $data): int
    {
        $source = trim($data['source'] ?? '');
        $matchQuery = !empty($data['match_query']) ? 1 : 0;
        $matchType = strpos($source, '*') !== false ? 1 : 0;

        $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_redirect` SET
            source = '" . $this->db->escape($source) . "',
            source_hash = '" . $this->db->escape($this->buildHash($source, $matchQuery)) . "',
            target = '" . $this->db->escape(trim($data['target'] ?? '')) . "',
            match_type = '" . (int) $matchType . "',
            match_query = '" . (int) $matchQuery . "',
            status_code = '" . (int) ($data['status_code'] ?? 301) . "',
            store_id = '" . (int) ($data['store_id'] ?? 0) . "',
            status = '" . (int) ($data['status'] ?? 1) . "',
            date_added = NOW(),
            date_modified = NOW()");

        return $this->db->getLastId();
    }

    public function editRedirect(int $redirectId, array $data): void
    {
        $source = trim($data['source'] ?? '');
        $matchQuery = !empty($data['match_query']) ? 1 : 0;
        $matchType = strpos($source, '*') !== false ? 1 : 0;

        $this->db->query("UPDATE `" . DB_PREFIX . "aw_redirect` SET
            source = '" . $this->db->escape($source) . "',
            source_hash = '" . $this->db->escape($this->buildHash($source, $matchQuery)) . "',
            target = '" . $this->db->escape(trim($data['target'] ?? '')) . "',
            match_type = '" . (int) $matchType . "',
            match_query = '" . (int) $matchQuery . "',
            status_code = '" . (int) ($data['status_code'] ?? 301) . "',
            store_id = '" . (int) ($data['store_id'] ?? 0) . "',
            status = '" . (int) ($data['status'] ?? 1) . "',
            date_modified = NOW()
            WHERE redirect_id = '" . (int) $redirectId . "'");
    }

    public function deleteRedirect(int $redirectId): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_redirect`
            WHERE redirect_id = '" . (int) $redirectId . "'");
    }

    public function sourceExists(string $source, int $matchQuery, int $storeId, int $ignoreId = 0): bool
    {
        $hash = $this->buildHash($source, $matchQuery);

        $query = $this->db->query("SELECT redirect_id FROM `" . DB_PREFIX . "aw_redirect`
            WHERE source_hash = '" . $this->db->escape($hash) . "'
            AND store_id = '" . (int) $storeId . "'
            AND redirect_id != '" . (int) $ignoreId . "'
            LIMIT 1");

        return (bool) $query->num_rows;
    }

    // --- 404 resolving log ---

    public function getLogs(array $data = []): array
    {
        $sql = "SELECT * FROM `" . DB_PREFIX . "aw_redirect_404`" . $this->buildLogWhere($data);

        $sortFields = ['url', 'hits', 'date_added', 'date_modified'];
        $sort = isset($data['sort']) && in_array($data['sort'], $sortFields, true) ? $data['sort'] : 'date_modified';
        $order = isset($data['order']) && strtoupper($data['order']) === 'ASC' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY `" . $sort . "` " . $order;

        if (isset($data['start']) || isset($data['limit'])) {
            $start = max(0, (int) ($data['start'] ?? 0));
            $limit = max(1, (int) ($data['limit'] ?? 20));
            $sql .= " LIMIT " . $start . ", " . $limit;
        }

        return $this->db->query($sql)->rows;
    }

    public function getTotalLogs(array $data = []): int
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "aw_redirect_404`" . $this->buildLogWhere($data));

        return (int) ($query->row['total'] ?? 0);
    }

    private function buildLogWhere(array $data): string
    {
        $where = " WHERE 1";

        if (!empty($data['filter_url'])) {
            $where .= " AND url LIKE '%" . $this->db->escape($data['filter_url']) . "%'";
        }

        return $where;
    }

    public function getLog(int $logId): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "aw_redirect_404`
            WHERE log_id = '" . (int) $logId . "'");

        return $query->row ?: [];
    }

    public function deleteLog(int $logId): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_redirect_404`
            WHERE log_id = '" . (int) $logId . "'");
    }

    public function clearLogs(): void
    {
        $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "aw_redirect_404`");
    }
}
