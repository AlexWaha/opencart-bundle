<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutShippingMethod extends Controller
{
    private string $moduleName = 'aw_easy_checkout';

    private \Alexwaha\Config $moduleConfig;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
    }

    public function index($data = [])
    {
        $render = $data['render'] ?? true;
        unset($data['render']);

        if (! $this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
            return;
        }

        $this->load->language('extension/' . $this->moduleName . '/lang');

        $this->load->model('account/address');
        $this->load->model('tool/image');

        $shippingAddress = $this->session->data['shipping_address'] ?? [
            'country_id' => $this->config->get('config_country_id'),
            'zone_id' => $this->config->get('config_zone_id'),
            'firstname' => '',
            'lastname' => '',
            'company' => '',
            'address_1' => '',
        ];

        $addressFields = [
            'firstname',
            'lastname',
            'address_1',
            'address_2',
            'company',
            'postcode',
        ];
        foreach ($addressFields as $field) {
            $shippingAddress[$field] = $this->request->post[$field] ?? $this->session->data['shipping_address'][$field] ?? '';
        }

        $city = $this->request->post['city'] ?? $this->session->data['shipping_address']['city'] ?? '';
        $shippingAddress['city'] = $shippingAddress['shipping_city'] = $city;

        $countryId = $this->request->post['country_id'] ?? $this->session->data['shipping_address']['country_id'] ?? $this->config->get('config_country_id');
        $shippingAddress['country_id'] = $shippingAddress['shipping_country_id'] = $countryId;

        $zoneId = $this->request->post['zone_id'] ?? $this->session->data['shipping_address']['zone_id'] ?? $this->config->get('config_zone_id');
        $shippingAddress['zone_id'] = $shippingAddress['zone_country_id'] = $shippingAddress['shipping_zone_id'] = $zoneId;

        if (! empty($zoneId)) {
            $this->load->model('localisation/zone');
            $zoneInfo = $this->model_localisation_zone->getZone($zoneId);
            $shippingAddress['zone'] = $this->session->data['shipping_address']['zone'] = $zoneInfo ? $zoneInfo['name'] : '';
            $shippingAddress['zone_code'] = $this->session->data['shipping_address']['zone_code'] = $zoneInfo ? $zoneInfo['code'] : '';
        }

        if (! empty($countryId)) {
            $this->load->model('localisation/country');
            $data['countries'] = $this->model_localisation_country->getCountries();
            $countryInfo = $this->model_localisation_country->getCountry($countryId);

            if ($countryInfo) {
                $countryFields = [
                    'country' => 'name',
                    'iso_code_2',
                    'iso_code_3',
                    'address_format',
                ];

                foreach ($countryFields as $key => $field) {
                    $targetField = is_numeric($key) ? $field : $key;
                    $shippingAddress[$targetField] = $this->session->data['shipping_address'][$targetField] = $countryInfo[$field];
                }
            }
        }

        $this->session->data['shipping_address'] = $shippingAddress;

        $shippingMethods = $this->moduleConfig->get('shipping_methods', []);
        $availableShippingMethods = [];

        $this->tax->setShippingAddress($shippingAddress['country_id'], $shippingAddress['zone_id']);

        $methodData = [];

        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/extension');
            $results = $this->model_extension_extension->getExtensions('shipping');
        } else {
            $this->load->model('setting/extension');
            $results = $this->model_setting_extension->getExtensions('shipping');
        }

        foreach ($results as $result) {
            $statusKey = $this->awCore->isLegacy()
                ? $result['code'] . '_status'
                : 'shipping_' . $result['code'] . '_status';

            if ($this->config->get($statusKey)) {
                $this->load->model('extension/shipping/' . $result['code']);
                $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

                if ($quote) {
                    if (isset($quote['quote'][$result['code']]['cost']) && $quote['quote'][$result['code']]['cost'] == 0) {
                        $quote['quote'][$result['code']]['text'] = '';
                    }
                    $methodData[$result['code']] = [
                        'title' => $quote['title'],
                        'quote' => $quote['quote'],
                        'sort_order' => $quote['sort_order'],
                        'error' => $quote['error'],
                    ];
                }
            }
        }

        $sortOrder = array_column($methodData, 'sort_order');
        array_multisort($sortOrder, SORT_ASC, $methodData);
        unset($this->session->data['shipping_methods']);

        $langId = $this->config->get('config_language_id');

        foreach ($methodData as $code => $method) {
            if (! is_array($method['quote'])) {
                continue;
            }

            $availableMethods = [];

            foreach ($method['quote'] as $quoteCode => $quoteData) {
                $methodCode = $quoteData['code'];
                $normalizedCode = str_replace('.', '_', $methodCode);

                if (! isset($shippingMethods[$normalizedCode])) {
                    $availableMethods[$quoteCode] = $quoteData;

                    continue;
                }

                $settings = $shippingMethods[$normalizedCode];

                if (! empty($settings['image'])) {
                    $quoteData['image_width'] = $settings['image_width'] ?? 36;
                    $quoteData['image_height'] = $settings['image_height'] ?? 36;
                    $quoteData['image'] = $this->model_tool_image->resize(
                        $settings['image'],
                        $quoteData['image_width'],
                        $quoteData['image_height']
                    );
                }

                $shippingMethodStatus = true;

                if (isset($settings['shipping_method_show_all_countries']) && $settings['shipping_method_show_all_countries'] == 0) {
                    $shippingMethodStatus = false;
                }

                if (! empty($settings['shipping_method_show_for_countries']) && is_array($settings['shipping_method_show_for_countries'])) {
                    $shippingMethodStatus = in_array($countryId, $settings['shipping_method_show_for_countries']);
                }

                if (! empty($settings['shipping_method_hide_for_countries']) && is_array($settings['shipping_method_hide_for_countries'])) {
                    if (in_array($countryId, $settings['shipping_method_hide_for_countries'])) {
                        $shippingMethodStatus = false;
                    }
                }

                if (! empty($settings['status_shipping_method_new_title']) && ! empty($settings['shipping_method_new_title'][$langId])) {
                    $quoteData['title'] = html_entity_decode(
                        $settings['shipping_method_new_title'][$langId],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                }

                if ($shippingMethodStatus) {
                    $availableMethods[$quoteCode] = $quoteData;
                }
            }

            if (! empty($availableMethods)) {
                $method['quote'] = $availableMethods;
                $availableShippingMethods[$code] = $method;
            }
        }

        $this->session->data['shipping_methods'] = $availableShippingMethods;

        if (! empty($this->request->post['shipping_method']) && ! empty($availableShippingMethods)) {
            [
                $methodCode,
                $quoteCode
            ] = explode('.', $this->request->post['shipping_method']);
            if (isset($availableShippingMethods[$methodCode]['quote'][$quoteCode])) {
                $this->session->data['shipping_method'] = $availableShippingMethods[$methodCode]['quote'][$quoteCode];
            }
        } elseif (!empty($availableShippingMethods)) {
            if (empty($this->session->data['shipping_method']) || !isset($this->session->data['shipping_method']['code'])) {
                foreach ($availableShippingMethods as $method) {
                    if (is_array($method['quote'])) {
                        $this->session->data['shipping_method'] = reset($method['quote']);
                        break;
                    }
                }
            } else {
                [
                    $methodCode,
                    $quoteCode
                ] = explode('.', $this->session->data['shipping_method']['code']);
                if (!isset($availableShippingMethods[$methodCode]['quote'][$quoteCode])) {
                    foreach ($availableShippingMethods as $method) {
                        if (is_array($method['quote'])) {
                            $this->session->data['shipping_method'] = reset($method['quote']);
                            break;
                        }
                    }
                }
            }
        }

        $data['error_warning'] = empty($this->session->data['shipping_methods']) ? sprintf(
            $this->language->get('error_no_shipping'),
            $this->url->link('information/contact')
        ) : '';

        $data['shipping_methods'] = $data['shippingMethods'] = $availableShippingMethods;

        $data['shipping_code'] = $this->session->data['shipping_method']['code'] ?? '';

        $result = $this->awCore->render('extension/' . $this->moduleName . '/shipping_method', $data);

        if ($render) {
            $this->response->setOutput($result);
        } else {
            return $result;
        }
    }
}
