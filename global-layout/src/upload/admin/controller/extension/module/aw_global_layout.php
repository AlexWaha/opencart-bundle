<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwGlobalLayout extends Controller
{
    private $moduleName = 'aw_global_layout';

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

    public function index(array $postData = [])
    {
        $this->document->setTitle($this->language->get('heading_main_title'));

        $this->document->addScript('view/javascript/Sortable.min.js');

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

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
                'href' => $this->url->link(
                    'extension/module/' . $this->moduleName,
                    $this->tokenData['param'] . '&type=module',
                    true
                ),
            ],
        ];

        $this->load->model('extension/module/' . $this->moduleName);

        $layout = $this->model_extension_module_aw_global_layout->getLayout($this->moduleName);

        $layoutId = $layout['id'] ?? null;

        $this->params['action'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/store',
            $this->tokenData['param'] . '&layout_id=' . $layoutId,
            true
        );

        $this->params['cancel'] = $this->url->link(
            $this->routeExtension,
            $this->tokenData['param'] . '&type=module',
            true
        );

        if (!$postData && $layout) {
            $this->params['name'] = $layout['name'];
            $this->params['status'] = $layout['status'];
            $this->params['layout_modules'] = $this->model_extension_module_aw_global_layout->getModules($layout['id']);
        } else {
            $this->params['name'] = $postData['name'] ?? '';
            $this->params['status'] = $postData['status'] ?? false;
            $this->params['layout_modules'] = $postData['layout_modules'] ?? [];
        }

        $extensionModelRoute = $this->awCore->isLegacy() ? 'extension/extension' : 'setting/extension';
        $moduleModelRoute = $this->awCore->isLegacy() ? 'extension/module' : 'setting/module';

        $this->load->model($extensionModelRoute);
        $this->load->model($moduleModelRoute);

        $modelExtension = $this->awCore->isLegacy()
            ? $this->model_extension_extension
            : $this->model_setting_extension;

        $modelModule = $this->awCore->isLegacy()
            ? $this->model_extension_module
            : $this->model_setting_module;

        $extensions = $modelExtension->getInstalled('module');

        $this->params['extensions'] = [];

        foreach ($extensions as $code) {
            $moduleData = [];

            $modules = $modelModule->getModulesByCode($code);

            if ($modules) {
                foreach ($modules as $module) {
                    $moduleData[] = [
                        'name' => strip_tags($module['name']),
                        'code' => $code . '.' . $module['module_id']
                    ];
                }
            }

            if ($this->config->has($code . '_status') || $this->config->get('module_' . $code . '_status') || $moduleData) {
                $this->language->load('extension/module/' . $code);

                $this->params['extensions'][] = [
                    'name'   => strip_tags($this->language->get('heading_title')),
                    'code'   => $code,
                    'module' => $moduleData
                ];
            }
        }

        if (!empty($this->params['layout_modules'])) {
            foreach ($this->params['layout_modules'] as $module) {
                $part = explode('.', $module['code']);

                $moduleInfo = [];

                if (isset($part[1])) {
                    $moduleInfo = $modelModule->getModule($part[1]);
                }

                if (!isset($part[1]) || $moduleInfo) {
                    $this->params['modules'][] = [
                        'code'       => $module['code'],
                        'edit'       => $this->url->link(
                            'extension/module/' . $part[0],
                            $this->tokenData['param'] . (isset($part[1]) ? '&module_id=' . $part[1] : ''),
                            true
                        ),
                        'position'   => $module['position'],
                        'sort_order' => $module['sort_order']
                    ];
                }
            }
        }

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName, $this->params));
    }

    public function store()
    {
        $id = $this->request->get['layout_id'];

        $this->load->model('extension/module/' . $this->moduleName);

        if ($id && ($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
            $this->model_extension_module_aw_global_layout->editLayout($id, $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true));
        }

        $this->index($this->request->post);
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

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    public function install()
    {
        $this->load->model('setting/store');
        $this->load->model('extension/module/' . $this->moduleName);

        $this->model_extension_module_aw_global_layout->install('All pages', $this->moduleName);
        $this->installPermissions();
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_global_layout`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_global_layout_module`");
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
