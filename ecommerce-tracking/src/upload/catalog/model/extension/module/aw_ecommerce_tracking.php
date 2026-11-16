<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwEcommerceTracking extends Model
{
    private const META_GRAPH_API_VERSION = 'v21.0';

    private const CALLING_CODES = [
        'US' => '1', 'CA' => '1', 'RU' => '7', 'KZ' => '7', 'EG' => '20', 'ZA' => '27', 'GR' => '30',
        'NL' => '31', 'BE' => '32', 'FR' => '33', 'ES' => '34', 'HU' => '36', 'IT' => '39', 'RO' => '40',
        'CH' => '41', 'AT' => '43', 'GB' => '44', 'DK' => '45', 'SE' => '46', 'NO' => '47', 'PL' => '48',
        'DE' => '49', 'PE' => '51', 'MX' => '52', 'CU' => '53', 'AR' => '54', 'BR' => '55', 'CL' => '56',
        'CO' => '57', 'VE' => '58', 'MY' => '60', 'AU' => '61', 'ID' => '62', 'PH' => '63', 'NZ' => '64',
        'SG' => '65', 'TH' => '66', 'JP' => '81', 'KR' => '82', 'VN' => '84', 'CN' => '86', 'TR' => '90',
        'IN' => '91', 'PK' => '92', 'AF' => '93', 'LK' => '94', 'MM' => '95', 'IR' => '98', 'SS' => '211',
        'MA' => '212', 'DZ' => '213', 'TN' => '216', 'LY' => '218', 'GM' => '220', 'SN' => '221',
        'MR' => '222', 'ML' => '223', 'GN' => '224', 'CI' => '225', 'BF' => '226', 'NE' => '227',
        'TG' => '228', 'BJ' => '229', 'MU' => '230', 'LR' => '231', 'SL' => '232', 'GH' => '233',
        'NG' => '234', 'TD' => '235', 'CF' => '236', 'CM' => '237', 'CV' => '238', 'ST' => '239',
        'GQ' => '240', 'GA' => '241', 'CG' => '242', 'CD' => '243', 'AO' => '244', 'GW' => '245',
        'IO' => '246', 'SC' => '248', 'SD' => '249', 'RW' => '250', 'ET' => '251', 'SO' => '252',
        'DJ' => '253', 'KE' => '254', 'TZ' => '255', 'UG' => '256', 'BI' => '257', 'MZ' => '258',
        'ZM' => '260', 'MG' => '261', 'RE' => '262', 'ZW' => '263', 'NA' => '264', 'MW' => '265',
        'LS' => '266', 'BW' => '267', 'SZ' => '268', 'KM' => '269', 'SH' => '290', 'ER' => '291',
        'AW' => '297', 'FO' => '298', 'GL' => '299', 'GI' => '350', 'PT' => '351', 'LU' => '352',
        'IE' => '353', 'IS' => '354', 'AL' => '355', 'MT' => '356', 'CY' => '357', 'FI' => '358',
        'BG' => '359', 'LT' => '370', 'LV' => '371', 'EE' => '372', 'MD' => '373', 'AM' => '374',
        'BY' => '375', 'AD' => '376', 'MC' => '377', 'SM' => '378', 'VA' => '379', 'UA' => '380',
        'RS' => '381', 'ME' => '382', 'XK' => '383', 'HR' => '385', 'SI' => '386', 'BA' => '387',
        'MK' => '389', 'CZ' => '420', 'SK' => '421', 'LI' => '423', 'FK' => '500', 'BZ' => '501',
        'GT' => '502', 'SV' => '503', 'HN' => '504', 'NI' => '505', 'CR' => '506', 'PA' => '507',
        'PM' => '508', 'HT' => '509', 'GP' => '590', 'BO' => '591', 'GY' => '592', 'EC' => '593',
        'GF' => '594', 'PY' => '595', 'MQ' => '596', 'SR' => '597', 'UY' => '598', 'CW' => '599',
        'TL' => '670', 'NF' => '672', 'BN' => '673', 'NR' => '674', 'PG' => '675', 'TO' => '676',
        'SB' => '677', 'VU' => '678', 'FJ' => '679', 'PW' => '680', 'WF' => '681', 'CK' => '682',
        'NU' => '683', 'WS' => '685', 'KI' => '686', 'NC' => '687', 'TV' => '688', 'PF' => '689',
        'TK' => '690', 'FM' => '691', 'MH' => '692', 'KP' => '850', 'HK' => '852', 'MO' => '853',
        'KH' => '855', 'LA' => '856', 'BD' => '880', 'TW' => '886', 'MV' => '960', 'LB' => '961',
        'JO' => '962', 'SY' => '963', 'IQ' => '964', 'KW' => '965', 'SA' => '966', 'YE' => '967',
        'OM' => '968', 'PS' => '970', 'AE' => '971', 'IL' => '972', 'BH' => '973', 'QA' => '974',
        'BT' => '975', 'MN' => '976', 'NP' => '977', 'TJ' => '992', 'TM' => '993', 'AZ' => '994',
        'GE' => '995', 'KG' => '996', 'UZ' => '998',
    ];

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
                'tax' => $row['tax'],
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

    private function claimMetaPurchase(int $orderId): bool
    {
        $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "aw_ecommerce_tracking_purchase` (order_id) VALUES ('" . $orderId . "')");

        $this->db->query("
            UPDATE `" . DB_PREFIX . "aw_ecommerce_tracking_purchase`
            SET meta_sent_at = NOW()
            WHERE order_id = '" . $orderId . "'
              AND meta_sent_at IS NULL
        ");

        return $this->db->countAffected() === 1;
    }

    private function releaseMetaPurchaseClaim(int $orderId): void
    {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "aw_ecommerce_tracking_purchase`
            SET meta_sent_at = NULL
            WHERE order_id = '" . $orderId . "'
        ");
    }

    public function sendMetaCapiPurchase(int $orderId): void
    {
        $config = $this->awCore->getConfig('aw_ecommerce_tracking');

        $pixelId = trim((string) $config->get('meta_pixel_id', ''));
        $capiToken = trim((string) ($config->all()['meta_capi_token'] ?? ''));

        if ($pixelId === '' || $capiToken === '') {
            return;
        }

        $order = $this->getMetaOrderData($orderId);

        if (empty($order) || !in_array((int) $order['order_status_id'], $this->getSuccessfulOrderStatuses(), true)) {
            return;
        }

        $gdprConfig = $this->awCore->getConfig('aw_gdpr_consent');

        if ($gdprConfig->get('status', false) && $this->getConsent($orderId)['ad_user_data'] !== 1) {
            return;
        }

        if (!$this->claimMetaPurchase($orderId)) {
            return;
        }

        $items = $this->getOrderProducts($orderId);

        if (empty($items)) {
            $this->releaseMetaPurchaseClaim($orderId);

            return;
        }

        $purchaseValueMode = (string) $config->get('purchase_value_mode', 'order_total');
        $priceWithTax = (bool) $config->get('price_with_tax', true);
        $event = $this->buildMetaPurchaseEvent($orderId, $order, $items, $purchaseValueMode, $priceWithTax);
        $testEventCode = trim((string) $config->get('meta_test_event_code', ''));

        if (!$this->sendMetaCapiRequest($pixelId, $capiToken, $event, $testEventCode)) {
            $this->releaseMetaPurchaseClaim($orderId);
        }
    }

    public function getSuccessfulOrderStatuses(): array
    {
        $statuses = array_unique(array_map('intval', array_merge(
            (array) $this->config->get('config_processing_status'),
            (array) $this->config->get('config_complete_status')
        )));

        return array_values(array_diff($statuses, [(int) $this->config->get('config_order_status_id')]));
    }

    private function getMetaOrderData(int $orderId): array
    {
        $query = $this->db->query("
            SELECT o.order_id, o.order_status_id, o.date_added, o.email, o.telephone, o.firstname, o.lastname, o.customer_id,
                o.ip, o.user_agent, o.payment_city, o.payment_postcode, o.currency_code, o.currency_value, o.total,
                c.iso_code_2 AS payment_iso_code_2, p.fbp, p.fbc
            FROM " . DB_PREFIX . "order o
            LEFT JOIN " . DB_PREFIX . "country c ON (c.country_id = o.payment_country_id)
            LEFT JOIN " . DB_PREFIX . "aw_ecommerce_tracking_purchase p ON (p.order_id = o.order_id)
            WHERE o.order_id = '" . $orderId . "'
        ");

        return $query->row ?: [];
    }

    private function buildMetaPurchaseEvent(int $orderId, array $order, array $items, string $purchaseValueMode, bool $priceWithTax): array
    {
        $currencyValue = (float) ($order['currency_value'] ?? 0) ?: 1.0;

        $contentIds = [];
        $contents = [];
        $itemsValueExTax = 0;

        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];
            $priceExTax = (float) ($item['price'] ?? 0);
            $displayPrice = ($purchaseValueMode !== 'items_only' && $priceWithTax)
                ? $priceExTax + (float) ($item['tax'] ?? 0)
                : $priceExTax;

            $price = round($displayPrice * $currencyValue, 2);
            $itemsValueExTax += $priceExTax * $currencyValue * $quantity;

            $contentIds[] = (string) $item['product_id'];
            $contents[] = [
                'id' => (string) $item['product_id'],
                'quantity' => $quantity,
                'item_price' => $price,
            ];
        }

        $userData = [
            'client_ip_address' => (string) ($order['ip'] ?? ''),
            'client_user_agent' => (string) ($order['user_agent'] ?? ''),
        ];

        $this->addHashedField($userData, 'em', (string) ($order['email'] ?? ''));
        $this->addHashedField($userData, 'ph', $this->normalizeMetaPhone((string) ($order['telephone'] ?? ''), (string) ($order['payment_iso_code_2'] ?? '')));
        $this->addHashedField($userData, 'fn', (string) ($order['firstname'] ?? ''));
        $this->addHashedField($userData, 'ln', (string) ($order['lastname'] ?? ''));
        $this->addHashedField($userData, 'ct', $this->normalizeMetaCity((string) ($order['payment_city'] ?? '')));
        $this->addHashedField($userData, 'zp', (string) ($order['payment_postcode'] ?? ''));
        $this->addHashedField($userData, 'country', (string) ($order['payment_iso_code_2'] ?? ''));

        $customerId = (int) ($order['customer_id'] ?? 0);
        if ($customerId > 0) {
            $this->addHashedField($userData, 'external_id', (string) $customerId);
        }

        if (!empty($order['fbp'])) {
            $userData['fbp'] = $order['fbp'];
        }

        if (!empty($order['fbc'])) {
            $userData['fbc'] = $order['fbc'];
        }

        return [
            'event_name' => 'Purchase',
            'event_time' => strtotime((string) ($order['date_added'] ?? '')) ?: time(),
            'event_id' => (string) $orderId,
            'action_source' => 'website',
            'event_source_url' => rtrim((string) HTTPS_SERVER, '/') . '/index.php?route=checkout/success',
            'user_data' => $userData,
            'custom_data' => [
                'currency' => (string) ($order['currency_code'] ?? ''),
                'value' => $purchaseValueMode === 'items_only'
                    ? round($itemsValueExTax, 2)
                    : round((float) ($order['total'] ?? 0) * $currencyValue, 2),
                'content_ids' => $contentIds,
                'contents' => $contents,
                'content_type' => 'product',
                'num_items' => count($contents),
                'order_id' => (string) $orderId,
            ],
        ];
    }

    private function addHashedField(array &$userData, string $key, string $value): void
    {
        $normalized = $this->normalizeMetaValue($value);

        if ($normalized !== '') {
            $userData[$key] = hash('sha256', $normalized);
        }
    }

    private function normalizeMetaValue(string $value): string
    {
        return trim(mb_strtolower($value, 'UTF-8'));
    }

    private function normalizeMetaCity(string $city): string
    {
        return preg_replace('/[^a-z0-9]/u', '', $this->normalizeMetaValue($city)) ?? '';
    }

    private function normalizeMetaPhone(string $phone, string $isoCode2): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        $callingCode = self::CALLING_CODES[strtoupper($isoCode2)] ?? '';

        if ($callingCode !== '' && strpos($digits, $callingCode) !== 0) {
            $digits = ltrim($digits, '0');
            $digits = $callingCode . $digits;
        }

        return $digits;
    }

    private function sendMetaCapiRequest(string $pixelId, string $capiToken, array $event, string $testEventCode): bool
    {
        $endpoint = 'https://graph.facebook.com/' . self::META_GRAPH_API_VERSION . '/' . rawurlencode($pixelId) . '/events';

        $payload = [
            'data' => [$event],
            'access_token' => $capiToken,
        ];

        if ($testEventCode !== '') {
            $payload['test_event_code'] = $testEventCode;
        }

        return $this->postJson($endpoint, $payload)['ok'];
    }

    public function postJson(string $url, array $payload): array
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 4,
        ]);

        $body = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        return [
            'ok' => $error === '' && $httpCode > 0 && $httpCode < 300,
            'http_code' => $httpCode,
            'body' => is_string($body) ? $body : '',
        ];
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
