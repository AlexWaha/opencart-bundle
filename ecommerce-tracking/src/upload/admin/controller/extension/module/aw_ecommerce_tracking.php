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
        $this->params['module_name'] = $this->moduleName;
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

    public function diagnostics(): void
    {
        $json = [];

        $this->load->model('extension/module/' . $this->moduleName);

        $expectedTriggers = [
            'catalog/view/common/header/after',
            'catalog/view/common/footer/after',
            'catalog/view/product/category/after',
            'catalog/view/product/search/after',
            'catalog/view/product/manufacturer_info/after',
            'catalog/view/product/special/after',
            'catalog/view/product/product/after',
            'catalog/view/checkout/cart/after',
            'catalog/view/checkout/checkout/after',
            'catalog/controller/extension/aw_easy_checkout/main/after',
            'catalog/view/common/success/after',
            'catalog/controller/account/login/after',
            'catalog/controller/account/register/after',
            'catalog/view/extension/module/featured/after',
            'catalog/view/extension/module/latest/after',
            'catalog/view/extension/module/bestseller/after',
            'catalog/view/extension/module/special/after',
        ];

        $eventRows = $this->{'model_extension_module_' . $this->moduleName}->getRegisteredEvents('aw_et_');

        $registeredTriggers = [];
        foreach ($eventRows as $row) {
            $registeredTriggers[$row['trigger']] = (int) ($row['status'] ?? 1);
        }

        $eventDetails = [];
        $registeredCount = 0;

        foreach ($expectedTriggers as $trigger) {
            $exists = isset($registeredTriggers[$trigger]);
            $enabled = $exists && $registeredTriggers[$trigger] === 1;

            if ($exists && $enabled) {
                $registeredCount++;
            }

            $eventDetails[] = [
                'trigger' => $trigger,
                'exists'  => $exists,
                'enabled' => $enabled,
            ];
        }

        $json['events'] = [
            'status'     => $registeredCount === count($expectedTriggers) ? 'ok' : 'error',
            'total'      => count($expectedTriggers),
            'registered' => $registeredCount,
            'details'    => $eventDetails,
        ];

        $json['config'] = $this->checkConfig();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function checkConfig(): array
    {
        $missing = [];

        $trackingCode = $this->moduleConfig->get('tracking_code', '');
        if (empty($trackingCode)) {
            $missing[] = ['field' => 'tracking_code', 'label' => $this->language->get('entry_tracking_code'), 'tab' => 'general'];
        }

        $status = $this->moduleConfig->get('status', false);
        if (!$status) {
            $missing[] = ['field' => 'status', 'label' => $this->language->get('entry_status'), 'tab' => 'general'];
        }

        return [
            'status'  => empty($missing) ? 'ok' : 'warning',
            'missing' => $missing,
        ];
    }

    public function exportConfig(): void
    {
        if (!$this->validate()) {
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

    public function importConfig(): void
    {
        $json = [];

        if (!$this->validate()) {
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
        $this->installEvents();
    }

    public function uninstall(): void
    {
        $this->uninstallEvents();

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);

        $this->awCore->removeConfig($this->moduleName);
    }

    private function installEvents(): void
    {
        $this->load->model('setting/event');

        $events = [
            // Global — GTM/gtag code injection
            ['aw_et_header',         'catalog/view/common/header/after',                'extension/aw_ecommerce_tracking/event/viewHeaderAfter'],
            ['aw_et_footer',         'catalog/view/common/footer/after',                'extension/aw_ecommerce_tracking/event/viewFooterAfter'],

            // Pages — view_item_list / view_item events
            ['aw_et_category',       'catalog/view/product/category/after',             'extension/aw_ecommerce_tracking/event/viewCategoryAfter'],
            ['aw_et_search',         'catalog/view/product/search/after',               'extension/aw_ecommerce_tracking/event/viewSearchAfter'],
            ['aw_et_manufacturer',   'catalog/view/product/manufacturer_info/after',    'extension/aw_ecommerce_tracking/event/viewManufacturerAfter'],
            ['aw_et_special',        'catalog/view/product/special/after',              'extension/aw_ecommerce_tracking/event/viewSpecialAfter'],
            ['aw_et_product',        'catalog/view/product/product/after',              'extension/aw_ecommerce_tracking/event/viewProductAfter'],

            // Checkout flow
            ['aw_et_cart',           'catalog/view/checkout/cart/after',                'extension/aw_ecommerce_tracking/event/viewCartAfter'],
            ['aw_et_checkout',       'catalog/view/checkout/checkout/after',            'extension/aw_ecommerce_tracking/event/viewCheckoutAfter'],
            ['aw_et_ec_main',        'catalog/controller/extension/aw_easy_checkout/main/after',  'extension/aw_ecommerce_tracking/event/controllerEasyCheckoutAfter'],
            ['aw_et_success',        'catalog/view/common/success/after',              'extension/aw_ecommerce_tracking/event/viewSuccessAfter'],

            // Account — login/signup tracking via controller events
            ['aw_et_login',          'catalog/controller/account/login/after',         'extension/aw_ecommerce_tracking/event/controllerLoginAfter'],
            ['aw_et_register',       'catalog/controller/account/register/after',      'extension/aw_ecommerce_tracking/event/controllerRegisterAfter'],

            // Product modules — view_item_list events
            ['aw_et_mod_featured',   'catalog/view/extension/module/featured/after',   'extension/aw_ecommerce_tracking/event/viewModuleFeaturedAfter'],
            ['aw_et_mod_latest',     'catalog/view/extension/module/latest/after',     'extension/aw_ecommerce_tracking/event/viewModuleLatestAfter'],
            ['aw_et_mod_bestseller', 'catalog/view/extension/module/bestseller/after', 'extension/aw_ecommerce_tracking/event/viewModuleBestsellerAfter'],
            ['aw_et_mod_special',    'catalog/view/extension/module/special/after',    'extension/aw_ecommerce_tracking/event/viewModuleSpecialAfter'],
        ];

        foreach ($events as $event) {
            $this->model_setting_event->deleteEventByCode($event[0]);
            $this->model_setting_event->addEvent($event[0], $event[1], $event[2]);
        }
    }

    private function uninstallEvents(): void
    {
        $this->load->model('setting/event');

        $codes = [
            'aw_et_header',
            'aw_et_footer',
            'aw_et_category',
            'aw_et_search',
            'aw_et_manufacturer',
            'aw_et_special',
            'aw_et_product',
            'aw_et_cart',
            'aw_et_checkout',
            'aw_et_ec_main',
            'aw_et_success',
            'aw_et_login',
            'aw_et_register',
            'aw_et_mod_featured',
            'aw_et_mod_latest',
            'aw_et_mod_bestseller',
            'aw_et_mod_special',
            'aw_ecommerce_tracking', // legacy cleanup
        ];

        foreach ($codes as $code) {
            $this->model_setting_event->deleteEventByCode($code);
        }
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
