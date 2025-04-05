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
     * @param  string  $route
     * @return mixed
     */
    public function getLayout(string $route)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "aw_global_layout WHERE route = '" . $this->db->escape($route) . "'");

        return $query->row;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getModules($id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "aw_global_layout_module WHERE layout_id = '" . (int)$id . "' ORDER BY position ASC, sort_order ASC");

        return $query->rows;
    }

    /**
     * @param $id
     * @param $data
     * @return void
     */
    public function editLayout($id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "aw_global_layout
        SET name = '" . $this->db->escape($data['name']) . "',
        status = '" . (int) $data['status'] . "'
        WHERE id = '" . (int)$id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "aw_global_layout_module WHERE layout_id = '" . (int)$id . "'");

        if (isset($data['layout_module'])) {
            foreach ($data['layout_module'] as $module) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "aw_global_layout_module
                SET layout_id = '" . (int)$id . "',
                code = '" . $this->db->escape($module['code']) . "',
                position = '" . $this->db->escape($module['position']) . "',
                sort_order = '" . (int)$module['sort_order'] . "'");
            }
        }
    }
    /**
     * @param  string  $name
     * @param  string  $route
     * @return void
     */
    public function install(string $name, string $route): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_global_layout` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `route` VARCHAR(255) NOT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_global_layout_module` (
          `module_id` int(11) NOT NULL AUTO_INCREMENT,
          `layout_id` int(11) NOT NULL,
          `code` varchar(64) NOT NULL,
          `position` varchar(64) NOT NULL,
          `sort_order` int(3) NOT NULL,
          PRIMARY KEY (`module_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8;");

        $this->db->query("INSERT INTO " . DB_PREFIX . "aw_global_layout SET 
        name = '" . $this->db->escape($name) . "', 
        route = '" . $this->db->escape($route) . "',
        status = 1");
    }
}
