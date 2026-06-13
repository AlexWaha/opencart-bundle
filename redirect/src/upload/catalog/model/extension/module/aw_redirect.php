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
    private const CACHE_KEY = 'aw_redirect.map';

    private const EXACT_CACHE_LIMIT = 5000;

    /**
     * Resolve an incoming request to a redirect rule.
     * Returns ['redirect_id', 'target', 'status_code'] or null.
     */
    public function getMatch(string $path, string $query, int $storeId): ?array
    {
        $map = $this->cache->get(self::CACHE_KEY);

        if (!$map) {
            $map = $this->buildCacheMap();
        }

        $normalizedPath = $this->normalizeUrl($path);
        $hashPath = md5($normalizedPath);
        $hashFull = $query !== '' ? md5($this->normalizeUrl($path . '?' . $query)) : null;

        // Exact match (query-specific rule wins over path-only)
        $hashes = $hashFull ? [$hashFull, $hashPath] : [$hashPath];

        if (!empty($map['exact_indexed'])) {
            $found = $this->findExactIndexed($hashes, $storeId);
            if ($found) {
                return $found;
            }
        } else {
            foreach ($hashes as $hash) {
                if (isset($map['exact'][$hash])) {
                    $found = $this->pickByStore($map['exact'][$hash], $storeId);
                    if ($found) {
                        return $found;
                    }
                }
            }
        }

        // Wildcard match (against normalized path)
        foreach ($map['wild'] as $rule) {
            if (($rule['s'] == 0 || $rule['s'] == $storeId) && fnmatch($rule['p'], $normalizedPath, FNM_CASEFOLD)) {
                return ['redirect_id' => $rule['id'], 'target' => $rule['t'], 'status_code' => $rule['c']];
            }
        }

        return null;
    }

    private function pickByStore(array $candidates, int $storeId): ?array
    {
        $fallback = null;

        foreach ($candidates as $candidate) {
            if ($candidate['s'] == $storeId) {
                return ['redirect_id' => $candidate['id'], 'target' => $candidate['t'], 'status_code' => $candidate['c']];
            }

            if ($candidate['s'] == 0) {
                $fallback = $candidate;
            }
        }

        return $fallback
            ? ['redirect_id' => $fallback['id'], 'target' => $fallback['t'], 'status_code' => $fallback['c']]
            : null;
    }

    public function buildCacheMap(): array
    {
        $map = ['exact' => [], 'wild' => [], 'exact_indexed' => false];

        $exactTotal = (int) ($this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "aw_redirect`
            WHERE status = '1' AND match_type = '0'")->row['total'] ?? 0);

        if ($exactTotal > self::EXACT_CACHE_LIMIT) {
            $map['exact_indexed'] = true;
        } else {
            $rows = $this->db->query("SELECT redirect_id, source_hash, target, status_code, store_id
                FROM `" . DB_PREFIX . "aw_redirect` WHERE status = '1' AND match_type = '0'")->rows;

            foreach ($rows as $row) {
                $map['exact'][$row['source_hash']][] = [
                    'id' => (int) $row['redirect_id'],
                    't' => $row['target'],
                    'c' => (int) $row['status_code'],
                    's' => (int) $row['store_id'],
                ];
            }
        }

        $wildRows = $this->db->query("SELECT redirect_id, source, target, status_code, store_id
            FROM `" . DB_PREFIX . "aw_redirect` WHERE status = '1' AND match_type = '1'")->rows;

        foreach ($wildRows as $row) {
            $map['wild'][] = [
                'id' => (int) $row['redirect_id'],
                'p' => $this->normalizeUrl($row['source']),
                't' => $row['target'],
                'c' => (int) $row['status_code'],
                's' => (int) $row['store_id'],
            ];
        }

        $this->cache->set(self::CACHE_KEY, $map);

        return $map;
    }

    private function findExactIndexed(array $hashes, int $storeId): ?array
    {
        $in = [];
        foreach ($hashes as $hash) {
            $in[] = "'" . $this->db->escape($hash) . "'";
        }

        $query = $this->db->query("SELECT redirect_id, target, status_code FROM `" . DB_PREFIX . "aw_redirect`
            WHERE source_hash IN (" . implode(',', $in) . ")
            AND status = '1' AND match_type = '0'
            AND (store_id = '0' OR store_id = '" . (int) $storeId . "')
            ORDER BY store_id DESC, FIELD(source_hash, " . implode(',', $in) . ")
            LIMIT 1");

        if (!$query->num_rows) {
            return null;
        }

        return [
            'redirect_id' => (int) $query->row['redirect_id'],
            'target' => $query->row['target'],
            'status_code' => (int) $query->row['status_code'],
        ];
    }

    public function incrementHit(int $redirectId): void
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "aw_redirect`
            SET hits = hits + 1 WHERE redirect_id = '" . (int) $redirectId . "'");
    }

    public function logNotFound(string $url, string $referrer, string $userAgent, int $storeId, int $languageId): void
    {
        $url = substr($url, 0, 2048);
        $hash = md5($this->normalizeUrl($url));

        $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_redirect_404` SET
            url = '" . $this->db->escape($url) . "',
            url_hash = '" . $this->db->escape($hash) . "',
            hits = 1,
            referrer = '" . $this->db->escape(substr($referrer, 0, 2048)) . "',
            user_agent = '" . $this->db->escape(substr($userAgent, 0, 512)) . "',
            store_id = '" . (int) $storeId . "',
            language_id = '" . (int) $languageId . "',
            date_added = NOW(),
            date_modified = NOW()
            ON DUPLICATE KEY UPDATE
                hits = hits + 1,
                referrer = VALUES(referrer),
                user_agent = VALUES(user_agent),
                date_modified = NOW()");
    }

    /**
     * Normalize a URL key for hashing/matching.
     * MUST stay identical to the admin model counterpart.
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
}
