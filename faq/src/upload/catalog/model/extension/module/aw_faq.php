<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwFaq extends Model
{
    public function getFaqs(): array
    {
        $sql = "SELECT f.faq_id, fd.question, fd.answer
            FROM `" . DB_PREFIX . "aw_faq` f
            LEFT JOIN `" . DB_PREFIX . "aw_faq_description` fd
                ON (f.faq_id = fd.faq_id AND fd.language_id = '" . (int) $this->config->get('config_language_id') . "')
            WHERE f.status = '1'
            ORDER BY f.sort_order ASC";

        $query = $this->db->query($sql);

        return $query->rows;
    }
}
