<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */
class ControllerExtensionAwSitemapProviderManufacturer extends Controller
{
    public function getCode(): string
    {
        return 'manufacturer';
    }

    public function getName(): string
    {
        return 'Manufacturers';
    }

    public function getTotal(int $languageId): int
    {
        $this->load->model('extension/aw_sitemap/sitemap');

        return $this->model_extension_aw_sitemap_sitemap->getTotalManufacturers();
    }

    public function getUrls(int $languageId, int $start, int $limit): array
    {
        $this->load->model('extension/aw_sitemap/sitemap');

        $manufacturers = $this->model_extension_aw_sitemap_sitemap->getManufacturers($start, $limit);

        $urls = [];

        foreach ($manufacturers as $manufacturer) {
            $urls[] = [
                'loc' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']),
                'lastmod' => date('Y-m-d\TH:i:sP'),
                'changefreq' => 'weekly',
                'priority' => '0.7',
                'images' => [],
            ];
        }

        return $urls;
    }
}
