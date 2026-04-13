<?php

class ControllerExtensionAwMicrodataMicrodata extends Controller
{
    private string $moduleName = 'aw_microdata';
    private ?\Alexwaha\Config $microdataConfig = null;

    public function __construct($registry)
    {
        parent::__construct($registry);

        if ($this->registry->has('awCore')) {
            $this->microdataConfig = $this->awCore->getConfig($this->moduleName);
        }
    }

    private function isEnabled(): bool
    {
        return $this->microdataConfig !== null
            && $this->microdataConfig->get('status', false);
    }

    private function buildJsonLd($schema): string
    {
        if (empty($schema)) {
            return '';
        }

        return '<script type="application/ld+json">'
            . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            . '</script>';
    }

    private function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = str_replace('><', '> <', $text);
        $text = str_replace(['<br />', '<br>', '<br/>'], ' ', $text);
        $text = strip_tags($text);
        $text = preg_replace('/[\r\n\t]+/', ' ', $text);
        $text = preg_replace('/\s{2,}/', ' ', $text);

        return trim($text);
    }

    private function extractVideoObjects(string $html, string $fallbackName = ''): array
    {
        $videos = [];
        $seen = [];

        if (preg_match_all(
            '/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w\-]{11})/i',
            $html,
            $matches
        )) {
            foreach ($matches[1] as $videoId) {
                if (isset($seen['yt_' . $videoId])) {
                    continue;
                }
                $seen['yt_' . $videoId] = true;

                $videos[] = [
                    '@type'        => 'VideoObject',
                    'name'         => ($fallbackName ? $fallbackName . ' - Video' : 'Video'),
                    'thumbnailUrl' => 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg',
                    'embedUrl'     => 'https://www.youtube.com/embed/' . $videoId,
                    'contentUrl'   => 'https://www.youtube.com/watch?v=' . $videoId,
                ];
            }
        }

        if (preg_match_all('/vimeo\.com\/(\d+)/i', $html, $matches)) {
            foreach ($matches[1] as $videoId) {
                if (isset($seen['vi_' . $videoId])) {
                    continue;
                }
                $seen['vi_' . $videoId] = true;

                $videos[] = [
                    '@type'      => 'VideoObject',
                    'name'       => ($fallbackName ? $fallbackName . ' - Video' : 'Video'),
                    'embedUrl'   => 'https://player.vimeo.com/video/' . $videoId,
                    'contentUrl' => 'https://vimeo.com/' . $videoId,
                ];
            }
        }

        return $videos;
    }

    private function extractImageObjects(string $html): array
    {
        $images = [];

        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $src = trim($src);
                if (!$src) {
                    continue;
                }

                $images[] = [
                    '@type'      => 'ImageObject',
                    'contentUrl' => $src,
                    'url'        => $src,
                ];
            }
        }

        return $images;
    }

    private function getShopUrl(): string
    {
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            return $this->config->get('config_ssl');
        }

        return $this->config->get('config_url');
    }

    private function getCurrencyCode(): string
    {
        return $this->session->data['currency']
            ?? $this->config->get('config_currency');
    }

    private function parsePrice(string $formatted): float
    {
        $formatted = str_replace(',', '.', $formatted);

        return (float)rtrim(preg_replace('/[^\d.]/', '', $formatted), '.');
    }

    private function getOrganizationData(): array
    {
        $shopUrl = rtrim($this->getShopUrl(), '/');
        $storeName = $this->cleanText($this->config->get('config_name'));

        $storeType = $this->microdataConfig->get('store_type', 'Store');
        $langId = (int)$this->config->get('config_language_id');
        $legalNameData = $this->microdataConfig->get('legal_name', '');
        $legalName = is_array($legalNameData) ? ($legalNameData[$langId] ?? reset($legalNameData) ?: $storeName) : ($legalNameData ?: $storeName);
        $email = $this->microdataConfig->get('email', $this->config->get('config_email'));

        $description = $this->cleanText($this->config->get('config_meta_description'));

        $org = [
            '@type'       => $storeType,
            'name'        => $storeName,
            'description' => $description ?: $storeName,
            'legalName'   => (string)$legalName,
            'url'         => $shopUrl,
            'email'       => $email,
        ];

        $priceRange = $this->microdataConfig->get('price_range_value', '');
        if ($priceRange) {
            $org['priceRange'] = $priceRange;
        }

        $logoConfig = $this->microdataConfig->get('logo', '');
        $logoPath = $logoConfig ?: $this->config->get('config_logo');

        if ($logoPath) {
            if (strpos($logoPath, 'http') === 0) {
                $org['logo'] = $logoPath;
            } else {
                $org['logo'] = $shopUrl . '/' . ltrim(str_replace(' ', '%20', $logoPath), '/');
            }
        }

        if (!empty($org['logo'])) {
            $org['image'] = $org['logo'];
        }

        $phones = $this->microdataConfig->get('phones', []);

        if ($phones && is_array($phones)) {
            $phoneList = [];

            foreach ($phones as $phone) {
                $num = is_array($phone) ? ($phone['number'] ?? '') : (string)$phone;

                if ($num) {
                    $phoneList[] = $num;
                }
            }

            if (count($phoneList) === 1) {
                $org['telephone'] = $phoneList[0];
            } elseif (count($phoneList) > 1) {
                $org['telephone'] = $phoneList;
            }
        }

        $social = $this->microdataConfig->get('social', []);

        if ($social && is_array($social)) {
            $socialLinks = [];

            foreach ($social as $item) {
                $url = is_array($item) ? ($item['url'] ?? '') : (string)$item;

                if ($url) {
                    $socialLinks[] = $url;
                }
            }

            if ($socialLinks) {
                $org['sameAs'] = array_values($socialLinks);
            }
        }

        $address = $this->buildPostalAddress();
        if ($address) {
            $org['address'] = $address;
        }

        $geo = $this->buildGeoCoordinates();
        if ($geo) {
            $org['geo'] = $geo;
        }

        $schedule = $this->buildOpeningHours();
        if ($schedule) {
            $org['openingHoursSpecification'] = $schedule;
        }

        if (!empty($org['telephone'])) {
            $firstPhone = is_array($org['telephone']) ? $org['telephone'][0] : $org['telephone'];
            $org['contactPoint'] = [
                '@type'       => 'ContactPoint',
                'telephone'   => $firstPhone,
                'contactType' => 'customer service',
            ];
        }

        $currency = $this->microdataConfig->get('currency', '');
        if ($currency) {
            $org['currenciesAccepted'] = $currency;
        }

        $payment = $this->microdataConfig->get('payment_methods', '');
        if ($payment) {
            $org['paymentAccepted'] = $payment;
        }

        return $org;
    }

    private function buildPostalAddress(): array
    {
        $langId = (int)$this->config->get('config_language_id');
        $addressData = $this->microdataConfig->get('address', []);

        if (isset($addressData[$langId]) && is_array($addressData[$langId])) {
            $addr = $addressData[$langId];
        } elseif (isset($addressData['street'])) {
            $addr = $addressData;
        } else {
            $addr = is_array($addressData) ? reset($addressData) : [];
        }

        if (!is_array($addr)) {
            return [];
        }

        $street = $addr['street'] ?? '';
        $locality = $addr['city'] ?? '';
        $region = $addr['region'] ?? '';
        $postalCode = $addr['zip'] ?? '';
        $country = $addr['country'] ?? '';

        if (!$country) {
            $this->load->model('localisation/country');
            $countryInfo = $this->model_localisation_country->getCountry((int)$this->config->get('config_country_id'));
            $country = $countryInfo['iso_code_2'] ?? '';
        }

        if (!$street && !$locality) {
            return [];
        }

        $address = ['@type' => 'PostalAddress'];

        if ($street) {
            $address['streetAddress'] = $street;
        }
        if ($locality) {
            $address['addressLocality'] = $locality;
        }
        if ($region) {
            $address['addressRegion'] = $region;
        }
        if ($postalCode) {
            $address['postalCode'] = $postalCode;
        }
        if ($country) {
            $address['addressCountry'] = $country;
        }

        return $address;
    }

    private function buildGeoCoordinates(): array
    {
        $geo = $this->microdataConfig->get('geo', []);
        $lat = is_array($geo) ? ($geo['lat'] ?? '') : '';
        $lng = is_array($geo) ? ($geo['lon'] ?? '') : '';

        if (!$lat || !$lng) {
            return [];
        }

        return [
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float)$lat,
            'longitude' => (float)$lng,
        ];
    }

    private function buildOpeningHours(): array
    {
        $schedule = $this->microdataConfig->get('schedule', []);

        if (empty($schedule) || !is_array($schedule)) {
            return [];
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $specs = [];

        foreach ($days as $index => $day) {
            if (isset($schedule[$index])) {
                $item = $schedule[$index];

                if (is_array($item)) {
                    if (!empty($item['closed'])) {
                        continue;
                    }
                    $open = $item['open'] ?? '';
                    $close = $item['close'] ?? '';
                } else {
                    $parts = explode('-', (string)$item);
                    if (count($parts) !== 2) {
                        continue;
                    }
                    $open = trim($parts[0]);
                    $close = trim($parts[1]);
                }

                if ($open && $close) {
                    $specs[] = [
                        '@type'     => 'OpeningHoursSpecification',
                        'dayOfWeek' => $day,
                        'opens'     => $open,
                        'closes'    => $close,
                    ];
                }
            }
        }

        return $specs;
    }

    private function getAggregateRatingData(): array
    {
        $this->load->model('extension/aw_microdata/microdata');

        $rating = $this->model_extension_aw_microdata_microdata->getStoreAggregateRating();

        $fakeCount = (int)$this->microdataConfig->get('fake_count', 0);
        $fakeBoost = (float)$this->microdataConfig->get('fake_boost', 0);

        $count = $rating['count'] + $fakeCount;
        $avg = $rating['avg'];

        if ($fakeBoost > 0 && $avg > 0) {
            $avg = min(5, $avg + $fakeBoost);
        } elseif ($fakeBoost > 0 && $avg == 0) {
            $avg = min(5, $fakeBoost);
        }

        if ($count <= 0) {
            return [];
        }

        return [
            '@type'       => 'AggregateRating',
            'ratingValue' => round($avg, 1),
            'bestRating'  => 5,
            'ratingCount' => $count,
        ];
    }

    private function getReviewsData(int $limit = 10): array
    {
        $this->load->model('extension/aw_microdata/microdata');

        $reviewSource = $this->microdataConfig->get('review_source', 'store');

        if ($reviewSource === 'store' || $reviewSource === 'both') {
            $reviews = $this->model_extension_aw_microdata_microdata->getStoreReviews($limit);
        } else {
            return [];
        }

        $result = [];

        foreach ($reviews as $review) {
            $item = [
                '@type'         => 'Review',
                'author'        => [
                    '@type' => 'Person',
                    'name'  => $this->cleanText($review['author']),
                ],
                'datePublished' => date('c', strtotime($review['date_added'])),
                'reviewRating'  => [
                    '@type'       => 'Rating',
                    'ratingValue' => (int)$review['rating'],
                    'bestRating'  => 5,
                ],
            ];

            $text = $this->cleanText($review['text']);
            if ($text) {
                $item['reviewBody'] = $text;
            }

            $result[] = $item;
        }

        return $result;
    }

    public function getOrganization(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('organization_enabled', true)) {
            return '';
        }

        $schema = ['@context' => 'https://schema.org'] + $this->getOrganizationData();

        $aggregateRating = $this->getAggregateRatingData();
        if ($aggregateRating) {
            $schema['aggregateRating'] = $aggregateRating;
        }

        return $this->buildJsonLd($schema);
    }

    public function getOg($data = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('og_enabled', true)) {
            return '';
        }

        if (!is_array($data)) {
            $data = [];
        }

        $shopUrl = rtrim($this->getShopUrl(), '/');
        $storeName = $this->cleanText($this->config->get('config_name'));

        $title = isset($data['heading_title']) ? $this->cleanText($data['heading_title']) : $storeName;
        $description = '';

        if (!empty($data['meta_description'])) {
            $description = $this->cleanText($data['meta_description']);
        } elseif (!empty($data['description'])) {
            $desc = $this->cleanText($data['description']);
            $description = mb_strlen($desc, 'UTF-8') > 290 ? mb_substr($desc, 0, 290, 'UTF-8') : $desc;
        }

        if (!$description) {
            $description = $title;
        }

        $url = $shopUrl . $this->request->server['REQUEST_URI'];
        $ogType = $this->microdataConfig->get('og_type', 'website');
        $locale = strtolower($this->session->data['language'] ?? 'uk-ua');

        $image = '';

        if (!empty($data['image'])) {
            $image = $data['image'];
        } elseif (!empty($data['thumb'])) {
            $image = $data['thumb'];
        } else {
            $logoPath = $this->config->get('config_logo');
            if ($logoPath) {
                $image = $shopUrl . '/image/' . $logoPath;
            }
        }

        $image = str_replace(' ', '%20', $image);

        $tags = [];
        $tags[] = '<meta property="og:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">';
        $tags[] = '<meta property="og:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '">';
        $tags[] = '<meta property="og:url" content="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
        $tags[] = '<meta property="og:site_name" content="' . htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8') . '">';
        $tags[] = '<meta property="og:locale" content="' . htmlspecialchars($locale, ENT_QUOTES, 'UTF-8') . '">';

        if ($image) {
            $tags[] = '<meta property="og:image" content="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '">';
        }

        $route = $this->request->get['route'] ?? 'common/home';

        if ($route === 'product/product') {
            $ogType = 'product';

            if (!empty($data['price'])) {
                $price = $this->parsePrice($data['price']);
                if ($price > 0) {
                    $tags[] = '<meta property="product:price:amount" content="' . $price . '">';
                    $tags[] = '<meta property="product:price:currency" content="' . $this->getCurrencyCode() . '">';
                }
            }
        } elseif ($route === 'information/information' || $route === 'blog/article') {
            $ogType = 'article';
        }

        $tags[] = '<meta property="og:type" content="' . $ogType . '">';

        $tags[] = '<meta name="twitter:card" content="' . htmlspecialchars($this->microdataConfig->get('twitter_card', 'summary_large_image'), ENT_QUOTES, 'UTF-8') . '">';
        $twitterSite = $this->microdataConfig->get('twitter_username', '');
        if ($twitterSite) {
            $tags[] = '<meta name="twitter:site" content="' . htmlspecialchars($twitterSite, ENT_QUOTES, 'UTF-8') . '">';
        }

        return implode("\n", $tags);
    }

    public function getWebsite(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('website_enabled', true)) {
            return '';
        }

        $shopUrl = rtrim($this->getShopUrl(), '/');
        $storeName = $this->cleanText($this->config->get('config_name'));
        $altName = $this->microdataConfig->get('website_alt_name', '');

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => $storeName,
            'url'      => $shopUrl,
        ];

        if ($altName) {
            $schema['alternateName'] = $altName;
        }

        $schema['potentialAction'] = [
            '@type'       => 'SearchAction',
            'target'      => $shopUrl . '/index.php?route=product/search&search={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ];

        $schema['publisher'] = [
            '@type' => 'Organization',
            'name'  => $storeName,
            'url'   => $shopUrl,
        ];

        $logoPath = $this->config->get('config_logo');

        if ($logoPath) {
            $schema['publisher']['logo'] = str_replace(' ', '%20', $shopUrl . '/image/' . $logoPath);
        }

        return $this->buildJsonLd($schema);
    }

    public function getProduct($data = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('product_schema', true)) {
            return '';
        }

        if (!is_array($data) || empty($data['product_id'])) {
            return '';
        }

        $this->load->model('extension/aw_microdata/microdata');

        $shopUrl = rtrim($this->getShopUrl(), '/');
        $storeName = $this->cleanText($this->config->get('config_name'));

        $name = $this->cleanText($data['heading_title'] ?? $data['name'] ?? '');
        $description = $this->cleanText($data['description'] ?? '');
        $productId = (int)$data['product_id'];

        // Load product identifiers directly from DB (sku/upc/ean/mpn/isbn are not available in $data)
        $this->load->model('catalog/product');
        $productInfo = $this->model_catalog_product->getProduct($productId);

        if (!$name) {
            return '';
        }

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $name,
            'description' => $description ?: $name,
        ];

        $images = [];

        if (!empty($data['popup'])) {
            $images[] = str_replace(' ', '%20', $data['popup']);
        } elseif (!empty($data['thumb'])) {
            $images[] = str_replace(' ', '%20', $data['thumb']);
        }

        if ($this->microdataConfig->get('all_images', false) && !empty($data['images'])) {
            foreach ($data['images'] as $img) {
                $imgUrl = !empty($img['popup']) ? $img['popup'] : (!empty($img['thumb']) ? $img['thumb'] : '');
                if ($imgUrl) {
                    $images[] = str_replace(' ', '%20', $imgUrl);
                }
            }
        }

        $images = array_unique($images);

        if ($images) {
            $schema['image'] = count($images) === 1 ? reset($images) : array_values($images);
        }

        $brandName = $this->cleanText($data['manufacturer'] ?? $productInfo['manufacturer'] ?? '');

        if ($brandName) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name'  => $brandName,
            ];
        }

        $model = $this->cleanText($productInfo['model'] ?? '');
        $sku = $this->cleanText($productInfo['sku'] ?? '') ?: $model;
        $mpn = $this->cleanText($productInfo['mpn'] ?? '') ?: $model;

        if ($sku) {
            $schema['sku'] = $sku;
        }
        if ($mpn) {
            $schema['mpn'] = $mpn;
        }

        if (!empty($productInfo['ean'])) {
            $schema['gtin'] = $this->cleanText($productInfo['ean']);
        } elseif (!empty($productInfo['upc'])) {
            $schema['gtin'] = $this->cleanText($productInfo['upc']);
        } elseif (!empty($productInfo['isbn'])) {
            $schema['gtin'] = $this->cleanText($productInfo['isbn']);
        }

        $priceValue = 0;
        $currency = $this->getCurrencyCode();

        if (!empty($data['special'])) {
            $priceValue = $this->parsePrice($data['special']);
        } elseif (!empty($data['price'])) {
            $priceValue = $this->parsePrice($data['price']);
        }

        if ($priceValue > 0) {
            $quantity = $data['quantity'] ?? 0;
            $alwaysInStock = $this->microdataConfig->get('force_instock', false);
            $availability = ($alwaysInStock || $quantity > 0)
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock';

            $productUrl = $this->url->link('product/product', 'product_id=' . $productId);

            if (!empty($data['breadcrumbs'])) {
                $lastBreadcrumb = end($data['breadcrumbs']);
                if (!empty($lastBreadcrumb['href'])) {
                    $productUrl = $lastBreadcrumb['href'];
                }
            }

            $offer = [
                '@type'           => 'Offer',
                'url'             => $productUrl,
                'priceCurrency'   => $currency,
                'price'           => $priceValue,
                'availability'    => $availability,
                'priceValidUntil' => date('Y-m-d', strtotime('+1 year')),
                'seller'          => [
                    '@type' => 'Organization',
                    'name'  => $storeName,
                ],
            ];

            if ($this->microdataConfig->get('unit_price', false)) {
                $unitCode = $this->microdataConfig->get('unit_code', 'LTR');
                $offer['priceSpecification'] = [
                    '@type'                => 'UnitPriceSpecification',
                    'price'                => $priceValue,
                    'priceCurrency'        => $currency,
                    'referenceQuantity'    => [
                        '@type'    => 'QuantitativeValue',
                        'value'    => 1,
                        'unitCode' => $unitCode,
                    ],
                ];
            }

            if ($this->microdataConfig->get('delivery_lead', false)) {
                $minDays = (int)$this->microdataConfig->get('delivery_min', 1);
                $maxDays = (int)$this->microdataConfig->get('delivery_max', 2);
                $offer['shippingDetails'] = [
                    '@type'            => 'OfferShippingDetails',
                    'deliveryTime'     => [
                        '@type'            => 'ShippingDeliveryTime',
                        'handlingTime'     => [
                            '@type'    => 'QuantitativeValue',
                            'minValue' => $minDays,
                            'maxValue' => $maxDays,
                            'unitCode' => 'd',
                        ],
                    ],
                ];
            }

            $returnPolicyEnabled = $this->microdataConfig->get('return_policy', false);

            if ($returnPolicyEnabled) {
                $returnDays = (int)$this->microdataConfig->get('return_days', 14);
                $returnType = $this->microdataConfig->get('return_type', 'MerchantReturnFiniteReturnWindow');
                $offer['hasMerchantReturnPolicy'] = [
                    '@type'                     => 'MerchantReturnPolicy',
                    'applicableCountry'         => $this->microdataConfig->get('address_country', 'UA'),
                    'returnPolicyCategory'      => 'https://schema.org/' . $returnType,
                    'merchantReturnDays'        => $returnDays,
                    'returnMethod'              => 'https://schema.org/ReturnByMail',
                    'returnFees'                => 'https://schema.org/FreeReturn',
                ];
            }

            $schema['offers'] = $offer;
        }

        $reviewSource = $this->microdataConfig->get('review_source', 'product');

        if ($reviewSource === 'product') {
            $aggRating = $this->model_extension_aw_microdata_microdata->getProductAggregateRating($productId);
            $reviews = $this->model_extension_aw_microdata_microdata->getProductReviews($productId);
        } else {
            $aggRating = $this->model_extension_aw_microdata_microdata->getStoreAggregateRating();
            $reviews = $this->model_extension_aw_microdata_microdata->getStoreReviews();
        }

        if ($reviewSource !== 'product') {
            $fakeCount = (int)$this->microdataConfig->get('fake_count', 0);
            $fakeBoost = (float)$this->microdataConfig->get('fake_boost', 0);
            $aggRating['count'] += $fakeCount;

            if ($fakeBoost > 0 && $aggRating['avg'] > 0) {
                $aggRating['avg'] = min(5, $aggRating['avg'] + $fakeBoost);
            } elseif ($fakeBoost > 0 && $aggRating['avg'] == 0) {
                $aggRating['avg'] = min(5, $fakeBoost);
            }
        }

        if ($aggRating['count'] > 0) {
            $schema['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => round($aggRating['avg'], 1),
                'bestRating'  => 5,
                'reviewCount' => $aggRating['count'],
            ];
        }

        if ($reviews) {
            $schema['review'] = [];

            foreach ($reviews as $review) {
                $reviewItem = [
                    '@type'         => 'Review',
                    'author'        => [
                        '@type' => 'Person',
                        'name'  => $this->cleanText($review['author']),
                    ],
                    'datePublished' => date('c', strtotime($review['date_added'])),
                    'reviewRating'  => [
                        '@type'       => 'Rating',
                        'ratingValue' => (int)$review['rating'],
                        'bestRating'  => 5,
                    ],
                ];

                $text = $this->cleanText($review['text']);
                if ($text) {
                    $reviewItem['reviewBody'] = $text;
                }

                $schema['review'][] = $reviewItem;
            }
        }

        if ($this->microdataConfig->get('attributes', false) && !empty($data['attribute_groups'])) {
            $schema['additionalProperty'] = [];

            foreach ($data['attribute_groups'] as $group) {
                if (!empty($group['attribute'])) {
                    foreach ($group['attribute'] as $attr) {
                        $schema['additionalProperty'][] = [
                            '@type' => 'PropertyValue',
                            'name'  => $this->cleanText($attr['name']),
                            'value' => $this->cleanText($attr['text']),
                        ];
                    }
                }
            }
        }

        if ($this->microdataConfig->get('include_weight', false) && !empty($data['weight'])) {
            $schema['weight'] = [
                '@type'    => 'QuantitativeValue',
                'value'    => $this->cleanText($data['weight']),
                'unitText' => $data['weight_class'] ?? 'kg',
            ];
        }

        if (!empty($data['attribute_groups'])) {
            $colorAttrId = (int)$this->microdataConfig->get('attr_color_id', 0);
            $materialAttrId = (int)$this->microdataConfig->get('attr_material_id', 0);
            $sizeAttrId = (int)$this->microdataConfig->get('attr_size_id', 0);

            foreach ($data['attribute_groups'] as $group) {
                if (!empty($group['attribute'])) {
                    foreach ($group['attribute'] as $attr) {
                        $attrId = (int)($attr['attribute_id'] ?? 0);
                        $attrValue = $this->cleanText($attr['text'] ?? '');
                        if (!$attrValue) {
                            continue;
                        }

                        if ($colorAttrId && $attrId === $colorAttrId) {
                            $schema['color'] = $attrValue;
                        }
                        if ($materialAttrId && $attrId === $materialAttrId) {
                            $schema['material'] = $attrValue;
                        }
                        if ($sizeAttrId && $attrId === $sizeAttrId) {
                            $schema['size'] = $attrValue;
                        }
                    }
                }
            }
        }

        $gender = $this->microdataConfig->get('attr_gender', '');
        if ($gender) {
            $schema['audience'] = [
                '@type'           => 'PeopleAudience',
                'suggestedGender' => $gender,
            ];
        }

        if ($this->microdataConfig->get('related_products', false) && !empty($data['products'])) {
            $related = [];
            foreach ($data['products'] as $product) {
                $relatedItem = [
                    '@type' => 'Product',
                    'name'  => $this->cleanText($product['name'] ?? ''),
                    'url'   => $product['href'] ?? '',
                ];
                if (!empty($product['thumb'])) {
                    $relatedItem['image'] = str_replace(' ', '%20', $product['thumb']);
                }
                $related[] = $relatedItem;
            }
            if ($related) {
                $schema['isRelatedTo'] = $related;
            }
        }

        if ($this->microdataConfig->get('video_object', false)) {
            $descriptionHtml = $data['description'] ?? '';
            $videos = $this->extractVideoObjects($descriptionHtml, $name);
            if ($videos) {
                $schema['video'] = count($videos) === 1 ? $videos[0] : $videos;
            }
        }

        if ($this->microdataConfig->get('image_object', false)) {
            $descriptionHtml = $data['description'] ?? '';
            $descImages = $this->extractImageObjects($descriptionHtml);
            if ($descImages) {
                if (!isset($schema['image'])) {
                    $schema['image'] = [];
                } elseif (!is_array($schema['image'])) {
                    $schema['image'] = [$schema['image']];
                }
                foreach ($descImages as $img) {
                    $schema['image'][] = $img;
                }
            }
        }

        return $this->buildJsonLd($schema);
    }

    private function buildListingPageSchema(
        string $name,
        string $description,
        string $url,
        array $priceRange,
        array $products = [],
        string $schemaType = 'CollectionPage'
    ): array {
        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => $schemaType,
            'name'        => $name,
            'description' => $description ?: $name,
            'url'         => $url,
        ];

        if ($priceRange['count'] > 0 && $priceRange['low'] > 0) {
            $offer = [
                '@type'         => 'AggregateOffer',
                'lowPrice'      => $priceRange['low'],
                'highPrice'     => $priceRange['high'],
                'offerCount'    => $priceRange['count'],
                'priceCurrency' => $this->getCurrencyCode(),
            ];

            if ($this->microdataConfig->get('listing_delivery', false)) {
                $minDays = (int)$this->microdataConfig->get('delivery_min', 1);
                $maxDays = (int)$this->microdataConfig->get('delivery_max', 3);
                $offer['shippingDetails'] = [
                    '@type'        => 'OfferShippingDetails',
                    'deliveryTime' => [
                        '@type'        => 'ShippingDeliveryTime',
                        'handlingTime' => [
                            '@type'    => 'QuantitativeValue',
                            'minValue' => $minDays,
                            'maxValue' => $maxDays,
                            'unitCode' => 'd',
                        ],
                    ],
                ];
            }

            if ($this->microdataConfig->get('listing_return_policy', false)) {
                $returnDays = (int)$this->microdataConfig->get('return_days', 14);
                $returnType = $this->microdataConfig->get('return_type', 'MerchantReturnFiniteReturnWindow');
                $offer['hasMerchantReturnPolicy'] = [
                    '@type'                => 'MerchantReturnPolicy',
                    'applicableCountry'    => $this->microdataConfig->get('address_country', 'UA'),
                    'returnPolicyCategory' => 'https://schema.org/' . $returnType,
                    'merchantReturnDays'   => $returnDays,
                    'returnMethod'         => 'https://schema.org/ReturnByMail',
                    'returnFees'           => 'https://schema.org/FreeReturn',
                ];
            }

            $schema['offers'] = $offer;
        }

        if ($this->microdataConfig->get('category_carousel', false) && !empty($products)) {
            $itemList = [
                '@type'           => 'ItemList',
                'itemListElement' => [],
            ];

            foreach ($products as $position => $product) {
                $itemList['itemListElement'][] = [
                    '@type'    => 'ListItem',
                    'position' => $position + 1,
                    'url'      => $product['href'] ?? '',
                    'name'     => $this->cleanText($product['name'] ?? ''),
                ];
            }

            $schema['mainEntity'] = $itemList;
        }

        return $schema;
    }

    public function getCategory($data = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('category_schema', true)) {
            return '';
        }

        if (!is_array($data)) {
            $data = [];
        }

        $this->load->model('extension/aw_microdata/microdata');

        $name = $this->cleanText($data['heading_title'] ?? '');

        if (!$name) {
            return '';
        }

        $description = $this->cleanText($data['description'] ?? '');
        $url = rtrim($this->getShopUrl(), '/') . $this->request->server['REQUEST_URI'];
        $schemaType = $this->microdataConfig->get('category_type', 'CollectionPage');

        $priceRange = ['low' => 0, 'high' => 0, 'count' => 0];

        if ($this->microdataConfig->get('price_range', true) && isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
            $categoryId = (int)array_pop($parts);

            if ($categoryId) {
                $priceRange = $this->model_extension_aw_microdata_microdata->getCategoryPriceRange($categoryId);
            }
        }

        $products = $data['products'] ?? [];
        $schema = $this->buildListingPageSchema($name, $description, $url, $priceRange, $products, $schemaType);

        return $this->buildJsonLd($schema);
    }

    public function getLandingPage($data = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('landing_enabled', true)) {
            return '';
        }

        if (!is_array($data)) {
            $data = [];
        }

        $this->load->model('extension/aw_microdata/microdata');

        $name = $this->cleanText($data['heading_title'] ?? '');

        if (!$name) {
            return '';
        }

        $description = $this->cleanText($data['description'] ?? '');
        $url = rtrim($this->getShopUrl(), '/') . $this->request->server['REQUEST_URI'];

        $priceRange = ['low' => 0, 'high' => 0, 'count' => 0];
        $landingPageId = (int)($this->request->get['landing_page_id'] ?? 0);

        if ($landingPageId) {
            $priceRange = $this->model_extension_aw_microdata_microdata->getLandingPriceRange($landingPageId);
        }

        $products = $data['products'] ?? [];
        $schema = $this->buildListingPageSchema($name, $description, $url, $priceRange, $products);

        if ($this->microdataConfig->get('area_served', false)) {
            $schema['areaServed'] = $name;
        }

        return $this->buildJsonLd($schema);
    }

    public function getSearchResults($data = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('search_schema', true)) {
            return '';
        }

        if (!is_array($data)) {
            $data = [];
        }

        $this->load->model('extension/aw_microdata/microdata');

        $name = $this->cleanText($data['heading_title'] ?? '');

        if (!$name) {
            return '';
        }

        $description = $this->cleanText($data['description'] ?? '');
        $url = rtrim($this->getShopUrl(), '/') . $this->request->server['REQUEST_URI'];

        $search = $this->request->get['search'] ?? '';
        $categoryId = (int)($this->request->get['category_id'] ?? 0);
        $subCategory = !empty($this->request->get['sub_category']);
        $searchDescription = !empty($this->request->get['description']);

        $priceRange = $this->model_extension_aw_microdata_microdata->getSearchPriceRange(
            $search,
            $categoryId,
            $subCategory,
            $searchDescription
        );

        $products = $data['products'] ?? [];
        $schema = $this->buildListingPageSchema($name, $description, $url, $priceRange, $products, 'SearchResultsPage');

        return $this->buildJsonLd($schema);
    }

    public function getManufacturer($data = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('manufacturer_schema', true)) {
            return '';
        }

        if (!is_array($data)) {
            $data = [];
        }

        $this->load->model('extension/aw_microdata/microdata');

        $name = $this->cleanText($data['heading_title'] ?? '');

        if (!$name) {
            return '';
        }

        $description = $this->cleanText($data['description'] ?? '');
        $url = rtrim($this->getShopUrl(), '/') . $this->request->server['REQUEST_URI'];

        $manufacturerId = (int)($this->request->get['manufacturer_id'] ?? 0);

        $priceRange = ['low' => 0, 'high' => 0, 'count' => 0];

        if ($manufacturerId) {
            $priceRange = $this->model_extension_aw_microdata_microdata->getManufacturerPriceRange($manufacturerId);
        }

        $products = $data['products'] ?? [];
        $schema = $this->buildListingPageSchema($name, $description, $url, $priceRange, $products);

        $schema['brand'] = [
            '@type' => 'Brand',
            'name'  => $name,
        ];

        return $this->buildJsonLd($schema);
    }

    public function getSpecial($data = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('special_schema', true)) {
            return '';
        }

        if (!is_array($data)) {
            $data = [];
        }

        $this->load->model('extension/aw_microdata/microdata');

        $name = $this->cleanText($data['heading_title'] ?? '');

        if (!$name) {
            return '';
        }

        $description = $this->cleanText($data['description'] ?? '');
        $url = rtrim($this->getShopUrl(), '/') . $this->request->server['REQUEST_URI'];

        $priceRange = $this->model_extension_aw_microdata_microdata->getSpecialPriceRange();

        $products = $data['products'] ?? [];
        $schema = $this->buildListingPageSchema($name, $description, $url, $priceRange, $products);

        return $this->buildJsonLd($schema);
    }

    public function getHomepageProducts(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('homepage_products', false)) {
            return '';
        }

        $this->load->model('extension/aw_microdata/microdata');

        $storeName = $this->cleanText($this->config->get('config_name'));
        $description = $this->cleanText($this->config->get('config_meta_description')) ?: $storeName;
        $url = rtrim($this->getShopUrl(), '/') . '/';

        $priceRange = $this->model_extension_aw_microdata_microdata->getHomepagePriceRange();

        $schema = $this->buildListingPageSchema($storeName, $description, $url, $priceRange);

        return $this->buildJsonLd($schema);
    }

    public function getInformation($data = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('info_schema', true)) {
            return '';
        }

        if (!is_array($data)) {
            $data = [];
        }

        $articleType = $this->microdataConfig->get('info_type', 'Article');
        $shopUrl = rtrim($this->getShopUrl(), '/');
        $storeName = $this->cleanText($this->config->get('config_name'));

        $headline = $this->cleanText($data['heading_title'] ?? '');
        $description = $this->cleanText($data['description'] ?? '');

        if (!$headline) {
            return '';
        }

        $url = '';

        if (!empty($data['breadcrumbs'])) {
            $lastBreadcrumb = end($data['breadcrumbs']);
            $url = $lastBreadcrumb['href'] ?? '';
        }

        if (!$url && !empty($this->request->get['information_id'])) {
            $url = $this->url->link('information/information', 'information_id=' . (int)$this->request->get['information_id']);
        }

        $schema = [
            '@context'      => 'https://schema.org',
            '@type'         => $articleType,
            'headline'      => $headline,
            'description'   => $description ?: $headline,
            'url'           => $url ?: $shopUrl,
            'datePublished' => date('c'),
            'dateModified'  => date('c'),
            'author'        => [
                '@type' => 'Organization',
                'name'  => $storeName,
                'url'   => $shopUrl,
            ],
            'publisher'     => [
                '@type' => 'Organization',
                'name'  => $storeName,
                'url'   => $shopUrl,
            ],
        ];

        $logoPath = $this->microdataConfig->get('logo', '') ?: $this->config->get('config_logo');

        if ($logoPath) {
            $logoUrl = (strpos($logoPath, 'http') === 0) ? $logoPath : $shopUrl . '/' . ltrim(str_replace(' ', '%20', $logoPath), '/');
            $schema['image'] = $logoUrl;
            $schema['publisher']['logo'] = [
                '@type' => 'ImageObject',
                'url'   => $logoUrl,
            ];
        }

        if ($url) {
            $schema['mainEntityOfPage'] = ['@type' => 'WebPage', '@id' => $url];
        }

        return $this->buildJsonLd($schema);
    }

    public function getBlogCategory(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('blog_schema', true)) {
            return '';
        }

        $shopUrl = rtrim($this->getShopUrl(), '/');
        $storeName = $this->cleanText($this->config->get('config_name'));

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Blog',
            'name'        => $storeName . ' - Blog',
            'description' => $storeName,
            'url'         => $shopUrl . $this->request->server['REQUEST_URI'],
        ];

        return $this->buildJsonLd($schema);
    }

    public function getBlogArticle($data = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('blog_schema', true)) {
            return '';
        }

        if (!is_array($data)) {
            $data = [];
        }

        $blogType = $this->microdataConfig->get('blog_type', 'BlogPosting');
        $shopUrl = rtrim($this->getShopUrl(), '/');
        $storeName = $this->cleanText($this->config->get('config_name'));

        $headline = $this->cleanText($data['heading_title'] ?? '');
        $body = $this->cleanText($data['description'] ?? '');

        if (!$headline) {
            return '';
        }

        $url = '';

        if (!empty($data['breadcrumbs'])) {
            $lastBreadcrumb = end($data['breadcrumbs']);
            $url = $lastBreadcrumb['href'] ?? '';
        }

        if (!$url && !empty($this->request->get['article_id'])) {
            $url = $this->url->link('blog/article', 'article_id=' . (int)$this->request->get['article_id']);
        }

        $schema = [
            '@context'      => 'https://schema.org',
            '@type'         => $blogType,
            'headline'      => $headline,
            'url'           => $url ?: $shopUrl,
            'datePublished' => date('c'),
            'dateModified'  => date('c'),
            'author'        => [
                '@type' => 'Organization',
                'name'  => $storeName,
                'url'   => $shopUrl,
            ],
            'publisher'     => [
                '@type' => 'Organization',
                'name'  => $storeName,
                'url'   => $shopUrl,
            ],
        ];

        if ($url) {
            $schema['mainEntityOfPage'] = ['@type' => 'WebPage', '@id' => $url];
        }

        if ($body) {
            $schema['articleBody'] = $body;

            if ($this->microdataConfig->get('word_count', false)) {
                $schema['wordCount'] = str_word_count($body);
            }
        }

        if (!empty($data['popup'])) {
            $schema['image'] = str_replace(' ', '%20', $data['popup']);
        } elseif (!empty($data['thumb'])) {
            $schema['image'] = str_replace(' ', '%20', $data['thumb']);
        }

        $logoPath = $this->microdataConfig->get('logo', '') ?: $this->config->get('config_logo');

        if ($logoPath) {
            $logoUrl = (strpos($logoPath, 'http') === 0) ? $logoPath : $shopUrl . '/' . ltrim(str_replace(' ', '%20', $logoPath), '/');
            $schema['publisher']['logo'] = [
                '@type' => 'ImageObject',
                'url'   => $logoUrl,
            ];
        }

        if (!empty($data['breadcrumbs']) && count($data['breadcrumbs']) > 1) {
            $section = $data['breadcrumbs'][count($data['breadcrumbs']) - 2] ?? null;
            if ($section && !empty($section['text'])) {
                $schema['articleSection'] = $this->cleanText($section['text']);
            }
        }

        if ($this->microdataConfig->get('video_object', false)) {
            $descriptionHtml = $data['description'] ?? '';
            $videos = $this->extractVideoObjects($descriptionHtml, $headline);
            if ($videos) {
                $schema['video'] = count($videos) === 1 ? $videos[0] : $videos;
            }
        }

        if ($this->microdataConfig->get('image_object', false)) {
            $descriptionHtml = $data['description'] ?? '';
            $descImages = $this->extractImageObjects($descriptionHtml);
            if ($descImages) {
                if (!isset($schema['image'])) {
                    $schema['image'] = [];
                } elseif (!is_array($schema['image'])) {
                    $schema['image'] = [$schema['image']];
                }
                foreach ($descImages as $img) {
                    $schema['image'][] = $img;
                }
            }
        }

        return $this->buildJsonLd($schema);
    }

    public function getFaq($data = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('faq_schema', true)) {
            return '';
        }

        if (!is_array($data)) {
            $data = [];
        }

        $faqs = $data['faqs'] ?? [];

        if (empty($faqs)) {
            $this->load->model('extension/aw_microdata/microdata');
            $faqs = $this->model_extension_aw_microdata_microdata->getFaqItems();
        }

        if (empty($faqs)) {
            return '';
        }

        $mainEntity = [];

        foreach ($faqs as $faq) {
            $question = $this->cleanText($faq['question'] ?? '');
            $answer = $this->cleanText($faq['answer'] ?? '');

            if ($question && $answer) {
                $mainEntity[] = [
                    '@type' => 'Question',
                    'name'  => $question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $answer,
                    ],
                ];
            }
        }

        if (empty($mainEntity)) {
            return '';
        }

        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];

        return $this->buildJsonLd($schema);
    }

    public function getReviewsPage(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('reviews_schema', true)) {
            return '';
        }

        $orgData = $this->getOrganizationData();
        $schema = ['@context' => 'https://schema.org'] + $orgData;

        $aggregateRating = $this->getAggregateRatingData();
        if ($aggregateRating) {
            $schema['aggregateRating'] = $aggregateRating;
        }

        $reviews = $this->getReviewsData();
        if ($reviews) {
            $schema['review'] = $reviews;
        }

        return $this->buildJsonLd($schema);
    }

    public function getCalculator(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('calculator_schema', true)) {
            return '';
        }

        $shopUrl = rtrim($this->getShopUrl(), '/');
        $storeName = $this->cleanText($this->config->get('config_name'));

        $calcName = $this->microdataConfig->get('calculator_name', 'Moonshine Calculator');
        $calcCategory = $this->microdataConfig->get('calculator_category', 'UtilitiesApplication');

        $schemas = [];

        $app = [
            '@context'            => 'https://schema.org',
            '@type'               => 'WebApplication',
            'name'                => $calcName,
            'url'                 => $shopUrl . $this->request->server['REQUEST_URI'],
            'applicationCategory' => $calcCategory,
            'operatingSystem'     => 'All',
            'offers'              => [
                '@type'         => 'Offer',
                'price'         => '0',
                'priceCurrency' => $this->getCurrencyCode(),
            ],
        ];

        $schemas[] = $app;

        if ($this->microdataConfig->get('calculator_howto', false)) {
            $steps = $this->microdataConfig->get('calculator_steps', []);

            if ($steps && is_array($steps)) {
                $howto = [
                    '@context' => 'https://schema.org',
                    '@type'    => 'HowTo',
                    'name'     => $calcName,
                    'step'     => [],
                ];

                foreach ($steps as $i => $step) {
                    $howto['step'][] = [
                        '@type'    => 'HowToStep',
                        'position' => $i + 1,
                        'text'     => $this->cleanText($step),
                    ];
                }

                $schemas[] = $howto;
            }
        }

        $output = '';

        foreach ($schemas as $s) {
            $output .= $this->buildJsonLd($s);
        }

        return $output;
    }

    public function getContactPage(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('contact_schema', true)) {
            return '';
        }

        $shopUrl = rtrim($this->getShopUrl(), '/');

        $orgData = $this->getOrganizationData();

        $schema = [
            '@context'      => 'https://schema.org',
            '@type'         => 'ContactPage',
            'name'          => $this->cleanText($this->config->get('config_name')) . ' - Contact',
            'url'           => $shopUrl . $this->request->server['REQUEST_URI'],
            'mainEntity'    => ['@type' => 'LocalBusiness'] + $orgData,
        ];

        return $this->buildJsonLd($schema);
    }

    public function getBreadcrumbs($data = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if (!$this->microdataConfig->get('breadcrumbs_enabled', true)) {
            return '';
        }

        if (!is_array($data)) {
            $data = [];
        }

        $breadcrumbs = $data['breadcrumbs'] ?? $data;

        if (empty($breadcrumbs) || !is_array($breadcrumbs)) {
            return '';
        }

        $items = [];
        $position = 1;

        foreach ($breadcrumbs as $crumb) {
            if (empty($crumb['href'])) {
                continue;
            }

            $text = $this->cleanText($crumb['text'] ?? '');

            if (!$text) {
                $text = 'Home';
            }

            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => $text,
                'item'     => $crumb['href'],
            ];

            $position++;
        }

        if (empty($items)) {
            return '';
        }

        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];

        return $this->buildJsonLd($schema);
    }
}
