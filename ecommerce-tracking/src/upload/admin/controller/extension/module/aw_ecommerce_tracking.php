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

        if ($this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->load->model('extension/module/' . $this->moduleName);
            $this->{'model_extension_module_' . $this->moduleName}->install();
            $this->repairEvents();
        }

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

        $this->params['ga4MeasurementId'] = $this->moduleConfig->get('ga4_measurement_id', '');
        $this->params['ga4ApiSecretSet'] = ($this->moduleConfig->all()['ga4_api_secret'] ?? '') !== '';
        $this->params['ga4MpDebug'] = $this->moduleConfig->get('ga4_mp_debug', false);
        $this->params['adsConversionId'] = $this->moduleConfig->get('ads_conversion_id', '');
        $this->params['adsConversionLabel'] = $this->moduleConfig->get('ads_conversion_label', '');

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
            $data = $this->request->post;

            $newSecret = trim((string) ($data['ga4_api_secret'] ?? ''));
            $data['ga4_api_secret'] = $newSecret !== '' ? $newSecret : (string) ($this->moduleConfig->all()['ga4_api_secret'] ?? '');

            $this->awCore->setConfig($this->moduleName, $data);

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
            'catalog/controller/checkout/success/before',
            'catalog/model/checkout/order/addOrder/after',
            'catalog/model/checkout/order/addOrderHistory/after',
            'catalog/controller/account/login/after',
            'catalog/controller/account/register/after',
            'catalog/view/extension/module/featured/after',
            'catalog/view/extension/module/latest/after',
            'catalog/view/extension/module/bestseller/after',
            'catalog/view/extension/module/special/after',
        ];

        $expectedTriggers = array_map([$this, 'legacyViewTrigger'], $expectedTriggers);

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
            $data = $this->moduleConfig->all();
            unset($data['ga4_api_secret']);

            $exportData = [
                'module' => $this->moduleName,
                'export_date' => date('Y-m-d H:i:s'),
                'data' => $data,
            ];

            $jsonData = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

                    $importData = json_decode($fileContent, true);

                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($importData)) {
                        throw new Exception($this->language->get('error_warning'));
                    }

                    if (($importData['module'] ?? null) !== $this->moduleName) {
                        throw new Exception($this->language->get('error_import_wrong_module'));
                    }

                    if (!isset($importData['data']) || !is_array($importData['data']) || !$this->validateImportData($importData['data'])) {
                        throw new Exception($this->language->get('error_warning'));
                    }

                    $importedSecret = trim((string) ($importData['data']['ga4_api_secret'] ?? ''));
                    $importData['data']['ga4_api_secret'] = $importedSecret !== '' ? $importedSecret : (string) ($this->moduleConfig->all()['ga4_api_secret'] ?? '');

                    $this->awCore->setConfig($this->moduleName, $importData['data']);
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

    private function validateImportData(array $data): bool
    {
        if (isset($data['custom_dimensions']) && $data['custom_dimensions'] !== '') {
            json_decode((string) $data['custom_dimensions'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
        }

        return $this->validateTrackingFields($data);
    }

    private function validateTrackingFields(array $data): bool
    {
        $measurementId = trim((string) ($data['ga4_measurement_id'] ?? ''));
        $adsConversionId = trim((string) ($data['ads_conversion_id'] ?? ''));
        $adsConversionLabel = trim((string) ($data['ads_conversion_label'] ?? ''));

        if ($measurementId !== '' && !preg_match('/^G-[A-Za-z0-9]+$/', $measurementId)) {
            return false;
        }

        if ($adsConversionId !== '' && !preg_match('/^AW-[A-Za-z0-9]+$/', $adsConversionId)) {
            return false;
        }

        if ($adsConversionLabel !== '' && !preg_match('/^[A-Za-z0-9_-]+$/', $adsConversionLabel)) {
            return false;
        }

        return true;
    }

    protected function validate(): bool
    {
        $this->load->language('extension/module/' . $this->moduleName);

        if (!$this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error && $this->request->server['REQUEST_METHOD'] === 'POST' && !$this->validateTrackingFields($this->request->post)) {
            $this->error['warning'] = $this->language->get('error_tracking_fields');
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

        $this->load->model('extension/module/' . $this->moduleName);
        $this->{'model_extension_module_' . $this->moduleName}->install();

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

    private function eventDefinitions(): array
    {
        return [
            ['aw_et_header',         'catalog/view/common/header/after',                'extension/aw_ecommerce_tracking/event/viewHeaderAfter'],
            ['aw_et_footer',         'catalog/view/common/footer/after',                'extension/aw_ecommerce_tracking/event/viewFooterAfter'],

            ['aw_et_category',       'catalog/view/product/category/after',             'extension/aw_ecommerce_tracking/event/viewCategoryAfter'],
            ['aw_et_search',         'catalog/view/product/search/after',               'extension/aw_ecommerce_tracking/event/viewSearchAfter'],
            ['aw_et_manufacturer',   'catalog/view/product/manufacturer_info/after',    'extension/aw_ecommerce_tracking/event/viewManufacturerAfter'],
            ['aw_et_special',        'catalog/view/product/special/after',              'extension/aw_ecommerce_tracking/event/viewSpecialAfter'],
            ['aw_et_product',        'catalog/view/product/product/after',              'extension/aw_ecommerce_tracking/event/viewProductAfter'],

            ['aw_et_cart',           'catalog/view/checkout/cart/after',                'extension/aw_ecommerce_tracking/event/viewCartAfter'],
            ['aw_et_checkout',       'catalog/view/checkout/checkout/after',            'extension/aw_ecommerce_tracking/event/viewCheckoutAfter'],
            ['aw_et_ec_main',        'catalog/controller/extension/aw_easy_checkout/main/after',  'extension/aw_ecommerce_tracking/event/controllerEasyCheckoutAfter'],
            ['aw_et_success',        'catalog/view/common/success/after',              'extension/aw_ecommerce_tracking/event/viewSuccessAfter'],
            ['aw_et_success_before', 'catalog/controller/checkout/success/before',     'extension/aw_ecommerce_tracking/event/successBefore'],
            ['aw_et_capture_ids',    'catalog/model/checkout/order/addOrder/after',    'extension/aw_ecommerce_tracking/event/addOrderAfter'],
            ['aw_et_history',        'catalog/model/checkout/order/addOrderHistory/after', 'extension/aw_ecommerce_tracking/event/addOrderHistoryAfter'],

            ['aw_et_login',          'catalog/controller/account/login/after',         'extension/aw_ecommerce_tracking/event/controllerLoginAfter'],
            ['aw_et_register',       'catalog/controller/account/register/after',      'extension/aw_ecommerce_tracking/event/controllerRegisterAfter'],

            ['aw_et_mod_featured',   'catalog/view/extension/module/featured/after',   'extension/aw_ecommerce_tracking/event/viewModuleFeaturedAfter'],
            ['aw_et_mod_latest',     'catalog/view/extension/module/latest/after',     'extension/aw_ecommerce_tracking/event/viewModuleLatestAfter'],
            ['aw_et_mod_bestseller', 'catalog/view/extension/module/bestseller/after', 'extension/aw_ecommerce_tracking/event/viewModuleBestsellerAfter'],
            ['aw_et_mod_special',    'catalog/view/extension/module/special/after',    'extension/aw_ecommerce_tracking/event/viewModuleSpecialAfter'],
        ];
    }

    private function loadEventModel(): string
    {
        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/event');

            return 'model_extension_event';
        }

        $this->load->model('setting/event');

        return 'model_setting_event';
    }

    private function deleteEventByCode(string $model, string $code): void
    {
        if ($this->awCore->isLegacy()) {
            $this->{$model}->deleteEvent($code);
        } else {
            $this->{$model}->deleteEventByCode($code);
        }
    }

    private function installEvents(): void
    {
        $model = $this->loadEventModel();

        foreach ($this->eventDefinitions() as $event) {
            $this->deleteEventByCode($model, $event[0]);
            $this->{$model}->addEvent($event[0], $this->legacyViewTrigger($event[1]), $event[2]);
        }
    }

    private function repairEvents(): void
    {
        $this->load->model('extension/module/' . $this->moduleName);
        $registered = [];

        foreach ($this->{'model_extension_module_' . $this->moduleName}->getRegisteredEvents('aw_et_') as $row) {
            $registered[$row['code']] = $row['trigger'];
        }

        $model = null;

        foreach ($this->eventDefinitions() as $event) {
            [$code, $trigger, $action] = $event;
            $trigger = $this->legacyViewTrigger($trigger);

            if (isset($registered[$code]) && $registered[$code] === $trigger) {
                continue;
            }

            if ($model === null) {
                $model = $this->loadEventModel();
            }

            $this->deleteEventByCode($model, $code);
            $this->{$model}->addEvent($code, $trigger, $action);
        }
    }

    private function legacyViewTrigger(string $trigger): string
    {
        if ($this->awCore->isLegacy() && preg_match('#^catalog/view/(.+)/after$#', $trigger, $matches)) {
            return 'catalog/view/*/template/' . $matches[1] . '/after';
        }

        return $trigger;
    }

    private function uninstallEvents(): void
    {
        $model = $this->loadEventModel();

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
            'aw_et_success_before',
            'aw_et_capture_ids',
            'aw_et_history',
            'aw_et_login',
            'aw_et_register',
            'aw_et_mod_featured',
            'aw_et_mod_latest',
            'aw_et_mod_bestseller',
            'aw_et_mod_special',
            'aw_ecommerce_tracking', // legacy cleanup
        ];

        foreach ($codes as $code) {
            $this->deleteEventByCode($model, $code);
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
