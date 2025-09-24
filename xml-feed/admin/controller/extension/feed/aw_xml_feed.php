<?php

/**
 * Age Verification Module
 *
 * @author Alexander Vakhovski (AlexWaha)
 *
 * @link https://alexwaha.com
 *
 * @email support@alexwaha.com
 *
 * @license GPLv3
 */
class ControllerExtensionFeedAwXmlFeed extends Controller
{
    private string $moduleName = 'aw_xml_feed';

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
        $this->params = $this->language->load('extension/feed/' . $this->moduleName);
        $this->params['token'] = $this->tokenData['token'];
        $this->params['token_param'] = $this->tokenData['param'];

        $this->routeExtension = $this->awCore->isLegacy() ? 'extension/extension' : 'marketplace/extension';
    }

    public function index()
    {
        $this->document->setTitle($this->language->get('heading_main_title'));

        $this->params['success'] = $this->session->data['success'] ?? '';

        $this->params['error'] = $this->error;

        $this->params['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true),
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=feed', true),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/feed/' . $this->moduleName, $this->tokenData['param'], true),
            ],
        ];

        $this->params['action'] = $this->url->link(
            'extension/feed/' . $this->moduleName . '/store',
            $this->tokenData['param'],
            true
        );

        $this->params['create'] = $this->url->link(
            'extension/feed/' . $this->moduleName . '/getForm',
            $this->tokenData['param'],
            true
        );

        $this->params['cancel'] = $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=feed', true);

        $this->params['status'] = $this->moduleConfig->get('status', false);
        $this->params['folder'] = $this->moduleConfig->get('folder', 'xml-feed');
        $this->params['batch_size'] = $this->moduleConfig->get('batch_size', 250);
        $this->params['access_key'] = $this->moduleConfig->get('access_key', $this->generateAccessKey());
        $this->params['shop_name'] = $this->moduleConfig->get('shop_name', []);
        $this->params['company_name'] = $this->moduleConfig->get('company_name', []);
        $this->params['shop_description'] = $this->moduleConfig->get('shop_description', []);
        $this->params['shop_country'] = $this->moduleConfig->get('shop_country', $this->config->get('config_country_id'));
        $this->params['delivery_service'] = $this->moduleConfig->get('delivery_service', []);
        $this->params['stock_status_available'] = $this->moduleConfig->get('stock_status_available', []);

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('localisation/country');
        $this->params['countries'] = $this->model_localisation_country->getCountries();

        $this->load->model('localisation/stock_status');
        $this->params['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

        $this->load->model('extension/feed/' . $this->moduleName);

        $this->params['feeds'] = [];
        $feeds = $this->model_extension_feed_aw_xml_feed->getFeeds();

        $templates = [
            'google' => $this->language->get('template_google'),
            'facebook' => $this->language->get('template_facebook'),
            'hotline' => $this->language->get('template_hotline'),
            'prom' => $this->language->get('template_prom'),
            'yml' => $this->language->get('template_yml'),
        ];

        foreach ($feeds as $feed) {
            $xmlFile = mb_strtolower(preg_replace('/\s+/', '', $feed['filename']), 'UTF-8') . '.xml';
            $xmlUrl = HTTPS_CATALOG . $this->moduleConfig->get('folder') . '/' . $xmlFile;

            $this->params['feeds'][] = [
                'feed_id' => $feed['feed_id'],
                'name' => $feed['name'],
                'template' => $templates[$feed['template']],
                'filename' => $feed['filename'] . '.xml',
                'url' => $xmlUrl,
                'status' => $feed['status'],
                'edit' => $this->url->link(
                    'extension/feed/' . $this->moduleName . '/getForm',
                    $this->tokenData['param'] . '&feed_id=' . $feed['feed_id'],
                    true
                ),
            ];
        }

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/feed/' . $this->moduleName, $this->params));
    }

    public function store()
    {
        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->awCore->setConfig($this->moduleName, $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                $this->routeExtension,
                $this->tokenData['param'] . '&type=feed',
                true
            ));
        }

        $this->index();
    }

    public function getForm($postData = [])
    {
        $this->load->model('extension/feed/' . $this->moduleName);

        $feedId = (int) ($this->request->get['feed_id'] ?? 0);

        $feed = [];
        $feedConfig = new \Alexwaha\Config([]);

        if ($feedId) {
            $feed = $this->model_extension_feed_aw_xml_feed->getFeed($feedId);
            $feedConfig = $this->awCore->getConfig($this->moduleName . '_feed_id_' . $feed['feed_id']);
        }

        $this->params['name'] = $postData['name'] ?? $feed['name'] ?? '';
        $this->params['filename'] = $postData['filename'] ?? $feed['filename'] ?? 'product-feed';
        $this->params['template_code'] = $postData['template'] ?? $feed['template'] ?? '';
        $this->params['language_id'] = (int) ($postData['language_id'] ?? $feed['language_id'] ?? $this->config->get('config_language_id'));
        $this->params['currency_code'] = ($postData['currency_code'] ?? $feed['currency_code'] ?? $this->config->get('config_currency'));
        $this->params['image_origin'] = $postData['image_origin'] ?? $feed['image_origin'] ?? false;
        $this->params['image_count'] = (int) ($postData['image_count'] ?? $feed['image_count'] ?? 8);
        $this->params['status'] = (int) ($postData['status'] ?? $feed['status'] ?? false);
        $this->params['config'] = $feedConfig;

        $this->params['image_width'] = $feedConfig->get('image_width', $postData['setting']['image_width'] ?? '800');
        $this->params['image_height'] = $feedConfig->get('image_height', $postData['setting']['image_height'] ?? '800');
        $this->params['category_list'] = $feedConfig->get('category_list', $postData['setting']['category_list'] ?? []);
        $this->params['category_related'] = $feedConfig->get(
            'category_related',
            $postData['setting']['category_related'] ?? []
        );
        $this->params['category_related_ids'] = $feedConfig->get(
            'category_related_ids',
            $postData['setting']['category_related_ids'] ?? []
        );
        $this->params['brand_list'] = $feedConfig->get('brand_list', $postData['setting']['brand_list'] ?? []);
        $this->params['attribute_list'] = $feedConfig->get(
            'attribute_list',
            $postData['setting']['attribute_list'] ?? []
        );
        $this->params['attribute_shipping'] = $feedConfig->get(
            'attribute_shipping',
            $postData['setting']['attribute_shipping'] ?? 0
        );
        $this->params['attribute_warranty'] = $feedConfig->get(
            'attribute_warranty',
            $postData['setting']['attribute_warranty'] ?? 0
        );
        $this->params['option_list'] = $feedConfig->get('option_list', $postData['setting']['option_list'] ?? []);
        $this->params['option_color'] = $feedConfig->get('option_color', $postData['setting']['option_color'] ?? 0);
        $this->params['option_size'] = $feedConfig->get('option_size', $postData['setting']['option_size'] ?? 0);
        $this->params['shipping_text'] = $feedConfig->get('shipping_text', $postData['setting']['shipping_text'] ?? []);
        $this->params['warranty_text'] = $feedConfig->get('warranty_text', $postData['setting']['warranty_text'] ?? []);

        $this->params['action'] = $this->url->link(
            'extension/feed/' . $this->moduleName . '/storeFeed',
            $this->tokenData['param'] . '&feed_id=' . $feedId,
            true
        );

        $this->params['cancel'] = $this->url->link(
            'extension/feed/' . $this->moduleName,
            $this->tokenData['param'] . '&type=feed',
            true
        );

        $this->params['templates'] = [
            'google' => $this->language->get('template_google'),
            'facebook' => $this->language->get('template_facebook'),
            'hotline' => $this->language->get('template_hotline'),
            'prom' => $this->language->get('template_prom'),
            'yml' => $this->language->get('template_yml'),
        ];

        $this->load->model('localisation/currency');
        $this->params['currencies'] = $this->model_localisation_currency->getCurrencies();

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('catalog/category');
        $this->params['categories'] = [];

        $this->params['categories'] = $this->model_catalog_category->getCategories(0);
        $this->load->model('catalog/manufacturer');

        $this->params['manufacturers'] = [];
        $this->params['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();

        $this->load->model('catalog/attribute');
        $this->params['attributes'] = [];

        $results = $this->model_catalog_attribute->getAttributes();

        foreach ($results as $result) {
            $this->params['attributes'][] = [
                'attribute_id' => $result['attribute_id'],
                'name' => $result['name'],
                'attribute_group' => $result['attribute_group'],
            ];
        }

        $this->load->model('catalog/option');
        $this->params['options'] = [];

        $results = $this->model_catalog_option->getOptions();

        foreach ($results as $result) {
            $this->params['options'][] = [
                'option_id' => $result['option_id'],
                'name' => $result['name'],
            ];
        }

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/feed/' . $this->moduleName . '_form', $this->params));
    }

    public function storeFeed()
    {
        $this->load->model('extension/feed/' . $this->moduleName);

        $feedId = (int) ($this->request->get['feed_id'] ?? 0);

        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateFeed()) {
            $postData = [
                'name' => $this->request->post['name'],
                'filename' => $this->request->post['filename'],
                'template' => $this->request->post['template'],
                'language_id' => $this->request->post['language_id'],
                'currency_code' => $this->request->post['currency_code'],
                'image_origin' => $this->request->post['image_origin'],
                'image_count' => $this->request->post['image_count'],
                'status' => $this->request->post['status'],
            ];

            if ($feedId) {
                $this->model_extension_feed_aw_xml_feed->editFeed($feedId, $postData);
            } else {
                $feedId = $this->model_extension_feed_aw_xml_feed->addFeed($postData);
            }

            $this->awCore->setConfig($this->moduleName . '_feed_id_' . $feedId, $this->request->post['setting']);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                'extension/feed/' . $this->moduleName,
                $this->tokenData['param'],
                true
            ));
        }

        $this->getForm($this->request->post);
    }

    public function delete()
    {
        $this->load->model('extension/feed/' . $this->moduleName);

        $json = [];

        $feedId = 0;

        if (isset($this->request->post['feed_id'])) {
            $feedId = (int) $this->request->post['feed_id'];
        }

        if ($feedId && $this->validate()) {
            $this->model_extension_feed_aw_xml_feed->deleteFeed($feedId);
            $this->awCore->removeConfig($this->moduleName . '_feed_id_' . $feedId);

            $json['success'] = $this->language->get('text_success_delete');
        } else {
            $json['error'] = $this->error;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function validate(): bool
    {
        if (! $this->user->hasPermission('modify', 'extension/feed/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['folder'])) {
            if ((utf8_strlen(trim($this->request->post['folder'])) < 3) || (utf8_strlen(trim($this->request->post['folder'])) > 64)) {
                $this->error['folder'] = $this->language->get('error_folder');
            }
        }

        if (isset($this->request->post['batch_size'])) {
            if ((int) $this->request->post['batch_size'] > 1000) {
                $this->error['batch_size'] = $this->language->get('error_batch_size');
            }
        }

        if (isset($this->request->post['access_key'])) {
            $accessKey = trim($this->request->post['access_key']);
            if (empty($accessKey) || utf8_strlen($accessKey) < 8) {
                $this->error['access_key'] = $this->language->get('error_access_key');
            }
        }

        if (isset($this->request->post['shop_name'])) {
            foreach ($this->request->post['shop_name'] as $languageId => $value) {
                if ((utf8_strlen(trim($value)) < 3) || (utf8_strlen(trim($value)) > 255)) {
                    $this->error['shop_name'][$languageId] = $this->language->get('error_shop_name');
                }
            }
        }

        if (isset($this->request->post['company_name'])) {
            foreach ($this->request->post['company_name'] as $languageId => $value) {
                if ((utf8_strlen(trim($value)) < 3) || (utf8_strlen(trim($value)) > 255)) {
                    $this->error['company_name'][$languageId] = $this->language->get('error_company_name');
                }
            }
        }

        if (isset($this->request->post['shop_description'])) {
            foreach ($this->request->post['shop_description'] as $languageId => $value) {
                if ((utf8_strlen(trim($value)) < 10) || (utf8_strlen(trim($value)) > 255)) {
                    $this->error['shop_description'][$languageId] = $this->language->get('error_shop_description');
                }
            }
        }

        if (isset($this->request->post['shop_country'])) {
            if (empty($this->request->post['shop_country'])) {
                $this->error['shop_country'] = $this->language->get('error_shop_country');
            }
        }

        if (isset($this->request->post['delivery_service'])) {
            foreach ($this->request->post['delivery_service'] as $languageId => $value) {
                if ((utf8_strlen(trim($value)) < 3) || (utf8_strlen(trim($value)) > 255)) {
                    $this->error['delivery_service'][$languageId] = $this->language->get('error_delivery_service');
                }
            }
        }

        if ($this->error && ! isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return ! $this->error;
    }

    private function validateFeed(): bool
    {
        if (! $this->user->hasPermission('modify', 'extension/feed/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['name'])) {
            if ((utf8_strlen(trim($this->request->post['name'])) < 3) || (utf8_strlen(trim($this->request->post['name'])) > 256)) {
                $this->error['name'] = $this->language->get('error_name');
            }
        }

        if (isset($this->request->post['filename'])) {
            if ((utf8_strlen(trim($this->request->post['filename'])) < 3) || (utf8_strlen(trim($this->request->post['filename'])) > 128)) {
                $this->error['filename'] = $this->language->get('error_filename');
            }
        }

        if (isset($this->request->post['image_count'])) {
            if ((int) $this->request->post['image_count'] > 8) {
                $this->error['image_count'] = $this->language->get('error_image_count');
            }
        }

        if ($this->error && ! isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return ! $this->error;
    }

    public function install()
    {
        $this->load->model('setting/setting');


        $this->model_setting_setting->editSetting(
            'feed_' . $this->moduleName,
            ['feed_' . $this->moduleName . '_status' => '1']
        );

        if ($this->awCore->isLegacy()) {
            $this->model_setting_setting->editSetting(
                $this->moduleName,
                [$this->moduleName . '_status' => '1']
            );
        }

        $this->load->model('extension/feed/' . $this->moduleName);
        $this->model_extension_feed_aw_xml_feed->install();

        $this->installPermissions();
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('feed_' . $this->moduleName);

        if ($this->awCore->isLegacy()) {
            $this->model_setting_setting->deleteSetting($this->moduleName);
        }

        $this->awCore->removeConfig($this->moduleName);

        $this->load->model('extension/feed/' . $this->moduleName);
        $this->model_extension_feed_aw_xml_feed->uninstall();
    }

    protected function installPermissions()
    {
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'access',
            'extension/feed/' . $this->moduleName
        );
        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'modify',
            'extension/feed/' . $this->moduleName
        );
    }

    private function generateAccessKey(): string
    {
        return substr(md5(uniqid(rand(), true)), 0, 8);
    }
}
