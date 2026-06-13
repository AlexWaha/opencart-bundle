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

    private ?\Alexwaha\Config $moduleConfig = null;

    private static bool $handled = false;

    public function __construct($registry)
    {
        parent::__construct($registry);

        if ($this->registry->has('awCore')) {
            $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
        }
    }

    private function isEnabled(): bool
    {
        return $this->moduleConfig !== null && $this->moduleConfig->get('status', false);
    }

    private function getModel()
    {
        $this->load->model('extension/module/' . $this->moduleName);

        return $this->model_extension_module_aw_redirect;
    }

    /**
     * Event: catalog/controller/*\/before  (run once per request).
     * Matches the requested URL against active redirect rules.
     */
    public function redirect(&$route, &$data): void
    {
        if (self::$handled) {
            return;
        }

        self::$handled = true;

        if (!$this->isEnabled() || ($this->request->server['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            return;
        }

        [$path, $query] = $this->requestParts();

        if ($path === '' || $path === '/') {
            return;
        }

        $storeId = (int) $this->config->get('config_store_id');
        $match = $this->getModel()->getMatch($path, $query, $storeId);

        if (!$match) {
            return;
        }

        $model = $this->getModel();
        $targetPath = parse_url($match['target'], PHP_URL_PATH) ?: $match['target'];

        if ($model->normalizeUrl($targetPath) === $model->normalizeUrl($path)) {
            return;
        }

        $model->incrementHit((int) $match['redirect_id']);

        $code = (int) $match['status_code'];

        if ($code === 410) {
            $this->sendGone();
            return;
        }

        $this->response->redirect($this->buildTargetUrl($match['target']), $code);
    }

    /**
     * Event: catalog/controller/error/not_found/before.
     * Logs the missing URL for later resolving.
     */
    public function notFound(&$route, &$data): void
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('log_404', true)) {
            return;
        }

        [$path, $query] = $this->requestParts();
        $url = $path . ($query !== '' ? '?' . $query : '');

        if ($url === '' || $this->isIgnored($url)) {
            return;
        }

        $this->getModel()->logNotFound(
            $url,
            $this->request->server['HTTP_REFERER'] ?? '',
            $this->request->server['HTTP_USER_AGENT'] ?? '',
            (int) $this->config->get('config_store_id'),
            (int) $this->config->get('config_language_id')
        );
    }

    /**
     * Extract the store-root-relative path and query from the raw request.
     */
    private function requestParts(): array
    {
        $uri = $this->request->server['REQUEST_URI'] ?? '';
        $parts = explode('?', $uri, 2);
        $path = rawurldecode($parts[0]);
        $query = $parts[1] ?? '';

        $base = rtrim((string) parse_url(defined('HTTP_SERVER') ? HTTP_SERVER : '', PHP_URL_PATH), '/');

        if ($base !== '' && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }

        return ['/' . ltrim($path, '/'), $query];
    }

    private function buildTargetUrl(string $target): string
    {
        if (preg_match('~^https?://~i', $target)) {
            return $target;
        }

        $server = !empty($this->request->server['HTTPS']) ? HTTPS_SERVER : HTTP_SERVER;

        return rtrim($server, '/') . '/' . ltrim($target, '/');
    }

    private function isIgnored(string $url): bool
    {
        $patterns = $this->moduleConfig->get('ignore_patterns', '');

        if (!$patterns) {
            return false;
        }

        foreach (preg_split('/[\r\n,]+/', $patterns) as $pattern) {
            $pattern = trim($pattern);

            if ($pattern !== '' && fnmatch($pattern, $url, FNM_CASEFOLD)) {
                return true;
            }
        }

        return false;
    }

    private function sendGone(): void
    {
        $this->response->addHeader(($this->request->server['SERVER_PROTOCOL'] ?? 'HTTP/1.1') . ' 410 Gone');
        $this->response->setOutput('<!doctype html><html lang="en"><head><meta charset="utf-8"><title>410 Gone</title></head><body><h1>410 Gone</h1><p>This page has been permanently removed.</p></body></html>');
        $this->response->output();
        exit;
    }
}
