<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutReload extends Controller
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

        if (! isset($this->session->data['address_just_changed'])) {
            $this->save();
        } else {
            unset($this->session->data['address_just_changed']);
        }

        if (! $this->cart->hasProducts()) {
            $json['redirect'] = $this->url->link('checkout/cart');
        } else {
            $methods = [
                'shipping_method',
                'shipping_address',
                'payment_address',
                'payment_method',
                'customer',
                'cart',
                'coupon',
                'voucher',
                'totals',
            ];

            $paymentAddressSameAsShipping = $this->moduleConfig->get('payment_address_same_as_shipping', true);

            foreach ($methods as $method) {
                if ($method === 'payment_address' && $paymentAddressSameAsShipping) {
                    $json[$method] = '';

                    continue;
                }

                $methodName = str_replace('_', '', ucwords($method, '_'));
                if ($method === 'coupon' || $method === 'voucher') {
                    $json[$method] = $this->{'get' . $methodName}([]);
                } else {
                    $json[$method] = $this->{'get' . $methodName}([]);
                }
            }

            $json['errors'] = $this->getErrors();
            $this->getAbandonedOrders();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function save()
    {
        $customerFields = [
            'firstname',
            'lastname',
            'telephone',
            'email',
            'fax',
        ];
        foreach ($customerFields as $field) {
            if (isset($this->request->post[$field])) {
                $this->session->data['customer'][$field] = $this->request->post[$field];
            }
        }

        $shippingAddressFields = [
            'shipping_city' => 'city',
            'shipping_address_1' => 'address_1',
            'shipping_address_2' => 'address_2',
            'shipping_postcode' => 'postcode',
            'shipping_company' => 'company',
            'shipping_country_id' => 'country_id',
            'shipping_zone_id' => 'zone_id',
        ];
        foreach ($shippingAddressFields as $postField => $sessionField) {
            if (isset($this->request->post[$postField])) {
                $this->session->data['shipping_address'][$sessionField] = $this->request->post[$postField];

                if (isset($this->request->post['shipping_method'])) {
                    if (
                        $sessionField === 'city' && in_array($this->request->post['shipping_method'], [
                            'novaposhta.department',
                            'novaposhta.poshtomat',
                        ])
                    ) {
                        $this->session->data['shipping_method_address']['novaposhta.department'][$sessionField] = $this->request->post[$postField];
                        $this->session->data['shipping_method_address']['novaposhta.poshtomat'][$sessionField] = $this->request->post[$postField];
                    } else {
                        $this->session->data['shipping_method_address'][$this->request->post['shipping_method']][$sessionField] = $this->request->post[$postField];
                    }
                }
            } elseif (isset($this->session->data['shipping_address'][$sessionField])) {
                if (isset($this->request->post['shipping_method'])) {
                    if (
                        $sessionField === 'city' && in_array($this->request->post['shipping_method'], [
                            'novaposhta.department',
                            'novaposhta.poshtomat',
                        ])
                    ) {
                        if (! isset($this->session->data['shipping_method_address']['novaposhta.department'][$sessionField])) {
                            $this->session->data['shipping_method_address']['novaposhta.department'][$sessionField] = $this->session->data['shipping_address'][$sessionField];
                        }
                        if (! isset($this->session->data['shipping_method_address']['novaposhta.poshtomat'][$sessionField])) {
                            $this->session->data['shipping_method_address']['novaposhta.poshtomat'][$sessionField] = $this->session->data['shipping_address'][$sessionField];
                        }
                    } else {
                        if (! isset($this->session->data['shipping_method_address'][$this->request->post['shipping_method']][$sessionField])) {
                            $this->session->data['shipping_method_address'][$this->request->post['shipping_method']][$sessionField] = $this->session->data['shipping_address'][$sessionField];
                        }
                    }
                }
            }
        }

        $paymentAddressFields = [
            'payment_city' => 'city',
            'payment_address_1' => 'address_1',
            'payment_address_2' => 'address_2',
            'payment_postcode' => 'postcode',
            'payment_company' => 'company',
            'payment_country_id' => 'country_id',
            'payment_zone_id' => 'zone_id',
        ];

        foreach ($paymentAddressFields as $postField => $sessionField) {
            if (isset($this->request->post[$postField])) {
                $this->session->data['payment_address'][$sessionField] = $this->request->post[$postField];
            } elseif (! isset($this->session->data['payment_address'][$sessionField]) && isset($this->session->data['shipping_address'][$sessionField])) {
                $this->session->data['payment_address'][$sessionField] = $this->session->data['shipping_address'][$sessionField];
            }
        }

        $customFields = [
            'customer' => 'customer_custom_field',
            'address' => 'address_custom_field',
            'payment' => 'payment_custom_field',
        ];

        foreach ($customFields as $type => $sessionKey) {
            $this->session->data['guest'][$sessionKey] = $this->request->post['custom_field'][$type] ?? [];
        }

        $this->session->data['payment_address_same_as_shipping'] = isset($this->request->post['payment_address_same_as_shipping']) ? 1 : 0;
        $this->session->data['register'] = isset($this->request->post['register']) ? 1 : 0;
        $this->session->data['dont_call_me'] = isset($this->request->post['dont_call_me']) ? 1 : 0;
        $this->session->data['agree'] = isset($this->request->post['agree']) ? 1 : 0;

        $this->getAbandonedOrders();
    }

    public function getAbandonedOrders()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('tool/upload');

            $products = [];

            foreach ($this->cart->getProducts() as $product) {
                $optionData = [];

                foreach ($product['option'] as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $uploadInfo = $this->model_tool_upload->getUploadByCode($option['value']);

                        if ($uploadInfo) {
                            $value = $uploadInfo['name'];
                        } else {
                            $value = '';
                        }
                    }

                    $optionData[] = [
                        'name' => $option['name'],
                        'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
                    ];
                }

                if ($this->customer->isLogged() || ! $this->config->get('config_customer_price')) {
                    $unitPrice = $this->tax->calculate(
                        $product['price'],
                        $product['tax_class_id'],
                        $this->config->get('config_tax')
                    );
                    $price = $this->currency->format($unitPrice, $this->session->data['currency']);
                    $total = $this->currency->format(
                        $unitPrice * $product['quantity'],
                        $this->session->data['currency']
                    );
                } else {
                    $price = false;
                    $total = false;
                }

                $products[] = [
                    'name' => $product['name'],
                    'product_id' => $product['product_id'],
                    'model' => $product['model'],
                    'option' => $optionData,
                    'quantity' => $product['quantity'],
                    'price' => $price,
                    'total' => $total,
                    'url' => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                ];
            }

            $abandonedData = [
                'store_id' => $this->config->get('store_id'),
                'customer_id' => $this->customer->isLogged() ? $this->customer->getId() : '',
                'email' => '',
                'firstname' => '',
                'lastname' => '',
                'telephone' => '',
                'products' => $products,
            ];

            if (isset($this->request->post['firstname'])) {
                $abandonedData['firstname'] = $this->request->post['firstname'];
            }
            if (isset($this->request->post['lastname'])) {
                $abandonedData['lastname'] = $this->request->post['lastname'];
            }

            $telephone = $this->request->post['telephone'] ?? '';
            if (((utf8_strlen($telephone) > 3) || (utf8_strlen($telephone) < 32))) {
                $abandonedData['telephone'] = $telephone;
            }

            $email = $this->request->post['email'] ?? '';

            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $abandonedData['email'] = $email;
            }

            if (! empty($abandonedData['products']) && (! empty($abandonedData['email']) || ! empty($abandonedData['telephone']))) {
                $this->load->model('extension/' . $this->moduleName . '/model');

                if (! isset($this->session->data['abandoned_id'])) {
                    $this->session->data['abandoned_id'] = $this->model_extension_aw_easy_checkout_model->addAbandonedOrder($abandonedData);
                } else {
                    $abandonedId = $this->session->data['abandoned_id'];
                    $this->session->data['abandoned_id'] = $this->model_extension_aw_easy_checkout_model->editAbandonedOrder(
                        $abandonedId,
                        $abandonedData
                    );
                }
            }
        }
    }

    private function getShippingMethod($data = [])
    {
        $data['render'] = false;

        return $this->load->controller('extension/' . $this->moduleName . '/shipping_method', $data);
    }

    private function getShippingAddress($data = [])
    {
        $data['addressType'] = 'shipping';

        return $this->load->controller('extension/' . $this->moduleName . '/address', $data);
    }

    private function getPaymentAddress($data = [])
    {
        $data['addressType'] = 'payment';

        return $this->load->controller('extension/' . $this->moduleName . '/address', $data);
    }

    private function getPaymentMethod($data = [])
    {
        $data['render'] = false;

        return $this->load->controller('extension/' . $this->moduleName . '/payment_method', $data);
    }

    private function getCustomer($data = [])
    {
        return $this->load->controller('extension/' . $this->moduleName . '/customer', $data);
    }

    private function getCart($data = [])
    {
        return $this->load->controller('extension/' . $this->moduleName . '/cart', $data);
    }

    private function getCoupon($data = [])
    {
        return $this->load->controller('extension/' . $this->moduleName . '/coupon_voucher/getCoupon', $data);
    }

    private function getVoucher($data = [])
    {
        return $this->load->controller('extension/' . $this->moduleName . '/coupon_voucher/getVoucher', $data);
    }

    private function getTotals($data = [])
    {
        return $this->load->controller('extension/' . $this->moduleName . '/cart/getTotals', $data);
    }

    private function getErrors()
    {
        return $this->load->controller('extension/' . $this->moduleName . '/getErrors');
    }

    public function changeAddress()
    {
        $json = ['success' => false];

        if ($this->customer->isLogged() && isset($this->request->post['address_id'])) {
            $addressId = (int) $this->request->post['address_id'];

            $this->load->model('account/address');
            $address = $this->model_account_address->getAddress($addressId);

            if ($address) {
                $addressData = [
                    'address_id' => $addressId,
                    'firstname' => $address['firstname'] ?? '',
                    'lastname' => $address['lastname'] ?? '',
                    'country_id' => $address['country_id'] ?? '',
                    'zone_id' => $address['zone_id'] ?? '',
                    'city' => $address['city'] ?? '',
                    'address_1' => $address['address_1'] ?? '',
                    'address_2' => $address['address_2'] ?? '',
                    'postcode' => $address['postcode'] ?? '',
                    'company' => $address['company'] ?? '',
                ];

                $this->session->data['shipping_address'] = $addressData;
                $this->session->data['payment_address'] = $addressData;
                $this->session->data['customer']['firstname'] = $addressData['firstname'];
                $this->session->data['customer']['lastname'] = $addressData['lastname'];

                unset($this->session->data['shipping_method_address']);

                $this->session->data['address_just_changed'] = true;

                $json['success'] = true;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
