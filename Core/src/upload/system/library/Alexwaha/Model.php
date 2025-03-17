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
