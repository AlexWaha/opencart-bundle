<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwFaq extends Controller
{
    private string $moduleName = 'aw_faq';
    private \Alexwaha\Config $moduleConfig;
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
        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
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

        $this->load->model('extension/module/' . $this->moduleName);

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $page = (int) ($this->request->get['page'] ?? 1);
        $limit = (int) $this->config->get('config_limit_admin');

        $filterData = [
            'sort'  => 'sort_order',
            'order' => 'ASC',
            'start' => ($page - 1) * $limit,
            'limit' => $limit,
        ];

        $total = $this->model_extension_module_aw_faq->getTotalFaqs();
        $results = $this->model_extension_module_aw_faq->getFaqs($filterData);

        $this->params['faqs'] = [];

        foreach ($results as $result) {
            $this->params['faqs'][] = [
                'faq_id'     => $result['faq_id'],
                'question'   => utf8_substr(strip_tags(html_entity_decode($result['question'], ENT_QUOTES, 'UTF-8')), 0, 120),
                'sort_order'  => (int) $result['sort_order'],
                'status'     => (int) $result['status'],
                'date_added' => date('d.m.Y', strtotime($result['date_added'])),
                'edit'       => $this->url->link(
                    'extension/module/' . $this->moduleName . '/edit',
                    $this->tokenData['param'] . '&faq_id=' . $result['faq_id'],
                    true
                ),
            ];
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'] . '&page={page}',
            true
        );

        $this->params['pagination'] = $pagination->render();

        $this->params['results'] = sprintf(
            $this->language->get('text_pagination'),
            ($total) ? (($page - 1) * $limit) + 1 : 0,
            (((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit)),
            $total,
            ceil($total / $limit)
        );

        $this->params['sort_url'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/updateSortOrder',
            $this->tokenData['param'],
            true
        );

        $this->params['add'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/add',
            $this->tokenData['param'],
            true
        );

        $this->params['delete'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/delete',
            $this->tokenData['param'],
            true
        );

        $this->params['settings_url'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/settings',
            $this->tokenData['param'],
            true
        );

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
                'href' => $this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true),
            ],
        ];

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/list', $this->params));
    }

    public function add(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

        $this->document->addScript('view/javascript/summernote/summernote.js');
        $this->document->addScript('view/javascript/summernote/summernote-lite.js');
        $this->document->addStyle('view/javascript/summernote/summernote.css');

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $this->params['action'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/store',
            $this->tokenData['param'],
            true
        );

        $this->params['cancel'] = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'],
            true
        );

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
                'href' => $this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true),
            ],
        ];

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->params['faq_description'] = $this->request->post['faq_description'] ?? [];
        $this->params['sort_order'] = $this->request->post['sort_order'] ?? 0;
        $this->params['status'] = $this->request->post['status'] ?? 1;
        $this->params['faq_id'] = 0;

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/form', $this->params));
    }

    public function edit(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

        $this->document->addScript('view/javascript/summernote/summernote.js');
        $this->document->addScript('view/javascript/summernote/summernote-lite.js');
        $this->document->addStyle('view/javascript/summernote/summernote.css');

        $this->load->model('extension/module/' . $this->moduleName);

        $faqId = (int) ($this->request->get['faq_id'] ?? 0);
        $faq = $this->model_extension_module_aw_faq->getFaq($faqId);

        if (!$faq) {
            $this->session->data['error'] = $this->language->get('error_not_found');
            $this->response->redirect($this->url->link(
                'extension/module/' . $this->moduleName,
                $this->tokenData['param'],
                true
            ));
            return;
        }

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $this->params['action'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/store',
            $this->tokenData['param'] . '&faq_id=' . $faqId,
            true
        );

        $this->params['cancel'] = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'],
            true
        );

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
                'href' => $this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true),
            ],
        ];

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->params['faq_id'] = $faqId;
        $this->params['faq_description'] = $this->request->post['faq_description'] ?? $faq['descriptions'];
        $this->params['sort_order'] = $this->request->post['sort_order'] ?? $faq['sort_order'];
        $this->params['status'] = $this->request->post['status'] ?? $faq['status'];

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/form', $this->params));
    }

    public function store(): void
    {
        $this->load->model('extension/module/' . $this->moduleName);

        $faqId = (int) ($this->request->get['faq_id'] ?? 0);

        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
            if ($faqId) {
                $this->model_extension_module_aw_faq->editFaq($faqId, $this->request->post);
                $this->session->data['success'] = $this->language->get('text_success_edit');
            } else {
                $this->model_extension_module_aw_faq->addFaq($this->request->post);
                $this->session->data['success'] = $this->language->get('text_success_add');
            }

            $this->response->redirect($this->url->link(
                'extension/module/' . $this->moduleName,
                $this->tokenData['param'],
                true
            ));
        }

        if ($faqId) {
            $this->edit();
        } else {
            $this->add();
        }
    }

    public function delete(): void
    {
        $this->load->model('extension/module/' . $this->moduleName);

        if (isset($this->request->post['selected']) && $this->validate()) {
            foreach ($this->request->post['selected'] as $faqId) {
                $this->model_extension_module_aw_faq->deleteFaq((int) $faqId);
            }

            $this->session->data['success'] = $this->language->get('text_success_delete');

            $this->response->redirect($this->url->link(
                'extension/module/' . $this->moduleName,
                $this->tokenData['param'],
                true
            ));
        }

        $this->index();
    }

    public function updateSortOrder(): void
    {
        $json = ['success' => false];

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->load->model('extension/module/' . $this->moduleName);

            $order = $this->request->post['order'] ?? [];

            foreach ($order as $position => $faqId) {
                $this->model_extension_module_aw_faq->updateSortOrder((int) $faqId, (int) $position);
            }

            $json['success'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function settings(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $this->params['action'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/saveSettings',
            $this->tokenData['param'],
            true
        );

        $this->params['cancel'] = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'],
            true
        );

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('setting/store');
        $this->params['stores'] = [];
        $this->params['stores'][] = [
            'store_id' => 0,
            'name' => $this->config->get('config_name') . ' (' . $this->language->get('text_default') . ')',
        ];
        foreach ($this->model_setting_store->getStores() as $store) {
            $this->params['stores'][] = [
                'store_id' => $store['store_id'],
                'name' => $store['name'],
            ];
        }

        $this->params['status'] = $this->moduleConfig->get('status', false);
        $this->params['seo'] = $this->moduleConfig->get('seo', []);
        $this->params['seo_url'] = $this->awCore->getSeoUrls('extension/module/' . $this->moduleName) ?? [];

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
                'href' => $this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true),
            ],
        ];

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/settings', $this->params));
    }

    public function saveSettings(): void
    {
        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
            $this->awCore->setConfig($this->moduleName, $this->request->post);

            $this->awCore->setSeoUrls($this->request->post['seo_url'] ?? [], 'extension/module/' . $this->moduleName);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                'extension/module/' . $this->moduleName . '/settings',
                $this->tokenData['param'],
                true
            ));
        }

        $this->settings();
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

    private function validate(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['faq_description'])) {
            foreach ($this->request->post['faq_description'] as $languageId => $value) {
                if (utf8_strlen(trim($value['question'] ?? '')) < 1 || utf8_strlen($value['question'] ?? '') > 500) {
                    $this->error['question_' . $languageId] = $this->language->get('error_question');
                }

                if (utf8_strlen(trim(strip_tags($value['answer'] ?? ''))) < 1) {
                    $this->error['answer_' . $languageId] = $this->language->get('error_answer');
                }
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    public function install(): void
    {
        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_faq->createTables();

        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting(
            'module_' . $this->moduleName,
            ['module_' . $this->moduleName . '_status' => '1']
        );

        $this->installPermissions();
        $this->installEvents();

        $existingConfig = $this->awCore->getConfig($this->moduleName);

        if (!$existingConfig->get('status')) {
            $this->awCore->setConfig($this->moduleName, [
                'status' => 1,
            ]);
        }

        // Remove old information page FAQ (id=10) and its SEO URLs
        $this->model_extension_module_aw_faq->deleteOldFaqPage();
    }

    public function uninstall(): void
    {
        $this->uninstallEvents();

        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_faq->dropTables();

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);
        $this->awCore->removeConfig($this->moduleName);
    }

    private function installEvents(): void
    {
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('aw_faq_sitemap');
        $this->model_setting_event->addEvent(
            'aw_faq_sitemap',
            'catalog/controller/extension/feed/google_sitemap/after',
            'extension/aw_faq/event/sitemapAfter'
        );
    }

    private function uninstallEvents(): void
    {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('aw_faq_sitemap');
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
