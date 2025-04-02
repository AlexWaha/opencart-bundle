<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwLandingLinks extends Model
{
    /**
     * @return mixed
     */
    public function getPage($landingPageId): array
    {
        $languageId = $this->config->get('config_language_id');

        $query = $this->db->query("SELECT l.landing_page_id, ld.name
            FROM `" . DB_PREFIX . "aw_landing_page` l
            LEFT JOIN `" . DB_PREFIX . "aw_landing_page_description` ld
                ON l.landing_page_id = ld.landing_page_id
            WHERE ld.language_id = '" . (int)$languageId . "'
              AND l.landing_page_id = '" . (int)$landingPageId . "'
            LIMIT 1");

        return $query->row ?? [];
    }

}
