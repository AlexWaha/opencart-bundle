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
    private $language;

    private $params;

    private $error = [];

    private $tokenData;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language = $this->awCore->getLanguage();
        $this->tokenData = $this->awCore->getToken();
        $this->params = $this->language->load('extension/module/aw_sms_notify');
        $this->params['token'] = $this->tokenData['token'];
        $this->params['token_param'] = $this->tokenData['param'];
    }

    public function index()
    {
        $this->document->setTitle($this->language->get('heading_main_title'));

        $this->load->model('extension/module/aw_sms_notify');

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('sms_notify', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                'extension/module/aw_sms_notify',
                $this->tokenData['param'] . '&type=module',
                true
            ));
        }

        $this->params['action'] = $this->url->link('extension/module/aw_sms_notify', $this->tokenData['param'], true);

        $this->params['cancel'] = $this->url->link('marketplace/extension', $this->tokenData['param'] . '&type=module', true);

        $this->params['sendMessage'] = $this->url->link('extension/module/aw_sms_notify/sendMessage', $this->tokenData['param'], true);

        $this->params['clearLog'] = $this->url->link('extension/module/aw_sms_notify/clearLog', $this->tokenData['param'], true);

        if (isset($this->error['warning'])) {
            $this->params['error_warning'] = $this->error['warning'];
        } else {
            $this->params['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $this->params['error_name'] = $this->error['name'];
        } else {
            $this->params['error_name'] = '';
        }

        $this->params['breadcrumbs'] = [];

        $this->params['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true),
        ];

        $this->params['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', $this->tokenData['param'] . '&type=module', true),
        ];

        $this->params['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/aw_sms_notify', $this->tokenData['param'], true),
        ];

        $this->params['sms_gatenames'] = [];

        $files = glob(DIR_SYSTEM . 'library/Alexwaha/Gateway/*.php');

        foreach ($files as $file) {
            $this->params['sms_gatenames'][] = basename($file, '.php');
        }

        if (isset($this->request->post['sms_notify_gatename'])) {
            $this->params['sms_notify_gatename'] = $this->request->post['sms_notify_gatename'];
        } else {
            $this->params['sms_notify_gatename'] = $this->config->get('sms_notify_gatename');
        }

        if (isset($this->request->post['sms_notify_to'])) {
            $this->params['sms_notify_to'] = $this->request->post['sms_notify_to'];
        } else {
            $this->params['sms_notify_to'] = $this->config->get('sms_notify_to');
        }

        if (isset($this->request->post['sms_notify_from'])) {
            $this->params['sms_notify_from'] = $this->request->post['sms_notify_from'];
        } else {
            $this->params['sms_notify_from'] = $this->config->get('sms_notify_from');
        }

        if (isset($this->request->post['sms_notify_message'])) {
            $this->params['sms_notify_message'] = $this->request->post['sms_notify_message'];
        } else {
            $this->params['sms_notify_message'] = $this->config->get('sms_notify_message');
        }

        if (isset($this->request->post['sms_notify_gate_username'])) {
            $this->params['sms_notify_gate_username'] = $this->request->post['sms_notify_gate_username'];
        } else {
            $this->params['sms_notify_gate_username'] = $this->config->get('sms_notify_gate_username');
        }

        if (isset($this->request->post['sms_notify_gate_password'])) {
            $this->params['sms_notify_gate_password'] = $this->request->post['sms_notify_gate_password'];
        } else {
            $this->params['sms_notify_gate_password'] = $this->config->get('sms_notify_gate_password');
        }

        if (isset($this->request->post['sms_notify_alert'])) {
            $this->params['sms_notify_alert'] = $this->request->post['sms_notify_alert'];
        } else {
            $this->params['sms_notify_alert'] = $this->config->get('sms_notify_alert');
        }

        if (isset($this->request->post['sms_notify_copy'])) {
            $this->params['sms_notify_copy'] = $this->request->post['sms_notify_copy'];
        } else {
            $this->params['sms_notify_copy'] = $this->config->get('sms_notify_copy');
        }

        if (isset($this->request->post['sms_notify_admin_alert'])) {
            $this->params['admin_alert'] = $this->request->post['sms_notify_admin_alert'];
        } elseif ($this->config->get('sms_notify_admin_alert')) {
            $this->params['admin_alert'] = $this->config->get('sms_notify_admin_alert');
        } else {
            $this->params['admin_alert'] = '';
        }

        if (isset($this->request->post['sms_notify_client_alert'])) {
            $this->params['client_alert'] = $this->request->post['sms_notify_client_alert'];
        } elseif ($this->config->get('sms_notify_client_alert')) {
            $this->params['client_alert'] = $this->config->get('sms_notify_client_alert');
        } else {
            $this->params['client_alert'] = '';
        }

        if (isset($this->request->post['sms_notify_register_alert'])) {
            $this->params['register_alert'] = $this->request->post['sms_notify_register_alert'];
        } elseif ($this->config->get('sms_notify_register_alert')) {
            $this->params['register_alert'] = $this->config->get('sms_notify_register_alert');
        } else {
            $this->params['register_alert'] = '';
        }

        if (isset($this->request->post['sms_notify_order_alert'])) {
            $this->params['order_alert'] = $this->request->post['sms_notify_order_alert'];
        } elseif ($this->config->get('sms_notify_order_alert')) {
            $this->params['order_alert'] = $this->config->get('sms_notify_order_alert');
        } else {
            $this->params['order_alert'] = '';
        }

        if (isset($this->request->post['sms_notify_reviews'])) {
            $this->params['reviews'] = $this->request->post['sms_notify_reviews'];
        } elseif ($this->config->get('sms_notify_reviews')) {
            $this->params['reviews'] = $this->config->get('sms_notify_reviews');
        } else {
            $this->params['reviews'] = '';
        }

        if (isset($this->request->post['sms_notify_payment_alert'])) {
            $this->params['payment_alert'] = $this->request->post['sms_notify_payment_alert'];
        } elseif ($this->config->get('sms_notify_order_alert')) {
            $this->params['payment_alert'] = $this->config->get('sms_notify_payment_alert');
        } else {
            $this->params['payment_alert'] = '';
        }

        if (isset($this->request->post['sms_notify_translit'])) {
            $this->params['translit'] = $this->request->post['sms_notify_translit'];
        } elseif ($this->config->get('sms_notify_translit')) {
            $this->params['translit'] = $this->config->get('sms_notify_translit');
        } else {
            $this->params['translit'] = false;
        }

        if (isset($this->request->post['sms_notify_force'])) {
            $this->params['force'] = $this->request->post['sms_notify_force'];
        } elseif ($this->config->get('sms_notify_force')) {
            $this->params['force'] = $this->config->get('sms_notify_force');
        } else {
            $this->params['force'] = '';
        }

        if (isset($this->request->post['sms_notify_admin_template'])) {
            $this->params['admin_template'] = $this->request->post['sms_notify_admin_template'];
        } elseif ($this->config->get('sms_notify_admin_template')) {
            $this->params['admin_template'] = $this->config->get('sms_notify_admin_template');
        } else {
            $this->params['admin_template'] = '';
        }

        if (isset($this->request->post['sms_notify_client_template'])) {
            $this->params['client_template'] = $this->request->post['sms_notify_client_template'];
        } elseif ($this->config->get('sms_notify_client_template')) {
            $this->params['client_template'] = $this->config->get('sms_notify_client_template');
        } else {
            $this->params['client_template'] = '';
        }

        if (isset($this->request->post['sms_notify_register_template'])) {
            $this->params['register_template'] = $this->request->post['sms_notify_register_template'];
        } elseif ($this->config->get('sms_notify_register_template')) {
            $this->params['register_template'] = $this->config->get('sms_notify_register_template');
        } else {
            $this->params['register_template'] = [];
        }

        if (isset($this->request->post['sms_notify_reviews_template'])) {
            $this->params['reviews_template'] = $this->request->post['sms_notify_reviews_template'];
        } elseif ($this->config->get('sms_notify_reviews_template')) {
            $this->params['reviews_template'] = $this->config->get('sms_notify_reviews_template');
        } else {
            $this->params['reviews_template'] = '';
        }

        $this->params['payments'] = [];

        $this->params['payments'] = $this->model_extension_module_aw_sms_notify->getPaymentList();

        if (isset($this->request->post['sms_notify_payment'])) {
            $this->params['sms_payment'] = $this->request->post['sms_notify_payment'];
        } elseif ($this->config->get('sms_notify_payment')) {
            $this->params['sms_payment'] = $this->config->get('sms_notify_payment');
        } else {
            $this->params['sms_payment'] = [];
        }

        if (isset($this->request->post['sms_notify_payment_template'])) {
            $this->params['payment_template'] = $this->request->post['sms_notify_payment_template'];
        } elseif ($this->config->get('sms_notify_payment_template')) {
            $this->params['payment_template'] = $this->config->get('sms_notify_payment_template');
        } else {
            $this->params['payment_template'] = [];
        }

        $this->load->model('customer/customer_group');

        $this->params['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        if (isset($this->request->post['sms_notify_customer_group'])) {
            $this->params['sms_customer_group'] = $this->request->post['sms_notify_customer_group'];
        } elseif ($this->config->get('sms_notify_customer_group')) {
            $this->params['sms_customer_group'] = $this->config->get('sms_notify_customer_group');
        } else {
            $this->params['sms_customer_group'] = [];
        }

        if (isset($this->request->post['sms_notify_viber_alert'])) {
            $this->params['viber_alert'] = $this->request->post['sms_notify_viber_alert'];
        } elseif ($this->config->get('sms_notify_viber_alert')) {
            $this->params['viber_alert'] = $this->config->get('sms_notify_viber_alert');
        } else {
            $this->params['viber_alert'] = '';
        }

        if (isset($this->request->post['sms_notify_viber_sender'])) {
            $this->params['viber_sender'] = $this->request->post['sms_notify_viber_sender'];
        } elseif ($this->config->get('sms_notify_viber_sender')) {
            $this->params['viber_sender'] = $this->config->get('sms_notify_viber_sender');
        } else {
            $this->params['viber_sender'] = '';
        }

        if (isset($this->request->post['sms_notify_viber_ttl'])) {
            $this->params['viber_ttl'] = $this->request->post['sms_notify_viber_ttl'];
        } elseif ($this->config->get('sms_notify_viber_ttl')) {
            $this->params['viber_ttl'] = $this->config->get('sms_notify_viber_ttl');
        } else {
            $this->params['viber_ttl'] = '3600';
        }

        if (isset($this->request->post['sms_notify_viber_caption'])) {
            $this->params['viber_caption'] = $this->request->post['sms_notify_viber_caption'];
        } elseif ($this->config->get('sms_notify_viber_caption')) {
            $this->params['viber_caption'] = $this->config->get('sms_notify_viber_caption');
        } else {
            $this->params['viber_caption'] = '';
        }

        if (isset($this->request->post['sms_notify_viber_url'])) {
            $this->params['viber_url'] = $this->request->post['sms_notify_viber_url'];
        } elseif ($this->config->get('sms_notify_viber_url')) {
            $this->params['viber_url'] = $this->config->get('sms_notify_viber_url');
        } else {
            $this->params['viber_url'] = '';
        }

        if (isset($this->request->post['sms_notify_viber_image_width'])) {
            $this->params['width'] = $this->request->post['sms_notify_viber_image_width'];
        } elseif ($this->config->get('sms_notify_viber_image_width')) {
            $this->params['width'] = $this->config->get('sms_notify_viber_image_width');
        } else {
            $this->params['width'] = '400';
        }

        if (isset($this->request->post['sms_notify_viber_image_height'])) {
            $this->params['height'] = $this->request->post['sms_notify_viber_image_height'];
        } elseif ($this->config->get('sms_notify_viber_image_height')) {
            $this->params['height'] = $this->config->get('sms_notify_viber_image_height');
        } else {
            $this->params['height'] = '400';
        }

        $this->load->model('tool/image');

        $this->params['viber_image'] = '';
        $this->params['viber_thumb'] = '';

        $this->params['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        if (isset($this->request->post['sms_notify_viber_image'])) {
            $this->params['viber_image'] = $this->request->post['sms_notify_viber_image'];
        } elseif ($this->config->get('sms_notify_viber_image')) {
            $image = $this->config->get('sms_notify_viber_image');

            if (is_file(DIR_IMAGE . $image)) {
                $this->params['viber_thumb'] = $this->model_tool_image->resize($image, 100, 100);
                $this->params['viber_image'] = $image;
            }
        }

        if (isset($this->request->post['sms_notify_client_viber_template'])) {
            $this->params['client_viber_template'] = $this->request->post['sms_notify_client_viber_template'];
        } elseif ($this->config->get('sms_notify_client_viber_template')) {
            $this->params['client_viber_template'] = $this->config->get('sms_notify_client_viber_template');
        } else {
            $this->params['client_viber_template'] = '';
        }

        if (isset($this->request->post['sms_notify_payment_viber_template'])) {
            $this->params['payment_viber_template'] = $this->request->post['sms_notify_payment_viber_template'];
        } elseif ($this->config->get('sms_notify_payment_viber_template')) {
            $this->params['payment_viber_template'] = $this->config->get('sms_notify_payment_viber_template');
        } else {
            $this->params['payment_viber_template'] = [];
        }

        if (isset($this->request->post['sms_notify_viber_template'])) {
            $this->params['order_viber_template'] = $this->request->post['sms_notify_viber_template'];
        } elseif ($this->config->get('sms_notify_viber_template')) {
            $this->params['order_viber_template'] = $this->config->get('sms_notify_viber_template');
        } else {
            $this->params['order_viber_template'] = [];
        }

        $this->load->model('localisation/language');

        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        if ($this->config->get('config_editor_default')) {
            $this->document->addScript('view/javascript/ckeditor/ckeditor.js');
            $this->document->addScript('view/javascript/ckeditor/ckeditor_init.js');
        }

        $this->params['ckeditor'] = $this->config->get('config_editor_default');

        $this->params['lang'] = $this->language->get('lang');

        if (isset($this->request->post['sms_notify_sms_template'])) {
            $this->params['sms_template'] = $this->request->post['sms_notify_sms_template'];
        } elseif ($this->config->get('sms_notify_sms_template')) {
            $this->params['sms_template'] = html_entity_decode(
                $this->config->get('sms_notify_sms_template'),
                ENT_QUOTES,
                'UTF-8'
            );
        } else {
            $this->params['sms_template'] = '';
        }

        $this->params['order_statuses'] = $this->model_extension_module_aw_sms_notify->getOrderStatuses();

        if (isset($this->request->post['sms_notify_status_template'])) {
            $this->params['order_status_template'] = $this->request->post['sms_notify_status_template'];
        } elseif ($this->config->get('sms_notify_status_template')) {
            $this->params['order_status_template'] = $this->config->get('sms_notify_status_template');
        } else {
            $this->params['order_status_template'] = [];
        }

        if (isset($this->request->post['sms_notify_order_status'])) {
            $this->params['sms_order_status'] = $this->request->post['sms_notify_order_status'];
        } elseif ($this->config->get('sms_notify_order_status')) {
            $this->params['sms_order_status'] = $this->config->get('sms_notify_order_status');
        } else {
            $this->params['sms_order_status'] = [];
        }

        if (isset($this->request->post['sms_notify_log'])) {
            $this->params['sms_notify_log'] = $this->request->post['sms_notify_log'];
        } elseif ($this->config->get('sms_notify_log')) {
            $this->params['sms_notify_log'] = $this->config->get('sms_notify_log');
        } else {
            $this->params['sms_notify_log'] = '';
        }

        if (isset($this->request->post['sms_notify_log_filename'])) {
            $this->params['sms_notify_log_filename'] = $this->request->post['sms_notify_log_filename'];
        } elseif ($this->config->get('sms_notify_log_filename')) {
            $this->params['sms_notify_log_filename'] = $this->config->get('sms_notify_log_filename');
        } else {
            $this->params['sms_notify_log_filename'] = 'aw_sms_notify';
        }

        $logFileName = $this->params['sms_notify_log_filename'] . '.log';

        $this->params['sms_log'] = '';

        if ($this->config->get('sms_notify_log')) {
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
                        $this->language->get('error_warning'),
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

        $this->response->setOutput($this->awCore->view('extension/module/aw_sms_notify', $this->params));
    }

    public function order()
    {
        $this->load->model('localisation/order_status');

        $this->params['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        return $this->awCore->view('extension/module/aw_sms_notify_list', $this->params);
    }

    public function orderInfoForm()
    {
        $order_id = $this->request->get['order_id'] ?? 0;

        $this->params['sendMessage'] = $this->url->link('extension/module/aw_sms_notify/sendMessage', $this->tokenData['param'] . '&order_id=' . $order_id, true);

        if (isset($this->request->post['sms_notify_sms_template'])) {
            $this->params['sms_template'] = $this->request->post['sms_notify_sms_template'];
        } elseif ($this->config->get('sms_notify_sms_template')) {
            $this->params['sms_template'] = html_entity_decode(
                $this->config->get('sms_notify_sms_template'),
                ENT_QUOTES,
                'UTF-8'
            );
        } else {
            $this->params['sms_template'] = '';
        }

        $this->params['force'] = $this->config->get('sms_notify_force');

        return $this->awCore->view('extension/module/aw_sms_notify_info', $this->params);
    }

    /**
     * @return void
     */
    public function sendMessage()
    {
        $json = [];

        $this->load->model('sale/order');
        $this->load->model('extension/module/aw_sms_notify');

        if (isset($this->request->get['order_id'])) {
            $order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
        } else {
            $order_info = [];
        }

        if ((utf8_strlen($this->request->post['sms_message']) < 3)) {
            $json['error'] = $this->language->get('error_sms');
        }

        $phone = false;

        if ($this->config->get('sms_notify_gatename') && $this->config->get('sms_notify_gate_username')) {
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

            $viber_image_src = $this->config->get('sms_notify_viber_image');
            $viber_image_width = $this->config->get('sms_notify_viber_image_width');
            $viber_image_height = $this->config->get('sms_notify_viber_image_height');

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
                'status' => $this->config->get('sms_notify_viber_alert'),
                'sender' => $this->config->get('sms_notify_viber_sender'),
                'message' => $this->request->post['sms_message'],
                'ttl' => $this->config->get('sms_notify_viber_ttl'),
                'image_url' => $viber_image ?: false,
                'caption' => $this->config->get('sms_notify_viber_caption'),
                'action' => $this->config->get('sms_notify_viber_url'),
            ];

            $options = [
                'to' => $phone,
                'from' => $this->config->get('sms_notify_from'),
                'username' => $this->config->get('sms_notify_gate_username'),
                'password' => $this->config->get('sms_notify_gate_password'),
                'message' => $this->request->post['sms_message'],
                'viber' => $viber_options,
            ];

            $dispatcher = new SmsDispatcher(
                $this->config->get('sms_notify_gatename'),
                $options,
                $this->config->get('sms_notify_log_filename')
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

        $logFileName = $this->config->get('sms_notify_log_filename');

        $logFile = DIR_LOGS . $logFileName . '.log';

        if (! file_exists($logFile)) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (! $this->user->hasPermission('modify', 'extension/module/aw_sms_notify')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            file_put_contents($logFile, '');
            $json['success'] = $this->language->get('text_success_log');
        }

        $this->response->setOutput(json_encode($json));
    }

    protected function validate(): bool
    {
        if (! $this->user->hasPermission('modify', 'extension/module/aw_sms_notify')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return ! $this->error;
    }

    /**
     * @return void
     */
    public function install()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_aw_sms_notify', ['module_aw_sms_notify_status' => '1']);

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
        if ($this->awCore->getVersion() >= 30) {
            $this->load->model('setting/event');
            $model = 'model_setting_event';
        } else {
            $this->load->model('extension/event');
            $model = 'model_extension_event';
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
            'extension/module/aw_sms_notify'
        );
        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'modify',
            'extension/module/aw_sms_notify'
        );
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('sms_notify');

        if ($this->awCore->getVersion() >= 30) {
            $this->load->model('setting/event');

            $this->model_setting_event->deleteEventByCode('aw_sms_notify_order_alert');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_review_alert');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_register_alert');
        } else {
            $this->load->model('extension/event');

            $this->model_extension_event->deleteEvent('aw_sms_notify_order_alert');
            $this->model_extension_event->deleteEvent('aw_sms_notify_review_alert');
            $this->model_extension_event->deleteEvent('aw_sms_notify_register_alert');
        }
    }

    /**
     * @return void
     */
    public function uninstallLegacyEvents()
    {
        if ($this->awCore->getVersion() >= 30) {
            $this->load->model('setting/event');

            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_order_alert');
            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_review_alert');
            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_register_alert');
            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_orderproh');
            $this->model_setting_event->deleteEventByCode('ochelp_sms_notify_order_alert');
            $this->model_setting_event->deleteEventByCode('ochelp_sms_notify_review_alert');
        } else {
            $this->load->model('extension/event');

            $this->model_extension_event->deleteEvent('ocd_sms_notify_order_alert');
            $this->model_extension_event->deleteEvent('ocd_sms_notify_review_alert');
            $this->model_extension_event->deleteEvent('ocd_sms_notify_register_alert');
            $this->model_extension_event->deleteEvent('ocd_sms_notify_orderproh');
            $this->model_extension_event->deleteEvent('ochelp_sms_notify_order_alert');
            $this->model_extension_event->deleteEvent('ochelp_sms_notify_review_alert');
        }
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
