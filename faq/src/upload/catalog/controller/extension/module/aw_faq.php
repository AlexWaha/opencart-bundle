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

        $languageId = (string) $this->config->get('config_language_id');

        // SEO from config
        $seo = $this->moduleConfig->get('seo', []);

        $metaTitle = $seo['meta_title'][$languageId] ?? $params['heading_title'];
        $metaDescription = $seo['meta_description'][$languageId] ?? '';
        $metaH1 = $seo['meta_h1'][$languageId] ?? $params['heading_title'];

        $this->document->setTitle($metaTitle);

        if ($metaDescription) {
            $this->document->setDescription($metaDescription);
        }

        $params['heading_title'] = $metaH1;

        $this->load->model('extension/module/' . $this->moduleName);

        $faqs = $this->model_extension_module_aw_faq->getFaqs();

        $params['faqs'] = [];

        foreach ($faqs as $faq) {
            $params['faqs'][] = [
                'faq_id'   => $faq['faq_id'],
                'question' => htmlspecialchars($faq['question'], ENT_QUOTES, 'UTF-8'),
                'answer'   => html_entity_decode($faq['answer'], ENT_QUOTES, 'UTF-8'),
            ];
        }

        $params['breadcrumbs'] = [
            [
                'text' => $language->get('text_home'),
                'href' => $this->url->link('common/home'),
            ],
            [
                'text' => $metaH1,
                'href' => $this->url->link('extension/module/' . $this->moduleName),
            ],
        ];

        $params['aw_microdata'] = $this->load->controller('extension/aw_microdata/microdata/getFaq', $params);

        $params['header'] = $this->load->controller('common/header');
        $params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName, $params));
    }
}
