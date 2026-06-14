<?php

/**
 * Viewed Products - admin model
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwViewed extends Model
{
    public function createTable(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_viewed` (
            `viewed_id` INT(11) NOT NULL AUTO_INCREMENT,
            `session_token` VARCHAR(40) NOT NULL DEFAULT '',
            `customer_id` INT(11) NOT NULL DEFAULT '0',
            `product_id` INT(11) NOT NULL DEFAULT '0',
            `store_id` INT(11) NOT NULL DEFAULT '0',
            `date_added` DATETIME NOT NULL,
            PRIMARY KEY (`viewed_id`),
            KEY `session_token` (`session_token`),
            KEY `customer_id` (`customer_id`),
            KEY `product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    public function dropTable(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_viewed`");
    }
}
