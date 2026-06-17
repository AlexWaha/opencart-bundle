<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */
class ControllerExtensionAwSitemapProviderProduct extends Controller
{
    public function getCode(): string
    {
        return 'product';
    }

    public function getName(): string
    {
        return 'Products';
    }

    public function getTotal(int $languageId): int
    {
        $this->load->model('extension/aw_sitemap/sitemap');

        return $this->model_extension_aw_sitemap_sitemap->getTotalProducts($languageId);
    }

    public function getUrls(int $languageId, int $start, int $limit): array
    {
        $this->load->model('extension/aw_sitemap/sitemap');

        $config = $this->awCore->getConfig('aw_sitemap');
        $withImages = (bool)$config->get('product_images', true);

        [$imageWidth, $imageHeight] = $this->getPopupSize();

        $products = $this->model_extension_aw_sitemap_sitemap->getProducts($languageId, $start, $limit);

        $urls = [];

        foreach ($products as $product) {
            $images = [];

            if ($withImages && $product['image']) {
                $imageUrl = $this->getCachedImageUrl($product['image'], $imageWidth, $imageHeight);

                if ($imageUrl) {
                    $caption = $this->cleanText($product['name']);
                    $images[] = [
                        'loc' => $imageUrl,
                        'caption' => $caption,
                        'title' => $caption,
                    ];
                }
            }

            $urls[] = [
                'loc' => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                'lastmod' => $this->formatDate($product['date_modified'], $product['date_added']),
                'changefreq' => 'weekly',
                'priority' => '1.0',
                'images' => $images,
            ];
        }

        return $urls;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function getPopupSize(): array
    {
        $theme = $this->config->get('config_theme');

        if ($this->awCore->isLegacy()) {
            $width = (int)$this->config->get($theme . '_image_popup_width');
            $height = (int)$this->config->get($theme . '_image_popup_height');
        } else {
            $width = (int)$this->config->get('theme_' . $theme . '_image_popup_width');
            $height = (int)$this->config->get('theme_' . $theme . '_image_popup_height');
        }

        return [$width ?: 500, $height ?: 500];
    }

    private function formatDate(string $modified, string $added): string
    {
        $date = ($modified && $modified !== '0000-00-00 00:00:00') ? $modified : $added;

        return date('Y-m-d\TH:i:sP', $date ? strtotime($date) : time());
    }

    /**
     * Cache-only image resolver: returns the cached image URL only when the
     * resized file already exists. It never calls the image resizer, so it is
     * safe to run across very large catalogs.
     */
    private function getCachedImageUrl(string $filename, int $width, int $height): string
    {
        if (!$filename || !is_file(DIR_IMAGE . $filename)) {
            return '';
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $base = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
        $cachedName = $base . '-' . $width . 'x' . $height . '.' . $extension;

        if (is_file(DIR_IMAGE . 'cache/' . $cachedName)) {
            return HTTPS_SERVER . 'image/cache/' . $cachedName;
        }

        return '';
    }

    private function cleanText(string $text): string
    {
        $text = strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = str_replace(['&', '<', '>', '"', "'"], ['&amp;', '&lt;', '&gt;', '&quot;', '&apos;'], $text);

        return trim($text);
    }
}
