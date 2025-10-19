<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutApi extends Controller
{
    private string $moduleName = 'aw_easy_checkout';

    public function getShippingMethods($data = [])
    {
        $this->load->language('extension/' . $this->moduleName . '/lang');

        $json = [];

        if (! isset($this->session->data['api_id'])) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (empty($data['shipping_address'])) {
                $data['shipping_address'] = [
                    'country_id' => $this->config->get('config_country_id'),
                    'zone_id' => $this->config->get('config_zone_id'),
                    'firstname' => '',
                    'lastname' => '',
                    'company' => '',
                    'address_1' => '',
                ];
            }

            $shippingMethods = [];

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
                    $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($data['shipping_address']);
                    if ($quote) {
                        $shippingMethods[$result['code']] = [
                            'title' => $quote['title'],
                            'quote' => $quote['quote'],
                            'sort_order' => $quote['sort_order'],
                            'error' => $quote['error'],
                        ];
                    }
                }
            }

            $sortOrder = [];

            foreach ($shippingMethods as $key => $value) {
                $sortOrder[$key] = $value['sort_order'];
            }

            array_multisort($sortOrder, SORT_ASC, $shippingMethods);

            if (! empty($shippingMethods)) {
                foreach ($shippingMethods as $code => $shippingMethod) {
                    $this->load->language('extension/shipping/' . $code);

                    if ($shippingMethod['quote']) {
                        foreach (
                            $shippingMethod['quote'] as $quote => $quoteData
                        ) {
                            $json['shipping_methods'][] = [
                                'shipping_method' => $quoteData['code'],
                                'code' => $code . '_' . $quote,
                                'text' => $shippingMethod['title'] . ' - ( ' . $quoteData['title'] . ' ) - [ ' . $quoteData['code'] . ' ]',
                            ];
                        }
                    }
                }
            } else {
                $json['error'] = $this->language->get('text_empty_shipping_methods');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
