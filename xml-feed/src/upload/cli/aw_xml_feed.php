<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

require_once __DIR__ . '/kernel.php';
require_once DIR_APPLICATION . 'controller/extension/feed/aw_xml_feed.php';

class AwXmlFeed extends Kernel
{
    public function run($feedId = null)
    {
        if ($feedId !== null) {
            $this->request->get['feed_id'] = (int) $feedId;
        }

        $this->initSeoUrl();

        $controller = new ControllerExtensionFeedAwXmlFeed($this->getRegistry());
        $controller->index();
    }

    private function initSeoUrl()
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
    $xmlFeed = new AwXmlFeed();
    $feedId = isset($argv[1]) ? (int) $argv[1] : null;
    $xmlFeed->run($feedId);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
