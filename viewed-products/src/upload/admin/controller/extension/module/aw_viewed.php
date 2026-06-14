<?php

/**
 * Viewed Products - admin controller
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwViewed extends Controller
{
    private string $moduleName = 'aw_viewed';

    private \Alexwaha\Config $moduleConfig;

    private \Alexwaha\Language $language;

    private array $error = [];

    private array $tokenData;

    private string $routeExtension;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language = $this->awCore->getLanguage();
        $this->tokenData = $this->awCore->getToken();
        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
        $this->routeExtension = $this->awCore->isLegacy() ? 'extension/extension' : 'marketplace/extension';
    }

    public function index(): void
    {
        $data = $this->language->load('extension/module/' . $this->moduleName);

        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $instance = [
                'name'      => $this->request->post['name'],
                'status'    => (int) ($this->request->post['status'] ?? 0),
                'title'     => $this->request->post['title'] ?? [],
                'limit'     => (int) $this->request->post['limit'],
                'width'     => (int) $this->request->post['width'],
                'height'    => (int) $this->request->post['height'],
                'show_link' => (int) ($this->request->post['show_link'] ?? 0),
            ];

            if (!isset($this->request->get['module_id'])) {
                $this->awCore->setModule($this->moduleName, $instance);
            } else {
                $this->awCore->setModule($this->moduleName, $instance, (int) $this->request->get['module_id']);
            }

            $this->awCore->setConfig($this->moduleName, [
                'storage_days'  => (int) $this->request->post['storage_days'],
                'product_limit' => (int) $this->request->post['product_limit'],
                'page_enabled'  => (int) ($this->request->post['page_enabled'] ?? 0),
                'menu_link'     => (int) ($this->request->post['menu_link'] ?? 0),
                'menu_label'    => $this->request->post['menu_label'] ?? [],
            ]);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true));
        }

        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['error_name'] = $this->error['name'] ?? '';
        $data['error_width'] = $this->error['width'] ?? '';
        $data['error_height'] = $this->error['height'] ?? '';

        $moduleId = isset($this->request->get['module_id']) ? (int) $this->request->get['module_id'] : 0;
        $module_info = $moduleId ? $this->awCore->getModule($moduleId) : [];

        $globals = $this->moduleConfig;

        $data['action'] = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'] . ($moduleId ? '&module_id=' . $moduleId : ''),
            true
        );
        $data['cancel'] = $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true);

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['name']      = $this->request->post['name']      ?? ($module_info['name'] ?? '');
        $data['status']    = $this->request->post['status']    ?? ($module_info['status'] ?? 0);
        $data['title']     = $this->request->post['title']     ?? ($module_info['title'] ?? []);
        $data['limit']     = $this->request->post['limit']     ?? ($module_info['limit'] ?? 4);
        $data['width']     = $this->request->post['width']     ?? ($module_info['width'] ?? 200);
        $data['height']    = $this->request->post['height']    ?? ($module_info['height'] ?? 200);
        $data['show_link'] = $this->request->post['show_link'] ?? ($module_info['show_link'] ?? 0);

        $data['storage_days']  = $this->request->post['storage_days']  ?? $globals->get('storage_days', 7);
        $data['product_limit'] = $this->request->post['product_limit'] ?? $globals->get('product_limit', 50);
        $data['page_enabled']  = $this->request->post['page_enabled']  ?? $globals->get('page_enabled', 1);
        $data['menu_link']     = $this->request->post['menu_link']     ?? $globals->get('menu_link', 1);
        $data['menu_label']    = $this->request->post['menu_label']    ?? $globals->get('menu_label', []);

        $data['token'] = $this->tokenData['token'];
        $data['token_param'] = $this->tokenData['param'];
        $data['module_name'] = $this->moduleName;

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true)],
            ['text' => $this->language->get('text_extension'), 'href' => $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true)],
            ['text' => $this->language->get('heading_title'), 'href' => $data['action']],
        ];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/main', $data));
    }

    public function install(): void
    {
        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_viewed->createTable();

        $this->installEvents();
        $this->installPermissions();

        if ($this->moduleConfig->get('storage_days') === null) {
            $this->awCore->setConfig($this->moduleName, [
                'storage_days'  => 7,
                'product_limit' => 50,
                'page_enabled'  => 1,
                'menu_link'     => 1,
                'menu_label'    => [],
            ]);
        }
    }

    public function uninstall(): void
    {
        $this->getEventModel()->deleteEventByCode($this->moduleName);

        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_viewed->dropTable();

        $this->awCore->removeConfig($this->moduleName);
    }

    private function installEvents(): void
    {
        $model = $this->getEventModel();
        $model->deleteEventByCode($this->moduleName);

        $model->addEvent($this->moduleName, 'catalog/controller/product/product/before', 'extension/module/' . $this->moduleName . '/track');
        $model->addEvent($this->moduleName, 'catalog/controller/account/account/before', 'extension/module/' . $this->moduleName . '/accountLogin');
        $model->addEvent($this->moduleName, 'catalog/view/account/account/after', 'extension/module/' . $this->moduleName . '/accountMenu');
    }

    private function getEventModel()
    {
        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/event');

            return $this->model_extension_event;
        }

        $this->load->model('setting/event');

        return $this->model_setting_event;
    }

    private function installPermissions(): void
    {
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/' . $this->moduleName);
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/' . $this->moduleName);
    }

    protected function validate(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (!(int) $this->request->post['width']) {
            $this->error['width'] = $this->language->get('error_width');
        }

        if (!(int) $this->request->post['height']) {
            $this->error['height'] = $this->language->get('error_height');
        }

        return !$this->error;
    }
}
