<?php

/**
 * Moonshine Calculator Module
 *
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @email support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwMoonshineCalculator extends Controller
{
    private string $moduleName = 'aw_moonshine_calculator';
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

        $this->routeExtension = $this->awCore->isLegacy()
            ? 'extension/extension'
            : 'marketplace/extension';
    }

    public function index()
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

        if ($this->config->get('config_editor_default')) {
            $this->document->addScript('view/javascript/ckeditor/ckeditor.js');
            $this->document->addScript('view/javascript/ckeditor/ckeditor_init.js');
        } else {
            $this->document->addScript('view/javascript/summernote/summernote.js');
            $this->document->addScript('view/javascript/summernote/lang/summernote-' . $this->language->get('lang') . '.js');
            $this->document->addScript('view/javascript/summernote/opencart.js');
            $this->document->addStyle('view/javascript/summernote/summernote.css');
        }

        $this->params['success'] = $this->session->data['success'] ?? '';

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

        $this->params['is_legacy'] = $this->awCore->isLegacy();
        $this->params['config_language_id'] = $this->config->get('config_language_id');

        $this->load->model('localisation/language');

        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('setting/store');

        $this->params['store_list'][] = [
            'store_id' => 0,
            'name' => $this->language->get('text_default')
        ];

        $stores = $this->model_setting_store->getStores();

        foreach ($stores as $store) {
            $this->params['store_list'][] = [
                'store_id' => $store['store_id'],
                'name' => $store['name']
            ];
        }

        $this->params['status'] = $this->moduleConfig->get('status', false);
        $this->params['title'] = $this->moduleConfig->get('title', []);
        $this->params['h1'] = $this->moduleConfig->get('h1', []);
        $this->params['meta_description'] = $this->moduleConfig->get('meta_description', []);
        $this->params['description'] = $this->moduleConfig->get('description', []);
        $this->params['instructions'] = $this->moduleConfig->get('instructions', []);
        $this->params['seo_url'] = $this->awCore->getSeoUrls('extension/module/' . $this->moduleName) ?? [];

        $this->params['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true)
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true)
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true)
            ],
        ];

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName, $this->params));
    }

    public function store()
    {
        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->awCore->setConfig($this->moduleName, $this->request->post);

            $this->awCore->setSeoUrls($this->request->post['seo_url'], 'extension/module/' . $this->moduleName);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                $this->routeExtension,
                $this->tokenData['param'] . '&type=module',
                true
            ));
        }

        $this->cache->delete('seo_pro');

        $this->index();
    }

    private function validate(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['title'])) {
            foreach ($this->request->post['title'] as $languageId => $value) {
                if ((utf8_strlen($value) < 3) || (utf8_strlen($value) > 64)) {
                    $this->error['title'][$languageId] = $this->language->get('error_title');
                }
            }
        }

        if (isset($this->request->post['meta_description'])) {
            foreach ($this->request->post['meta_description'] as $languageId => $value) {
                if ((utf8_strlen($value) < 3) || (utf8_strlen($value) > 255)) {
                    $this->error['meta_description'][$languageId] = $this->language->get('error_meta_description');
                }
            }
        }

        if (isset($this->request->post['h1'])) {
            foreach ($this->request->post['h1'] as $languageId => $value) {
                if ((utf8_strlen($value) < 3) || (utf8_strlen($value) > 64)) {
                    $this->error['h1'][$languageId] = $this->language->get('error_h1');
                }
            }
        }

        if (isset($this->request->post['seo_url'])) {
            foreach ($this->request->post['seo_url'] as $storeId => $languages) {
                foreach ($languages as $languageId => $seo_url) {
                    if (!empty($seo_url)) {

                        if (count(array_keys($languages, $seo_url)) > 1) {
                            $this->error['seo_url'][$storeId][$languageId] = $this->language->get('error_seo_url_unique');
                        }

                        $seoUrlExists = $this->awCore->seoUrlExists(
                            $seo_url,
                            $storeId,
                            $languageId,
                            'extension/module/' . $this->moduleName
                        );


                        if ($seoUrlExists) {
                            $this->error['seo_url'][$storeId][$languageId] = $this->language->get('error_seo_url_exists');
                        }
                    }

                    if ((utf8_strlen($seo_url) < 1) || (utf8_strlen($seo_url) > 255)) {
                        $this->error['seo_url'][$storeId][$languageId] = $this->language->get('error_seo_url');
                    }
                }
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    public function install()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_' . $this->moduleName, ['module_' . $this->moduleName . '_status' => '1']);
        $this->installPermissions();
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);
        $this->awCore->removeConfig($this->moduleName);
    }

    protected function installPermissions()
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
