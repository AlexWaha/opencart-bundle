<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwLandingLinks extends Controller
{
    private $moduleName = 'aw_landing_links';
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

        $this->routeExtension = $this->awCore->isLegacy()
            ? 'extension/extension'
            : 'marketplace/extension';
    }

    public function index()
    {
        $this->document->setTitle($this->language->get('heading_main_title'));

        $this->document->addScript('view/javascript/Sortable.min.js');

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $this->params['token_param'] = $this->tokenData['param'];

        $moduleId = $this->request->get['module_id'] ?? 0;

        $this->params['action'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/store',
            $this->tokenData['param'] . ($moduleId ? '&module_id=' . $moduleId : '&type=module'),
            true
        );

        $this->params['cancel'] = $this->url->link(
            $this->routeExtension,
            $this->tokenData['param'] . '&type=module',
            true
        );

        $this->load->model('localisation/language');

        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $moduleData = $this->awCore->getModule($moduleId);

        $this->params['name'] = $this->request->post['name'] ?? $moduleData['name'] ?? '';
        $this->params['title'] = $this->request->post['title'] ?? $moduleData['title'] ?? [];
        $this->params['status'] = $this->request->post['status'] ?? $moduleData['status'] ?? false;

        $pages = $this->request->post['pages'] ?? $moduleData['pages'] ?? [];

        $this->params['pages'] = [];

        $this->load->model('extension/module/aw_landing_links');

        if ($pages) {
            foreach ($pages as $pageId) {
                $page = $this->model_extension_module_aw_landing_links->getPage($pageId);

                if ($page) {
                    $this->params['pages'][] = [
                        'landing_page_id' => $page['landing_page_id'],
                        'name' => $page['name'],
                    ];
                }
            }
        }

        $this->params['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link(
                    'common/dashboard',
                    $this->tokenData['param'],
                    true
                ),
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link(
                    $this->routeExtension,
                    $this->tokenData['param'] . '&type=module',
                    true
                ),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link(
                    'extension/module/' . $this->moduleName,
                    $this->tokenData['param'] . ($moduleId ? '&module_id=' . $moduleId : '&type=module'),
                    true
                ),
            ],
        ];

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName, $this->params));
    }

    public function store()
    {
        $moduleId = $this->request->get['module_id'] ?? 0;

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {

            $this->awCore->setModule($this->moduleName, $this->request->post, $moduleId);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                $this->routeExtension,
                $this->tokenData['param'] . '&type=module',
                true
            ));
        }

        $this->index();
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['name'])) {
            if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 255)) {
                $this->error['name'] = $this->language->get('error_name');
            }
        }

        if (isset($this->request->post['title'])) {
            foreach ($this->request->post['title'] as $languageId => $value) {
                if ((utf8_strlen($value) < 1) || (utf8_strlen($value) > 255)) {
                    $this->error['title'][$languageId] = $this->language->get('error_title');
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
