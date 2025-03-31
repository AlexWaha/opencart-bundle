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

final class Model
{
    private $db;

    private $config;

    /**
     * @param $registry
     */
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
    }

    /**
     * @param  string  $code
     * @param  array  $data
     * @param  int  $moduleId
     * @return void
     */
    public function setModule(string $code, array $data, int $moduleId = 0): void
    {
        $json = $this->db->escape(json_encode($data));

        if ($moduleId) {
            $this->db->query(
                "UPDATE `" . DB_PREFIX . "module`
             SET `name` = '" . $this->db->escape($data['name']) . "',
                 `setting` = '" . $json . "'
             WHERE `module_id` = '" . $moduleId . "'"
            );
        } else {
            $this->db->query(
                "INSERT INTO `" . DB_PREFIX . "module`
             SET `name` = '" . $this->db->escape($data['name']) . "',
                 `code` = '" . $this->db->escape($code) . "',
                 `setting` = '" . $json . "'"
            );
        }
    }

    public function getModule($moduleId)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `module_id` = '" . $moduleId . "'");

        return $query->rows;
    }

    /**
     * @return void
     */
    public function createTables()
    {
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "aw_module_config'");

        if (!$query->num_rows) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_module_config` (
                    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `code` VARCHAR(255) NOT NULL,
                    `config` JSON NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `code_unique` (`code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }
    }

    /**
     * @param $code
     * @return string|null
     */
    public function getConfig($code): ?string
    {
        $query = $this->db->query(
            "SELECT `config`
         FROM `" . DB_PREFIX . "aw_module_config`
         WHERE `code` = '" . $this->db->escape($code) . "'
         LIMIT 1"
        );

        return $query->row['config'] ?? null;
    }

    /**
     * @param  string  $code
     * @param  string  $json
     * @return void
     */
    public function setConfig(string $code, string $json): void
    {
        $exists = $this->db->query(
            "SELECT `id`
             FROM `" . DB_PREFIX . "aw_module_config`
             WHERE `code` = '" . $this->db->escape($code) . "'
             LIMIT 1"
        );

        if ($exists->num_rows) {
            $this->db->query(
                "UPDATE `" . DB_PREFIX . "aw_module_config`
                 SET `config` = '" . $this->db->escape($json) . "'
                 WHERE `code` = '" . $this->db->escape($code) . "'"
            );
        } else {
            $this->db->query(
                "INSERT INTO `" . DB_PREFIX . "aw_module_config`
                 SET `code` = '" . $this->db->escape($code) . "',
                     `config` = '" . $this->db->escape($json) . "'"
            );
        }
    }


    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        $query = $this->db->query('SELECT code FROM `' . DB_PREFIX . "language` WHERE 
        `language_id` = '" . $this->config->get('config_language_id') . "'");

        return $query->row['code'] ?: '';
    }
}
