<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */
class ControllerExtensionAwSitemapProviderHome extends Controller
{
    public function getCode(): string
    {
        return 'home';
    }

    public function getName(): string
    {
        return 'Home page';
    }

    public function getTotal(int $languageId): int
    {
        return 1;
    }

    public function getUrls(int $languageId, int $start, int $limit): array
    {
        if ($start > 0) {
            return [];
        }

        return [
            [
                'loc' => $this->url->link('common/home'),
                'lastmod' => date('Y-m-d\TH:i:sP'),
                'changefreq' => 'daily',
                'priority' => '1.0',
                'images' => [],
            ],
        ];
    }
}
