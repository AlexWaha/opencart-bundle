<?php

/**
 * Sitemap provider template.
 *
 * Drop a copy of this file into this folder (e.g. `blog.php`, `news.php`) to add
 * your own entities to the sitemap. The sitemap core scans this folder, loads
 * every `*.php` (files prefixed with `_` are skipped), instantiates the class and
 * drives it through the contract below. The core owns all sharding and looping -
 * a provider only returns URL rows.
 *
 * Naming: file `blog.php` -> class `ControllerExtensionAwSitemapProviderBlog`.
 * Multi-word file `landing_page.php` -> class `...ProviderLandingPage`.
 *
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */
class ControllerExtensionAwSitemapProviderExample extends Controller
{
    /**
     * Unique provider code. Used as the config key for enable/disable and as the
     * shard filename prefix in static mode.
     */
    public function getCode(): string
    {
        return 'example';
    }

    /**
     * Human label shown in the admin Providers tab.
     */
    public function getName(): string
    {
        return 'Example';
    }

    /**
     * Total number of URLs this provider will produce for the given language.
     * The core uses it to compute the number of shards.
     */
    public function getTotal(int $languageId): int
    {
        return 0;
    }

    /**
     * Return a slice of URL rows. Each row:
     *   [
     *     'loc'        => 'https://store/...',         // required, absolute URL
     *     'lastmod'    => '2026-06-16T12:00:00+00:00', // ISO 8601, optional
     *     'changefreq' => 'weekly',                    // optional
     *     'priority'   => '0.5',                       // optional
     *     'images'     => [                            // optional, product feeds only
     *       ['loc' => '...', 'caption' => '...', 'title' => '...'],
     *     ],
     *   ]
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUrls(int $languageId, int $start, int $limit): array
    {
        return [];
    }
}
