<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */
class ControllerExtensionFeedAwSitemap extends Controller
{
    private string $moduleName = 'aw_sitemap';

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

    public function index(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

        $this->params['error'] = $this->error;
        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

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
        $this->params['cancel'] = $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=feed', true);

        $this->params['status'] = $this->moduleConfig->get('status', false);
        $this->params['mode'] = $this->moduleConfig->get('mode', 'dynamic');
        $this->params['folder'] = $this->moduleConfig->get('folder', 'sitemap');
        $this->params['shard_size'] = $this->moduleConfig->get('shard_size', 5000);
        $this->params['product_images'] = $this->moduleConfig->get('product_images', true);
        $this->params['cache_enabled'] = $this->moduleConfig->get('cache_enabled', false);
        $this->params['cache_ttl'] = $this->moduleConfig->get('cache_ttl', 3600);
        $this->params['product_threshold'] = $this->moduleConfig->get('product_threshold', 1000);
        $this->params['languages_selected'] = $this->moduleConfig->get('languages', []);

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->params['providers'] = $this->getProviders();

        $this->load->model('extension/feed/' . $this->moduleName);
        $totalProducts = $this->model_extension_feed_aw_sitemap->getTotalProducts();
        $this->params['total_products'] = $totalProducts;
        $this->params['show_threshold_warning'] = ($this->params['mode'] === 'dynamic'
            && $totalProducts > (int)$this->params['product_threshold']);

        $root = str_replace('admin/', '', DIR_APPLICATION);
        $this->params['cron_command'] = 'php ' . $root . 'cli/aw_sitemap.php';

        $this->params['sitemap_url'] = HTTPS_CATALOG . $this->params['folder'] . '/sitemap.xml';
        $this->params['robots_line'] = 'Sitemap: ' . $this->params['sitemap_url'];

        // Dynamic mode serves the sitemap through the catalog route, so a rewrite
        // rule is required to expose it at the clean /{folder}/sitemap.xml path.
        // Static mode writes a real file and needs no rewrite.
        $generationRoute = 'index.php?route=extension/' . $this->moduleName . '/sitemap';
        $path = $this->params['folder'] . '/sitemap.xml';
        $this->params['rewrite_needed'] = $this->params['mode'] === 'dynamic';
        $this->params['rewrite_apache'] = 'RewriteRule ^' . $path . '$ ' . $generationRoute . ' [L]';
        $this->params['rewrite_nginx'] = "location = /" . $path . " {\n    try_files \$uri /" . $generationRoute . ";\n}";

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/feed/' . $this->moduleName . '/main', $this->params));
    }

    public function store(): void
    {
        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->awCore->setConfig($this->moduleName, $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                'extension/feed/' . $this->moduleName,
                $this->tokenData['param'],
                true
            ));
        }

        $this->index();
    }

    /**
     * Discover provider files for the admin Providers tab.
     *
     * @return array<int, array{code: string, name: string, enabled: bool}>
     */
    private function getProviders(): array
    {
        $dir = DIR_CATALOG . 'controller/extension/' . $this->moduleName . '/provider/';
        $status = $this->moduleConfig->get('provider_status', []);

        $providers = [];

        foreach (glob($dir . '*.php') as $file) {
            $name = basename($file, '.php');

            if ($name[0] === '_') {
                continue;
            }

            require_once $file;

            $class = 'ControllerExtensionAwSitemapProvider'
                . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

            if (!class_exists($class)) {
                continue;
            }

            $provider = new $class($this->registry);
            $code = $provider->getCode();

            $providers[] = [
                'code' => $code,
                'name' => $provider->getName(),
                'enabled' => !isset($status[$code]) || (bool)$status[$code],
            ];
        }

        return $providers;
    }

    private function validate(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/feed/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['folder'])) {
            $folder = trim($this->request->post['folder']);
            if (utf8_strlen($folder) < 2 || utf8_strlen($folder) > 64 || !preg_match('/^[a-z0-9_-]+$/i', $folder)) {
                $this->error['folder'] = $this->language->get('error_folder');
            }
        }

        if (isset($this->request->post['shard_size'])) {
            $shardSize = (int)$this->request->post['shard_size'];
            if ($shardSize < 100 || $shardSize > 50000) {
                $this->error['shard_size'] = $this->language->get('error_shard_size');
            }
        }

        if (isset($this->request->post['cache_ttl'])) {
            if ((int)$this->request->post['cache_ttl'] < 0) {
                $this->error['cache_ttl'] = $this->language->get('error_cache_ttl');
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    public function install(): void
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'feed_' . $this->moduleName,
            ['feed_' . $this->moduleName . '_status' => '1']
        );

        if ($this->awCore->isLegacy()) {
            $this->model_setting_setting->editSetting($this->moduleName, [$this->moduleName . '_status' => '1']);
        }

        $this->installPermissions();
    }

    public function uninstall(): void
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('feed_' . $this->moduleName);

        if ($this->awCore->isLegacy()) {
            $this->model_setting_setting->deleteSetting($this->moduleName);
        }

        $this->awCore->removeConfig($this->moduleName);
    }

    private function installPermissions(): void
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
}
