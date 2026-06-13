<?php

/**
 * GDPR Consent Module (Cookie Consent + Google Consent Mode v2)
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @email support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwGdprConsent extends Controller
{
    private string $moduleName = 'aw_gdpr_consent';

    private ?\Alexwaha\Config $moduleConfig = null;

    private ?\Alexwaha\Language $language = null;

    private array $params = [];

    public function __construct($registry)
    {
        parent::__construct($registry);

        if ($this->registry->has('awCore')) {
            $this->language = $this->awCore->getLanguage();
            $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
            $this->params = $this->language->load('extension/module/' . $this->moduleName);
        }
    }

    private function isEnabled(): bool
    {
        return $this->moduleConfig !== null && $this->moduleConfig->get('status', false);
    }

    /**
     * Event: catalog/view/common/header/after
     * Injects assets + Google Consent Mode v2 default state at the top of <head>,
     * before any analytics tag runs.
     */
    public function head(&$route, &$data, &$output): void
    {
        if (!$this->isEnabled() || strpos($output, '<head>') === false) {
            return;
        }

        $server = (!empty($this->request->server['HTTPS'])) ? HTTPS_SERVER : HTTP_SERVER;
        $asset = $server . 'catalog/view/javascript/' . $this->moduleName;
        $assetFile = DIR_APPLICATION . 'view/javascript/' . $this->moduleName;
        $cssVersion = (int) @filemtime($assetFile . '.css');
        $jsVersion = (int) @filemtime($assetFile . '.js');

        $this->params['signals'] = json_encode($this->getSignalsMap());
        $this->params['cookie_days'] = (int) $this->moduleConfig->get('cookie_days', 365);

        $head = $this->awCore->render('extension/module/' . $this->moduleName . '_head', $this->params)
            . '<link rel="stylesheet" href="' . $asset . '.css?v=' . $cssVersion . '">'
            . '<script src="' . $asset . '.js?v=' . $jsVersion . '"></script>';

        $output = str_replace('<head>', '<head>' . $head, $output);
    }

    /**
     * Event: catalog/view/common/footer/after
     * Injects the consent bar + reopen widget before </body>.
     */
    public function footer(&$route, &$data, &$output): void
    {
        if (!$this->isEnabled() || strpos($output, '</body>') === false) {
            return;
        }

        $output = str_replace('</body>', $this->renderBar() . '</body>', $output);
    }

    private function renderBar(): string
    {
        $languageId = (int) $this->config->get('config_language_id');

        $title = $this->moduleConfig->get('title', []);
        $message = $this->moduleConfig->get('message', []);

        $this->params['title'] = !empty($title[$languageId])
            ? $title[$languageId]
            : $this->language->get('text_default_title');

        $this->params['message'] = !empty($message[$languageId])
            ? html_entity_decode($message[$languageId], ENT_QUOTES, 'UTF-8')
            : $this->language->get('text_default_message');

        $this->params['theme'] = $this->moduleConfig->get('theme', 'light');
        $this->params['accent_color'] = $this->moduleConfig->get('accent_color', '#0937cc');

        $policyPage = (int) $this->moduleConfig->get('policy_page', 0);
        $this->params['policy_link'] = $policyPage
            ? $this->url->link('information/information', 'information_id=' . $policyPage)
            : '';

        $this->params['categories'] = $this->getCategories();

        return $this->awCore->render('extension/module/' . $this->moduleName, $this->params);
    }

    /**
     * Category -> Google Consent Mode v2 signals mapping.
     */
    private function getSignalsMap(): array
    {
        return [
            'necessary' => ['security_storage', 'functionality_storage'],
            'preferences' => ['personalization_storage'],
            'statistics' => ['analytics_storage'],
            'marketing' => ['ad_storage', 'ad_user_data', 'ad_personalization'],
        ];
    }

    private function getCategories(): array
    {
        return [
            [
                'key' => 'necessary',
                'name' => $this->language->get('text_cat_necessary'),
                'description' => $this->language->get('text_cat_necessary_desc'),
                'locked' => true,
            ],
            [
                'key' => 'preferences',
                'name' => $this->language->get('text_cat_preferences'),
                'description' => $this->language->get('text_cat_preferences_desc'),
                'locked' => false,
            ],
            [
                'key' => 'statistics',
                'name' => $this->language->get('text_cat_statistics'),
                'description' => $this->language->get('text_cat_statistics_desc'),
                'locked' => false,
            ],
            [
                'key' => 'marketing',
                'name' => $this->language->get('text_cat_marketing'),
                'description' => $this->language->get('text_cat_marketing_desc'),
                'locked' => false,
            ],
        ];
    }
}
