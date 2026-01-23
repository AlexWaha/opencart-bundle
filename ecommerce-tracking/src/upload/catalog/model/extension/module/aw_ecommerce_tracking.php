<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwEcommerceTracking extends Model
{
    public function getCategoryName(int $productId): string
    {
        $query = $this->db->query("
            SELECT cd.name
            FROM " . DB_PREFIX . "product_to_category p2c
            LEFT JOIN " . DB_PREFIX . "category_description cd
                ON (p2c.category_id = cd.category_id)
            WHERE p2c.product_id = '" . $productId . "'
              AND cd.language_id = '" . (int) $this->config->get('config_language_id') . "'
            ORDER BY p2c.category_id ASC
            LIMIT 1
        ");

        return $query->row['name'] ?? '';
    }

    public function getManufacturerName(int $productId): string
    {
        $query = $this->db->query("
            SELECT m.name
            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
            WHERE p.product_id = '" . $productId . "'
        ");

        return $query->row['name'] ?? '';
    }

    public function getOrderInfo(int $orderId): array
    {
        $query = $this->db->query("
            SELECT *
            FROM " . DB_PREFIX . "order
            WHERE order_id = '" . $orderId . "'
        ");

        return $query->row ?: [];
    }

    public function getOrderProducts(int $orderId): array
    {
        $query = $this->db->query("
            SELECT
                op.order_product_id,
                op.product_id,
                op.name,
                op.model,
                op.quantity,
                op.price,
                op.total,
                op.tax,
                p.tax_class_id,
                m.name AS manufacturer
            FROM " . DB_PREFIX . "order_product op
            LEFT JOIN " . DB_PREFIX . "product p ON (op.product_id = p.product_id)
            LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
            WHERE op.order_id = '" . $orderId . "'
        ");

        $products = [];

        foreach ($query->rows as $row) {
            $product = [
                'product_id' => $row['product_id'],
                'name' => $row['name'],
                'model' => $row['model'],
                'quantity' => $row['quantity'],
                'price' => $row['price'],
                'tax_class_id' => $row['tax_class_id'] ?? 0,
                'manufacturer' => $row['manufacturer'] ?? '',
            ];

            $options = $this->getOrderProductOptions($orderId, (int) $row['order_product_id']);
            if ($options) {
                $product['option'] = $options;
            }

            $categoryName = $this->getCategoryName((int) $row['product_id']);
            if ($categoryName) {
                $product['category'] = $categoryName;
            }

            $products[] = $product;
        }

        return $products;
    }

    public function getOrderProductOptions(int $orderId, int $orderProductId): array
    {
        $query = $this->db->query("
            SELECT name, value
            FROM " . DB_PREFIX . "order_option
            WHERE order_id = '" . $orderId . "'
              AND order_product_id = '" . $orderProductId . "'
        ");

        return $query->rows;
    }

    public function getOrderTotals(int $orderId): array
    {
        $result = [
            'total' => 0,
            'subTotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'discount' => 0,
            'coupons' => [],
        ];

        $query = $this->db->query("
            SELECT code, title, value
            FROM " . DB_PREFIX . "order_total
            WHERE order_id = '" . $orderId . "'
            ORDER BY sort_order ASC
        ");

        foreach ($query->rows as $row) {
            $code = $row['code'];
            $value = (float) $row['value'];

            switch ($code) {
                case 'total':
                    $result['total'] = $value;
                    break;

                case 'sub_total':
                    $result['subTotal'] = $value;
                    break;

                case 'tax':
                    $result['tax'] += $value;
                    break;

                case 'shipping':
                    $result['shipping'] = $value;
                    break;

                case 'coupon':
                case 'voucher':
                case 'reward':
                    $result['discount'] += abs($value);
                    $result['coupons'][] = $this->extractCouponCode($row['title']);
                    break;
            }
        }

        return $result;
    }

    public function getProductById(int $productId): array
    {
        $query = $this->db->query("
            SELECT
                p.product_id,
                pd.name,
                p.price,
                p.tax_class_id,
                p.model,
                m.name AS manufacturer,
                (SELECT price FROM " . DB_PREFIX . "product_special ps
                 WHERE ps.product_id = p.product_id
                   AND ps.customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "'
                   AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW())
                   AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))
                 ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special
            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "product_description pd
                ON (p.product_id = pd.product_id)
            LEFT JOIN " . DB_PREFIX . "manufacturer m
                ON (p.manufacturer_id = m.manufacturer_id)
            WHERE p.product_id = '" . $productId . "'
              AND pd.language_id = '" . (int) $this->config->get('config_language_id') . "'
        ");

        if (!$query->row) {
            return [];
        }

        $product = $query->row;

        $categoryName = $this->getCategoryName($productId);
        if ($categoryName) {
            $product['category'] = $categoryName;
        }

        return $product;
    }

    private function extractCouponCode(string $title): string
    {
        if (preg_match('/\(([^)]+)\)/', $title, $matches)) {
            return trim($matches[1]);
        }

        return trim($title);
    }
}
