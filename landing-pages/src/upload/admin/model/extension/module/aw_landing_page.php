<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwLandingPage extends Model
{
    /**
     * @return void
     */
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_landing_page` (
                `landing_page_id` INT(11) NOT NULL AUTO_INCREMENT,
                `status` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`landing_page_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_landing_page_description` (
                `landing_page_id` INT(11) NOT NULL,
                `language_id` INT(11) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `short_description` TEXT,
                `description` TEXT,
                `meta_title` VARCHAR(255) NOT NULL,
                `meta_description` TEXT,
                `meta_h1` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`landing_page_id`, `language_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_landing_page_to_store` (
                `landing_page_id` INT(11) NOT NULL,
                `store_id` INT(11) NOT NULL,
                PRIMARY KEY (`landing_page_id`, `store_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_landing_page_to_product` (
                `landing_page_id` INT(11) NOT NULL,
                `product_id` INT(11) NOT NULL,
                PRIMARY KEY (`landing_page_id`, `product_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_landing_page`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_landing_page_description`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_landing_page_to_store`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_landing_page_to_product`");
    }

    /**
     * @param  array  $data
     * @return int
     */
    public function addPage(array $data): int
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_landing_page` SET status = '" . (int) $data['status'] . "'");

        $landingPageId = $this->db->getLastId();

        foreach ($data['description'] as $languageId => $desc) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_landing_page_description` SET 
                landing_page_id = '" . (int) $landingPageId . "',
                language_id = '" . (int) $languageId . "',
                name = '" . $this->db->escape($desc['name']) . "',
                meta_title = '" . $this->db->escape($desc['meta_title']) . "',
                meta_description = '" . $this->db->escape($desc['meta_description']) . "',
                meta_h1 = '" . $this->db->escape($desc['meta_h1']) . "',
                short_description = '" . $this->db->escape($desc['short_description']) . "',
                description = '" . $this->db->escape($desc['description']) . "'");
        }

        if (isset($data['store'])) {
            foreach ($data['store'] as $storeId) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_landing_page_to_store` SET landing_page_id = '" . (int) $landingPageId . "', store_id = '" . (int) $storeId . "'");
            }
        }

        if (isset($data['products'])) {
            foreach ($data['products'] as $productId) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_landing_page_to_product` SET landing_page_id = '" . (int) $landingPageId . "', product_id = '" . (int) $productId . "'");
            }
        }

        return $landingPageId;
    }

    /**
     * @param  int  $landingPageId
     * @param  array  $data
     * @return void
     */
    public function editPage(int $landingPageId, array $data)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "aw_landing_page` SET status = '" . (int) $data['status'] . "' WHERE landing_page_id = '" . $landingPageId . "'");

        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_landing_page_description` WHERE landing_page_id = '" . $landingPageId . "'");

        foreach ($data['description'] as $languageId => $desc) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_landing_page_description` SET 
                landing_page_id = '" . $landingPageId . "',
                language_id = '" . (int) $languageId . "',
                name = '" . $this->db->escape($desc['name']) . "',
                meta_title = '" . $this->db->escape($desc['meta_title']) . "',
                meta_description = '" . $this->db->escape($desc['meta_description']) . "',
                meta_h1 = '" . $this->db->escape($desc['meta_h1']) . "',
                short_description = '" . $this->db->escape($desc['short_description']) . "',
                description = '" . $this->db->escape($desc['description']) . "'");
        }

        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_landing_page_to_store` WHERE landing_page_id = '" . $landingPageId . "'");

        if (isset($data['store'])) {
            foreach ($data['store'] as $storeId) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_landing_page_to_store` SET landing_page_id = '" . $landingPageId . "', store_id = '" . (int) $storeId . "'");
            }
        }

        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_landing_page_to_product` WHERE landing_page_id = '" . $landingPageId . "'");

        if (isset($data['products'])) {
            foreach ($data['products'] as $productId) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_landing_page_to_product` SET landing_page_id = '" . $landingPageId . "', product_id = '" . (int) $productId . "'");
            }
        }
    }

    /**
     * @param  int  $landingPageId
     * @param  bool  $isLegacy
     * @return void
     */
    public function deletePage(int $landingPageId, $isLegacy = false)
    {
        $queryParam = 'landing_page_id=' . $landingPageId;

        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_landing_page` WHERE landing_page_id = '" . $landingPageId . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_landing_page_description` WHERE landing_page_id = '" . $landingPageId . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_landing_page_to_store` WHERE landing_page_id = '" . $landingPageId . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_landing_page_to_product` WHERE landing_page_id = '" . $landingPageId . "'");

        if ($isLegacy) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE query = '" . $this->db->escape($queryParam) . "'");
        } else {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = '" . $this->db->escape($queryParam) . "'");
        }
    }

    /**
     * @param  int  $landingPageId
     * @return mixed
     */
    public function getPage(int $landingPageId)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "aw_landing_page` WHERE landing_page_id = '" . $landingPageId . "'");

        return $query->row;
    }

    /**
     * @param  array  $data
     * @return array
     */
    public function getPages(array $data = []): array
    {
        $storeId = (int) $this->config->get('config_store_id');
        $languageId = (int) $this->config->get('config_language_id');

        $conditions = [
            "rd.language_id = '" . $languageId . "'",
            "rps.store_id = '" . $storeId . "'"
        ];

        if (!empty($data['filter_name'])) {
            $conditions[] = "rd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sql = "
        SELECT r.landing_page_id,
               r.status,
               rd.name,
               COUNT(rpp.product_id) AS product_count
        FROM `" . DB_PREFIX . "aw_landing_page` r
        LEFT JOIN `" . DB_PREFIX . "aw_landing_page_description` rd
            ON r.landing_page_id = rd.landing_page_id
        LEFT JOIN `" . DB_PREFIX . "aw_landing_page_to_product` rpp
            ON r.landing_page_id = rpp.landing_page_id
        LEFT JOIN `" . DB_PREFIX . "aw_landing_page_to_store` rps
            ON r.landing_page_id = rps.landing_page_id
    ";

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY r.landing_page_id";

        $sortFields = [
            'rd.name',
            'r.status',
            'product_count'
        ];

        $sort = isset($data['sort']) && in_array($data['sort'], $sortFields, true) ? $data['sort'] : 'rd.name';

        $order = isset($data['order']) && strtoupper($data['order']) === 'DESC' ? 'DESC' : 'ASC';

        $sql .= " ORDER BY " . $sort . " " . $order;

        if (isset($data['start']) || isset($data['limit'])) {
            $start = max(0, (int) ($data['start'] ?? 0));
            $limit = max(1, (int) ($data['limit'] ?? 20));
            $sql .= " LIMIT " . $start . ", " . $limit;
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * @param  array  $data
     * @return int
     */
    public function getPagesTotal(array $data = []): int
    {
        $storeId = (int) $this->config->get('config_store_id');
        $languageId = (int) $this->config->get('config_language_id');

        $conditions = [
            "rd.language_id = '" . $languageId . "'",
            "rps.store_id = '" . $storeId . "'"
        ];

        if (!empty($data['filter_name'])) {
            $conditions[] = "rd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sql = "SELECT COUNT(DISTINCT r.landing_page_id) AS total
        FROM `" . DB_PREFIX . "aw_landing_page` r
        LEFT JOIN `" . DB_PREFIX . "aw_landing_page_description` rd
            ON r.landing_page_id = rd.landing_page_id
        LEFT JOIN `" . DB_PREFIX . "aw_landing_page_to_store` rps
            ON r.landing_page_id = rps.landing_page_id";

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query = $this->db->query($sql);

        return (int) ($query->row['total'] ?? 0);
    }

    /**
     * @param  int  $landingPageId
     * @return array
     */
    public function getPageDescriptions(int $landingPageId): array
    {
        $descriptions = [];

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "aw_landing_page_description` WHERE landing_page_id = '" . $landingPageId . "'");

        foreach ($query->rows as $row) {
            $descriptions[$row['language_id']] = [
                'name' => $row['name'],
                'meta_title' => $row['meta_title'],
                'meta_description' => $row['meta_description'],
                'meta_h1' => $row['meta_h1'],
                'short_description' => $row['short_description'],
                'description' => $row['description'],
            ];
        }

        return $descriptions;
    }

    /**
     * @param  int  $landingPageId
     * @return mixed
     */
    public function getPageProducts(int $landingPageId)
    {
        $query = $this->db->query("SELECT p.product_id, pd.name
        FROM `" . DB_PREFIX . "aw_landing_page_to_product` rpp
        LEFT JOIN `" . DB_PREFIX . "product` p ON (rpp.product_id = p.product_id)
        LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)
        WHERE rpp.landing_page_id = '" . $landingPageId . "'
          AND pd.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        return $query->rows;
    }

    /**
     * @param  int  $landingPageId
     * @return array
     */
    public function getStores(int $landingPageId): array
    {
        $result = [];

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "aw_landing_page_to_store WHERE landing_page_id = '" . $landingPageId . "'");

        foreach ($query->rows as $row) {
            $result[] = $row['store_id'];
        }

        return $result;
    }

    /**
     * @param  int  $landingPageId
     * @param  array  $seoUrls
     * @param  bool  $isLegacy
     * @return void
     */
    public function setSeoUrls(int $landingPageId, array $seoUrls, bool $isLegacy = false)
    {
        $queryParam = 'landing_page_id=' . $landingPageId;
        $languageId = $this->config->get('config_language_id');
        $defaultStoreId = (int) $this->config->get('config_store_id');

        if ($isLegacy) {
            $exists = $this->db->query("SELECT url_alias_id
             FROM `" . DB_PREFIX . "url_alias`
             WHERE query = '" . $this->db->escape($queryParam) . "'");

            $seoUrl = $seoUrls[$defaultStoreId][$languageId];

            if ($exists->num_rows) {
                $this->db->query("UPDATE `" . DB_PREFIX . "url_alias`
                 SET keyword = '" . $this->db->escape($seoUrl) . "'
                 WHERE query = '" . $this->db->escape($queryParam) . "'");
            } else {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias`
                 SET query = '" . $this->db->escape($queryParam) . "',
                     keyword = '" . $this->db->escape($seoUrl) . "'");
            }
        } else {
            foreach ($seoUrls as $storeId => $languages) {
                foreach ($languages as $languageId => $seoUrl) {
                    $exists = $this->db->query("SELECT seo_url_id
                     FROM `" . DB_PREFIX . "seo_url`
                     WHERE query = '" . $this->db->escape($queryParam) . "'
                       AND language_id = '" . $languageId . "'");

                    if ($exists->num_rows) {
                        $this->db->query("UPDATE `" . DB_PREFIX . "seo_url`
                         SET keyword = '" . $this->db->escape($seoUrl) . "'
                         WHERE query = '" . $this->db->escape($queryParam) . "'
                           AND language_id = '" . $languageId . "'");
                    } else {
                        $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url`
                     SET store_id = '" . $storeId . "',
                         language_id = '" . $languageId . "',
                         query = '" . $this->db->escape($queryParam) . "',
                         keyword = '" . $this->db->escape($seoUrl) . "'");
                    }
                }
            }
        }
    }

    /**
     * @param  int  $landingPageId
     * @param  bool  $isLegacy
     * @return array
     */
    public function getSeoUrls(int $landingPageId, bool $isLegacy = false): array
    {
        $queryParam = 'landing_page_id=' . $landingPageId;
        $defaultStoreId = (int) $this->config->get('config_store_id');

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        if ($isLegacy) {
            $sql = "SELECT keyword
                FROM `" . DB_PREFIX . "url_alias`
                WHERE query = '" . $this->db->escape($queryParam) . "'";
        } else {
            $sql = "SELECT keyword, language_id, store_id
                FROM `" . DB_PREFIX . "seo_url`
                WHERE query = '" . $this->db->escape($queryParam) . "'";
        }

        $query = $this->db->query($sql);

        $result = [];

        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                if ($isLegacy) {
                    foreach ($languages as $language) {
                        $result[$defaultStoreId][$language['language_id']] = $row['keyword'];
                    }
                } else {
                    $result[$row['store_id']][$row['language_id']] = $row['keyword'];
                }
            }
        }

        return $result;
    }

    /**
     * @param  string  $seoUrl
     * @param  int  $storeId
     * @param  int  $languageId
     * @param  int  $landingPageId
     * @param  bool  $isLegacy
     * @return bool
     */
    public function seoUrlExists(string $seoUrl, int $storeId, int $languageId, int $landingPageId = 0, bool $isLegacy = false): bool
    {
        if ($isLegacy) {
            $sql = "
            SELECT query
            FROM `" . DB_PREFIX . "url_alias`
            WHERE keyword = '" . $this->db->escape($seoUrl) . "'
        ";
        } else {
            $sql = "
            SELECT query
            FROM `" . DB_PREFIX . "seo_url`
            WHERE keyword = '" . $this->db->escape($seoUrl) . "'
              AND store_id = '" . $storeId . "'
              AND language_id = '" . $languageId . "'
        ";
        }

        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            return false;
        }

        foreach ($query->rows as $row) {
            if ($row['query'] === 'landing_page_id=' . $landingPageId) {
                return false;
            }
        }

        return true;
    }
}
