<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutCustomer extends Controller
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

        $data['status_email'] = false;

        if (isset($this->session->data['customer_id'])) {
            $data['customer_id'] = $this->session->data['customer_id'];
        }

        $customerMethodsData = $this->moduleConfig->get('customer', []);

        $shippingCode = ! empty($this->session->data['shipping_method']['code']) ? str_replace(
            '.',
            '_',
            $this->session->data['shipping_method']['code']
        ) : null;

        $customerFields = ! empty($customerMethodsData[$shippingCode]) ? $customerMethodsData[$shippingCode] : $customerMethodsData['default'];

        $this->sortFieldsBySortOrder($customerFields);

        $customerLogged = $this->customer->isLogged();

        $this->load->model('extension/' . $this->moduleName . '/model');

        $this->load->model('account/customer_group');

        $data['customer_groups'] = [];

        if (is_array($this->config->get('config_customer_group_display'))) {
            $customerGroups = $this->model_account_customer_group->getCustomerGroups();

            foreach ($customerGroups as $customerGroup) {
                if (
                    in_array($customerGroup['customer_group_id'], $this->config->get('config_customer_group_display'))
                ) {
                    $data['customer_groups'][] = $customerGroup;
                }
            }
        }

        if ($this->customer->isLogged()) {
            $customerGroupId = $data['customer_group_id'] = $this->config->get('config_customer_group_id');
        } else {
            if (isset($this->request->post['customer_group_id'])) {
                $customerGroupId = $data['customer_group_id'] = $this->session->data['guest']['customer_group_id'] = $this->request->post['customer_group_id'];
            } elseif (isset($this->session->data['guest']['customer_group_id'])) {
                $customerGroupId = $data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
            } else {
                $customerGroupId = $data['customer_group_id'] = $this->config->get('config_customer_group_id');
            }
        }

        $customerCustomFields = $this->model_extension_aw_easy_checkout_model->getCustomFields('customer', $customerGroupId);

        $data['customer_fields'] = [];

        foreach ($customerFields as $fieldKey => $customerField) {
            if (is_array($customerField)) {
                if (isset($customerField['status']) && ($customerField['status'] != '0')) {
                    if ($customerLogged && ($customerField['show_when'] == 'guest')) {
                        continue;
                    }

                    if (! $customerLogged && ($customerField['show_when'] == 'authorized')) {
                        continue;
                    }

                    if (! empty($customerField['setting']['field_name'][$this->config->get('config_language_id')])) {
                        $data['entry_' . $fieldKey] = $customerField['setting']['field_name'][$this->config->get('config_language_id')];
                    }
                    if (! empty($customerField['setting']['placeholder_field'][$this->config->get('config_language_id')])) {
                        $data['entry_placeholder_' . $fieldKey] = $customerField['setting']['placeholder_field'][$this->config->get('config_language_id')];
                    }

                    $data['customer_fields'][$fieldKey] = [
                        'status' => $customerField['status'],
                    ];
                }

                if (strpos($fieldKey, 'custom_field_') === 0) {
                    if (! empty($customerCustomFields[$customerField['id']])) {
                        if ($customerLogged && ($customerField['show_when'] == 'guest')) {
                            continue;
                        }

                        if (! $customerLogged && ($customerField['show_when'] == 'authorized')) {
                            continue;
                        }
                        $data['customer_fields'][$fieldKey] = $customerCustomFields[$customerField['id']];
                    }
                }
            }
        }

        $registerStatus = $this->moduleConfig->get('register_status', 'default');

        $data['register_status'] = true;
        $data['register_required'] = false;

        if ($this->customer->isLogged() || $registerStatus === 'disabled') {
            $data['register_status'] = false;
        }

        if (! $this->customer->isLogged() && $registerStatus === 'required') {
            $data['register_required'] = true;
        }

        $data['register_checked'] = false;

        if (! $this->customer->isLogged()) {
            if (isset($this->session->data['register'])) {
                $data['register_checked'] = $this->session->data['register'];
            }

            if ($data['register_required'] || $data['register_checked']) {
                $data['customer_fields']['email']['status'] = 'required';
            }
        }

        $data['customerFields'] = $data['customer_fields'];

        $customerFieldsData = [
            'firstname',
            'lastname',
            'telephone',
            'fax',
            'email',
        ];

        foreach ($customerFieldsData as $field) {
            if (isset($this->request->post[$field])) {
                $data[$field] = $this->session->data['customer'][$field] = $this->request->post[$field];
            } elseif (isset($this->session->data['customer'][$field])) {
                $data[$field] = $this->session->data['customer'][$field];
            } else {
                $data[$field] = '';
            }
        }

        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $data['email'] = '';
        }

        if ($this->customer->isLogged()) {
            $this->load->model('account/address');

            $data['firstname'] = $this->session->data['customer']['firstname'] ??
                                $this->session->data['shipping_address']['firstname'] ??
                                $this->session->data['payment_address']['firstname'] ??
                                $this->customer->getFirstName();

            $data['lastname'] = $this->session->data['customer']['lastname'] ??
                               $this->session->data['shipping_address']['lastname'] ??
                               $this->session->data['payment_address']['lastname'] ??
                               $this->customer->getLastName();

            $data['email'] = $this->customer->getEmail();
            $data['telephone'] = (! empty($this->session->data['customer']['telephone'])) ? $this->session->data['customer']['telephone'] : $this->customer->getTelephone();
            $data['payment_address_id'] = $this->customer->getAddressId();
            $data['address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
        }

        if (isset($this->request->post['custom_field']['customer'])) {
            $data['customer_custom_field'] = $this->session->data['guest']['customer_custom_field'] = $this->request->post['custom_field']['customer'];
        } elseif (isset($this->session->data['guest']['customer_custom_field'])) {
            $data['customer_custom_field'] = $this->session->data['guest']['customer_custom_field'];
        } else {
            $data['customer_custom_field'] = [];
        }

        return $this->awCore->render('extension/' . $this->moduleName . '/customer', $data);
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
