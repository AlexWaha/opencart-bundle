<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwLandingPage extends Controller
{
    private string $moduleName = 'aw_landing_page';

    private array $params;

    private \Alexwaha\Language $language;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language = $this->awCore->getLanguage();
        $this->params = $this->language->load('extension/module/' . $this->moduleName);
    }

    public function index()
    {
        $this->load->model('extension/module/' . $this->moduleName);

        $sort = $this->request->get['sort'] ?? 'pd.name';
        $order = $this->request->get['order'] ?? 'ASC';
        $page = $this->request->get['page'] ?? 1;

        $theme = $this->config->get('config_theme');

        $limit = $this->awCore->isLegacy()
            ? $this->config->get($theme . '_product_limit')
            : $this->config->get('theme_' . $theme . '_product_limit');

        $limit = (int)($this->request->get['limit'] ?? $limit);

        $landingPageId = $this->request->get['landing_page_id'] ?? 0;

        $pageInfo = $this->model_extension_module_aw_landing_page->getPage($landingPageId);

        if ($pageInfo) {
            $this->document->setTitle($pageInfo['meta_title']);
            $this->document->setDescription($pageInfo['meta_description']);

            $this->params['heading_title'] = $pageInfo['meta_h1'] ?? $pageInfo['name'];

            $this->params['short_description'] = html_entity_decode(
                $pageInfo['short_description'],
                ENT_QUOTES,
                'UTF-8'
            );

            $this->params['description'] = html_entity_decode($pageInfo['description'], ENT_QUOTES, 'UTF-8');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $this->params['breadcrumbs'] = [
                [
                    'text' => $this->language->get('text_home'),
                    'href' => $this->url->link('common/home'),
                ],
                [
                    'text' => $pageInfo['name'],
                    'href' => $this->url->link('extension/module/' . $this->moduleName, 'landing_page_id=' . $landingPageId . $url),
                ],
            ];

            $filterData = [
                'sort' => $sort,
                'order' => $order,
                'start' => ($page - 1) * $limit,
                'limit' => $limit,
            ];

            $totalProducts = $this->model_extension_module_aw_landing_page->getTotalPageProducts($landingPageId);
            $products = $this->model_extension_module_aw_landing_page->getPageProducts($landingPageId, $filterData);

            $this->params['products'] = [];

            $this->load->model('tool/image');

            $theme = $this->config->get('config_theme');

            $imageWidth = $this->awCore->isLegacy()
                ? $this->config->get($theme . '_image_product_width')
                : $this->config->get('theme_' . $theme . '_image_product_width');

            $imageHeight = $this->awCore->isLegacy()
                ? $this->config->get($theme . '_image_product_height')
                : $this->config->get('theme_' . $theme . '_image_product_height');

            $descLength = $this->awCore->isLegacy()
                ? $this->config->get($theme . '_product_description_length')
                : $this->config->get('theme_' . $theme . '_product_description_length');

            foreach ($products as $product) {
                $imagePath = $product['image'] ?: 'placeholder.png';
                $image = $this->model_tool_image->resize($imagePath, $imageWidth, $imageHeight);

                $price = ($this->customer->isLogged() || !$this->config->get('config_customer_price'))
                    ? $this->currency->format(
                        $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')),
                        $this->session->data['currency']
                    )
                    : false;

                if (!is_null($product['special']) && (float)$product['special'] >= 0) {
                    $special = $this->currency->format(
                        $this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')),
                        $this->session->data['currency']
                    );
                    $tax_price = (float)$product['special'];
                } else {
                    $special = false;
                    $tax_price = (float)$product['price'];
                }

                $tax = $this->config->get('config_tax')
                    ? $this->currency->format($tax_price, $this->session->data['currency'])
                    : false;

                $rating = $this->config->get('config_review_status')
                    ? (int)$product['rating']
                    : false;

                $description = utf8_substr(
                    trim(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8'))),
                    0,
                    $descLength
                ) . '..';

                $this->params['products'][] = [
                    'product_id'  => $product['product_id'],
                    'thumb'       => $image,
                    'name'        => $product['name'],
                    'description' => $description,
                    'price'       => $price,
                    'special'     => $special,
                    'tax'         => $tax,
                    'minimum'     => $product['minimum'] > 0 ? $product['minimum'] : 1,
                    'rating'      => $rating,
                    'href'        => $this->url->link('product/product', 'product_id=' . $product['product_id'] . $url),
                ];
            }

            $url = '';

            if (!empty($this->request->get['limit'])) {
                $url .= '&limit=' . (int)$this->request->get['limit'];
            }

            $this->params['sorts'] = [];

            $sortOptions = [
                ['text' => 'text_default', 'value' => 'p.sort_order-ASC', 'sort' => 'p.sort_order', 'order' => 'ASC'],
                ['text' => 'text_name_asc', 'value' => 'pd.name-ASC', 'sort' => 'pd.name', 'order' => 'ASC'],
                ['text' => 'text_name_desc', 'value' => 'pd.name-DESC', 'sort' => 'pd.name', 'order' => 'DESC'],
                ['text' => 'text_price_asc', 'value' => 'ps.price-ASC', 'sort' => 'ps.price', 'order' => 'ASC'],
                ['text' => 'text_price_desc', 'value' => 'ps.price-DESC', 'sort' => 'ps.price', 'order' => 'DESC'],
                ['text' => 'text_model_asc', 'value' => 'p.model-ASC', 'sort' => 'p.model', 'order' => 'ASC'],
                ['text' => 'text_model_desc', 'value' => 'p.model-DESC', 'sort' => 'p.model', 'order' => 'DESC'],
            ];

            if ($this->config->get('config_review_status')) {
                $sortOptions[] = ['text' => 'text_rating_desc', 'value' => 'rating-DESC', 'sort' => 'rating', 'order' => 'DESC'];
                $sortOptions[] = ['text' => 'text_rating_asc', 'value' => 'rating-ASC', 'sort' => 'rating', 'order' => 'ASC'];
            }

            foreach ($sortOptions as $option) {
                $this->params['sorts'][] = [
                    'text'  => $this->language->get($option['text']),
                    'value' => $option['value'],
                    'href'  => $this->url->link('extension/module/' . $this->moduleName, 'landing_page_id=' . $landingPageId . '&sort=' . $option['sort'] . '&order=' . $option['order'] . $url),
                ];
            }

            $url = '';

            if (!empty($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            if (!empty($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            $this->params['limits'] = [];

            $theme = $this->config->get('config_theme');

            $limitKey = $this->awCore->isLegacy()
                ? $theme . '_product_limit'
                : 'theme_' . $theme . '_product_limit';

            $defaultLimit = (int) $this->config->get($limitKey);

            $limits = array_unique([$defaultLimit, 25, 50, 75, 100]);

            sort($limits);

            foreach ($limits as $value) {
                $this->params['limits'][] = [
                    'text'  => $value,
                    'value' => $value,
                    'href'  => $this->url->link('extension/module/' . $this->moduleName, 'landing_page_id=' . $landingPageId . $url . '&limit=' . $value),
                ];
            }

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $pagination = new Pagination();
            $pagination->total = $totalProducts;
            $pagination->page = $page;
            $pagination->limit = $limit;
            $pagination->url = $this->url->link(
                'extension/module/' . $this->moduleName,
                'landing_page_id=' . $landingPageId . $url . '&page={page}'
            );

            $this->params['pagination'] = $pagination->render();

            $this->params['results'] = sprintf(
                $this->language->get('text_pagination'),
                $totalProducts ? (($page - 1) * $limit) + 1 : 0,
                $totalProducts < $page * $limit ? $totalProducts : ($page * $limit),
                $totalProducts,
                $limit > 0 ? ceil($totalProducts / $limit) : 0
            );

            if (isset($this->request->get['page']) && $this->request->get['page'] > ceil($totalProducts / $limit)) {
                $this->response->redirect($this->url->link('extension/module/' . $this->moduleName, 'landing_page_id=' . $landingPageId), 301);
            }

            $this->document->addLink($this->url->link('extension/module/' . $this->moduleName, 'landing_page_id=' . $landingPageId . $url), 'canonical');

            if ($page == 2) {
                $this->document->addLink($this->url->link('extension/module/' . $this->moduleName, 'landing_page_id=' . $landingPageId . $url), 'prev');
            } elseif ($page > 2) {
                $this->document->addLink($this->url->link('extension/module/' . $this->moduleName, 'landing_page_id=' . $landingPageId . $url . '&page=' . ($page - 1), true), 'prev');
            }

            if ($limit && ceil($totalProducts / $limit) > $page) {
                $this->document->addLink($this->url->link('extension/module/' . $this->moduleName, 'landing_page_id=' . $landingPageId . $url . '&page=' . ($page + 1), true), 'next');
            }
        }

        $this->params['sort'] = $sort;
        $this->params['order'] = $order;
        $this->params['limit'] = $limit;

        $this->params['continue'] = $this->url->link('common/home');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['column_right'] = $this->load->controller('common/column_right');
        $this->params['content_top'] = $this->load->controller('common/content_top');
        $this->params['content_bottom'] = $this->load->controller('common/content_bottom');
        $this->params['footer'] = $this->load->controller('common/footer');
        $this->params['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->awCore->render('product/' . $this->moduleName, $this->params));
    }
}
