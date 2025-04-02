<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwLandingLinks extends Model
{
    public function getPages(array $pageIds = []): array
    {
        if (empty($pageIds)) {
            return [];
        }

        $idsList = implode(', ', array_map(function ($v) {
            return is_string($v) ? "'" . addslashes($v) . "'" : $v;
        }, $pageIds));

        $languageId = (int)$this->config->get('config_language_id');

        $query = $this->db->query("SELECT l.landing_page_id, ld.name
        FROM `" . DB_PREFIX . "aw_landing_page` l
        LEFT JOIN `" . DB_PREFIX . "aw_landing_page_description` ld
            ON l.landing_page_id = ld.landing_page_id
        WHERE l.landing_page_id IN ($idsList)
          AND l.status = 1
          AND ld.language_id = '" . $languageId . "'");

        return $query->rows;
    }
}
