<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwLandingLinks extends Controller
{
    private string $moduleName = 'aw_landing_links';
    private $params;
    private $language;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language = $this->awCore->getLanguage();
        $this->params = $this->language->load('extension/module/' . $this->moduleName);
    }

    public function index($setting)
    {
        $this->load->model('extension/module/' . $this->moduleName);
        $this->load->model('setting/setting');

        $languageId = $this->config->get('config_language_id');

        $this->params['status'] = $setting['status'] ?? false;
        $this->params['title'] = '';

        if (isset($setting['title'])) {
            $this->params['title'] = $setting['title'][$languageId] ?? '';
        }

        $pagesConfig = $setting['pages'] ?? [];

        $pages = [];

        $allPages = $this->model_extension_module_aw_landing_links->getPages($pagesConfig);

        foreach ($allPages as $page) {
            $pages[] = [
                'landing_page_id' => $page['landing_page_id'],
                'name'            => $page['name'],
            ];
        }

        $grouped = [];
        $columns = 4;
        $count = count($pages);
        $perColumn = $count > 0 ? ceil($count / $columns) : 1;

        $chunks = array_chunk($pages, $perColumn);

        foreach ($chunks as $chunk) {
            $column = [];

            foreach ($chunk as $item) {
                $column[] = [
                    'name' => $item['name'],
                    'href' => $this->url->link(
                        'extension/module/aw_landing_page',
                        'landing_page_id=' . (int)$item['landing_page_id']
                    ),
                ];
            }

            $grouped[] = $column;
        }

        $this->params['links'] = $grouped;

        return $this->awCore->render('extension/module/' . $this->moduleName, $this->params);
    }
}
