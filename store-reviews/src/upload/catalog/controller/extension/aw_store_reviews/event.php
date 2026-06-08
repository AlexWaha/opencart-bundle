<?php

/**
 * AW Store Reviews - Event Handler
 *
 * Injects the Store Reviews page URL into google_sitemap output via
 * OpenCart event system. No core file modifications needed.
 *
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwStoreReviewsEvent extends Controller
{
    public function sitemapAfter(&$route, &$data, &$output): void
    {
        $currentOutput = $this->response->getOutput();

        if (!is_string($currentOutput) || strpos($currentOutput, '</urlset>') === false) {
            return;
        }

        if (!$this->registry->has('awCore')) {
            return;
        }

        $config = $this->awCore->getConfig('aw_store_reviews');

        if (!$config->get('status', false)) {
            return;
        }

        $url = $this->url->link('extension/module/aw_store_reviews');

        $urlXml = '<url>';
        $urlXml .= '  <loc>' . $url . '</loc>';
        $urlXml .= '  <changefreq>monthly</changefreq>';
        $urlXml .= '  <priority>0.5</priority>';
        $urlXml .= '</url>';

        $currentOutput = str_replace('</urlset>', $urlXml . '</urlset>', $currentOutput);

        $this->response->setOutput($currentOutput);
    }
}
