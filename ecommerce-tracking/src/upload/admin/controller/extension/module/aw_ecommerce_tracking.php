<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwEcommerceTracking extends Controller
{
    private string $moduleName = 'aw_ecommerce_tracking';

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

    public function index(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

        $this->load->model('setting/setting');

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

        $this->params['status'] = $this->moduleConfig->get('status', false);
        $this->params['trackingCode'] = $this->moduleConfig->get('tracking_code', '');
        $this->params['trackingCodeBody'] = $this->moduleConfig->get('tracking_code_body', '');
        $this->params['debugMode'] = $this->moduleConfig->get('debug_mode', false);

        $this->params['trackCategory'] = $this->moduleConfig->get('track_category', true);
        $this->params['trackSearch'] = $this->moduleConfig->get('track_search', true);
        $this->params['trackManufacturer'] = $this->moduleConfig->get('track_manufacturer', true);
        $this->params['trackSpecial'] = $this->moduleConfig->get('track_special', true);
        $this->params['trackProduct'] = $this->moduleConfig->get('track_product', true);
        $this->params['trackCompare'] = $this->moduleConfig->get('track_compare', false);

        $this->params['trackModuleLatest'] = $this->moduleConfig->get('track_module_latest', true);
        $this->params['trackModuleFeatured'] = $this->moduleConfig->get('track_module_featured', true);
        $this->params['trackModuleBestseller'] = $this->moduleConfig->get('track_module_bestseller', true);
        $this->params['trackModuleSpecial'] = $this->moduleConfig->get('track_module_special', true);
        $this->params['trackModuleAwViewed'] = $this->moduleConfig->get('track_module_aw_viewed', false);

        $this->params['trackAddToCart'] = $this->moduleConfig->get('track_add_to_cart', true);
        $this->params['trackRemoveFromCart'] = $this->moduleConfig->get('track_remove_from_cart', true);
        $this->params['trackViewCart'] = $this->moduleConfig->get('track_view_cart', true);
        $this->params['trackBeginCheckout'] = $this->moduleConfig->get('track_begin_checkout', true);
        $this->params['trackShippingInfo'] = $this->moduleConfig->get('track_shipping_info', true);
        $this->params['trackPaymentInfo'] = $this->moduleConfig->get('track_payment_info', true);
        $this->params['trackPurchase'] = $this->moduleConfig->get('track_purchase', true);
        $this->params['includeTax'] = $this->moduleConfig->get('include_tax', true);
        $this->params['includeShipping'] = $this->moduleConfig->get('include_shipping', true);
        $this->params['includeCoupons'] = $this->moduleConfig->get('include_coupons', true);

        $this->params['trackLogin'] = $this->moduleConfig->get('track_login', true);
        $this->params['trackSignup'] = $this->moduleConfig->get('track_signup', true);
        $this->params['trackWishlist'] = $this->moduleConfig->get('track_wishlist', true);
        $this->params['trackSelectItem'] = $this->moduleConfig->get('track_select_item', true);
        $this->params['trackCoupon'] = $this->moduleConfig->get('track_coupon', true);

        $this->params['currencyFormat'] = $this->moduleConfig->get('currency_format', 'session');
        $this->params['priceWithTax'] = $this->moduleConfig->get('price_with_tax', true);
        $this->params['sendProductOptions'] = $this->moduleConfig->get('send_product_options', true);
        $this->params['customDimensions'] = $this->moduleConfig->get('custom_dimensions', '');

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/main', $this->params));
    }

    public function store(): void
    {
        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
            $this->awCore->setConfig($this->moduleName, $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                'extension/module/' . $this->moduleName,
                $this->tokenData['param'] . '&type=module',
                true
            ));
        }

        $this->index();
    }

    protected function validate(): bool
    {
        $this->load->language('extension/module/' . $this->moduleName);

        if (!$this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return empty($this->error);
    }

    public function install(): void
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting(
            'module_' . $this->moduleName,
            ['module_' . $this->moduleName . '_status' => '1']
        );

        $this->installPermissions();
    }

    public function uninstall(): void
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);

        $this->awCore->removeConfig($this->moduleName);
    }

    protected function installPermissions(): void
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
    }
}
