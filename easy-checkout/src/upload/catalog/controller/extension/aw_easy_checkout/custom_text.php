<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutCustomText extends Controller
{
    private string $moduleName = 'aw_easy_checkout';

    private \Alexwaha\Config $moduleConfig;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
    }

    public function index()
    {
        $this->load->language('extension/' . $this->moduleName . '/lang');

        $langId = $this->config->get('config_language_id');

        $customTextConfig = $this->moduleConfig->get('custom_text', []);

        $customText = $customTextConfig[$langId] ?? '';

        $decodedText = html_entity_decode($customText, ENT_QUOTES, 'UTF-8');

        $data['custom_text'] = ! empty(strip_tags($decodedText)) ? $decodedText : '';

        return $this->awCore->render('extension/' . $this->moduleName . '/custom_text', $data);
    }

}
