<?php

/**
 * GDPR Consent Module (Cookie Consent + Google Consent Mode v2)
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @email support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwGdprConsent extends Controller
{
    private string $moduleName = 'aw_gdpr_consent';

    private \Alexwaha\Config $moduleConfig;

    private \Alexwaha\Language $language;

    private array $error = [];

    private string $routeExtension;

    private array $tokenData;

    private array $params;

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

        $this->routeExtension = $this->awCore->isLegacy() ? 'extension/extension' : 'marketplace/extension';
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

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('catalog/information');
        $this->params['informations'] = $this->model_catalog_information->getInformations();

        $this->params['gdpr_consent_status'] = $this->moduleConfig->get('status', false);
        $this->params['gdpr_consent_theme'] = $this->moduleConfig->get('theme', 'light');
        $this->params['gdpr_consent_accent_color'] = $this->moduleConfig->get('accent_color', '#0937cc');
        $this->params['gdpr_consent_title'] = $this->moduleConfig->get('title', []);
        $this->params['gdpr_consent_message'] = $this->moduleConfig->get('message', []);
        $this->params['gdpr_consent_policy_page'] = $this->moduleConfig->get('policy_page', 0);
        $this->params['gdpr_consent_cookie_days'] = $this->moduleConfig->get('cookie_days', 365);

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

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/main', $this->params));
    }

    public function store()
    {
        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->awCore->setConfig($this->moduleName, $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                $this->routeExtension,
                $this->tokenData['param'] . '&type=module',
                true
            ));
        }

        $this->index();
    }

    public function exportConfig(): void
    {
        if (!$this->validate()) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));

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
        } elseif (isset($this->request->files['import_file']) && is_uploaded_file($this->request->files['import_file']['tmp_name'])) {
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

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
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

        if (isset($this->request->post['cookie_days'])) {
            $cookieDays = (int) $this->request->post['cookie_days'];
            if ($cookieDays < 1 || $cookieDays > 730) {
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
        $this->installEvents();
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);
        $this->getEventModel()->deleteEventByCode($this->moduleName);
        $this->awCore->removeConfig($this->moduleName);
    }

    protected function installEvents()
    {
        $model = $this->getEventModel();
        $model->deleteEventByCode($this->moduleName);

        $model->addEvent(
            $this->moduleName,
            'catalog/view/common/header/after',
            'extension/module/' . $this->moduleName . '/head'
        );
        $model->addEvent(
            $this->moduleName,
            'catalog/view/common/footer/after',
            'extension/module/' . $this->moduleName . '/footer'
        );
    }

    protected function getEventModel()
    {
        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/event');

            return $this->model_extension_event;
        }

        $this->load->model('setting/event');

        return $this->model_setting_event;
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
