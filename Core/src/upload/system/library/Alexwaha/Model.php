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

class Model
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

    public function checkTables(): bool
    {
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "aw_module_config'");

        if (!$query->num_rows) {
            return false;
        }

        return true;
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
     * @return string
     */
    public function getLanguageCode(): string
    {
        $query = $this->db->query('SELECT code FROM `' . DB_PREFIX . "language` WHERE 
        `language_id` = '" . $this->config->get('config_language_id') . "'");

        return $query->row['code'] ?: '';
    }
}
