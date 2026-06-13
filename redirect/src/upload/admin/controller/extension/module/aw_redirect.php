<?php

/**
 * Redirect Manager Module
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @email support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwRedirect extends Controller
{
    private string $moduleName = 'aw_redirect';

    private \Alexwaha\Config $moduleConfig;

    private \Alexwaha\Language $language;

    private array $error = [];

    private string $routeExtension;

    private array $params;

    private array $tokenData;

    private array $statusCodes = [301, 302, 410];

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

    private function link(string $suffix = '', string $extra = ''): string
    {
        return $this->url->link(
            'extension/module/' . $this->moduleName . $suffix,
            $this->tokenData['param'] . $extra,
            true
        );
    }

    private function breadcrumbs(): array
    {
        return [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true)],
            ['text' => $this->language->get('text_extension'), 'href' => $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true)],
            ['text' => $this->language->get('heading_title'), 'href' => $this->link()],
        ];
    }

    private function clearCache(): void
    {
        $this->cache->delete('aw_redirect.map');
    }

    // --- Redirect rules list ---

    public function index(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();
        $this->load->model('extension/module/' . $this->moduleName);

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);
        $this->params['error_warning'] = $this->session->data['error'] ?? '';
        unset($this->session->data['error']);

        $filterStatus = $this->request->get['filter_status'] ?? '';
        $filterType = $this->request->get['filter_match_type'] ?? '';
        $filterSource = $this->request->get['filter_source'] ?? '';
        $page = (int) ($this->request->get['page'] ?? 1);
        $limit = (int) $this->config->get('config_limit_admin');

        $filterData = [
            'filter_status' => $filterStatus,
            'filter_match_type' => $filterType,
            'filter_source' => $filterSource,
            'sort' => $this->request->get['sort'] ?? 'date_added',
            'order' => $this->request->get['order'] ?? 'DESC',
            'start' => ($page - 1) * $limit,
            'limit' => $limit,
        ];

        $total = $this->model_extension_module_aw_redirect->getTotalRedirects($filterData);
        $results = $this->model_extension_module_aw_redirect->getRedirects($filterData);

        $this->params['redirects'] = [];

        foreach ($results as $row) {
            $this->params['redirects'][] = [
                'redirect_id' => $row['redirect_id'],
                'source' => $row['source'],
                'target' => $row['target'],
                'match_type' => (int) $row['match_type'],
                'status_code' => (int) $row['status_code'],
                'status' => (int) $row['status'],
                'hits' => (int) $row['hits'],
                'edit' => $this->link('/edit', '&redirect_id=' . $row['redirect_id']),
            ];
        }

        $filterQuery = '&filter_status=' . urlencode($filterStatus)
            . '&filter_match_type=' . urlencode($filterType)
            . '&filter_source=' . urlencode($filterSource);

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->link('', $filterQuery . '&page={page}');
        $this->params['pagination'] = $pagination->render();

        $this->params['results'] = sprintf(
            $this->language->get('text_pagination'),
            $total ? ((($page - 1) * $limit) + 1) : 0,
            ((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit),
            $total,
            ceil($total / $limit) ?: 1
        );

        $this->params['filter_status'] = $filterStatus;
        $this->params['filter_match_type'] = $filterType;
        $this->params['filter_source'] = $filterSource;

        $this->params['add'] = $this->link('/add');
        $this->params['delete'] = $this->link('/delete');
        $this->params['log_url'] = $this->link('/log');
        $this->params['settings_url'] = $this->link('/settings');

        $this->params['breadcrumbs'] = $this->breadcrumbs();
        $this->renderPage('list');
    }

    public function add(): void
    {
        $this->form(0);
    }

    public function edit(): void
    {
        $this->form((int) ($this->request->get['redirect_id'] ?? 0));
    }

    private function form(int $redirectId): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();
        $this->load->model('extension/module/' . $this->moduleName);

        $this->params['error'] = $this->error;

        $rule = $redirectId ? $this->model_extension_module_aw_redirect->getRedirect($redirectId) : [];

        if ($redirectId && !$rule) {
            $this->session->data['error'] = $this->language->get('error_not_found');
            $this->response->redirect($this->link());
            return;
        }

        $post = $this->request->post;

        $this->params['redirect_id'] = $redirectId;
        $this->params['source'] = $post['source'] ?? $rule['source'] ?? ($this->request->get['source'] ?? '');
        $this->params['target'] = $post['target'] ?? $rule['target'] ?? '';
        $this->params['match_query'] = (int) ($post['match_query'] ?? $rule['match_query'] ?? 0);
        $this->params['status_code'] = (int) ($post['status_code'] ?? $rule['status_code'] ?? $this->moduleConfig->get('default_code', 301));
        $this->params['status'] = (int) ($post['status'] ?? $rule['status'] ?? 1);
        $this->params['store_id'] = (int) ($post['store_id'] ?? $rule['store_id'] ?? 0);
        $this->params['status_codes'] = $this->statusCodes;

        $this->load->model('setting/store');
        $this->params['stores'] = array_merge(
            [['store_id' => 0, 'name' => $this->config->get('config_name') . ' (' . $this->language->get('text_all_stores') . ')']],
            $this->model_setting_store->getStores()
        );

        $this->params['action'] = $this->link('/store', $redirectId ? '&redirect_id=' . $redirectId : '');
        $this->params['cancel'] = $this->link();

        $this->params['breadcrumbs'] = $this->breadcrumbs();
        $this->renderPage('form');
    }

    public function store(): void
    {
        $this->load->model('extension/module/' . $this->moduleName);
        $redirectId = (int) ($this->request->get['redirect_id'] ?? 0);

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validateRule($redirectId)) {
            if ($redirectId) {
                $this->model_extension_module_aw_redirect->editRedirect($redirectId, $this->request->post);
                $this->session->data['success'] = $this->language->get('text_success_edit');
            } else {
                $this->model_extension_module_aw_redirect->addRedirect($this->request->post);
                $this->session->data['success'] = $this->language->get('text_success_add');
            }

            $this->clearCache();
            $this->response->redirect($this->link());
            return;
        }

        $this->form($redirectId);
    }

    public function delete(): void
    {
        $this->load->model('extension/module/' . $this->moduleName);

        if (isset($this->request->post['selected']) && $this->validatePermission()) {
            foreach ($this->request->post['selected'] as $redirectId) {
                $this->model_extension_module_aw_redirect->deleteRedirect((int) $redirectId);
            }

            $this->clearCache();
            $this->session->data['success'] = $this->language->get('text_success_delete');
            $this->response->redirect($this->link());
            return;
        }

        $this->index();
    }

    // --- 404 resolving log ---

    public function log(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();
        $this->load->model('extension/module/' . $this->moduleName);

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $filterUrl = $this->request->get['filter_url'] ?? '';
        $page = (int) ($this->request->get['page'] ?? 1);
        $limit = (int) $this->config->get('config_limit_admin');

        $filterData = [
            'filter_url' => $filterUrl,
            'sort' => $this->request->get['sort'] ?? 'date_modified',
            'order' => $this->request->get['order'] ?? 'DESC',
            'start' => ($page - 1) * $limit,
            'limit' => $limit,
        ];

        $total = $this->model_extension_module_aw_redirect->getTotalLogs($filterData);
        $results = $this->model_extension_module_aw_redirect->getLogs($filterData);

        $this->params['logs'] = [];

        foreach ($results as $row) {
            $this->params['logs'][] = [
                'log_id' => $row['log_id'],
                'url' => $row['url'],
                'hits' => (int) $row['hits'],
                'date_modified' => date('d.m.Y H:i', strtotime($row['date_modified'])),
                'create' => $this->link('/add', '&source=' . urlencode($row['url'])),
            ];
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->link('/log', '&filter_url=' . urlencode($filterUrl) . '&page={page}');
        $this->params['pagination'] = $pagination->render();

        $this->params['results'] = sprintf(
            $this->language->get('text_pagination'),
            $total ? ((($page - 1) * $limit) + 1) : 0,
            ((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit),
            $total,
            ceil($total / $limit) ?: 1
        );

        $this->params['filter_url'] = $filterUrl;
        $this->params['back'] = $this->link();
        $this->params['delete_log'] = $this->link('/deleteLog');
        $this->params['clear_log'] = $this->link('/clearLog');
        $this->params['redirect_home'] = $this->link('/redirectToHome');

        $this->params['breadcrumbs'] = array_merge($this->breadcrumbs(), [
            ['text' => $this->language->get('text_log'), 'href' => $this->link('/log')],
        ]);
        $this->renderPage('log');
    }

    public function deleteLog(): void
    {
        $this->load->model('extension/module/' . $this->moduleName);

        if (isset($this->request->post['selected']) && $this->validatePermission()) {
            foreach ($this->request->post['selected'] as $logId) {
                $this->model_extension_module_aw_redirect->deleteLog((int) $logId);
            }

            $this->session->data['success'] = $this->language->get('text_success_delete');
        }

        $this->response->redirect($this->link('/log'));
    }

    public function clearLog(): void
    {
        $this->load->model('extension/module/' . $this->moduleName);

        if ($this->validatePermission()) {
            $this->model_extension_module_aw_redirect->clearLogs();
            $this->session->data['success'] = $this->language->get('text_success_clear');
        }

        $this->response->redirect($this->link('/log'));
    }

    public function redirectToHome(): void
    {
        $this->load->model('extension/module/' . $this->moduleName);

        if (isset($this->request->post['selected']) && $this->validatePermission()) {
            foreach ($this->request->post['selected'] as $logId) {
                $log = $this->model_extension_module_aw_redirect->getLog((int) $logId);

                if ($log) {
                    $this->model_extension_module_aw_redirect->addRedirect([
                        'source' => $log['url'],
                        'target' => '/',
                        'match_query' => strpos($log['url'], '?') !== false ? 1 : 0,
                        'status_code' => 301,
                        'store_id' => 0,
                        'status' => 1,
                    ]);
                    $this->model_extension_module_aw_redirect->deleteLog((int) $logId);
                }
            }

            $this->clearCache();
            $this->session->data['success'] = $this->language->get('text_success_home');
        }

        $this->response->redirect($this->link('/log'));
    }

    // --- Settings ---

    public function settings(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);
        $this->params['error'] = $this->error;

        $this->params['action'] = $this->link('/saveSettings');
        $this->params['cancel'] = $this->link();
        $this->params['import_action'] = $this->link('/importCsv');
        $this->params['export_url'] = $this->link('/exportCsv');

        $this->params['gdpr_status'] = $this->moduleConfig->get('status', false);
        $this->params['default_code'] = (int) $this->moduleConfig->get('default_code', 301);
        $this->params['log_404'] = $this->moduleConfig->get('log_404', true);
        $this->params['ignore_patterns'] = $this->moduleConfig->get('ignore_patterns', $this->defaultIgnore());
        $this->params['status_codes'] = $this->statusCodes;

        $this->params['breadcrumbs'] = array_merge($this->breadcrumbs(), [
            ['text' => $this->language->get('text_settings'), 'href' => $this->link('/settings')],
        ]);
        $this->renderPage('settings');
    }

    public function saveSettings(): void
    {
        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validatePermission()) {
            $this->awCore->setConfig($this->moduleName, $this->request->post);
            $this->clearCache();
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->link('/settings'));
            return;
        }

        $this->settings();
    }

    public function exportCsv(): void
    {
        if (!$this->validatePermission()) {
            $this->response->redirect($this->link('/settings'));
            return;
        }

        $this->load->model('extension/module/' . $this->moduleName);
        $rows = $this->model_extension_module_aw_redirect->getRedirects(['limit' => 1000000]);

        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['source', 'target', 'status_code', 'match_query', 'store_id', 'status']);

        foreach ($rows as $row) {
            fputcsv($out, [$row['source'], $row['target'], $row['status_code'], $row['match_query'], $row['store_id'], $row['status']]);
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        $this->response->addHeader('Content-Type: text/csv; charset=utf-8');
        $this->response->addHeader('Content-Disposition: attachment; filename="aw_redirect_' . date('Y-m-d_H-i-s') . '.csv"');
        $this->response->setOutput($csv);
    }

    public function importCsv(): void
    {
        $json = [];

        if (!$this->validatePermission()) {
            $json['error'] = $this->language->get('error_permission');
        } elseif (isset($this->request->files['import_file']) && is_uploaded_file($this->request->files['import_file']['tmp_name'])) {
            $this->load->model('extension/module/' . $this->moduleName);

            $handle = fopen($this->request->files['import_file']['tmp_name'], 'r');
            $imported = 0;
            $header = fgetcsv($handle);

            if ($header && in_array('source', array_map('strtolower', $header), true)) {
                while (($cols = fgetcsv($handle)) !== false) {
                    if (empty($cols[0]) || empty($cols[1])) {
                        continue;
                    }

                    $this->model_extension_module_aw_redirect->addRedirect([
                        'source' => $cols[0],
                        'target' => $cols[1],
                        'status_code' => isset($cols[2]) && in_array((int) $cols[2], $this->statusCodes, true) ? (int) $cols[2] : 301,
                        'match_query' => (int) ($cols[3] ?? 0),
                        'store_id' => (int) ($cols[4] ?? 0),
                        'status' => isset($cols[5]) ? (int) $cols[5] : 1,
                    ]);
                    $imported++;
                }
            } else {
                $json['error'] = $this->language->get('error_import_format');
            }

            fclose($handle);

            if (!isset($json['error'])) {
                $this->clearCache();
                $json['success'] = sprintf($this->language->get('text_import_success'), $imported);
            }
        } else {
            $json['error'] = $this->language->get('error_import_file');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // --- Validation ---

    private function validatePermission(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    private function validateRule(int $redirectId): bool
    {
        $this->validatePermission();

        $source = trim($this->request->post['source'] ?? '');
        $target = trim($this->request->post['target'] ?? '');
        $code = (int) ($this->request->post['status_code'] ?? 0);

        if ($source === '') {
            $this->error['source'] = $this->language->get('error_source');
        }

        if ($code !== 410 && $target === '') {
            $this->error['target'] = $this->language->get('error_target');
        }

        if (!in_array($code, $this->statusCodes, true)) {
            $this->error['warning'] = $this->language->get('error_code');
        }

        if ($source !== '' && $target !== '') {
            $sourcePath = parse_url($source, PHP_URL_PATH) ?: $source;
            $targetPath = parse_url($target, PHP_URL_PATH) ?: $target;

            if ($this->model_extension_module_aw_redirect->normalizeUrl($sourcePath) === $this->model_extension_module_aw_redirect->normalizeUrl($targetPath)) {
                $this->error['target'] = $this->language->get('error_loop');
            }
        }

        if ($source !== '' && $this->model_extension_module_aw_redirect->sourceExists($source, !empty($this->request->post['match_query']) ? 1 : 0, (int) ($this->request->post['store_id'] ?? 0), $redirectId)) {
            $this->error['source'] = $this->language->get('error_duplicate');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    private function defaultIgnore(): string
    {
        return "*.php\n*.env\n/wp-*\n*.aspx\n/feed/*";
    }

    private function renderPage(string $view): void
    {
        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/' . $view, $this->params));
    }

    // --- Admin menu (event: admin/view/common/column_left/before) ---

    public function menu(&$route, &$data): void
    {
        if (!$this->moduleConfig->get('status', false) || !isset($data['menus'])) {
            return;
        }

        if (!$this->user->hasPermission('access', 'extension/module/' . $this->moduleName)) {
            return;
        }

        $data['menus'][] = [
            'id' => 'menu-aw-redirect',
            'icon' => 'fa-exchange',
            'name' => $this->language->get('text_menu'),
            'href' => '',
            'children' => [
                [
                    'name' => $this->language->get('text_list'),
                    'href' => $this->link(),
                    'children' => [],
                ],
                [
                    'name' => $this->language->get('text_log'),
                    'href' => $this->link('/log'),
                    'children' => [],
                ],
                [
                    'name' => $this->language->get('text_settings'),
                    'href' => $this->link('/settings'),
                    'children' => [],
                ],
            ],
        ];
    }

    // --- Install / Uninstall ---

    public function install(): void
    {
        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_redirect->createTables();

        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_' . $this->moduleName, ['module_' . $this->moduleName . '_status' => '1']);

        $this->installPermissions();
        $this->installEvents();

        if (!$this->awCore->getConfig($this->moduleName)->get('status')) {
            $this->awCore->setConfig($this->moduleName, [
                'status' => 1,
                'default_code' => 301,
                'log_404' => 1,
                'ignore_patterns' => $this->defaultIgnore(),
            ]);
        }

        $this->clearCache();
    }

    public function uninstall(): void
    {
        $this->getEventModel()->deleteEventByCode($this->moduleName);

        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_redirect->dropTables();

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);
        $this->awCore->removeConfig($this->moduleName);

        $this->clearCache();
    }

    private function installEvents(): void
    {
        $model = $this->getEventModel();
        $model->deleteEventByCode($this->moduleName);

        $model->addEvent($this->moduleName, 'catalog/controller/*/before', 'extension/module/' . $this->moduleName . '/redirect');
        $model->addEvent($this->moduleName, 'catalog/controller/error/not_found/before', 'extension/module/' . $this->moduleName . '/notFound');
        $model->addEvent($this->moduleName, 'admin/view/common/column_left/before', 'extension/module/' . $this->moduleName . '/menu');
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

    protected function installPermissions(): void
    {
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/' . $this->moduleName);
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/' . $this->moduleName);
    }
}
