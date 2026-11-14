<?php

class ModelExtensionModuleAwGdprConsent extends Model
{
    public function getRegisteredEvents(string $codePrefix): array
    {
        $query = $this->db->query(
            "SELECT `code`, `trigger`, `action`, `status` FROM `" . DB_PREFIX . "event` WHERE `code` LIKE '" . $this->db->escape($codePrefix) . "%' ORDER BY `event_id`"
        );

        return $query->rows;
    }
}
