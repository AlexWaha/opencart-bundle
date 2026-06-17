<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */
class ControllerExtensionAwSitemapProviderCategory extends Controller
{
    public function getCode(): string
    {
        return 'category';
    }

    public function getName(): string
    {
        return 'Categories';
    }

    public function getTotal(int $languageId): int
    {
        $this->load->model('extension/aw_sitemap/sitemap');

        return $this->model_extension_aw_sitemap_sitemap->getTotalCategories($languageId);
    }

    public function getUrls(int $languageId, int $start, int $limit): array
    {
        $this->load->model('extension/aw_sitemap/sitemap');

        $categories = $this->model_extension_aw_sitemap_sitemap->getCategories($languageId, $start, $limit);

        $urls = [];

        foreach ($categories as $category) {
            $path = $category['path'] ?: $category['category_id'];

            $urls[] = [
                'loc' => $this->url->link('product/category', 'path=' . $path),
                'lastmod' => $this->formatDate($category['date_modified'], $category['date_added']),
                'changefreq' => 'weekly',
                'priority' => '0.7',
                'images' => [],
            ];
        }

        return $urls;
    }

    private function formatDate(string $modified, string $added): string
    {
        $date = ($modified && $modified !== '0000-00-00 00:00:00') ? $modified : $added;

        return date('Y-m-d\TH:i:sP', $date ? strtotime($date) : time());
    }
}
