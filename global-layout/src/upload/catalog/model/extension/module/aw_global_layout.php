<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwGlobalLayout extends Model
{
    /**
     * @param  string  $position
     * @return mixed
     */
    public function getModules(string $position)
    {
        $query = $this->db->query("SELECT lm.* FROM " . DB_PREFIX . "aw_global_layout_module lm
        LEFT JOIN " . DB_PREFIX . "aw_global_layout l ON l.id = lm.layout_id
        WHERE lm.position = '" . $this->db->escape($position) . "'
        AND l.status = 1 ORDER BY lm.position ASC, lm.sort_order ASC");

        return $query->rows ?? [];
    }
}
