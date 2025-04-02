<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwLandingPage extends Controller
{
    private $moduleName = 'aw_landing_page';

    private $language;

    private $error = [];

    private $routeExtension;

    private $params;

    private $tokenData;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language = $this->awCore->getLanguage();
        $this->tokenData = $this->awCore->getToken();
        $this->params = $this->language->load('extension/module/' . $this->moduleName);
        $this->params['token'] = $this->tokenData['token'];
        $this->params['token_param'] = $this->tokenData['param'];
        $this->routeExtension = $this->awCore->isLegacy() ? 'extension/extension' : 'marketplace/extension';
    }

    public function index()
    {
        $this->document->setTitle($this->language->get('heading_main_title'));

        $this->load->model('extension/module/' . $this->moduleName);

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $this->params['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true)
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true)
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true)
            ]
        ];

        $filterName = $this->request->get['filter_name'] ?? '';
        $sort = $this->request->get['sort'] ?? 'pd.name';
        $order = $this->request->get['order'] ?? 'ASC';
        $page = $this->request->get['page'] ?? 1;
        $limit = $this->config->get('config_limit_admin');

        $filterData = [
            'filter_name' => $filterName,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $limit,
            'limit' => $limit,
        ];

        $total = $this->model_extension_module_aw_landing_page->getPagesTotal($filterData);
        $results = $this->model_extension_module_aw_landing_page->getPages($filterData);

        $this->params['pages'] = [];

        $this->load->model('localisation/language');
        $this->load->model('setting/store');

        foreach ($results as $result) {
            $seoUrls = $this->model_extension_module_aw_landing_page->getSeoUrls(
                $result['landing_page_id'],
                $this->awCore->isLegacy()
            );

            $keywords = [];

            if ($seoUrls) {
                foreach ($seoUrls as $storeId => $language) {
                    foreach ($language as $languageId => $seoUrl) {
                        $language = $this->model_localisation_language->getLanguage($languageId);
                        $store = $this->model_setting_store->getStore($storeId);
                        $storeName = $store['name'] ?? $this->language->get('text_default');
                        $keywords[$storeName][$language['name']] = $seoUrl;
                    }
                }
            }

            $this->params['pages'][] = [
                'landing_page_id' => $result['landing_page_id'],
                'name' => $result['name'],
                'product_count' => $result['product_count'],
                'seo_urls' => $keywords,
                'edit' => $this->url->link(
                    'extension/module/' . $this->moduleName . '/create',
                    $this->tokenData['param'] . '&landing_page_id=' . $result['landing_page_id'],
                    true
                )
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
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'] . $url . '&page={page}',
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

        $this->params['sort'] = $sort;
        $this->params['order'] = $order;
        $this->params['limit'] = $limit;

        $this->params['add'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/create',
            $this->tokenData['param'],
            true
        );
        $this->params['delete'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/delete',
            $this->tokenData['param'],
            true
        );

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/list', $this->params));
    }

    public function create($postData = [])
    {
        $this->document->setTitle($this->language->get('heading_main_title'));

        $this->load->model('extension/module/' . $this->moduleName);
        $this->load->model('catalog/product');
        $this->load->model('localisation/language');

        if ($this->config->get('config_editor_default')) {
            $this->document->addScript('view/javascript/ckeditor/ckeditor.js');
            $this->document->addScript('view/javascript/ckeditor/ckeditor_init.js');
        } else {
            $this->document->addScript('view/javascript/summernote/summernote.js');
            $this->document->addScript('view/javascript/summernote/lang/summernote-' . $this->language->get('lang') . '.js');
            $this->document->addScript('view/javascript/summernote/opencart.js');
            $this->document->addStyle('view/javascript/summernote/summernote.css');
        }
        $this->document->addScript('view/javascript/Sortable.min.js');

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $this->params['token_param'] = $this->tokenData['param'];

        $this->params['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true)
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true)
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true)
            ]
        ];

        $landingPageId = (int) ($this->request->get['landing_page_id'] ?? 0);

        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('setting/store');

        $this->params['store_list'][] = [
            'store_id' => 0,
            'name' => $this->language->get('text_default')
        ];

        $stores = $this->model_setting_store->getStores();

        foreach ($stores as $store) {
            $this->params['store_list'][] = [
                'store_id' => $store['store_id'],
                'name' => $store['name']
            ];
        }

        if ($landingPageId && !$postData) {
            $page = $this->model_extension_module_aw_landing_page->getPage($landingPageId);

            $this->params['status'] = $page['status'];
            $this->params['description'] = $this->model_extension_module_aw_landing_page->getPageDescriptions($landingPageId);
            $this->params['products'] = $this->model_extension_module_aw_landing_page->getPageProducts($landingPageId);
            $this->params['seo_url'] = $this->model_extension_module_aw_landing_page->getSeoUrls($landingPageId, $this->awCore->isLegacy());
            $this->params['stores'] = $this->model_extension_module_aw_landing_page->getStores($landingPageId);
        } else {
            $this->params['status'] = $postData['status'] ?? false;
            $this->params['description'] = $postData['description'] ?? [];
            $this->params['products'] = $postData['products'] ?? [];
            $this->params['seo_url'] = $postData['seo_url'] ?? [];
            $this->params['stores'] = $postData['stores'] ?? [];
        }

        $actionUrl = 'extension/module/' . $this->moduleName . '/store';

        if ($landingPageId) {
            $actionUrl .= '&landing_page_id=' . $landingPageId;
        }

        $this->params['action'] = $this->url->link($actionUrl, $this->tokenData['param'], true);
        $this->params['cancel'] = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'],
            true
        );

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/form', $this->params));
    }

    public function store()
    {
        $this->load->language('extension/module/' . $this->moduleName);
        $this->load->model('extension/module/' . $this->moduleName);

        $landingPageId = (int) ($this->request->get['landing_page_id'] ?? 0);

        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
            if ($landingPageId) {
                $this->model_extension_module_aw_landing_page->editPage($landingPageId, $this->request->post);
                $this->session->data['success'] = $this->language->get('text_success_edit');
            } else {
                $landingPageId = $this->model_extension_module_aw_landing_page->addPage($this->request->post);
                $this->session->data['success'] = $this->language->get('text_success_add');
            }

            $this->model_extension_module_aw_landing_page->setSeoUrls(
                $landingPageId,
                $this->request->post['seo_url'],
                $this->awCore->isLegacy()
            );

            $this->response->redirect($this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true));
        }

        $this->create($this->request->post);
    }

    public function delete()
    {
        $this->load->language('extension/module/' . $this->moduleName);
        $this->load->model('extension/module/' . $this->moduleName);

        if (isset($this->request->post['selected']) && $this->validate()) {
            foreach ($this->request->post['selected'] as $landingPageId) {
                $this->model_extension_module_aw_landing_page->deletePage((int) $landingPageId, $this->awCore->isLegacy());
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

    public function autocomplete()
    {
        $json = [];

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('extension/module/' . $this->moduleName);

            $filterName = $this->request->get['filter_name'] ?? '';

            $limit = $this->config->get('config_limit_admin');

            $filterData = [
                'filter_name' => $filterName,
                'start' => 0,
                'limit' => $limit
            ];

            $results = $this->model_extension_module_aw_landing_page->getPages($filterData);

            foreach ($results as $result) {
                $json[] = [
                    'landing_page_id' => $result['landing_page_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validate()
    {
        $landingPageId = (int) ($this->request->get['landing_page_id'] ?? 0);

        if (!$this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('extension/module/' . $this->moduleName);

        if (isset($this->request->post['description'])) {
            foreach ($this->request->post['description'] as $languageId => $value) {
                if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
                    $this->error['name'][$languageId] = $this->language->get('error_name');
                }
            }
        }
        if (isset($this->request->post['seo_url'])) {
            foreach ($this->request->post['seo_url'] as $storeId => $languages) {
                foreach ($languages as $languageId => $seo_url) {
                    if (!empty($seo_url)) {
                        $seoUrlExists = $this->model_extension_module_aw_landing_page->seoUrlExists(
                            $seo_url,
                            $storeId,
                            $languageId,
                            $landingPageId,
                            $this->awCore->isLegacy()
                        );

                        if ($seoUrlExists) {
                            $this->error['seo_url'][$storeId][$languageId] = $this->language->get('error_seo_url_exists');
                        }
                    }

                    if ((utf8_strlen($seo_url) < 1) || (utf8_strlen($seo_url) > 255)) {
                        $this->error['seo_url'][$storeId][$languageId] = $this->language->get('error_seo_url');
                    }
                }
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    public function install()
    {
        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_landing_page->install();

        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting(
            'module_' . $this->moduleName,
            ['module_' . $this->moduleName . '_status' => '1']
        );
        $this->installPermissions();
    }

    public function uninstall()
    {
        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_landing_page->uninstall();

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);
    }

    protected function installPermissions()
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
