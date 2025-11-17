<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwEasyCheckout extends Controller
{
    private string $moduleName = 'aw_easy_checkout';

    private string $moduleChildName = 'aw_easy_abandoned';

    private \Alexwaha\Config $moduleConfig;

    private \Alexwaha\Language $language;

    private array $error = [];

    private string $routeExtension;

    private array $params;

    private array $tokenData;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language = $this->awCore->getLanguage();
        $this->tokenData = $this->awCore->getToken();
        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
        $this->params = $this->language->load('extension/module/' . $this->moduleName);
        $this->params['token'] = $this->tokenData['token'];
        $this->params['token_param'] = $this->tokenData['param'];

        $this->routeExtension = $this->awCore->isLegacy() ? 'extension/extension' : 'marketplace/extension';
    }

    public function index()
    {
        $this->document->setTitle($this->language->get('heading_main_title'));

        $this->load->model('setting/setting');
        $this->load->model('extension/module/' . $this->moduleName);

        $this->document->addScript('view/javascript/' . $this->moduleName . '/Sortable.min.js');
        $this->document->addStyle('view/stylesheet/' . $this->moduleName . '/style.min.css');

        $this->document->addStyle('view/javascript/summernote/summernote.css');
        $this->document->addScript('view/javascript/summernote/summernote.js');

        if (! $this->awCore->isLegacy()) {
            $this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
        }

        $this->document->addScript('view/javascript/summernote/opencart.js');

        $this->params['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true),
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true),
            ],
        ];

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $configLanguage = $this->config->get('config_language');

        $defaultLanguage = $this->model_localisation_language->getLanguageByCode($configLanguage);

        $this->params['defaultLanguageId'] = $defaultLanguage['language_id'] ?? $this->config->get('config_language_id');

        $this->load->model('localisation/country');

        $this->params['countryId'] = $countryId = $this->config->get('config_country_id');
        $this->params['countries'] = $this->model_localisation_country->getCountries();

        $this->params['action'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/store',
            $this->tokenData['param'],
            true
        );

        $this->params['cancel'] = $this->url->link(
            $this->routeExtension,
            $this->tokenData['param'] . '&type=module',
            true
        );

        $this->load->model('tool/image');

        $this->params['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        $this->load->model('customer/customer_group');
        $this->params['customerGroups'] = $this->model_customer_customer_group->getCustomerGroups(['sort' => 'cg.sort_order']);

        $this->params['status'] = $this->moduleConfig->get('status', false);
        $this->params['replace_cart'] = $this->moduleConfig->get('replace_cart', false);
        $this->params['replace_checkout'] = $this->moduleConfig->get('replace_checkout', false);
        $this->params['payment_address_same_as_shipping'] = $this->moduleConfig->get('payment_address_same_as_shipping', true);
        $this->params['show_customer_addresses'] = $this->moduleConfig->get('show_customer_addresses', false);
        $this->params['col_left_width'] = $this->moduleConfig->get('col_left_width', '65');
        $this->params['col_right_width'] = $this->moduleConfig->get('col_right_width', '35');
        $this->params['use_theme_css'] = $this->moduleConfig->get('use_theme_css', false);
        $this->params['mask'] = $this->moduleConfig->get('mask', '');
        $this->params['mask_type'] = $this->moduleConfig->get('mask_type', 'dynamic');
        $this->params['email_default'] = $this->moduleConfig->get('email_default', '');
        $this->params['min_price_order'] = $this->moduleConfig->get('min_price_order', []);
        $this->params['agree_default'] = $this->moduleConfig->get('agree_default', false);
        $this->params['javascript'] = $this->moduleConfig->get('javascript', '');
        $this->params['comment'] = $this->moduleConfig->get('comment', []);
        $this->params['register_status'] = $this->moduleConfig->get('register_status', '');
        $this->params['custom_text'] = $this->moduleConfig->get('custom_text', []);
        $this->params['seo_url'] = $this->awCore->getSeoUrls('extension/' . $this->moduleName . '/main') ?? [];

        $this->params['isLegacy'] = $this->awCore->isLegacy();

        $this->load->model('setting/store');

        $this->params['store_list'][] = [
            'store_id' => 0,
            'name' => $this->language->get('text_default')
        ];

        $stores = $this->model_setting_store->getStores();

        foreach ($stores as $store) {
            $this->params['store_list'][] = [
                'store_id' => $store['store_id'],
                'name' => $store['name']
            ];
        }

        $blockStatus = $this->moduleConfig->get('block_status', []);
        if (empty($blockStatus)) {
            $blockStatus = [
                'comment' => $this->moduleConfig->get('comment_status', ''),
                'custom_text' => $this->moduleConfig->get('custom_text_status', false),
                'coupon' => $this->moduleConfig->get('coupon_status', true),
                'voucher' => $this->moduleConfig->get('voucher_status', true),
            ];
        }

        $blockPositions = $this->moduleConfig->get('block_position', []);
        $blockSortOrder = $this->moduleConfig->get('block_sort_order', []);

        $blockDefaults = [
            'cart' => 'top_left',
            'custom_text' => 'bottom_full',
            'customer' => 'top_left',
            'shipping_method' => 'center_left',
            'payment_method' => 'center_right',
            'shipping_address' => 'bottom_left',
            'comment' => 'bottom_left',
            'coupon' => 'fix_right',
            'voucher' => 'fix_right',
            'totals' => 'fix_right',
        ];

        if (empty($blockPositions) || ($blockPositions['cart'] ?? '') == 'full_column') {
            $blockPositions = $blockDefaults;
        }

        $blockPositions = array_merge($blockDefaults, $blockPositions);

        $allowedPositions = [
            'top_left',
            'center_left',
            'center_right',
            'bottom_left',
            'bottom_full',
            'top_full',
            'fix_right',
        ];

        $result = [];

        foreach ($blockPositions as $blockName => $position) {
            if (! in_array($position, $allowedPositions)) {
                continue;
            }

            $result[$position][] = [
                'name' => $blockName,
                'status' => $blockStatus[$blockName] ?? true,
                'sort_order' => $blockSortOrder[$blockName] ?? 999,
                'position' => $position,
            ];
        }

        foreach ($result as &$blocks) {
            usort($blocks, function ($a, $b) {
                return $a['sort_order'] <=> $b['sort_order'];
            });
        }
        unset($blocks);

        $this->params['blockPosition'] = $result;

        $countryInfo = $this->model_localisation_country->getCountry($countryId);

        if ($countryInfo) {
            $this->load->model('localisation/zone');
            $this->params['zones'] = $this->model_localisation_zone->getZonesByCountryId($countryId);
        }

        $this->params['customerFields'] = $customerFields = [
            'firstname',
            'lastname',
            'telephone',
            'email',
        ];

        $settingCustomer = $this->moduleConfig->get('customer', []);

        if (empty($settingCustomer['default'])) {
            $settingCustomer['default']['title'] = $this->language->get('text_default');

            foreach ($customerFields as $index => $customerField) {
                $settingCustomer['default'][$customerField] = [
                    'status' => false,
                    'show_when' => 'all',
                    'setting' => [],
                    'sort_order' => $index + 1,
                ];
            }
        }

        if (! empty($settingCustomer)) {
            foreach ($settingCustomer as $languageId => $settings) {
                foreach ($settings as $fieldName => $field) {
                    if (strpos($fieldName, 'custom_field_') === 0) {
                        $customFieldName = $this->model_extension_module_aw_easy_checkout->getCustomFieldName($field['id']);
                        if ($customFieldName) {
                            $settingCustomer[$languageId][$fieldName] = [
                                'show_when' => 'all',
                                'name' => $customFieldName,
                                'id' => $field['id'],
                                'sort_order' => $field['sort_order'] ?? 0,
                            ];
                        }
                    }
                }
            }
        }

        foreach ($settingCustomer as &$customerData) {
            if (is_array($customerData)) {
                $title = $customerData['title'] ?? null;
                unset($customerData['title']);

                uasort($customerData, function ($a, $b) {
                    return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
                });

                if ($title) {
                    $customerData = ['title' => $title] + $customerData;
                }
            }
        }

        $this->params['customers'] = $settingCustomer;

        $this->params['fieldTypes'] = [
            'city',
            'address_1',
            'address_2',
            'postcode',
            'company',
        ];

        $this->params['shippingFields'] = $shippingFields = [
            'country',
            'zone_id',
            'city',
            'address_1',
            'address_2',
            'postcode',
            'company',
        ];

        $settingShippingAddress = $this->moduleConfig->get('shipping_address', []);

        if (empty($settingShippingAddress['default'])) {
            $settingShippingAddress['default']['title'] = $this->language->get('text_default');

            foreach ($shippingFields as $index => $shippingField) {
                $settingShippingAddress['default'][$shippingField] = [
                    'status' => false,
                    'show_when' => 'all',
                    'setting' => [],
                    'sort_order' => $index + 1,
                ];
            }
        }

        if (! empty($settingShippingAddress)) {
            foreach ($settingShippingAddress as $languageId => $addresses) {
                foreach ($addresses as $key => $addressField) {
                    if (strpos($key, 'custom_field_') === 0) {
                        $customFieldName = $this->model_extension_module_aw_easy_checkout->getCustomFieldName($addressField['id']);
                        if ($customFieldName) {
                            $settingShippingAddress[$languageId][$key] = [
                                'show_when' => 'all',
                                'name' => $customFieldName,
                                'id' => $addressField['id'],
                                'sort_order' => $addressField['sort_order'] ?? 0,
                            ];
                        }
                    }
                }
            }
        }

        foreach ($settingShippingAddress as &$addressData) {
            if (is_array($addressData)) {
                $title = $addressData['title'] ?? null;
                unset($addressData['title']);

                uasort($addressData, function ($a, $b) {
                    return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
                });

                if ($title) {
                    $addressData = ['title' => $title] + $addressData;
                }
            }
        }

        $this->params['shippingAddress'] = $settingShippingAddress;

        $settingShippingMethods = $this->moduleConfig->get('shipping_methods', []);

        if (empty($settingShippingMethods['default'])) {
            $settingShippingMethods['default'] = [
                'title' => $this->language->get('text_default'),
                'free_shipping_status' => false,
                'free_shipping_price' => 0,
                'shipping_method_show_all_countries' => 0,
            ];
        }

        if (! empty($settingShippingMethods['default'])) {
            foreach ($settingShippingMethods as $key => $settingShippingMethod) {
                $shippingMethodShowOnlyCountries = [];

                if (! empty($settingShippingMethod['shipping_method_show_for_countries'])) {
                    foreach ($settingShippingMethod['shipping_method_show_for_countries'] as $countryId) {
                        $countryInfo = $this->model_localisation_country->getCountry($countryId);

                        if ($countryInfo) {
                            $shippingMethodShowOnlyCountries[] = [
                                'name' => $countryInfo['name'],
                                'country_id' => $countryInfo['country_id'],
                            ];
                        }
                    }
                }

                $shippingMethodDisabledCountries = [];

                if (! empty($settingShippingMethod['shipping_method_hide_for_countries'])) {
                    foreach ($settingShippingMethod['shipping_method_hide_for_countries'] as $countryId) {
                        $countryInfo = $this->model_localisation_country->getCountry($countryId);

                        if ($countryInfo) {
                            $shippingMethodDisabledCountries[] = [
                                'name' => $countryInfo['name'],
                                'country_id' => $countryInfo['country_id'],
                            ];
                        }
                    }
                }

                if (isset($settingShippingMethod['image']) && is_file(DIR_IMAGE . $settingShippingMethod['image'])) {
                    $thumb = $this->model_tool_image->resize($settingShippingMethod['image'], 100, 100);
                    $image = $settingShippingMethod['image'];
                } else {
                    $thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
                    $image = '';
                }

                $settingShippingMethods[$key] = [
                    'thumb' => $thumb,
                    'image' => $image,
                    'image_width' => $settingShippingMethod['image_width'] ?? 36,
                    'image_height' => $settingShippingMethod['image_height'] ?? 36,
                    'title' => $settingShippingMethod['title'],
                    'free_shipping_status' => $settingShippingMethod['free_shipping_status'] ?? 0,
                    'free_shipping_price' => $settingShippingMethod['free_shipping_price'],
                    'status_shipping_method_new_title' => $settingShippingMethod['status_shipping_method_new_title'] ?? 0,
                    'shipping_method_new_title' => $settingShippingMethod['shipping_method_new_title'] ?? [],
                    'shipping_method_show_all_countries' => $settingShippingMethod['shipping_method_show_all_countries'] ?? false,
                    'shipping_method_show_for_countries' => $shippingMethodShowOnlyCountries,
                    'shipping_method_hide_for_countries' => $shippingMethodDisabledCountries,
                ];
            }
        }

        $this->params['shippingMethods'] = $settingShippingMethods;

        $paymentMethodList = $this->model_extension_module_aw_easy_checkout->getPaymentMethods();

        $this->params['paymentMethodList'] = [];

        foreach ($paymentMethodList as $paymentMethod) {
            $statusKey = $this->awCore->isLegacy()
                ? $paymentMethod['code'] . '_status'
                : 'payment_' . $paymentMethod['code'] . '_status';

            if ($this->config->get($statusKey)) {
                $this->params['paymentMethodList'][] = [
                    'code' => $paymentMethod['code'],
                    'name' => $paymentMethod['name'],
                ];
            }
        }

        $settingPaymentMethod = $this->moduleConfig->get('payment_methods', []);

        $paymentMethodData = [];

        if (! empty($settingPaymentMethod)) {
            foreach ($settingPaymentMethod as $code => $setting) {
                if (isset($setting)) {
                    $setting['code'] = $code;

                    if (! isset($setting['status_payment_method_title'])) {
                        $setting['status_payment_method_title'] = 0;
                    }

                    if (! isset($setting['status_payment_method_description'])) {
                        $setting['status_payment_method_description'] = 0;
                    }

                    if (isset($setting['image']) && is_file(DIR_IMAGE . $setting['image'])) {
                        $setting['thumb'] = $this->model_tool_image->resize(
                            $setting['image'],
                            100,
                            100
                        );
                    } else {
                        $setting['thumb'] = $this->model_tool_image->resize(
                            'no_image.png',
                            100,
                            100
                        );
                    }

                    if (! isset($setting['image_width'])) {
                        $setting['image_width'] = 36;
                    }

                    if (! isset($setting['image_height'])) {
                        $setting['image_height'] = 36;
                    }

                    $paymentMethodData[] = $setting;
                }
            }
        }

        $this->params['paymentMethods'] = $paymentMethodData;

        $this->params['show_weight'] = $this->moduleConfig->get('show_weight', false);
        $this->params['show_dont_call_me'] = $this->moduleConfig->get('show_dont_call_me', false);

        $customFields = $this->model_extension_module_aw_easy_checkout->getCustomFields();

        $customFieldsConfig = [];

        if (! empty($customFields)) {
            foreach ($customFields as $customField) {
                $typeMap = [
                    'select',
                    'radio',
                    'checkbox',
                    'input',
                    'text',
                    'textarea',
                    'file',
                    'date',
                    'datetime',
                    'time',
                ];

                $customFieldType = in_array($customField['type'], $typeMap) ? $this->language->get('text_' . $customField['type']) : '';

                $customFieldLocation = $this->language->get('text_customer');

                if ($customField['location'] == 'address') {
                    $customFieldLocation = $this->language->get('text_address');
                }

                if ($customField['status'] == 1) {
                    $customFieldStatus = $this->language->get('text_enabled');
                } else {
                    $customFieldStatus = $this->language->get('text_disabled');
                }

                $customFieldsConfig[] = [
                    'custom_field_id' => $customField['custom_field_id'],
                    'name' => $customField['name'],
                    'location_code' => $customField['location'],
                    'location' => $customFieldLocation,
                    'type' => $customFieldType,
                    'status' => $customFieldStatus,
                    'edit' => $this->url->link(
                        'extension/module/' . $this->moduleName . '/editCustomField',
                        $this->tokenData['param'] . '&custom_field_id=' . $customField['custom_field_id'],
                        true
                    ),
                    'remove' => $this->url->link(
                        'extension/module/' . $this->moduleName . '/removeCustomField',
                        $this->tokenData['param'] . '&custom_field_id=' . $customField['custom_field_id'],
                        true
                    ),
                ];
            }
        }

        $this->params['customFields'] = $customFieldsConfig;

        $this->params['customerCustomFields'] = [];
        $this->params['shippingAddressCustomFields'] = [];
        $this->params['paymentAddressCustomFields'] = [];

        if (! empty($customFieldsConfig)) {
            foreach ($customFieldsConfig as $customField) {
                if ($customField['location_code'] === 'customer') {
                    $this->params['customerCustomFields'][] = $customField;
                }
                if ($customField['location_code'] === 'address') {
                    $this->params['addressCustomFields'][] = $customField;
                }
            }
        }

        $this->params['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

        $this->load->model('user/api');

        $apiInfo = $this->model_user_api->getApi($this->config->get('config_api_id'));

        $this->params['api_token'] = '';

        if ($apiInfo && $this->user->hasPermission('modify', 'sale/order')) {
            if ($this->awCore->isLegacy()) {
                if (!isset($this->session->data['api_token'])) {
                    $this->session->data['api_token'] = substr(bin2hex(random_bytes(26)), 0, 26);

                    $this->model_user_api->addApiSession(
                        $apiInfo['api_id'],
                        ['token' => $this->session->data['api_token']]
                    );
                }

                $this->params['api_token'] = $this->session->data['api_token'];
            } else {
                $session = new Session($this->config->get('session_engine'), $this->registry);

                $session->start();

                $this->model_user_api->deleteApiSessionBySessionId($session->getId());

                $this->model_user_api->addApiSession(
                    $apiInfo['api_id'],
                    $session->getId(),
                    $this->request->server['REMOTE_ADDR']
                );

                $this->session->data['api_id'] = $apiInfo['api_id'];

                $this->params['api_token'] = $session->getId();
            }
        }

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/main', $this->params));
    }

    public function store()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->awCore->setConfig($this->moduleName, $this->request->post);

            $this->awCore->setSeoUrls($this->request->post['seo_url'], 'extension/' . $this->moduleName . '/main');

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                'extension/module/' . $this->moduleName,
                $this->tokenData['param'] . '&type=module',
                true
            ));
        }

        $this->cache->delete('seo_pro');

        $this->index();
    }

    public function autocompleteCountry()
    {
        $json = [];

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('extension/module/' . $this->moduleName);

            $filterData = [
                'filter_name' => $this->request->get['filter_name'],
                'sort' => 'name',
                'order' => 'ASC',
                'start' => 0,
                'limit' => 10,
            ];

            $results = $this->model_extension_module_aw_easy_checkout->getCountries($filterData);

            foreach ($results as $result) {
                $json[] = [
                    'country_id' => $result['country_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                ];
            }
        }

        $sortOrder = [];

        foreach ($json as $key => $value) {
            $sortOrder[$key] = $value['name'];
        }

        array_multisort($sortOrder, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getCountry()
    {
        $json = [];

        $countryId = $this->request->get['country_id'] ?? 0;

        $this->load->model('localisation/country');
        $countryInfo = $this->model_localisation_country->getCountry($countryId);

        if ($countryInfo) {
            $this->load->model('localisation/zone');

            $json = [
                'country_id' => $countryInfo['country_id'],
                'name' => $countryInfo['name'],
                'zone' => $this->model_localisation_zone->getZonesByCountryId($countryInfo['country_id']),
                'status' => $countryInfo['status'],
            ];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getFormCustomField()
    {
        if (isset($this->request->get['custom_field_id']) && $this->request->get['custom_field_id']) {
            $customFieldId = $this->request->get['custom_field_id'];
        } else {
            $customFieldId = 0;
        }

        $this->params['text_form'] = $customFieldId ? $this->language->get('text_edit_custom_field') : $this->language->get('text_add_custom_field');

        $this->params['custom_field_id'] = $customFieldId;

        if ($customFieldId) {
            $this->params['method'] = 'editCustomField';
        } else {
            $this->params['method'] = 'addCustomField';
        }

        $customField = [];

        if ($customFieldId && $this->request->server['REQUEST_METHOD'] != 'POST') {
            $customField = $this->model_extension_module_aw_easy_checkout->getCustomField($customFieldId);
        }

        $this->params['custom_field_description'] =
            $this->model_extension_module_aw_easy_checkout
                ->getCustomFieldDescriptions($customFieldId) ?? [];

        $this->params['location'] = $customField['location'] ?? '';
        $this->params['type'] = $customField['type'] ?? '';
        $this->params['value'] = $customField['value'] ?? '';
        $this->params['validation'] = $customField['validation'] ?? '';
        $this->params['status'] = $customField['status'] ?? '';
        $this->params['required'] = $customField['required'] ?? 0;
        $this->params['save_to_order'] = $customField['save_to_order'] ?? '';

        $customFieldValueDescriptions = $this->model_extension_module_aw_easy_checkout
            ->getCustomFieldValueDescriptions($customFieldId) ?? [];

        $customFieldValues = [];

        foreach ($customFieldValueDescriptions as $customFieldValueDescription) {
            $customFieldValues[] = [
                'custom_field_value_id' => $customFieldValueDescription['custom_field_value_id'],
                'custom_field_value_description' => $customFieldValueDescription['custom_field_value_description'],
                'sort_order' => $customFieldValueDescription['sort_order'],
            ];
        }

        $this->params['custom_field_values'] = $customFieldValues;

        $customFieldCustomerGroups = $this->model_extension_module_aw_easy_checkout->getCustomFieldCustomerGroups($customFieldId) ?? [];

        $this->params['custom_field_customer_group'] = [];

        foreach ($customFieldCustomerGroups as $customFieldCustomerGroup) {
            $this->params['custom_field_customer_group'][] = $customFieldCustomerGroup['customer_group_id'];
        }

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('customer/customer_group');
        $this->params['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/components/custom_field', $this->params));
    }

    public function getCustomFieldData()
    {
        $json = [
            'success' => false,
            'custom_fields' => [],
        ];

        if (($this->request->server['REQUEST_METHOD'] ?? '') === 'POST') {
            $json['success'] = true;
            $json['custom_fields'] = $this->getCustomFieldsData();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function getCustomFieldsData(): array
    {
        $this->load->model('extension/module/' . $this->moduleName);
        $customFieldsData = $this->model_extension_module_aw_easy_checkout->getCustomFields();

        $customFields = [];

        if (! empty($customFieldsData)) {
            foreach ($customFieldsData as $customField) {
                $allowedTypes = [
                    'select',
                    'radio',
                    'checkbox',
                    'input',
                    'text',
                    'textarea',
                    'file',
                    'date',
                    'datetime',
                    'time',
                ];

                $customFieldType = in_array(
                    $customField['type'],
                    $allowedTypes,
                    true
                ) ? $this->language->get('text_' . $customField['type']) : '';

                $customFieldLocation = $this->language->get('text_customer');

                if ($customField['location'] == 'address') {
                    $customFieldLocation = $this->language->get('text_address');
                }

                if ($customField['status'] == 1) {
                    $customFieldStatus = $this->language->get('text_enabled');
                } else {
                    $customFieldStatus = $this->language->get('text_disabled');
                }

                $customFields[] = [
                    'custom_field_id' => $customField['custom_field_id'],
                    'name' => $customField['name'],
                    'location' => $customFieldLocation,
                    'location_code' => $customField['location'],
                    'type' => $customFieldType,
                    'status' => $customFieldStatus,
                    'edit' => $this->url->link(
                        'extension/module/' . $this->moduleName . '/editCustomField',
                        $this->tokenData['param'] . '&custom_field_id=' . $customField['custom_field_id'],
                        true
                    ),
                    'remove' => $this->url->link(
                        'extension/module/' . $this->moduleName . '/removeCustomField',
                        $this->tokenData['param'] . '&custom_field_id=' . $customField['custom_field_id'],
                        true
                    ),
                ];
            }
        }

        return $customFields;
    }

    public function addCustomField()
    {
        $this->load->model('extension/module/' . $this->moduleName);

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $json = [];

            if ($this->validateFormCustomField()) {

                $post = $this->request->post;

                if (! isset($post['custom_field_customer_group']) || ! is_array($post['custom_field_customer_group'])) {
                    $post['custom_field_customer_group'] = [];
                } else {
                    foreach ($post['custom_field_customer_group'] as $index => $group) {
                        if (! isset($group['customer_group_id'])) {
                            $post['custom_field_customer_group'][$index]['customer_group_id'] = 0;
                        }
                    }
                }

                if (! isset($post['save_to_order'])) {
                    $post['save_to_order'] = 0;
                }

                if (! isset($post['status'])) {
                    $post['status'] = 0;
                }

                if (! isset($post['required'])) {
                    $post['required'] = 0;
                }

                $this->model_extension_module_aw_easy_checkout->addCustomField($post);

                $json['success'] = $this->language->get('text_success_add_custom_field');
                $json['custom_fields'] = $this->getCustomFieldsData();
            } else {
                $json['error'] = $this->error;
            }

            return $this->response->setOutput(json_encode($json));
        }

        $this->getFormCustomField();
    }

    public function editCustomField()
    {
        $this->load->language('extension/module/' . $this->moduleName);
        $this->load->model('extension/module/' . $this->moduleName);

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $json = [];

            if ($this->validateFormCustomField()) {
                $customFieldId = (int) ($this->request->get['custom_field_id'] ?? 0);

                $customField = $this->model_extension_module_aw_easy_checkout->getCustomField($customFieldId);

                $currentLocation = $customField['location'] ?? '';
                $newLocation = $this->request->post['location'] ?? '';

                $isLocationChanged = $currentLocation !== $newLocation;
                $isFieldUsed = false;

                if ($isLocationChanged) {
                    $configKey = ($currentLocation === 'address') ? 'address' : 'customer';

                    $configData = (array) $this->moduleConfig->get($configKey, []);
                    $fieldKey = 'custom_field_' . $customFieldId;

                    foreach ($configData as $fields) {
                        if (isset($fields[$fieldKey])) {
                            $isFieldUsed = true;
                            break;
                        }
                    }
                }

                if ($isFieldUsed) {
                    $locationName = $this->language->get('text_customer');

                    if ($currentLocation === 'address') {
                        $locationName = $this->language->get('text_address');
                    }

                    $json['error']['warning'] = $this->language->get('error_location') . ' - ' . $locationName;
                } else {
                    $this->model_extension_module_aw_easy_checkout->editCustomField($customFieldId, $this->request->post);

                    $json['success'] = $this->language->get('text_success_edit_custom_field');
                    $json['custom_fields'] = $this->getCustomFieldsData();
                }
            } else {
                $json['error'] = $this->error;
            }

            $this->response->addHeader('Content-Type: application/json');

            return $this->response->setOutput(json_encode($json));
        }

        $this->getFormCustomField();
    }

    public function deleteCustomField()
    {
        $this->load->model('extension/module/' . $this->moduleName);

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $json = [];

            if ($this->validate()) {
                $this->model_extension_module_aw_easy_checkout->deleteCustomField($this->request->post['custom_field_id']);

                $json['success'] = $this->language->get('text_success_remove_custom_field');
                $json['custom_fields'] = $this->getCustomFieldsData();
            } else {
                $json['error'] = $this->error;
            }

            return $this->response->setOutput(json_encode($json));
        }

        $this->getFormCustomField();
    }

    protected function validateFormCustomField(): bool
    {
        if (! $this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $descriptions = $this->request->post['custom_field_description'] ?? [];

        if (isset($descriptions['name'])) {
            foreach ($descriptions['name'] as $languageId => $value) {
                if (utf8_strlen($value) < 1 || utf8_strlen($value) > 128) {
                    $this->error['name'][$languageId] = $this->language->get('error_name');
                }
            }
        }

        $type = $this->request->post['type'] ?? '';

        $isAllowedType = in_array($type, [
            'select',
            'radio',
            'checkbox',
        ], true);

        if ($isAllowedType) {
            $customFieldValues = $this->request->post['custom_field_value'] ?? null;

            if (! is_array($customFieldValues) || $customFieldValues === []) {
                $this->error['warning'] = $this->language->get('error_type');
            } else {
                foreach ($customFieldValues as $customFieldValueId => $customFieldValue) {
                    foreach (
                        $customFieldValue['custom_field_value_description'] as $languageId => $customFieldValueDescription
                    ) {
                        if (utf8_strlen($customFieldValueDescription['name']) < 1 || utf8_strlen($customFieldValueDescription['name']) > 128) {
                            $this->error['custom_field_value'][$customFieldValueId][$languageId] = $this->language->get('error_custom_value');
                        }
                    }
                }
            }
        }

        return empty($this->error);
    }

    protected function validate(): bool
    {
        $this->load->language('extension/module/' . $this->moduleName);

        if (! $this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['seo_url'])) {
            foreach ($this->request->post['seo_url'] as $storeId => $languages) {
                foreach ($languages as $languageId => $seo_url) {
                    if (!empty($seo_url)) {

                        if (count(array_keys($languages, $seo_url)) > 1) {
                            $this->error['seo_url'][$storeId][$languageId] = $this->language->get('error_seo_url_unique');
                        }

                        $seoUrlExists = $this->awCore->seoUrlExists(
                            $seo_url,
                            $storeId,
                            $languageId,
                            'extension/' . $this->moduleName . '/main'
                        );


                        if ($seoUrlExists) {
                            $this->error['seo_url'][$storeId][$languageId] = $this->language->get('error_seo_url_exists');
                        }
                    }

                    if ((utf8_strlen($seo_url) < 1) || (utf8_strlen($seo_url) > 255)) {
                        $this->error['seo_url'][$storeId][$languageId] = $this->language->get('error_seo_url');
                    }
                }
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    public function install()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_' . $this->moduleName, ['module_' . $this->moduleName . '_status' => '1']);
        $this->model_setting_setting->editSetting('module_' . $this->moduleChildName, ['module_' . $this->moduleChildName . '_status' => '1']);

        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_easy_checkout->install();

        $this->installPermissions();
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleChildName);

        $this->awCore->removeConfig($this->moduleName);
        $this->awCore->removeConfig($this->moduleChildName);
    }

    protected function installPermissions()
    {
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'access',
            'extension/module/' . $this->moduleName
        );
        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'modify',
            'extension/module/' . $this->moduleName
        );

        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'access',
            'extension/module/' . $this->moduleChildName
        );
        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'modify',
            'extension/module/' . $this->moduleChildName
        );
    }

    public function exportConfig()
    {
        if (! $this->validate()) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'error' => $this->language->get('error_permission'),
            ]));

            return;
        }

        try {
            $jsonData = $this->awCore->exportConfig($this->moduleName);

            $filename = $this->moduleName . '_settings_' . date('Y-m-d_H-i-s') . '.json';

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
            $this->response->setOutput($jsonData);

        } catch (Exception $e) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'error' => sprintf($this->language->get('error_import_failed'), $e->getMessage()),
            ]));
        }
    }

    public function importConfig()
    {
        $json = [];

        if (! $this->validate()) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->files['import_file']) && is_uploaded_file($this->request->files['import_file']['tmp_name'])) {
                try {
                    $fileContent = file_get_contents($this->request->files['import_file']['tmp_name']);

                    if ($fileContent === false) {
                        throw new Exception($this->language->get('error_import_read_file'));
                    }

                    $this->awCore->importConfig($this->moduleName, $fileContent);

                    $json['success'] = $this->language->get('text_import_success');

                } catch (Exception $e) {
                    $json['error'] = sprintf($this->language->get('error_import_failed'), $e->getMessage());
                }
            } else {
                $json['error'] = $this->language->get('error_import_file');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
