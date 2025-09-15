<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 *
 * @link    https://alexwaha.com
 *
 * @email   support@alexwaha.com
 *
 * @license GPLv3
 */

namespace Alexwaha;

use DB;
use Config;

final class Model
{
    private DB $db;

    private Config $config;

    private static bool $schemaReady = false;

    public function __construct($registry)
    {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
    }

    public function ensureSchema(): void
    {
        if (! self::$schemaReady) {
            $this->createTables();
            self::$schemaReady = true;
        }
    }

    public function setModule(string $code, array $data, int $moduleId = 0): void
    {
        $json = $this->db->escape(json_encode($data));

        if ($moduleId) {
            $this->db->query('UPDATE `' . DB_PREFIX . "module`
             SET `name` = '" . $this->db->escape($data['name']) . "',
                 `setting` = '" . $json . "'
             WHERE `module_id` = '" . $moduleId . "'");
        } else {
            $this->db->query('INSERT INTO `' . DB_PREFIX . "module`
             SET `name` = '" . $this->db->escape($data['name']) . "',
                 `code` = '" . $this->db->escape($code) . "',
                 `setting` = '" . $json . "'");
        }
    }

    /**
     * @return mixed|null
     */
    public function getModule($moduleId)
    {
        $query = $this->db->query('SELECT * FROM `' . DB_PREFIX . "module` WHERE `module_id` = '" . $moduleId . "'");

        return $query->row ? json_decode($query->row['setting'], true) : [];
    }

    /**
     * @return void
     */
    public function createTables()
    {
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "aw_module_config'");

        if (! $query->num_rows) {
            $this->db->query('
                CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'aw_module_config` (
                    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `code` VARCHAR(255) NOT NULL,
                    `config` JSON NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `code_unique` (`code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ');
        }
    }

    public function getConfig($code): ?string
    {
        $query = $this->db->query('SELECT `config`
         FROM `' . DB_PREFIX . "aw_module_config`
         WHERE `code` = '" . $this->db->escape($code) . "'
         LIMIT 1");

        return $query->row['config'] ?? null;
    }

    public function setConfig(string $code, string $json): void
    {
        $exists = $this->db->query('SELECT `id`
             FROM `' . DB_PREFIX . "aw_module_config`
             WHERE `code` = '" . $this->db->escape($code) . "'
             LIMIT 1");

        if ($exists->num_rows) {
            $this->db->query('UPDATE `' . DB_PREFIX . "aw_module_config`
                 SET `config` = '" . $this->db->escape($json) . "'
                 WHERE `code` = '" . $this->db->escape($code) . "'");
        } else {
            $this->db->query('INSERT INTO `' . DB_PREFIX . "aw_module_config`
                 SET `code` = '" . $this->db->escape($code) . "',
                     `config` = '" . $this->db->escape($json) . "'");
        }
    }

    public function removeConfig($code): void
    {
        $this->db->query('DELETE FROM `' . DB_PREFIX . "aw_module_config` WHERE `code` = '" . $this->db->escape($code) . "'");
    }

    public function getLanguageCode(): string
    {
        $query = $this->db->query('SELECT code FROM `' . DB_PREFIX . "language` WHERE 
        `language_id` = '" . $this->config->get('config_language_id') . "'");

        return $query->row['code'] ?: '';
    }

    public function getLanguages(): array
    {
        $query = $this->db->query('SELECT * FROM `' . DB_PREFIX . "language` WHERE `status` = '1'");

        return $query->rows;
    }

    /**
     * @return void
     */
    public function setSeoUrls(array $seoUrls, string $entityQuery, int $entityId = 0, bool $isLegacy = false)
    {
        $queryParam = $entityId ? $entityQuery . '=' . $entityId : $entityQuery;

        $languageId = $this->config->get('config_language_id');
        $defaultStoreId = (int) $this->config->get('config_store_id');

        if ($isLegacy) {
            $exists = $this->db->query('SELECT url_alias_id
             FROM `' . DB_PREFIX . "url_alias`
             WHERE query = '" . $this->db->escape($queryParam) . "'");

            $seoUrl = $seoUrls[$defaultStoreId][$languageId];
            $keyword = mb_strtolower(trim((string) $seoUrl));

            if ($exists->num_rows) {
                $this->db->query('UPDATE `' . DB_PREFIX . "url_alias`
                 SET keyword = '" . $this->db->escape($keyword) . "'
                 WHERE query = '" . $this->db->escape($queryParam) . "'");
            } else {
                $this->db->query('INSERT INTO `' . DB_PREFIX . "url_alias`
                 SET query = '" . $this->db->escape($queryParam) . "',
                     keyword = '" . $this->db->escape($keyword) . "'");
            }
        } else {
            foreach ($seoUrls as $storeId => $languages) {
                foreach ($languages as $languageId => $seoUrl) {
                    $keyword = mb_strtolower(trim((string) $seoUrl));

                    $exists = $this->db->query('
                        SELECT seo_url_id
                        FROM `' . DB_PREFIX . "seo_url`
                        WHERE store_id = '" . $storeId . "'
                          AND language_id = '" . $languageId . "'
                          AND query = '" . $this->db->escape($queryParam) . "'
                        LIMIT 1
                    ");

                    if ($exists->num_rows) {
                        $this->db->query('
                            UPDATE `' . DB_PREFIX . "seo_url`
                            SET keyword = '" . $this->db->escape($keyword) . "'
                            WHERE seo_url_id = '" . $exists->row['seo_url_id'] . "'
                            LIMIT 1
                        ");
                    } else {
                        $this->db->query('
                            INSERT INTO `' . DB_PREFIX . "seo_url`
                            SET store_id   = '" . $storeId . "',
                                language_id= '" . $languageId . "',
                                query      = '" . $this->db->escape($queryParam) . "',
                                keyword    = '" . $this->db->escape($keyword) . "'
                        ");
                    }
                }
            }
        }
    }

    public function getSeoUrls(string $entityQuery, int $entityId = 0, bool $isLegacy = false): array
    {
        $queryParam = $entityId ? $entityQuery . '=' . $entityId : $entityQuery;

        $defaultStoreId = (int) $this->config->get('config_store_id');

        $languages = $this->getLanguages();

        if ($isLegacy) {
            $sql = 'SELECT keyword FROM `' . DB_PREFIX . "url_alias`
                WHERE query = '" . $this->db->escape($queryParam) . "'";
        } else {
            $sql = 'SELECT keyword, language_id, store_id
                FROM `' . DB_PREFIX . "seo_url`
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

    public function seoUrlExists(
        string $seoUrl,
        int $storeId,
        int $languageId,
        string $entityQuery,
        int $entityId = 0,
        bool $isLegacy = false
    ): bool {
        $queryParam = $entityId ? $entityQuery . '=' . $entityId : $entityQuery;

        if ($isLegacy) {
            $sql = 'SELECT query FROM `' . DB_PREFIX . "url_alias` WHERE keyword = '" . $this->db->escape($seoUrl) . "'";
        } else {
            $sql = 'SELECT query FROM `' . DB_PREFIX . "seo_url`
              WHERE keyword = '" . $this->db->escape($seoUrl) . "'
              AND store_id = '" . $storeId . "'
              AND language_id = '" . $languageId . "'";
        }

        $query = $this->db->query($sql);

        if (! $query->num_rows) {
            return false;
        }

        foreach ($query->rows as $row) {
            if ($row['query'] === $queryParam) {
                return false;
            }
        }

        return true;
    }
}
