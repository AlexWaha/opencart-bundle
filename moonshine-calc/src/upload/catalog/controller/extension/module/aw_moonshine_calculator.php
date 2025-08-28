<?php
/**
 * Moonshine Calculator Module - Catalog Controller
 *
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @email support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwMoonshineCalculator extends Controller {

    private $moduleName = 'aw_moonshine_calculator';
    private $params;
    private $language;
    private $moduleConfig;
    private $error = [];

    /**
     * @param $registry
     */
    public function __construct($registry) {
        parent::__construct($registry);

        $this->language = $this->awCore->getLanguage();
        $this->params = $this->language->load('extension/module/' . $this->moduleName);
        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
    }

    /**
     * @return void
     */
    public function index() {
        $this->load->model('extension/module/' . $this->moduleName);

        $languageId = $this->config->get('config_language_id');

        $title = $this->moduleConfig->get('title');
        $h1 = $this->moduleConfig->get('h1');
        $description = $this->moduleConfig->get('description');
        $instructions = $this->moduleConfig->get('instructions');

        $this->document->addStyle('catalog/view/javascript/aw_moonshine_calculator.min.css');


        $this->params['title'] = (isset($title[$languageId]) && $title[$languageId]) ? $title[$languageId] : $this->params['text_calculator_title'];
        $this->params['h1'] = (isset($h1[$languageId]) && $h1[$languageId]) ? $h1[$languageId] : $this->params['text_calculator_title'];
        $this->params['description'] = (isset($description[$languageId]) && $description[$languageId]) ? html_entity_decode($description[$languageId], ENT_QUOTES, 'UTF-8') : '';
        $this->params['instructions'] = (isset($instructions[$languageId]) && $instructions[$languageId]) ? html_entity_decode($instructions[$languageId], ENT_QUOTES, 'UTF-8') : '';

        $this->params['home'] = $this->url->link('common/home');

        $this->params['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home'),
            ],
            [
                'text' => $this->params['h1'],
                'href' => $this->url->link('extension/module/' . $this->moduleName),
            ],
        ];

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateCalculation()) {
            $initial_strength = (float)$this->request->post['initial_strength'];
            $final_strength = (float)$this->request->post['final_strength'];
            $moonshine_volume = (float)$this->request->post['moonshine_volume'];

            $calculation = $this->model_extension_module_aw_moonshine_calculator->calculate($initial_strength, $final_strength, $moonshine_volume);

            $this->params['calculation_result'] = $calculation;
            $this->params['show_result'] = true;
        } else {
            $this->params['show_result'] = false;
        }

        $this->document->setTitle($this->params['title']);

        if ($this->params['description']) {
            $this->document->setDescription(strip_tags($this->params['description']));
        }

        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['column_right'] = $this->load->controller('common/column_right');
        $this->params['content_top'] = $this->load->controller('common/content_top');
        $this->params['content_bottom'] = $this->load->controller('common/content_bottom');
        $this->params['footer'] = $this->load->controller('common/footer');
        $this->params['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->awCore->render('extension/module/aw_moonshine_calculator', $this->params));
    }

    /**
     * @return void
     */
    public function calculate() {
        $this->load->model('extension/module/' . $this->moduleName);

        $json = [];

        if ($this->validateCalculation()) {
            $initial_strength = (float)$this->request->post['initial_strength'];
            $final_strength = (float)$this->request->post['final_strength'];
            $moonshine_volume = (float)$this->request->post['moonshine_volume'];

            $result = $this->model_extension_module_aw_moonshine_calculator->calculate($initial_strength, $final_strength, $moonshine_volume);

            $json['success'] = true;
            $json['result'] = $result;
        } else {
            $json['success'] = false;
            $json['errors'] = $this->error;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * @return bool
     */
    private function validateCalculation(): bool
    {
        if (!isset($this->request->post['initial_strength']) || $this->request->post['initial_strength'] == '') {
            $this->error['initial_strength'] = $this->params['error_initial_strength'];
        } elseif ($this->request->post['initial_strength'] < 0 || $this->request->post['initial_strength'] > 100) {
            $this->error['initial_strength'] = $this->params['error_initial_strength_range'];
        }

        if (!isset($this->request->post['final_strength']) || $this->request->post['final_strength'] == '') {
            $this->error['final_strength'] = $this->params['error_final_strength'];
        } elseif ($this->request->post['final_strength'] < 0 || $this->request->post['final_strength'] > 100) {
            $this->error['final_strength'] = $this->params['error_final_strength_range'];
        }

        if (!isset($this->request->post['moonshine_volume']) || $this->request->post['moonshine_volume'] == '') {
            $this->error['moonshine_volume'] = $this->params['error_moonshine_volume'];
        } elseif ($this->request->post['moonshine_volume'] <= 0) {
            $this->error['moonshine_volume'] = $this->params['error_moonshine_volume_positive'];
        }

        if (isset($this->request->post['initial_strength']) && isset($this->request->post['final_strength'])) {
            if ($this->request->post['final_strength'] >= $this->request->post['initial_strength']) {
                $this->error['final_strength'] = $this->params['error_final_strength_less'];
            }
        }

        return !$this->error;
    }
}
