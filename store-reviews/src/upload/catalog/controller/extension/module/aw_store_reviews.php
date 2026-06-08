<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwStoreReviews extends Controller
{
    private string $moduleName = 'aw_store_reviews';
    private ?\Alexwaha\Config $moduleConfig = null;

    public function __construct($registry)
    {
        parent::__construct($registry);

        if ($this->registry->has('awCore')) {
            $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
        }
    }

    public function isEnabled(): bool
    {
        return $this->moduleConfig !== null
            && $this->moduleConfig->get('status', false);
    }

    public function index(): void
    {
        if (!$this->isEnabled()) {
            $this->response->redirect($this->url->link('common/home'));
            return;
        }

        $language = $this->awCore->getLanguage();
        $params = $language->load('extension/module/' . $this->moduleName);

        $langId = (int)$this->config->get('config_language_id');
        $seo = $this->moduleConfig->get('seo', []);

        $metaTitle = !empty($seo['meta_title'][$langId]) ? $seo['meta_title'][$langId] : $params['heading_title'];
        $this->document->setTitle($metaTitle);

        $metaDesc = !empty($seo['meta_description'][$langId]) ? $seo['meta_description'][$langId] : '';
        if ($metaDesc) {
            $this->document->setDescription($metaDesc);
        }

        $h1 = !empty($seo['meta_h1'][$langId]) ? $seo['meta_h1'][$langId] : $params['heading_title'];
        $params['heading_title'] = $h1;

        $this->load->model('extension/module/' . $this->moduleName);

        $perPage = (int)$this->moduleConfig->get('per_page', 10);
        $page = (int)($this->request->get['page'] ?? 1);

        $totalReviews = $this->model_extension_module_aw_store_reviews->getTotalReviews();

        $reviews = $this->model_extension_module_aw_store_reviews->getReviews([
            'start' => ($page - 1) * $perPage,
            'limit' => $perPage,
        ]);

        $params['reviews'] = [];

        foreach ($reviews as $review) {
            $params['reviews'][] = [
                'author'     => $review['author'],
                'city'       => $review['city'],
                'rating'     => (int)$review['rating'],
                'text'       => nl2br(htmlspecialchars($review['text'], ENT_QUOTES, 'UTF-8')),
                'date_added' => date('d.m.Y', strtotime($review['date_added'])),
            ];
        }

        $params['breadcrumbs'] = [
            [
                'text' => $language->get('text_home'),
                'href' => $this->url->link('common/home'),
            ],
            [
                'text' => $h1,
                'href' => $this->url->link('extension/module/' . $this->moduleName),
            ],
        ];

        $pagination = new Pagination();
        $pagination->total = $totalReviews;
        $pagination->page = $page;
        $pagination->limit = $perPage;
        $pagination->url = $this->url->link('extension/module/' . $this->moduleName, '&page={page}');

        $params['pagination'] = $pagination->render();
        $params['write_url'] = $this->url->link('extension/module/' . $this->moduleName . '/write');

        $params['aw_microdata'] = $this->load->controller('extension/aw_microdata/microdata/getReviewsPage');

        $params['header'] = $this->load->controller('common/header');
        $params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('product/' . $this->moduleName, $params));
    }

    public function write(): void
    {
        $language = $this->awCore->getLanguage();
        $lang = $language->load('extension/module/' . $this->moduleName);

        $json = [];

        $name = trim($this->request->post['name'] ?? '');
        $text = trim($this->request->post['text'] ?? '');
        $rating = (int)($this->request->post['rating'] ?? 0);

        if (utf8_strlen($name) < 1 || utf8_strlen($name) > 64) {
            $json['error'] = $lang['error_author'];
        } elseif (utf8_strlen($text) < 10) {
            $json['error'] = $lang['error_text'];
        } elseif ($rating < 1 || $rating > 5) {
            $json['error'] = $lang['error_rating'];
        } else {
            $this->load->model('extension/module/' . $this->moduleName);

            $this->model_extension_module_aw_store_reviews->addReview([
                'author'     => $name,
                'city'       => '',
                'text'       => $text,
                'rating'     => $rating,
                'status'     => 0,
                'date_added' => date('Y-m-d H:i:s'),
            ]);

            $json['success'] = $lang['text_success_review'];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
