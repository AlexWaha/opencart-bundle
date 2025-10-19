<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

namespace Alexwaha;

class EasyCheckoutHelper
{
    private string $moduleName = 'aw_easy_checkout';

    private Core $core;

    private Config $moduleConfig;

    private $registry;

    private static $countryPhoneMasks = null;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->core = new Core($registry);
        $this->moduleConfig = $this->core->getConfig($this->moduleName);
    }

    public function shouldRedirect()
    {
        $route = $_GET['route'] ?? ($_GET['_route_'] ?? '');
        $status = $this->moduleConfig->get('status');

        if (! $status) {
            return false;
        }

        if ($this->moduleConfig->get('replace_cart') && ($route === 'checkout/cart' || $route === 'cart')) {
            return 'extension/' . $this->moduleName . '/main';
        }

        if ($this->moduleConfig->get('replace_checkout') && ($route === 'checkout/checkout' || $route === 'checkout')) {
            return 'extension/' . $this->moduleName . '/main';
        }

        return false;
    }

    public function rewrite($url)
    {
        $route = $_GET['route'] ?? ($_GET['_route_'] ?? '');
        $status = $this->moduleConfig->get('status');

        $config = $this->registry->get('config');
        $storeId = (int)$config->get('config_store_id');
        $languageId = (int)$config->get('config_language_id');

        $seoKeyword = $this->getSeoKeyword($storeId, $languageId);

        if ($status && $seoKeyword) {
            $baseUrl = $config->get('config_ssl') ?: $config->get('config_url');
            $easyCheckoutUrl = rtrim($baseUrl, '/') . '/' . $seoKeyword;

            $pages = [
                'checkout/buy',
                'checkout/checkout',
                'checkout/oct_fastorder',
                'checkout/newstorecheckout',
                'checkout/pixelshopcheckout',
                'revolution/revcheckout',
                'checkout/simplecheckout',
                'checkout/unicheckout',
                'checkout/uni_checkout',
                'checkout/onepcheckout',
                'lightcheckout/checkout',
            ];

            if ($this->moduleConfig->get('replace_cart') && $route != 'checkout/cart' && $route != 'cart') {
                if (strpos($url, 'checkout/cart') !== false) {
                    $url = str_replace('checkout/cart', 'extension/' . $this->moduleName . '/main', $url);
                }

                $cartUrl = rtrim($baseUrl, '/') . '/cart';
                if ($url === $cartUrl) {
                    $url = $easyCheckoutUrl;
                }
            }

            if ($this->moduleConfig->get('replace_checkout')) {
                foreach ($pages as $page) {
                    $shortPage = basename($page);
                    if ($route != $page && $route != $shortPage) {
                        if (strpos($url, $page) !== false) {
                            $url = str_replace($page, 'extension/' . $this->moduleName . '/main', $url);
                        }

                        if ($shortPage === 'checkout') {
                            $checkoutUrl = rtrim($baseUrl, '/') . '/checkout';
                            if ($url === $checkoutUrl) {
                                $url = $easyCheckoutUrl;
                            }
                        }
                    }
                }
            }
        }

        if ($seoKeyword && strpos($url, 'route=extension/' . $this->moduleName . '/main') !== false) {
            $urlParts = parse_url(str_replace('&amp;', '&', $url));
            parse_str($urlParts['query'] ?? '', $queryParams);

            unset($queryParams['route']);

            $newUrl = str_replace('index.php?route=extension/' . $this->moduleName . '/main', $seoKeyword, $url);

            if (!empty($queryParams)) {
                $newUrl = preg_replace('/[?&].*$/', '', $newUrl);
                $newUrl .= '?' . http_build_query($queryParams);
            }

            $url = str_replace('&', '&amp;', $newUrl);
        }

        return $url;
    }

    private function getSeoKeyword($storeId, $languageId)
    {
        $seoUrls = $this->core->getSeoUrls('extension/' . $this->moduleName . '/main');

        if (empty($seoUrls)) {
            return null;
        }

        if (isset($seoUrls[$storeId][$languageId])) {
            return $seoUrls[$storeId][$languageId];
        }

        foreach ($seoUrls as $store => $languages) {
            if (is_array($languages) && isset($languages[$languageId])) {
                return $languages[$languageId];
            }
        }

        return null;
    }

    /**
     * Get all country phone masks from JSON file
     *
     * @return array
     */
    public function getCountryPhoneMasks(): array
    {
        if (self::$countryPhoneMasks === null) {
            $jsonPath = DIR_SYSTEM . 'library/Alexwaha/fixtures/country_phone_masks.json';

            if (file_exists($jsonPath)) {
                $jsonContent = file_get_contents($jsonPath);
                self::$countryPhoneMasks = json_decode($jsonContent, true) ?: [];
            } else {
                self::$countryPhoneMasks = [];
            }
        }

        return self::$countryPhoneMasks;
    }

    /**
     * Get phone mask for specific country by ISO code
     *
     * @param string $isoCode ISO 3166-1 alpha-2 code (e.g., 'UA', 'US', 'GB')
     * @return string|null
     */
    public function getCountryPhoneMask(string $isoCode): ?string
    {
        $masks = $this->getCountryPhoneMasks();
        $isoCode = strtoupper($isoCode);

        return $masks[$isoCode]['mask'] ?? null;
    }

    /**
     * Get phone mask by country ID from database
     *
     * @param int $countryId
     * @return string|null
     */
    public function getPhoneMaskByCountryId(int $countryId): ?string
    {
        $db = $this->registry->get('db');

        $query = $db->query("SELECT iso_code_2 FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$countryId . "'");

        if ($query->num_rows) {
            $isoCode = $query->row['iso_code_2'];
            return $this->getCountryPhoneMask($isoCode);
        }

        return null;
    }

    /**
     * Get default phone mask based on config_country_id
     *
     * @return string|null
     */
    public function getDefaultPhoneMask(): ?string
    {
        $config = $this->registry->get('config');
        $countryId = $config->get('config_country_id');

        if ($countryId) {
            return $this->getPhoneMaskByCountryId($countryId);
        }

        return null;
    }

    /**
     * Get country data by ISO code (includes name, dial_code, mask)
     *
     * @param string $isoCode
     * @return array|null
     */
    public function getCountryData(string $isoCode): ?array
    {
        $masks = $this->getCountryPhoneMasks();
        $isoCode = strtoupper($isoCode);

        return $masks[$isoCode] ?? null;
    }

    /**
     * Get country ISO code by country ID from database
     *
     * @param int $countryId
     * @return string|null
     */
    public function getCountryIsoCode(int $countryId): ?string
    {
        $db = $this->registry->get('db');

        $query = $db->query("SELECT iso_code_2 FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$countryId . "'");

        if ($query->num_rows) {
            return $query->row['iso_code_2'];
        }

        return null;
    }
}
