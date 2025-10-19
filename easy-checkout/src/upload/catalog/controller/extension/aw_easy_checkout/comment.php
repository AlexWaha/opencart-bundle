<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutComment extends Controller
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

        $blockStatus = $this->moduleConfig->get('block_status', []);
        $config = $this->moduleConfig->get('comment', []);
        $langId = $this->config->get('config_language_id');

        $data['lang_id'] = $langId;
        $data['status'] = $blockStatus['comment'] ?? false;
        $data['text_comments'] = $config['name'][$langId] ?? $this->language->get('text_comments');
        $data['text_placeholder_comments'] = $config['placeholder'][$langId] ?? $this->language->get('text_comments');
        $data['comment'] = $this->session->data['comment'] ?? '';

        return $this->awCore->render('extension/' . $this->moduleName . '/comment', $data);
    }
}
