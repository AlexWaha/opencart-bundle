<?php

/**
 * Age Verification Module
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @email support@alexwaha.com
 * @license GPLv3
 */
class ModelExtensionFeedAwXmlFeed extends Model
{
    public function addFeed($data): int
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "aw_xml_feed SET 
            name = '" . $this->db->escape($data['name']) . "', 
            filename = '" . $this->db->escape($data['filename']) . "', 
            template = '" . $this->db->escape($data['template']) . "', 
            language_id = '" . (int) $data['language_id'] . "',
            currency_code = '" . $this->db->escape($data['currency_code']) . "',
            image_origin = '" . (int) $data['image_origin'] . "',
            image_count = '" . (int) $data['image_count'] . "',
            status = '" . (int) $data['status'] . "'");

        return $this->db->getLastId();
    }

    public function editFeed($feedId, $data)
    {
        if (isset($data)) {
            $this->db->query("UPDATE " . DB_PREFIX . "aw_xml_feed SET
                `name` = '" . $this->db->escape($data['name']) . "',
                filename = '" . $this->db->escape($data['filename']) . "',
                template = '" . $this->db->escape($data['template']) . "',
                language_id = '" . (int) $data['language_id'] . "',
                currency_code = '" . $this->db->escape($data['currency_code']) . "',
                image_origin = '" . (int) $data['image_origin'] . "',
                image_count = '" . (int) $data['image_count'] . "',
                `status` = '" . (int) $data['status'] . "'
                WHERE feed_id = '" . (int) $feedId . "'");
        }
    }

    public function getFeed($feedId)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "aw_xml_feed WHERE feed_id = '" . (int) $feedId . "'");

        return $query->row;
    }

    public function getFeeds()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "aw_xml_feed ORDER BY `name` DESC");

        return $query->rows;
    }

    public function deleteFeed($feedId)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "aw_xml_feed WHERE feed_id = '" . (int) $feedId . "'");
    }

    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_xml_feed` (
            `feed_id`       INT(11)      NOT NULL AUTO_INCREMENT,
            `name`          VARCHAR(256) NOT NULL,
            `filename`      VARCHAR(128) NOT NULL,
            `template`      TEXT         NOT NULL,
            `language_id`   INT(11)      NOT NULL,
            `currency_code` VARCHAR(3)   NOT NULL,
            `image_origin`  TINYINT(1)   NOT NULL,
            `image_count`   INT(11)      NOT NULL,
			`status` TINYINT(1) NOT NULL, PRIMARY KEY (`feed_id`))
			ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE " . DB_PREFIX . "aw_xml_feed");
    }
}
