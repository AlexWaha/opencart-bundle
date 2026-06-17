<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */
class ControllerExtensionAwSitemapProviderInformation extends Controller
{
    public function getCode(): string
    {
        return 'information';
    }

    public function getName(): string
    {
        return 'Information pages';
    }

    public function getTotal(int $languageId): int
    {
        $this->load->model('extension/aw_sitemap/sitemap');

        return $this->model_extension_aw_sitemap_sitemap->getTotalInformations($languageId);
    }

    public function getUrls(int $languageId, int $start, int $limit): array
    {
        $this->load->model('extension/aw_sitemap/sitemap');

        $informations = $this->model_extension_aw_sitemap_sitemap->getInformations($languageId, $start, $limit);

        $urls = [];

        foreach ($informations as $information) {
            $urls[] = [
                'loc' => $this->url->link('information/information', 'information_id=' . $information['information_id']),
                'lastmod' => date('Y-m-d\TH:i:sP'),
                'changefreq' => 'monthly',
                'priority' => '0.5',
                'images' => [],
            ];
        }

        return $urls;
    }
}
