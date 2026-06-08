<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwStoreReviewsCarousel extends Controller
{
    private string $moduleName = 'aw_store_reviews_carousel';

    public function index($setting = []): string
    {
        if (!$this->registry->has('awCore')) {
            return '';
        }

        if (empty($setting['status'])) {
            return '';
        }

        $languageId = (int)$this->config->get('config_language_id');
        $limit = (int)($setting['limit'] ?? 6);
        $perView = (int)($setting['per_view'] ?? 1);

        $this->load->model('extension/module/aw_store_reviews');
        $this->load->language('extension/module/' . $this->moduleName);

        $selectedIds = $setting['reviews'] ?? [];

        if ($selectedIds) {
            $reviews = [];
            foreach ($selectedIds as $id) {
                $review = $this->model_extension_module_aw_store_reviews->getReview((int)$id);
                if ($review && $review['status']) {
                    $reviews[] = $review;
                }
            }
        } else {
            $reviews = $this->model_extension_module_aw_store_reviews->getReviews([
                'start' => 0,
                'limit' => $limit,
            ]);
        }

        if (!$reviews) {
            return '';
        }

        $this->document->addStyle('catalog/view/javascript/aw_store_reviews/swiper-bundle.min.css');
        $this->document->addStyle('catalog/view/javascript/aw_store_reviews/carousel.css');
        $this->document->addScript('catalog/view/javascript/aw_store_reviews/swiper-bundle.min.js');

        $params = [];
        $params['heading_title'] = $setting['title'][$languageId] ?? '';
        $params['per_view'] = $perView;
        $params['reviews_url'] = $this->url->link('extension/module/aw_store_reviews');
        $params['text_all_reviews'] = $this->language->get('text_all_reviews');
        $params['reviews'] = [];

        foreach ($reviews as $review) {
            $params['reviews'][] = [
                'author' => $review['author'],
                'city'   => $review['city'],
                'rating' => (int)$review['rating'],
                'text'   => nl2br(htmlspecialchars($review['text'], ENT_QUOTES, 'UTF-8')),
            ];
        }

        return $this->awCore->render('extension/module/' . $this->moduleName, $params);
    }
}
