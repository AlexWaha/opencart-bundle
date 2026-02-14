<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

use Alexwaha\EasyCheckoutHelper;

class ControllerExtensionAwEasyCheckoutMain extends Controller
{
    private string $moduleName = 'aw_easy_checkout';

    private \Alexwaha\Config $moduleConfig;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);

        // Register URL rewrite handler early
        $helper = new \Alexwaha\EasyCheckoutHelper($registry);
        $this->url->addRewrite($helper);
    }

    public function index()
    {
        $this->load->language('extension/' . $this->moduleName . '/lang');

        if (!$this->moduleConfig->get('status')) {
            $this->response->redirect($this->url->link('error/not_found'));
        }

        unset($this->session->data['success']);

        $this->document->setTitle($this->language->get('heading_title'));

        $scripts = [];

        if ($this->awCore->isLegacy()) {
            $scripts[] = 'catalog/view/javascript/jquery/datetimepicker/moment.js';
        } else {
            $scripts[] = 'catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js';
            $scripts[] = 'catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js';
        }

        $scripts[] = 'catalog/view/javascript/' . $this->moduleName . '/select2.min.js';
        $scripts[] = 'catalog/view/javascript/' . $this->moduleName . '/script.min.js';
        $scripts[] = 'catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js';
        $scripts[] = 'catalog/view/javascript/' . $this->moduleName . '/maska.min.js';

        $styles = [
            'catalog/view/javascript/' . $this->moduleName . '/select2.min.css',
            'catalog/view/javascript/' . $this->moduleName . '/base.css',
            'catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css',
        ];

        if (!empty($this->moduleConfig->get('use_theme_css'))) {
            $customTheme = $this->moduleConfig->get('custom_theme_css');
            $themeFile = !empty($customTheme) ? trim($customTheme) . '.css' : 'theme.css';
            $styles[] = 'catalog/view/javascript/' . $this->moduleName . '/' . $themeFile;
        }

        foreach ($scripts as $script) {
            $this->document->addScript($script);
        }

        foreach ($styles as $style) {
            $this->document->addStyle($style);
        }

        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home'),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/' . $this->moduleName . '/main'),
            ],
        ];

        unset($this->session->data['shipping_address_id']);

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_customer'] = $this->language->get('text_customer');

        $data['status'] = $this->moduleConfig->get('status', false);
        $data['mask'] = $this->moduleConfig->get('mask', '');
        $data['phone_validation'] = $this->moduleConfig->get('phone_validation', '');
        $data['email_default'] = $this->moduleConfig->get('email_default', '');
        $data['agree_default'] = $this->moduleConfig->get('agree_default', false);
        $data['min_price_order'] = $this->moduleConfig->get('min_price_order', []);

        $blockStatus = $this->moduleConfig->get('block_status', []);

        if ((! $this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (! $this->cart->hasStock() && ! $this->config->get('config_stock_checkout'))) {
            $data['errors']['stock'] = $this->language->get('error_stock');
        }

        $customerGroupId = $this->config->get('config_customer_group_id');
        if (! empty($data['min_price_order'][$customerGroupId]) && $this->cart->getTotal() < $data['min_price_order'][$customerGroupId]) {
            $data['errors']['error_min_totals'] = sprintf(
                $this->language->get('text_min_totals_order'),
                $this->currency->format(
                    $data['min_price_order'][$customerGroupId],
                    $this->session->data['currency']
                )
            );
        }

        $this->session->data['guest']['customer_group_id'] = $this->session->data['guest']['customer_group_id'] ?? (int) $this->config->get('config_customer_group_id');

        if (! $this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
            $this->response->redirect($this->url->link('common/home'));
        }

        if ($this->customer->isLogged()) {
            $data['customer_id'] = $this->session->data['customer_id'];

            $currentCustomerId = $this->customer->getId();
            $sessionCustomerId = $this->session->data['checkout_customer_id'] ?? null;

            if ($sessionCustomerId && $sessionCustomerId != $currentCustomerId) {
                $sessionKeys = [
                    'checkout_customer_id',
                    'shipping_method',
                    'shipping_methods',
                    'shipping_address',
                    'shipping_address_id',
                    'payment_address',
                    'payment_address_id',
                    'payment_method',
                    'payment_methods',
                    'guest',
                    'account',
                    'customer',
                    'shipping_country_id',
                    'shipping_zone_id',
                    'payment_country_id',
                    'payment_zone_id',
                ];
                foreach ($sessionKeys as $key) {
                    unset($this->session->data[$key]);
                }
            }

            $this->session->data['checkout_customer_id'] = $currentCustomerId;

            $this->load->model('account/address');
            $customerAddress = $this->model_account_address->getAddress($this->customer->getAddressId());

            if ($customerAddress) {
                $addressData = [
                    'firstname' => $customerAddress['firstname'] ?? '',
                    'lastname' => $customerAddress['lastname'] ?? '',
                    'country_id' => $customerAddress['country_id'] ?? '',
                    'zone_id' => $customerAddress['zone_id'] ?? '',
                    'city' => $customerAddress['city'] ?? '',
                    'address_1' => $customerAddress['address_1'] ?? '',
                    'address_2' => $customerAddress['address_2'] ?? '',
                    'postcode' => $customerAddress['postcode'] ?? '',
                    'company' => $customerAddress['company'] ?? '',
                ];

                $this->session->data['shipping_address'] = $addressData;
                $this->session->data['payment_address'] = $addressData;
            }
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

        $validPositions = [
            'fix_right',
            'top_left',
            'center_left',
            'center_right',
            'bottom_left',
            'bottom_full',
            'top_full',
        ];

        $data['blocks'] = [];

        foreach ($blockPositions as $blockName => $blockPosition) {
            if (! in_array($blockPosition, $validPositions)) {
                continue;
            }

            $status = $blockStatus[$blockName] ?? true;

            $data['blocks'][$blockPosition][] = [
                'name' => $blockName,
                'status' => $status,
            ];
        }

        foreach ($data['blocks'] as &$blocks) {
            usort($blocks, function ($a, $b) use ($blockSortOrder) {
                $orderA = isset($blockSortOrder[$a['name']]) ? (int) $blockSortOrder[$a['name']] : 999;
                $orderB = isset($blockSortOrder[$b['name']]) ? (int) $blockSortOrder[$b['name']] : 999;

                return $orderA - $orderB;
            });
        }
        unset($blocks);

        $paymentAddressSameAsShipping = $this->moduleConfig->get('payment_address_same_as_shipping', true);
        if (! $paymentAddressSameAsShipping) {
            foreach ($data['blocks'] as &$blocks) {
                $shippingAddressIndex = null;
                foreach ($blocks as $index => $block) {
                    if ($block['name'] === 'shipping_address') {
                        $shippingAddressIndex = $index;
                        break;
                    }
                }

                if ($shippingAddressIndex !== null) {
                    array_splice($blocks, $shippingAddressIndex + 1, 0, [[
                        'name' => 'payment_address',
                        'status' => $blockStatus['payment_address'] ?? true,
                    ]]);
                    break;
                }
            }
            unset($blocks);
        }

        $data['block']['customer'] = $this->load->controller('extension/' . $this->moduleName . '/customer', $data);
        $data['block']['cart'] = $this->load->controller('extension/' . $this->moduleName . '/cart', $data);

        $shippingMethodData = array_merge($data, ['render' => false]);
        $data['block']['shipping_method'] = $this->load->controller('extension/' . $this->moduleName . '/shipping_method', $shippingMethodData);

        $paymentMethodData = array_merge($data, ['render' => false]);
        $data['block']['payment_method'] = $this->load->controller('extension/' . $this->moduleName . '/payment_method', $paymentMethodData);

        $shippingAddressData = array_merge($data, ['addressType' => 'shipping']);
        $data['block']['shipping_address'] = $this->load->controller('extension/' . $this->moduleName . '/address', $shippingAddressData);

        if (! $paymentAddressSameAsShipping) {
            $paymentAddressData = array_merge($data, ['addressType' => 'payment']);
            $data['block']['payment_address'] = $this->load->controller('extension/' . $this->moduleName . '/address', $paymentAddressData);
        } else {
            $data['block']['payment_address'] = '';
        }

        $data['block']['coupon'] = $this->load->controller('extension/' . $this->moduleName . '/coupon_voucher/getCoupon', $data);
        $data['block']['voucher'] = $this->load->controller('extension/' . $this->moduleName . '/coupon_voucher/getVoucher', $data);
        $data['block']['totals'] = $this->load->controller('extension/' . $this->moduleName . '/cart/getTotals', $data);
        $data['block']['comment'] = $this->load->controller('extension/' . $this->moduleName . '/comment');
        $data['block']['custom_text'] = $this->load->controller('extension/' . $this->moduleName . '/custom_text');

        $data['country_id'] = $this->session->data['shipping_address']['country_id'] ?? $this->config->get('config_country_id');

        $maskType = $this->moduleConfig->get('mask_type', 'dynamic');

        $helper = new EasyCheckoutHelper($this->registry);

        if ($maskType === 'disabled') {
            $telMask = '';
            $countryMasks = [];
            $currentCountryIso = '';
        } elseif ($maskType === 'static') {
            $telMask = $this->moduleConfig->get('mask', '');
            $countryMasks = [];
            $currentCountryIso = '';
        } else {
            $customMask = $this->moduleConfig->get('mask', '');

            if (! empty($customMask)) {
                $telMask = $customMask;
            } else {
                if (! empty($this->session->data['shipping_address']['country_id'])) {
                    $countryId = $this->session->data['shipping_address']['country_id'];
                } elseif (! empty($this->session->data['payment_address']['country_id'])) {
                    $countryId = $this->session->data['payment_address']['country_id'];
                } else {
                    $countryId = $this->config->get('config_country_id');
                }

                $telMask = $helper->getPhoneMaskByCountryId($countryId) ?: '';
                $currentCountryIso = $helper->getCountryIsoCode($countryId) ?: 'UA';
            }

            $countryMasks = $helper->getCountryPhoneMasks();
        }

        $data['setting'] = [
            'text_select' => $this->language->get('text_select'),
            'text_coupon_required' => $this->language->get('text_coupon_required'),
            'text_coupon_success' => $this->language->get('text_coupon_success'),
            'text_voucher_required' => $this->language->get('text_voucher_required'),
            'text_voucher_success' => $this->language->get('text_voucher_success'),
            'tel_mask' => $telMask,
            'country_masks' => $countryMasks,
            'current_country_iso' => $currentCountryIso ?? '',
            'mask_type' => $maskType,
            'load_script' => html_entity_decode($this->moduleConfig->get('javascript', ''), ENT_QUOTES, 'UTF-8'),
        ];

        $data['col_right_width'] = max(1, (int) $this->moduleConfig->get('col_right_width', 35));
        $data['col_left_width'] = max(1, (int) $this->moduleConfig->get('col_left_width', 65));

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->awCore->render('extension/' . $this->moduleName . '/main', $data));
    }

    public function login()
    {
        $this->load->language('extension/' . $this->moduleName . '/lang');

        $data['register'] = $this->url->link('account/register', '', true);
        $data['forgotten'] = $this->url->link('account/forgotten', '', true);

        $this->response->setOutput($this->awCore->render('extension/' . $this->moduleName . '/login', $data));
    }

    public function getErrors(): array
    {
        $this->load->language('extension/' . $this->moduleName . '/lang');

        $data = [];
        $hasProducts = $this->cart->hasProducts() || ! empty($this->session->data['vouchers']);
        $hasStock = $this->cart->hasStock() || $this->config->get('config_stock_checkout');

        if (! $hasProducts || ! $hasStock) {
            $data['stock'] = $this->language->get('error_stock');
        }

        $minPriceOrder = $this->moduleConfig->get('min_price_order', []);
        $customerGroupId = $this->config->get('config_customer_group_id');
        $minPrice = $minPriceOrder[$customerGroupId] ?? 0;

        if ($minPrice > 0 && $this->cart->getTotal() < $minPrice) {
            $data['error_min_totals'] = sprintf(
                $this->language->get('text_min_totals_order'),
                $this->currency->format($minPrice, $this->session->data['currency'])
            );
        }

        return $data;
    }
}
