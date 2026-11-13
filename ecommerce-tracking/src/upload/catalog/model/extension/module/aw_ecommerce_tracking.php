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

    public function captureClientId(
        int $orderId,
        ?string $gaCookie,
        ?string $gaSessionCookie,
        ?string $capturedClientId,
        ?string $capturedSessionId,
        ?string $fbp,
        ?string $fbc,
        ?int $consentAnalytics,
        ?int $consentAdUserData,
        ?int $consentAdPersonalization
    ): void {
        $clientId = $capturedClientId !== null ? $capturedClientId : $this->extractGaClientId($gaCookie);
        $sessionId = $capturedSessionId !== null ? $capturedSessionId : $this->extractGaSessionId($gaSessionCookie);

        if ($clientId === null && $sessionId === null && $fbp === null && $fbc === null
            && $consentAnalytics === null && $consentAdUserData === null && $consentAdPersonalization === null) {
            return;
        }

        $clientIdSql = $clientId !== null ? "'" . $this->db->escape($clientId) . "'" : 'NULL';
        $sessionIdSql = $sessionId !== null ? "'" . $this->db->escape($sessionId) . "'" : 'NULL';
        $fbpSql = $fbp !== null ? "'" . $this->db->escape($fbp) . "'" : 'NULL';
        $fbcSql = $fbc !== null ? "'" . $this->db->escape($fbc) . "'" : 'NULL';
        $consentAnalyticsSql = $consentAnalytics !== null ? (string) $consentAnalytics : 'NULL';
        $consentAdUserDataSql = $consentAdUserData !== null ? (string) $consentAdUserData : 'NULL';
        $consentAdPersonalizationSql = $consentAdPersonalization !== null ? (string) $consentAdPersonalization : 'NULL';

        try {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "aw_ecommerce_tracking_purchase` (order_id, ga_client_id, ga_session_id, fbp, fbc, consent_analytics, consent_ad_user_data, consent_ad_personalization)
                VALUES ('" . $orderId . "', " . $clientIdSql . ', ' . $sessionIdSql . ', ' . $fbpSql . ', ' . $fbcSql . ', ' . $consentAnalyticsSql . ', ' . $consentAdUserDataSql . ', ' . $consentAdPersonalizationSql . ")
                ON DUPLICATE KEY UPDATE ga_client_id = VALUES(ga_client_id), ga_session_id = VALUES(ga_session_id), fbp = VALUES(fbp), fbc = VALUES(fbc), consent_analytics = VALUES(consent_analytics), consent_ad_user_data = VALUES(consent_ad_user_data), consent_ad_personalization = VALUES(consent_ad_personalization)
            ");
        } catch (\Throwable $e) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "aw_ecommerce_tracking_purchase` (order_id, ga_client_id, ga_session_id, fbp, fbc)
                VALUES ('" . $orderId . "', " . $clientIdSql . ', ' . $sessionIdSql . ', ' . $fbpSql . ', ' . $fbcSql . ")
                ON DUPLICATE KEY UPDATE ga_client_id = VALUES(ga_client_id), ga_session_id = VALUES(ga_session_id), fbp = VALUES(fbp), fbc = VALUES(fbc)
            ");
        }
    }

    public function getGaClientId(int $orderId): ?string
    {
        $query = $this->db->query("
            SELECT ga_client_id
            FROM " . DB_PREFIX . "aw_ecommerce_tracking_purchase
            WHERE order_id = '" . $orderId . "'
        ");

        return !empty($query->row['ga_client_id']) ? $query->row['ga_client_id'] : null;
    }

    public function getGaSessionId(int $orderId): ?string
    {
        $query = $this->db->query("
            SELECT ga_session_id
            FROM " . DB_PREFIX . "aw_ecommerce_tracking_purchase
            WHERE order_id = '" . $orderId . "'
        ");

        return !empty($query->row['ga_session_id']) ? $query->row['ga_session_id'] : null;
    }

    public function getConsent(int $orderId): array
    {
        $query = $this->db->query("
            SELECT consent_analytics, consent_ad_user_data, consent_ad_personalization
            FROM " . DB_PREFIX . "aw_ecommerce_tracking_purchase
            WHERE order_id = '" . $orderId . "'
        ");

        $row = $query->row ?: [];

        return [
            'analytics' => isset($row['consent_analytics']) && $row['consent_analytics'] !== '' ? (int) $row['consent_analytics'] : null,
            'ad_user_data' => isset($row['consent_ad_user_data']) && $row['consent_ad_user_data'] !== '' ? (int) $row['consent_ad_user_data'] : null,
            'ad_personalization' => isset($row['consent_ad_personalization']) && $row['consent_ad_personalization'] !== '' ? (int) $row['consent_ad_personalization'] : null,
        ];
    }

    public function claimPurchase(int $orderId): bool
    {
        $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "aw_ecommerce_tracking_purchase` (order_id) VALUES ('" . $orderId . "')");

        $this->db->query("
            UPDATE `" . DB_PREFIX . "aw_ecommerce_tracking_purchase`
            SET sent_at = NOW()
            WHERE order_id = '" . $orderId . "'
              AND sent_at IS NULL
        ");

        return $this->db->countAffected() === 1;
    }

    public function releasePurchaseClaim(int $orderId): void
    {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "aw_ecommerce_tracking_purchase`
            SET sent_at = NULL
            WHERE order_id = '" . $orderId . "'
        ");
    }

    public function claimClientPurchase(int $orderId): bool
    {
        $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "aw_ecommerce_tracking_purchase` (order_id) VALUES ('" . $orderId . "')");

        $this->db->query("
            UPDATE `" . DB_PREFIX . "aw_ecommerce_tracking_purchase`
            SET client_claimed_at = NOW()
            WHERE order_id = '" . $orderId . "'
              AND client_claimed_at IS NULL
        ");

        return $this->db->countAffected() === 1;
    }

    public function releaseClientPurchaseClaim(int $orderId): void
    {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "aw_ecommerce_tracking_purchase`
            SET client_claimed_at = NULL
            WHERE order_id = '" . $orderId . "'
        ");
    }

    private function extractGaClientId(?string $gaCookie): ?string
    {
        if (!$gaCookie) {
            return null;
        }

        return preg_match('/^GA\d+\.\d+\.(\d{1,20}\.\d{1,20})$/', $gaCookie, $matches) ? $matches[1] : null;
    }

    private function extractGaSessionId(?string $gaSessionCookie): ?string
    {
        if (!$gaSessionCookie) {
            return null;
        }

        $parts = explode('.', $gaSessionCookie);
        $segment = $parts[2] ?? '';
        $sessionId = (isset($segment[0]) && $segment[0] === 's') ? explode('$', substr($segment, 1))[0] : $segment;

        return (ctype_digit($sessionId) && strlen($sessionId) <= 20) ? $sessionId : null;
    }
}
