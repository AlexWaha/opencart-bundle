<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwMicrodata extends Controller
{
    private string $moduleName = 'aw_microdata';
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
        $this->params['module_name'] = $this->moduleName;

        $this->routeExtension = $this->awCore->isLegacy()
            ? 'extension/extension'
            : 'marketplace/extension';
    }

    public function index(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();
        $this->document->addScript('view/javascript/jquery/datetimepicker/moment/moment.min.js');
        $this->document->addScript('view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
        $this->document->addStyle('view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

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

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('tool/image');
        $imageUrl = $this->request->server['HTTPS'] ? HTTPS_CATALOG . 'image/' : HTTP_CATALOG . 'image/';
        $this->params['image_url'] = $imageUrl;
        $this->params['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        $this->params['status'] = $this->moduleConfig->get('status', false);
        $this->params['syntax'] = $this->moduleConfig->get('syntax', 'ld');
        $this->params['website_name'] = $this->moduleConfig->get('website_name', []);
        $this->params['website_alt_name'] = $this->moduleConfig->get('website_alt_name', []);
        $this->params['site_search_url'] = $this->moduleConfig->get('site_search_url', '');
        $this->params['default_image'] = $this->moduleConfig->get('default_image', '');
        $this->params['default_image_thumb'] = $this->params['default_image']
            ? $this->model_tool_image->resize($this->params['default_image'], 100, 100)
            : $this->params['placeholder'];

        $this->params['store_type'] = $this->moduleConfig->get('store_type', 'Store');
        $this->params['legal_name'] = $this->moduleConfig->get('legal_name', []);
        $this->params['email'] = $this->moduleConfig->get('email', '');
        $this->params['phones'] = $this->moduleConfig->get('phones', []);
        $socialRaw = $this->moduleConfig->get('social', []);
        $socialNormalized = [];

        if (is_array($socialRaw)) {
            foreach ($socialRaw as $item) {
                $socialNormalized[] = is_array($item) ? ($item['url'] ?? '') : (string)$item;
            }
        }

        $this->params['social'] = array_values(array_filter($socialNormalized));
        $this->params['address'] = $this->moduleConfig->get('address', []);
        $this->params['geo'] = $this->moduleConfig->get('geo', ['lat' => '', 'lon' => '']);
        $this->params['logo'] = $this->moduleConfig->get('logo', '');
        $this->params['logo_thumb'] = $this->params['logo']
            ? $this->model_tool_image->resize($this->params['logo'], 100, 100)
            : $this->params['placeholder'];
        $this->params['schedule'] = $this->moduleConfig->get('schedule', $this->getDefaultSchedule());
        $this->params['currency'] = $this->moduleConfig->get('currency', $this->config->get('config_currency'));
        $this->params['price_range_value'] = $this->moduleConfig->get('price_range_value', '$$');
        $this->params['payment_methods'] = $this->moduleConfig->get('payment_methods', []);
        $this->params['delivery_areas'] = $this->moduleConfig->get('delivery_areas', []);

        $this->params['product_schema'] = $this->moduleConfig->get('product_schema', true);
        $this->params['review_source'] = $this->moduleConfig->get('review_source', 'both');
        $this->params['min_reviews'] = $this->moduleConfig->get('min_reviews', 1);
        $this->params['min_rating'] = $this->moduleConfig->get('min_rating', 1);
        $this->params['unit_code'] = $this->moduleConfig->get('unit_code', '');
        $this->params['unit_quantity'] = $this->moduleConfig->get('unit_quantity', 1);
        $this->params['availability_map'] = $this->moduleConfig->get('availability_map', []);
        $this->params['condition'] = $this->moduleConfig->get('condition', 'NewCondition');
        $this->params['brand_source'] = $this->moduleConfig->get('brand_source', 'manufacturer');
        $this->params['delivery_lead'] = $this->moduleConfig->get('delivery_lead', false);
        $this->params['delivery_min'] = $this->moduleConfig->get('delivery_min', 1);
        $this->params['delivery_max'] = $this->moduleConfig->get('delivery_max', 3);
        $this->params['return_policy'] = $this->moduleConfig->get('return_policy', false);
        $this->params['return_days'] = $this->moduleConfig->get('return_days', 14);
        $this->params['return_type'] = $this->moduleConfig->get('return_type', 'MerchantReturnFiniteReturnWindow');
        $this->params['shipping_details'] = $this->moduleConfig->get('shipping_details', false);
        $this->params['include_weight'] = $this->moduleConfig->get('include_weight', false);
        $this->params['include_dimensions'] = $this->moduleConfig->get('include_dimensions', false);
        $this->params['all_images'] = $this->moduleConfig->get('all_images', true);
        $this->params['attributes'] = $this->moduleConfig->get('attributes', true);
        $this->params['product_rating'] = $this->moduleConfig->get('product_rating', true);
        $this->params['unit_price'] = $this->moduleConfig->get('unit_price', false);

        $this->params['search_schema'] = $this->moduleConfig->get('search_schema', true);
        $this->params['manufacturer_schema'] = $this->moduleConfig->get('manufacturer_schema', true);
        $this->params['special_schema'] = $this->moduleConfig->get('special_schema', true);
        $this->params['homepage_products'] = $this->moduleConfig->get('homepage_products', false);
        $this->params['listing_delivery'] = $this->moduleConfig->get('listing_delivery', false);
        $this->params['listing_return_policy'] = $this->moduleConfig->get('listing_return_policy', false);

        $this->params['category_schema'] = $this->moduleConfig->get('category_schema', true);
        $this->params['category_type'] = $this->moduleConfig->get('category_type', 'CollectionPage');
        $this->params['price_range'] = $this->moduleConfig->get('price_range', true);
        $this->params['product_count'] = $this->moduleConfig->get('product_count', true);
        $this->params['category_rating'] = $this->moduleConfig->get('category_rating', false);
        $this->params['landing_local'] = $this->moduleConfig->get('landing_local', false);
        $this->params['area_served'] = $this->moduleConfig->get('area_served', false);

        $this->params['info_schema'] = $this->moduleConfig->get('info_schema', true);
        $this->params['info_type'] = $this->moduleConfig->get('info_type', 'Article');
        $this->params['author_name'] = $this->moduleConfig->get('author_name', '');
        $this->params['author_url'] = $this->moduleConfig->get('author_url', '');
        $this->params['blog_schema'] = $this->moduleConfig->get('blog_schema', false);
        $this->params['blog_type'] = $this->moduleConfig->get('blog_type', 'BlogPosting');
        $this->params['word_count'] = $this->moduleConfig->get('word_count', false);
        $this->params['faq_schema'] = $this->moduleConfig->get('faq_schema', false);
        $this->params['reviews_schema'] = $this->moduleConfig->get('reviews_schema', false);
        $this->params['calculator_schema'] = $this->moduleConfig->get('calculator_schema', false);
        $this->params['calculator_howto'] = $this->moduleConfig->get('calculator_howto', false);
        $this->params['contact_schema'] = $this->moduleConfig->get('contact_schema', false);

        $this->params['og_enabled'] = $this->moduleConfig->get('og_enabled', false);
        $this->params['og_type'] = $this->moduleConfig->get('og_type', 'website');
        $this->params['fb_app_id'] = $this->moduleConfig->get('fb_app_id', '');
        $this->params['fb_pages'] = $this->moduleConfig->get('fb_pages', '');
        $this->params['twitter_card'] = $this->moduleConfig->get('twitter_card', 'summary_large_image');
        $this->params['twitter_username'] = $this->moduleConfig->get('twitter_username', '');
        $this->params['og_image'] = $this->moduleConfig->get('og_image', '');
        $this->params['og_image_thumb'] = $this->params['og_image']
            ? $this->model_tool_image->resize($this->params['og_image'], 100, 100)
            : $this->params['placeholder'];

        $this->params['global_rating'] = $this->moduleConfig->get('global_rating', false);
        $this->params['fake_count'] = $this->moduleConfig->get('fake_count', 0);
        $this->params['fake_boost'] = $this->moduleConfig->get('fake_boost', 0);
        $this->params['force_instock'] = $this->moduleConfig->get('force_instock', false);
        $this->params['competitor_sameas'] = $this->moduleConfig->get('competitor_sameas', []);
        $this->params['custom_jsonld'] = $this->moduleConfig->get('custom_jsonld', '');
        $this->params['speakable'] = $this->moduleConfig->get('speakable', false);
        $this->params['video_object'] = $this->moduleConfig->get('video_object', false);

        $this->load->model('localisation/stock_status');
        $this->params['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

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

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/main', $this->params));
    }

    public function diagnostics(): void
    {
        $json = [];

        $this->load->model('extension/module/' . $this->moduleName);

        $expectedTriggers = [
            'catalog/view/common/header/after',
            'catalog/view/common/footer/after',
            'catalog/view/common/home/after',
            'catalog/view/product/product/after',
            'catalog/view/product/category/after',
            'catalog/view/product/search/after',
            'catalog/view/product/manufacturer_info/after',
            'catalog/view/product/special/after',
            'catalog/view/information/information/after',
            'catalog/view/information/contact/after',
            'catalog/view/blog/article/after',
            'catalog/view/blog/category/after',
        ];

        $eventRows = $this->model_extension_module_aw_microdata->getRegisteredEvents('aw_microdata_');

        $registeredTriggers = [];

        foreach ($eventRows as $row) {
            $registeredTriggers[$row['trigger']] = (int)($row['status'] ?? 1);
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

        $duplicateFiles = [];
        $themePath = DIR_CATALOG . 'view/theme/';
        $themeDir = is_dir($themePath) ? $themePath : '';

        if ($themeDir) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($themeDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'twig') {
                    continue;
                }

                $content = file_get_contents($file->getPathname());

                if (preg_match('/<script\s+type=["\']application\/ld\+json["\']/', $content)) {
                    if (!preg_match('/\{\{.*aw_microdata.*\}\}/', $content)) {
                        $relativePath = str_replace(
                            str_replace('/', DIRECTORY_SEPARATOR, DIR_CATALOG),
                            'catalog/',
                            $file->getPathname()
                        );
                        $duplicateFiles[] = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
                    }
                }
            }
        }

        $json['duplicates'] = [
            'status' => empty($duplicateFiles) ? 'ok' : 'warning',
            'files'  => $duplicateFiles,
        ];

        $missing = [];
        $logo = $this->moduleConfig->get('logo', '');

        if (empty($logo)) {
            $missing[] = ['field' => 'logo', 'tab' => 'organization'];
        }

        $phones = $this->moduleConfig->get('phones', []);

        if (!is_array($phones) || empty($phones)) {
            $missing[] = ['field' => 'phones', 'tab' => 'organization'];
        }

        $address = $this->moduleConfig->get('address', []);
        $hasAddress = false;

        if (is_array($address)) {
            foreach ($address as $langAddress) {
                if (is_array($langAddress) && (!empty($langAddress['street']) || !empty($langAddress['city']))) {
                    $hasAddress = true;
                    break;
                }
            }
        }

        if (!$hasAddress) {
            $missing[] = ['field' => 'address', 'tab' => 'organization'];
        }

        $email = $this->moduleConfig->get('email', '');

        if (empty($email)) {
            $missing[] = ['field' => 'email', 'tab' => 'organization'];
        }

        $json['config'] = [
            'status'  => empty($missing) ? 'ok' : 'warning',
            'missing' => $missing,
        ];

        $shopUrl = defined('HTTP_CATALOG') ? HTTP_CATALOG : $this->config->get('config_url');
        $shopUrl = rtrim($shopUrl, '/');

        $validationUrls = [];

        $validationUrls[] = [
            'name'       => 'Homepage',
            'url'        => $shopUrl . '/',
            'google_url' => 'https://search.google.com/test/rich-results?url=' . urlencode($shopUrl . '/'),
        ];

        $firstProductId = $this->model_extension_module_aw_microdata->getFirstProductId();

        if ($firstProductId) {
            $productUrl = $shopUrl . '/index.php?route=product/product&product_id=' . $firstProductId;
            $validationUrls[] = [
                'name'       => 'Product #' . $firstProductId,
                'url'        => $productUrl,
                'google_url' => 'https://search.google.com/test/rich-results?url=' . urlencode($productUrl),
            ];
        }

        $firstCategoryId = $this->model_extension_module_aw_microdata->getFirstCategoryId();

        if ($firstCategoryId) {
            $categoryUrl = $shopUrl . '/index.php?route=product/category&path=' . $firstCategoryId;
            $validationUrls[] = [
                'name'       => 'Category #' . $firstCategoryId,
                'url'        => $categoryUrl,
                'google_url' => 'https://search.google.com/test/rich-results?url=' . urlencode($categoryUrl),
            ];
        }

        $contactUrl = $shopUrl . '/index.php?route=information/contact';
        $validationUrls[] = [
            'name'       => 'Contact',
            'url'        => $contactUrl,
            'google_url' => 'https://search.google.com/test/rich-results?url=' . urlencode($contactUrl),
        ];

        $json['validation_urls'] = $validationUrls;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function store(): void
    {
        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
            $data = $this->request->post;

            if (isset($data['phones']) && is_array($data['phones'])) {
                $data['phones'] = array_values($data['phones']);
            }

            if (isset($data['social']) && is_array($data['social'])) {
                $data['social'] = array_values(array_filter($data['social'], function ($v) {
                    return !empty(trim($v));
                }));
            }

            if (isset($data['schedule']) && is_array($data['schedule'])) {
                $data['schedule'] = array_values($data['schedule']);
            }

            if (isset($data['competitor_sameas']) && is_array($data['competitor_sameas'])) {
                $data['competitor_sameas'] = array_values(array_filter($data['competitor_sameas'], function ($v) {
                    return !empty(trim($v));
                }));
            }

            $this->awCore->setConfig($this->moduleName, $data);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                'extension/module/' . $this->moduleName,
                $this->tokenData['param'],
                true
            ));
        }

        $this->index();
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

    private function validate(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
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
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);
        $this->awCore->removeConfig($this->moduleName);
        $this->uninstallEvents();
    }

    private function installEvents(): void
    {
        $this->load->model('setting/event');

        $events = [
            ['aw_microdata_og_header',     'catalog/view/common/header/after',           'extension/aw_microdata/event/viewHeaderAfter'],
            ['aw_microdata_org_footer',    'catalog/view/common/footer/after',           'extension/aw_microdata/event/viewFooterAfter'],
            ['aw_microdata_website_home',  'catalog/view/common/home/after',             'extension/aw_microdata/event/viewHomeAfter'],
            ['aw_microdata_product',       'catalog/view/product/product/after',         'extension/aw_microdata/event/viewProductAfter'],
            ['aw_microdata_category',      'catalog/view/product/category/after',        'extension/aw_microdata/event/viewCategoryAfter'],
            ['aw_microdata_information',   'catalog/view/information/information/after', 'extension/aw_microdata/event/viewInformationAfter'],
            ['aw_microdata_contact',       'catalog/view/information/contact/after',     'extension/aw_microdata/event/viewContactAfter'],
            ['aw_microdata_search',        'catalog/view/product/search/after',            'extension/aw_microdata/event/viewSearchAfter'],
            ['aw_microdata_manufacturer',  'catalog/view/product/manufacturer_info/after','extension/aw_microdata/event/viewManufacturerAfter'],
            ['aw_microdata_special',       'catalog/view/product/special/after',          'extension/aw_microdata/event/viewSpecialAfter'],
            ['aw_microdata_blog_article',  'catalog/view/blog/article/after',            'extension/aw_microdata/event/viewBlogArticleAfter'],
            ['aw_microdata_blog_category', 'catalog/view/blog/category/after',           'extension/aw_microdata/event/viewBlogCategoryAfter'],
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
            'aw_microdata_og_header',
            'aw_microdata_org_footer',
            'aw_microdata_website_home',
            'aw_microdata_product',
            'aw_microdata_category',
            'aw_microdata_information',
            'aw_microdata_contact',
            'aw_microdata_search',
            'aw_microdata_manufacturer',
            'aw_microdata_special',
            'aw_microdata_blog_article',
            'aw_microdata_blog_category',
            'aw_microdata', // legacy cleanup
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

    private function getDefaultSchedule(): array
    {
        return [
            ['day' => 'Mo', 'open' => '09:00', 'close' => '20:00', 'closed' => false],
            ['day' => 'Tu', 'open' => '09:00', 'close' => '20:00', 'closed' => false],
            ['day' => 'We', 'open' => '09:00', 'close' => '20:00', 'closed' => false],
            ['day' => 'Th', 'open' => '09:00', 'close' => '20:00', 'closed' => false],
            ['day' => 'Fr', 'open' => '09:00', 'close' => '20:00', 'closed' => false],
            ['day' => 'Sa', 'open' => '10:00', 'close' => '18:00', 'closed' => false],
            ['day' => 'Su', 'open' => '10:00', 'close' => '18:00', 'closed' => false],
        ];
    }
}
