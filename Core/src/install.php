<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

$this->db->query("
    CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_module_config` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `code` VARCHAR(255) NOT NULL,
        `config` JSON NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `code_unique` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
