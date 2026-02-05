<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutPaymentMethod extends Controller
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

        $this->load->language('extension/' . $this->moduleName . '/lang');

        $this->load->model('extension/' . $this->moduleName . '/model');
        $this->load->model('account/address');
        $this->load->model('tool/image');

        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/extension');
        } else {
            $this->load->model('setting/extension');
        }

        $paymentMethodAddress = [];

        $addressFields = [
            'firstname',
            'lastname',
            'address_1',
            'address_2',
            'postcode',
            'company',
        ];
        foreach ($addressFields as $field) {
            $paymentMethodAddress[$field] = $this->request->post[$field] ?? $this->session->data['payment_address'][$field] ?? '';
        }

        $city = $this->request->post['city'] ?? $this->session->data['payment_address']['city'] ?? '';
        $paymentMethodAddress['city'] = $paymentMethodAddress['shipping_city'] = $city;

        $countryId = $this->request->post['country_id'] ?? $this->session->data['payment_address']['country_id'] ?? $this->config->get('config_country_id');
        $paymentMethodAddress['country_id'] = $paymentMethodAddress['shipping_country_id'] = $countryId;

        $zoneId = $this->request->post['zone_id'] ?? $this->session->data['payment_address']['zone_id'] ?? $this->config->get('config_zone_id');
        $paymentMethodAddress['zone_id'] = $paymentMethodAddress['zone_country_id'] = $paymentMethodAddress['payment_zone_id'] = $zoneId;

        if (! empty($zoneId)) {
            $this->load->model('localisation/zone');
            $zoneInfo = $this->model_localisation_zone->getZone($zoneId);
            $paymentMethodAddress['zone'] = $this->session->data['payment_address']['zone'] = $zoneInfo ? $zoneInfo['name'] : '';
            $paymentMethodAddress['zone_code'] = $this->session->data['payment_address']['zone_code'] = $zoneInfo ? $zoneInfo['code'] : '';
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
                    $sourceField = is_numeric($key) ? $field : $field;
                    $paymentMethodAddress[$targetField] = $this->session->data['payment_address'][$targetField] = $countryInfo[$sourceField];
                }
            }
        }

        $this->session->data['payment_address'] = $paymentMethodAddress;
        $this->tax->setPaymentAddress($paymentMethodAddress['country_id'], $paymentMethodAddress['zone_id']);

        $totals = [];
        $taxes = $this->cart->getTaxes();
        $total = 0;

        $totalData = [
            'totals' => &$totals,
            'taxes' => &$taxes,
            'total' => &$total,
        ];

        if ($this->awCore->isLegacy()) {
            $results = $this->model_extension_extension->getExtensions('total');
        } else {
            $results = $this->model_setting_extension->getExtensions('total');
        }

        $sortOrder = array_column($results, null, 'code');

        foreach ($sortOrder as $key => $value) {
            $sortOrderKey = $this->awCore->isLegacy()
                ? $value['code'] . '_sort_order'
                : 'total_' . $value['code'] . '_sort_order';

            $sortOrder[$key] = $this->config->get($sortOrderKey);
        }

        array_multisort($sortOrder, SORT_ASC, $results);

        foreach ($results as $result) {
            $statusKey = $this->awCore->isLegacy()
                ? $result['code'] . '_status'
                : 'total_' . $result['code'] . '_status';

            if ($this->config->get($statusKey)) {
                $this->load->model('extension/total/' . $result['code']);
                $this->{'model_extension_total_' . $result['code']}->getTotal($totalData);
            }
        }

        $methodData = [];

        if ($this->awCore->isLegacy()) {
            $results = $this->model_extension_extension->getExtensions('payment');
        } else {
            $results = $this->model_setting_extension->getExtensions('payment');
        }

        $recurring = $this->cart->hasRecurringProducts();

        foreach ($results as $result) {

            $statusKey = $this->awCore->isLegacy()
                ? $result['code'] . '_status'
                : 'payment_' . $result['code'] . '_status';

            if ($this->config->get($statusKey)) {
                $this->load->model('extension/payment/' . $result['code']);
                $method = $this->{'model_extension_payment_' . $result['code']}->getMethod(
                    $this->session->data['payment_address'],
                    $total
                );

                if ($method) {
                    if ($recurring) {
                        if (
                            property_exists(
                                $this->{'model_extension_payment_' . $result['code']},
                                'recurringPayments'
                            ) && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()
                        ) {
                            $methodData[$result['code']] = $method;
                        }
                    } else {
                        $methodData[$result['code']] = $method;
                    }
                }
            }
        }

        $sortOrder = array_column($methodData, 'sort_order');
        array_multisort($sortOrder, SORT_ASC, $methodData);

        $paymentMethod = $this->moduleConfig->get('payment_methods', []);
        $langId = $this->config->get('config_language_id');
        $customerLogged = $this->customer->isLogged();

        $customerGroupId = $customerLogged ? $this->config->get('config_customer_group_id') : ($this->request->post['customer_group_id'] ?? $this->session->data['guest']['customer_group_id'] ?? $this->config->get('config_customer_group_id'));

        if (! $customerLogged) {
            $this->session->data['guest']['customer_group_id'] = $customerGroupId;
        }

        foreach ($methodData as $paymentCode => &$method) {
            $method['image'] = false;

            if (! empty($paymentMethod[$paymentCode]['image'])) {
                $method['image_width'] = $paymentMethod[$paymentCode]['image_width'] ?? 32;
                $method['image_height'] = $paymentMethod[$paymentCode]['image_height'] ?? 32;
                $method['image'] = $this->model_tool_image->resize(
                    $paymentMethod[$paymentCode]['image'],
                    $method['image_width'],
                    $method['image_height']
                );
            }

            if (! empty($paymentMethod[$paymentCode]['status_payment_method_title']) && ! empty($paymentMethod[$paymentCode]['title'][$langId])) {
                $method['title'] = html_entity_decode(
                    $paymentMethod[$paymentCode]['title'][$langId],
                    ENT_QUOTES,
                    'UTF-8'
                );
            }

            $method['payment_method_description'] = '';
            if (! empty($paymentMethod[$paymentCode]['status_payment_method_description']) && ! empty($paymentMethod[$paymentCode]['description'][$langId])) {
                $method['payment_method_description'] = html_entity_decode(
                    $paymentMethod[$paymentCode]['description'][$langId],
                    ENT_QUOTES,
                    'UTF-8'
                );
            }

            $method['auto_confirm'] = !empty($paymentMethod[$paymentCode]['auto_confirm']) ? 1 : 0;
        }
        unset($method);

        $selectedShippingMethod = $this->request->post['shipping_method'] ?? ($this->session->data['shipping_method']['code'] ?? '');

        $filteredMethodData = [];

        foreach ($methodData as $paymentCode => $method) {
            if (!empty($paymentMethod[$paymentCode])) {
                $availableShippingMethods = $paymentMethod[$paymentCode]['shipping'] ?? [];
                $hasMatchingShipping = empty($availableShippingMethods) || array_filter(
                    $availableShippingMethods,
                    fn ($shippingMethod) => $shippingMethod['code'] === $selectedShippingMethod
                );

                $userTypeAllowed = ($customerLogged && $paymentMethod[$paymentCode]['authorized'] == 1) || (! $customerLogged && $paymentMethod[$paymentCode]['guest'] == 1);

                if ($hasMatchingShipping && $userTypeAllowed) {
                    $filteredMethodData[$paymentCode] = $method;
                }
            } else {
                $filteredMethodData[$paymentCode] = $method;
            }
        }

        $this->session->data['payment_methods'] = $filteredMethodData;

        if (isset($this->request->post['payment_method']) && isset($filteredMethodData[$this->request->post['payment_method']])) {
            $this->session->data['payment_method'] = $filteredMethodData[$this->request->post['payment_method']];
        } elseif (! empty($filteredMethodData)) {
            if (empty($this->session->data['payment_method']) || ! isset($filteredMethodData[$this->session->data['payment_method']['code']])) {
                $this->session->data['payment_method'] = reset($filteredMethodData);
            }
        }

        $data['error_warning'] = empty($this->session->data['payment_methods']) ? sprintf(
            $this->language->get('error_no_payment'),
            $this->url->link('information/contact')
        ) : '';

        $data['payment_methods'] = $this->session->data['payment_methods'] ?? [];

        $data['payment_code'] = $this->session->data['payment_method']['code'] ?? '';

        $data['payment_custom_field'] = $this->session->data['guest']['payment_custom_field'] = $this->request->post['custom_field']['payment'] ?? ($this->session->data['guest']['payment_custom_field'] ?? []);

        if (isset($this->request->post['comment'])) {
            $this->session->data['comment'] = $this->request->post['comment'];
        }

        if (isset($this->request->post['agree'])) {
            $this->session->data['agree'] = $this->request->post['agree'];
        }

        $result = $this->awCore->render('extension/' . $this->moduleName . '/payment_method', $data);

        if ($render) {
            $this->response->setOutput($result);
        } else {
            return $result;
        }
    }
}
