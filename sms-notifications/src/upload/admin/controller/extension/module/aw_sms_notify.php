<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 *
 * @link    https://alexwaha.com
 *
 * @email   support@alexwaha.com
 *
 * @license GPLv3
 */

use Alexwaha\SmsDispatcher;

class ControllerExtensionModuleAwSmsNotify extends Controller
{
    private string $moduleName = 'aw_sms_notify';
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
        $this->routeExtension = $this->awCore->isLegacy()
            ? 'extension/extension'
            : 'marketplace/extension';
    }

    public function index()
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

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

        $this->params['action'] = $this->url->link('extension/module/' . $this->moduleName . '/store', $this->tokenData['param'], true);

        $this->params['cancel'] = $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true);

        $this->params['sendMessage'] = $this->url->link('extension/module/' . $this->moduleName . '/sendMessage', $this->tokenData['param'], true);

        $this->params['clearLog'] = $this->url->link('extension/module/' . $this->moduleName . '/clearLog', $this->tokenData['param'], true);

        $this->params['sms_gatenames'] = [];

        $files = glob(DIR_SYSTEM . 'library/Alexwaha/Gateway/*.php');

        foreach ($files as $file) {
            $this->params['sms_gatenames'][] = basename($file, '.php');
        }

        $this->params['sms_notify_gatename'] = $this->moduleConfig->get('sms_notify_gatename', '');
        $this->params['sms_notify_to'] = $this->moduleConfig->get('sms_notify_to', '');
        $this->params['sms_notify_from'] = $this->moduleConfig->get('sms_notify_from', '');
        $this->params['sms_notify_gate_username'] = $this->moduleConfig->get('sms_notify_gate_username', '');
        $this->params['sms_notify_gate_password'] = $this->moduleConfig->get('sms_notify_gate_password', '');
        $this->params['sms_notify_copy'] = $this->moduleConfig->get('sms_notify_copy', '');
        $this->params['admin_alert'] = $this->moduleConfig->get('sms_notify_admin_alert', false);
        $this->params['client_alert'] = $this->moduleConfig->get('sms_notify_client_alert', false);
        $this->params['register_alert'] = $this->moduleConfig->get('sms_notify_register_alert', false);
        $this->params['order_alert'] = $this->moduleConfig->get('sms_notify_order_alert', false);
        $this->params['reviews'] = $this->moduleConfig->get('sms_notify_reviews', false);
        $this->params['translit'] = $this->moduleConfig->get('sms_notify_translit', false);
        $this->params['force'] = $this->moduleConfig->get('sms_notify_force', false);
        $this->params['admin_template'] = $this->moduleConfig->get('sms_notify_admin_template', '');
        $this->params['client_template'] = $this->moduleConfig->get('sms_notify_client_template', []);
        $this->params['register_template'] = $this->moduleConfig->get('sms_notify_register_template', []);
        $this->params['reviews_template'] = $this->moduleConfig->get('sms_notify_reviews_template', '');
        $this->params['sms_payment'] = $this->moduleConfig->get('sms_notify_payment', []);
        $this->params['payment_template'] = $this->moduleConfig->get('sms_notify_payment_template', []);
        $this->params['sms_customer_group'] = $this->moduleConfig->get('sms_notify_customer_group', []);
        $this->params['viber_alert'] = $this->moduleConfig->get('sms_notify_viber_alert', false);
        $this->params['viber_sender'] = $this->moduleConfig->get('sms_notify_viber_sender', '');
        $this->params['viber_ttl'] = $this->moduleConfig->get('sms_notify_viber_ttl', '3600');
        $this->params['viber_caption'] = $this->moduleConfig->get('sms_notify_viber_caption', '');
        $this->params['viber_url'] = $this->moduleConfig->get('sms_notify_viber_url', '');
        $this->params['width'] = $this->moduleConfig->get('sms_notify_viber_image_width', '400');
        $this->params['height'] = $this->moduleConfig->get('sms_notify_viber_image_height', '400');
        $this->params['client_viber_template'] = $this->moduleConfig->get('sms_notify_client_viber_template', []);
        $this->params['payment_viber_template'] = $this->moduleConfig->get('sms_notify_payment_viber_template', []);
        $this->params['order_viber_template'] = $this->moduleConfig->get('sms_notify_viber_template', []);
        $this->params['sms_template'] = html_entity_decode(
            $this->moduleConfig->get('sms_notify_sms_template', ''),
            ENT_QUOTES,
            'UTF-8'
        );
        $this->params['custom_client_sms_template'] = $this->moduleConfig->get('sms_notify_custom_client_sms_template', '');
        $this->params['order_status_template'] = $this->moduleConfig->get('sms_notify_status_template', []);
        $this->params['sms_order_status'] = $this->moduleConfig->get('sms_notify_order_status', []);
        $this->params['sms_notify_log'] = $this->moduleConfig->get('sms_notify_log', true);
        $this->params['sms_notify_log_filename'] = $this->moduleConfig->get('sms_notify_log_filename', $this->moduleName);

        $this->params['payments'] = [];

        $this->params['payments'] = $this->model_extension_module_aw_sms_notify->getPaymentList();

        $this->load->model('customer/customer_group');

        $this->params['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        $this->load->model('tool/image');

        $this->params['viber_image'] = '';
        $this->params['viber_thumb'] = '';

        $this->params['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        $image = $this->moduleConfig->get('sms_notify_viber_image', '');

        if ($image && is_file(DIR_IMAGE . $image)) {
            $this->params['viber_thumb'] = $this->model_tool_image->resize($image, 100, 100);
            $this->params['viber_image'] = $image;
        }

        $this->load->model('localisation/language');

        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        if ($this->config->get('config_editor_default')) {
            $this->document->addScript('view/javascript/ckeditor/ckeditor.js');
            $this->document->addScript('view/javascript/ckeditor/ckeditor_init.js');
        }

        $this->params['ckeditor'] = $this->config->get('config_editor_default');

        $this->params['lang'] = $this->language->get('lang');

        $this->params['order_statuses'] = $this->model_extension_module_aw_sms_notify->getOrderStatuses();

        $logFileName = $this->params['sms_notify_log_filename'] . '.log';

        $this->params['sms_log'] = '';

        if ($this->params['sms_notify_log']) {
            $logFilePath = DIR_LOGS . $logFileName;

            if (file_exists($logFilePath)) {
                $size = filesize($logFilePath);

                if ($size >= 5242880) {
                    $suffix = [
                        'B',
                        'KB',
                        'MB',
                        'GB',
                        'TB',
                        'PB',
                        'EB',
                        'ZB',
                        'YB',
                    ];

                    $i = 0;

                    while (($size / 1024) > 1) {
                        $size = $size / 1024;
                        $i++;
                    }

                    $this->params['error_warning'] = sprintf(
                        $this->language->get('error_log_size'),
                        basename($logFilePath),
                        round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i]
                    );
                } else {
                    $this->params['sms_log'] = file_get_contents($logFilePath, FILE_USE_INCLUDE_PATH, null);
                }
            }
        } else {
            $this->clearLog();
        }

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName, $this->params));
    }

    public function store()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->awCore->setConfig($this->moduleName, $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                'extension/module/' . $this->moduleName,
                $this->tokenData['param'] . '&type=module',
                true
            ));
        }

        $this->index();
    }

    public function order()
    {
        $this->load->model('localisation/order_status');

        $this->params['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        return $this->awCore->render('extension/module/' . $this->moduleName . '_list', $this->params);
    }

    public function orderInfoForm()
    {
        $order_id = $this->request->get['order_id'] ?? 0;

        $this->params['sendMessage'] = $this->url->link('extension/module/' . $this->moduleName . '/sendMessage', $this->tokenData['param'] . '&order_id=' . $order_id, true);

        $this->params['sms_template'] = html_entity_decode(
            $this->moduleConfig->get('sms_notify_sms_template', ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $this->params['force'] = $this->moduleConfig->get('sms_notify_force', false);

        return $this->awCore->render('extension/module/' . $this->moduleName . '_info', $this->params);
    }

    /**
     * @return void
     */
    public function sendMessage()
    {
        $json = [];

        $this->load->model('sale/order');
        $this->load->model('extension/module/' . $this->moduleName);

        if (isset($this->request->get['order_id'])) {
            $order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
        } else {
            $order_info = [];
        }

        if ((utf8_strlen($this->request->post['sms_message']) < 3)) {
            $json['error'] = $this->language->get('error_sms');
        }

        $phone = false;

        if ($this->moduleConfig->get('sms_notify_gatename') && $this->moduleConfig->get('sms_notify_gate_username')) {
            if ($order_info) {
                $phone = $this->awCore->prepareNumber($order_info['telephone']);
            } elseif ($this->request->post['phone']) {
                $phone = $this->awCore->prepareNumber($this->request->post['phone']);
            } else {
                $json['error'] = $this->language->get('error_sms');
            }
        } else {
            $json['error'] = $this->language->get('error_sms_setting');
        }

        if (! isset($json['error'])) {
            $this->load->model('tool/image');

            $viber_image_src = $this->moduleConfig->get('sms_notify_viber_image');
            $viber_image_width = $this->moduleConfig->get('sms_notify_viber_image_width');
            $viber_image_height = $this->moduleConfig->get('sms_notify_viber_image_height');

            if (is_file(DIR_IMAGE . $viber_image_src)) {
                $viber_image = $this->model_tool_image->resize(
                    $viber_image_src,
                    $viber_image_width,
                    $viber_image_height
                );
            } else {
                $viber_image = false;
            }

            $viber_options = [
                'status' => $this->moduleConfig->get('sms_notify_viber_alert'),
                'sender' => $this->moduleConfig->get('sms_notify_viber_sender'),
                'message' => $this->request->post['sms_message'],
                'ttl' => $this->moduleConfig->get('sms_notify_viber_ttl'),
                'image_url' => $viber_image ?: false,
                'caption' => $this->moduleConfig->get('sms_notify_viber_caption'),
                'action' => $this->moduleConfig->get('sms_notify_viber_url'),
            ];

            $options = [
                'to' => $phone,
                'from' => $this->moduleConfig->get('sms_notify_from'),
                'username' => $this->moduleConfig->get('sms_notify_gate_username'),
                'password' => $this->moduleConfig->get('sms_notify_gate_password'),
                'message' => $this->request->post['sms_message'],
                'viber' => $viber_options,
            ];

            $dispatcher = new SmsDispatcher(
                $this->moduleConfig->get('sms_notify_gatename'),
                $options,
                $this->moduleConfig->get('sms_notify_log_filename')
            );
            $dispatcher->send();

            if ($order_info) {
                $this->model_extension_module_aw_sms_notify->addOrderHistory(
                    $order_info['order_id'],
                    $order_info['order_status_id'],
                    $options['message']
                );
            }

            $json['success'] = $this->language->get('text_success_sms');
        }

        $this->response->setOutput(json_encode($json));
    }

    public function clearLog()
    {
        $json = [];

        $logFileName = $this->moduleConfig->get('sms_notify_log_filename');

        $logFile = DIR_LOGS . $logFileName . '.log';

        if (! file_exists($logFile)) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (! $this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            file_put_contents($logFile, '');
            $json['success'] = $this->language->get('text_success_log');
        }

        $this->response->setOutput(json_encode($json));
    }

    protected function validate(): bool
    {
        if (! $this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['sms_notify_log_filename']) && !$this->request->post['sms_notify_log_filename']) {
            $this->error['log_filename'] = $this->language->get('error_log_filename');
        }

        if (isset($this->request->post['sms_notify_gatename']) && !$this->request->post['sms_notify_gatename']) {
            $this->error['gatename'] = $this->language->get('error_gatename');
        }

        if (isset($this->request->post['sms_notify_from']) && !$this->request->post['sms_notify_from']) {
            $this->error['from'] = $this->language->get('error_from');
        }

        if (isset($this->request->post['sms_notify_gate_username']) && !$this->request->post['sms_notify_gate_username']) {
            $this->error['username'] = $this->language->get('error_username');
        }

        if ((isset($this->request->post['sms_notify_admin_alert']) && $this->request->post['sms_notify_admin_alert'])
            && isset($this->request->post['sms_notify_admin_template'])) {
            if ((utf8_strlen(trim($this->request->post['sms_notify_admin_template'])) < 3)) {
                $this->error['admin_template'] = $this->language->get('error_admin_template');
            }
        }

        if ((isset($this->request->post['sms_notify_reviews']) && $this->request->post['sms_notify_reviews'])
            && isset($this->request->post['sms_notify_reviews_template'])) {
            if ((utf8_strlen(trim($this->request->post['sms_notify_reviews_template'])) < 3)) {
                $this->error['reviews_template'] = $this->language->get('error_reviews_template');
            }
        }

        if ((isset($this->request->post['sms_notify_client_alert']) && $this->request->post['sms_notify_client_alert'])) {
            if (isset($this->request->post['sms_notify_client_template'])) {
                foreach ($this->request->post['sms_notify_client_template'] as $languageId => $value) {
                    if ((utf8_strlen(trim($value)) < 3)) {
                        $this->error['client_template'][$languageId] = $this->language->get('error_client_template');
                    }
                }
            }
        }

        if ((isset($this->request->post['sms_notify_register_alert']) && $this->request->post['sms_notify_register_alert'])) {
            if (isset($this->request->post['sms_notify_register_template'])) {
                foreach ($this->request->post['sms_notify_register_template'] as $languageId => $value) {
                    if ((utf8_strlen(trim($value)) < 3)) {
                        $this->error['register_template'][$languageId] = $this->language->get('error_register_template');
                    }
                }
            }
        }

        if ((isset($this->request->post['sms_notify_viber_alert']) && $this->request->post['sms_notify_viber_alert'])
            && isset($this->request->post['sms_notify_viber_sender'])) {
            if ((utf8_strlen(trim($this->request->post['sms_notify_viber_sender'])) < 3)) {
                $this->error['viber_sender'] = $this->language->get('error_viber_sender');
            }
        }

        if ((isset($this->request->post['sms_notify_viber_alert']) && $this->request->post['sms_notify_viber_alert'])) {
            if (isset($this->request->post['sms_notify_client_viber_template'])) {
                foreach ($this->request->post['sms_notify_client_viber_template'] as $languageId => $value) {
                    if ((utf8_strlen(trim($value)) < 3)) {
                        $this->error['client_viber_template'][$languageId] = $this->language->get('error_client_viber_template');
                    }
                }
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    /**
     * @return void
     */
    public function install()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_' . $this->moduleName, ['module_' . $this->moduleName . '_status' => '1']);

        $this->awCore->removeLegacyFiles($this->getLegacyList());
        $this->uninstallLegacyEvents();
        $this->installEvents();
        $this->installPermissions();
    }

    /**
     * @return void
     */
    protected function installEvents()
    {
        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/event');
            $model = 'model_extension_event';
        } else {
            $this->load->model('setting/event');
            $model = 'model_setting_event';
        }

        $this->{$model}->addEvent(
            'aw_sms_notify_order_alert',
            'catalog/model/checkout/order/addOrderHistory/before',
            'extension/module/aw_sms_notify/order',
            1
        );

        $this->{$model}->addEvent(
            'aw_sms_notify_review_alert',
            'catalog/model/catalog/review/addReview/before',
            'extension/module/aw_sms_notify/review',
            1
        );

        $this->{$model}->addEvent(
            'aw_sms_notify_register_alert',
            'catalog/model/account/customer/addCustomer/after',
            'extension/module/aw_sms_notify/register',
            1
        );
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('sms_notify');
        $this->awCore->removeConfig($this->moduleName);

        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/event');

            $this->model_extension_event->deleteEvent('aw_sms_notify_order_alert');
            $this->model_extension_event->deleteEvent('aw_sms_notify_review_alert');
            $this->model_extension_event->deleteEvent('aw_sms_notify_register_alert');
        } else {
            $this->load->model('setting/event');

            $this->model_setting_event->deleteEventByCode('aw_sms_notify_order_alert');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_review_alert');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_register_alert');
        }
    }

    /**
     * @return void
     */
    public function uninstallLegacyEvents()
    {
        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/event');

            $this->model_extension_event->deleteEvent('ocd_sms_notify_order_alert');
            $this->model_extension_event->deleteEvent('ocd_sms_notify_review_alert');
            $this->model_extension_event->deleteEvent('ocd_sms_notify_register_alert');
            $this->model_extension_event->deleteEvent('ocd_sms_notify_orderproh');
            $this->model_extension_event->deleteEvent('ochelp_sms_notify_order_alert');
            $this->model_extension_event->deleteEvent('ochelp_sms_notify_review_alert');
        } else {
            $this->load->model('setting/event');

            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_order_alert');
            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_review_alert');
            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_register_alert');
            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_orderproh');
            $this->model_setting_event->deleteEventByCode('ochelp_sms_notify_order_alert');
            $this->model_setting_event->deleteEventByCode('ochelp_sms_notify_review_alert');
        }
    }

    public function exportConfig()
    {
        if (! $this->validate()) {
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
                'error' => sprintf($this->language->get('error_export_failed'), $e->getMessage()),
            ]));
        }
    }

    public function importConfig()
    {
        $json = [];

        if (! $this->validate()) {
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

    protected function getLegacyList(): array
    {
        return [
            DIR_APPLICATION . 'controller/extension/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'controller/extension/module/aw_sms_event.php',
            DIR_APPLICATION . 'controller/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'model/extension/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'model/extension/module/aw_sms_event.php',
            DIR_APPLICATION . 'model/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify_info.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify_list.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify.twig',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify_info.twig',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify_list.twig',
            DIR_APPLICATION . 'view/template/module/ocd_sms_notify.tpl',
            DIR_APPLICATION . 'view/template/module/ocd_sms_notify_info.tpl',
            DIR_APPLICATION . 'view/template/module/ocd_sms_notify_list.tpl',
            DIR_APPLICATION . 'language/en-gb/extension/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'language/english/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'language/ru-ru/extension/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'language/russian/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'language/en-gb/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'language/ru-ru/module/ocd_sms_notify.php',
            DIR_CATALOG . 'controller/extension/module/ocd_sms_notify.php',
            DIR_CATALOG . 'controller/module/ocd_sms_notify.php',
            DIR_CATALOG . 'model/extension/module/ocd_sms_notify.php',
            DIR_CATALOG . 'model/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'controller/extension/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'controller/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'model/extension/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'model/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify_info.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify_list.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify.twig',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify_info.twig',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify_list.twig',
            DIR_APPLICATION . 'view/template/module/ochelp_sms_notify.tpl',
            DIR_APPLICATION . 'view/template/module/ochelp_sms_notify_info.tpl',
            DIR_APPLICATION . 'view/template/module/ochelp_sms_notify_list.tpl',
            DIR_APPLICATION . 'language/en-gb/extension/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'language/english/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'language/ru-ru/extension/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'language/russian/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'language/en-gb/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'language/ru-ru/module/ochelp_sms_notify.php',
            DIR_CATALOG . 'controller/extension/module/ochelp_sms_notify.php',
            DIR_CATALOG . 'controller/module/ochelp_sms_notify.php',
            DIR_CATALOG . 'model/extension/module/ochelp_sms_notify.php',
            DIR_CATALOG . 'model/module/ochelp_sms_notify.php',
            DIR_SYSTEM . 'smsgate',
            DIR_SYSTEM . 'library/smsgate',
            DIR_SYSTEM . 'library/ocdsms.php',
            DIR_LOGS . 'sms_log.log',
        ];
    }
}
