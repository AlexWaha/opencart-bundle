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

    private function buildOfferShippingDetails(string $fallbackCurrency): array
    {
        $rateValue = $this->microdataConfig->get('shipping_rate_value', '0');
        $rateCurrency = (string)$this->microdataConfig->get('shipping_rate_currency', $fallbackCurrency);
        $country = (string)$this->microdataConfig->get('shipping_destination_country', 'UA');

        $handlingMin = (int)$this->microdataConfig->get('handling_min', 0);
        $handlingMax = (int)$this->microdataConfig->get('handling_max', 1);
        $transitMin = (int)$this->microdataConfig->get('transit_min', 1);
        $transitMax = (int)$this->microdataConfig->get('transit_max', 3);

        return [
            '@type'               => 'OfferShippingDetails',
            'shippingRate'        => [
                '@type'    => 'MonetaryAmount',
                'value'    => (string)$rateValue,
                'currency' => $rateCurrency ?: $fallbackCurrency,
            ],
            'shippingDestination' => [
                '@type'          => 'DefinedRegion',
                'addressCountry' => $country ?: 'UA',
            ],
            'deliveryTime'        => [
                '@type'        => 'ShippingDeliveryTime',
                'handlingTime' => [
                    '@type'    => 'QuantitativeValue',
                    'minValue' => $handlingMin,
                    'maxValue' => $handlingMax,
                    'unitCode' => 'DAY',
                ],
                'transitTime'  => [
                    '@type'    => 'QuantitativeValue',
                    'minValue' => $transitMin,
                    'maxValue' => $transitMax,
                    'unitCode' => 'DAY',
                ],
            ],
        ];
    }

    private function buildMerchantReturnPolicy(): array
    {
        $country = (string)$this->microdataConfig->get('return_country', 'UA');
        $applicableCountry = (string)$this->microdataConfig->get('return_applicable_country', $country);
        $category = (string)$this->microdataConfig->get('return_type', 'MerchantReturnFiniteReturnWindow');
        $returnMethod = (string)$this->microdataConfig->get('return_method', 'ReturnByMail');
        $refundType = (string)$this->microdataConfig->get('refund_type', 'FullRefund');
        $returnFees = (string)$this->microdataConfig->get('return_fees', 'FreeReturn');
        $returnDays = (int)$this->microdataConfig->get('return_days', 14);

        $policy = [
            '@type'                => 'MerchantReturnPolicy',
            'applicableCountry'    => $applicableCountry ?: 'UA',
            'returnPolicyCountry'  => $country ?: 'UA',
            'returnPolicyCategory' => 'https://schema.org/' . $category,
        ];

        if ($category === 'MerchantReturnFiniteReturnWindow' && $returnDays > 0) {
            $policy['merchantReturnDays'] = $returnDays;
        }

        if ($category !== 'MerchantReturnNotPermitted') {
            $policy['returnMethod'] = 'https://schema.org/' . $returnMethod;
            $policy['refundType']   = 'https://schema.org/' . $refundType;
            $policy['returnFees']   = 'https://schema.org/' . $returnFees;
        }

        return $policy;
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

        $idLinking = (bool)$this->microdataConfig->get('id_linking_enabled', true);

        $org = [
            '@type'       => $storeType,
            'name'        => $storeName,
            'description' => $description ?: $storeName,
            'legalName'   => (string)$legalName,
            'url'         => $shopUrl,
            'email'       => $email,
        ];

        if ($idLinking) {
            $org['@id'] = $shopUrl . '/#organization';
        }

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

        // contactPoint[] repeatable (item 14); fallback to single from phones
        $contactPoints = $this->microdataConfig->get('contact_points', []);
        $contactPointList = [];

        if (is_array($contactPoints) && !empty($contactPoints)) {
            foreach ($contactPoints as $cp) {
                if (!is_array($cp) || empty($cp['telephone'])) {
                    continue;
                }
                $item = [
                    '@type'       => 'ContactPoint',
                    'telephone'   => (string)$cp['telephone'],
                    'contactType' => (string)($cp['contact_type'] ?? 'customer service'),
                ];

                if (!empty($cp['area_served']) && is_array($cp['area_served'])) {
                    $areas = array_values(array_filter($cp['area_served']));
                    if ($areas) {
                        $item['areaServed'] = count($areas) === 1 ? $areas[0] : $areas;
                    }
                }

                if (!empty($cp['available_language']) && is_array($cp['available_language'])) {
                    $langs = array_values(array_filter($cp['available_language']));
                    if ($langs) {
                        $item['availableLanguage'] = count($langs) === 1 ? $langs[0] : $langs;
                    }
                }

                $contactPointList[] = $item;
            }
        }

        if ($contactPointList) {
            $org['contactPoint'] = count($contactPointList) === 1 ? $contactPointList[0] : $contactPointList;
        } elseif (!empty($org['telephone'])) {
            $firstPhone = is_array($org['telephone']) ? $org['telephone'][0] : $org['telephone'];
            $org['contactPoint'] = [
                '@type'       => 'ContactPoint',
                'telephone'   => $firstPhone,
                'contactType' => 'customer service',
            ];
        }

        // hasMap (item 16)
        $hasMap = (string)$this->microdataConfig->get('has_map', '');
        if ($hasMap) {
            $org['hasMap'] = $hasMap;
        }

        // Store-level hasMerchantReturnPolicy (item 15)
        if ($this->microdataConfig->get('store_return_policy_enabled', false)
            && $this->microdataConfig->get('return_policy', false)) {
            $storeReturn = $this->buildMerchantReturnPolicy();
            if ($storeReturn) {
                $org['hasMerchantReturnPolicy'] = $storeReturn;
            }
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

        $langId = (int)$this->config->get('config_language_id');
        $isProduct = $route === 'product/product';

        // OG product:* base (#6)
        if ($isProduct && $this->microdataConfig->get('og_product_base', false) && !empty($data['product_id'])) {
            $this->load->model('catalog/product');
            $productInfo = $this->model_catalog_product->getProduct((int)$data['product_id']);

            $brand = $this->cleanText($data['manufacturer'] ?? $productInfo['manufacturer'] ?? '');
            if ($brand) {
                $tags[] = '<meta property="product:brand" content="' . htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') . '">';
            }

            $category = $this->cleanText($data['category_name'] ?? '');
            if ($category) {
                $tags[] = '<meta property="product:category" content="' . htmlspecialchars($category, ENT_QUOTES, 'UTF-8') . '">';
            }

            $quantity = $data['quantity'] ?? 0;
            $availability = ($this->microdataConfig->get('force_instock', false) || $quantity > 0) ? 'instock' : 'oos';
            $tags[] = '<meta property="product:availability" content="' . $availability . '">';

            $condition = strtolower((string)$this->microdataConfig->get('condition', 'NewCondition'));
            $ogCondition = strpos($condition, 'used') !== false ? 'used' : (strpos($condition, 'refurb') !== false ? 'refurbished' : 'new');
            $tags[] = '<meta property="product:condition" content="' . $ogCondition . '">';

            $gender = (string)$this->microdataConfig->get('attr_gender', '');
            if ($gender) {
                $tags[] = '<meta property="product:target_gender" content="' . htmlspecialchars($gender, ENT_QUOTES, 'UTF-8') . '">';
            }

            $ageRestriction = (string)$this->microdataConfig->get('og_age_restriction', '');
            if ($ageRestriction) {
                $tags[] = '<meta property="og:restrictions:age" content="' . htmlspecialchars($ageRestriction, ENT_QUOTES, 'UTF-8') . '">';
            }
        }

        // OG product:* extended (#7) — color/material/size from configured attrs
        if ($isProduct && $this->microdataConfig->get('og_product_extended', false) && !empty($data['attribute_groups'])) {
            $colorAttrId = (int)$this->microdataConfig->get('attr_color_id', 0);
            $materialAttrId = (int)$this->microdataConfig->get('attr_material_id', 0);
            $sizeAttrId = (int)$this->microdataConfig->get('attr_size_id', 0);

            foreach ($data['attribute_groups'] as $group) {
                if (empty($group['attribute'])) {
                    continue;
                }
                foreach ($group['attribute'] as $attr) {
                    $attrId = (int)($attr['attribute_id'] ?? 0);
                    $val = $this->cleanText($attr['text'] ?? '');

                    if (!$val) {
                        continue;
                    }

                    if ($colorAttrId && $attrId === $colorAttrId) {
                        $tags[] = '<meta property="product:color" content="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '">';
                    }
                    if ($materialAttrId && $attrId === $materialAttrId) {
                        $tags[] = '<meta property="product:material" content="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '">';
                    }
                    if ($sizeAttrId && $attrId === $sizeAttrId) {
                        $tags[] = '<meta property="product:size" content="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '">';
                    }
                }
            }
        }

        // OG product:sale_price (#8) — only when special active
        if ($isProduct && $this->microdataConfig->get('og_sale_price', false) && !empty($data['special']) && !empty($data['price'])) {
            $salePrice = $this->parsePrice($data['special']);
            if ($salePrice > 0) {
                $tags[] = '<meta property="product:sale_price:amount" content="' . $salePrice . '">';
                $tags[] = '<meta property="product:sale_price:currency" content="' . $this->getCurrencyCode() . '">';
            }
        }

        // OG og:see_also (#22) — related products
        if ($isProduct && $this->microdataConfig->get('og_see_also_enabled', false) && !empty($data['products'])) {
            foreach ($data['products'] as $related) {
                if (!empty($related['href'])) {
                    $tags[] = '<meta property="og:see_also" content="' . htmlspecialchars($related['href'], ENT_QUOTES, 'UTF-8') . '">';
                }
            }
        }

        // OG business:contact_data:* (#23)
        if ($this->microdataConfig->get('og_business_contact', false)) {
            $addressData = $this->microdataConfig->get('address', []);
            $addr = [];

            if (isset($addressData[$langId]) && is_array($addressData[$langId])) {
                $addr = $addressData[$langId];
            } elseif (isset($addressData['street'])) {
                $addr = $addressData;
            } elseif (is_array($addressData)) {
                $first = reset($addressData);
                $addr = is_array($first) ? $first : [];
            }

            $bcMap = [
                'street_address' => $addr['street'] ?? '',
                'locality'       => $addr['city'] ?? '',
                'region'         => $addr['region'] ?? '',
                'postal_code'    => $addr['zip'] ?? '',
                'country_name'   => $addr['country'] ?? '',
            ];

            $bcMap['email'] = (string)$this->microdataConfig->get('email', '');

            $phones = $this->microdataConfig->get('phones', []);

            if (is_array($phones) && !empty($phones)) {
                $first = reset($phones);
                $bcMap['phone_number'] = is_array($first) ? ($first['number'] ?? '') : (string)$first;
            }

            $bcMap['website'] = rtrim($this->getShopUrl(), '/');

            foreach ($bcMap as $field => $value) {
                if ($value !== '' && $value !== null) {
                    $tags[] = '<meta property="business:contact_data:' . $field . '" content="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '">';
                }
            }
        }

        // OG place:location (#24)
        if ($this->microdataConfig->get('og_place_location', false)) {
            $geo = $this->microdataConfig->get('geo', []);
            $lat = is_array($geo) ? ($geo['lat'] ?? '') : '';
            $lng = is_array($geo) ? ($geo['lon'] ?? '') : '';

            if ($lat && $lng) {
                $tags[] = '<meta property="place:location:latitude" content="' . htmlspecialchars((string)$lat, ENT_QUOTES, 'UTF-8') . '">';
                $tags[] = '<meta property="place:location:longitude" content="' . htmlspecialchars((string)$lng, ENT_QUOTES, 'UTF-8') . '">';
            }
        }

        // fb:profile_id (#25)
        $fbProfileId = (string)$this->microdataConfig->get('fb_profile_id', '');
        if ($fbProfileId) {
            $tags[] = '<meta property="fb:profile_id" content="' . htmlspecialchars($fbProfileId, ENT_QUOTES, 'UTF-8') . '">';
        }

        $fbAppId = (string)$this->microdataConfig->get('fb_app_id', '');
        if ($fbAppId) {
            $tags[] = '<meta property="fb:app_id" content="' . htmlspecialchars($fbAppId, ENT_QUOTES, 'UTF-8') . '">';
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

        $idLinking = (bool)$this->microdataConfig->get('id_linking_enabled', true);
        $orgId = $shopUrl . '/#organization';

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $name,
            'description' => $description ?: $name,
        ];

        if ($idLinking) {
            $productUrl = $this->url->link('product/product', 'product_id=' . $productId);
            $schema['@id'] = $productUrl . '#product';
        }

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

            if ($idLinking) {
                $schema['brand']['@id'] = $orgId;
            }
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

        // GTIN auto-variant by admin-chosen source (#10)
        $gtinSource = (string)$this->microdataConfig->get('gtin_source', 'ean');
        $gtinValue = '';

        switch ($gtinSource) {
            case 'sku':      $gtinValue = (string)($productInfo['sku'] ?? '');
                break;
            case 'upc':      $gtinValue = (string)($productInfo['upc'] ?? '');
                break;
            case 'jan':      $gtinValue = (string)($productInfo['jan'] ?? '');
                break;
            case 'isbn':     $gtinValue = (string)($productInfo['isbn'] ?? '');
                break;
            case 'mpn':      $gtinValue = (string)($productInfo['mpn'] ?? '');
                break;
            case 'location': $gtinValue = (string)($productInfo['location'] ?? '');
                break;
            case 'custom':
                $customAttrId = (int)$this->microdataConfig->get('gtin_custom_attribute_id', 0);

                if ($customAttrId && !empty($data['attribute_groups'])) {
                    foreach ($data['attribute_groups'] as $group) {
                        if (!empty($group['attribute'])) {
                            foreach ($group['attribute'] as $attr) {
                                if ((int)($attr['attribute_id'] ?? 0) === $customAttrId) {
                                    $gtinValue = (string)($attr['text'] ?? '');
                                    break 2;
                                }
                            }
                        }
                    }
                }
                break;
            case 'ean':
            default:
                $gtinValue = (string)($productInfo['ean'] ?? '');
                break;
        }

        $gtinValue = preg_replace('/\D+/', '', $gtinValue);

        if ($gtinValue) {
            $len = strlen($gtinValue);

            if ($len === 8) {
                $schema['gtin8'] = $gtinValue;
            } elseif ($len === 12) {
                $schema['gtin12'] = $gtinValue;
            } elseif ($len === 13) {
                $schema['gtin13'] = $gtinValue;
            } elseif ($len === 14) {
                $schema['gtin14'] = $gtinValue;
            } else {
                $schema['gtin'] = $gtinValue;
            }
        }

        // Extra identifiers UPC/EAN/ISBN (#26)
        if ($this->microdataConfig->get('upc_enabled', false) && !empty($productInfo['upc'])) {
            $schema['productID'] = 'upc:' . $this->cleanText($productInfo['upc']);
        }
        if ($this->microdataConfig->get('ean_enabled', false) && !empty($productInfo['ean'])) {
            $schema['gtin13'] = $schema['gtin13'] ?? $this->cleanText($productInfo['ean']);
        }
        if ($this->microdataConfig->get('isbn_enabled', false) && !empty($productInfo['isbn'])) {
            $schema['isbn'] = $this->cleanText($productInfo['isbn']);
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
                '@type'         => 'Offer',
                'url'           => $productUrl,
                'priceCurrency' => $currency,
                'price'         => $priceValue,
                'availability'  => $availability,
                'seller'        => [
                    '@type' => 'Organization',
                    'name'  => $storeName,
                ],
            ];

            $merchantEnabled = (bool)$this->microdataConfig->get('merchant_listings_enabled', true);

            if ($merchantEnabled && $this->microdataConfig->get('price_valid_until_enabled', true)) {
                $offer['priceValidUntil'] = date('c', strtotime('+365 days'));
            }

            if ($merchantEnabled && $this->microdataConfig->get('item_condition_enabled', true)) {
                $condition = (string)$this->microdataConfig->get('condition', 'NewCondition');
                $offer['itemCondition'] = 'https://schema.org/' . $condition;
            }

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

            // StrikethroughPrice on active special (#9)
            if (!empty($data['special']) && !empty($data['price'])) {
                $regularPrice = $this->parsePrice($data['price']);

                if ($regularPrice > 0 && $regularPrice > $priceValue) {
                    $strikethrough = [
                        '@type'         => 'UnitPriceSpecification',
                        'priceType'     => 'https://schema.org/StrikethroughPrice',
                        'price'         => $regularPrice,
                        'priceCurrency' => $currency,
                    ];

                    if (isset($offer['priceSpecification'])) {
                        $existing = $offer['priceSpecification'];
                        $offer['priceSpecification'] = isset($existing['@type'])
                            ? [$existing, $strikethrough]
                            : array_merge($existing, [$strikethrough]);
                    } else {
                        $offer['priceSpecification'] = $strikethrough;
                    }
                }
            }

            if ($merchantEnabled && $this->microdataConfig->get('shipping_details', false)) {
                $shippingBlock = $this->buildOfferShippingDetails($currency);
                if ($shippingBlock) {
                    $offer['shippingDetails'] = $shippingBlock;
                }
            }

            if ($merchantEnabled && $this->microdataConfig->get('return_policy', false)) {
                $returnBlock = $this->buildMerchantReturnPolicy();
                if ($returnBlock) {
                    $offer['hasMerchantReturnPolicy'] = $returnBlock;
                }
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
            $apEnabled = (bool)$this->microdataConfig->get('additional_property_enabled', false);
            $apGroups = $this->microdataConfig->get('additional_property_groups', []);
            $apAllowlist = $apEnabled && is_array($apGroups) ? array_map('intval', $apGroups) : [];

            $schema['additionalProperty'] = [];

            foreach ($data['attribute_groups'] as $group) {
                if ($apEnabled && !empty($apAllowlist)) {
                    $groupId = (int)($group['attribute_group_id'] ?? 0);
                    if (!in_array($groupId, $apAllowlist, true)) {
                        continue;
                    }
                }

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

            if (empty($schema['additionalProperty'])) {
                unset($schema['additionalProperty']);
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

        // ImageObject full pack on primary image (#20)
        if ($this->microdataConfig->get('image_object_enabled', false) && !empty($schema['image'])) {
            $primaryUrl = is_array($schema['image']) ? reset($schema['image']) : $schema['image'];
            if (is_string($primaryUrl)) {
                $primary = [
                    '@type'      => 'ImageObject',
                    'contentUrl' => $primaryUrl,
                    'url'        => $primaryUrl,
                ];

                $licenseVal = (string)$this->microdataConfig->get('image_object_license', '');
                if ($licenseVal) {
                    $primary['license'] = $licenseVal;
                }
                $acquireVal = (string)$this->microdataConfig->get('image_object_acquire_page', '');
                if ($acquireVal) {
                    $primary['acquireLicensePage'] = $acquireVal;
                }
                $creditVal = (string)$this->microdataConfig->get('image_object_credit_text', '');
                if ($creditVal) {
                    $primary['creditText'] = $creditVal;
                }
                $creatorVal = (string)$this->microdataConfig->get('image_object_creator', '');
                if ($creatorVal) {
                    $primary['creator'] = [
                        '@type' => 'Organization',
                        'name'  => $creatorVal,
                    ];
                }
                $copyrightVal = (string)$this->microdataConfig->get('image_object_copyright', '');
                if ($copyrightVal) {
                    $primary['copyrightNotice'] = $copyrightVal;
                }

                if (is_array($schema['image'])) {
                    $schema['image'][0] = $primary;
                } else {
                    $schema['image'] = $primary;
                }
            }
        }

        // ProductGroup + hasVariant[] (#27) — only when product belongs to allowlist categories and has options
        if ($this->microdataConfig->get('productgroup_enabled', false) && !empty($data['options'])) {
            $allowedCats = $this->microdataConfig->get('productgroup_categories', []);
            $shouldEmit = false;

            if (is_array($allowedCats) && !empty($allowedCats)) {
                $this->load->model('catalog/product');
                $productCats = $this->model_catalog_product->getCategories($productId);

                foreach ($productCats as $catRow) {
                    if (in_array((int)($catRow['category_id'] ?? 0), array_map('intval', $allowedCats), true)) {
                        $shouldEmit = true;
                        break;
                    }
                }
            }

            if ($shouldEmit) {
                $variants = [];

                foreach ($data['options'] as $option) {
                    if (empty($option['product_option_value'])) {
                        continue;
                    }
                    foreach ($option['product_option_value'] as $pov) {
                        $variants[] = [
                            '@type' => 'Product',
                            'name'  => $name . ' — ' . $this->cleanText($pov['name'] ?? ''),
                            'sku'   => isset($pov['sku']) ? (string)$pov['sku'] : ($sku ?: ''),
                        ];
                    }
                }

                if ($variants) {
                    $groupSchema = [
                        '@type'           => 'ProductGroup',
                        'name'            => $name,
                        'productGroupID'  => (string)$productId,
                        'hasVariant'      => $variants,
                    ];

                    $schema = [
                        '@context' => 'https://schema.org',
                        '@graph'   => [$schema, $groupSchema],
                    ];
                }
            }
        }

        // speakable spec (#30)
        $speakableSelectors = $this->microdataConfig->get('speakable_selectors', []);
        if ($this->microdataConfig->get('speakable', false) && is_array($speakableSelectors) && !empty($speakableSelectors)) {
            $speakableBlock = [
                '@type'    => 'SpeakableSpecification',
                'cssSelector' => array_values($speakableSelectors),
            ];

            if (isset($schema['@graph'])) {
                $schema['@graph'][0]['speakable'] = $speakableBlock;
            } else {
                $schema['speakable'] = $speakableBlock;
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

        // AggregateOffer: emit only when admin-toggled AND MIN < MAX (#29)
        $aggregateGateOk = $this->microdataConfig->get('category_aggregate_offer', false)
            && $priceRange['count'] > 0
            && $priceRange['low'] > 0
            && $priceRange['high'] > $priceRange['low'];

        if ($aggregateGateOk) {
            $offer = [
                '@type'         => 'AggregateOffer',
                'lowPrice'      => $priceRange['low'],
                'highPrice'     => $priceRange['high'],
                'offerCount'    => $priceRange['count'],
                'priceCurrency' => $this->getCurrencyCode(),
            ];

            $merchantEnabled = (bool)$this->microdataConfig->get('merchant_listings_enabled', true);

            if ($merchantEnabled && $this->microdataConfig->get('listing_delivery', false)) {
                $offer['shippingDetails'] = $this->buildOfferShippingDetails($this->getCurrencyCode());
            }

            if ($merchantEnabled && $this->microdataConfig->get('listing_return_policy', false)) {
                $offer['hasMerchantReturnPolicy'] = $this->buildMerchantReturnPolicy();
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

        // Article auto-extract <img> from description up to first </p> (#19)
        if ($this->microdataConfig->get('article_image_extract', false) && !empty($data['description'])) {
            $rawDesc = (string)$data['description'];
            $cutPos = stripos($rawDesc, '</p>');
            $chunk = $cutPos !== false ? substr($rawDesc, 0, $cutPos) : $rawDesc;
            $imageUrls = [];

            if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $chunk, $matches)) {
                foreach ($matches[1] as $src) {
                    $src = trim($src);
                    if ($src) {
                        $imageUrls[] = $src;
                    }
                }
            }

            if ($imageUrls) {
                $imageUrls = array_values(array_unique($imageUrls));
                $existing = isset($schema['image']) ? (array)$schema['image'] : [];
                $schema['image'] = array_values(array_unique(array_merge($existing, $imageUrls)));
                if (count($schema['image']) === 1) {
                    $schema['image'] = $schema['image'][0];
                }
            }
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
