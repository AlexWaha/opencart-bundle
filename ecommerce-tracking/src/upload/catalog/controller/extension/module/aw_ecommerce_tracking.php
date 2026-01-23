<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwEcommerceTracking extends Controller
{
    private string $moduleName = 'aw_ecommerce_tracking';

    private \Alexwaha\Config $moduleConfig;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
    }

    public function isEnabled(): bool
    {
        return $this->registry->has('awCore') && $this->moduleConfig->get('status', false);
    }

    public function getTrackingCode(): string
    {
        $codeConfig = (string) $this->moduleConfig->get('tracking_code', '');
        $trackingCode = html_entity_decode($codeConfig, ENT_QUOTES, 'UTF-8');

        return $this->isEnabled() ? $trackingCode : '';
    }

    public function getTrackingCodeBody(): string
    {
        $codeConfig = (string) $this->moduleConfig->get('tracking_code_body', '');
        $trackingCodeBody = html_entity_decode($codeConfig, ENT_QUOTES, 'UTF-8');

        return $this->isEnabled() ? $trackingCodeBody : '';
    }

    public function getHeader(): string
    {
        $view = $this->awCore->view->render('extension/aw_ecommerce_tracking/header', [
            'trackingCode' => $this->getTrackingCode(),
            'jsConfig' => json_encode($this->getSettingForJs(), JSON_UNESCAPED_UNICODE),
        ]);

        return $this->isEnabled() ? $view : '';
    }

    public function getBody(): string
    {
        return $this->getTrackingCodeBody();
    }

    public function isDebugMode(): bool
    {
        return $this->isEnabled() && $this->moduleConfig->get('debug_mode', false);
    }

    public function getCurrencyCode(): string
    {
        $currencyFormat = $this->moduleConfig->get('currency_format', 'session');

        return $currencyFormat === 'config' ? $this->config->get('config_currency') : ($this->session->data['currency'] ?? $this->config->get('config_currency'));
    }

    public function formatPrice(float $price, int $taxClassId = 0): float
    {
        $config = $this->moduleConfig;
        $currencyCode = $this->getCurrencyCode();

        if ($config->get('price_with_tax', true) && $taxClassId) {
            $price = $this->tax->calculate($price, $taxClassId, $this->config->get('config_tax'));
        }

        $price = $this->currency->convert($price, $this->config->get('config_currency'), $currencyCode);

        return round($price, 2);
    }

    public function formatOptions(array $options): string
    {
        if (empty($options) || !$this->moduleConfig->get('send_product_options', true)) {
            return '';
        }

        $variants = [];

        foreach ($options as $option) {
            if (isset($option['value'])) {
                $variants[] = $option['value'];
            } elseif (isset($option['name'])) {
                $variants[] = $option['name'];
            }
        }

        return implode(' / ', $variants);
    }

    public function prepareItemData(array $product, string $listName = '', string $listId = '', int $index = 0): array
    {
        $this->load->model('extension/module/' . $this->moduleName);

        $productId = (int) ($product['product_id'] ?? 0);

        $item = [
            'item_id' => (string) $productId,
            'item_name' => $this->cleanText($product['name'] ?? ''),
            'price' => $this->formatPrice(
                (float) ($product['special'] ?? $product['price'] ?? 0),
                (int) ($product['tax_class_id'] ?? 0)
            ),
            'quantity' => (int) ($product['quantity'] ?? 1),
        ];

        if (!empty($product['manufacturer'])) {
            $item['item_brand'] = $this->cleanText($product['manufacturer']);
        } elseif ($productId) {
            $manufacturerName = $this->{'model_extension_module_' . $this->moduleName}->getManufacturerName($productId);
            if ($manufacturerName) {
                $item['item_brand'] = $this->cleanText($manufacturerName);
            }
        }

        if (!empty($product['category'])) {
            $item['item_category'] = $this->cleanText($product['category']);
        } elseif ($productId) {
            $categoryName = $this->{'model_extension_module_' . $this->moduleName}->getCategoryName($productId);
            if ($categoryName) {
                $item['item_category'] = $this->cleanText($categoryName);
            }
        }

        if (!empty($product['option'])) {
            $variant = $this->formatOptions($product['option']);
            if ($variant) {
                $item['item_variant'] = $variant;
            }
        }

        if (isset($product['special']) && isset($product['price']) && $product['special'] < $product['price']) {
            $originalPrice = $this->formatPrice((float) $product['price'], (int) ($product['tax_class_id'] ?? 0));
            $item['discount'] = round($originalPrice - $item['price'], 2);
        }

        if ($listName) {
            $item['item_list_name'] = $listName;
        }

        if ($listId) {
            $item['item_list_id'] = $listId;
        }

        if ($index > 0) {
            $item['index'] = $index;
        }

        return $item;
    }

    public function prepareViewItemList(array $products, string $listName, string $listId = ''): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $items = [];
        $index = 1;

        foreach ($products as $product) {
            $items[] = $this->prepareItemData($product, $listName, $listId, $index);
            $index++;
        }

        return [
            'event' => 'view_item_list',
            'ecommerce' => [
                'currency' => $this->getCurrencyCode(),
                'item_list_name' => $listName,
                'item_list_id' => $listId ?: $this->generateListId($listName),
                'items' => $items,
            ],
        ];
    }

    public function prepareViewItem(array $product): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $item = $this->prepareItemData($product);

        return [
            'event' => 'view_item',
            'ecommerce' => [
                'currency' => $this->getCurrencyCode(),
                'value' => $item['price'] * ($item['quantity'] ?? 1),
                'items' => [$item],
            ],
        ];
    }

    public function prepareAddToCart(array $product): array
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_add_to_cart', true)) {
            return [];
        }

        $item = $this->prepareItemData($product);

        return [
            'event' => 'add_to_cart',
            'ecommerce' => [
                'currency' => $this->getCurrencyCode(),
                'value' => $item['price'] * ($item['quantity'] ?? 1),
                'items' => [$item],
            ],
        ];
    }

    public function prepareRemoveFromCart(array $product): array
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_remove_from_cart', true)) {
            return [];
        }

        $item = $this->prepareItemData($product);

        return [
            'event' => 'remove_from_cart',
            'ecommerce' => [
                'currency' => $this->getCurrencyCode(),
                'value' => $item['price'] * ($item['quantity'] ?? 1),
                'items' => [$item],
            ],
        ];
    }

    public function prepareViewCart(array $products): array
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_view_cart', true)) {
            return [];
        }

        $items = [];
        $totalValue = 0;

        foreach ($products as $product) {
            $item = $this->prepareItemData($product);
            $items[] = $item;
            $totalValue += $item['price'] * ($item['quantity'] ?? 1);
        }

        return [
            'event' => 'view_cart',
            'ecommerce' => [
                'currency' => $this->getCurrencyCode(),
                'value' => round($totalValue, 2),
                'items' => $items,
            ],
        ];
    }

    public function prepareBeginCheckout(array $products): array
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_begin_checkout', true)) {
            return [];
        }

        $items = [];
        $totalValue = 0;

        foreach ($products as $product) {
            $item = $this->prepareItemData($product);
            $items[] = $item;
            $totalValue += $item['price'] * ($item['quantity'] ?? 1);
        }

        return [
            'event' => 'begin_checkout',
            'ecommerce' => [
                'currency' => $this->getCurrencyCode(),
                'value' => round($totalValue, 2),
                'items' => $items,
            ],
        ];
    }

    public function prepareAddShippingInfo(array $products, string $shippingTier = ''): array
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_shipping_info', true)) {
            return [];
        }

        $items = [];
        $totalValue = 0;

        foreach ($products as $product) {
            $item = $this->prepareItemData($product);
            $items[] = $item;
            $totalValue += $item['price'] * ($item['quantity'] ?? 1);
        }

        $data = [
            'event' => 'add_shipping_info',
            'ecommerce' => [
                'currency' => $this->getCurrencyCode(),
                'value' => round($totalValue, 2),
                'items' => $items,
            ],
        ];

        if ($shippingTier) {
            $data['ecommerce']['shipping_tier'] = $shippingTier;
        }

        return $data;
    }

    public function prepareAddPaymentInfo(array $products, string $paymentType = ''): array
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_payment_info', true)) {
            return [];
        }

        $items = [];
        $totalValue = 0;

        foreach ($products as $product) {
            $item = $this->prepareItemData($product);
            $items[] = $item;
            $totalValue += $item['price'] * ($item['quantity'] ?? 1);
        }

        $data = [
            'event' => 'add_payment_info',
            'ecommerce' => [
                'currency' => $this->getCurrencyCode(),
                'value' => round($totalValue, 2),
                'items' => $items,
            ],
        ];

        if ($paymentType) {
            $data['ecommerce']['payment_type'] = $paymentType;
        }

        return $data;
    }

    public function preparePurchase(int $orderId): array
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_purchase', true)) {
            return [];
        }

        $this->load->model('extension/module/' . $this->moduleName);
        $model = $this->{'model_extension_module_' . $this->moduleName};

        $orderProducts = $model->getOrderProducts($orderId);
        $orderTotals = $model->getOrderTotals($orderId);
        $orderInfo = $model->getOrderInfo($orderId);

        if (empty($orderProducts) || empty($orderInfo)) {
            return [];
        }

        $items = [];

        foreach ($orderProducts as $product) {
            $items[] = $this->prepareItemData($product);
        }

        $data = [
            'event' => 'purchase',
            'ecommerce' => [
                'transaction_id' => (string) $orderId,
                'currency' => $orderInfo['currency_code'] ?? $this->getCurrencyCode(),
                'value' => round((float) $orderTotals['total'], 2),
                'items' => $items,
            ],
        ];

        if ($this->moduleConfig->get('include_tax', true) && $orderTotals['tax'] > 0) {
            $data['ecommerce']['tax'] = round((float) $orderTotals['tax'], 2);
        }

        if ($this->moduleConfig->get('include_shipping', true) && $orderTotals['shipping'] > 0) {
            $data['ecommerce']['shipping'] = round((float) $orderTotals['shipping'], 2);
        }

        if ($this->moduleConfig->get('include_coupons', true) && !empty($orderTotals['coupons'])) {
            $data['ecommerce']['coupon'] = implode(', ', $orderTotals['coupons']);
        }

        return $data;
    }

    public function prepareLogin(): array
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_login', true)) {
            return [];
        }

        return [
            'event' => 'login',
            'method' => 'website',
        ];
    }

    public function prepareSignUp(): array
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_signup', true)) {
            return [];
        }

        return [
            'event' => 'sign_up',
            'method' => 'website',
        ];
    }

    public function prepareAddToWishlist(array $product): array
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_wishlist', true)) {
            return [];
        }

        $item = $this->prepareItemData($product);

        return [
            'event' => 'add_to_wishlist',
            'ecommerce' => [
                'currency' => $this->getCurrencyCode(),
                'value' => $item['price'],
                'items' => [$item],
            ],
        ];
    }

    public function renderDataLayer(array $eventData): string
    {
        if (empty($eventData)) {
            return '';
        }

        $customDimensions = $this->moduleConfig->get('custom_dimensions', '');
        if ($customDimensions) {
            $custom = json_decode($customDimensions, true);
            if (is_array($custom)) {
                $eventData = array_merge($eventData, $custom);
            }
        }

        return $this->awCore->view->render('extension/aw_ecommerce_tracking/tracking', [
            'eventDataJson' => json_encode($eventData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'productsDataJson' => '',
            'debug' => $this->isDebugMode(),
        ]);
    }

    private function formatProductsData(array $eventData, array $customKeys = []): array
    {
        $result = [];

        if (!empty($eventData['ecommerce']['items'])) {
            foreach ($eventData['ecommerce']['items'] as $index => $item) {
                $key = $customKeys[$index] ?? $item['item_id'];
                $result[$key] = [
                    'id' => $item['item_id'],
                    'product_id' => $item['item_id'],
                    'name' => $item['item_name'],
                    'price' => $item['price'],
                    'brand' => $item['item_brand'] ?? '',
                    'manufacturer' => $item['item_brand'] ?? '',
                    'category' => $item['item_category'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                ];
            }
        }

        return $result;
    }

    private function renderView(array $eventData, array $productsData = []): string
    {
        if (empty($eventData) && empty($productsData)) {
            return '';
        }

        $customDimensions = $this->moduleConfig->get('custom_dimensions', '');
        if ($customDimensions && !empty($eventData)) {
            $custom = json_decode($customDimensions, true);
            if (is_array($custom)) {
                $eventData = array_merge($eventData, $custom);
            }
        }

        return $this->awCore->view->render('extension/aw_ecommerce_tracking/tracking', [
            'eventDataJson' => !empty($eventData) ? json_encode(
                $eventData,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) : '',
            'productsDataJson' => !empty($productsData) ? json_encode(
                $productsData,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) : '',
            'debug' => $this->isDebugMode(),
        ]);
    }

    private function extractProducts(array $results, string $category = '', string $manufacturer = ''): array
    {
        $products = [];

        foreach ($results as $result) {
            $product = [
                'product_id' => $result['product_id'],
                'name' => $result['name'],
                'price' => !empty($result['special']) ? $result['special'] : $result['price'],
                'special' => $result['special'] ?? null,
                'tax_class_id' => $result['tax_class_id'] ?? 0,
                'manufacturer' => $result['manufacturer'] ?? $manufacturer,
            ];

            if ($category) {
                $product['category'] = $category;
            }

            $products[] = $product;
        }

        return $products;
    }

    private function extractCartProducts(array $cartProducts): array
    {
        $products = [];

        foreach ($cartProducts as $product) {
            $products[] = [
                'product_id' => $product['product_id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $product['quantity'],
                'tax_class_id' => $product['tax_class_id'] ?? 0,
                'option' => $product['option'] ?? [],
            ];
        }

        return $products;
    }

    public function category(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_category', true)) {
            return '';
        }

        $results = $args['products'] ?? [];
        if (empty($results)) {
            return '';
        }

        $categoryInfo = $args['category_info'] ?? [];
        $categoryId = $args['category_id'] ?? 0;

        $categoryName = $categoryInfo['name'] ?? '';
        $products = $this->extractProducts($results, $categoryName);

        $listName = $categoryName ?: 'Category';
        $listId = 'category_' . $categoryId;

        $eventData = $this->prepareViewItemList($products, $listName, $listId);
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function search(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_search', true)) {
            return '';
        }

        $results = $args['products'] ?? [];
        if (empty($results)) {
            return '';
        }

        $products = $this->extractProducts($results);

        $eventData = $this->prepareViewItemList($products, 'Search Results', 'search_results');
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function manufacturer(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_manufacturer', true)) {
            return '';
        }

        $results = $args['products'] ?? [];
        if (empty($results)) {
            return '';
        }

        $manufacturerInfo = $args['manufacturer_info'] ?? [];
        $manufacturerId = $args['manufacturer_id'] ?? 0;

        $manufacturerName = $manufacturerInfo['name'] ?? '';
        $products = $this->extractProducts($results, '', $manufacturerName);

        $listName = $manufacturerName ?: 'Manufacturer';
        $listId = 'manufacturer_' . $manufacturerId;

        $eventData = $this->prepareViewItemList($products, $listName, $listId);
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function special(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_special', true)) {
            return '';
        }

        $results = $args['products'] ?? [];
        if (empty($results)) {
            return '';
        }

        $products = $this->extractProducts($results);

        $eventData = $this->prepareViewItemList($products, 'Special Offers', 'special_offers');
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function product(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_product', true)) {
            return '';
        }

        $productInfo = $args['product_info'] ?? [];
        if (!$productInfo) {
            return '';
        }

        $product = [
            'product_id' => $productInfo['product_id'],
            'name' => $productInfo['name'],
            'price' => !empty($productInfo['special']) ? $productInfo['special'] : $productInfo['price'],
            'special' => $productInfo['special'] ?? null,
            'tax_class_id' => $productInfo['tax_class_id'],
            'manufacturer' => $productInfo['manufacturer'] ?? '',
        ];

        $eventData = $this->prepareViewItem($product);
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function cart(): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_view_cart', true)) {
            return '';
        }

        $cartProducts = $this->cart->getProducts();
        if (empty($cartProducts)) {
            return '';
        }

        $products = [];
        $customKeys = [];

        foreach ($cartProducts as $cartProduct) {
            $customKeys[] = $cartProduct['cart_id'];
            $products[] = [
                'cart_id' => $cartProduct['cart_id'],
                'product_id' => $cartProduct['product_id'],
                'name' => $cartProduct['name'],
                'price' => $cartProduct['price'],
                'quantity' => $cartProduct['quantity'],
                'tax_class_id' => $cartProduct['tax_class_id'] ?? 0,
                'option' => $cartProduct['option'] ?? [],
            ];
        }

        $eventData = $this->prepareViewCart($products);
        $productsData = $this->formatProductsData($eventData, $customKeys);

        return $this->renderView($eventData, $productsData);
    }

    public function checkout(): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_begin_checkout', true)) {
            return '';
        }

        $cartProducts = $this->cart->getProducts();
        if (empty($cartProducts)) {
            return '';
        }

        $products = $this->extractCartProducts($cartProducts);
        $eventData = $this->prepareBeginCheckout($products);
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function success(array $args): string
    {
        $orderId = (int) ($args['order_id'] ?? 0);

        if (!$orderId || !$this->isEnabled() || !$this->moduleConfig->get('track_purchase', true)) {
            return '';
        }

        $eventData = $this->preparePurchase($orderId);

        return $this->renderView($eventData);
    }

    public function setLoginFlag(): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_login', true)) {
            return '';
        }

        $this->session->data['awTrackLogin'] = true;

        return '';
    }

    public function setSignupFlag(): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_signup', true)) {
            return '';
        }

        $this->session->data['awTrackSignup'] = true;

        return '';
    }

    public function accountLogin(): string
    {
        if (!$this->isEnabled() || empty($this->session->data['awTrackLogin'])) {
            return '';
        }

        unset($this->session->data['awTrackLogin']);

        $eventData = $this->prepareLogin();

        return $this->renderView($eventData);
    }

    public function accountSuccess(): string
    {
        if (!$this->isEnabled() || empty($this->session->data['awTrackSignup'])) {
            return '';
        }

        unset($this->session->data['awTrackSignup']);

        $eventData = $this->prepareSignUp();

        return $this->renderView($eventData);
    }

    public function moduleFeatured(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_module_featured', true)) {
            return '';
        }

        $results = $args['products'] ?? [];
        if (empty($results)) {
            return '';
        }

        $products = [];
        foreach ($results as $productInfo) {
            $products[] = [
                'product_id' => $productInfo['product_id'],
                'name' => $productInfo['name'],
                'price' => !empty($productInfo['special']) ? $productInfo['special'] : $productInfo['price'],
                'special' => $productInfo['special'] ?? null,
                'tax_class_id' => $productInfo['tax_class_id'] ?? 0,
                'manufacturer' => $productInfo['manufacturer'] ?? '',
            ];
        }

        $eventData = $this->prepareViewItemList($products, 'Featured Products', 'module_featured');
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function moduleLatest(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_module_latest', true)) {
            return '';
        }

        $results = $args['products'] ?? [];
        if (empty($results)) {
            return '';
        }

        $products = $this->extractProducts($results);

        $eventData = $this->prepareViewItemList($products, 'Latest Products', 'module_latest');
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function moduleBestseller(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_module_bestseller', true)) {
            return '';
        }

        $results = $args['products'] ?? [];
        if (empty($results)) {
            return '';
        }

        $products = $this->extractProducts($results);

        $eventData = $this->prepareViewItemList($products, 'Bestseller Products', 'module_bestseller');
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function moduleSpecial(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_module_special', true)) {
            return '';
        }

        $results = $args['products'] ?? [];
        if (empty($results)) {
            return '';
        }

        $products = $this->extractProducts($results);

        $eventData = $this->prepareViewItemList($products, 'Special Products', 'module_special');
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function moduleViewed(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_module_aw_viewed', true)) {
            return '';
        }

        $results = $args['products'] ?? [];
        if (empty($results)) {
            return '';
        }

        $products = $this->extractProducts($results);

        $eventData = $this->prepareViewItemList($products, 'Recently Viewed Products', 'module_aw_viewed');
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function pageViewed(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_module_aw_viewed', true)) {
            return '';
        }

        $results = $args['products'] ?? [];
        if (empty($results)) {
            return '';
        }

        $products = $this->extractProducts($results);

        $eventData = $this->prepareViewItemList($products, 'Recently Viewed Products', 'page_aw_viewed');
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function simpleCart(array $args): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_view_cart', true)) {
            return '';
        }

        $products = $args['products'] ?? [];
        if (empty($products)) {
            return '';
        }

        $eventData = $this->prepareViewCart($products);
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function getCouponDiscount(array $args): float
    {
        $couponInfo = $args['coupon_info'] ?? null;
        if (empty($couponInfo)) {
            return 0.0;
        }

        $subTotal = $this->cart->getSubTotal();
        $discount = $couponInfo['type'] == 'F' ? min(
            (float) $couponInfo['discount'],
            $subTotal
        ) : $subTotal * (float) $couponInfo['discount'] / 100;

        return round($discount, 2);
    }

    public function getVoucherDiscount(array $args): float
    {
        $voucherInfo = $args['voucher_info'] ?? null;

        return empty($voucherInfo) ? 0.0 : (float) $voucherInfo['amount'];
    }

    public function viewItemList(array $products, string $listName, string $listId, string $configKey): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get($configKey, true) || empty($products)) {
            return '';
        }

        $eventData = $this->prepareViewItemList($products, $listName, $listId);
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function viewItem(array $product): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_product', true)) {
            return '';
        }

        $eventData = $this->prepareViewItem($product);
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function viewCart(array $products, bool $indexByCartId = false): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_view_cart', true) || empty($products)) {
            return '';
        }

        $eventData = $this->prepareViewCart($products);
        $customKeys = $indexByCartId ? array_keys($products) : [];
        $productsData = $this->formatProductsData($eventData, $customKeys);

        return $this->renderView($eventData, $productsData);
    }

    public function beginCheckout(array $products): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_begin_checkout', true) || empty($products)) {
            return '';
        }

        $eventData = $this->prepareBeginCheckout($products);
        $productsData = $this->formatProductsData($eventData);

        return $this->renderView($eventData, $productsData);
    }

    public function purchase(int $orderId): string
    {
        if (!$orderId || !$this->isEnabled() || !$this->moduleConfig->get('track_purchase', true)) {
            return '';
        }

        $eventData = $this->preparePurchase($orderId);

        return $this->renderView($eventData);
    }

    public function login(): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_login', true)) {
            return '';
        }

        $eventData = $this->prepareLogin();

        return $this->renderView($eventData);
    }

    public function signUp(): string
    {
        if (!$this->isEnabled() || !$this->moduleConfig->get('track_signup', true)) {
            return '';
        }

        $eventData = $this->prepareSignUp();

        return $this->renderView($eventData);
    }

    public function getSettingForJs(): array
    {
        if (!$this->isEnabled()) {
            return ['enabled' => false];
        }

        $config = $this->moduleConfig;

        return [
            'enabled' => true,
            'debug' => $this->isDebugMode(),
            'currency' => $this->getCurrencyCode(),
            'trackAddToCart' => (bool) $config->get('track_add_to_cart', true),
            'trackRemoveFromCart' => (bool) $config->get('track_remove_from_cart', true),
            'trackSelectItem' => (bool) $config->get('track_select_item', true),
            'trackWishlist' => (bool) $config->get('track_wishlist', true),
            'trackCoupon' => (bool) $config->get('track_coupon', true),
            'trackShippingInfo' => (bool) $config->get('track_shipping_info', true),
            'trackPaymentInfo' => (bool) $config->get('track_payment_info', true),
        ];
    }

    private function cleanText(string $text): string
    {
        $text = strip_tags(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function generateListId(string $listName): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $listName));
    }
}
