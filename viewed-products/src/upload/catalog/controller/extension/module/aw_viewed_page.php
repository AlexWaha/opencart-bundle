<?php

/**
 * Viewed Products - account page controller (AJAX only)
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwViewedPage extends Controller
{
    private string $moduleName = 'aw_viewed';

    private ?\Alexwaha\Config $moduleConfig = null;

    private const COOKIE = 'aw_viewed';

    public function __construct($registry)
    {
        parent::__construct($registry);

        if ($this->registry->has('awCore')) {
            $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
        }
    }

    public function index(): void
    {
        if ($this->moduleConfig === null || !$this->moduleConfig->get('page_enabled', true)) {
            $this->load->controller('error/not_found');
            return;
        }

        $this->load->language('extension/module/aw_viewed_page');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->setRobots('noindex,follow');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')],
            ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/module/aw_viewed_page')],
        ];

        $data['list_url'] = $this->url->link('extension/module/aw_viewed_page/list');
        $data['delete_url'] = $this->url->link('extension/module/aw_viewed/delete');
        $data['page_limit'] = (int) $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');

        if ($data['page_limit'] < 1) {
            $data['page_limit'] = 15;
        }

        $this->document->addStyle('catalog/view/javascript/aw_viewed/aw_viewed.css');
        $this->document->addScript('catalog/view/javascript/aw_viewed/aw_viewed.js');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->awCore->render('extension/aw_viewed/page', $data));
    }

    public function list(): void
    {
        if ($this->moduleConfig === null || !$this->moduleConfig->get('page_enabled', true)) {
            $this->response->setOutput('');
            return;
        }

        $this->load->language('extension/module/aw_viewed_page');
        $this->load->model('extension/module/' . $this->moduleName);
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $page = isset($this->request->post['page']) ? (int) $this->request->post['page'] : 1;
        $limit = isset($this->request->post['limit']) ? (int) $this->request->post['limit'] : (int) $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');

        if ($limit < 1) {
            $limit = 15;
        }

        $token = $this->request->cookie[self::COOKIE] ?? '';
        $start = ($page - 1) * $limit;

        $ids = $this->model_extension_module_aw_viewed->getViewedProductIds($start, $limit, 0, $token);
        $total = $this->model_extension_module_aw_viewed->getTotalViewedProduct($token);

        $width = (int) $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
        $height = (int) $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');

        $data['text_empty'] = $this->language->get('text_empty');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_cart'] = $this->language->get('button_cart');
        $data['button_wishlist'] = $this->language->get('button_wishlist');
        $data['button_compare'] = $this->language->get('button_compare');
        $data['text_tax'] = $this->language->get('text_tax');
        $data['delete'] = $this->customer->isLogged();
        $data['products'] = $this->buildProducts($ids, $width ?: 200, $height ?: 200);

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('extension/module/aw_viewed_page', 'page={page}');
        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf(
            $this->language->get('text_pagination'),
            $total ? (($page - 1) * $limit) + 1 : 0,
            ((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit),
            $total,
            $limit ? ceil($total / $limit) : 0
        );

        $this->response->setOutput($this->awCore->render('extension/aw_viewed/page_list', $data));
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

            if ($this->config->get('config_tax')) {
                $tax = $this->currency->format((float) $info['special'] ?: $info['price'], $this->session->data['currency']);
            } else {
                $tax = false;
            }

            $rating = $this->config->get('config_review_status') ? (int) $info['rating'] : false;

            $products[] = [
                'product_id'  => $productId,
                'thumb'       => $this->model_tool_image->resize($image, $width, $height),
                'name'        => $info['name'],
                'description' => utf8_substr(trim(strip_tags(html_entity_decode($info['description'], ENT_QUOTES, 'UTF-8'))), 0, (int) $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                'price'       => $price,
                'special'     => $special,
                'tax'         => $tax,
                'rating'      => $rating,
                'href'        => $this->url->link('product/product', 'product_id=' . $productId),
            ];
        }

        return $products;
    }
}
