<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwPositionAwContentBottom extends Controller
{
    private $layoutRoute = 'aw_global_layout';

    private $moduleName = 'aw_content_bottom';

    private $params;

    public function index()
    {
        $this->load->model('extension/module/' . $this->layoutRoute);

        $moduleModelRoute = $this->awCore->isLegacy() ? 'extension/module' : 'setting/module';

        $this->load->model($moduleModelRoute);

        $modelModule = $this->awCore->isLegacy() ? $this->model_extension_module : $this->model_setting_module;

        $this->params['modules'] = [];

        $modules = $this->model_extension_module_aw_global_layout->getModules($this->moduleName);

        foreach ($modules as $module) {
            $part = explode('.', $module['code']);

            if (isset($part[0])) {
                if ($this->config->get('module_' . $part[0] . '_status') || $this->config->get($part[0] . '_status')) {
                    $moduleData = $this->load->controller('extension/module/' . $part[0]);

                    if ($moduleData) {
                        $this->params['modules'][] = $moduleData;
                    }
                }
            }

            if (isset($part[1])) {
                $settingInfo = $modelModule->getModule($part[1]);

                if ($settingInfo && $settingInfo['status']) {
                    $output = $this->load->controller('extension/module/' . $part[0], $settingInfo);

                    if ($output) {
                        $this->params['modules'][] = $output;
                    }
                }
            }
        }

        return $this->awCore->render('extension/aw_position/' . $this->moduleName, $this->params);
    }
}
