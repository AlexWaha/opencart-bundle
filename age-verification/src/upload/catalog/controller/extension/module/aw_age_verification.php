<?php

/**
 * Age Verification Module
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @email support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwAgeVerification extends Controller
{
    private $moduleName = 'aw_age_verification';

    private $moduleConfig;

    private $params;

    private $language;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language = $this->awCore->getLanguage();
        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
        $this->params = $this->language->load('extension/module/' . $this->moduleName);
    }

    public function index()
    {
        $this->document->addStyle('catalog/view/javascript/' . $this->moduleName . '.min.css');
        $this->load->language('extension/module/' . $this->moduleName);

        $languageId = $this->config->get('config_language_id');

        $configTitle = $this->moduleConfig->get('title');
        $configDescription = $this->moduleConfig->get('description');

        $this->params['title'] = isset($configTitle[$languageId]) && !empty($configTitle[$languageId]) ? $configTitle[$languageId] : $this->language->get('text_age_title');

        $this->params['message'] = (isset($configDescription[$languageId]) && $configDescription[$languageId]) ? html_entity_decode($configDescription[$languageId], ENT_QUOTES, 'UTF-8') : '';

        $this->params['btn_confirm'] = $this->language->get('button_age_confirm');
        $this->params['btn_decline'] = $this->language->get('button_age_decline');

        $this->params['status'] = $this->moduleConfig->get('status') ?? false;

        if (isset($this->request->cookie[$this->moduleName]) && $this->request->cookie[$this->moduleName] == '1') {
            $this->params['verified'] = true;
        } else {
            $this->params['verified'] = false;
        }

        $this->params['redirect_url'] = $this->moduleConfig->get('redirect_url') ?? 'https://google.com';
        $this->params['cookie_days'] = $this->moduleConfig->get('cookie_days') ?? 30;

        $this->params['action_confirm'] = $this->url->link('extension/module/' . $this->moduleName . '/confirm');
        $this->params['action_decline'] = $this->url->link('extension/module/' . $this->moduleName . '/decline');

        return $this->awCore->render('extension/module/' . $this->moduleName, $this->params);
    }

    public function confirm()
    {
        $json = [];

        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
        $cookieDays = $this->moduleConfig->get('cookie_days') ?? 30;

        setcookie(
            $this->moduleName,
            '1',
            time() + ($cookieDays * 24 * 60 * 60),
            '/',
            $this->request->server['HTTP_HOST']
        );

        $json['success'] = true;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function decline()
    {
        $json = [];

        $json['redirect'] = $this->moduleConfig->get('redirect_url') ?? 'https://google.com';

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
