<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutCart extends Controller
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
        $this->load->language('extension/total/coupon');
        $this->load->language('extension/total/reward');
        $this->load->language('extension/total/voucher');

        $this->session->data['vouchers'] = $this->session->data['vouchers'] ?? [];

        $this->load->model('tool/image');
        $this->load->model('tool/upload');

        $themePrefix = ($this->awCore->isLegacy() ? '' : 'theme_') . $this->config->get('config_theme') . '_image_cart_';
        $data['cart_width'] = $this->config->get($themePrefix . 'width');
        $data['cart_height'] = $this->config->get($themePrefix . 'height');

        $data['products'] = [];
        $products = $this->cart->getProducts();

        $productTotals = [];
        foreach ($products as $product) {
            $productTotals[$product['product_id']] = ($productTotals[$product['product_id']] ?? 0) + $product['quantity'];
        }

        $frequencies = [
            'day' => $this->language->get('text_day'),
            'week' => $this->language->get('text_week'),
            'semi_month' => $this->language->get('text_semi_month'),
            'month' => $this->language->get('text_month'),
            'year' => $this->language->get('text_year'),
        ];

        $showPrices = $this->customer->isLogged() || ! $this->config->get('config_customer_price');
        $currency = $this->session->data['currency'];

        foreach ($products as $product) {
            $productTotal = $productTotals[$product['product_id']];

            if ($product['minimum'] > $productTotal) {
                $data['error_warning'] = sprintf(
                    $this->language->get('error_minimum'),
                    $product['name'],
                    $product['minimum']
                );
            }

            $image = $product['image'] ? $this->model_tool_image->resize(
                $product['image'],
                $data['cart_width'],
                $data['cart_height']
            ) : '';

            $optionData = [];
            foreach ($product['option'] as $option) {
                $value = $option['type'] != 'file' ? $option['value'] : ($this->model_tool_upload->getUploadByCode($option['value'])['name'] ?? '');

                $optionData[] = [
                    'name' => $option['name'],
                    'value' => utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value,
                ];
            }

            if ($showPrices) {
                $unitPrice = $this->tax->calculate(
                    $product['price'],
                    $product['tax_class_id'],
                    $this->config->get('config_tax')
                );
                $price = $this->currency->format($unitPrice, $currency);
                $total = $this->currency->format($unitPrice * $product['quantity'], $currency);
            } else {
                $price = $total = false;
            }

            $recurring = '';
            if ($product['recurring']) {
                $r = $product['recurring'];
                $taxPrice = fn ($p) => $this->currency->format($this->tax->calculate(
                    $p * $product['quantity'],
                    $product['tax_class_id'],
                    $this->config->get('config_tax')
                ), $currency);

                if ($r['trial']) {
                    $recurring = sprintf(
                        $this->language->get('text_trial_description'),
                        $taxPrice($r['trial_price']),
                        $r['trial_cycle'],
                        $frequencies[$r['trial_frequency']],
                        $r['trial_duration']
                    ) . ' ';
                }

                $textKey = $r['duration'] ? 'text_payment_description' : 'text_payment_cancel';
                $recurring .= sprintf(
                    $this->language->get($textKey),
                    $taxPrice($r['price']),
                    $r['cycle'],
                    $frequencies[$r['frequency']],
                    $r['duration']
                );
            }

            $data['products'][] = [
                'minimum' => max($product['minimum'] ?? 1, 1),
                'key' => $product['cart_id'],
                'product_id' => $product['product_id'],
                'thumb' => $image,
                'name' => $product['name'],
                'model' => $product['model'],
                'option' => $optionData,
                'quantity' => $product['quantity'],
                'stock' => $product['stock'] || ! (! $this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                'reward' => $product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : '',
                'price' => $price,
                'total' => $total,
                'href' => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                'remove' => $this->url->link('checkout/cart', 'remove=' . $product['cart_id']),
                'recurring' => $recurring,
            ];
        }

        $data['vouchers'] = [];

        foreach ($this->session->data['vouchers'] as $key => $voucher) {
            $data['vouchers'][] = [
                'key' => $key,
                'description' => $voucher['description'],
                'amount' => $this->currency->format($voucher['amount'], $currency),
                'remove' => $this->url->link('checkout/cart', 'remove=' . $key),
            ];
        }

        return $this->awCore->render('extension/' . $this->moduleName . '/cart', $data);
    }

    public function getTotals($data = [])
    {
        $this->load->language('extension/' . $this->moduleName . '/lang');

        $checkoutId = $this->config->get('config_checkout_id');
        if ($checkoutId) {
            $this->load->model('catalog/information');
            $informationInfo = $this->model_catalog_information->getInformation($checkoutId);

            $data['text_agree'] = $informationInfo ? sprintf(
                $this->language->get('text_agree'),
                $this->url->link('information/information/agree', 'information_id=' . $checkoutId),
                $informationInfo['title'],
                $informationInfo['title']
            ) : '';
        } else {
            $data['text_agree'] = '';
        }

        $shippingMethods = $this->moduleConfig->get('shipping_methods');
        $shippingCode = ! empty($this->session->data['shipping_method']['code']) ? str_replace(
            '.',
            '_',
            $this->session->data['shipping_method']['code']
        ) : null;

        $freeShippingData = ! empty($shippingMethods[$shippingCode]) ? $shippingMethods[$shippingCode] : $shippingMethods['default'];

        $orderSum = $this->cart->getSubTotal();
        $freeShippingFrom = $freeShippingData['free_shipping_price'] ?? 0;

        $data['free_shipping_status'] = ($freeShippingData['free_shipping_status'] ?? false) && $freeShippingFrom > 0;

        $freeShippingPercentage = $freeShippingFrom > 0 && $orderSum >= $freeShippingFrom ? 100 : round(($orderSum / max(
            $freeShippingFrom,
            1
        )) * 100, 2);

        $data['free_shipping_percentage'] = $freeShippingPercentage;
        $data['text_free_shipping'] = $this->language->get('text_free_shipping');

        if ($freeShippingPercentage >= 100) {
            $data['text_free_shipping_left'] = $this->language->get('text_free_shipping');
        } else {
            $data['text_free_shipping_left'] = sprintf(
                $this->language->get('text_free_shipping_left'),
                $this->currency->format($freeShippingFrom - $orderSum, $this->session->data['currency'])
            );
        }

        $totals = [];
        $taxes = $this->cart->getTaxes();
        $total = 0;

        $totalData = [
            'totals' => &$totals,
            'taxes' => &$taxes,
            'total' => &$total,
        ];

        if ($this->customer->isLogged() || ! $this->config->get('config_customer_price')) {
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
                    if ($data['free_shipping_status'] && $freeShippingPercentage == 100 && $result['code'] == 'shipping') {
                        if (isset($this->session->data['shipping_method']['cost'])) {
                            $this->session->data['shipping_method']['cost'] = 0;
                            $this->session->data['shipping_method']['title'] = $this->language->get('text_free_shipping');
                        }
                    }

                    $this->load->model('extension/total/' . $result['code']);
                    $this->{'model_extension_total_' . $result['code']}->getTotal($totalData);

                    if ($data['free_shipping_status'] && $freeShippingPercentage == 100 && $result['code'] == 'shipping') {
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
        }

        $data['totals'] = [];

        foreach ($totals as $totalItem) {
            $data['totals'][] = [
                'title' => $totalItem['title'],
                'value' => $totalItem['value'],
                'text' => $this->currency->format($totalItem['value'], $this->session->data['currency']),
                'code' => $totalItem['code'] ?? '',
            ];
        }

        $data['show_dont_call_me'] = $this->moduleConfig->get('show_dont_call_me');
        $data['dont_call_me'] = $this->session->data['dont_call_me'] ?? '';
        $data['agree'] = $this->session->data['agree'] ?? $this->moduleConfig->get('agree_default') ?? '';

        $data['show_weight'] = $this->moduleConfig->get('show_weight');
        $data['weight'] = $this->config->get('config_cart_weight') ? $this->weight->format(
            $this->cart->getWeight(),
            $this->config->get('config_weight_class_id'),
            $this->language->get('decimal_point'),
            $this->language->get('thousand_point')
        ) : '';

        $data['payment'] = false;

        return $this->awCore->render('extension/' . $this->moduleName . '/totals', $data);
    }

    public function editCart()
    {
        $json = [];

        if (! empty($this->request->post['quantity'])) {
            foreach ($this->request->post['quantity'] as $key => $value) {
                $this->cart->update($key, $value);
            }

            unset($this->session->data['reward']);
            $json['total'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function removeFromCart()
    {
        $json = [];

        if (isset($this->request->post['key'])) {
            $this->cart->remove($this->request->post['key']);

            unset($this->session->data['vouchers'][$this->request->post['key']]);

            $json['success'] = true;
            $json['total'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['reward']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
