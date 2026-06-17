<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */
class ControllerExtensionAwSitemapSitemap extends Controller
{
    private const READ_CHUNK = 5000;

    private string $moduleName = 'aw_sitemap';

    private \Alexwaha\Config $moduleConfig;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
    }

    public function index(): void
    {
        $this->load->model('extension/aw_sitemap/sitemap');
        $this->load->language('extension/aw_sitemap/sitemap');

        $isCli = php_sapi_name() === 'cli';

        if (!$this->moduleConfig->get('status', false)) {
            return;
        }

        $mode = $this->moduleConfig->get('mode', 'dynamic');

        if ($isCli || $mode === 'static') {
            $result = $this->generateStatic();
            $this->outputResult($result, $isCli);

            return;
        }

        $this->generateDynamic();
    }

    private function generateDynamic(): void
    {
        $languageId = (int)$this->config->get('config_language_id');

        $cacheFile = DIR_CACHE . 'aw_sitemap.' . $languageId . '.xml';
        $ttl = (int)$this->moduleConfig->get('cache_ttl', 3600);

        if ($this->moduleConfig->get('cache_enabled', false) && is_file($cacheFile)
            && (filemtime($cacheFile) + $ttl) > time()) {
            $this->outputXml(file_get_contents($cacheFile));

            return;
        }

        $urls = [];

        foreach ($this->getProviders() as $provider) {
            $total = $provider->getTotal($languageId);

            for ($start = 0; $start < $total; $start += self::READ_CHUNK) {
                $urls = array_merge($urls, $provider->getUrls($languageId, $start, self::READ_CHUNK));
            }
        }

        $xml = $this->renderUrlset($urls);

        if ($this->moduleConfig->get('cache_enabled', false)) {
            file_put_contents($cacheFile, $xml, LOCK_EX);
        }

        $this->outputXml($xml);
    }

    private function generateStatic(): array
    {
        $folder = $this->moduleConfig->get('folder', 'sitemap');
        $shardSize = max(100, min(50000, (int)$this->moduleConfig->get('shard_size', 5000)));

        $baseDir = str_replace('catalog', $folder, DIR_APPLICATION);

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        $providers = $this->getProviders();
        $languages = $this->getLanguages();

        $shards = [];

        foreach ($languages as $language) {
            $languageId = (int)$language['language_id'];

            $this->config->set('config_language_id', $languageId);
            $this->config->set('config_language', $language['code']);

            foreach ($providers as $code => $provider) {
                $total = $provider->getTotal($languageId);

                if ($total <= 0) {
                    continue;
                }

                $pages = (int)ceil($total / $shardSize);

                for ($page = 0; $page < $pages; $page++) {
                    $urls = $provider->getUrls($languageId, $page * $shardSize, $shardSize);

                    if (!$urls) {
                        continue;
                    }

                    $filename = $language['code'] . '-' . $code . '-' . $page . '.xml';

                    file_put_contents($baseDir . $filename, $this->renderUrlset($urls), LOCK_EX);

                    $shards[] = [
                        'loc' => $this->escapeXml(HTTPS_SERVER . $folder . '/' . $filename),
                        'lastmod' => date('Y-m-d\TH:i:sP'),
                    ];
                }
            }
        }

        $indexFile = $baseDir . 'sitemap.xml';
        $indexXml = $this->awCore->render('extension/' . $this->moduleName . '/index', ['shards' => $shards]);

        file_put_contents($indexFile, $indexXml, LOCK_EX);

        return [
            'index_url' => HTTPS_SERVER . $folder . '/sitemap.xml',
            'shard_count' => count($shards),
        ];
    }

    /**
     * Discover enabled provider instances. A provider is any `*.php` controller in
     * the provider folder (files prefixed with `_` are templates and skipped).
     *
     * @return array<string, Controller>
     */
    private function getProviders(): array
    {
        $dir = DIR_APPLICATION . 'controller/extension/' . $this->moduleName . '/provider/';
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

            if (isset($status[$code]) && !$status[$code]) {
                continue;
            }

            $providers[$code] = $provider;
        }

        ksort($providers);

        return $providers;
    }

    private function getLanguages(): array
    {
        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();
        $selected = $this->moduleConfig->get('languages', []);

        if (!$selected) {
            return $languages;
        }

        return array_filter($languages, static function ($language) use ($selected) {
            return in_array($language['language_id'], $selected);
        });
    }

    private function renderUrlset(array $urls): string
    {
        return $this->awCore->render('extension/' . $this->moduleName . '/urlset', [
            'urls' => $this->escapeUrls($urls),
        ]);
    }

    /**
     * Escape `&` in loc/image URLs so the XML stays well-formed. Text fields
     * (captions/titles) are already escaped by the providers.
     */
    private function escapeUrls(array $urls): array
    {
        foreach ($urls as &$url) {
            $url['loc'] = $this->escapeXml($url['loc']);

            if (!empty($url['images'])) {
                foreach ($url['images'] as &$image) {
                    $image['loc'] = $this->escapeXml($image['loc']);
                }
                unset($image);
            }
        }
        unset($url);

        return $urls;
    }

    private function escapeXml(string $value): string
    {
        return str_replace('&', '&amp;', $value);
    }

    private function outputXml(string $xml): void
    {
        $this->response->addHeader('Content-Type: application/xml; charset=utf-8');
        $this->response->setOutput($xml);
    }

    private function outputResult(array $result, bool $isCli): void
    {
        $data = [
            'index_url' => $result['index_url'],
            'shard_count' => $result['shard_count'],
        ];

        if ($isCli) {
            echo $this->awCore->render('extension/' . $this->moduleName . '/result_cli', $data);

            return;
        }

        $this->response->addHeader('Content-Type: text/html; charset=utf-8');
        $this->response->setOutput($this->awCore->render('extension/' . $this->moduleName . '/result', $data));
    }
}
