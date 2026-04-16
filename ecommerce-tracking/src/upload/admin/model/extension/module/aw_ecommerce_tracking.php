<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwEcommerceTracking extends Model
{
    public function getRegisteredEvents(string $codePrefix): array
    {
        $query = $this->db->query(
            "SELECT `trigger`, `action`, `status` FROM `" . DB_PREFIX . "event` WHERE `code` LIKE '" . $this->db->escape($codePrefix) . "%' ORDER BY `event_id`"
        );

        return $query->rows;
    }
}
