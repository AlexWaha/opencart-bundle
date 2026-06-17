<?php

/**
 * Example adapter: feeds Landing Pages (aw_landing_page module) into the sitemap.
 *
 * This is a working reference for third-party providers. It targets the
 * `aw_landing_page` tables and no-ops gracefully when that module is not
 * installed, so it is safe to ship even on stores without Landing Pages.
 *
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */
class ControllerExtensionAwSitemapProviderLanding extends Controller
{
    public function getCode(): string
    {
        return 'landing';
    }

    public function getName(): string
    {
        return 'Landing pages';
    }

    public function getTotal(int $languageId): int
    {
        if (!$this->tableExists()) {
            return 0;
        }

        $query = $this->db->query("
            SELECT COUNT(DISTINCT l.landing_page_id) AS total
            FROM `" . DB_PREFIX . "aw_landing_page` l
            LEFT JOIN `" . DB_PREFIX . "aw_landing_page_to_store` l2s ON (l.landing_page_id = l2s.landing_page_id)
            WHERE l.status = '1'
              AND l2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
        ");

        return (int)($query->row['total'] ?? 0);
    }

    public function getUrls(int $languageId, int $start, int $limit): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        $query = $this->db->query("
            SELECT l.landing_page_id
            FROM `" . DB_PREFIX . "aw_landing_page` l
            LEFT JOIN `" . DB_PREFIX . "aw_landing_page_to_store` l2s ON (l.landing_page_id = l2s.landing_page_id)
            WHERE l.status = '1'
              AND l2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
            GROUP BY l.landing_page_id
            ORDER BY l.landing_page_id
            LIMIT " . (int)$start . ", " . (int)$limit . "
        ");

        $urls = [];

        foreach ($query->rows as $row) {
            $urls[] = [
                'loc' => $this->url->link('extension/module/aw_landing_page', 'landing_page_id=' . $row['landing_page_id']),
                'lastmod' => date('Y-m-d\TH:i:sP'),
                'changefreq' => 'weekly',
                'priority' => '0.6',
                'images' => [],
            ];
        }

        return $urls;
    }

    private function tableExists(): bool
    {
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "aw_landing_page'");

        return (bool)$query->num_rows;
    }
}
