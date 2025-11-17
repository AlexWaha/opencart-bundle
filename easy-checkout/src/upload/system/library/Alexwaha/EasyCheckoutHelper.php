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

    public function rewrite($url)
    {
        $getRoute = isset($_GET['route']) ? $_GET['route'] : (isset($_GET['_route_']) ? $_GET['_route_'] : '');
        $status = $this->moduleConfig->get('status');

        if (!$status) {
            return $url;
        }

        // Replace cart links
        if ($this->moduleConfig->get('replace_cart') && strpos($url, 'checkout/cart') !== false && $getRoute != 'checkout/cart') {
            $url = str_replace('checkout/cart', 'extension/' . $this->moduleName . '/main', $url);
        }

        // Replace checkout links
        if ($this->moduleConfig->get('replace_checkout') && strpos($url, 'checkout/checkout') !== false && $getRoute != 'checkout/checkout') {
            $url = str_replace('checkout/checkout', 'extension/' . $this->moduleName . '/main', $url);
        }

        // Replace other checkout pages
        if ($this->moduleConfig->get('replace_checkout')) {
            $pages = [
                'checkout/buy',
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

            foreach ($pages as $page) {
                if (strpos($url, $page) !== false && $getRoute != $page) {
                    $url = str_replace($page, 'extension/' . $this->moduleName . '/main', $url);
                    break;
                }
            }
        }

        return $url;
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
