<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwEcommerceTracking extends Model
{
    public function install(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_ecommerce_tracking_purchase` (
            `order_id` INT(11) NOT NULL,
            `ga_client_id` VARCHAR(64) NULL DEFAULT NULL,
            `ga_session_id` VARCHAR(32) NULL DEFAULT NULL,
            `fbp` VARCHAR(255) NULL DEFAULT NULL,
            `fbc` VARCHAR(255) NULL DEFAULT NULL,
            `sent_at` DATETIME NULL DEFAULT NULL,
            `client_claimed_at` DATETIME NULL DEFAULT NULL,
            `meta_sent_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    public function getRegisteredEvents(string $codePrefix): array
    {
        $query = $this->db->query(
            "SELECT `code`, `trigger`, `action`, `status` FROM `" . DB_PREFIX . "event` WHERE `code` LIKE '" . $this->db->escape($codePrefix) . "%' ORDER BY `event_id`"
        );

        return $query->rows;
    }
}
