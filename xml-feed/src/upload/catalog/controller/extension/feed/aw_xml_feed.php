<?php

/**
 * Age Verification Module
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @email support@alexwaha.com
 * @license GPLv3
 */
class ControllerExtensionFeedAwXmlFeed extends Controller
{
    private string $moduleName = 'aw_xml_feed';

    private \Alexwaha\Config $moduleConfig;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
    }

    public function index()
    {
        $this->load->model('extension/feed/' . $this->moduleName);
        $this->load->model('localisation/language');
        $this->load->model('localisation/currency');
        $this->load->model('catalog/product');

        $this->load->language('extension/feed/' . $this->moduleName);

        if (php_sapi_name() !== 'cli') {
            $accessKey = $this->request->get['access_key'] ?? '';
            $configuredKey = $this->moduleConfig->get('access_key', '');

            if (empty($configuredKey) || $accessKey !== $configuredKey) {
                $this->response->addHeader('HTTP/1.1 401 Unauthorized');
                $this->response->addHeader('Content-Type: text/plain');
                $this->response->setOutput($this->language->get('text_access_denied'));
                return;
            }
        }

        $feedId = (int) ($this->request->get['feed_id'] ?? 0);
        $generatedFeeds = [];

        if (!$feedId) {
            $feeds = $this->model_extension_feed_aw_xml_feed->getFeeds();

            foreach ($feeds as $feedData) {
                if ($feedData['status']) {
                    $result = $this->generateFeed($feedData['feed_id']);
                    if ($result) {
                        $generatedFeeds[] = $result;
                    }
                }
            }
        } else {
            $result = $this->generateFeed($feedId);
            if ($result) {
                $generatedFeeds[] = $result;
            }
        }

        $this->outputResults($generatedFeeds);
    }

    private function generateFeed($feedId)
    {
        $this->load->language('extension/feed/' . $this->moduleName);

        $feed = $this->model_extension_feed_aw_xml_feed->getFeed($feedId);

        if (!$feed || !$feed['status']) {
            return false;
        }

        $feedConfig = $this->awCore->getConfig($this->moduleName . '_feed_id_' . $feed['feed_id']);

        if ($feedConfig) {
            $language = $this->model_localisation_language->getLanguage($feed['language_id']);
            $currency = $this->model_localisation_currency->getCurrencyByCode($feed['currency_code']);

            $this->load->language('extension/feed/' . $this->moduleName, $language['code']);

            $xmlFile = mb_strtolower(preg_replace('/\s+/', '', $feed['filename']), 'UTF-8') . '.xml';
            $xmlUrl = HTTPS_SERVER . $this->moduleConfig->get('folder') . '/' . $xmlFile;

            $xml['date'] = date('d.m.Y H:i:s');
            $xml['url'] = $xmlUrl;
            $xml['language'] = $language['code'];
            $xml['currency'] = $currency['code'];
            $xml['currency_rate'] = round($currency['value'], 2);

            $shopName = $this->moduleConfig->get('shop_name');
            $companyName = $this->moduleConfig->get('company_name');
            $shopDescription = $this->moduleConfig->get('shop_description');
            $shopCountryId = $this->moduleConfig->get('shop_country', $this->config->get('config_country_id'));
            $deliveryService = $this->moduleConfig->get('delivery_service');
            $deliveryDays = $this->moduleConfig->get('delivery_days', 1);
            $deliveryPrice = $this->moduleConfig->get('delivery_price', 0);
            $warrantyText = $this->moduleConfig->get('warranty_text');

            $this->load->model('localisation/country');
            $country = $this->model_localisation_country->getCountry($shopCountryId);

            $xml['shop_name'] = $this->cleanTextForXml($shopName[$language['language_id']] ?? $this->config->get('config_name'));
            $xml['company_name'] = $this->cleanTextForXml($companyName[$language['language_id']] ?? $this->config->get('config_owner'));
            $xml['shop_description'] = $this->cleanTextForXml($shopDescription[$language['language_id']] ?? $this->config->get('config_meta_description'));
            $xml['shop_country'] = $country['iso_code_2'];
            $xml['delivery_service'] = $this->cleanTextForXml($deliveryService[$language['language_id']] ?? $this->config->get('config_name'));
            $xml['delivery_days'] = (int)$deliveryDays;
            $xml['delivery_price'] = (int)$deliveryPrice;
            $xml['warranty_text'] = $this->cleanTextForXml($warrantyText[$language['language_id']] ?? '');

            $categoryList = $feedConfig->get('category_list');
            $categoryRelated = $feedConfig->get('category_related');
            $categoryRelatedIds = $feedConfig->get('category_related_ids');
            $brandList = $feedConfig->get('brand_list');

            if (!empty($categoryList)) {
                $xml['categories'] = $this->getCategories(
                    $categoryList,
                    $categoryRelated,
                    $categoryRelatedIds,
                    $language['language_id']
                );
            } else {
                $xml['categories'] = $this->getCategoriesFromProducts(
                    $categoryRelated,
                    $categoryRelatedIds,
                    $language['language_id'],
                    $brandList
                );
            }

            $filterData = [
                'filter_category' => $categoryList ?: false,
                'filter_manufacturer' => $brandList ?: false,
                'filter_language_id' => $language['language_id'],
            ];

            $productTotal = $this->model_extension_feed_aw_xml_feed->getTotalProducts($filterData);

            $batchSize = $this->moduleConfig->get('batch_size', 250);

            $pages = ceil($productTotal / $batchSize) - 1;

            $settings = [
                'image_origin' => $feed['image_origin'],
                'category_list' => $categoryList,
                'brand_list' => $brandList,
                'language_id' => $language['language_id'],
                'image_width' => $feedConfig->get('image_width', 800),
                'image_height' => $feedConfig->get('image_height', 800),
                'image_count' => $feed['image_count'],
                'category_related' => $categoryRelated,
                'category_related_ids' => $categoryRelatedIds,
                'attribute_list' => $feedConfig->get('attribute_list', []),
                'attribute_warranty' => $feedConfig->get('attribute_warranty', 0),
                'option_list' => $feedConfig->get('option_list', []),
                'option_color' => $feedConfig->get('option_color', 0),
                'option_size' => $feedConfig->get('option_size', 0),
                'stock_status_available' => $this->moduleConfig->get('stock_status_available', []),
                'delivery_days' => $deliveryDays,
                'delivery_price' => $deliveryPrice,
                'currency' => $currency,
            ];

            $file = str_replace('catalog', $this->moduleConfig->get('folder'), DIR_APPLICATION) . '/' . $xmlFile;
            $dir = dirname($file);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $header = $this->awCore->render('extension/feed/' . $this->moduleName . '/layout/' . $feed['template'] . '/header', $xml);
            $footer = $this->awCore->render('extension/feed/' . $this->moduleName . '/layout/' . $feed['template'] . '/footer', $xml);

            file_put_contents($file, $header, LOCK_EX);

            for ($page = 0; $page <= $pages; $page++) {
                $offers = $this->getProducts($settings, $page, $batchSize);
                $templateData = [
                    'offers' => $offers,
                    'currency' => $currency['code'],
                    'shop_country' => $xml['shop_country'],
                    'delivery_service' => $xml['delivery_service'],
                    'delivery_days' => $xml['delivery_days'],
                    'delivery_price' => $xml['delivery_price'],
                    'warranty_text' => $xml['warranty_text'],
                    'availability_in_stock' => $this->language->get('text_availability_in_stock'),
                    'availability_out_of_stock' => $this->language->get('text_availability_out_of_stock')
                ];
                $itemsXml = $this->awCore->render('extension/feed/' . $this->moduleName . '/layout/' . $feed['template'] . '/items', $templateData);
                file_put_contents($file, $itemsXml, FILE_APPEND | LOCK_EX);
            }

            file_put_contents($file, $footer, FILE_APPEND | LOCK_EX);

            return [
                'id' => $feed['feed_id'],
                'template' => $feed['template'],
                'url' => $xmlUrl
            ];
        }
    }

    protected function getProducts($setting, $page, $limit): array
    {
        $start = $page * $limit;

        $filterData = [
            'filter_category' => $setting['category_list'] ?: false,
            'filter_manufacturer' => $setting['brand_list'] ?: false,
            'filter_language_id' => $setting['language_id'],
            'start' => $start,
            'limit' => $limit,
        ];

        $this->load->model('tool/image');

        $products = $this->model_extension_feed_aw_xml_feed->getProducts($filterData);

        $productData = [];

        if ($products) {
            foreach ($products as $product) {
                if ($product['image']) {
                    if ($setting['image_origin']) {
                        $image = HTTPS_SERVER . 'image/' . $product['image'];
                    } else {
                        $image = $this->checkImageCache(
                            $product['image'],
                            $setting['image_width'],
                            $setting['image_height']
                        );
                    }
                } else {
                    $image = '';
                }

                $images = [];

                $productImages = $this->model_catalog_product->getProductImages($product['product_id']);

                $attributes = [];

                if ($setting['attribute_list']) {
                    $attributes = $this->getAttributes($product, $setting['attribute_list'], $setting['language_id']);
                }

                if ($setting['option_list']) {
                    $options = $this->getOptions($product, $setting['option_list'], $setting['language_id']);
                } else {
                    $options = [];
                }

                if ($setting['option_color']) {
                    $optionColor = $this->getOptions(
                        $product,
                        [$setting['option_color']],
                        $setting['language_id']
                    );
                } else {
                    $optionColor = [];
                }

                if ($setting['option_size']) {
                    $optionSize = $this->getOptions($product, [$setting['option_size']], $setting['language_id']);
                } else {
                    $optionSize = [];
                }

                foreach ($productImages as $productImage) {
                    if ($setting['image_origin']) {
                        $addImage = HTTPS_SERVER . 'image/' . $productImage['image'];
                    } else {
                        $addImage = $this->checkImageCache(
                            $productImage['image'],
                            $setting['image_width'],
                            $setting['image_height']
                        );
                    }
                    $images[] = $addImage;
                }

                if (count($images) > $setting['image_count']) {
                    $images = array_slice($images, $setting['image_count']);
                }

                $price = round($product['price'], 2);

                if ((float) $product['special']) {
                    $special = round($product['special'], 2);
                } else {
                    $special = false;
                }

                if ($setting['delivery_days'] > 0) {
                    $dayForms = $this->language->get('text_day_forms');
                    $dayWord = $this->getDeclension($setting['delivery_days'], $dayForms);

                    if ($setting['delivery_price'] > 0) {
                        $shipping = sprintf($this->language->get('text_delivery_info_with_price'), $setting['delivery_days'], $dayWord, $setting['delivery_price'], $setting['currency']['code']);
                    } else {
                        $shipping = sprintf($this->language->get('text_delivery_info'), $setting['delivery_days'], $dayWord);
                    }
                } else {
                    $shipping = false;
                }

                $warranty = false;
                if ($setting['attribute_warranty']) {
                    $warranty = $this->getProductAttribute($product['product_id'], $setting['attribute_warranty'], $setting['language_id']);
                }

                if (!$warranty) {
                    $warrantyText = $this->moduleConfig->get('warranty_text');
                    $warranty = $this->cleanTextForXml($warrantyText[$setting['language_id']] ?? '');
                }

                $weightUnit = $this->model_extension_feed_aw_xml_feed->getWeightUnit($product['weight_class_id'], $setting['language_id']);

                if (
                    $setting['category_related_ids'] && array_key_exists(
                        $product['category_id'],
                        $setting['category_related_ids']
                    ) && $setting['category_related_ids'][$product['category_id']]
                ) {
                    $categoryId = $setting['category_related_ids'][$product['category_id']];
                } else {
                    $categoryId = $product['category_id'];
                }

                $googleProductCategory = $this->buildGoogleCategoryPath($product['category_id'], $setting['category_related'], $setting['language_id']);

                $isAvailable = empty($setting['stock_status_available']) || in_array($product['stock_status_id'], $setting['stock_status_available']);

                if ($product['quantity'] > 0) {
                    $availabilityStatus = 'in stock';
                    $availabilityDate = '';
                } elseif ($isAvailable) {
                    $availabilityStatus = 'preorder';
                    $availabilityDate = '';
                    if ($product['date_available'] && $product['date_available'] != '0000-00-00' && $product['date_available'] > date('Y-m-d')) {
                        $availabilityDate = date('Y-m-d', strtotime($product['date_available']));
                    }
                } else {
                    $availabilityStatus = 'out of stock';
                    $availabilityDate = '';
                }

                $productData[] = [
                    'id' => $product['product_id'],
                    'category_id' => $categoryId,
                    'model' => $this->cleanTextForXml($product['model']),
                    'vendor' => $this->cleanTextForXml($product['manufacturer']),
                    'vendorCode' => $product['sku'],
                    'image' => $image,
                    'images' => $images,
                    'name' => $this->cleanTextForXml($product['name']),
                    'description' => $this->cleanTextForXml($product['description']),
                    'available' => $isAvailable ? 'true' : 'false',
                    'in_stock' => $product['quantity'] > 0,
                    'availability_status' => $availabilityStatus,
                    'availability_date' => $availabilityDate,
                    'condition' => 'new',
                    'quantity' => $product['quantity'],
                    'stock_status_id' => $product['stock_status_id'],
                    'date_available' => $product['date_available'],
                    'weight' => $product['weight'] > 0 ? round($product['weight'], 2) . ' ' . $weightUnit : '',
                    'upc' => $product['upc'],
                    'ean' => $product['ean'],
                    'jan' => $product['jan'],
                    'isbn' => $product['isbn'],
                    'mpn' => $product['mpn'],
                    'gtin' => $product['ean'] ?: $product['upc'] ?: $product['jan'] ?: $product['isbn'] ?: '',
                    'google_product_category' => $googleProductCategory,
                    'price' => $price,
                    'special' => $special,
                    'url' => $this->cleanTextForXml($this->url->link('product/product', 'product_id=' . $product['product_id']), 'url'),
                    'attributes' => $attributes,
                    'options' => $options,
                    'option_size' => $optionSize,
                    'option_color' => $optionColor,
                    'shipping' => $shipping,
                    'warranty' => $warranty
                ];
            }
        }

        return $productData;
    }

    protected function getCategories($categoryList, $related, $relatedId, $languageId): array
    {
        $this->load->model('extension/feed/' . $this->moduleName);

        $result = [];

        if ($categoryList) {
            foreach ($categoryList as $categoryId) {
                $categories = $this->model_extension_feed_aw_xml_feed->getCategory($categoryId, $languageId);

                foreach ($categories as $category) {
                    if ($relatedId && $relatedId[$category['category_id']]) {
                        $categoryId = $relatedId[$category['category_id']];
                    } else {
                        $categoryId = $category['category_id'];
                    }

                    if ($related && $related[$category['category_id']]) {
                        $categoryName = $related[$category['category_id']];
                        $googleName = $this->buildGoogleCategoryPath($category['category_id'], $related, $languageId);
                    } else {
                        $categoryName = $category['name'];
                        $googleName = $this->buildGoogleCategoryPath($category['category_id'], [], $languageId);
                    }

                    $result[] = [
                        'id' => $categoryId,
                        'name' => $this->cleanTextForXml($categoryName),
                        'google_name' => $this->cleanTextForXml($googleName),
                        'parent_id' => $category['parent_id'],
                    ];
                }
            }
        }

        return $result;
    }

    protected function getAttributes($product, $attributeList, $languageId): array
    {
        $productAttributeData = [];

        $productAttributes = $this->model_extension_feed_aw_xml_feed->getProductAttributes(
            $product['product_id'],
            $languageId,
            $attributeList
        );

        foreach ($productAttributes as $attribute) {
            $productAttributeData[] = [
                'group' => $this->cleanTextForXml($attribute['group']),
                'name' => $this->cleanTextForXml($attribute['name']),
                'value' => $this->cleanTextForXml($attribute['value']),
            ];
        }

        return $productAttributeData;
    }

    protected function getOptions($product, $optionList, $languageId): array
    {
        $options = [];

        $productOptions = $this->model_extension_feed_aw_xml_feed->getProductOptions(
            $product['product_id'],
            $languageId,
            $optionList
        );

        if ($product['special']) {
            $productPrice = $product['special'];
        } else {
            $productPrice = $product['price'];
        }

        if ($productOptions) {
            foreach ($productOptions as $option) {
                if ($option['price_prefix'] == '+') {
                    $productPrice += $option['price'];
                } elseif ($option['price_prefix'] == '-') {
                    $productPrice -= $option['price'];
                }

                if ($product['weight']) {
                    $productWeight = $product['weight'];
                } else {
                    $productWeight = '';
                }

                if ($option['weight_prefix'] == '+') {
                    $productWeight += $option['weight'];
                } elseif ($option['weight_prefix'] == '-') {
                    $productWeight -= $option['weight'];
                }

                $options[] = [
                    'id' => $option['id'],
                    'group' => $this->cleanTextForXml($option['group_name']),
                    'name' => $this->cleanTextForXml($option['name']),
                    'quantity' => $option['quantity'],
                    'price' => round($productPrice, 2),
                    'weight' => $this->weight->format($productWeight, $this->config->get('config_weight_class_id')),
                ];
            }
        }

        return $options;
    }

    protected function checkImageCache($filename, $width, $height)
    {
        $this->load->model('tool/image');
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $imageName = utf8_substr(
            $filename,
            0,
            utf8_strrpos($filename, '.')
        ) . '-' . (int) $width . 'x' . (int) $height . '.' . $extension;
        $imageCached = DIR_IMAGE . 'cache/' . $imageName;

        if (file_exists($imageCached)) {
            return HTTPS_SERVER . 'image/cache/' . $imageName;
        } else {
            return $this->model_tool_image->resize($filename, $width, $height);
        }
    }

    protected function cleanTextForXml($text, $type = 'text'): string
    {
        if (empty($text)) {
            return '';
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($type === 'url') {
            $text = str_replace(['&', '"', "'", '<', '>', ' '], ['&amp;', '&quot;', '&apos;', '&lt;', '&gt;', '%20'], $text);
            return trim($text);
        }

        if ($type === 'image') {
            $text = str_replace('&', '&amp;', $text);
            return trim($text);
        }

        $text = strip_tags($text);

        $text = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $text);

        $text = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F\xA0]/u', '', $text);

        $text = str_replace(['&#39;', '&#039;', '&amp;#39;', '&amp;#039;', '&nbsp;'], ["'", "'", "'", "'", ' '], $text);

        $text = preg_replace('/\s+/', ' ', $text);

        $text = str_replace(['&', '<', '>', '"', "'"], ['&amp;', '&lt;', '&gt;', '&quot;', '&apos;'], $text);

        return trim($text);
    }

    private function outputResults($generatedFeeds)
    {
        $data = [
            'generated_feeds' => $generatedFeeds,
            'language' => $this->language
        ];

        if (php_sapi_name() == 'cli') {
            echo $this->awCore->render('extension/feed/' . $this->moduleName . '/result_cli', $data);
        } else {
            $this->response->addHeader('Content-Type: text/html');
            $this->response->setOutput($this->awCore->render('extension/feed/' . $this->moduleName . '/result', $data));
        }
    }

    protected function getCategoriesFromProducts($related, $relatedId, $languageId, $brandList = false): array
    {
        $this->load->model('extension/feed/' . $this->moduleName);

        $filterData = [
            'filter_category' => false,
            'filter_manufacturer' => $brandList ?: false,
            'filter_language_id' => $languageId,
            'start' => 0,
            'limit' => 10000
        ];

        $products = $this->model_extension_feed_aw_xml_feed->getProducts($filterData);

        $categoryIds = [];
        foreach ($products as $product) {
            if ($product['category_id'] && !in_array($product['category_id'], $categoryIds)) {
                $categoryIds[] = $product['category_id'];
            }
        }

        $result = [];
        foreach ($categoryIds as $categoryId) {
            $categories = $this->model_extension_feed_aw_xml_feed->getCategory($categoryId, $languageId);

            foreach ($categories as $category) {
                if ($relatedId && $relatedId[$category['category_id']]) {
                    $categoryIdResult = $relatedId[$category['category_id']];
                } else {
                    $categoryIdResult = $category['category_id'];
                }

                if ($related && $related[$category['category_id']]) {
                    $categoryName = $related[$category['category_id']];
                } else {
                    $categoryName = $category['name'];
                }

                $result[] = [
                    'id' => $categoryIdResult,
                    'name' => $this->cleanTextForXml($categoryName),
                    'parent_id' => $category['parent_id'],
                ];
            }
        }

        return array_unique($result, SORT_REGULAR);
    }

    protected function buildGoogleCategoryPath($categoryId, $related, $languageId): string
    {
        $path = [];
        $currentId = $categoryId;

        while ($currentId > 0) {
            $categories = $this->model_extension_feed_aw_xml_feed->getCategory($currentId, $languageId);

            if (!empty($categories)) {
                $category = $categories[0];

                if ($related && isset($related[$currentId])) {
                    $path[] = $related[$currentId];
                } else {
                    $path[] = $category['name'];
                }

                $currentId = $category['parent_id'];
            } else {
                break;
            }
        }

        return implode(' &gt; ', array_reverse($path));
    }

    protected function getProductAttribute($productId, $attributeId, $languageId)
    {
        $query = $this->db->query("SELECT pa.text FROM " . DB_PREFIX . "product_attribute pa WHERE pa.product_id = '" . (int)$productId . "' AND pa.attribute_id = '" . (int)$attributeId . "' AND pa.language_id = '" . (int)$languageId . "'");

        return $query->row ? $this->cleanTextForXml($query->row['text']) : false;
    }

    protected function getDeclension($number, $forms)
    {
        $n = abs($number);
        $n10 = $n % 10;
        $n100 = $n % 100;

        if ($n10 == 1 && $n100 != 11) {
            return $forms[0];
        } elseif ($n10 >= 2 && $n10 <= 4 && ($n100 < 10 || $n100 >= 20)) {
            return $forms[1];
        } else {
            return $forms[2];
        }
    }
}
