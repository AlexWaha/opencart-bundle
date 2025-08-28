<?php

/**
 * Age Verification Module
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @email support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwAgeVerification extends Controller
{
    private $moduleName = 'aw_age_verification';

    private $language;

    private $error = [];

    private $routeExtension;

    private $params;

    private $tokenData;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language = $this->awCore->getLanguage();
        $this->tokenData = $this->awCore->getToken();
        $this->params = $this->language->load('extension/module/' . $this->moduleName);
        $this->params['token'] = $this->tokenData['token'];
        $this->params['token_param'] = $this->tokenData['param'];

        $this->routeExtension = $this->awCore->isLegacy() ? 'extension/extension' : 'marketplace/extension';
    }

    public function index()
    {
        $this->document->setTitle($this->language->get('heading_main_title'));

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

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $moduleConfig = $this->awCore->getConfig($this->moduleName);

        $this->params['age_verification_status'] = $this->request->post['status'] ?? $moduleConfig->get('status') ?? false;
        $this->params['age_verification_title'] = $this->request->post['title'] ?? $moduleConfig->get('title') ?? [];
        $this->params['age_verification_description'] = $this->request->post['description'] ?? $moduleConfig->get('description') ?? [];
        $this->params['age_verification_cookie_days'] = $this->request->post['cookie_days'] ?? $moduleConfig->get('cookie_days') ?? 30;
        $this->params['age_verification_redirect_url'] = $this->request->post['redirect_url'] ?? $moduleConfig->get('redirect_url') ?? 'https://google.com';

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
            $configData = [
                'status' => $this->request->post['status'] ?? 0,
                'title' => $this->request->post['title'] ?? [],
                'description' => $this->request->post['description'] ?? [],
                'cookie_days' => $this->request->post['cookie_days'] ?? 30,
                'redirect_url' => $this->request->post['redirect_url'] ?? 'https://google.com'
            ];

            $this->awCore->setConfig($this->moduleName, $configData);

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
                if ((utf8_strlen(trim($value)) < 3) || (utf8_strlen(trim($value)) > 255)) {
                    $this->error['title'][$languageId] = $this->language->get('error_title');
                }
            }
        }

        if (isset($this->request->post['description'])) {
            foreach ($this->request->post['description'] as $languageId => $value) {
                if ((utf8_strlen(trim($value)) < 10) || (utf8_strlen(trim($value)) > 1000)) {
                    $this->error['description'][$languageId] = $this->language->get('error_description');
                }
            }
        }

        if (isset($this->request->post['cookie_days'])) {
            $cookieDays = (int) $this->request->post['cookie_days'];
            if ($cookieDays < 1 || $cookieDays > 365) {
                $this->error['cookie_days'] = $this->language->get('error_cookie_days');
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
        $this->model_setting_setting->editSetting(
            'module_' . $this->moduleName,
            ['module_' . $this->moduleName . '_status' => '1']
        );
        $this->installPermissions();
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);
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
