<?php

/**
 * Viewed Products - catalog controller (widget + events)
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwViewed extends Controller
{
    private string $moduleName = 'aw_viewed';

    private ?\Alexwaha\Config $moduleConfig = null;

    private static bool $tracked = false;

    private const COOKIE = 'aw_viewed';

    public function __construct($registry)
    {
        parent::__construct($registry);

        if ($this->registry->has('awCore')) {
            $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
        }
    }

    /**
     * Event: catalog/controller/product/product/before.
     * Records the viewed product once per GET request.
     */
    public function track(&$route, &$data): void
    {
        if (self::$tracked || $this->moduleConfig === null) {
            return;
        }

        if (($this->request->server['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            return;
        }

        if (empty($this->request->get['product_id'])) {
            return;
        }

        self::$tracked = true;

        $storageDays = (int) $this->moduleConfig->get('storage_days', 7);
        $productLimit = (int) $this->moduleConfig->get('product_limit', 50);

        $this->load->model('extension/module/' . $this->moduleName);

        if (!$this->model_extension_module_aw_viewed->isProductViewable((int) $this->request->get['product_id'])) {
            return;
        }

        if (empty($this->request->cookie[self::COOKIE])) {
            $this->model_extension_module_aw_viewed->deleteOldViewedProduct($storageDays);
            $token = token(32);
            setcookie(self::COOKIE, $token, time() + 60 * 60 * 24 * $storageDays, '/', $this->request->server['HTTP_HOST']);
            $this->request->cookie[self::COOKIE] = $token;
        } else {
            $token = $this->request->cookie[self::COOKIE];
        }

        $this->model_extension_module_aw_viewed->addViewedProduct($token, (int) $this->request->get['product_id'], $productLimit);
    }

    /**
     * Event: catalog/controller/account/account/before.
     * Merges guest rows onto the customer after login.
     */
    public function accountLogin(&$route, &$data): void
    {
        if ($this->moduleConfig === null || !$this->customer->isLogged()) {
            return;
        }

        if (empty($this->request->cookie[self::COOKIE])) {
            return;
        }

        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_viewed->mergeCustomer($this->request->cookie[self::COOKIE]);
    }

    /**
     * Event: catalog/view/account/account/after.
     * Injects the "Viewed Products" menu link into the account page output.
     */
    public function accountMenu(&$route, &$data, &$output): void
    {
        if ($this->moduleConfig === null || !$this->moduleConfig->get('menu_link', false)) {
            return;
        }

        $this->load->language('extension/module/' . $this->moduleName);

        $languageId = (int) $this->config->get('config_language_id');
        $labels = $this->moduleConfig->get('menu_label', []);
        $label = !empty($labels[$languageId]) ? $labels[$languageId] : $this->language->get('text_viewed');

        $href = $this->url->link('extension/module/aw_viewed_page');
        $link = '<li><a href="' . $href . '">' . $label . '</a></li>';

        // Anchor on the actual wishlist link the page rendered (SEO-safe).
        $wishlistUrl = $this->url->link('account/wishlist', '', true);
        $marker = 'href="' . $wishlistUrl . '"';
        $pos = strpos($output, $marker);

        if ($pos === false) {
            return;
        }

        $liEnd = strpos($output, '</li>', $pos);

        if ($liEnd === false) {
            return;
        }

        $insertAt = $liEnd + strlen('</li>');
        $output = substr($output, 0, $insertAt) . $link . substr($output, $insertAt);
    }

    /**
     * Widget render (layout module). Returns shell + AJAX loader only.
     */
    public function index($setting): string
    {
        if ($this->moduleConfig === null) {
            return '';
        }

        $this->load->language('extension/module/' . $this->moduleName);

        $languageId = (int) $this->config->get('config_language_id');

        if (!empty($setting['title'][$languageId])) {
            $title = $setting['title'][$languageId];
        } else {
            $title = $this->language->get('heading_title_widget');
        }

        $data['heading_title'] = $title;
        $data['text_show_all'] = $this->language->get('text_show_all');
        $data['show_link'] = !empty($setting['show_link']);
        $data['page_link'] = $this->url->link('extension/module/aw_viewed_page');

        $data['module'] = isset($setting['module_id']) ? (int) $setting['module_id'] : 0;
        $data['product_id'] = isset($this->request->get['product_id']) ? (int) $this->request->get['product_id'] : 0;
        $data['limit'] = !empty($setting['limit']) ? (int) $setting['limit'] : 4;
        $data['width'] = !empty($setting['width']) ? (int) $setting['width'] : 200;
        $data['height'] = !empty($setting['height']) ? (int) $setting['height'] : 200;

        $data['products_url'] = $this->url->link('extension/module/aw_viewed/products');
        $data['delete_url'] = $this->url->link('extension/module/aw_viewed/delete');

        $this->document->addStyle('catalog/view/javascript/aw_viewed/aw_viewed.css');
        $this->document->addScript('catalog/view/javascript/aw_viewed/aw_viewed.js');

        return $this->awCore->render('extension/aw_viewed/widget', $data) ?: '';
    }

    /**
     * Widget AJAX list. Returns the products HTML fragment.
     */
    public function products(): void
    {
        if ($this->moduleConfig === null) {
            $this->response->setOutput('');
            return;
        }

        $this->load->language('extension/module/' . $this->moduleName);
        $this->load->model('extension/module/' . $this->moduleName);
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $limit = isset($this->request->post['limit']) ? (int) $this->request->post['limit'] : 4;
        $width = isset($this->request->post['width']) ? (int) $this->request->post['width'] : 200;
        $height = isset($this->request->post['height']) ? (int) $this->request->post['height'] : 200;
        $excludeId = isset($this->request->post['product_id']) ? (int) $this->request->post['product_id'] : 0;
        $token = $this->request->cookie[self::COOKIE] ?? '';

        $ids = $this->model_extension_module_aw_viewed->getViewedProductIds(0, $limit, $excludeId, $token);

        $data['delete'] = $this->customer->isLogged();
        $data['button_delete'] = $this->language->get('button_delete');
        $data['products'] = $this->buildProducts($ids, $width, $height);

        $this->response->setOutput($this->awCore->render('extension/aw_viewed/widget_list', $data));
    }

    /**
     * Shared delete endpoint (logged-in customer removes one item).
     */
    public function delete(): void
    {
        $json = [];

        if ($this->moduleConfig !== null && $this->request->server['REQUEST_METHOD'] === 'POST'
            && isset($this->request->post['product_id']) && $this->customer->isLogged()) {
            $this->load->language('extension/module/' . $this->moduleName);
            $this->load->model('extension/module/' . $this->moduleName);
            $this->model_extension_module_aw_viewed->deleteViewedProduct((int) $this->request->post['product_id']);
            $json['success'] = $this->language->get('text_deleted');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function buildProducts(array $ids, int $width, int $height): array
    {
        $products = [];

        foreach ($ids as $productId) {
            $info = $this->model_catalog_product->getProduct($productId);

            if (!$info) {
                continue;
            }

            $image = $info['image'] ? $info['image'] : 'placeholder.png';

            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($info['price'], $info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $price = false;
            }

            $special = false;
            if ((float) $info['special']) {
                $special = $this->currency->format($this->tax->calculate($info['special'], $info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            }

            $rating = $this->config->get('config_review_status') ? (int) $info['rating'] : false;

            $products[] = [
                'product_id' => $productId,
                'thumb'      => $this->model_tool_image->resize($image, $width, $height),
                'name'       => $info['name'],
                'price'      => $price,
                'special'    => $special,
                'rating'     => $rating,
                'href'       => $this->url->link('product/product', 'product_id=' . $productId),
            ];
        }

        return $products;
    }
}
