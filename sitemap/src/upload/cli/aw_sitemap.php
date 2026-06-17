<?php

/**
 * Sitemap static generator (cron entry point).
 *
 * Usage: php cli/aw_sitemap.php
 *
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

require_once __DIR__ . '/kernel.php';
require_once DIR_APPLICATION . 'controller/extension/aw_sitemap/sitemap.php';

class AwSitemap extends Kernel
{
    public function run(): void
    {
        $this->initSeoUrl();

        $controller = new ControllerExtensionAwSitemapSitemap($this->getRegistry());
        $controller->index();
    }

    private function initSeoUrl(): void
    {
        $registry = $this->getRegistry();
        $config = $registry->get('config');

        $config->set('config_seo_url', true);

        require_once DIR_APPLICATION . 'controller/startup/seo_url.php';

        $seoUrl = new ControllerStartupSeoUrl($registry);
        $seoUrl->index();
    }
}

try {
    $sitemap = new AwSitemap();
    $sitemap->run();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
