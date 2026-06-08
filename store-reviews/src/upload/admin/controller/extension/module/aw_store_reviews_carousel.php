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
    private \Alexwaha\Language $language;
    private array $error = [];
    private string $routeExtension;
    private array $params;
    private array $tokenData;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language = $this->awCore->getLanguage();
        $this->tokenData = $this->awCore->getToken();
        $this->params = $this->language->load('extension/module/' . $this->moduleName);
        $this->params['token'] = $this->tokenData['token'];
        $this->params['token_param'] = $this->tokenData['param'];
        $this->params['module_name'] = $this->moduleName;

        $this->routeExtension = $this->awCore->isLegacy()
            ? 'extension/extension'
            : 'marketplace/extension';
    }

    public function index(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $moduleId = $this->request->get['module_id'] ?? 0;

        $this->params['action'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/store',
            $this->tokenData['param'] . ($moduleId ? '&module_id=' . $moduleId : '&type=module'),
            true
        );

        $this->params['cancel'] = $this->url->link(
            $this->routeExtension,
            $this->tokenData['param'] . '&type=module',
            true
        );

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $moduleData = $this->awCore->getModule($moduleId);

        $this->params['name'] = $this->request->post['name'] ?? $moduleData['name'] ?? '';
        $this->params['title'] = $this->request->post['title'] ?? $moduleData['title'] ?? [];
        $this->params['limit'] = $this->request->post['limit'] ?? $moduleData['limit'] ?? 6;
        $this->params['per_view'] = $this->request->post['per_view'] ?? $moduleData['per_view'] ?? 1;
        $this->params['status'] = $this->request->post['status'] ?? $moduleData['status'] ?? false;

        $reviewIds = $this->request->post['reviews'] ?? $moduleData['reviews'] ?? [];

        $this->params['reviews'] = [];
        $this->load->model('extension/module/aw_store_reviews');

        foreach ($reviewIds as $reviewId) {
            $review = $this->model_extension_module_aw_store_reviews->getReview($reviewId);
            if ($review) {
                $this->params['reviews'][] = [
                    'review_id' => $review['review_id'],
                    'name'      => $review['author'] . ($review['city'] ? ', ' . $review['city'] : '') . ' - ' . mb_substr($review['text'], 0, 50) . '...',
                ];
            }
        }

        $this->params['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true),
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link(
                    'extension/module/' . $this->moduleName,
                    $this->tokenData['param'] . ($moduleId ? '&module_id=' . $moduleId : ''),
                    true
                ),
            ],
        ];

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName, $this->params));
    }

    public function store(): void
    {
        $moduleId = $this->request->get['module_id'] ?? 0;

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->awCore->setModule($this->moduleName, $this->request->post, $moduleId);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                $this->routeExtension,
                $this->tokenData['param'] . '&type=module',
                true
            ));
        }

        $this->index();
    }

    public function autocomplete(): void
    {
        $json = [];

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('extension/module/aw_store_reviews');

            $filter = trim($this->request->get['filter_name']);

            $results = $this->model_extension_module_aw_store_reviews->getReviews([
                'start' => 0,
                'limit' => 20,
            ]);

            foreach ($results as $result) {
                $label = $result['author'] . ($result['city'] ? ', ' . $result['city'] : '') . ' - ' . mb_substr($result['text'], 0, 60) . '...';

                if (!$filter || mb_stripos($label, $filter) !== false) {
                    $json[] = [
                        'review_id' => $result['review_id'],
                        'name'      => $label,
                    ];
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function validate(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['name'])) {
            if (utf8_strlen($this->request->post['name']) < 1 || utf8_strlen($this->request->post['name']) > 255) {
                $this->error['name'] = $this->language->get('error_name');
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    public function exportConfig(): void
    {
        if (!$this->validate()) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'error' => $this->language->get('error_permission'),
            ]));
            return;
        }

        try {
            $jsonData = $this->awCore->exportConfig($this->moduleName);
            $filename = $this->moduleName . '_settings_' . date('Y-m-d_H-i-s') . '.json';

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
            $this->response->setOutput($jsonData);
        } catch (Exception $e) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'error' => sprintf($this->language->get('error_import_failed'), $e->getMessage()),
            ]));
        }
    }

    public function importConfig(): void
    {
        $json = [];

        if (!$this->validate()) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->files['import_file']) && is_uploaded_file($this->request->files['import_file']['tmp_name'])) {
                try {
                    $fileContent = file_get_contents($this->request->files['import_file']['tmp_name']);

                    if ($fileContent === false) {
                        throw new Exception($this->language->get('error_import_read_file'));
                    }

                    $this->awCore->importConfig($this->moduleName, $fileContent);
                    $json['success'] = $this->language->get('text_import_success');
                } catch (Exception $e) {
                    $json['error'] = sprintf($this->language->get('error_import_failed'), $e->getMessage());
                }
            } else {
                $json['error'] = $this->language->get('error_import_file');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function install(): void
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_' . $this->moduleName, ['module_' . $this->moduleName . '_status' => '1']);
        $this->installPermissions();
    }

    public function uninstall(): void
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);
    }

    protected function installPermissions(): void
    {
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'access',
            'extension/module/' . $this->moduleName
        );
        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'modify',
            'extension/module/' . $this->moduleName
        );
    }
}
