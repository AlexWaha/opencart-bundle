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
    public function getFeed($feedId)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "aw_xml_feed 
        WHERE feed_id = '" . (int) $feedId . "'
         AND status = '1'");

        return $query->row;
    }

    public function getFeeds()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "aw_xml_feed WHERE status = '1'");

        return $query->rows;
    }

    public function getProducts($data = [])
    {
        if (!empty($data['filter_category'])) {
            $categories = "'" . implode("', '", $data['filter_category']) . "'";
        } else {
            $categories = false;
        }

        if (!empty($data['filter_manufacturer'])) {
            $manufacturers = "'" . implode("', '", $data['filter_manufacturer']) . "'";
        } else {
            $manufacturers = false;
        }

        if (!empty($data['filter_language_id'])) {
            $languageId = $data['filter_language_id'];
        } else {
            $languageId = $this->config->get('config_language_id');
        }

        $query = $this->db->query("SELECT *, pd.name AS name, pd.meta_h1 AS heading, p.image, p.stock_status_id, p.date_available, p.weight, p.upc, p.ean, p.jan, p.isbn, p.mpn, m.name AS manufacturer,
			(SELECT price FROM " . DB_PREFIX . "product_discount pd2 
			WHERE pd2.product_id = p.product_id 
			AND pd2.customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "' 
			AND pd2.quantity = '1' 
			AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) 
			AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount,
			(SELECT price FROM " . DB_PREFIX . "product_special ps 
			WHERE ps.product_id = p.product_id 
			AND ps.customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "' 
			AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) 
			AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special,
			(SELECT category_id FROM " . DB_PREFIX . "product_to_category p2c 
			WHERE p2c.product_id = p.product_id 
			AND p2c.main_category = '1') AS category_id
			FROM " . DB_PREFIX . "product p
			LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
			LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
			LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
			LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
			WHERE p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'"
            . ($categories ? " AND p2c.category_id IN (" . $categories . ")" : "")
            . ($manufacturers ? " AND p.manufacturer_id IN (" . $manufacturers . ")" : "") . "
			AND pd.language_id = '" . (int) $languageId . "'
			AND p.status = '1'
			AND p.date_available <= NOW()
			GROUP BY p.product_id ORDER BY p.product_id LIMIT " . (int)$data['start'] . ", " . (int)$data['limit']);

        return $query->rows;
    }

    public function getTotalProducts($data = [])
    {
        if (!empty($data['filter_category'])) {
            $categories = "'" . implode("', '", $data['filter_category']) . "'";
        } else {
            $categories = false;
        }

        if (!empty($data['filter_manufacturer'])) {
            $manufacturers = "'" . implode("', '", $data['filter_manufacturer']) . "'";
        } else {
            $manufacturers = false;
        }

        if (!empty($data['filter_language_id'])) {
            $languageId = $data['filter_language_id'];
        } else {
            $languageId = $this->config->get('config_language_id');
        }

        $query = $this->db->query("SELECT COUNT(DISTINCT p.product_id) AS total	FROM " . DB_PREFIX . "product p
			LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
			LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
			LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
			LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
			WHERE p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'"
            . ($categories ? " AND p2c.category_id IN (" . $categories . ")" : "")
            . ($manufacturers ? " AND p.manufacturer_id IN (" . $manufacturers . ")" : "") . "
			AND pd.language_id = '" . (int) $languageId . "'
			AND p.status = '1'");

        return $query->row['total'];
    }

    public function getProductImages($productId)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image 
        WHERE product_id = '" . (int) $productId . "' ORDER BY sort_order ASC");

        return $query->rows;
    }

    public function getCategory($categoryId, $languageId)
    {
        $query = $this->db->query("SELECT cd.name, c.category_id, c.parent_id FROM " . DB_PREFIX . "category c
			LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
			LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) 
			WHERE c.category_id = '" . (int)$categoryId . "'
			 AND cd.language_id = '" . (int)$languageId . "' 
			 AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' 
			 AND c.status = '1' 
			 AND c.sort_order <> '-1'");

        return $query->rows;
    }

    public function getCategoryParent($categoryId)
    {
        $query = $this->db->query("SELECT path_id FROM " . DB_PREFIX . "category_path 
        WHERE category_id = '" . (int)$categoryId . "' 
        AND level = 0");

        return $query->row['path_id'];
    }

    public function getProductAttributes($productId, $languageId, $attributeList = [])
    {
        $result = [];

        $productAttributeQuery = $this->db->query("SELECT pa.attribute_id, pa.text FROM " . DB_PREFIX . "product_attribute pa 
        WHERE pa.product_id = '" . (int) $productId . "' 
        AND pa.language_id = '" . $languageId . "'");

        if ($productAttributeQuery->num_rows) {
            foreach ($productAttributeQuery->rows as $productAttribute) {
                if (in_array($productAttribute['attribute_id'], $attributeList)) {
                    $attributeQuery = $this->db->query("SELECT ad.name, agd.name AS group_name FROM " . DB_PREFIX . "attribute a 
                    LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) 
                    LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (a.attribute_group_id = agd.attribute_group_id) 
                    WHERE a.attribute_id = '" . (int) $productAttribute['attribute_id'] . "' 
                    AND ad.language_id = '" . $languageId . "' 
                    AND agd.language_id = '" . $languageId . "'");

                    foreach ($attributeQuery->rows as $attribute) {
                        $result[] = array(
                            'group' => $attribute['group_name'],
                            'name' => $attribute['name'],
                            'value' => $productAttribute['text'],
                        );
                    }
                }
            }
        }

        return $result;

    }

    public function getProductOptions($productId, $languageId, $optionList = [])
    {
        $productOptionData = [];

        $optionQuery = $this->db->query("SELECT od.name, po.option_id, po.product_option_id, po.value FROM " . DB_PREFIX . "product_option po 
        LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) 
        LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) 
        WHERE po.product_id = '" . (int) $productId . "' 
        AND od.language_id = '" . (int) $this->config->get('config_language_id') . "' ORDER BY o.sort_order");

        foreach ($optionQuery->rows as $option) {
            if (in_array($option['option_id'], $optionList)) {
                $optionValueQuery = $this->db->query("SELECT pov.product_option_value_id, ovd.name, pov.quantity, pov.price, pov.price_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov 
                LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) 
                LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) 
                WHERE pov.product_id = '" . (int) $productId . "' 
                AND pov.product_option_id = '" . (int) $option['product_option_id'] . "' 
                AND ovd.language_id = '" . (int) $languageId . "' ORDER BY ov.sort_order");

                foreach ($optionValueQuery->rows as $optionValue) {
                    $productOptionData[] = array(
                        'id'            => $optionValue['product_option_value_id'],
                        'group_name'    => $option['name'],
                        'value'         => $option['value'],
                        'name'          => $optionValue['name'],
                        'quantity'      => $optionValue['quantity'],
                        'price'         => $optionValue['price'],
                        'price_prefix'  => $optionValue['price_prefix'],
                        'weight'        => $optionValue['weight'],
                        'weight_prefix' => $optionValue['weight_prefix'],
                    );
                }
            }
        }

        return $productOptionData;
    }
}
