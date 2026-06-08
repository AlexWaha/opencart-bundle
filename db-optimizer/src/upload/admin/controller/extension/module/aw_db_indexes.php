<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwDbIndexes extends Controller
{
    private string $moduleName = 'aw_db_indexes';

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
    }

    public function index(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

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

        $this->params['action'] = $this->url->link('extension/module/' . $this->moduleName . '/store', $this->tokenData['param'], true);
        $this->params['cancel'] = $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true);

        $this->params['status'] = (bool) $this->moduleConfig->get('status', false);
        $this->params['min_rows'] = (int) $this->moduleConfig->get('min_rows', 500);
        $this->params['max_indexes_per_table'] = (int) $this->moduleConfig->get('max_indexes_per_table', 8);
        $this->params['scope'] = (string) $this->moduleConfig->get('scope', 'all');

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

    public function analyze(): void
    {
        $json = [];

        if (! $this->validate()) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('extension/module/' . $this->moduleName);

            $options = [
                'min_rows' => (int) $this->moduleConfig->get('min_rows', 500),
                'max_indexes_per_table' => (int) $this->moduleConfig->get('max_indexes_per_table', 8),
                'scope' => (string) $this->moduleConfig->get('scope', 'all'),
            ];

            $json = $this->model_extension_module_aw_db_indexes->analyze($options);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function applyFixes(): void
    {
        $json = ['results' => []];

        if (! $this->validate()) {
            $json['error'] = $this->language->get('error_permission');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));

            return;
        }

        $this->load->model('extension/module/' . $this->moduleName);

        $actions = $this->request->post['actions'] ?? [];

        if (! is_array($actions) || ! $actions) {
            $json['error'] = $this->language->get('error_no_actions');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));

            return;
        }

        foreach ($actions as $action) {
            $type = $action['type'] ?? '';
            $table = $action['table'] ?? '';
            $column = $action['column'] ?? '';

            switch ($type) {
                case 'index':
                    $json['results'][] = $this->model_extension_module_aw_db_indexes->addRecommendedIndex($table, $column);
                    break;
                case 'engine':
                    $json['results'][] = $this->model_extension_module_aw_db_indexes->convertEngine($table);
                    break;
                case 'optimize':
                    $json['results'][] = $this->model_extension_module_aw_db_indexes->optimizeTable($table);
                    break;
                default:
                    $json['results'][] = [
                        'ok' => false,
                        'table' => $table,
                        'error' => $this->language->get('error_unknown_action'),
                    ];
            }
        }

        $json['success'] = $this->language->get('text_apply_done');

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function listApplied(): void
    {
        $json = [];

        if (! $this->validate()) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('extension/module/' . $this->moduleName);
            $json['indexes'] = $this->model_extension_module_aw_db_indexes->listAwIndexes();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function rollback(): void
    {
        $json = [];

        if (! $this->validate()) {
            $json['error'] = $this->language->get('error_permission');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));

            return;
        }

        $this->load->model('extension/module/' . $this->moduleName);

        $table = $this->request->post['table'] ?? '';
        $index = $this->request->post['index'] ?? '';

        if ($table !== '' && $index !== '') {
            $json['result'] = $this->model_extension_module_aw_db_indexes->dropAwIndex($table, $index);
        } else {
            $json['dropped'] = $this->model_extension_module_aw_db_indexes->dropAwIndexes();
        }

        $json['success'] = $this->language->get('text_rollback_done');

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function validate(): bool
    {
        if (! $this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return ! $this->error;
    }

    public function install(): void
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_' . $this->moduleName, ['module_' . $this->moduleName . '_status' => '1']);

        $this->installPermissions();
    }

    public function uninstall(): void
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);

        $this->awCore->removeConfig($this->moduleName);
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
