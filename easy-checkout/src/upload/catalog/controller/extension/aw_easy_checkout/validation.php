<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutValidation extends Controller
{
    private string $moduleName = 'aw_easy_checkout';

    private \Alexwaha\Config $moduleConfig;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
    }

    public function index()
    {
        $json = [];

        $this->load->language('extension/' . $this->moduleName . '/lang');

        $this->load->model('account/customer');

        if ((! $this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (! $this->cart->hasStock() && ! $this->config->get('config_stock_checkout'))) {
            $json['warning'] = $this->language->get('error_stock');
        }

        $minPriceOrder = $this->moduleConfig->get('min_price_order');
        $customerGroupId = $this->config->get('config_customer_group_id');

        if ((! empty($minPriceOrder[$customerGroupId]) && ($this->cart->getTotal() < $minPriceOrder[$customerGroupId]))) {
            $json['warning'] = sprintf(
                $this->language->get('text_min_totals_order'),
                $this->currency->format($minPriceOrder[$customerGroupId], $this->session->data['currency'])
            );
        }
        $products = $this->cart->getProducts();
        $productTotals = [];

        foreach ($products as $product) {
            $productTotals[$product['product_id']] = ($productTotals[$product['product_id']] ?? 0) + $product['quantity'];
        }

        foreach ($products as $product) {
            if ($product['minimum'] > $productTotals[$product['product_id']]) {
                $json['warning'] = sprintf(
                    $this->language->get('error_minimum'),
                    $product['name'],
                    $product['minimum']
                );
                break;
            }
        }

        if (! $this->customer->isLogged() && ! isset($this->request->post['register'])) {
            if (! $this->config->get('config_checkout_guest') || $this->config->get('config_customer_price')) {
                $json['warning'] = $this->language->get('error_register');
            }
        }

        if (
            isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array(
                $this->request->post['customer_group_id'],
                $this->config->get('config_customer_group_display')
            )
        ) {
            $customerGroupId = $this->request->post['customer_group_id'];
        } else {
            $customerGroupId = $this->config->get('config_customer_group_id');
        }

        $this->load->model('extension/' . $this->moduleName . '/model');

        $customerCustomFields = $this->model_extension_aw_easy_checkout_model->getCustomFields(
            'customer',
            $this->config->get('config_customer_group_id')
        );
        $addressCustomFields = $this->model_extension_aw_easy_checkout_model->getCustomFields(
            'address',
            $this->config->get('config_customer_group_id')
        );

        $customerMethodsData = $this->moduleConfig->get('customer', []);
        $shippingMethodsData = $this->moduleConfig->get('shipping_address', []);

        $shippingCode = ! empty($this->session->data['shipping_method']['code']) ? str_replace(
            '.',
            '_',
            $this->session->data['shipping_method']['code']
        ) : null;

        $customerFields = ! empty($customerMethodsData[$shippingCode]) ? $customerMethodsData[$shippingCode] : $customerMethodsData['default'];

        $shippingMethodsFields = ! empty($shippingMethodsData[$shippingCode]) ? $shippingMethodsData[$shippingCode] : $shippingMethodsData['default'];

        $paymentAddressSameAsShipping = $this->moduleConfig->get('payment_address_same_as_shipping', true);
        $checkboxChecked = isset($this->request->post['payment_address_same_as_shipping']) && $this->request->post['payment_address_same_as_shipping'] == '1';
        $paymentAddressFields = (! $paymentAddressSameAsShipping && ! $checkboxChecked) ? $shippingMethodsFields : [];

        if ($this->config->get('config_checkout_id')) {
            $this->load->model('catalog/information');

            $informationInfo = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

            if ($informationInfo && ! isset($this->request->post['agree'])) {
                $json['error']['agree'] = sprintf($this->language->get('error_agree'), $informationInfo['title']);
            }
        }

        $customerLogged = $this->customer->isLogged();

        $registerStatus = $this->moduleConfig->get('register_status', 'default');
        $registerRequired = ! $this->customer->isLogged() && $registerStatus === 'required';
        $registerCheckedInPost = ! empty($this->request->post['register']);

        if ($registerRequired || $registerCheckedInPost) {
            $customerFields['email']['status'] = 'required';
        }

        foreach ($customerFields as $fieldKey => $customerField) {
            if (is_array($customerField)) {
                $showWhen = $customerField['show_when'] ?? 'all';
                $shouldValidate = (! $customerLogged && ($showWhen == 'guest' || $showWhen == 'all')) || ($customerLogged && ($showWhen == 'authorized' || $showWhen == 'all'));

                if ($fieldKey == 'firstname' && isset($customerField['status']) && $customerField['status'] == 'required') {
                    $firstname = isset($this->request->post['firstname']) ? trim($this->request->post['firstname']) : '';
                    if ($shouldValidate) {
                        if ((utf8_strlen($firstname) < 1) || (utf8_strlen($firstname) > 32)) {
                            $json['error']['firstname'] = ! empty($customerField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $customerField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_firstname');
                        }
                    }
                }

                if ($fieldKey == 'lastname' && isset($customerField['status']) && $customerField['status'] == 'required') {
                    $lastname = isset($this->request->post['lastname']) ? trim($this->request->post['lastname']) : '';
                    if ($shouldValidate) {
                        if (((utf8_strlen($lastname) < 1) || (utf8_strlen($lastname) > 32))) {
                            $json['error']['lastname'] = ! empty($customerField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $customerField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_lastname');
                        }
                    }
                }

                if ($fieldKey == 'telephone' && isset($customerField['status']) && $customerField['status'] == 'required') {
                    $telephone = $this->request->post['telephone'] ?? '';
                    if ($shouldValidate) {
                        if (((utf8_strlen($telephone) < 3) || (utf8_strlen($telephone) > 32))) {
                            $json['error']['telephone'] = ! empty($customerField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $customerField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_telephone');
                        }
                    }
                }

                if ($fieldKey == 'fax' && isset($customerField['status']) && $customerField['status'] == 'required') {
                    $fax = isset($this->request->post['fax']) ? trim($this->request->post['fax']) : '';
                    if ($shouldValidate) {
                        if (((utf8_strlen($fax) < 1) || (utf8_strlen($fax) > 264))) {
                            $json['error']['fax'] = ! empty($customerField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $customerField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_fax');
                        }
                    }
                }

                if ($fieldKey == 'email' && (($customerField['status'] ?? '') == 'required' || ! empty($this->request->post['email']))) {
                    $email = $this->request->post['email'] ?? '';
                    if ($shouldValidate) {
                        $validationFailed = false;

                        if (isset($customerField['status']) && $customerField['status'] == 'required' && trim($email) === '') {
                            $validationFailed = true;
                        } elseif (trim($email) !== '') {
                            if (! empty($customerField['setting']['validation'])) {
                                if (! preg_match($customerField['setting']['validation'], $email)) {
                                    $validationFailed = true;
                                }
                            } else {
                                if ((utf8_strlen($email) > 96) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    $validationFailed = true;
                                }
                            }
                        }

                        if ($validationFailed) {
                            $json['error']['email'] = ! empty($customerField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $customerField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_email');
                        }
                    }

                    if (! $this->customer->isLogged()) {
                        if (! empty($this->request->post['register'])) {
                            if ($this->model_account_customer->getTotalCustomersByEmail($email)) {
                                $json['error']['warning'] = $this->language->get('error_exists');
                            }
                        }
                    }
                }

                if (strpos($fieldKey, 'custom_field_') === 0) {
                    if (! empty($customerCustomFields[$customerField['id']])) {
                        $customField = $customerCustomFields[$customerField['id']];
                        if ((! $customerLogged && ($customerField['show_when'] == 'guest')) || ($customerLogged && ($customerField['show_when'] == 'authorized')) || ($customerField['show_when'] == 'all')) {
                            if (($customField['location'] == 'customer') && in_array($customField['type'], ['text', 'textarea', 'date', 'time', 'datetime'])) {
                                $value = $this->request->post['custom_field']['customer'][$customField['custom_field_id']] ?? '';
                                $validationFailed = false;

                                if ($customField['required'] && trim($value) === '') {
                                    $validationFailed = true;
                                } elseif (trim($value) !== '') {
                                    if (! empty($customField['validation'])) {
                                        if (! preg_match($customField['validation'], $value)) {
                                            $validationFailed = true;
                                        }
                                    }
                                }

                                if ($validationFailed) {
                                    if (! empty($customField['text_error'])) {
                                        $json['error']['customer_custom_field' . $customField['custom_field_id']] = $customField['text_error'];
                                    } else {
                                        $json['error']['customer_custom_field' . $customField['custom_field_id']] = sprintf(
                                            $this->language->get('error_custom_field'),
                                            $customField['name']
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($shippingMethodsFields as $fieldKey => $shippingField) {
            if (is_array($shippingField)) {
                if (
                    $fieldKey == 'country' && ((isset($shippingField['status']) && $shippingField['status'] == 'required'))
                ) {
                    $countryId = $this->request->post['shipping_country_id'] ?? '';
                    if ((! $customerLogged && ($shippingField['show_when'] == 'guest')) || ($customerLogged && ($shippingField['show_when'] == 'authorized')) || ($shippingField['show_when'] == 'all')) {
                        if ($countryId == '') {
                            $json['error']['shipping_country_id'] = $this->language->get('error_country');
                        }
                    }
                }

                if (
                    $fieldKey == 'zone_id' && ((isset($shippingField['status']) && $shippingField['status'] == 'required'))
                ) {
                    $zoneId = $this->request->post['shipping_zone_id'] ?? '';
                    $countryId = $this->request->post['shipping_country_id'] ?? '';

                    if ((! $customerLogged && ($shippingField['show_when'] == 'guest')) || ($customerLogged && ($shippingField['show_when'] == 'authorized')) || ($shippingField['show_when'] == 'all')) {
                        $countryHasZones = false;
                        if ($countryId) {
                            $this->load->model('localisation/zone');
                            $zones = $this->model_localisation_zone->getZonesByCountryId($countryId);
                            $countryHasZones = !empty($zones);
                        }

                        if ($countryHasZones && $zoneId == '') {
                            $json['error']['shipping_zone_id'] = $this->language->get('error_zone');
                        }
                    }
                }

                if ($fieldKey == 'city' && isset($shippingField['status']) && $shippingField['status'] == 'required') {
                    $city = isset($this->request->post['shipping_city']) ? trim($this->request->post['shipping_city']) : '';
                    if ((! $customerLogged && ($shippingField['show_when'] == 'guest')) || ($customerLogged && ($shippingField['show_when'] == 'authorized')) || ($shippingField['show_when'] == 'all')) {
                        if (! empty($shippingField['setting']['custom_fields']) && ($shippingField['setting']['type'] != 'input')) {
                            if (((utf8_strlen($city) < 1) || (utf8_strlen($city) > 3))) {
                                $json['error']['shipping_city'] = ! empty($shippingField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shippingField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_city');
                            }
                        } elseif ((utf8_strlen($city) < 3) || (utf8_strlen($city) > 128)) {
                            $json['error']['shipping_city'] = ! empty($shippingField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shippingField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_city');
                        }
                    }
                }

                if ($fieldKey == 'address_1' && isset($shippingField['status']) && $shippingField['status'] == 'required') {
                    $address1 = isset($this->request->post['shipping_address_1']) ? trim($this->request->post['shipping_address_1']) : '';
                    if ((! $customerLogged && ($shippingField['show_when'] == 'guest')) || ($customerLogged && ($shippingField['show_when'] == 'authorized')) || ($shippingField['show_when'] == 'all')) {
                        if (! empty($shippingField['setting']['custom_fields']) && ($shippingField['setting']['type'] != 'input')) {
                            if ((utf8_strlen($address1) < 1) || (utf8_strlen($address1) > 3)) {
                                $json['error']['shipping_address_1'] = ! empty($shippingField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shippingField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_1');
                            }
                        } elseif ((utf8_strlen($address1) < 1) || (utf8_strlen($address1) > 128)) {
                            $json['error']['shipping_address_1'] = ! empty($shippingField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shippingField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_1');
                        }
                    }
                }

                if ($fieldKey == 'address_2' && isset($shippingField['status']) && $shippingField['status'] == 'required') {
                    $address2 = isset($this->request->post['shipping_address_2']) ? trim($this->request->post['shipping_address_2']) : '';
                    if ((! $customerLogged && ($shippingField['show_when'] == 'guest')) || ($customerLogged && ($shippingField['show_when'] == 'authorized')) || ($shippingField['show_when'] == 'all')) {
                        if (! empty($shippingField['setting']['custom_fields']) && ($shippingField['setting']['type'] != 'input')) {
                            if ((utf8_strlen($address2) < 1) || (utf8_strlen($address2) > 3)) {
                                $json['error']['shipping_address_2'] = ! empty($shippingField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shippingField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_2');
                            }
                        } elseif ((utf8_strlen($address2) < 1) || (utf8_strlen($address2) > 128)) {
                            $json['error']['shipping_address_2'] = ! empty($shippingField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shippingField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_2');
                        }
                    }
                }

                if ($fieldKey == 'company' && isset($shippingField['status']) && $shippingField['status'] == 'required') {
                    $company = isset($this->request->post['shipping_company']) ? trim($this->request->post['shipping_company']) : '';
                    if ((! $customerLogged && ($shippingField['show_when'] == 'guest')) || ($customerLogged && ($shippingField['show_when'] == 'authorized')) || ($shippingField['show_when'] == 'all')) {
                        if (! empty($shippingField['setting']['custom_fields']) && ($shippingField['setting']['type'] != 'input')) {
                            if ((utf8_strlen($company) < 1) || (utf8_strlen($company) > 3)) {
                                $json['error']['shipping_company'] = ! empty($shippingField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shippingField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_company');
                            }
                        } elseif ((utf8_strlen($company) < 1) || (utf8_strlen($company) > 128)) {
                            $json['error']['shipping_company'] = ! empty($shippingField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shippingField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_company');
                        }
                    }
                }

                if ($fieldKey == 'postcode' && isset($shippingField['status']) && $shippingField['status'] == 'required') {
                    $postcode = isset($this->request->post['shipping_postcode']) ? trim($this->request->post['shipping_postcode']) : '';
                    if ((! $customerLogged && ($shippingField['show_when'] == 'guest')) || ($customerLogged && ($shippingField['show_when'] == 'authorized')) || ($shippingField['show_when'] == 'all')) {
                        if (! empty($shippingField['setting']['custom_fields']) && ($shippingField['setting']['type'] != 'input')) {
                            if ((utf8_strlen($postcode) < 1) || (utf8_strlen($postcode) > 3)) {
                                $json['error']['shipping_postcode'] = ! empty($shippingField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shippingField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_postcode');
                            }
                        } elseif ((utf8_strlen($postcode) < 1) || (utf8_strlen($postcode) > 128)) {
                            $json['error']['shipping_postcode'] = ! empty($shippingField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shippingField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_postcode');
                        }
                    }
                }

                if (strpos($fieldKey, 'custom_field_') === 0) {
                    if (! empty($addressCustomFields[$shippingField['id']])) {
                        $customField = $addressCustomFields[$shippingField['id']];
                        if ((! $customerLogged && ($shippingField['show_when'] == 'guest')) || ($customerLogged && ($shippingField['show_when'] == 'authorized')) || ($shippingField['show_when'] == 'all')) {
                            if (($customField['location'] == 'address') && in_array($customField['type'], ['text', 'textarea', 'date', 'time', 'datetime'])) {
                                $value = $this->request->post['custom_field']['shipping_address'][$customField['custom_field_id']] ?? '';
                                $validationFailed = false;

                                if ($customField['required'] && trim($value) === '') {
                                    $validationFailed = true;
                                } elseif (trim($value) !== '') {
                                    if (! empty($customField['validation'])) {
                                        if (! preg_match($customField['validation'], $value)) {
                                            $validationFailed = true;
                                        }
                                    }
                                }

                                if ($validationFailed) {
                                    if (! empty($customField['text_error'])) {
                                        $json['error']['shipping_custom_field' . $customField['custom_field_id']] = $customField['text_error'];
                                    } else {
                                        $json['error']['shipping_custom_field' . $customField['custom_field_id']] = sprintf(
                                            $this->language->get('error_custom_field'),
                                            $customField['name']
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($paymentAddressFields as $fieldKey => $paymentField) {
            if (is_array($paymentField)) {
                if (
                    $fieldKey == 'country' && ((isset($paymentField['status']) && $paymentField['status'] == 'required'))
                ) {
                    $countryId = $this->request->post['payment_country_id'] ?? '';
                    if ((! $customerLogged && ($paymentField['show_when'] == 'guest')) || ($customerLogged && ($paymentField['show_when'] == 'authorized')) || ($paymentField['show_when'] == 'all')) {
                        if ($countryId == '') {
                            $json['error']['payment_country_id'] = $this->language->get('error_country');
                        }
                    }
                }

                if (
                    $fieldKey == 'zone_id' && ((isset($paymentField['status']) && $paymentField['status'] == 'required'))
                ) {
                    $zoneId = $this->request->post['payment_zone_id'] ?? '';
                    $countryId = $this->request->post['payment_country_id'] ?? '';

                    if ((! $customerLogged && ($paymentField['show_when'] == 'guest')) || ($customerLogged && ($paymentField['show_when'] == 'authorized')) || ($paymentField['show_when'] == 'all')) {
                        $countryHasZones = false;
                        if ($countryId) {
                            $this->load->model('localisation/zone');
                            $zones = $this->model_localisation_zone->getZonesByCountryId($countryId);
                            $countryHasZones = !empty($zones);
                        }

                        if ($countryHasZones && $zoneId == '') {
                            $json['error']['payment_zone_id'] = $this->language->get('error_zone');
                        }
                    }
                }

                if ($fieldKey == 'city' && isset($paymentField['status']) && $paymentField['status'] == 'required') {
                    $city = isset($this->request->post['payment_city']) ? trim($this->request->post['payment_city']) : '';
                    if ((! $customerLogged && ($paymentField['show_when'] == 'guest')) || ($customerLogged && ($paymentField['show_when'] == 'authorized')) || ($paymentField['show_when'] == 'all')) {
                        if (! empty($paymentField['setting']['custom_fields']) && ($paymentField['setting']['type'] != 'input')) {
                            if (((utf8_strlen($city) < 1) || (utf8_strlen($city) > 3))) {
                                $json['error']['payment_city'] = ! empty($paymentField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $paymentField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_city');
                            }
                        } elseif ((utf8_strlen($city) < 3) || (utf8_strlen($city) > 128)) {
                            $json['error']['payment_city'] = ! empty($paymentField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $paymentField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_city');
                        }
                    }
                }

                if ($fieldKey == 'address_1' && isset($paymentField['status']) && $paymentField['status'] == 'required') {
                    $address1 = isset($this->request->post['payment_address_1']) ? trim($this->request->post['payment_address_1']) : '';
                    if ((! $customerLogged && ($paymentField['show_when'] == 'guest')) || ($customerLogged && ($paymentField['show_when'] == 'authorized')) || ($paymentField['show_when'] == 'all')) {
                        if (! empty($paymentField['setting']['custom_fields']) && ($paymentField['setting']['type'] != 'input')) {
                            if ((utf8_strlen($address1) < 1) || (utf8_strlen($address1) > 3)) {
                                $json['error']['payment_address_1'] = ! empty($paymentField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $paymentField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_1');
                            }
                        } elseif ((utf8_strlen($address1) < 1) || (utf8_strlen($address1) > 128)) {
                            $json['error']['payment_address_1'] = ! empty($paymentField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $paymentField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_1');
                        }
                    }
                }

                if ($fieldKey == 'address_2' && isset($paymentField['status']) && $paymentField['status'] == 'required') {
                    $address2 = isset($this->request->post['payment_address_2']) ? trim($this->request->post['payment_address_2']) : '';
                    if ((! $customerLogged && ($paymentField['show_when'] == 'guest')) || ($customerLogged && ($paymentField['show_when'] == 'authorized')) || ($paymentField['show_when'] == 'all')) {
                        if (! empty($paymentField['setting']['custom_fields']) && ($paymentField['setting']['type'] != 'input')) {
                            if ((utf8_strlen($address2) < 1) || (utf8_strlen($address2) > 3)) {
                                $json['error']['payment_address_2'] = ! empty($paymentField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $paymentField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_2');
                            }
                        } elseif ((utf8_strlen($address2) < 1) || (utf8_strlen($address2) > 128)) {
                            $json['error']['payment_address_2'] = ! empty($paymentField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $paymentField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_2');
                        }
                    }
                }

                if ($fieldKey == 'company' && isset($paymentField['status']) && $paymentField['status'] == 'required') {
                    $company = isset($this->request->post['payment_company']) ? trim($this->request->post['payment_company']) : '';
                    if ((! $customerLogged && ($paymentField['show_when'] == 'guest')) || ($customerLogged && ($paymentField['show_when'] == 'authorized')) || ($paymentField['show_when'] == 'all')) {
                        if (! empty($paymentField['setting']['custom_fields']) && ($paymentField['setting']['type'] != 'input')) {
                            if ((utf8_strlen($company) < 1) || (utf8_strlen($company) > 3)) {
                                $json['error']['payment_company'] = ! empty($paymentField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $paymentField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_company');
                            }
                        } elseif ((utf8_strlen($company) < 1) || (utf8_strlen($company) > 128)) {
                            $json['error']['payment_company'] = ! empty($paymentField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $paymentField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_company');
                        }
                    }
                }

                if ($fieldKey == 'postcode' && isset($paymentField['status']) && $paymentField['status'] == 'required') {
                    $postcode = isset($this->request->post['payment_postcode']) ? trim($this->request->post['payment_postcode']) : '';
                    if ((! $customerLogged && ($paymentField['show_when'] == 'guest')) || ($customerLogged && ($paymentField['show_when'] == 'authorized')) || ($paymentField['show_when'] == 'all')) {
                        if (! empty($paymentField['setting']['custom_fields']) && ($paymentField['setting']['type'] != 'input')) {
                            if ((utf8_strlen($postcode) < 1) || (utf8_strlen($postcode) > 3)) {
                                $json['error']['payment_postcode'] = ! empty($paymentField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $paymentField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_postcode');
                            }
                        } elseif ((utf8_strlen($postcode) < 1) || (utf8_strlen($postcode) > 128)) {
                            $json['error']['payment_postcode'] = ! empty($paymentField['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $paymentField['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_postcode');
                        }
                    }
                }

                if (strpos($fieldKey, 'custom_field_') === 0) {
                    if (! empty($addressCustomFields[$paymentField['id']])) {
                        $customField = $addressCustomFields[$paymentField['id']];
                        if ((! $customerLogged && ($paymentField['show_when'] == 'guest')) || ($customerLogged && ($paymentField['show_when'] == 'authorized')) || ($paymentField['show_when'] == 'all')) {
                            if (($customField['location'] == 'address') && in_array($customField['type'], ['text', 'textarea', 'date', 'time', 'datetime'])) {
                                $value = $this->request->post['custom_field']['payment_address'][$customField['custom_field_id']] ?? '';
                                $validationFailed = false;

                                if ($customField['required'] && trim($value) === '') {
                                    $validationFailed = true;
                                } elseif (trim($value) !== '') {
                                    if (! empty($customField['validation'])) {
                                        if (! preg_match($customField['validation'], $value)) {
                                            $validationFailed = true;
                                        }
                                    }
                                }

                                if ($validationFailed) {
                                    if (! empty($customField['text_error'])) {
                                        $json['error']['payment_custom_field' . $customField['custom_field_id']] = $customField['text_error'];
                                    } else {
                                        $json['error']['payment_custom_field' . $customField['custom_field_id']] = sprintf(
                                            $this->language->get('error_custom_field'),
                                            $customField['name']
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($registerRequired || $registerCheckedInPost || ! empty($this->session->data['register'])) {
            $password = $this->request->post['password'] ?? '';
            $confirm = $this->request->post['confirm'] ?? '';

            if ((utf8_strlen($password) < 4) || (utf8_strlen($password) > 20)) {
                $json['error']['password'] = $this->language->get('error_password');
            }

            if ($confirm != $password) {
                $json['error']['confirm'] = $this->language->get('error_confirm');
            }
        }

        if (! isset($this->request->post['shipping_method'])) {
            $json['error']['shipping_method'] = $this->language->get('error_shipping');
        } else {
            $shipping = explode('.', $this->request->post['shipping_method']);
            if (! isset($shipping[0]) || ! isset($shipping[1])) {
                $json['error']['shipping_method'] = $this->language->get('error_shipping');
            } else {
                if (isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
                    $this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
                }
            }
        }

        if (! isset($this->request->post['payment_method'])) {
            $json['error']['payment_method'] = $this->language->get('error_payment');
        } elseif (! isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
            $json['error']['payment_method'] = $this->language->get('error_payment');
        } else {
            $this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];
        }

        if (! empty($json['error'])) {
            $json['warning'] = $this->language->get('error_validation');
        }

        if (! isset($json['error'])) {
            if (! empty($this->request->post['register'])) {
                $this->session->data['account'] = 'register';

                if (! $this->customer->isLogged()) {
                    $addressPrefix = (! empty($paymentAddressFields) && ! $checkboxChecked) ? 'payment_' : 'shipping_';

                    $customerData = [
                        'customer_group_id' => $customerGroupId,
                        'firstname' => (isset($this->request->post['firstname'])) ? $this->request->post['firstname'] : '',
                        'lastname' => (isset($this->request->post['lastname'])) ? $this->request->post['lastname'] : '',
                        'email' => (isset($this->request->post['email'])) ? $this->request->post['email'] : '',
                        'telephone' => (isset($this->request->post['telephone'])) ? $this->request->post['telephone'] : '',
                        'fax' => (isset($this->request->post['fax'])) ? $this->request->post['fax'] : '',
                        'company' => (isset($this->request->post[$addressPrefix . 'company'])) ? $this->request->post[$addressPrefix . 'company'] : '',
                        'address_1' => (isset($this->request->post[$addressPrefix . 'address_1'])) ? $this->request->post[$addressPrefix . 'address_1'] : '',
                        'address_2' => (isset($this->request->post[$addressPrefix . 'address_2'])) ? $this->request->post[$addressPrefix . 'address_2'] : '',
                        'city' => (isset($this->request->post[$addressPrefix . 'city'])) ? $this->request->post[$addressPrefix . 'city'] : '',
                        'postcode' => (isset($this->request->post[$addressPrefix . 'postcode'])) ? $this->request->post[$addressPrefix . 'postcode'] : '',
                        'country_id' => (isset($this->request->post[$addressPrefix . 'country_id'])) ? $this->request->post[$addressPrefix . 'country_id'] : '',
                        'zone_id' => (isset($this->request->post[$addressPrefix . 'zone_id'])) ? $this->request->post[$addressPrefix . 'zone_id'] : '',
                        'custom_field' => (isset($this->request->post['custom_field'])) ? $this->request->post['custom_field'] : [],
                        'password' => (isset($this->request->post['password'])) ? $this->request->post['password'] : '',
                    ];
                    $this->session->data['customer_id'] = $customerId = $this->model_account_customer->addCustomer($customerData);
                    $this->session->data['checkout_customer_id'] = true;
                }

                $this->customer->login($this->request->post['email'], $this->request->post['password']);

                unset($this->session->data['guest']);

                $this->load->model('account/activity');

                $activityData = [
                    'customer_id' => $customerId,
                    'name' => $this->request->post['firstname'] . ' ' . $this->request->post['lastname'],
                ];

                $this->model_account_activity->addActivity('register', $activityData);
                $this->registry->set('cart', new Cart\Cart($this->registry));
            } elseif (! isset($this->session->data['customer_id'])) {
                $this->session->data['account'] = 'guest';
                $this->session->data['guest']['customer_group_id'] = $customerGroupId;
                $this->session->data['guest']['firstname'] = (isset($this->request->post['firstname'])) ? $this->request->post['firstname'] : '';
                $this->session->data['guest']['lastname'] = (isset($this->request->post['lastname'])) ? $this->request->post['lastname'] : '';
                $this->session->data['guest']['email'] = (isset($this->request->post['email'])) ? $this->request->post['email'] : '';
                $this->session->data['guest']['telephone'] = (isset($this->request->post['telephone'])) ? $this->request->post['telephone'] : '';
                $this->session->data['guest']['fax'] = (isset($this->request->post['fax'])) ? $this->request->post['fax'] : '';
            } elseif ($this->customer->isLogged()) {
                $this->session->data['customer']['firstname'] = (isset($this->request->post['firstname'])) ? $this->request->post['firstname'] : '';
                $this->session->data['customer']['lastname'] = (isset($this->request->post['lastname'])) ? $this->request->post['lastname'] : '';
                $this->session->data['customer']['telephone'] = (isset($this->request->post['telephone'])) ? $this->request->post['telephone'] : '';
                $this->session->data['customer']['fax'] = (isset($this->request->post['fax'])) ? $this->request->post['fax'] : '';
            }

            if (isset($this->request->post['custom_field']['customer'])) {
                $this->session->data['guest']['customer_custom_field'] = $this->request->post['custom_field']['customer'];
            } else {
                $this->session->data['guest']['customer_custom_field'] = [];
            }

            if (isset($this->request->post['custom_field']['shipping_address'])) {
                $this->session->data['guest']['address_custom_field'] = $this->request->post['custom_field']['shipping_address'];
            } else {
                $this->session->data['guest']['address_custom_field'] = [];
            }

            if (isset($this->request->post['custom_field']['payment_address'])) {
                $this->session->data['guest']['payment_custom_field'] = $this->request->post['custom_field']['payment_address'];
            } else {
                $this->session->data['guest']['payment_custom_field'] = [];
            }

            if (isset($this->request->post['dont_call_me'])) {
                $this->session->data['dont_call_me'] = $this->request->post['dont_call_me'];
            } else {
                $this->session->data['dont_call_me'] = '';
            }

            $this->load->model('localisation/country');
            $this->load->model('localisation/zone');

            $this->session->data['shipping_address']['country_id'] = (isset($this->request->post['shipping_country_id'])) ? $this->request->post['shipping_country_id'] : '';
            $this->session->data['shipping_address']['zone_id'] = (isset($this->request->post['shipping_zone_id'])) ? $this->request->post['shipping_zone_id'] : '';
            $this->session->data['shipping_address']['firstname'] = (isset($this->request->post['firstname'])) ? $this->request->post['firstname'] : '';
            $this->session->data['shipping_address']['lastname'] = (isset($this->request->post['lastname'])) ? $this->request->post['lastname'] : '';
            $this->session->data['shipping_address']['company'] = (isset($this->request->post['shipping_company'])) ? $this->request->post['shipping_company'] : '';
            $this->session->data['shipping_address']['address_1'] = (isset($this->request->post['shipping_address_1'])) ? $this->request->post['shipping_address_1'] : '';
            $this->session->data['shipping_address']['address_2'] = (isset($this->request->post['shipping_address_2'])) ? $this->request->post['shipping_address_2'] : '';
            $this->session->data['shipping_address']['postcode'] = (isset($this->request->post['shipping_postcode'])) ? $this->request->post['shipping_postcode'] : '';
            $this->session->data['shipping_address']['city'] = (isset($this->request->post['shipping_city'])) ? $this->request->post['shipping_city'] : '';

            $this->session->data['shipping_address']['custom_field'] = $this->session->data['guest']['address_custom_field'] ?? [];

            foreach ($shippingMethodsFields as $fieldKey => $shippingField) {
                if (is_array($shippingField) && isset($shippingField['status']) && $shippingField['status'] != '0') {
                    if (! empty($shippingField['setting']['custom_fields'])) {
                        if ($fieldKey == 'city') {
                            if (! empty($shippingField['setting']['custom_fields'][$this->request->post['shipping_city']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['shipping_city']))) {
                                $this->session->data['shipping_address']['city'] = $shippingField['setting']['custom_fields'][$this->request->post['shipping_city']][$this->config->get('config_language_id')]['name'];
                            }
                        }
                        if ($fieldKey == 'postcode') {
                            if (! empty($shippingField['setting']['custom_fields'][$this->request->post['shipping_postcode']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['shipping_postcode']))) {
                                $this->session->data['shipping_address']['postcode'] = $shippingField['setting']['custom_fields'][$this->request->post['shipping_postcode']][$this->config->get('config_language_id')]['name'];
                            }
                        }
                        if ($fieldKey == 'address_1') {
                            if (! empty($shippingField['setting']['custom_fields'][$this->request->post['shipping_address_1']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['shipping_address_1']))) {
                                $this->session->data['shipping_address']['address_1'] = $shippingField['setting']['custom_fields'][$this->request->post['shipping_address_1']][$this->config->get('config_language_id')]['name'];
                            }
                        }
                        if ($fieldKey == 'address_2') {
                            if (! empty($shippingField['setting']['custom_fields'][$this->request->post['shipping_address_2']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['shipping_address_2']))) {
                                $this->session->data['shipping_address']['address_2'] = $shippingField['setting']['custom_fields'][$this->request->post['shipping_address_2']][$this->config->get('config_language_id')]['name'];
                            }
                        }
                        if ($fieldKey == 'company') {
                            if (! empty($shippingField['setting']['custom_fields'][$this->request->post['shipping_company']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['shipping_company']))) {
                                $this->session->data['shipping_address']['company'] = $shippingField['setting']['custom_fields'][$this->request->post['shipping_company']][$this->config->get('config_language_id')]['name'];
                            }
                        }
                    }
                }
            }

            if (! empty($this->request->post['shipping_country_id'])) {
                $countryInfo = $this->model_localisation_country->getCountry($this->request->post['shipping_country_id']);

                if ($countryInfo) {
                    $this->session->data['shipping_address']['country'] = $countryInfo['name'];
                    $this->session->data['shipping_address']['iso_code_2'] = $countryInfo['iso_code_2'];
                    $this->session->data['shipping_address']['iso_code_3'] = $countryInfo['iso_code_3'];
                    $this->session->data['shipping_address']['address_format'] = $countryInfo['address_format'];
                } else {
                    $this->session->data['shipping_address']['country'] = '';
                    $this->session->data['shipping_address']['iso_code_2'] = '';
                    $this->session->data['shipping_address']['iso_code_3'] = '';
                    $this->session->data['shipping_address']['address_format'] = '';
                }
            } else {
                $this->session->data['shipping_address']['country'] = '';
                $this->session->data['shipping_address']['iso_code_2'] = '';
                $this->session->data['shipping_address']['iso_code_3'] = '';
                $this->session->data['shipping_address']['address_format'] = '';
            }

            if (! empty($this->request->post['shipping_zone_id'])) {
                $zoneInfo = $this->model_localisation_zone->getZone($this->request->post['shipping_zone_id']);

                if ($zoneInfo) {
                    $this->session->data['shipping_address']['zone'] = $zoneInfo['name'];
                    $this->session->data['shipping_address']['zone_code'] = $zoneInfo['code'];
                } else {
                    $this->session->data['shipping_address']['zone'] = '';
                    $this->session->data['shipping_address']['zone_code'] = '';
                }
            } else {
                $this->session->data['shipping_address']['zone'] = '';
                $this->session->data['shipping_address']['zone_code'] = '';
            }

            if (! empty($paymentAddressFields) && ! $checkboxChecked) {
                $this->session->data['payment_address']['country_id'] = (isset($this->request->post['payment_country_id'])) ? $this->request->post['payment_country_id'] : '';
                $this->session->data['payment_address']['zone_id'] = (isset($this->request->post['payment_zone_id'])) ? $this->request->post['payment_zone_id'] : '';
                $this->session->data['payment_address']['firstname'] = (isset($this->request->post['firstname'])) ? $this->request->post['firstname'] : '';
                $this->session->data['payment_address']['lastname'] = (isset($this->request->post['lastname'])) ? $this->request->post['lastname'] : '';
                $this->session->data['payment_address']['company'] = (isset($this->request->post['payment_company'])) ? $this->request->post['payment_company'] : '';
                $this->session->data['payment_address']['address_1'] = (isset($this->request->post['payment_address_1'])) ? $this->request->post['payment_address_1'] : '';
                $this->session->data['payment_address']['address_2'] = (isset($this->request->post['payment_address_2'])) ? $this->request->post['payment_address_2'] : '';
                $this->session->data['payment_address']['postcode'] = (isset($this->request->post['payment_postcode'])) ? $this->request->post['payment_postcode'] : '';
                $this->session->data['payment_address']['city'] = (isset($this->request->post['payment_city'])) ? $this->request->post['payment_city'] : '';

                $this->session->data['payment_address']['custom_field'] = $this->session->data['guest']['payment_custom_field'] ?? [];

                foreach ($paymentAddressFields as $fieldKey => $paymentField) {
                    if (is_array($paymentField) && isset($paymentField['status']) && $paymentField['status'] != '0') {
                        if (! empty($paymentField['setting']['custom_fields'])) {
                            if ($fieldKey == 'city') {
                                if (! empty($paymentField['setting']['custom_fields'][$this->request->post['payment_city']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['payment_city']))) {
                                    $this->session->data['payment_address']['city'] = $paymentField['setting']['custom_fields'][$this->request->post['payment_city']][$this->config->get('config_language_id')]['name'];
                                }
                            }
                            if ($fieldKey == 'postcode') {
                                if (! empty($paymentField['setting']['custom_fields'][$this->request->post['payment_postcode']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['payment_postcode']))) {
                                    $this->session->data['payment_address']['postcode'] = $paymentField['setting']['custom_fields'][$this->request->post['payment_postcode']][$this->config->get('config_language_id')]['name'];
                                }
                            }
                            if ($fieldKey == 'address_1') {
                                if (! empty($paymentField['setting']['custom_fields'][$this->request->post['payment_address_1']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['payment_address_1']))) {
                                    $this->session->data['payment_address']['address_1'] = $paymentField['setting']['custom_fields'][$this->request->post['payment_address_1']][$this->config->get('config_language_id')]['name'];
                                }
                            }
                            if ($fieldKey == 'address_2') {
                                if (! empty($paymentField['setting']['custom_fields'][$this->request->post['payment_address_2']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['payment_address_2']))) {
                                    $this->session->data['payment_address']['address_2'] = $paymentField['setting']['custom_fields'][$this->request->post['payment_address_2']][$this->config->get('config_language_id')]['name'];
                                }
                            }
                            if ($fieldKey == 'company') {
                                if (! empty($paymentField['setting']['custom_fields'][$this->request->post['payment_company']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['payment_company']))) {
                                    $this->session->data['payment_address']['company'] = $paymentField['setting']['custom_fields'][$this->request->post['payment_company']][$this->config->get('config_language_id')]['name'];
                                }
                            }
                        }
                    }
                }

                if (! empty($this->request->post['payment_country_id'])) {
                    $countryInfo = $this->model_localisation_country->getCountry($this->request->post['payment_country_id']);

                    if ($countryInfo) {
                        $this->session->data['payment_address']['country'] = $countryInfo['name'];
                        $this->session->data['payment_address']['iso_code_2'] = $countryInfo['iso_code_2'];
                        $this->session->data['payment_address']['iso_code_3'] = $countryInfo['iso_code_3'];
                        $this->session->data['payment_address']['address_format'] = $countryInfo['address_format'];
                    } else {
                        $this->session->data['payment_address']['country'] = '';
                        $this->session->data['payment_address']['iso_code_2'] = '';
                        $this->session->data['payment_address']['iso_code_3'] = '';
                        $this->session->data['payment_address']['address_format'] = '';
                    }
                } else {
                    $this->session->data['payment_address']['country'] = '';
                    $this->session->data['payment_address']['iso_code_2'] = '';
                    $this->session->data['payment_address']['iso_code_3'] = '';
                    $this->session->data['payment_address']['address_format'] = '';
                }

                if (! empty($this->request->post['payment_zone_id'])) {
                    $zoneInfo = $this->model_localisation_zone->getZone($this->request->post['payment_zone_id']);

                    if ($zoneInfo) {
                        $this->session->data['payment_address']['zone'] = $zoneInfo['name'];
                        $this->session->data['payment_address']['zone_code'] = $zoneInfo['code'];
                    } else {
                        $this->session->data['payment_address']['zone'] = '';
                        $this->session->data['payment_address']['zone_code'] = '';
                    }
                } else {
                    $this->session->data['payment_address']['zone'] = '';
                    $this->session->data['payment_address']['zone_code'] = '';
                }
            } else {
                $this->session->data['payment_address'] = $this->session->data['shipping_address'];
            }

            $this->session->data['comment'] = (isset($this->request->post['comment'])) ? strip_tags($this->request->post['comment']) : '';

            $json = $this->confirm();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function validateLogin()
    {
        $this->load->language('extension/' . $this->moduleName . '/lang');
        $this->load->model('account/customer');

        $json = [];

        if ($this->customer->isLogged()) {
            $json['isLogged'] = true;
        }

        if (! isset($json['isLogged'])) {
            $email = trim($this->request->post['email_popup'] ?? '');
            $password = $this->request->post['password_popup'] ?? '';

            if (empty($email)) {
                $json['error']['email'] = $this->language->get('error_login_email');
            }

            if (empty($password)) {
                $json['error']['password'] = $this->language->get('error_login_password');
            }

            if (! isset($json['error'])) {
                $customerInfo = $this->model_account_customer->getCustomerByEmail($email);

                if (! $customerInfo) {
                    $json['error']['email'] = $this->language->get('error_login_not_found');
                } else {
                    $loginInfo = $this->model_account_customer->getLoginAttempts($email);

                    if ($loginInfo && $loginInfo['total'] >= $this->config->get('config_login_attempts') && strtotime('-1 hour') < strtotime($loginInfo['date_modified'])) {
                        $json['error']['warning'] = $this->language->get('error_attempts');
                    }

                    if (! isset($json['error']) && ! $customerInfo['status']) {
                        $json['error']['warning'] = $this->language->get('error_approved');
                    }

                    if (! isset($json['error'])) {
                        if (! $this->customer->login($email, $password)) {
                            $json['error']['password'] = $this->language->get('error_login');
                            $this->model_account_customer->addLoginAttempt($email);
                        } else {
                            $json['success'] = $this->language->get('text_login_success');

                            unset($this->session->data['guest'], $this->session->data['customer']);

                            $this->load->model('account/address');
                            $taxCustomer = $this->config->get('config_tax_customer');
                            $addressId = $this->customer->getAddressId();

                            if ($taxCustomer == 'payment') {
                                $this->session->data['payment_address'] = $this->model_account_address->getAddress($addressId);
                            }

                            if ($taxCustomer == 'shipping') {
                                $this->session->data['shipping_address'] = $this->model_account_address->getAddress($addressId);
                            }

                            if (! empty($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
                                $this->load->model('account/wishlist');

                                foreach ($this->session->data['wishlist'] as $key => $productId) {
                                    $this->model_account_wishlist->addWishlist($productId);
                                    unset($this->session->data['wishlist'][$key]);
                                }
                            }

                            $this->model_account_customer->deleteLoginAttempts($email);
                        }
                    }
                }
            }
        }

        if (isset($json['success'])) {
            $this->session->data['checkout_customer_id'] = true;
            $this->session->data['customer_id'] = $this->customer->getId();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function confirm(): array
    {
        $this->load->language('extension/' . $this->moduleName . '/lang');

        $sessionGuest = $this->session->data['guest'] ?? [];

        $shippingMethods = $this->moduleConfig->get('shipping_methods');

        $shippingCode = ! empty($this->session->data['shipping_method']['code']) ? str_replace(
            '.',
            '_',
            $this->session->data['shipping_method']['code']
        ) : null;

        $freeShippingData = ! empty($shippingMethods[$shippingCode]) ? $shippingMethods[$shippingCode] : $shippingMethods['default'];

        $orderSum = $this->cart->getSubTotal();

        $freeShippingFrom = $freeShippingData['free_shipping_price'] ?? 0;

        $freeShippingStatus = ($freeShippingData['free_shipping_status'] ?? false) && $freeShippingFrom > 0;

        $freeShippingPercentage = $freeShippingFrom > 0 && $orderSum >= $freeShippingFrom ? 100 : round(($orderSum / max(
            $freeShippingFrom,
            1
        )) * 100, 2);

        $totals = [];
        $taxes = $this->cart->getTaxes();
        $total = 0;

        $totalData = [
            'totals' => &$totals,
            'taxes' => &$taxes,
            'total' => &$total,
        ];

        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/extension');

            $results = $this->model_extension_extension->getExtensions('total');
        } else {
            $this->load->model('setting/extension');

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
                if ($freeShippingStatus && $freeShippingPercentage == 100 && ($result['code'] == 'shipping')) {
                    if (isset($this->session->data['shipping_method']['cost'])) {
                        $this->session->data['shipping_method']['cost'] = 0;
                        $this->session->data['shipping_method']['title'] = $this->language->get('text_free_shipping');
                    }
                }

                $this->load->model('extension/total/' . $result['code']);
                $this->{'model_extension_total_' . $result['code']}->getTotal($totalData);

                if ($freeShippingStatus && $freeShippingPercentage == 100 && ($result['code'] == 'shipping')) {
                    foreach ($totals as &$totalItem) {
                        if ($totalItem['code'] == 'shipping') {
                            $total -= $totalItem['value'];
                            $totalItem['value'] = 0;
                            $totalItem['text'] = $this->currency->format(0, $this->session->data['currency']);
                            $totalItem['title'] = $this->language->get('text_free_shipping');
                        }
                    }
                    unset($totalItem);
                }
            }
        }

        $sortOrder = array_column($totals, 'sort_order');
        array_multisort($sortOrder, SORT_ASC, $totals);

        $orderData = [
            'totals' => $totals,
            'invoice_prefix' => $this->config->get('config_invoice_prefix'),
            'store_id' => $this->config->get('config_store_id'),
            'store_name' => $this->config->get('config_name'),
            'store_url' => $this->config->get('config_store_id') ? $this->config->get('config_url') : ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER),
        ];

        $emailDefault = $this->moduleConfig->get('email_default') ?: 'no-email' . time() . '@localhost';

        $this->load->model('account/customer');

        if ($this->customer->isLogged()) {
            $customerInfo = $this->model_account_customer->getCustomer($this->customer->getId());
            $sessionCustomer = $this->session->data['customer'];
            $orderData['customer_id'] = $this->customer->getId();
            $orderData['customer_group_id'] = $customerInfo['customer_group_id'];
            $orderData['firstname'] = ! empty($sessionCustomer['firstname']) ? $sessionCustomer['firstname'] : '';
            $orderData['lastname'] = ! empty($sessionCustomer['lastname']) ? $sessionCustomer['lastname'] : '';
            $orderData['email'] = $customerInfo['email'];
            $orderData['telephone'] = ! empty($sessionCustomer['telephone']) ? $sessionCustomer['telephone'] : '';
            $orderData['fax'] = ! empty($sessionCustomer['fax']) ? $sessionCustomer['fax'] : '';
            $orderData['custom_field'] = json_decode($customerInfo['custom_field']);
        } elseif (isset($this->session->data['guest'])) {
            $sessionGuest = $this->session->data['guest'];
            $orderData['customer_id'] = 0;
            $orderData['customer_group_id'] = $sessionGuest['customer_group_id'] ?? $this->config->get('config_customer_group_id');
            $orderData['firstname'] = $sessionGuest['firstname'] ?? '';
            $orderData['lastname'] = $sessionGuest['lastname'] ?? '';
            $orderData['email'] = $sessionGuest['email'] ?? $emailDefault;
            $orderData['telephone'] = $sessionGuest['telephone'] ?? '';
            $orderData['fax'] = $sessionGuest['fax'] ?? '';
            $orderData['custom_field'] = $sessionGuest['custom_field'] ?? '';
        }

        if (empty($orderData['email'])) {
            $orderData['email'] = $emailDefault;
        }

        $paymentAddress = [];

        if (isset($this->session->data['payment_address'])) {
            $paymentAddress = $this->session->data['payment_address'];
        }

        $orderData['payment_firstname'] = $paymentAddress['firstname'] ?? '';
        $orderData['payment_lastname'] = $paymentAddress['lastname'] ?? '';
        $orderData['payment_company'] = $paymentAddress['company'] ?? '';
        $orderData['payment_company_id'] = $paymentAddress['company_id'] ?? '';
        $orderData['payment_tax_id'] = $paymentAddress['tax_id'] ?? '';
        $orderData['payment_address_1'] = $paymentAddress['address_1'] ?? '';
        $orderData['payment_address_2'] = $paymentAddress['address_2'] ?? '';
        $orderData['payment_city'] = $paymentAddress['city'] ?? '';
        $orderData['payment_postcode'] = $paymentAddress['postcode'] ?? '';
        $orderData['payment_zone'] = $paymentAddress['zone'] ?? '';
        $orderData['payment_zone_id'] = $paymentAddress['zone_id'] ?? '';
        $orderData['payment_country'] = $paymentAddress['country'] ?? '';
        $orderData['payment_country_id'] = $paymentAddress['country_id'] ?? '';
        $orderData['payment_address_format'] = $paymentAddress['address_format'] ?? '';
        $orderData['payment_custom_field'] = $paymentAddress['custom_field'] ?? [];

        $paymentMethod = [];

        if (isset($this->session->data['payment_method'])) {
            $paymentMethod = $this->session->data['payment_method'];
        }

        $orderData['payment_method'] = $paymentMethod['title'] ?? '';
        $orderData['payment_code'] = $paymentMethod['code'] ?? '';

        if ($this->cart->hasShipping()) {
            $shippingAddress = $this->session->data['shipping_address'] ?? $this->session->data['payment_address'];

            $orderData['shipping_firstname'] = $shippingAddress['firstname'] ?? '';
            $orderData['shipping_lastname'] = $shippingAddress['lastname'] ?? '';
            $orderData['shipping_company'] = $shippingAddress['company'] ?? '';
            $orderData['shipping_address_1'] = $shippingAddress['address_1'] ?? '';
            $orderData['shipping_address_2'] = $shippingAddress['address_2'] ?? '';
            $orderData['shipping_city'] = $shippingAddress['city'] ?? '';
            $orderData['shipping_postcode'] = $shippingAddress['postcode'] ?? '';
            $orderData['shipping_zone'] = $shippingAddress['zone'] ?? '';
            $orderData['shipping_zone_id'] = $shippingAddress['zone_id'] ?? '';
            $orderData['shipping_country'] = $shippingAddress['country'] ?? '';
            $orderData['shipping_country_id'] = $shippingAddress['country_id'] ?? '';
            $orderData['shipping_address_format'] = $shippingAddress['address_format'] ?? '';
            $orderData['shipping_custom_field'] = $shippingAddress['custom_field'] ?? [];

            $shippingMethod = [];

            if (isset($this->session->data['shipping_method'])) {
                $shippingMethod = $this->session->data['shipping_method'];
            }

            $orderData['shipping_method'] = $shippingMethod['title'] ?? '';
            $orderData['shipping_code'] = $shippingMethod['code'] ?? '';
        } else {
            $orderData['shipping_firstname'] = '';
            $orderData['shipping_lastname'] = '';
            $orderData['shipping_company'] = '';
            $orderData['shipping_address_1'] = '';
            $orderData['shipping_address_2'] = '';
            $orderData['shipping_city'] = '';
            $orderData['shipping_postcode'] = '';
            $orderData['shipping_zone'] = '';
            $orderData['shipping_zone_id'] = '';
            $orderData['shipping_country'] = '';
            $orderData['shipping_country_id'] = '';
            $orderData['shipping_address_format'] = '';
            $orderData['shipping_custom_field'] = [];
            $orderData['shipping_method'] = '';
            $orderData['shipping_code'] = '';
        }

        $orderData['comment'] = $this->session->data['comment'];

        if (! empty($sessionGuest['customer_custom_field'])) {
            $this->load->model('extension/' . $this->moduleName . '/model');

            foreach ($sessionGuest['customer_custom_field'] as $customFieldId => $customFieldValue) {
                $customerCustomFields = $this->model_extension_aw_easy_checkout_model->getCustomField($customFieldId);

                if (empty($customerCustomFields['save_to_order'])) {
                    continue;
                }

                if ($customerCustomFields['type'] == 'select' || $customerCustomFields['type'] == 'radio') {
                    $customFieldValueData = $this->model_extension_aw_easy_checkout_model->getCustomFieldValue($customFieldValue);
                    if (! empty($customFieldValueData['name'])) {
                        $orderData['comment'] .= "\n" . '[' . $this->language->get('text_customer') . '] <b>' . $customerCustomFields['name'] . '</b>: ' . $customFieldValueData['name'];
                    }
                } elseif ($customerCustomFields['type'] == 'checkbox' && is_array($customFieldValue)) {
                    $customFieldValueData = $this->model_extension_aw_easy_checkout_model->getCustomFieldValues($customFieldId);

                    $checkboxValues = [];

                    foreach ($customFieldValue as $customFieldValueId) {
                        if (isset($customFieldValueData[$customFieldValueId])) {
                            $checkboxValues[] = $customFieldValueData[$customFieldValueId]['name'];
                        }
                    }

                    if (! empty($checkboxValues)) {
                        $checkboxValuesText = implode(', ', $checkboxValues);
                        $orderData['comment'] .= "\n" . '[' . $this->language->get('text_customer') . '] <b>' . $customerCustomFields['name'] . '</b>: ' . $checkboxValuesText;
                    }
                } elseif (in_array($customerCustomFields['type'], ['text', 'textarea', 'date', 'time', 'datetime']) && ! empty($customFieldValue)) {
                    $orderData['comment'] .= "\n" . '[' . $this->language->get('text_customer') . '] <b>' . $customerCustomFields['name'] . '</b>: ' . $customFieldValue;
                }
            }
        }

        if (! empty($sessionGuest['address_custom_field'])) {
            $this->load->model('extension/' . $this->moduleName . '/model');

            foreach ($sessionGuest['address_custom_field'] as $customFieldId => $customFieldValue) {
                $addressCustomFields = $this->model_extension_aw_easy_checkout_model->getCustomField($customFieldId);

                if (empty($addressCustomFields['save_to_order'])) {
                    continue;
                }

                if ($addressCustomFields['type'] == 'select' || $addressCustomFields['type'] == 'radio') {
                    $customFieldValueData = $this->model_extension_aw_easy_checkout_model->getCustomFieldValue($customFieldValue);
                    if (! empty($customFieldValueData['name'])) {
                        $orderData['comment'] .= "\n" . '[' . $this->language->get('title_shipping_address') . '] <b>' . $addressCustomFields['name'] . '</b>: ' . $customFieldValueData['name'];
                    }
                } elseif ($addressCustomFields['type'] == 'checkbox' && is_array($customFieldValue)) {
                    $customFieldValueData = $this->model_extension_aw_easy_checkout_model->getCustomFieldValues($customFieldId);

                    $checkboxValues = [];

                    foreach ($customFieldValue as $customFieldValueId) {
                        if (isset($customFieldValueData[$customFieldValueId])) {
                            $checkboxValues[] = $customFieldValueData[$customFieldValueId]['name'];
                        }
                    }

                    if (! empty($checkboxValues)) {
                        $checkboxValuesText = implode(', ', $checkboxValues);
                        $orderData['comment'] .= "\n" . '[' . $this->language->get('title_shipping_address') . '] <b>' . $addressCustomFields['name'] . '</b>: ' . $checkboxValuesText;
                    }
                } elseif (in_array($addressCustomFields['type'], ['text', 'textarea', 'date', 'time', 'datetime']) && ! empty($customFieldValue)) {
                    $orderData['comment'] .= "\n" . '[' . $this->language->get('title_shipping_address') . '] <b>' . $addressCustomFields['name'] . '</b>: ' . $customFieldValue;
                }
            }
        }

        if (! empty($sessionGuest['payment_custom_field'])) {
            $this->load->model('extension/' . $this->moduleName . '/model');

            foreach ($sessionGuest['payment_custom_field'] as $customFieldId => $customFieldValue) {
                $paymentCustomFields = $this->model_extension_aw_easy_checkout_model->getCustomField($customFieldId);

                if (empty($paymentCustomFields['save_to_order'])) {
                    continue;
                }

                if ($paymentCustomFields['type'] == 'select' || $paymentCustomFields['type'] == 'radio') {
                    $customFieldValueData = $this->model_extension_aw_easy_checkout_model->getCustomFieldValue($customFieldValue);
                    if (! empty($customFieldValueData['name'])) {
                        $orderData['comment'] .= "\n" . '[' . $this->language->get('title_payment_address') . '] <b>' . $paymentCustomFields['name'] . '</b>: ' . $customFieldValueData['name'];
                    }
                } elseif ($paymentCustomFields['type'] == 'checkbox' && is_array($customFieldValue)) {
                    $customFieldValueData = $this->model_extension_aw_easy_checkout_model->getCustomFieldValues($customFieldId);

                    $checkboxValues = [];

                    foreach ($customFieldValue as $customFieldValueId) {
                        if (isset($customFieldValueData[$customFieldValueId])) {
                            $checkboxValues[] = $customFieldValueData[$customFieldValueId]['name'];
                        }
                    }

                    if (! empty($checkboxValues)) {
                        $checkboxValuesText = implode(', ', $checkboxValues);
                        $orderData['comment'] .= "\n" . '[' . $this->language->get('title_payment_address') . '] <b>' . $paymentCustomFields['name'] . '</b>: ' . $checkboxValuesText;
                    }
                } elseif (in_array($paymentCustomFields['type'], ['text', 'textarea', 'date', 'time', 'datetime']) && ! empty($customFieldValue)) {
                    $orderData['comment'] .= "\n" . '[' . $this->language->get('title_payment_address') . '] <b>' . $paymentCustomFields['name'] . '</b>: ' . $customFieldValue;
                }
            }
        }

        if (isset($this->session->data['dont_call_me']) && $this->session->data['dont_call_me'] == 1) {
            $orderData['comment'] .= "\n" . '<b>' . $this->language->get('text_dont_call_me') . '</b>';
        }

        $orderData['products'] = [];

        foreach ($this->cart->getProducts() as $product) {
            $optionData = [];

            foreach ($product['option'] as $option) {
                $optionData[] = [
                    'product_option_id' => $option['product_option_id'],
                    'product_option_value_id' => $option['product_option_value_id'],
                    'option_id' => $option['option_id'],
                    'option_value_id' => $option['option_value_id'],
                    'name' => $option['name'],
                    'value' => $option['value'],
                    'type' => $option['type'],
                ];
            }

            $orderData['products'][] = [
                'product_id' => $product['product_id'],
                'name' => $product['name'],
                'model' => $product['model'],
                'option' => $optionData,
                'download' => $product['download'],
                'quantity' => $product['quantity'],
                'subtract' => $product['subtract'],
                'price' => $product['price'],
                'total' => $product['total'],
                'tax' => $this->tax->getTax($product['price'], $product['tax_class_id']),
                'reward' => $product['reward'],
            ];
        }

        $orderData['vouchers'] = [];

        if (! empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $voucher) {
                $orderData['vouchers'][] = [
                    'description' => $voucher['description'],
                    'code' => substr(md5(mt_rand()), 0, 10),
                    'to_name' => $voucher['to_name'],
                    'to_email' => $voucher['to_email'],
                    'from_name' => $voucher['from_name'],
                    'from_email' => $voucher['from_email'],
                    'voucher_theme_id' => $voucher['voucher_theme_id'],
                    'message' => $voucher['message'],
                    'amount' => $voucher['amount'],
                ];
            }
        }

        $orderData['total'] = $total;

        if (isset($this->request->cookie['tracking'])) {
            $orderData['tracking'] = $this->request->cookie['tracking'];

            $subTotal = $this->cart->getSubTotal();

            $affiliateInfo = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

            if ($affiliateInfo) {
                $orderData['affiliate_id'] = $affiliateInfo['customer_id'];
                $orderData['commission'] = ($subTotal / 100) * $affiliateInfo['commission'];
            } else {
                $orderData['affiliate_id'] = 0;
                $orderData['commission'] = 0;
            }

            $this->load->model('checkout/marketing');

            $marketingInfo = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

            if ($marketingInfo) {
                $orderData['marketing_id'] = $marketingInfo['marketing_id'];
            } else {
                $orderData['marketing_id'] = 0;
            }
        } else {
            $orderData['affiliate_id'] = 0;
            $orderData['commission'] = 0;
            $orderData['marketing_id'] = 0;
            $orderData['tracking'] = '';
        }

        $orderData['language_id'] = $this->config->get('config_language_id');
        $orderData['currency_id'] = $this->currency->getId($this->session->data['currency']);
        $orderData['currency_code'] = $this->session->data['currency'];
        $orderData['currency_value'] = $this->currency->getValue($this->session->data['currency']);
        $orderData['ip'] = $this->request->server['REMOTE_ADDR'];

        if (! empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $orderData['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } elseif (! empty($this->request->server['HTTP_CLIENT_IP'])) {
            $orderData['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
        } else {
            $orderData['forwarded_ip'] = '';
        }

        if (isset($this->request->server['HTTP_USER_AGENT'])) {
            $orderData['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
        } else {
            $orderData['user_agent'] = '';
        }

        if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
            $orderData['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $orderData['accept_language'] = '';
        }

        $this->load->model('checkout/order');

        $this->session->data['order_id'] = $this->model_checkout_order->addOrder($orderData);

        $json = [];

        $json['success']['payment'] = $this->load->controller('extension/payment/' . $this->session->data['payment_method']['code']);

        if ($json['success']) {
            $this->load->model('extension/' . $this->moduleName . '/model');

            if (isset($this->session->data['abandoned_id']) && $this->session->data['abandoned_id'] != '') {
                $abandonedId = $this->session->data['abandoned_id'];
                $this->model_extension_aw_easy_checkout_model->removeAbandonedOrder($abandonedId);
            }
        }

        return $json;
    }
}
