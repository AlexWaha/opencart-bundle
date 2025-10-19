<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutAddress extends Controller
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
        $this->load->language('extension/' . $this->moduleName . '/lang');

        $this->load->model('account/address');
        $this->load->model('extension/' . $this->moduleName . '/model');
        $this->load->model('localisation/country');

        $addressType = $data['addressType'] ?? 'shipping';

        $addressCustomFields = $this->model_extension_aw_easy_checkout_model->getCustomFields(
            'address',
            $this->config->get('config_customer_group_id')
        );

        $shippingAddressData = $this->moduleConfig->get('shipping_address', []);

        $customerLogged = $this->customer->isLogged();
        $langId = $this->config->get('config_language_id');

        $shippingMethodCode = ! empty($this->session->data['shipping_method']['code']) ? str_replace(
            '.',
            '_',
            $this->session->data['shipping_method']['code']
        ) : null;

        $data['shippingAddressFields'] = [];

        $shippingAddressFields = ! empty($shippingAddressData[$shippingMethodCode]) ? $shippingAddressData[$shippingMethodCode] : $shippingAddressData['default'];

        $this->sortFieldsBySortOrder($shippingAddressFields);

        foreach ($shippingAddressFields as $fieldKey => $addressField) {
            if (! is_array($addressField)) {
                continue;
            }

            if (strpos($fieldKey, 'custom_field_') === 0) {
                if (! empty($addressCustomFields[$addressField['id']])) {
                    if ($customerLogged && $addressField['show_when'] == 'guest') {
                        continue;
                    }
                    if (! $customerLogged && $addressField['show_when'] == 'authorized') {
                        continue;
                    }

                    $data['shippingAddressFields'][$fieldKey] = $addressCustomFields[$addressField['id']];
                }
                continue;
            }

            if (! empty($addressField['status']) && $addressField['status'] != '0') {
                if ($customerLogged && $addressField['show_when'] == 'guest') {
                    continue;
                }
                if (! $customerLogged && $addressField['show_when'] == 'authorized') {
                    continue;
                }

                if (! empty($addressField['setting']['field_name'][$langId])) {
                    $data['entry_' . $fieldKey] = $addressField['setting']['field_name'][$langId];
                }
                if (! empty($addressField['setting']['placeholder_field'][$langId])) {
                    $data['entry_placeholder_' . $fieldKey] = $addressField['setting']['placeholder_field'][$langId];
                }

                $customFields = [];
                if (! empty($addressField['setting']['custom_fields'])) {
                    foreach ($addressField['setting']['custom_fields'] as $customFieldValue => $customField) {
                        if (! empty($customField[$langId]['name'])) {
                            $customFields[] = [
                                'value' => $customFieldValue,
                                'name' => $customField[$langId]['name'],
                            ];
                        }
                    }
                }

                $addressFieldType = $addressField['setting']['type'] ?? 'input';
                $defaultSelectCustomField = $addressField['setting']['custom_field_default_select'] ?? '';

                if (
                    $defaultSelectCustomField && in_array($fieldKey, [
                        'city',
                        'address_1',
                        'address_2',
                        'postcode',
                        'company',
                    ], true) && empty($this->session->data['shipping_address'][$fieldKey])
                ) {
                    $this->session->data['shipping_address'][$fieldKey] = $defaultSelectCustomField;
                }

                $data['shippingAddressFields'][$fieldKey] = [
                    'status' => $addressField['status'],
                    'type' => $addressFieldType,
                    'default_select_custom_field' => $defaultSelectCustomField,
                    'custom_fields' => $customFields,
                ];
            }
        }

        $sessionAddressKey = $addressType . '_address';
        $address = $this->session->data[$sessionAddressKey] ?? [];

        $shippingMethodCode = $this->session->data['shipping_method']['code'] ?? null;
        $addressFields = [
            'city',
            'address_1',
            'address_2',
            'postcode',
            'company',
        ];

        if ($addressType === 'shipping') {
            foreach ($addressFields as $field) {
                if ($shippingMethodCode && isset($this->session->data['shipping_method_address'][$shippingMethodCode][$field])) {
                    $fieldValue = $this->session->data['shipping_method_address'][$shippingMethodCode][$field];
                    $this->session->data[$sessionAddressKey][$field] = $fieldValue;
                    $data[$field] = $fieldValue;
                } else {
                    $data[$field] = $address[$field] ?? '';
                }
            }
        } else {
            foreach ($addressFields as $field) {
                $data[$field] = $address[$field] ?? '';
            }
        }

        $data['country_id'] = ! empty($address['country_id']) ? $address['country_id'] : $this->config->get('config_country_id');

        $data['zone_id'] = $address['zone_id'] ?? '';
        $data['countries'] = $this->model_localisation_country->getCountries();

        $countryInfo = $this->model_localisation_country->getCountry($data['country_id']);
        if ($countryInfo) {
            $this->load->model('localisation/zone');
            $data['zones'] = $this->model_localisation_zone->getZonesByCountryId($data['country_id']);
        }

        $data['address_custom_field'] = $this->session->data['guest']['address_custom_field'] = $this->request->post['custom_field']['address'] ?? ($this->session->data['guest']['address_custom_field'] ?? []);
        $data['guest_custom_field'] = $data['address_custom_field'];

        $data['addressType'] = $addressType;

        $paymentAddressSameAsShipping = $this->moduleConfig->get('payment_address_same_as_shipping', true);
        $data['showPaymentAddressCheckbox'] = !$paymentAddressSameAsShipping;

        if (!isset($this->session->data['payment_address_same_as_shipping'])) {
            $this->session->data['payment_address_same_as_shipping'] = $paymentAddressSameAsShipping ? 1 : 0;
        }
        $data['paymentAddressSameAsShippingChecked'] = $this->session->data['payment_address_same_as_shipping'];

        $data['showCustomerAddresses'] = false;
        $data['customerAddresses'] = [];
        $data['currentAddressId'] = 0;

        if ($addressType === 'shipping' && $customerLogged) {
            $showAddressSelector = $this->moduleConfig->get('show_customer_addresses', false);
            if ($showAddressSelector) {
                $data['showCustomerAddresses'] = true;
                $data['customerAddresses'] = $this->model_account_address->getAddresses();
                $data['currentAddressId'] = $this->session->data['shipping_address']['address_id'] ?? $this->customer->getAddressId();
            }
        }

        return $this->awCore->render('extension/' . $this->moduleName . '/address', $data);
    }

    private function sortFieldsBySortOrder(&$fields)
    {
        if (is_array($fields)) {
            uasort($fields, function ($a, $b) {
                $sortA = isset($a['sort_order']) ? (int) $a['sort_order'] : 0;
                $sortB = isset($b['sort_order']) ? (int) $b['sort_order'] : 0;

                return $sortA - $sortB;
            });
        }
    }
}
